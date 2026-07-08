<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Status;
use App\Http\Controllers\BackendController;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Headquarters;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Role;

class InternalStaffDataController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['installed']);
        $this->data['sitetitle'] = 'Carga Interna';
    }

    public function create()
    {
        if (!$this->hasInternalDataAccess()) {
            abort(403, 'No tiene permisos para acceder a este formulario.');
        }

        $this->data['designations'] = Designation::query()
            ->where('status', Status::ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name']);

        $this->data['departments'] = Department::query()
            ->where('status', Status::ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name']);

        $this->data['regions'] = Region::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $this->data['headquarters'] = Headquarters::query()
            ->orderBy('name')
            ->get(['id', 'name', 'region_id']);

        $selectedDesignationId = session()->getOldInput('designation_id');
        $selectedDepartmentId = session()->getOldInput('department_id');

        $this->data['selectedDesignation'] = null;
        if ($selectedDesignationId) {
            $this->data['selectedDesignation'] = Designation::find($selectedDesignationId);
        }

        $this->data['selectedDepartment'] = null;
        if ($selectedDepartmentId) {
            $this->data['selectedDepartment'] = Department::find($selectedDepartmentId);
        }

        return view('admin.internal-data.create', $this->data);
    }

    public function store(Request $request)
    {
        if (!$this->hasInternalDataAccess()) {
            abort(403, 'No tiene permisos para enviar este formulario.');
        }

        $regionId = (int) $request->input('region_id');
        $regionHasHeadquarters = Headquarters::query()
            ->where('region_id', $regionId)
            ->exists();

        $headquartersRules = $regionHasHeadquarters
            ? [
                'required',
                'integer',
                Rule::exists('headquarters', 'id')->where(function ($query) use ($regionId) {
                    $query->where('region_id', $regionId);
                }),
            ]
            : ['nullable', 'integer', 'prohibited'];

        $validated = $request->validate([
            'cedula' => ['required', 'string', 'max:20'],
            'extension_telefonica' => ['nullable', 'string', 'max:10'],
            'nombres' => ['required', 'string', 'max:120'],
            'apellidos' => ['required', 'string', 'max:120'],
            'email_institucional' => ['nullable', 'email', 'max:120'],
            'region_id' => ['required', 'integer', 'exists:regions,id'],
            'headquarters_id' => $headquartersRules,
            'designation_id' => ['required', 'integer', 'exists:designations,id'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ], [
            'headquarters_id.required' => 'Debe seleccionar una sede para la región indicada.',
            'headquarters_id.integer' => 'La sede seleccionada no es válida.',
            'headquarters_id.exists' => 'La sede seleccionada no pertenece a la región indicada.',
            'headquarters_id.prohibited' => 'La región seleccionada no tiene sedes asociadas; no debe indicar sede.',
        ]);

        $normalized = [
            'cedula' => $this->normalizeUpper($validated['cedula']),
            'extension_telefonica' => $this->normalizeUpper($validated['extension_telefonica'] ?? ''),
            'nombres' => $this->normalizeUpper($validated['nombres']),
            'apellidos' => $this->normalizeUpper($validated['apellidos']),
            'email_institucional' => !empty($validated['email_institucional']) ? mb_strtolower(trim($validated['email_institucional'])) : '',
            'region_id' => (int) $validated['region_id'],
            'headquarters_id' => isset($validated['headquarters_id'])
                ? (int) $validated['headquarters_id']
                : null,
            'designation_id' => (int) $validated['designation_id'],
            'department_id' => (int) $validated['department_id'],
            'observaciones' => $this->normalizeUpper($validated['observaciones'] ?? ''),
        ];

        if ($this->hasConflictingFuncionarioMatch($normalized['cedula'], $normalized['email_institucional'])) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Conflicto detectado: la cédula y el correo corresponden a funcionarios distintos.');
        }

        $result = null;
        DB::transaction(function () use ($normalized, &$result) {
            $result = $this->upsertEmployeeFromNormalizedPayload($normalized);
        });

        if (($result['action'] ?? '') === 'updated') {
            return redirect()
                ->route('admin.internal-staff-data.create')
                ->with('success', 'Los datos del funcionario ' . ($result['name'] ?? $normalized['nombres']) . ' fueron actualizados a los cargados.');
        }

        return redirect()
            ->route('admin.internal-staff-data.create')
            ->with('success', 'Funcionario registrado correctamente: ' . $normalized['nombres'] . ' ' . $normalized['apellidos'] . '.');
    }

    public function storeMassive(Request $request)
    {
        if (!$this->hasInternalDataAccess()) {
            abort(403, 'No tiene permisos para enviar este formulario.');
        }

        $request->validate([
            'massive_file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
        ]);

        $file = $request->file('massive_file');
        $originalName = $file ? $file->getClientOriginalName() : 'archivo';

        if (!$file) {
            return redirect()
                ->route('admin.internal-staff-data.create')
                ->with('error', 'No se recibió archivo para carga masiva.');
        }

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getSheet(0);
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 2) {
            return redirect()
                ->route('admin.internal-staff-data.create')
                ->with('error', 'La plantilla no contiene filas de datos.');
        }

        // Validación de encabezados esperados de la plantilla.
        $expectedHeaders = [
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

        $headerRow = $rows[1] ?? [];
        foreach ($expectedHeaders as $column => $expected) {
            $actual = $this->normalizeUpper((string) ($headerRow[$column] ?? ''));
            if ($actual !== $this->normalizeUpper($expected)) {
                return redirect()
                    ->route('admin.internal-staff-data.create')
                    ->with('error', 'La plantilla no coincide en el encabezado de la columna ' . $column . '.');
            }
        }

        $payloads = [];
        $errors = [];
        $emailsSeen = [];
        $cedulasSeen = [];

        // Comenzar desde fila 2 (datos)
        for ($index = 2; $index <= count($rows); $index++) {
            $row = $rows[$index] ?? [];

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

            // Saltar filas completamente vacías.
            if ($cedula === '' && $nombres === '' && $apellidos === '' && $email === '') {
                continue;
            }

            if (
                $cedula === '' || $nombres === '' || $apellidos === '' ||
                $regionName === '' || $headquartersName === '' ||
                $designationName === '' || $departmentName === ''
            ) {
                $errors[] = 'Fila ' . $index . ': faltan campos obligatorios.';
                continue;
            }

            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Fila ' . $index . ': correo inválido.';
                continue;
            }

            if ($email !== '' && isset($emailsSeen[$email])) {
                $errors[] = 'Fila ' . $index . ': correo repetido dentro del archivo (' . $email . ').';
                continue;
            }
            if ($email !== '') {
                $emailsSeen[$email] = true;
            }

            if (isset($cedulasSeen[$cedula])) {
                $errors[] = 'Fila ' . $index . ': cédula repetida dentro del archivo (' . $cedula . ').';
                continue;
            }
            $cedulasSeen[$cedula] = true;

            $region = Region::query()
                ->whereRaw('UPPER(name) = ?', [$regionName])
                ->first();
            if (!$region) {
                $errors[] = 'Fila ' . $index . ': región no encontrada (' . $regionName . ').';
                continue;
            }

            $headquarters = Headquarters::query()
                ->whereRaw('UPPER(name) = ?', [$headquartersName])
                ->where('region_id', $region->id)
                ->first();
            if (!$headquarters) {
                $errors[] = 'Fila ' . $index . ': sede no encontrada para la región indicada (' . $headquartersName . ').';
                continue;
            }

            $designation = Designation::query()
                ->where('status', Status::ACTIVE)
                ->whereRaw('UPPER(name) = ?', [$designationName])
                ->first();
            if (!$designation) {
                $errors[] = 'Fila ' . $index . ': cargo no encontrado (' . $designationName . ').';
                continue;
            }

            $department = Department::query()
                ->where('status', Status::ACTIVE)
                ->whereRaw('UPPER(name) = ?', [$departmentName])
                ->first();
            if (!$department) {
                $errors[] = 'Fila ' . $index . ': gerencia/oficina no encontrada (' . $departmentName . ').';
                continue;
            }

            if ($this->hasConflictingFuncionarioMatch($cedula, $email)) {
                $errors[] = 'Fila ' . $index . ': conflicto entre cédula y correo (pertenecen a funcionarios distintos).';
                continue;
            }

            $payloads[] = [
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
            ];
        }

        if (!empty($errors)) {
            return redirect()
                ->route('admin.internal-staff-data.create')
                ->with('error', 'No se procesó la carga masiva. Errores: ' . implode(' | ', array_slice($errors, 0, 8)));
        }

        if (empty($payloads)) {
            return redirect()
                ->route('admin.internal-staff-data.create')
                ->with('error', 'No se encontraron filas válidas para registrar.');
        }

        $createdCount = 0;
        $updatedCount = 0;
        $updatedNames = [];

        DB::transaction(function () use ($payloads, &$createdCount, &$updatedCount, &$updatedNames) {
            foreach ($payloads as $payload) {
                $result = $this->upsertEmployeeFromNormalizedPayload($payload);
                if (($result['action'] ?? '') === 'updated') {
                    $updatedCount++;
                    $updatedNames[] = $result['name'] ?? ($payload['nombres'] . ' ' . $payload['apellidos']);
                } else {
                    $createdCount++;
                }
            }
        });

        $message = 'Carga masiva procesada correctamente. Archivo: ' . $originalName . '. Registros creados: ' . $createdCount . '. Registros actualizados: ' . $updatedCount . '.';
        if ($updatedCount > 0) {
            $namesPreview = implode(', ', array_slice($updatedNames, 0, 5));
            $message .= ' Los datos del funcionario ' . $namesPreview . ($updatedCount > 5 ? ' y otros' : '') . ' fueron actualizados a los cargados.';
        }

        return redirect()
            ->route('admin.internal-staff-data.create')
            ->with('success', $message);
    }

    public function downloadMassiveTemplate()
    {
        if (!$this->hasInternalDataAccess()) {
            abort(403, 'No tiene permisos para descargar este recurso.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('plantilla_funcionarios');

        $headers = [
            'A1' => 'cedula',
            'B1' => 'nombres',
            'C1' => 'apellidos',
            'D1' => 'email_institucional',
            'E1' => 'extension_telefonica',
            'F1' => 'region',
            'G1' => 'sede',
            'H1' => 'cargo_actual',
            'I1' => 'gerencia_oficina',
            'J1' => 'observaciones',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Fila de ejemplo para guiar la carga masiva.
        $sheet->fromArray([
            'V-12345678',
            'JUAN ALBERTO',
            'PEREZ GONZALEZ',
            'usuario@seniat.gob.ve',
            '8888',
            'DISTRITO CAPITAL',
            'SEDE CENTRAL',
            'ANALISTA TRIBUTARIO II',
            'GERENCIA DE FISCALIZACION',
            'SIN OBSERVACIONES',
        ], null, 'A2');

        // Hoja auxiliar de catálogos para facilitar llenado correcto.
        $catalogSheet = $spreadsheet->createSheet();
        $catalogSheet->setTitle('catalogos');
        $catalogSheet->setCellValue('A1', 'REGIONES');
        $catalogSheet->setCellValue('B1', 'SEDES');
        $catalogSheet->setCellValue('C1', 'CARGOS');
        $catalogSheet->setCellValue('D1', 'GERENCIAS_OFICINAS');

        $regions = Region::query()->orderBy('name')->pluck('name')->values();
        $headquarters = Headquarters::query()->orderBy('name')->pluck('name')->values();
        $designations = Designation::query()->where('status', Status::ACTIVE)->orderBy('name')->pluck('name')->values();
        $departments = Department::query()->where('status', Status::ACTIVE)->orderBy('name')->pluck('name')->values();

        $maxRows = max($regions->count(), $headquarters->count(), $designations->count(), $departments->count());
        for ($i = 0; $i < $maxRows; $i++) {
            $row = $i + 2;
            if (isset($regions[$i])) {
                $catalogSheet->setCellValue('A' . $row, $regions[$i]);
            }
            if (isset($headquarters[$i])) {
                $catalogSheet->setCellValue('B' . $row, $headquarters[$i]);
            }
            if (isset($designations[$i])) {
                $catalogSheet->setCellValue('C' . $row, $designations[$i]);
            }
            if (isset($departments[$i])) {
                $catalogSheet->setCellValue('D' . $row, $departments[$i]);
            }
        }

        // Auto ancho básico para legibilidad.
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        foreach (range('A', 'D') as $col) {
            $catalogSheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'plantilla_carga_masiva_funcionarios_seniat.xlsx';
        $writer = new Xlsx($spreadsheet);

        // Descarga en stream para evitar escritura en disco y problemas de permisos.
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function getDesignation(Designation $designation)
    {
        if (!$this->hasInternalDataAccess()) {
            abort(403, 'No tiene permisos para consultar este recurso.');
        }

        $designation->load('headquarters.region');

        return response()->json([
            'id' => $designation->id,
            'name' => $designation->name,
            'status' => (int) $designation->status === Status::ACTIVE ? 'Activo' : 'Inactivo',
            'headquarters' => optional($designation->headquarters)->name,
            'region' => optional(optional($designation->headquarters)->region)->name,
        ]);
    }

    public function searchDesignations(Request $request)
    {
        if (!$this->hasInternalDataAccess()) {
            abort(403, 'No tiene permisos para consultar este recurso.');
        }

        $term = (string) $request->query('q', '');
        $page = max((int) $request->query('page', 1), 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $term = trim($term);

        if (mb_strlen($term) < 2) {
            return response()->json([
                'results' => [],
                'pagination' => ['more' => false],
            ]);
        }

        $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $query = Designation::query()
            ->where('status', Status::ACTIVE)
            ->orderBy('name');

        $query->where('name', $operator, '%' . $term . '%');

        $designations = $query
            ->skip($offset)
            ->take($perPage + 1)
            ->get();

        $hasMore = $designations->count() > $perPage;
        $results = $designations->take($perPage);

        return response()->json([
            'results' => $results->map(function (Designation $designation) {
                return [
                    'id' => $designation->id,
                    'text' => $designation->name,
                ];
            })->values(),
            'pagination' => ['more' => $hasMore],
        ]);
    }

    public function getDepartment(Department $department)
    {
        if (!$this->hasInternalDataAccess()) {
            abort(403, 'No tiene permisos para consultar este recurso.');
        }

        $department->load('headquarters.region');

        return response()->json([
            'id' => $department->id,
            'name' => $department->name,
            'status' => (int) $department->status === Status::ACTIVE ? 'Activo' : 'Inactivo',
            'headquarters' => optional($department->headquarters)->name,
            'region' => optional(optional($department->headquarters)->region)->name,
        ]);
    }

    public function searchDepartments(Request $request)
    {
        if (!$this->hasInternalDataAccess()) {
            abort(403, 'No tiene permisos para consultar este recurso.');
        }

        $term = (string) $request->query('q', '');
        $page = max((int) $request->query('page', 1), 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $term = trim($term);

        if (mb_strlen($term) < 2) {
            return response()->json([
                'results' => [],
                'pagination' => ['more' => false],
            ]);
        }

        $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $query = Department::query()
            ->where('status', Status::ACTIVE)
            ->orderBy('name');

        $query->where('name', $operator, '%' . $term . '%');

        $departments = $query
            ->skip($offset)
            ->take($perPage + 1)
            ->get();

        $hasMore = $departments->count() > $perPage;
        $results = $departments->take($perPage);

        return response()->json([
            'results' => $results->map(function (Department $department) {
                return [
                    'id' => $department->id,
                    'text' => $department->name,
                ];
            })->values(),
            'pagination' => ['more' => $hasMore],
        ]);
    }

    private function hasInternalDataAccess(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return true;
        }

        return $user->hasRole('Admin') ||
            $user->hasRole('admin') ||
            $user->hasRole('supervisor') ||
            $user->hasRole('Supervisor');
    }

    private function normalizeUpper(string $value): string
    {
        return mb_strtoupper(trim($value), 'UTF-8');
    }

    private function upsertEmployeeFromNormalizedPayload(array $payload): array
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

                // Evitar choque de correo al actualizar.
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

            $employeeData = [
                'first_name' => mb_substr($payload['nombres'], 0, 20),
                'last_name' => mb_substr($payload['apellidos'], 0, 20),
                'phone' => $providedExtension !== '' ? $phoneValue : ($existingEmployee->phone ?: $phoneValue),
                'official_identification_number' => $payload['cedula'],
                'gender' => 1,
                'department_id' => (int) $payload['department_id'],
                'designation_id' => (int) $payload['designation_id'],
                'region_id' => (int) $payload['region_id'],
                'headquarters_id' => isset($payload['headquarters_id']) ? (int) $payload['headquarters_id'] : null,
                'date_of_joining' => $existingEmployee->date_of_joining ?: Carbon::now()->format('Y-m-d'),
                'about' => $payload['observaciones'] ?: ($providedExtension !== '' ? ('EXTENSION: ' . $providedExtension) : ($existingEmployee->about ?: 'SIN OBSERVACIONES')),
                'status' => Status::ACTIVE,
                'editor_type' => 'App\Models\User',
                'editor_id' => auth()->id() ?? 1,
            ];

            $existingEmployee->update($employeeData);

            $employeeRole = Role::where('name', 'Employee')->first();
            if ($employeeRole && $existingEmployee->user) {
                $existingEmployee->user->assignRole($employeeRole->name);
            }

            $action = 'updated';
            return [
                'action' => $action,
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
            $username = $this->generateUniqueUsernameFromEmail($email);
            $temporaryPassword = Str::random(12);
            $user = User::create([
                'first_name' => $payload['nombres'],
                'last_name' => $payload['apellidos'],
                'username' => $username,
                'email' => $email,
                'phone' => $phoneValue,
                'status' => Status::ACTIVE,
                'password' => Hash::make($temporaryPassword),
            ]);
        }

        $employeeRole = Role::where('name', 'Employee')->first();
        if ($employeeRole && $user) {
            $user->assignRole($employeeRole->name);
        }

        $employeeData = [
            'first_name' => mb_substr($payload['nombres'], 0, 20),
            'last_name' => mb_substr($payload['apellidos'], 0, 20),
            'phone' => $phoneValue,
            'official_identification_number' => $payload['cedula'],
            'user_id' => $user->id,
            'gender' => 1, // valor por defecto para registro rápido
            'department_id' => (int) $payload['department_id'],
            'designation_id' => (int) $payload['designation_id'],
            'region_id' => (int) $payload['region_id'],
            'headquarters_id' => isset($payload['headquarters_id']) ? (int) $payload['headquarters_id'] : null,
            'date_of_joining' => Carbon::now()->format('Y-m-d'),
            'about' => $payload['observaciones'] ?: ($providedExtension !== '' ? ('EXTENSION: ' . $providedExtension) : 'SIN OBSERVACIONES'),
            'status' => Status::ACTIVE,
            'barcode' => null,
            'creator_type' => 'App\Models\User',
            'creator_id' => auth()->id() ?? 1,
            'editor_type' => 'App\Models\User',
            'editor_id' => auth()->id() ?? 1,
        ];

        $employee = Employee::create($employeeData);

        return [
            'action' => $action,
            'employee' => $employee,
            'name' => trim(($payload['nombres'] ?? '') . ' ' . ($payload['apellidos'] ?? '')),
        ];
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
        $normalizedEmail = mb_strtolower(trim((string) $email));
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

        $userByEmail = null;
        if ($normalizedEmail !== '') {
            $userByEmail = User::query()
                ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
                ->first();
        }

        if ($userByEmail && $userByEmail->employee) {
            return $userByEmail->employee;
        }

        return null;
    }

    private function hasConflictingFuncionarioMatch(string $cedula, string $email): bool
    {
        $normalizedCedula = $this->normalizeUpper($cedula);
        $normalizedEmail = mb_strtolower(trim((string) $email));
        $numericCedula = preg_replace('/\D/', '', $normalizedCedula);

        $byCedula = Employee::query()
            ->whereRaw('UPPER(official_identification_number) = ?', [$normalizedCedula])
            ->first();

        if (!$byCedula && $numericCedula !== '') {
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

        if ($byCedula && $byEmail && $byCedula->id !== $byEmail->id) {
            return true;
        }

        return false;
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
