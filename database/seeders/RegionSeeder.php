<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Region;
use Illuminate\Support\Facades\DB;

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
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Vaciar la tabla de regiones
        DB::table('regions')->truncate();
        
        // Reactivar verificación de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
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
            ['name' => 'Region Guyana'],
            ['name' => 'Region Los Llanos'],
            ['name' => 'Nivel Normativo'],
        ];
        
        foreach ($regions as $region) {
            Region::create($region);
        }
    }
}
