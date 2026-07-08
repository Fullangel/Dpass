<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\VisitDestination;
use App\Models\VisitDestinationRule;
use App\Models\VisitingDetails;
use App\Models\User;
use App\Http\Services\PushNotificationService;

class VisitDestinationService
{
    /** @var \Illuminate\Support\Collection<int, VisitDestinationRule>|null */
    private $activeRulesCache = null;
    public function isEnabled(): bool
    {
        return (bool) config('visit_destinations.enabled', false);
    }

    public function userCanCreate(User $user): bool
    {
        return $user->hasRole('Admin') || $user->hasRole('supervisor');
    }

    public function userCanEdit(User $user): bool
    {
        return $user->hasRole('Admin') || $user->hasRole('supervisor');
    }

    public function userCanDelete(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function destinationCanBeDeleted(VisitDestination $destination): bool
    {
        return ! $destination->visitingDetails()->exists();
    }

    /**
     * @param  array{employee_id?: int|null, headquarters_id?: int|null, department_id?: int|null, designation_id?: int|null}  $context
     */
    public function resolveDestinationId(array $context): ?int
    {
        $employeeId = isset($context['employee_id']) ? (int) $context['employee_id'] : null;
        $headquartersId = isset($context['headquarters_id']) ? (int) $context['headquarters_id'] : null;
        $departmentId = isset($context['department_id']) ? (int) $context['department_id'] : null;
        $designationId = isset($context['designation_id']) ? (int) $context['designation_id'] : null;

        if (! $employeeId && ! $departmentId && ! $designationId) {
            return null;
        }

        if ($employeeId && ! $departmentId) {
            $employee = Employee::query()->find($employeeId);
            if ($employee) {
                $departmentId = $departmentId ?: (int) $employee->department_id;
                $designationId = $designationId ?: (int) $employee->designation_id;
                $headquartersId = $headquartersId ?: (int) $employee->headquarters_id;
            }
        }

        $rules = $this->activeRules();

        foreach ([VisitDestinationRule::TYPE_EMPLOYEE, VisitDestinationRule::TYPE_DESIGNATION, VisitDestinationRule::TYPE_DEPARTMENT] as $type) {
            $value = match ($type) {
                VisitDestinationRule::TYPE_EMPLOYEE => $employeeId,
                VisitDestinationRule::TYPE_DEPARTMENT => $departmentId,
                VisitDestinationRule::TYPE_DESIGNATION => $designationId,
            };

            if (! $value) {
                continue;
            }

            $rule = $rules->first(function ($rule) use ($type, $value) {
                return $rule->rule_type === $type && (int) $rule->rule_value === (int) $value;
            });

            if (! $rule || ! $rule->visitDestination) {
                continue;
            }

            $destination = $rule->visitDestination;

            if ($headquartersId && $destination->headquarters_id && (int) $destination->headquarters_id !== $headquartersId) {
                continue;
            }

            return (int) $destination->id;
        }

        return null;
    }

    public function applyToVisitingPayload(array &$visiting, ?Employee $employee = null): void
    {
        $employee = $employee ?: (isset($visiting['employee_id']) ? Employee::find($visiting['employee_id']) : null);

        $destinationId = $this->resolveDestinationId([
            'employee_id' => $visiting['employee_id'] ?? optional($employee)->id,
            'headquarters_id' => $visiting['headquarters_id'] ?? optional($employee)->headquarters_id,
            'department_id' => optional($employee)->department_id,
            'designation_id' => optional($employee)->designation_id,
        ]);

        if ($destinationId) {
            $visiting['visit_destination_id'] = $destinationId;
        }
    }

    public function persistDestinationForVisit(VisitingDetails $visit): void
    {
        if ($visit->visit_destination_id) {
            return;
        }

        $employee = $visit->employee ?: Employee::query()->find($visit->employee_id);

        $destinationId = $this->resolveDestinationId([
            'employee_id' => $visit->employee_id,
            'headquarters_id' => $visit->headquarters_id,
            'department_id' => optional($employee)->department_id,
            'designation_id' => optional($employee)->designation_id,
        ]);

        if ($destinationId) {
            $visit->forceFill(['visit_destination_id' => $destinationId])->saveQuietly();
        }
    }

    /**
     * Repara visitas recientes sin destino (p. ej. creadas antes de activar reglas).
     */
    public function repairMissingDestinations(?\Illuminate\Support\Carbon $since = null): int
    {
        $query = VisitingDetails::query()->whereNull('visit_destination_id');

        if ($since) {
            $query->where('created_at', '>=', $since->copy()->startOfDay());
        }

        $updated = 0;

        $query->orderBy('id')->chunkById(100, function ($visits) use (&$updated) {
            foreach ($visits as $visit) {
                $before = (int) $visit->visit_destination_id;
                $this->persistDestinationForVisit($visit);
                $visit->refresh();

                if (! $before && $visit->visit_destination_id) {
                    $updated++;
                }
            }
        });

        return $updated;
    }

    public function applyDestinationFilter($query, $destinationIds)
    {
        $destinationIds = collect($destinationIds)->filter()->map(fn ($id) => (int) $id)->values();

        if ($destinationIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        $ruleGroups = VisitDestinationRule::query()
            ->active()
            ->whereIn('visit_destination_id', $destinationIds)
            ->get()
            ->groupBy('visit_destination_id');

        return $query->where(function ($outer) use ($destinationIds, $ruleGroups) {
            $outer->whereIn('visit_destination_id', $destinationIds);

            foreach ($destinationIds as $destinationId) {
                $rules = $ruleGroups->get($destinationId, collect());

                if ($rules->isEmpty()) {
                    continue;
                }

                $outer->orWhere(function ($match) use ($rules) {
                    $match->whereNull('visit_destination_id');
                    $match->where(function ($ruleMatch) use ($rules) {
                        foreach ($rules as $rule) {
                            if ($rule->rule_type === VisitDestinationRule::TYPE_EMPLOYEE) {
                                $ruleMatch->orWhere('employee_id', (int) $rule->rule_value);
                            }
                        }
                    });
                });
            }
        });
    }

    public function notifyDestinationReceivers(VisitingDetails $visitingDetails): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $this->persistDestinationForVisit($visitingDetails);

        if (! $visitingDetails->visit_destination_id) {
            return;
        }

        $users = User::query()
            ->whereHas('visitDestinations', function ($query) use ($visitingDetails) {
                $query->where('visit_destinations.id', $visitingDetails->visit_destination_id);
            })
            ->whereNotNull('web_token')
            ->get(['id', 'web_token']);

        if ($users->isEmpty()) {
            return;
        }

        app(PushNotificationService::class)->sendWebNotificationToUsers(
            $visitingDetails,
            $users,
            'Visitante en camino',
            sprintf(
                'Nuevo visitante para %s: %s',
                optional($visitingDetails->visitDestination)->name ?? 'su area',
                optional($visitingDetails->visitor)->name ?? 'visitante'
            )
        );
    }

    /**
     * @return \Illuminate\Support\Collection<int, VisitDestinationRule>
     */
    private function activeRules()
    {
        if ($this->activeRulesCache !== null) {
            return $this->activeRulesCache;
        }

        return $this->activeRulesCache = VisitDestinationRule::query()
            ->active()
            ->with(['visitDestination' => fn ($query) => $query->active()])
            ->get()
            ->filter(fn ($rule) => $rule->visitDestination !== null)
            ->values();
    }

    public static function clearRulesCache(): void
    {
        app(static::class)->activeRulesCache = null;
    }

    /**
     * @return \Illuminate\Support\Collection<int, VisitDestination>
     */
    public function destinationsForUser(User $user)
    {
        return $user->visitDestinations()->active()->orderBy('sort_order')->orderBy('name')->get();
    }

    public function userCanAccessDestinationQueue(User $user): bool
    {
        if (! $user->visitDestinations()->active()->exists()) {
            return false;
        }

        return $user->hasRole('Reception')
            || $user->hasRole('supervisor')
            || $user->hasRole('Admin');
    }

    /**
     * @return \Illuminate\Support\Collection<int, VisitDestination>
     */
    public function destinationsForEmployeeForm(?int $headquartersId = null)
    {
        $query = VisitDestination::query()->active()->orderBy('sort_order')->orderBy('name');

        if ($headquartersId) {
            $query->where(function ($builder) use ($headquartersId) {
                $builder->whereNull('headquarters_id')
                    ->orWhere('headquarters_id', $headquartersId);
            });
        }

        return $query->get();
    }

    public function syncUserDestinations(User $user, array $destinationIds): void
    {
        $user->visitDestinations()->sync(
            collect($destinationIds)
                ->filter(fn ($id) => $id !== null && $id !== '')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all()
        );
    }
}
