<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VisitorStatus;
use App\Http\Controllers\BackendController;
use App\Models\VisitingDetails;
use App\Services\VisitDestinationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class VisitDestinationQueueController extends BackendController
{
    public function __construct(private VisitDestinationService $visitDestinationService)
    {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('visit_destination.access:queue');
        $this->data['sitetitle'] = 'Cola de destinos';
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        if (! $this->visitDestinationService->userCanAccessDestinationQueue($user)) {
            abort(403, 'No tiene destinos de visita asignados.');
        }

        $destinations = $this->visitDestinationService->destinationsForUser($user);
        $destinationIds = $destinations->pluck('id');

        $this->visitDestinationService->repairMissingDestinations(today());

        $todayQuery = VisitingDetails::query();
        $this->visitDestinationService->applyDestinationFilter($todayQuery, $destinationIds);

        $todayCount = (clone $todayQuery)->whereDate('created_at', today())->count();

        $pendingQuery = VisitingDetails::query();
        $this->visitDestinationService->applyDestinationFilter($pendingQuery, $destinationIds);

        $pendingCount = (clone $pendingQuery)
            ->whereNull('checkout_at')
            ->whereIn('status', [VisitorStatus::PENDDING, VisitorStatus::ACCEPT])
            ->count();

        $this->data['destinations'] = $destinations;
        $this->data['todayCount'] = $todayCount;
        $this->data['pendingCount'] = $pendingCount;

        return view('admin.visit-destination-queue.index', $this->data);
    }

    public function getVisits(Request $request)
    {
        $user = auth()->user();

        if (! $this->visitDestinationService->userCanAccessDestinationQueue($user)) {
            abort(403);
        }

        $destinations = $this->visitDestinationService->destinationsForUser($user);
        $destinationIds = $destinations->pluck('id');

        if ($destinationIds->isEmpty()) {
            return Datatables::of(collect())->make(true);
        }

        $this->visitDestinationService->repairMissingDestinations(today());

        $query = VisitingDetails::query()
            ->with(['visitor', 'employee.user', 'region', 'headquarters', 'visitDestination'])
            ->orderByDesc('created_at');

        $this->visitDestinationService->applyDestinationFilter($query, $destinationIds);

        if ($request->boolean('today_only', true)) {
            $query->whereDate('created_at', today());
        }

        if ($request->boolean('inside_only', false)) {
            $query->whereNull('checkout_at');
        }

        $likeOperator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        return Datatables::of($query)
            ->addIndexColumn()
            ->filterColumn('name', function ($query, $keyword) use ($likeOperator) {
                $term = '%' . addcslashes((string) $keyword, '%_\\') . '%';
                $query->whereHas('visitor', function ($q) use ($term, $likeOperator) {
                    $q->where(function ($inner) use ($term, $likeOperator) {
                        $inner->where('first_name', $likeOperator, $term)
                            ->orWhere('last_name', $likeOperator, $term)
                            ->orWhere('national_identification_no', $likeOperator, $term);
                    });
                });
            })
            ->addColumn('action', function ($visitingDetail) {
                $retAction = '';

                if (auth()->user()->can('visitors_show')) {
                    $retAction .= '<a href="' . route('admin.visitors.show', $visitingDetail) . '" class="btn btn-sm btn-icon mr-1 float-left btn-info" data-toggle="tooltip" data-placement="top" title="Ver"><i class="far fa-eye"></i></a>';
                }

                return $retAction;
            })
            ->editColumn('name', function ($visitingDetail) {
                return Str::limit(optional($visitingDetail->visitor)->name, 50);
            })
            ->addColumn('image', function ($visitingDetail) {
                return '<figure class="avatar mr-2"><img src="' . $visitingDetail->images . '" alt=""></figure>';
            })
            ->editColumn('visitor_id', function ($visitingDetail) {
                return optional($visitingDetail->visitor)->national_identification_no;
            })
            ->editColumn('employee_id', function ($visitingDetail) {
                return optional($visitingDetail->employee)->name;
            })
            ->editColumn('destination', function ($visitingDetail) {
                return optional($visitingDetail->visitDestination)->name;
            })
            ->editColumn('purpose', function ($visitingDetail) {
                return Str::limit((string) $visitingDetail->purpose, 60);
            })
            ->editColumn('registered_at', function ($visitingDetail) {
                return optional($visitingDetail->created_at)->format('d-M-Y h:i A');
            })
            ->editColumn('date', function ($visitingDetail) {
                return $visitingDetail->checkin_at
                    ? date('d-M-Y h:i A', strtotime($visitingDetail->checkin_at))
                    : 'N/A';
            })
            ->editColumn('checkout', function ($visitingDetail) {
                return $visitingDetail->checkout_at
                    ? date('d-M-Y h:i A', strtotime($visitingDetail->checkout_at))
                    : 'N/A';
            })
            ->editColumn('status', function ($visitingDetail) {
                $label = trans('visitor_statuses.' . $visitingDetail->status);
                $badgeClass = match ((int) $visitingDetail->status) {
                    VisitorStatus::PENDDING => 'badge-warning',
                    VisitorStatus::ACCEPT => 'badge-success',
                    VisitorStatus::REJECT => 'badge-danger',
                    default => 'badge-secondary',
                };

                return '<span class="badge ' . $badgeClass . '">' . e($label) . '</span>';
            })
            ->rawColumns(['action', 'image', 'status'])
            ->make(true);
    }
}
