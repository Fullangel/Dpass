<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\VisitingDetails;
use App\Services\VisitDestinationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillVisitDestinations extends Command
{
    protected $signature = 'visit-destinations:backfill
                            {--dry-run : Simular cambios sin actualizar la BD}
                            {--only-null : Solo filas sin visit_destination_id}
                            {--headquarters= : Limitar por sede (ID)}';

    protected $description = 'Asigna visit_destination_id a visitas existentes segun reglas configuradas';

    public function handle(VisitDestinationService $service): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $onlyNull = (bool) $this->option('only-null');
        $headquartersId = $this->option('headquarters');

        $this->line($dryRun ? 'Modo simulacion (dry-run)' : 'Modo actualizacion real');

        $query = VisitingDetails::query()->orderBy('id');

        if ($onlyNull) {
            $query->whereNull('visit_destination_id');
        }

        if ($headquartersId) {
            $query->where('headquarters_id', (int) $headquartersId);
        }

        $total = (clone $query)->count();
        $updated = 0;
        $skipped = 0;

        $this->info("Registros a revisar: {$total}");

        $query->chunkById(200, function ($visits) use ($service, $dryRun, &$updated, &$skipped) {
            foreach ($visits as $visit) {
                $employee = Employee::query()->find($visit->employee_id);

                $destinationId = $service->resolveDestinationId([
                    'employee_id' => $visit->employee_id,
                    'headquarters_id' => $visit->headquarters_id,
                    'department_id' => optional($employee)->department_id,
                    'designation_id' => optional($employee)->designation_id,
                ]);

                if (! $destinationId) {
                    $skipped++;
                    continue;
                }

                if ((int) $visit->visit_destination_id === (int) $destinationId) {
                    continue;
                }

                if ($dryRun) {
                    $updated++;
                    continue;
                }

                DB::table('visiting_details')
                    ->where('id', $visit->id)
                    ->update(['visit_destination_id' => $destinationId]);

                $updated++;
            }
        });

        $assignedTotal = DB::table('visiting_details')->whereNotNull('visit_destination_id')->count();

        $this->newLine();
        $this->info(sprintf(
            'Resumen: %d %s, %d sin regla, %d filas con destino en BD.',
            $updated,
            $dryRun ? 'simulados' : 'actualizados',
            $skipped,
            $assignedTotal
        ));

        return self::SUCCESS;
    }
}
