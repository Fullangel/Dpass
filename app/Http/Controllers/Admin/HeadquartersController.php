<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Headquarters;
use App\Models\Region;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;

class HeadquartersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:headquarters|headquarters_create|headquarters_edit|headquarters_delete', ['only' => ['index', 'getHeadquarters']]);
        $this->middleware('permission:headquarters_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:headquarters_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:headquarters_delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        // Verificar permisos adicionales para seguridad
        if (!auth()->user()->can('headquarters') && !auth()->user()->can('headquarters_show')) {
            Log::warning('Intento de acceso no autorizado a sedes', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email,
                'ip' => request()->ip(),
                'timestamp' => now()
            ]);
            abort(403, 'No tiene permisos para ver sedes.');
        }
        
        Log::info('Acceso autorizado a sedes', [
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email,
            'timestamp' => now()
        ]);
        
        $regions = Region::all();
        return view('admin.headquarters.index', compact('regions'));
    }

    public function getHeadquarters(Request $request)
    {
        $headquarters = Headquarters::with('region')->select('headquarters.*');
        
        return DataTables::of($headquarters)
            ->addColumn('region_name', function ($headquarter) {
                return $headquarter->region ? $headquarter->region->name : '-';
            })
            ->addColumn('action', function ($headquarter) {
                $return = '<div class="d-flex justify-content-center">';
                
                if (auth()->user()->can('headquarters_edit')) {
                    $return .= '<a href="' . route('admin.headquarters.edit', $headquarter->id) . '" class="btn btn-sm btn-primary mr-1" data-toggle="tooltip" data-placement="top" title="' . __('levels.edit') . '">
                                <i class="fa fa-edit"></i>
                            </a>';
                }
                
                if (auth()->user()->can('headquarters_delete')) {
                    $return .= '<form action="' . route('admin.headquarters.destroy', $headquarter->id) . '" method="POST" class="d-inline" onsubmit="return confirm(\'¿Está seguro de que desea eliminar esta sede?\');">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-sm btn-danger" data-toggle="tooltip" data-placement="top" title="' . __('levels.delete') . '">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>';
                }
                
                $return .= '</div>';
                return $return;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        $regions = Region::all();
        return view('admin.headquarters.create', compact('regions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'region_id' => 'required|exists:regions,id'
        ]);

        Headquarters::create([
            'name' => $request->name,
            'description' => $request->description,
            'address' => $request->address,
            'phone' => $request->phone,
            'region_id' => $request->region_id
        ]);

        return redirect()->route('admin.headquarters.index')->with('success', 'Sede creada exitosamente.');
    }

    public function edit($id)
    {
        $headquarter = Headquarters::findOrFail($id);
        $regions = Region::all();
        return view('admin.headquarters.edit', compact('headquarter', 'regions'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'region_id' => 'required|exists:regions,id'
        ]);

        $headquarter = Headquarters::findOrFail($id);
        $headquarter->update([
            'name' => $request->name,
            'description' => $request->description,
            'address' => $request->address,
            'phone' => $request->phone,
            'region_id' => $request->region_id
        ]);

        return redirect()->route('admin.headquarters.index')->with('success', 'Sede actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $headquarter = Headquarters::findOrFail($id);
        
        // Verificar si la sede tiene departamentos asociados
        if ($headquarter->departments()->count() > 0) {
            return redirect()->route('admin.headquarters.index')->with('error', 'No se puede eliminar una sede que tiene departamentos asignados.');
        }
        
        // Verificar si la sede tiene visitas asociadas
        if ($headquarter->visitingDetails()->count() > 0) {
            return redirect()->route('admin.headquarters.index')->with('error', 'No se puede eliminar una sede que tiene visitas asociadas.');
        }
        
        // Verificar si la sede tiene asistencias asociadas
        if ($headquarter->attendances()->count() > 0) {
            return redirect()->route('admin.headquarters.index')->with('error', 'No se puede eliminar una sede que tiene asistencias asociadas.');
        }
        
        // Verificar si la sede tiene reservaciones asociadas
        if ($headquarter->bookings()->count() > 0) {
            return redirect()->route('admin.headquarters.index')->with('error', 'No se puede eliminar una sede que tiene reservaciones asociadas.');
        }

        $headquarter->delete();
        return redirect()->route('admin.headquarters.index')->with('success', 'Sede eliminada exitosamente.');
    }

    public function getHeadquartersByRegion(Request $request)
    {
        $region_id = $request->input('region_id');
        
        if (!$region_id) {
            return response()->json([]);
        }
        
        $headquarters = Headquarters::where('region_id', $region_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
        
        return response()->json($headquarters);
    }
}