<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Headquarters;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class InternalStaffDataProductionSafeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_individual_form_stores_data_in_expected_fields(): void
    {
        $admin = $this->getAdminUserOrSkip();
        $catalog = $this->getCatalogOrSkip();

        $unique = (string) now()->timestamp;
        $email = 'qa.individual.' . $unique . '@seniat.gob.ve';
        $cedula = 'V-' . $unique;

        $response = $this->actingAs($admin)->post(route('admin.internal-staff-data.store'), [
            'cedula' => strtolower($cedula),
            'extension_telefonica' => '9001',
            'nombres' => 'jose luis',
            'apellidos' => 'ramirez perez',
            'email_institucional' => $email,
            'region_id' => $catalog['region']->id,
            'headquarters_id' => $catalog['headquarters']->id,
            'designation_id' => $catalog['designation']->id,
            'department_id' => $catalog['department']->id,
            'observaciones' => 'ingreso de prueba',
        ]);

        $response->assertRedirect(route('admin.internal-staff-data.create'));
        $response->assertSessionHas('success');

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertSame('JOSE LUIS', $user->first_name);
        $this->assertSame('RAMIREZ PEREZ', $user->last_name);

        $employee = Employee::where('user_id', $user->id)->first();
        $this->assertNotNull($employee);
        $this->assertSame('V-' . $unique, $employee->official_identification_number);
        $this->assertSame($catalog['region']->id, $employee->region_id);
        $this->assertSame($catalog['headquarters']->id, $employee->headquarters_id);
        $this->assertSame($catalog['designation']->id, $employee->designation_id);
        $this->assertSame($catalog['department']->id, $employee->department_id);
        $this->assertSame('INGRESO DE PRUEBA', $employee->about);
    }

    public function test_individual_form_updates_existing_funcionario_and_shows_notification(): void
    {
        $admin = $this->getAdminUserOrSkip();
        $catalog = $this->getCatalogOrSkip();

        $unique = (string) (now()->timestamp + 1);
        $email = 'qa.duplicate.' . $unique . '@seniat.gob.ve';
        $cedula = 'V-' . $unique;

        $payload = [
            'cedula' => $cedula,
            'extension_telefonica' => '9002',
            'nombres' => 'maria',
            'apellidos' => 'gomez',
            'email_institucional' => $email,
            'region_id' => $catalog['region']->id,
            'headquarters_id' => $catalog['headquarters']->id,
            'designation_id' => $catalog['designation']->id,
            'department_id' => $catalog['department']->id,
            'observaciones' => 'duplicado',
        ];

        $this->actingAs($admin)->post(route('admin.internal-staff-data.store'), $payload);
        $updateResponse = $this->from(route('admin.internal-staff-data.create'))
            ->actingAs($admin)
            ->post(route('admin.internal-staff-data.store'), array_merge($payload, [
                'nombres' => 'maria actualizada',
                'apellidos' => 'gomez actualizada',
                'email_institucional' => 'qa.duplicate.changed.' . $unique . '@seniat.gob.ve',
            ]));

        $updateResponse->assertRedirect(route('admin.internal-staff-data.create'));
        $updateResponse->assertSessionHas('success');

        $employeeCount = Employee::where('official_identification_number', $cedula)->count();
        $this->assertSame(1, $employeeCount);

        $updatedEmployee = Employee::where('official_identification_number', $cedula)->first();
        $this->assertNotNull($updatedEmployee);
        $this->assertSame('MARIA ACTUALIZADA', $updatedEmployee->first_name);
        $this->assertSame('GOMEZ ACTUALIZADA', $updatedEmployee->last_name);
    }

    public function test_massive_upload_stores_valid_file_and_rejects_missing_required_fields(): void
    {
        $admin = $this->getAdminUserOrSkip();
        $catalog = $this->getCatalogOrSkip();

        $uniqueOk = (string) (now()->timestamp + 2);
        $goodFile = $this->buildMassiveUploadFile([[
            'cedula' => 'V-' . $uniqueOk,
            'nombres' => 'ana',
            'apellidos' => 'vasquez',
            'email_institucional' => 'qa.massive.ok.' . $uniqueOk . '@seniat.gob.ve',
            'extension_telefonica' => '9010',
            'region' => $catalog['region']->name,
            'sede' => $catalog['headquarters']->name,
            'cargo_actual' => $catalog['designation']->name,
            'gerencia_oficina' => $catalog['department']->name,
            'observaciones' => 'fila valida',
        ]]);

        $okResponse = $this->actingAs($admin)->post(route('admin.internal-staff-data.store-massive'), [
            'massive_file' => $goodFile,
        ]);

        $okResponse->assertRedirect(route('admin.internal-staff-data.create'));
        $okResponse->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'email' => 'qa.massive.ok.' . $uniqueOk . '@seniat.gob.ve',
        ]);

        $uniqueBad = (string) (now()->timestamp + 3);
        $badFile = $this->buildMassiveUploadFile([[
            'cedula' => 'V-' . $uniqueBad,
            'nombres' => '',
            'apellidos' => 'sin nombre',
            'email_institucional' => 'qa.massive.bad.' . $uniqueBad . '@seniat.gob.ve',
            'extension_telefonica' => '9011',
            'region' => $catalog['region']->name,
            'sede' => $catalog['headquarters']->name,
            'cargo_actual' => $catalog['designation']->name,
            'gerencia_oficina' => $catalog['department']->name,
            'observaciones' => 'debe fallar',
        ]]);

        $badResponse = $this->from(route('admin.internal-staff-data.create'))
            ->actingAs($admin)
            ->post(route('admin.internal-staff-data.store-massive'), [
                'massive_file' => $badFile,
            ]);

        $badResponse->assertRedirect(route('admin.internal-staff-data.create'));
        $badResponse->assertSessionHas('error');
        $this->assertDatabaseMissing('users', [
            'email' => 'qa.massive.bad.' . $uniqueBad . '@seniat.gob.ve',
        ]);
    }

    public function test_massive_upload_updates_existing_funcionarios(): void
    {
        $admin = $this->getAdminUserOrSkip();
        $catalog = $this->getCatalogOrSkip();

        $existingUnique = (string) (now()->timestamp + 4);
        $existingCedula = 'V-' . $existingUnique;
        $existingEmail = 'qa.massive.existing.' . $existingUnique . '@seniat.gob.ve';

        $this->actingAs($admin)->post(route('admin.internal-staff-data.store'), [
            'cedula' => $existingCedula,
            'extension_telefonica' => '9020',
            'nombres' => 'base',
            'apellidos' => 'existente',
            'email_institucional' => $existingEmail,
            'region_id' => $catalog['region']->id,
            'headquarters_id' => $catalog['headquarters']->id,
            'designation_id' => $catalog['designation']->id,
            'department_id' => $catalog['department']->id,
            'observaciones' => 'base para duplicado',
        ]);

        $duplicateFile = $this->buildMassiveUploadFile([
            [
                'cedula' => $existingCedula,
                'nombres' => 'repetido bd',
                'apellidos' => 'uno',
                'email_institucional' => 'qa.massive.new.one.' . $existingUnique . '@seniat.gob.ve',
                'extension_telefonica' => '9021',
                'region' => $catalog['region']->name,
                'sede' => $catalog['headquarters']->name,
                'cargo_actual' => $catalog['designation']->name,
                'gerencia_oficina' => $catalog['department']->name,
                'observaciones' => 'debe actualizar',
            ],
        ]);

        $response = $this->from(route('admin.internal-staff-data.create'))
            ->actingAs($admin)
            ->post(route('admin.internal-staff-data.store-massive'), [
                'massive_file' => $duplicateFile,
            ]);

        $response->assertRedirect(route('admin.internal-staff-data.create'));
        $response->assertSessionHas('success');

        $existingEmployee = Employee::where('official_identification_number', $existingCedula)->first();
        $this->assertNotNull($existingEmployee);
        $this->assertSame('REPETIDO BD', $existingEmployee->first_name);
        $this->assertSame('UNO', $existingEmployee->last_name);
    }

    private function getAdminUserOrSkip(): User
    {
        $admin = User::whereHas('roles', function ($query) {
            $query->where('name', 'Admin');
        })->first();

        if (!$admin) {
            $this->markTestSkipped('No existe usuario con rol Admin para ejecutar pruebas.');
        }

        return $admin;
    }

    private function getCatalogOrSkip(): array
    {
        $designation = Designation::where('status', Status::ACTIVE)->first();
        $department = Department::where('status', Status::ACTIVE)->first();
        $headquarters = Headquarters::first();
        $region = $headquarters ? Region::find($headquarters->region_id) : null;

        if (!$designation || !$department || !$headquarters || !$region) {
            $this->markTestSkipped('Catálogos insuficientes (región/sede/cargo/gerencia) para pruebas.');
        }

        return [
            'designation' => $designation,
            'department' => $department,
            'headquarters' => $headquarters,
            'region' => $region,
        ];
    }

    private function buildMassiveUploadFile(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'cedula',
            'nombres',
            'apellidos',
            'email_institucional',
            'extension_telefonica',
            'region',
            'sede',
            'cargo_actual',
            'gerencia_oficina',
            'observaciones',
        ];

        $sheet->fromArray($headers, null, 'A1');

        $line = 2;
        foreach ($rows as $row) {
            $sheet->fromArray([
                $row['cedula'],
                $row['nombres'],
                $row['apellidos'],
                $row['email_institucional'],
                $row['extension_telefonica'],
                $row['region'],
                $row['sede'],
                $row['cargo_actual'],
                $row['gerencia_oficina'],
                $row['observaciones'],
            ], null, 'A' . $line);
            $line++;
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'massive-test-');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpPath);

        return new UploadedFile(
            $tmpPath,
            'carga-masiva.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }
}
