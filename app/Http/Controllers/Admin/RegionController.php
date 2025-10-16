<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegionRequest;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class RegionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:regions|regions_create|regions_edit|regions_delete', ['only' => ['index', 'getRegions']]);
        $this->middleware('permission:regions_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:regions_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:regions_delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $title = 'Regions';
        return view('admin.region.index', compact('title'));
    }

    public function getRegions(Request $request)
    {
        if ($request->ajax()) {
            $regions = Region::select('id', 'name', 'created_at', 'updated_at')->get();
            
            return DataTables::of($regions)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';
                    $user = Auth::user();
                    if ($user && $user->can('regions_edit')) {
                        $btn .= '<a href="' . route('admin.regions.edit', $row->id) . '" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>';
                    }
                    if ($user && $user->can('regions_delete')) {
                        if ($btn) $btn .= ' ';
                        $btn .= '<form action="' . route('admin.regions.destroy', $row->id) . '" method="POST" style="display: inline-block;">';
                        $btn .= csrf_field();
                        $btn .= method_field('DELETE');
                        $btn .= '<button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(&quot;Are you sure you want to delete this region?&quot;)"><i class="fas fa-trash"></i></button>';
                        $btn .= '</form>';
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function create()
    {
        $title = 'Create Region';
        return view('admin.region.create', compact('title'));
    }

    public function store(RegionRequest $request)
    {
        Region::create([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.regions.index')->with('success', 'Region created successfully.');
    }

    public function edit($id)
    {
        $title = 'Edit Region';
        $region = Region::findOrFail($id);
        return view('admin.region.edit', compact('title', 'region'));
    }

    public function update(RegionRequest $request, $id)
    {
        $region = Region::findOrFail($id);

        $region->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.regions.index')->with('success', 'Region updated successfully.');
    }

    public function destroy($id)
    {
        $region = Region::findOrFail($id);
        
        // Check if region has any headquarters
        if ($region->headquarters()->count() > 0) {
            return redirect()->route('admin.regions.index')->with('error', 'Cannot delete region that has headquarters assigned.');
        }

        $region->delete();
        return redirect()->route('admin.regions.index')->with('success', 'Region deleted successfully.');
    }
}