<?php

namespace App\Http\Services\Employee;

use App\Http\Requests\EmployeeRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class EmployeeService
{
    /**
     * @param Request $request
     * @param int $limit
     * @return mixed
     */
    public function all($headquartersId = null)
    {
        $query = Employee::orderBy('id', 'desc');
        
        // Si es supervisor, filtrar por su sede
        if (auth()->user()->hasRole('supervisor')) {
            $supervisorEmployee = Employee::where('user_id', auth()->user()->id)->first();
            if ($supervisorEmployee) {
                $query->where('headquarters_id', $supervisorEmployee->headquarters_id);
            }
        } elseif ($headquartersId) {
            // Si se proporciona headquartersId y no es supervisor
            $query->where('headquarters_id', $headquartersId);
        }
        
        return $query->get();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        $employee = Employee::find($id);
        
        if (!$employee) {
            return null;
        }
        
        // Verificar que el supervisor solo pueda ver empleados de su sede
        if (auth()->user()->hasRole('supervisor')) {
            $supervisorEmployee = Employee::where('user_id', auth()->user()->id)->first();
            if ($supervisorEmployee && $employee->headquarters_id != $supervisorEmployee->headquarters_id) {
                return null;
            }
        }
        
        return $employee;
    }

    /**
     * @param $column
     * @param $value
     * @return mixed
     */
    public function findWhere($column, $value)
    {
        $result = Employee::where($column, $value)->get();

        return $result;
    }

    /**
     * @param $column
     * @param $value
     * @return mixed
     */
    public function findWhereFirst($column, $value)
    {
        $result = Employee::where($column, $value)->first();

        return $result;
    }

    /**
     * @param int $perPage
     * @return mixed
     */
    public function paginate($perPage = 10)
    {
        return Employee::paginate($perPage);
    }

    /**
     * @param EmployeeRequest $request
     * @return mixed
     */
    public function make(EmployeeRequest $request)
    {
        $input['first_name'] = $request->input('first_name');
        $input['last_name']  = $request->input('last_name');
        $input['username']   = $this->username($request->input('email'));
        $input['email']      = $request->input('email');
        $input['phone']      = $request->input('phone');
        $input['status']      = $request->input('status');
        $input['password']   = Hash::make($request->input('password'));
               $user         = User::create($input);
               
        // Asignar rol seleccionado si viene en la petición, sino rol por defecto (Employee)
        if ($request->has('role_id')) {
            $role = Role::find($request->input('role_id'));
        } else {
            $role = Role::find(2); // Rol Employee por defecto
        }
        $user->assignRole($role->name);

        if ($request->file('image')) {
            $user->addMedia($request->file('image'))->toMediaCollection('user');
        }
        $result='';
        if($user) {
            $data['first_name'] = $request->input('first_name');
            $data['last_name'] = $request->input('last_name');
            $data['phone'] = $request->input('phone');
            $data['user_id'] = $user->id;
            $data['gender'] = $request->input('gender');
            $data['department_id'] = $request->input('department_id');
            $data['designation_id'] = $request->input('designation_id');
            $data['region_id'] = $request->input('region_id');
            $data['headquarters_id'] = $request->input('headquarters_id');
            $data['date_of_joining'] = $request->input('date_of_joining');
            $data['about'] = $request->input('about');
            $data['status'] = $request->input('status');
            
            // Agregar campos de auditoría para actualización
            $data['editor_type'] = 'App\Models\User';
            $data['editor_id'] = auth()->id() ?? 1; // Usar ID del usuario autenticado o 1 por defecto
            
            // Agregar campos de auditoría requeridos
            $data['creator_type'] = 'App\Models\User';
            $data['creator_id'] = auth()->id() ?? 1; // Usar ID del usuario autenticado o 1 por defecto

            $file_name = 'qrcode-' . preg_replace("/[^0-9]/", "", $request->input('phone')) . '.png';
            $data['barcode']  = $file_name;
            $file = public_path('qrcode/' . $file_name);
            QRCode::size(300)->format('png')->generate(route('checkin.visitor-details', preg_replace("/[^0-9]/", "", $request->input('phone'))), $file);

            $result = Employee::create($data);


        }
        return $result;

    }

    /**
     * @param $id
     * @param EmployeeUpdateRequest $request
     * @return mixed
     */
    public function update($id, EmployeeUpdateRequest $request)
    {
        $employee = Employee::find($id);
        $input['first_name'] = $request->input('first_name');
        $input['last_name'] = $request->input('last_name');
        $input['username'] = $this->username($request->input('email'));
        $input['email'] = $request->input('email');
        $input['phone'] = $request->input('phone');
        $input['status']      = $request->input('status');
        $user = User::find($employee->user_id);
        $user->update($input);
        
        // Actualizar rol si viene en la petición y es diferente al actual
        if ($request->has('role_id')) {
            $newRole = Role::find($request->input('role_id'));
            if ($newRole && $user->hasRole($newRole->name) === false) {
                // Remover todos los roles actuales y asignar el nuevo
                $user->syncRoles([$newRole->name]);
            }
        }
        
        if ($request->file('image')) {
            $user->media()->delete();
            $user->addMedia($request->file('image'))->toMediaCollection('user');
        }
        if($user) {
            $data['first_name'] = $request->input('first_name');
            $data['last_name'] = $request->input('last_name');
            $data['phone'] = $request->input('phone');
            $data['user_id'] = $user->id;
            $data['gender'] = $request->input('gender');
            $data['department_id'] = $request->input('department_id');
            $data['designation_id'] = $request->input('designation_id');
            $data['region_id'] = $request->input('region_id');
            $data['headquarters_id'] = $request->input('headquarters_id');
            $data['date_of_joining'] = $request->input('date_of_joining');
            $data['about'] = $request->input('about');
            $data['status'] = $request->input('status');

            $file_name = 'qrcode-' . preg_replace("/[^0-9]/", "", $request->input('phone')) . '.png';
            $data['barcode']  = $file_name;
            $file = public_path('qrcode/' . $file_name);
            QRCode::size(300)->format('png')->generate(route('checkin.visitor-details', preg_replace("/[^0-9]/", "", $request->input('phone'))), $file);

            $employee->update($data);

        }
        return $employee;
    }

    public function check($id,$request)
    {

        if($request['status'] == 1){
            $checkin = new Attendance();
            $checkin->employee_id            = $id;
            $checkin->status                 = $request['status'];
            $checkin->checkin_time           = $request['checkin_time'];
            $checkin->date                   = date('Y-m-d', strtotime($request['date']));
            $checkin->save();
            return $checkin;
        }elseif ($request['status'] == 2){

            $checkout = Attendance::where(['employee_id'=>$id,'date'=>date('Y-m-d')])->first();
            $checkout->status                   = $request['status'];
            $checkout->checkout_time            = $request['checkout_time'];
            $checkout->date                     = date('Y-m-d', strtotime($request['date']));
            $checkout->save();
            return $checkout;
        }
        return false;
    }
    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        try {
            $employee = Employee::find($id);
            $employee->user->delete();
            $employee->delete();
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    private function username($email)
    {
        $emails = explode('@', $email);
        return $emails[0] . mt_rand();
    }
}
