<?php

namespace App\Http\Composers;

use Illuminate\View\View;
use App\Enums\VisitorStatus;
use App\Models\VisitingDetails;

class NotificationComposer
{
    public function compose(View $view)
    {
        $latestVisitors = [];
        
        // Verificar que el usuario esté autenticado
        $user = auth()->user();
        if (!$user) {
            $view->with('latestVisitors', $latestVisitors);
            return;
        }
        
        // Verificar que el usuario tenga un rol y que sea el rol 2 (Employee)
        if (isset($user->myrole) && $user->myrole == 2) {
            // Verificar que el usuario tenga un empleado asociado
            if ($user->employee) {
                $latestVisitors = VisitingDetails::where('status', VisitorStatus::PENDDING)
                    ->where(['employee_id' => $user->employee->id])
                    ->orderBy('id', 'desc')
                    ->get();
            }
        }
        
        $view->with('latestVisitors', $latestVisitors);
    }
}
