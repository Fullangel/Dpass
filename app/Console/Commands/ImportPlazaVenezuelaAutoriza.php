<?php

namespace App\Console\Commands;

use App\Enums\Status;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Headquarters;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Permission\Models\Role;

class ImportPlazaVenezuelaAutoriza extends Command
{
    protected $signature = 'import:plaza-venezuela-autoriza
                            {--path=Plaza Venezuela : Carpeta con archivos xlsx}
                            {--dry-run : Simular sin insertar en BD}';

    protected $description = 'Importa funcionarios desde columna Autoriza de los Excel de Plaza Venezuela';

    private const HEADER_ROW = 7;
    private const DATA_START_ROW = 8;

    private array $gerenciaAliases = [
        'CNTROL TRIB' => 'DIVISION DE CONTROL TRIBUTARIO',
        'CONTROL TRIB' => 'DIVISION DE CONTROL TRIBUTARIO',
        'TRIBU' => 'DIVISION DE CONTROL TRIBUTARIO',
        'TRIBUTARIO' => 'DIVISION DE CONTROL TRIBUTARIO',
        'CNTROL AD' => 'GERENCIA DE CONTROL ADUANERO',
        'CONTROL AD' => 'GERENCIA DE CONTROL ADUANERO',
        'CNTROL AD.' => 'GERENCIA DE CONTROL ADUANERO',
        'CONTROL AD.' => 'GERENCIA DE CONTROL ADUANERO',
        'CNTROOL AD' => 'GERENCIA DE CONTROL ADUANERO',
        'CNTROL POS' => 'DIVISIÓN DE CONTROL POSTERIOR',
        'CONTROL POS' => 'DIVISIÓN DE CONTROL POSTERIOR',
        'CNTROL POST' => 'DIVISIÓN DE CONTROL POSTERIOR',
        'CONTROL POST' => 'DIVISIÓN DE CONTROL POSTERIOR',
        'CNTROL PÑOS' => 'DIVISIÓN DE CONTROL POSTERIOR',
        'SER MED' => 'DIVISION DE SERVICIO MEDICO Y SEGURIDAD SOCIAL',
        'SERV MED' => 'DIVISION DE SERVICIO MEDICO Y SEGURIDAD SOCIAL',
        'SERVICIO MED' => 'DIVISION DE SERVICIO MEDICO Y SEGURIDAD SOCIAL',
        'SERVICIO MEDICO' => 'DIVISION DE SERVICIO MEDICO Y SEGURIDAD SOCIAL',
        'SERV' => 'DIVISION DE SERVICIO MEDICO Y SEGURIDAD SOCIAL',
        'RECAUDACION' => 'GERENCIA DE RECAUDACIÓN',
        'RECUDACION' => 'GERENCIA DE RECAUDACIÓN',
        'RECAUDOS' => 'GERENCIA DE RECAUDACIÓN',
        'FINANCIERA' => 'GERENCIA FINANCIERA ADMINISTRATIVA',
        'FINA' => 'GERENCIA FINANCIERA ADMINISTRATIVA',
        'FINNCIERA' => 'GERENCIA FINANCIERA ADMINISTRATIVA',
        'INANCIERA' => 'GERENCIA FINANCIERA ADMINISTRATIVA',
        'FISCALIZACION' => 'GERENCIA DE FISCALIZACION',
        'NORMATIVA LEGAL' => 'GERNCIA DE NORMATIVA LEGAL',
        'AUDITORIA' => 'COORDINACION DE AUDITORIA',
        'ONIPC' => 'DESPACHO ONIPC',
        'INFRAESTRUCTURA' => 'GERENCIA  DE INFRAESTRUCTURA',
        'INFRESTRUCTURA' => 'GERENCIA  DE INFRAESTRUCTURA',
        'SAS' => 'DIVISION DE SERVICIO AUTOADMINISTRADO DE SALUD SENIAT (SASS)',
        'SASS' => 'DIVISION DE SERVICIO AUTOADMINISTRADO DE SALUD SENIAT (SASS)',
        'CEF' => 'OFICINA DE CENTRO DE ESTUDIOS FISCALES',
        'CENTRO DE ESTUDIOS FISCALES' => 'OFICINA DE CENTRO DE ESTUDIOS FISCALES',
        'PLANIFICACION Y PRESUPUESTO' => 'OFICINA DE PLANIFICACION Y PRESUPUESTO',
        'REGIMENES ADUANEROS' => 'GERENCIA DE REGIMENES ADUANEROS',
        'REGIMENES ADUNEROS' => 'GERENCIA DE REGIMENES ADUANEROS',
        'REG' => 'GERENCIA DE REGIMENES ADUANEROS',
        'BIENES NAC' => 'DIVISION DE DISPOSICION DE BIENES',
        'BIENESADJUDICADOS' => 'OFICINA DE ALMACENAMIENTO Y DISPOSICION DE BIENES ADJUDICADOS',
        'BIENES ADJUDICADOS' => 'OFICINA DE ALMACENAMIENTO Y DISPOSICION DE BIENES ADJUDICADOS',
        'ADMINISTRACION' => 'GERENCIA FINANCIERA ADMINISTRATIVA',
        'ADINISTRACION' => 'GERENCIA FINANCIERA ADMINISTRATIVA',
        'JURIDICO' => 'GERENCIA DE DOCTRINA Y ASESORIA',
        'CAPRES' => 'OFICINA DE PLANIFICACION Y PRESUPUESTO',
        'COMEDOR' => 'EXTRAS',
        'INTI' => 'EXTRAS',
        'RRHH' => 'GERENCIA DE RECURSOS ADMINISTRATIVOS',
        'RECURSOS HUMANOS' => 'GERENCIA DE RECURSOS ADMINISTRATIVOS',
        'GFV' => 'GERENCIA DEL VALOR',
        'VALOR' => 'GERENCIA DEL VALOR',
        'COMPRAS' => 'GERENCIA FINANCIERA ADMINISTRATIVA',
        'COMPRA' => 'GERENCIA FINANCIERA ADMINISTRATIVA',
        'CMPRAS' => 'GERENCIA FINANCIERA ADMINISTRATIVA',
        'COMPRAS Y CONTRATOS' => 'GERENCIA FINANCIERA ADMINISTRATIVA',
        'DEPORTE' => 'EXTRAS',
        'AULAS' => 'OFICINA DE CENTRO DE ESTUDIOS FISCALES',
        'INA' => 'OFICINA DE CENTRO DE ESTUDIOS FISCALES',
        'INI' => 'EXTRAS',
        'UNTI' => 'EXTRAS',
        'LICORES' => 'UNIDAD DE COSTOS',
        'ESPECIES ALCOHOLICAS' => 'UNIDAD DE COSTOS',
        'ESPECIES CONTROLADAS' => 'UNIDAD DE COSTOS',
        'BIATICOS' => 'GERENCIA FINANCIERA ADMINISTRATIVA',
        'BIE NAC' => 'DIVISION DE DISPOSICION DE BIENES',
        'CONTR' => 'GERENCIA FINANCIERA ADMINISTRATIVA',
        'SERVICIO' => 'DIVISION DE SERVICIO MEDICO Y SEGURIDAD SOCIAL',
        'TRANSPORTE' => 'GERENCIA DE RECURSOS ADMINISTRATIVOS',
        'SEGURO' => 'DIVISION DE SERVICIO MEDICO Y SEGURIDAD SOCIAL',
        'ARANCEL' => 'DIVISION DE ARANCELES',
        'CULTURA' => 'EXTRAS',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $folder = base_path($this->option('path'));

        if (! is_dir($folder)) {
            $this->error("No existe la carpeta: {$folder}");
            return self::FAILURE;
        }

        $headquarters = Headquarters::query()
            ->whereRaw('UPPER(name) LIKE ?', ['%PLAZA%VENEZUELA%'])
            ->first();

        if (! $headquarters) {
            $this->error('No se encontró la sede Plaza Venezuela.');
            return self::FAILURE;
        }

        $designation = Designation::query()
            ->where('status', Status::ACTIVE)
            ->whereRaw('UPPER(name) = ?', ['NO PROVISTO'])
            ->first();

        if (! $designation) {
            $this->error('No se encontró el cargo NO PROVISTO.');
            return self::FAILURE;
        }

        $departments = Department::query()
            ->where('status', Status::ACTIVE)
            ->get(['id', 'name']);

        $departmentByName = [];
        foreach ($departments as $department) {
            $departmentByName[$this->normalizeUpper($department->name)] = $department;
        }

        $fallbackDepartment = $departmentByName['EXTRAS'] ?? $departments->first();
        if (! $fallbackDepartment) {
            $this->error('No hay departamentos activos en catálogo.');
            return self::FAILURE;
        }

        $files = glob($folder . '/*.xlsx') ?: [];
        if ($files === []) {
            $this->error('No se encontraron archivos .xlsx en la carpeta.');
            return self::FAILURE;
        }

        sort($files);
        $this->info('Sede: ' . $headquarters->name . ' (ID ' . $headquarters->id . ')');
        $this->info('Archivos a procesar: ' . count($files));
        $this->line($dryRun ? 'Modo: simulación (dry-run)' : 'Modo: importación real');

        $employees = Employee::query()->get(['id', 'first_name', 'last_name', 'headquarters_id']);
        $candidates = $this->collectCandidates($files);

        $skippedExisting = 0;
        $skippedInvalid = 0;
        $created = 0;
        $errors = [];

        foreach ($candidates as $normalizedName => $data) {
            if (! $this->isValidAutorizaName($normalizedName)) {
                $skippedInvalid++;
                continue;
            }

            if ($this->employeeExistsByName($normalizedName, $employees)) {
                $skippedExisting++;
                continue;
            }

            $department = $this->resolveDepartment(
                $data['gerencia'],
                $departmentByName,
                $fallbackDepartment
            );

            $nameParts = $this->splitFullName($normalizedName);
            $cedula = $this->generateUniqueCedula();

            if ($dryRun) {
                $created++;
                $this->line(sprintf(
                    '[DRY] %s | ger=%s -> %s | cedula=%s',
                    $normalizedName,
                    $data['gerencia'] ?: 'SIN GERENCIA',
                    $department->name,
                    $cedula
                ));
                continue;
            }

            try {
                DB::transaction(function () use (
                    $nameParts,
                    $cedula,
                    $department,
                    $designation,
                    $headquarters,
                    &$created,
                    $normalizedName
                ) {
                    $this->createEmployee(
                        $nameParts,
                        $cedula,
                        $department->id,
                        $designation->id,
                        $headquarters
                    );
                    $created++;
                });
            } catch (\Throwable $exception) {
                $errors[] = $normalizedName . ': ' . $exception->getMessage();
            }
        }

        $this->newLine();
        $this->info('Resumen de importación');
        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Candidatos únicos leídos', count($candidates)],
                ['Omitidos (ya existían)', $skippedExisting],
                ['Omitidos (nombre inválido)', $skippedInvalid],
                [$dryRun ? 'Simulados para crear' : 'Creados en BD', $created],
                ['Errores', count($errors)],
            ]
        );

        foreach (array_slice($errors, 0, 10) as $error) {
            $this->warn($error);
        }

        if (! $dryRun && $created > 0) {
            $this->info('Funcionarios en Plaza Venezuela ahora: ' .
                Employee::query()->where('headquarters_id', $headquarters->id)->count());
        }

        return $errors === [] ? self::SUCCESS : self::FAILURE;
    }

    private function collectCandidates(array $files): array
    {
        $candidates = [];

        foreach ($files as $file) {
            $spreadsheet = IOFactory::load($file);
            $rows = $spreadsheet->getSheet(0)->toArray(null, true, true, true);
            $columns = $this->resolveColumns($rows[self::HEADER_ROW] ?? []);

            if ($columns['autoriza'] === null) {
                $this->warn('Sin columna Autoriza: ' . basename($file));
                continue;
            }

            for ($rowIndex = self::DATA_START_ROW; $rowIndex <= count($rows); $rowIndex++) {
                $row = $rows[$rowIndex] ?? [];
                $autoriza = $this->normalizeUpper((string) ($row[$columns['autoriza']] ?? ''));

                if ($autoriza === '') {
                    continue;
                }

                $gerencia = $this->normalizeUpper((string) ($row[$columns['gerencia']] ?? ''));

                if (! isset($candidates[$autoriza])) {
                    $candidates[$autoriza] = [
                        'gerencia' => $gerencia,
                        'gerencia_counts' => [],
                    ];
                }

                if ($gerencia !== '') {
                    $candidates[$autoriza]['gerencia_counts'][$gerencia] =
                        ($candidates[$autoriza]['gerencia_counts'][$gerencia] ?? 0) + 1;
                }
            }
        }

        foreach ($candidates as $name => $data) {
            if ($data['gerencia_counts'] !== []) {
                arsort($data['gerencia_counts']);
                $candidates[$name]['gerencia'] = (string) array_key_first($data['gerencia_counts']);
            }
        }

        return $candidates;
    }

    private function resolveColumns(array $headerRow): array
    {
        $autoriza = null;
        $gerencia = null;

        foreach ($headerRow as $column => $title) {
            $normalizedTitle = $this->normalizeUpper((string) $title);

            if ($autoriza === null && str_contains($normalizedTitle, 'AUTORIZA')) {
                $autoriza = $column;
            }

            if ($gerencia === null && str_contains($normalizedTitle, 'GERENCIA') && str_contains($normalizedTitle, 'OFICINA')) {
                $gerencia = $column;
            }
        }

        return [
            'autoriza' => $autoriza,
            'gerencia' => $gerencia,
        ];
    }

    private function resolveDepartment(string $gerencia, array $departmentByName, Department $fallback): Department
    {
        $normalizedGerencia = $this->normalizeUpper($gerencia);

        if ($normalizedGerencia === '') {
            return $fallback;
        }

        if (isset($this->gerenciaAliases[$normalizedGerencia])) {
            $normalizedGerencia = $this->normalizeUpper($this->gerenciaAliases[$normalizedGerencia]);
        }

        if (isset($departmentByName[$normalizedGerencia])) {
            return $departmentByName[$normalizedGerencia];
        }

        foreach ($departmentByName as $departmentName => $department) {
            if (str_contains($departmentName, $normalizedGerencia) || str_contains($normalizedGerencia, $departmentName)) {
                return $department;
            }
        }

        return $fallback;
    }

    private function createEmployee(
        array $nameParts,
        string $cedula,
        int $departmentId,
        int $designationId,
        Headquarters $headquarters
    ): Employee {
        $email = $this->generateUniqueSystemEmail($cedula);
        $username = $this->generateUniqueUsernameFromEmail($email);
        $phone = 'EXT-SINEXT-' . $cedula;
        $temporaryPassword = Str::random(12);

        $user = User::create([
            'first_name' => mb_substr($nameParts['first'], 0, 20),
            'last_name' => mb_substr($nameParts['last'], 0, 20),
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'status' => Status::ACTIVE,
            'password' => Hash::make($temporaryPassword),
        ]);

        $employeeRole = Role::where('name', 'Employee')->first();
        if ($employeeRole) {
            $user->assignRole($employeeRole->name);
        }

        return Employee::create([
            'first_name' => mb_substr($nameParts['first'], 0, 20),
            'last_name' => mb_substr($nameParts['last'], 0, 20),
            'phone' => $phone,
            'official_identification_number' => $cedula,
            'user_id' => $user->id,
            'gender' => 1,
            'department_id' => $departmentId,
            'designation_id' => $designationId,
            'region_id' => $headquarters->region_id,
            'headquarters_id' => $headquarters->id,
            'date_of_joining' => Carbon::now()->format('Y-m-d'),
            'about' => 'IMPORTADO DESDE EXCEL PLAZA VENEZUELA (AUTORIZA)',
            'status' => Status::ACTIVE,
            'barcode' => null,
            'creator_type' => User::class,
            'creator_id' => 1,
            'editor_type' => User::class,
            'editor_id' => 1,
        ]);
    }

    private function employeeExistsByName(string $normalizedName, $employees): bool
    {
        $tokens = $this->nameTokens($normalizedName);

        foreach ($employees as $employee) {
            $fullName = $this->normalizeUpper(trim($employee->first_name . ' ' . $employee->last_name));

            if ($fullName === $normalizedName) {
                return true;
            }

            if (count($tokens) >= 2 && count(array_intersect($tokens, $this->nameTokens($fullName))) === count($tokens)) {
                return true;
            }
        }

        return false;
    }

    private function isValidAutorizaName(string $name): bool
    {
        if ($name === '' || preg_match('/^\*+$/', $name)) {
            return false;
        }

        return true;
    }

    private function splitFullName(string $fullName): array
    {
        $normalized = $this->normalizeUpper($fullName);
        $parts = $this->nameTokens($normalized);

        if (count($parts) === 1) {
            $token = mb_substr($parts[0], 0, 20);

            return ['first' => $token, 'last' => $token];
        }

        if (count($parts) === 2) {
            return ['first' => $parts[0], 'last' => $parts[1]];
        }

        $lastName = array_pop($parts);

        return [
            'first' => implode(' ', $parts),
            'last' => $lastName,
        ];
    }

    private function generateUniqueCedula(): string
    {
        do {
            $cedula = (string) random_int(10000000, 99999999);
        } while (
            Employee::query()->where('official_identification_number', $cedula)->exists()
            || Employee::query()->where('phone', 'like', '%-' . $cedula)->exists()
        );

        return $cedula;
    }

    private function generateUniqueSystemEmail(string $cedula): string
    {
        $base = 'sinemail' . $cedula . '@seniat.local';
        $candidate = $base;
        $index = 1;

        while (User::query()->whereRaw('LOWER(email) = ?', [mb_strtolower($candidate)])->exists()) {
            $candidate = 'sinemail' . $cedula . $index . '@seniat.local';
            $index++;
        }

        return mb_strtolower($candidate);
    }

    private function generateUniqueUsernameFromEmail(string $email): string
    {
        $localPart = explode('@', $email)[0] ?? 'user';
        $base = preg_replace('/[^a-z0-9_]/', '', mb_strtolower($localPart));

        if ($base === '') {
            $base = 'user';
        }

        $username = $base;
        $index = 1;

        while (User::query()->where('username', $username)->exists()) {
            $username = $base . $index;
            $index++;
        }

        return $username;
    }

    private function normalizeUpper(string $value): string
    {
        return preg_replace('/\s+/', ' ', mb_strtoupper(trim($value), 'UTF-8'));
    }

    private function nameTokens(string $name): array
    {
        return array_values(array_filter(explode(' ', $this->normalizeUpper($name))));
    }
}
