<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Headquarters;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportMataDeCocoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:mata-de-coco'
        . ' {--path_departments=Departamentos mata de coco.csv}'
        . ' {--path_designations=Designaciones mata de coco.csv}'
        . ' {--path_employees=Empleados mata de coco.csv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa departamentos, designaciones y empleados de la sede Mata de Coco desde CSV';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando importación de datos de Mata de Coco...');

        // Buscar la sede de Mata de Coco en la BD remota
        /** @var Headquarters|null $hq */
        $hq = Headquarters::query()
            ->where('name', 'LIKE', '%Mata de Coco%')
            ->orWhere('name', 'LIKE', '%MATA DE COCO%')
            ->first();

        if (! $hq) {
            $this->error('No se encontró la sede "Mata de Coco" en la tabla headquarters.');
            $this->error('Por favor, crea o revisa la sede antes de ejecutar este comando.');
            return self::FAILURE;
        }

        $this->line('Sede encontrada: ' . $hq->id . ' - ' . $hq->name);

        try {
            // Ejecutamos cada bloque de importación de forma independiente.
            // Construimos mapas entre los IDs del CSV y los IDs reales en BD.
            $departmentMap  = $this->importDepartments($hq);
            $designationMap = $this->importDesignations($hq);
            $this->importEmployees($hq, $departmentMap, $designationMap);

            $this->info('Importación completada correctamente.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error durante la importación: ' . $e->getMessage());
            Log::error('Error en import:mata-de-coco', [
                'exception' => $e,
            ]);

            return self::FAILURE;
        }
    }

    /**
     * Importa departamentos y devuelve un mapa [csv_id => db_id].
     */
    protected function importDepartments(Headquarters $hq): array
    {
        $path = base_path($this->option('path_departments'));
        $this->info("Importando departamentos desde: {$path}");

        if (! is_readable($path)) {
            throw new \RuntimeException("No se puede leer el archivo de departamentos: {$path}");
        }

        $handle = fopen($path, 'r');
        if (! $handle) {
            throw new \RuntimeException("No se pudo abrir el archivo de departamentos: {$path}");
        }

        // Saltar cabecera
        fgetcsv($handle);

        $count = 0;
        $map   = [];
        while (($row = fgetcsv($handle)) !== false) {
            // Esperado: id,name,status,created_at,updated_at
            if (count($row) < 3) {
                continue;
            }

            $id     = (int) $row[0];
            $name   = trim($row[1] ?? '');
            $status = (int) ($row[2] ?? 5);

            if ($name === '') {
                continue;
            }

            // Buscamos por nombre + sede para no depender del ID del CSV.
            $department = Department::query()->firstOrCreate(
                [
                    'name'            => $name,
                    'headquarters_id' => $hq->id,
                ],
                [
                    'status' => $status,
                ]
            );

            $map[$id] = $department->id;
            $count++;
        }

        fclose($handle);

        $this->info("Departamentos importados/actualizados: {$count}");
        return $map;
    }

    /**
     * Importa designaciones y devuelve un mapa [csv_id => db_id].
     */
    protected function importDesignations(Headquarters $hq): array
    {
        $path = base_path($this->option('path_designations'));
        $this->info("Importando designaciones desde: {$path}");

        if (! is_readable($path)) {
            throw new \RuntimeException("No se puede leer el archivo de designaciones: {$path}");
        }

        $handle = fopen($path, 'r');
        if (! $handle) {
            throw new \RuntimeException("No se pudo abrir el archivo de designaciones: {$path}");
        }

        // Saltar cabecera
        fgetcsv($handle);

        $count = 0;
        $map   = [];
        while (($row = fgetcsv($handle)) !== false) {
            // Esperado: id,name,status,created_at,updated_at
            if (count($row) < 3) {
                continue;
            }

            $id     = (int) $row[0];
            $name   = trim($row[1] ?? '');
            $status = (int) ($row[2] ?? 5);

            if ($name === '') {
                continue;
            }

            // Buscamos por nombre + sede para no depender del ID del CSV.
            $designation = Designation::query()->firstOrCreate(
                [
                    'name'            => $name,
                    'headquarters_id' => $hq->id,
                ],
                [
                    'status' => $status,
                ]
            );

            $map[$id] = $designation->id;
            $count++;
        }

        fclose($handle);

        $this->info("Designaciones importadas/actualizadas: {$count}");
        return $map;
    }

    /**
     * Importa empleados usando los mapas de departamentos y designaciones.
     *
     * @param  Headquarters  $hq
     * @param  array<int,int>  $departmentMap  [csv_id => db_id]
     * @param  array<int,int>  $designationMap [csv_id => db_id]
     */
    protected function importEmployees(Headquarters $hq, array $departmentMap, array $designationMap): void
    {
        $path = base_path($this->option('path_employees'));
        $this->info("Importando empleados desde: {$path}");

        if (! is_readable($path)) {
            throw new \RuntimeException("No se puede leer el archivo de empleados: {$path}");
        }

        $handle = fopen($path, 'r');
        if (! $handle) {
            throw new \RuntimeException("No se pudo abrir el archivo de empleados: {$path}");
        }

        // Saltar cabecera
        fgetcsv($handle);

        $count = 0;
        $defaultUserId   = 1; // usuario del sistema al que se asociarán los empleados
        $defaultStatus   = 5; // siguiendo el patrón de status=5 en tus CSV
        $defaultGender   = 5; // valor usado frecuentemente como género en visitantes
        $today           = now()->toDateString();

        while (($row = fgetcsv($handle)) !== false) {
            // Esperado: first_name,last_name,department_id,designation_id
            if (count($row) < 4) {
                continue;
            }

            $firstName        = trim($row[0] ?? '');
            $lastName         = trim($row[1] ?? '');
            $csvDepartmentId  = (int) ($row[2] ?? 0);
            $csvDesignationId = (int) ($row[3] ?? 0);

            if ($firstName === '' && $lastName === '') {
                continue;
            }

            // Resolver IDs reales en BD a partir de los IDs del CSV
            $departmentId  = $departmentMap[$csvDepartmentId]  ?? null;
            $designationId = $designationMap[$csvDesignationId] ?? null;

            if (! $departmentId) {
                $this->warn("Empleado omitido por departamento inexistente en BD. CSV department_id={$csvDepartmentId}, empleado={$firstName} {$lastName}");
                continue;
            }
            if (! $designationId) {
                $this->warn("Empleado omitido por designación inexistente en BD. CSV designation_id={$csvDesignationId}, empleado={$firstName} {$lastName}");
                continue;
            }

            Employee::create([
                'first_name'                     => $firstName,
                'last_name'                      => $lastName,
                'phone'                          => 'N/A',
                'nickname'                       => null,
                'display_name'                   => trim($firstName . ' ' . $lastName),
                'gender'                         => $defaultGender,
                'official_identification_number' => 'N/A',
                'date_of_joining'                => $today,
                'status'                         => $defaultStatus,
                'barcode'                        => null,
                'user_id'                        => $defaultUserId,
                'department_id'                  => $departmentId,
                'designation_id'                 => $designationId,
                'about'                          => null,
                'region_id'                      => $hq->region_id,
                'headquarters_id'                => $hq->id,
                // columnas de auditoría obligatorias (creator/editor)
                'creator_type'                   => \App\Models\User::class,
                'creator_id'                     => $defaultUserId,
                'editor_type'                    => \App\Models\User::class,
                'editor_id'                      => $defaultUserId,
            ]);

            $count++;
        }

        fclose($handle);

        $this->info("Empleados importados: {$count}");
    }
}


