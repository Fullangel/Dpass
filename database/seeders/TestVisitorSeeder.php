<?php

namespace Database\Seeders;

use App\Models\Visitor;
use App\Models\VisitingDetails;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestVisitorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Crear visitantes de prueba con números únicos
        $timestamp = time();
        $testVisitors = [
            [
                'first_name' => 'Juan',
                'last_name' => 'Pérez',
                'email' => 'juan.perez.test@example.com',
                'phone' => '+58-414-' . ($timestamp + 1),
                'national_identification_no' => 'V-' . ($timestamp + 1),
                'gender' => 2, // Male
                'address' => 'Caracas, Venezuela',
                'is_pre_register' => false,
                'status' => 5, // Active
                'creator_type' => 'App\Models\User',
                'creator_id' => 1,
                'editor_type' => 'App\Models\User',
                'editor_id' => 1,
            ],
            [
                'first_name' => 'María',
                'last_name' => 'González',
                'email' => 'maria.gonzalez.test@example.com',
                'phone' => '+58-414-' . ($timestamp + 2),
                'national_identification_no' => 'V-' . ($timestamp + 2),
                'gender' => 1, // Female
                'address' => 'Valencia, Venezuela',
                'is_pre_register' => false,
                'status' => 5, // Active
                'creator_type' => 'App\Models\User',
                'creator_id' => 1,
                'editor_type' => 'App\Models\User',
                'editor_id' => 1,
            ],
            [
                'first_name' => 'Carlos',
                'last_name' => 'Rodríguez',
                'email' => 'carlos.rodriguez.test@example.com',
                'phone' => '+58-414-' . ($timestamp + 3),
                'national_identification_no' => 'V-' . ($timestamp + 3),
                'gender' => 2, // Male
                'address' => 'Maracaibo, Venezuela',
                'is_pre_register' => false,
                'status' => 5, // Active
                'creator_type' => 'App\Models\User',
                'creator_id' => 1,
                'editor_type' => 'App\Models\User',
                'editor_id' => 1,
            ],
            [
                'first_name' => 'Ana',
                'last_name' => 'Martínez',
                'email' => 'ana.martinez.test@example.com',
                'phone' => '+58-414-' . ($timestamp + 4),
                'national_identification_no' => 'V-' . ($timestamp + 4),
                'gender' => 1, // Female
                'address' => 'Barquisimeto, Venezuela',
                'is_pre_register' => false,
                'status' => 5, // Active
                'creator_type' => 'App\Models\User',
                'creator_id' => 1,
                'editor_type' => 'App\Models\User',
                'editor_id' => 1,
            ],
            [
                'first_name' => 'Luis',
                'last_name' => 'Hernández',
                'email' => 'luis.hernandez.test@example.com',
                'phone' => '+58-414-' . ($timestamp + 5),
                'national_identification_no' => 'V-' . ($timestamp + 5),
                'gender' => 2, // Male
                'address' => 'Ciudad Guayana, Venezuela',
                'is_pre_register' => false,
                'status' => 5, // Active
                'creator_type' => 'App\Models\User',
                'creator_id' => 1,
                'editor_type' => 'App\Models\User',
                'editor_id' => 1,
            ]
        ];

        $visitors = [];
        foreach ($testVisitors as $visitorData) {
            // Verificar si ya existe un visitante con este teléfono
            $existingVisitor = Visitor::where('phone', $visitorData['phone'])->first();
            if (!$existingVisitor) {
                $visitor = Visitor::create($visitorData);
                $visitors[] = $visitor;
            } else {
                $visitors[] = $existingVisitor;
            }
        }

        // Crear detalles de visita para algunos visitantes
        $visitingDetails = [
            [
                'reg_no' => 'REG-' . str_pad(1, 6, '0', STR_PAD_LEFT),
                'visitor_id' => $visitors[0]->id,
                'employee_id' => 1, // Usar el primer empleado
                'user_id' => 1,
                'checkin_at' => now()->subHours(2),
                'checkout_at' => null,
                'status' => 2, // Inside
                'disable' => false, // No bloqueado
                'creator_type' => 'App\Models\User',
                'creator_id' => 1,
                'editor_type' => 'App\Models\User',
                'editor_id' => 1,
            ],
            [
                'reg_no' => 'REG-' . str_pad(2, 6, '0', STR_PAD_LEFT),
                'visitor_id' => $visitors[1]->id,
                'employee_id' => 2, // Usar el segundo empleado
                'user_id' => 1,
                'checkin_at' => now()->subHours(1),
                'checkout_at' => null,
                'status' => 2, // Inside
                'disable' => true, // BLOQUEADO
                'creator_type' => 'App\Models\User',
                'creator_id' => 1,
                'editor_type' => 'App\Models\User',
                'editor_id' => 1,
            ],
            [
                'reg_no' => 'REG-' . str_pad(3, 6, '0', STR_PAD_LEFT),
                'visitor_id' => $visitors[2]->id,
                'employee_id' => 3, // Usar el tercer empleado
                'user_id' => 1,
                'checkin_at' => now()->subDays(1),
                'checkout_at' => now()->subDays(1)->addHours(3),
                'status' => 1, // Completed
                'disable' => true, // BLOQUEADO
                'creator_type' => 'App\Models\User',
                'creator_id' => 1,
                'editor_type' => 'App\Models\User',
                'editor_id' => 1,
            ],
            [
                'reg_no' => 'REG-' . str_pad(4, 6, '0', STR_PAD_LEFT),
                'visitor_id' => $visitors[3]->id,
                'employee_id' => 1,
                'user_id' => 1,
                'checkin_at' => now()->subHours(3),
                'checkout_at' => null,
                'status' => 2, // Inside
                'disable' => false, // No bloqueado
                'creator_type' => 'App\Models\User',
                'creator_id' => 1,
                'editor_type' => 'App\Models\User',
                'editor_id' => 1,
            ],
            [
                'reg_no' => 'REG-' . str_pad(5, 6, '0', STR_PAD_LEFT),
                'visitor_id' => $visitors[4]->id,
                'employee_id' => 2,
                'user_id' => 1,
                'checkin_at' => now()->subDays(2),
                'checkout_at' => now()->subDays(2)->addHours(2),
                'status' => 1, // Completed
                'disable' => true, // BLOQUEADO
                'creator_type' => 'App\Models\User',
                'creator_id' => 1,
                'editor_type' => 'App\Models\User',
                'editor_id' => 1,
            ]
        ];

        foreach ($visitingDetails as $detailData) {
            VisitingDetails::create($detailData);
        }

        echo "✅ Visitantes de prueba creados exitosamente:\n";
        echo "- Juan Pérez (NO bloqueado)\n";
        echo "- María González (BLOQUEADO) - Para probar el mensaje\n";
        echo "- Carlos Rodríguez (BLOQUEADO) - Para probar el mensaje\n";
        echo "- Ana Martínez (NO bloqueado)\n";
        echo "- Luis Hernández (BLOQUEADO) - Para probar el mensaje\n";
    }
}
