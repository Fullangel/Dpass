<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Headquarters;
use App\Models\Region;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Permission\Models\Role;

class InternalStaffImportService
{
    private const EXPECTED_HEADERS = [
        'A' => 'cedula',
        'B' => 'nombres',
        'C' => 'apellidos',
        'D' => 'email_institucional',
        'E' => 'extension_telefonica',
        'F' => 'region',
        'G' => 'sede',
        'H' => 'cargo_actual',
        'I' => 'gerencia_oficina',
        'J' => 'observaciones',
    ];

    private array $regionsByName = [];
    private array $headquartersByRegionAndName = [];
    private array $designationsByName = [];
    private array $departmentsByName = [];
    private ?Role $employeeRole = null;

    public function importFromFile(string $filePath, bool $allSheets = true, int $actorId = 1): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $this->warmCatalogCaches();

        $payloads = [];
        $errors = [];
        $emailsSeen = [];
        $cedulasSeen = [];

        $sheets = $allSheets
            ? $spreadsheet->getAllSheets()
            : [$spreadsheet->getSheet(0)];

        foreach ($sheets as $sheet) {
            $sheetName = $sheet->getTitle();
            $rows = $sheet->toArray(null, true, true, true);

            if (count($rows) < 2) {
                $errors[] = 'Hoja ' . $sheetName . ': no contiene filas de datos.';
                continue;
            }

            $headerErrors = $this->validateHeaders($rows[1] ?? [], $sheetName);
            if ($headerErrors !== []) {
                $errors = array_merge($errors, $headerErrors);
                continue;
            }

            for ($index = 2; $index <= count($rows); $index++) {
                $row = $rows[$index] ?? [];
                $rowResult = $this->buildPayloadFromRow(
                    $row,
                    $index,
                    $sheetName,
                    $emailsSeen,
                    $cedulasSeen
                );

                if (($rowResult['error'] ?? null) !== null) {
                    $errors[] = $rowResult['error'];
                    continue;
                }

                if (($rowResult['payload'] ?? null) !== null) {
                    $payloads[] = $rowResult['payload'];
                }
            }
        }

        if ($errors !== []) {
            return [
                'success' => false,
                'created' => 0,
                'updated' => 0,
                'errors' => $errors,
                'payloads' => count($payloads),
            ];
        }

        if ($payloads === []) {
            return [
                'success' => false,
                'created' => 0,
                'updated' => 0,
                'errors' => ['No se encontraron filas validas para registrar.'],
                'payloads' => 0,
            ];
        }

        $created = 0;
        $updated = 0;
        $importErrors = [];

        foreach ($payloads as $payload) {
            try {
                DB::transaction(function () use ($payload, $actorId, &$created, &$updated) {
                    $result = $this->upsertEmployeeFromNormalizedPayload($payload, $actorId);
                    if (($result['action'] ?? '') === 'updated') {
                        $updated++;
                    } else {
                        $created++;
                    }
                });
            } catch (\Throwable $exception) {
                $importErrors[] = ($payload['source'] ?? 'fila') . ': ' . $exception->getMessage();
            }
        }

        return [
            'success' => $importErrors === [],
            'created' => $created,
            'updated' => $updated,
            'errors' => $importErrors,
            'payloads' => count($payloads),
        ];
    }

    private function warmCatalogCaches(): void
    {
        foreach (Region::query()->get(['id', 'name']) as $region) {
            $this->regionsByName[$this->normalizeUpper($region->name)] = $region;
        }

        foreach (Headquarters::query()->get(['id', 'name', 'region_id']) as $headquarters) {
            $key = $headquarters->region_id . '|' . $this->normalizeUpper($headquarters->name);
            $this->headquartersByRegionAndName[$key] = $headquarters;
        }

        foreach (Designation::query()->where('status', Status::ACTIVE)->get(['id', 'name']) as $designation) {
            $this->designationsByName[$this->normalizeUpper($designation->name)] = $designation;
        }

        foreach (Department::query()->where('status', Status::ACTIVE)->get(['id', 'name']) as $department) {
            $this->departmentsByName[$this->normalizeUpper($department->name)] = $department;
        }

        $this->employeeRole = Role::where('name', 'Employee')->first();
    }

    private function validateHeaders(array $headerRow, string $sheetName): array
    {
        $errors = [];

        foreach (self::EXPECTED_HEADERS as $column => $expected) {
            $actual = $this->normalizeUpper((string) ($headerRow[$column] ?? ''));
            if ($actual !== $this->normalizeUpper($expected)) {
                $errors[] = 'Hoja ' . $sheetName . ': encabezado invalido en columna ' . $column . '.';
            }
        }

        return $errors;
    }

    private function buildPayloadFromRow(
        array $row,
        int $index,
        string $sheetName,
        array &$emailsSeen,
        array &$cedulasSeen
    ): array {
        $cedula = $this->normalizeUpper((string) ($row['A'] ?? ''));
        $nombres = $this->normalizeUpper((string) ($row['B'] ?? ''));
        $apellidos = $this->normalizeUpper((string) ($row['C'] ?? ''));
        $email = mb_strtolower(trim((string) ($row['D'] ?? '')));
        $extension = $this->normalizeUpper((string) ($row['E'] ?? ''));
        $regionName = $this->normalizeUpper((string) ($row['F'] ?? ''));
        $headquartersName = $this->normalizeUpper((string) ($row['G'] ?? ''));
        $designationName = $this->normalizeUpper((string) ($row['H'] ?? ''));
        $departmentName = $this->normalizeUpper((string) ($row['I'] ?? ''));
        $observaciones = $this->normalizeUpper((string) ($row['J'] ?? ''));
        $source = 'Hoja ' . $sheetName . ', fila ' . $index;

        if ($cedula === '' && $nombres === '' && $apellidos === '' && $email === '') {
            return ['payload' => null, 'error' => null];
        }

        if (
            $cedula === '' || $nombres === '' || $apellidos === '' ||
            $regionName === '' || $headquartersName === '' ||
            $designationName === '' || $departmentName === ''
        ) {
            return ['payload' => null, 'error' => $source . ': faltan campos obligatorios.'];
        }

        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['payload' => null, 'error' => $source . ': correo invalido.'];
        }

        if ($email !== '' && isset($emailsSeen[$email])) {
            return ['payload' => null, 'error' => $source . ': correo repetido dentro del archivo (' . $email . ').'];
        }

        if (isset($cedulasSeen[$cedula])) {
            return ['payload' => null, 'error' => $source . ': cedula repetida dentro del archivo (' . $cedula . ').'];
        }

        $region = $this->regionsByName[$regionName] ?? null;
        if (! $region) {
            return ['payload' => null, 'error' => $source . ': region no encontrada (' . $regionName . ').'];
        }

        $headquarters = $this->headquartersByRegionAndName[$region->id . '|' . $headquartersName] ?? null;
        if (! $headquarters) {
            return ['payload' => null, 'error' => $source . ': sede no encontrada (' . $headquartersName . ').'];
        }

        $designation = $this->designationsByName[$designationName] ?? null;
        if (! $designation) {
            return ['payload' => null, 'error' => $source . ': cargo no encontrado (' . $designationName . ').'];
        }

        $department = $this->departmentsByName[$departmentName] ?? null;
        if (! $department) {
            return ['payload' => null, 'error' => $source . ': gerencia/oficina no encontrada (' . $departmentName . ').'];
        }

        if ($this->hasConflictingFuncionarioMatch($cedula, $email)) {
            return ['payload' => null, 'error' => $source . ': conflicto entre cedula y correo.'];
        }

        if ($email !== '') {
            $emailsSeen[$email] = true;
        }
        $cedulasSeen[$cedula] = true;

        return [
            'payload' => [
                'source' => $source,
                'cedula' => $cedula,
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'email_institucional' => $email,
                'extension_telefonica' => $extension,
                'region_id' => $region->id,
                'headquarters_id' => $headquarters->id,
                'designation_id' => $designation->id,
                'department_id' => $department->id,
                'observaciones' => $observaciones,
            ],
            'error' => null,
        ];
    }

    private function upsertEmployeeFromNormalizedPayload(array $payload, int $actorId): array
    {
        $providedEmail = mb_strtolower(trim((string) ($payload['email_institucional'] ?? '')));
        $providedExtension = $this->normalizeUpper((string) ($payload['extension_telefonica'] ?? ''));
        $cedulaDigits = preg_replace('/[^0-9]/', '', (string) $payload['cedula']);
        $email = $providedEmail !== '' ? $providedEmail : $this->generateUniqueSystemEmail((string) $payload['cedula']);
        $phoneValue = $this->buildPhoneValue($providedExtension, $cedulaDigits);

        $existingEmployee = $this->findExistingFuncionario($payload['cedula'], $email);
        $action = 'created';

        if ($existingEmployee) {
            $user = $existingEmployee->user;
            if ($user) {
                $userData = [
                    'first_name' => $payload['nombres'],
                    'last_name' => $payload['apellidos'],
                    'status' => Status::ACTIVE,
                ];
                if ($providedEmail !== '') {
                    $userData['email'] = $email;
                }
                if ($providedExtension !== '') {
                    $userData['phone'] = $phoneValue;
                }

                if (isset($userData['email'])) {
                    $emailInUseByOther = User::whereRaw('LOWER(email) = ?', [$email])
                        ->where('id', '!=', $user->id)
                        ->exists();
                    if ($emailInUseByOther) {
                        unset($userData['email']);
                    }
                }

                $user->update($userData);
            }

            $existingEmployee->update([
                'first_name' => mb_substr($payload['nombres'], 0, 20),
                'last_name' => mb_substr($payload['apellidos'], 0, 20),
                'phone' => $providedExtension !== '' ? $phoneValue : ($existingEmployee->phone ?: $phoneValue),
                'official_identification_number' => $payload['cedula'],
                'gender' => 1,
                'department_id' => (int) $payload['department_id'],
                'designation_id' => (int) $payload['designation_id'],
                'region_id' => (int) $payload['region_id'],
                'headquarters_id' => (int) $payload['headquarters_id'],
                'date_of_joining' => $existingEmployee->date_of_joining ?: Carbon::now()->format('Y-m-d'),
                'about' => $payload['observaciones'] ?: ($providedExtension !== '' ? ('EXTENSION: ' . $providedExtension) : ($existingEmployee->about ?: 'SIN OBSERVACIONES')),
                'status' => Status::ACTIVE,
                'editor_type' => User::class,
                'editor_id' => $actorId,
            ]);

            if ($this->employeeRole && $existingEmployee->user) {
                $existingEmployee->user->assignRole($this->employeeRole->name);
            }

            return [
                'action' => 'updated',
                'employee' => $existingEmployee->fresh(['user']),
                'name' => trim(($payload['nombres'] ?? '') . ' ' . ($payload['apellidos'] ?? '')),
            ];
        }

        $existingUserByEmail = $providedEmail !== ''
            ? User::whereRaw('LOWER(email) = ?', [$email])->first()
            : null;

        if ($existingUserByEmail) {
            $user = $existingUserByEmail;
            $user->update([
                'first_name' => $payload['nombres'],
                'last_name' => $payload['apellidos'],
                'phone' => $providedExtension !== '' ? $phoneValue : ($existingUserByEmail->phone ?: $phoneValue),
                'status' => Status::ACTIVE,
            ]);
        } else {
            $user = User::create([
                'first_name' => $payload['nombres'],
                'last_name' => $payload['apellidos'],
                'username' => $this->generateUniqueUsernameFromEmail($email),
                'email' => $email,
                'phone' => $phoneValue,
                'status' => Status::ACTIVE,
                'password' => Hash::make(Str::random(12)),
            ]);
        }

        if ($this->employeeRole && $user) {
            $user->assignRole($this->employeeRole->name);
        }

        $employee = Employee::create([
            'first_name' => mb_substr($payload['nombres'], 0, 20),
            'last_name' => mb_substr($payload['apellidos'], 0, 20),
            'phone' => $phoneValue,
            'official_identification_number' => $payload['cedula'],
            'user_id' => $user->id,
            'gender' => 1,
            'department_id' => (int) $payload['department_id'],
            'designation_id' => (int) $payload['designation_id'],
            'region_id' => (int) $payload['region_id'],
            'headquarters_id' => (int) $payload['headquarters_id'],
            'date_of_joining' => Carbon::now()->format('Y-m-d'),
            'about' => $payload['observaciones'] ?: ($providedExtension !== '' ? ('EXTENSION: ' . $providedExtension) : 'SIN OBSERVACIONES'),
            'status' => Status::ACTIVE,
            'barcode' => null,
            'creator_type' => User::class,
            'creator_id' => $actorId,
            'editor_type' => User::class,
            'editor_id' => $actorId,
        ]);

        return [
            'action' => $action,
            'employee' => $employee,
            'name' => trim(($payload['nombres'] ?? '') . ' ' . ($payload['apellidos'] ?? '')),
        ];
    }

    private function normalizeUpper(string $value): string
    {
        return mb_strtoupper(trim($value), 'UTF-8');
    }

    private function generateUniqueUsernameFromEmail(string $email): string
    {
        $localPart = explode('@', $email)[0] ?? 'user';
        $base = preg_replace('/[^a-z0-9_]/', '', mb_strtolower($localPart));
        if ($base === '') {
            $base = 'user';
        }

        $username = $base;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . $i;
            $i++;
        }

        return $username;
    }

    private function findExistingFuncionario(string $cedula, string $email): ?Employee
    {
        $normalizedCedula = $this->normalizeUpper($cedula);
        $normalizedEmail = mb_strtolower(trim($email));
        $numericCedula = preg_replace('/\D/', '', $normalizedCedula);

        $employeeByOfficialId = Employee::query()
            ->whereRaw('UPPER(official_identification_number) = ?', [$normalizedCedula])
            ->first();
        if ($employeeByOfficialId) {
            return $employeeByOfficialId;
        }

        if ($numericCedula !== '') {
            $employeeByLegacyPhone = Employee::query()
                ->where('phone', 'like', '%-' . $numericCedula)
                ->first();
            if ($employeeByLegacyPhone) {
                return $employeeByLegacyPhone;
            }
        }

        if ($normalizedEmail !== '') {
            $userByEmail = User::query()
                ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
                ->first();
            if ($userByEmail && $userByEmail->employee) {
                return $userByEmail->employee;
            }
        }

        return null;
    }

    private function hasConflictingFuncionarioMatch(string $cedula, string $email): bool
    {
        $normalizedCedula = $this->normalizeUpper($cedula);
        $normalizedEmail = mb_strtolower(trim($email));
        $numericCedula = preg_replace('/\D/', '', $normalizedCedula);

        $byCedula = Employee::query()
            ->whereRaw('UPPER(official_identification_number) = ?', [$normalizedCedula])
            ->first();

        if (! $byCedula && $numericCedula !== '') {
            $byCedula = Employee::query()
                ->where('phone', 'like', '%-' . $numericCedula)
                ->first();
        }

        $byEmail = null;
        if ($normalizedEmail !== '') {
            $byEmail = Employee::query()
                ->whereHas('user', function ($query) use ($normalizedEmail) {
                    $query->whereRaw('LOWER(email) = ?', [$normalizedEmail]);
                })
                ->first();
        }

        return $byCedula && $byEmail && $byCedula->id !== $byEmail->id;
    }

    private function buildPhoneValue(string $extension, string $cedulaDigits): string
    {
        $safeExtension = preg_replace('/[^A-Z0-9]/', '', $extension);
        $safeCedula = $cedulaDigits !== '' ? $cedulaDigits : '00000000';

        if ($safeExtension === '') {
            return 'EXT-SINEXT-' . $safeCedula;
        }

        return 'EXT-' . $safeExtension . '-' . $safeCedula;
    }

    private function generateUniqueSystemEmail(string $cedula): string
    {
        $cedulaDigits = preg_replace('/[^0-9]/', '', $cedula);
        $base = 'sinemail' . ($cedulaDigits !== '' ? $cedulaDigits : 'funcionario') . '@seniat.local';
        $candidate = $base;
        $i = 1;

        while (User::whereRaw('LOWER(email) = ?', [mb_strtolower($candidate)])->exists()) {
            $candidate = 'sinemail' . ($cedulaDigits !== '' ? $cedulaDigits : 'funcionario') . $i . '@seniat.local';
            $i++;
        }

        return mb_strtolower($candidate);
    }
}
