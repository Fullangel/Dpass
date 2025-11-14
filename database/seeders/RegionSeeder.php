<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Region;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Desactivar verificación de claves foráneas temporalmente
        Schema::disableForeignKeyConstraints();
        
        // Vaciar la tabla de regiones
        DB::table('regions')->truncate();
        
        // Reactivar verificación de claves foráneas
        Schema::enableForeignKeyConstraints();
        
        // Crear las regiones especificadas
        $regions = [
            ['name' => 'Region Zuliana'],
            ['name' => 'Region Capital'],
            ['name' => 'Region Central'],
            ['name' => 'Region Libertador'],
            ['name' => 'Region Insular'],
            ['name' => 'Region Occidental'],
            ['name' => 'Region Nororiental'],
            ['name' => 'Region Los Andes'],
            ['name' => 'Region Especiales Plaza'],
            ['name' => 'Region Guayana'],
            ['name' => 'Region Los Llanos'],
            ['name' => 'Nivel Normativo'],
            ['name' => 'Region Centro Occidental'],
            ['name' => 'Region De Contribuyentes Especiales'],
            ['name' => 'Region De La Unidad De Cobros y Recuperaciones De Aduanas'],
            ['name' => 'Region Falcon'],
            ['name' => 'Informacion No Disponible'],
        ];
        
        foreach ($regions as $region) {
            Region::create($region);
        }
    }
}
