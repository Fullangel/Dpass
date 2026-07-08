<?php

namespace App\Console\Commands;

use App\Support\PurposeNormalizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormalizePurposes extends Command
{
    protected $signature = 'purposes:normalize
                            {--dry-run : Simular cambios sin actualizar la BD}
                            {--table=visiting_details,bookings : Tablas a normalizar}';

    protected $description = 'Normaliza los motivos de visita (purpose) a mayusculas sin duplicados por variante';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $tables = array_filter(array_map('trim', explode(',', (string) $this->option('table'))));

        if ($tables === []) {
            $this->error('Debe indicar al menos una tabla.');
            return self::FAILURE;
        }

        $this->line($dryRun ? 'Modo simulacion (dry-run)' : 'Modo actualizacion real');

        $totalUpdated = 0;
        $totalRows = 0;

        foreach ($tables as $table) {
            if (! $this->tableHasPurposeColumn($table)) {
                $this->warn("Tabla omitida (sin columna purpose): {$table}");
                continue;
            }

            $result = $this->normalizeTable($table, $dryRun);
            $totalUpdated += $result['updated'];
            $totalRows += $result['rows'];

            $this->info(sprintf(
                '%s: %d registros revisados, %d %s',
                $table,
                $result['rows'],
                $result['updated'],
                $dryRun ? 'cambiarian' : 'actualizados'
            ));
        }

        $distinctBefore = DB::table('visiting_details')
            ->whereNotNull('purpose')
            ->whereRaw("TRIM(purpose) <> ''")
            ->distinct('purpose')
            ->count('purpose');

        $this->newLine();
        $this->info('Motivos distintos actuales en visiting_details: ' . $distinctBefore);

        if (! $dryRun && $totalUpdated > 0) {
            $distinctAfter = DB::selectOne("
                SELECT COUNT(DISTINCT purpose) AS total
                FROM visiting_details
                WHERE purpose IS NOT NULL AND TRIM(purpose) <> ''
            ");

            $this->info('Motivos distintos despues: ' . ($distinctAfter->total ?? 0));
        }

        $this->info(sprintf(
            'Resumen global: %d registros revisados, %d %s.',
            $totalRows,
            $totalUpdated,
            $dryRun ? 'simulados' : 'actualizados'
        ));

        return self::SUCCESS;
    }

    private function normalizeTable(string $table, bool $dryRun): array
    {
        $updated = 0;
        $rows = 0;

        DB::table($table)
            ->select(['id', 'purpose'])
            ->whereNotNull('purpose')
            ->whereRaw("TRIM(purpose) <> ''")
            ->orderBy('id')
            ->chunkById(500, function ($chunk) use ($table, $dryRun, &$updated, &$rows) {
                foreach ($chunk as $row) {
                    $rows++;
                    $canonical = PurposeNormalizer::canonicalize($row->purpose);

                    if ($canonical === null || $canonical === $row->purpose) {
                        continue;
                    }

                    if (! $dryRun) {
                        DB::table($table)->where('id', $row->id)->update(['purpose' => $canonical]);
                    }

                    $updated++;
                }
            });

        return [
            'rows' => $rows,
            'updated' => $updated,
        ];
    }

    private function tableHasPurposeColumn(string $table): bool
    {
        $column = DB::selectOne("
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = 'public'
              AND table_name = ?
              AND column_name = 'purpose'
            LIMIT 1
        ", [$table]);

        return $column !== null;
    }
}
