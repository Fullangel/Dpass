<?php

// Test de validación de sintaxis para migraciones y seeders

require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 Validando sintaxis de archivos refactorizados...\n\n";

// Validar RegionSeeder
$regionSeederPath = database_path('seeders/RegionSeeder.php');
echo "Validando RegionSeeder...\n";
if (file_exists($regionSeederPath)) {
    $output = [];
    $returnCode = 0;
    exec("php -l $regionSeederPath", $output, $returnCode);
    if ($returnCode === 0) {
        echo "✅ RegionSeeder: Sin errores de sintaxis\n";
    } else {
        echo "❌ RegionSeeder: Errores encontrados\n";
        echo implode("\n", $output) . "\n";
    }
}

// Validar migración de foreign keys
$migrationPath = database_path('migrations/2025_10_13_000004_add_foreign_keys_custom_relations.php');
echo "\nValidando migración de foreign keys...\n";
if (file_exists($migrationPath)) {
    $output = [];
    $returnCode = 0;
    exec("php -l $migrationPath", $output, $returnCode);
    if ($returnCode === 0) {
        echo "✅ Migración FK: Sin errores de sintaxis\n";
    } else {
        echo "❌ Migración FK: Errores encontrados\n";
        echo implode("\n", $output) . "\n";
    }
}

// Verificar uso de clases
echo "\n📋 Verificando uso de clases y métodos...\n";

// Verificar que Schema esté importado en RegionSeeder
$regionContent = file_get_contents($regionSeederPath);
if (strpos($regionContent, 'use Illuminate\Support\Facades\Schema;') !== false) {
    echo "✅ RegionSeeder importa Schema correctamente\n";
} else {
    echo "❌ RegionSeeder no importa Schema\n";
}

// Verificar que se usen métodos de Schema
if (strpos($regionContent, 'Schema::disableForeignKeyConstraints()') !== false) {
    echo "✅ RegionSeeder usa Schema::disableForeignKeyConstraints()\n";
} else {
    echo "❌ RegionSeeder no usa Schema::disableForeignKeyConstraints()\n";
}

if (strpos($regionContent, 'Schema::enableForeignKeyConstraints()') !== false) {
    echo "✅ RegionSeeder usa Schema::enableForeignKeyConstraints()\n";
} else {
    echo "❌ RegionSeeder no usa Schema::enableForeignKeyConstraints()\n";
}

// Verificar migración
$migrationContent = file_get_contents($migrationPath);
if (strpos($migrationContent, 'config(\'database.connections.\' . config(\'database.default\') . \'.driver\')') !== false) {
    echo "✅ Migración detecta driver de base de datos correctamente\n";
} else {
    echo "❌ Migración no detecta driver de base de datos\n";
}

echo "\n🎉 Validación completada!\n";
echo "\nPara probar la migración completa:\n";
echo "1. Configura PostgreSQL: bash scripts/setup_postgresql.sh\n";
echo "2. Migra los datos: bash scripts/migrate_mysql_to_postgresql.sh\n";
echo "3. Actualiza .env con la configuración PostgreSQL\n";
echo "4. Ejecuta: php artisan migrate:fresh --seed\n";