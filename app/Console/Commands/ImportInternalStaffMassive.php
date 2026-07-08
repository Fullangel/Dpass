<?php

namespace App\Console\Commands;

use App\Services\InternalStaffImportService;
use Illuminate\Console\Command;

class ImportInternalStaffMassive extends Command
{
    protected $signature = 'import:internal-staff-massive
                            {file : Ruta al archivo xlsx/csv}
                            {--first-sheet-only : Procesar solo la primera hoja}';

    protected $description = 'Importa funcionarios desde plantilla de carga masiva (todas las hojas por defecto)';

    public function handle(InternalStaffImportService $importService): int
    {
        $file = $this->argument('file');

        if (! is_readable($file)) {
            $this->error('No se puede leer el archivo: ' . $file);
            return self::FAILURE;
        }

        $allSheets = ! (bool) $this->option('first-sheet-only');
        $this->info('Archivo: ' . $file);
        $this->info($allSheets ? 'Modo: todas las hojas' : 'Modo: solo primera hoja');

        $start = microtime(true);
        $result = $importService->importFromFile($file, $allSheets);

        if (($result['errors'] ?? []) !== [] && ($result['created'] ?? 0) === 0 && ($result['updated'] ?? 0) === 0) {
            $this->error('La importacion no se completo.');
            foreach (array_slice($result['errors'], 0, 20) as $error) {
                $this->warn($error);
            }
            if (count($result['errors']) > 20) {
                $this->warn('... y ' . (count($result['errors']) - 20) . ' errores mas.');
            }

            return self::FAILURE;
        }

        $this->newLine();
        $this->table(
            ['Metrica', 'Valor'],
            [
                ['Filas procesadas', $result['payloads'] ?? 0],
                ['Creados', $result['created'] ?? 0],
                ['Actualizados', $result['updated'] ?? 0],
                ['Errores', count($result['errors'] ?? [])],
                ['Tiempo (s)', number_format(microtime(true) - $start, 1)],
            ]
        );

        foreach (array_slice($result['errors'] ?? [], 0, 10) as $error) {
            $this->warn($error);
        }

        if (($result['success'] ?? false) === false && ($result['errors'] ?? []) !== []) {
            return self::FAILURE;
        }

        $this->info('Importacion finalizada.');

        return self::SUCCESS;
    }
}
