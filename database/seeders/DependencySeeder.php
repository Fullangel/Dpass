<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Dependency;
use App\Models\Region;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DependencySeeder extends Seeder
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
        
        // Vaciar la tabla de dependencias
        DB::table('dependencies')->truncate();
        
        // Reactivar verificación de claves foráneas
        Schema::enableForeignKeyConstraints();
        
        // Obtener las regiones con sus IDs
        $regions = Region::pluck('id', 'name')->toArray();
        
        // Crear las dependencias organizadas por región
        $dependencies = [];
        
        // REGION CAPITAL
        $regionCapital = $regions['Region Capital'] ?? null;
        if ($regionCapital) {
            $dependencies[] = ['name' => 'UNIDAD DE CONTRIBUYENTE ESPECIALES GUARENAS / GUATIRE', 'region_id' => $regionCapital];
            $dependencies[] = ['name' => 'UNIDAD DE CONTRIBUYENTES ESPECIALES (HIGUEROTE)', 'region_id' => $regionCapital];
            $dependencies[] = ['name' => 'UNIDAD DE CONTRIBUYENTES ESPECIALES (BARUTA)', 'region_id' => $regionCapital];
            $dependencies[] = ['name' => 'SECTOR HIGUEROTE', 'region_id' => $regionCapital];
            $dependencies[] = ['name' => 'SECTOR  BARUTA', 'region_id' => $regionCapital];
            $dependencies[] = ['name' => 'GERENCIA REGIONAL DE TRIBUTOS INTERNOS REGIÓN CAPITAL', 'region_id' => $regionCapital];
            $dependencies[] = ['name' => 'DIVISIÓN DE ADMINISTRACIÓN', 'region_id' => $regionCapital];
            $dependencies[] = ['name' => 'DIVISIÓN DE FISCALIZACIÓN (REGION CAPITAL)', 'region_id' => $regionCapital];
            $dependencies[] = ['name' => 'DIVISIÓN DE TRAMITACIONES -  REGION CAPITAL', 'region_id' => $regionCapital];
            $dependencies[] = ['name' => 'SECTOR  ALTOS MIRANDINOS', 'region_id' => $regionCapital];
            $dependencies[] = ['name' => 'DIVISIÓN DE SUMARIO ADMINISTRATIVO', 'region_id' => $regionCapital];
            $dependencies[] = ['name' => 'DIVISIÓN DE CONTRIBUYENTES ESPECIALES (LOS RUICES)', 'region_id' => $regionCapital];
            $dependencies[] = ['name' => 'SECTOR VALLES DEL TUY', 'region_id' => $regionCapital];
            $dependencies[] = ['name' => 'UNIDAD DE CONTRIBUYENTES ESPECIALES (VALLES DEL TUY)', 'region_id' => $regionCapital];
            $dependencies[] = ['name' => 'UNIDAD RIO CHICO', 'region_id' => $regionCapital];
            $dependencies[] = ['name' => 'SECTOR GUARENAS / GUATIRE', 'region_id' => $regionCapital];
            $dependencies[] = ['name' => 'CENTRO AUXILIAR NRO. 2 PROPATRIA', 'region_id' => $regionCapital];
            $dependencies[] = ['name' => 'DIVISIÓN JURÍDICO TRIBUTARIA', 'region_id' => $regionCapital];
            $dependencies[] = ['name' => 'DIVISIÓN DE RECAUDACIÓN  (REGION CAPITAL)', 'region_id' => $regionCapital];
            $dependencies[] = ['name' => 'UNIDAD DE CONTRIBUYENTES ESPECIALES (ALTOS MIRANDINOS)', 'region_id' => $regionCapital];
            $dependencies[] = ['name' => 'SEDE REGIONAL DE TRIBUTOS INTERNOS REGION CAPITAL', 'region_id' => $regionCapital];
            $dependencies[] = ['name' => 'DIVISIÓN DE ASISTENCIA AL CONTRIBUYENTE', 'region_id' => $regionCapital];
            $dependencies[] = ['name' => 'DIVISIÓN DE COBRO EJECUTIVO Y MEDIDAS CAUTELARES', 'region_id' => $regionCapital];
        }

        // REGION LOS LLANOS
        $regionLlanos = $regions['Region Los Llanos'] ?? null;
        if ($regionLlanos) {
            $dependencies[] = ['name' => 'DIVISIÓN DE SUMARIO ADMINISTRATIVO', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'SECTOR SAN FERNANDO DE APURE', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'ADUANA SUBALTERNA (LA VICTORIA)', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'DIVISION DE OPERACIONES (ADUANA SUB. LA VICTORIA)', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'DIVISION DE RECAUDACIÓN (ADUANA SUB. LA VICTORIA)', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'DIVISIÓN DE ASISTENCIA AL CONTRIBUYENTE', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'DIVISIÓN JURÍDICO TRIBUTARIA', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'DIVISIÓN DE RECAUDACIÓN  (REGION LOS LLANOS)', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'GERENCIA REGIONAL DE TRIBUTOS INTERNOS REGIÓN LOS LLANOS', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'DIVISIÓN DE ADMINISTRACIÓN', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'DIVISIÓN DE FISCALIZACIÓN', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'SECTOR VALLE DE LA PASCUA', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'UNIDAD ALTAGRACIA DE ORITUCO', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'ADUANA PRINCIPAL EL AMPARO DE APURE', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'DIVISION ASISTENCIA AL CONTRIBUYENTE (ADUANA EL AMPARO DE APURE)', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'DIVISION DE APOYO JURIDICO (ADUANA EL AMPARO DE APURE)', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'SECTOR SAN JUAN DE LOS MORROS', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'DIVISION  ALMACENES Y BIENES ADJUDICADOS (ADUANA EL AMPARO DE APURE)', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'DIVISION DE ADMINISTRACION (ADUANA EL AMPARO DE APURE)', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'DIVISION DE OPERACIONES (ADUANA EL AMPARO DE APURE)', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'DIVISION DE TRAMITACIONES (ADUANA EL AMPARO DE APURE)', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'DIVISION DE RECAUDACION (ADUANA EL AMPARO DE APURE)', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'SEDE REGIONAL DE TRIBUTOS INTERNOS REGION LOS LLANOS', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'DIVISIÓN DE CONTRIBUYENTES ESPECIALES (LOS LLANOS)', 'region_id' => $regionLlanos];
            $dependencies[] = ['name' => 'DIVISIÓN DE TRAMITACIONES', 'region_id' => $regionLlanos];
        }

        // REGION CENTRO OCCIDENTAL
        $regionCentroOccidental = $regions['Region Centro Occidental'] ?? null;
        if ($regionCentroOccidental) {
            $dependencies[] = ['name' => 'GERENCIA REGIONAL DE TRIBUTOS INTERNOS REGIÓN CENTRO OCCIDENTAL', 'region_id' => $regionCentroOccidental];
            $dependencies[] = ['name' => 'DIVISIÓN DE ASISTENCIA AL CONTRIBUYENTE', 'region_id' => $regionCentroOccidental];
            $dependencies[] = ['name' => 'DIVISIÓN JURÍDICO TRIBUTARIA', 'region_id' => $regionCentroOccidental];
            $dependencies[] = ['name' => 'DIVISION DE RECAUDACIÓN  (REGION CENTRO OCCIDENTAL)', 'region_id' => $regionCentroOccidental];
            $dependencies[] = ['name' => 'DIVISIÓN DE SUMARIO ADMINISTRATIVO', 'region_id' => $regionCentroOccidental];
            $dependencies[] = ['name' => 'DIVISIÓN DE CONTRIBUYENTES ESPECIALES (BARQUISIMETO)', 'region_id' => $regionCentroOccidental];
            $dependencies[] = ['name' => 'UNIDAD GUANARE', 'region_id' => $regionCentroOccidental];
            $dependencies[] = ['name' => 'UNIDAD NIRGUA', 'region_id' => $regionCentroOccidental];
            $dependencies[] = ['name' => 'SECTOR CABUDARE', 'region_id' => $regionCentroOccidental];
            $dependencies[] = ['name' => 'SECTOR SAN FELIPE', 'region_id' => $regionCentroOccidental];
            $dependencies[] = ['name' => 'SECTOR ACARIGUA', 'region_id' => $regionCentroOccidental];
            $dependencies[] = ['name' => 'SECTOR CARORA', 'region_id' => $regionCentroOccidental];
            $dependencies[] = ['name' => 'UNIDAD CHIVACOA', 'region_id' => $regionCentroOccidental];
            $dependencies[] = ['name' => 'UNIDAD EL TOCUYO', 'region_id' => $regionCentroOccidental];
            $dependencies[] = ['name' => 'DIVISIÓN DE FISCALIZACIÓN', 'region_id' => $regionCentroOccidental];
            $dependencies[] = ['name' => 'DIVISIÓN DE TRAMITACIONES', 'region_id' => $regionCentroOccidental];
            $dependencies[] = ['name' => 'SEDE REGIONAL DE TRIBUTOS INTERNOS REGION CENTRO OCCIDENTAL', 'region_id' => $regionCentroOccidental];
            $dependencies[] = ['name' => 'DIVISIÓN DE ADMINISTRACIÓN', 'region_id' => $regionCentroOccidental];
            $dependencies[] = ['name' => 'DIVISIÓN DE COBRO EJECUTIVO Y MEDIDA CAUTELAR (REGION CENTRO OCCIDENTAL)', 'region_id' => $regionCentroOccidental];
            $dependencies[] = ['name' => 'SECTOR QUIBOR', 'region_id' => $regionCentroOccidental];
        }

        // REGION ZULIANA
        $regionZuliana = $regions['Region Zuliana'] ?? null;
        if ($regionZuliana) {
            $dependencies[] = ['name' => 'DIVISIÓN DE FISCALIZACIÓN', 'region_id' => $regionZuliana];
            $dependencies[] = ['name' => 'DIVISIÓN JURÍDICO TRIBUTARIA', 'region_id' => $regionZuliana];
            $dependencies[] = ['name' => 'GERENCIA REGIONAL DE TRIBUTOS INTERNOS REGIÓN ZULIANA', 'region_id' => $regionZuliana];
            $dependencies[] = ['name' => 'DIVISIÓN DE ADMINISTRACIÓN', 'region_id' => $regionZuliana];
            $dependencies[] = ['name' => 'DIVISIÓN DE TRAMITACIONES', 'region_id' => $regionZuliana];
            $dependencies[] = ['name' => 'UNIDAD DE CABIMAS (REG.ZULIANA)', 'region_id' => $regionZuliana];
            $dependencies[] = ['name' => 'DIVISIÓN DE SUMARIO ADMINISTRATIVO', 'region_id' => $regionZuliana];
            $dependencies[] = ['name' => 'DIVISIÓN DE CONTRIBUYENTES ESPECIALES (MARACAIBO)', 'region_id' => $regionZuliana];
            $dependencies[] = ['name' => 'UNIDAD SANTA BARBARA', 'region_id' => $regionZuliana];
            $dependencies[] = ['name' => 'UNIDAD MACHIQUES', 'region_id' => $regionZuliana];
            $dependencies[] = ['name' => 'SECTOR CIUDAD OJEDA', 'region_id' => $regionZuliana];
            $dependencies[] = ['name' => 'DIVISIÓN DE ASISTENCIA AL CONTRIBUYENTE', 'region_id' => $regionZuliana];
            $dependencies[] = ['name' => 'SEDE REGIONAL DE TRIBUTOS INTERNOS REGION ZULIANA', 'region_id' => $regionZuliana];
            $dependencies[] = ['name' => 'DIVISIÓN DE RECAUDACIÓN', 'region_id' => $regionZuliana];
        }

        // REGION LOS ANDES
        $regionLosAndes = $regions['Region Los Andes'] ?? null;
        if ($regionLosAndes) {
            $dependencies[] = ['name' => 'UNIDAD TRUJILLO', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'UNIDAD LA GRITA', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'ADUANA SUBALTERNA JUAN PABLO PEREZ ALFONZO', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'SECTOR SOCOPO', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'ADUANA PRINCIPAL SAN ANTONIO DEL TACHIRA', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'ADUANA SUBALTERNA LA CEIBA', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'ADUANA SUBALTERNA AEREA DE SANTO DOMINGO', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'ADUANA SUBALTERNA AEREA JUAN VICENTE GOMEZ', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'DIVISIÓN DE SUMARIO ADMINISTRATIVO', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'DIVISIÓN DE TRAMITACIONES', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'SECTOR MERIDA', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'SECTOR BARINAS', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'SECTOR SAN ANTONIO DEL TACHIRA', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'UNIDAD BOCONÓ', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'SECTOR EL VIGIA', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'UNIDAD GUASDUALITO', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'DIVISIÓN JURÍDICO TRIBUTARIA', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'DIVISIÓN DE CONTRIBUYENTES ESPECIALES (SAN CRISTOBAL)', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'SECTOR  VALERA', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'DIVISIÓN DE ADMINISTRACIÓN', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'DIVISIÓN DE ASISTENCIA AL CONTRIBUYENTE', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'DIVISIÓN DE FISCALIZACIÓN', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'GERENCIA REGIONAL DE TRIBUTOS INTERNOS REGIÓN LOS ANDES', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'ADUANA SUBALTERNA DE UREÑA', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'ADUANA SUBALTERNA BOCA DE GRITA', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'SEDE REGIONAL DE TRIBUTOS INTERNOS REGION LOS ANDES', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'DIVISIÓN DE RECAUDACIÓN', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'ADUANA PRINCIPAL DE MERIDA', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'SECTOR LA FRIA', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'UNIDAD SABANETA', 'region_id' => $regionLosAndes];
            $dependencies[] = ['name' => 'UNIDAD UREÑA', 'region_id' => $regionLosAndes];
        }

        // REGION NOR ORIENTAL
        $regionNorOriental = $regions['Region Nororiental'] ?? null;
        if ($regionNorOriental) {
            $dependencies[] = ['name' => 'UNIDAD DE PUNTA DE MATA', 'region_id' => $regionNorOriental];
            $dependencies[] = ['name' => 'UNIDAD CASANAY', 'region_id' => $regionNorOriental];
            $dependencies[] = ['name' => 'SECTOR MATURIN', 'region_id' => $regionNorOriental];
            $dependencies[] = ['name' => 'SECTOR CARÚPANO', 'region_id' => $regionNorOriental];
            $dependencies[] = ['name' => 'SECTOR EL TIGRE', 'region_id' => $regionNorOriental];
            $dependencies[] = ['name' => 'GERENCIA REGIONAL DE TRIBUTOS INTERNOS REGIÓN NOR-ORIENTAL', 'region_id' => $regionNorOriental];
            $dependencies[] = ['name' => 'DIVISIÓN DE SUMARIO ADMINISTRATIVO', 'region_id' => $regionNorOriental];
            $dependencies[] = ['name' => 'DIVISIÓN DE TRAMITACIONES ', 'region_id' => $regionNorOriental];
            $dependencies[] = ['name' => 'DIVISIÓN DE ADMINISTRACIÓN', 'region_id' => $regionNorOriental];
            $dependencies[] = ['name' => 'UNIDAD DE CONTRIBUYENTES ESPECIALES (MATURIN)', 'region_id' => $regionNorOriental];
            $dependencies[] = ['name' => 'SECTOR ANACO', 'region_id' => $regionNorOriental];
            $dependencies[] = ['name' => 'DIVISIÓN CONTRIBUYENTES ESPECIALES (BARCELONA)', 'region_id' => $regionNorOriental];
            $dependencies[] = ['name' => 'DIVISIÓN DE COBRO EJECUTIVO Y MEDIDAS CAUTELARES', 'region_id' => $regionNorOriental];
            $dependencies[] = ['name' => 'SEDE REGIONAL DE TRIBUTOS INTERNOS REGION NOR-ORIENTAL', 'region_id' => $regionNorOriental];
            $dependencies[] = ['name' => 'DIVISIÓN DE RECAUDACIÓN', 'region_id' => $regionNorOriental];
            $dependencies[] = ['name' => 'SECTOR CUMANA', 'region_id' => $regionNorOriental];
            $dependencies[] = ['name' => 'DIVISIÓN DE ASISTENCIA AL CONTRIBUYENTE', 'region_id' => $regionNorOriental];
            $dependencies[] = ['name' => 'DIVISIÓN DE FISCALIZACIÓN', 'region_id' => $regionNorOriental];
            $dependencies[] = ['name' => 'DIVISIÓN JURÍDICO TRIBUTARIA', 'region_id' => $regionNorOriental];
        }

        // REGION GUAYANA
        $regionGuayana = $regions['Region Guayana'] ?? null;
        if ($regionGuayana) {
            $dependencies[] = ['name' => 'DIVISION CONTRIBUYENTES ESPECIALES (REG. GUAYANA)', 'region_id' => $regionGuayana];
            $dependencies[] = ['name' => 'DIVISION DE TRAMITACIONES', 'region_id' => $regionGuayana];
            $dependencies[] = ['name' => 'GERENCIA REGIONAL DE TRIBUTOS INTERNOS REGIÓN GUAYANA', 'region_id' => $regionGuayana];
            $dependencies[] = ['name' => 'DIVISIÓN DE ADMINISTRACIÓN', 'region_id' => $regionGuayana];
            $dependencies[] = ['name' => 'DIVISIÓN DE ASISTENCIA AL CONTRIBUYENTE', 'region_id' => $regionGuayana];
            $dependencies[] = ['name' => 'DIVISIÓN DE FISCALIZACIÓN', 'region_id' => $regionGuayana];
            $dependencies[] = ['name' => 'DIVISIÓN JURÍDICO TRIBUTARIA', 'region_id' => $regionGuayana];
            $dependencies[] = ['name' => 'DIVISIÓN DE RECAUDACIÓN', 'region_id' => $regionGuayana];
            $dependencies[] = ['name' => 'DIVISIÓN DE SUMARIO ADMINISTRATIVO', 'region_id' => $regionGuayana];
            $dependencies[] = ['name' => 'SECTOR PUERTO ORDAZ', 'region_id' => $regionGuayana];
            $dependencies[] = ['name' => 'SECTOR TUCUPITA', 'region_id' => $regionGuayana];
            $dependencies[] = ['name' => 'UNIDAD UPATA', 'region_id' => $regionGuayana];
            $dependencies[] = ['name' => 'UNIDAD CAICARA DEL ORINOCO', 'region_id' => $regionGuayana];
            $dependencies[] = ['name' => 'SECTOR PUERTO AYACUCHO', 'region_id' => $regionGuayana];
            $dependencies[] = ['name' => 'ADUANA PRINCIPAL DE CIUDAD GUAYANA', 'region_id' => $regionGuayana];
            $dependencies[] = ['name' => 'DIV DE COBRO EJECUT Y MEDID CAUTEL (REG.GUAYANA)', 'region_id' => $regionGuayana];
            $dependencies[] = ['name' => 'SEDE REGIONAL DE TRIBUTOS INTERNOS REGION GUAYANA', 'region_id' => $regionGuayana];
            $dependencies[] = ['name' => 'UNIDAD SANTA ELENA DE UAIREN', 'region_id' => $regionGuayana];
            $dependencies[] = ['name' => 'UNIDAD DE CONTRIBUYENTES ESPECIALES PUERTO ORDAZ', 'region_id' => $regionGuayana];
            $dependencies[] = ['name' => 'SECTOR SAN FELIX', 'region_id' => $regionGuayana];
        }

        // REGION INSULAR
        $regionInsular = $regions['Region Insular'] ?? null;
        if ($regionInsular) {
            $dependencies[] = ['name' => 'GERENCIA REGIONAL DE TRIBUTOS INTERNOS REGIÓN INSULAR', 'region_id' => $regionInsular];
            $dependencies[] = ['name' => 'DIVISION DE RECAUDACION (REG.INSULAR)', 'region_id' => $regionInsular];
            $dependencies[] = ['name' => 'DIVISIÓN DE SUMARIO ADMINISTRATIVO', 'region_id' => $regionInsular];
            $dependencies[] = ['name' => 'DIVISION DE TRAMITACIONES (REGION INSULAR)', 'region_id' => $regionInsular];
            $dependencies[] = ['name' => 'DIVISIÓN DE ADMINISTRACIÓN', 'region_id' => $regionInsular];
            $dependencies[] = ['name' => 'DIVISIÓN DE ASISTENCIA AL CONTRIBUYENTE', 'region_id' => $regionInsular];
            $dependencies[] = ['name' => 'DIVISIÓN DE FISCALIZACIÓN', 'region_id' => $regionInsular];
            $dependencies[] = ['name' => 'DIVISIÓN JURÍDICO TRIBUTARIA', 'region_id' => $regionInsular];
            $dependencies[] = ['name' => 'SEDE REGIONAL DE TRIBUTOS INTERNOS REGION INSULAR', 'region_id' => $regionInsular];
            $dependencies[] = ['name' => 'DIVISION DE CONTRIBUYENTES ESPECIALES (REGION INSULAR NUEVA ESPARTA)', 'region_id' => $regionInsular];
        }

        // REGION CENTRAL
        $regionCentral = $regions['Region Central'] ?? null;
        if ($regionCentral) {
            $dependencies[] = ['name' => 'UNIDAD LA VICTORIA', 'region_id' => $regionCentral];
            $dependencies[] = ['name' => 'DIVISIÓN DE ADMINISTRACIÓN', 'region_id' => $regionCentral];
            $dependencies[] = ['name' => 'DIVISIÓN JURÍDICO TRIBUTARIA', 'region_id' => $regionCentral];
            $dependencies[] = ['name' => 'DIVISIÓN DE TRAMITACIONES--REGION CENTRAL', 'region_id' => $regionCentral];
            $dependencies[] = ['name' => 'UNIDAD PUERTO CABELLO', 'region_id' => $regionCentral];
            $dependencies[] = ['name' => 'UNIDAD BEJUMA', 'region_id' => $regionCentral];
            $dependencies[] = ['name' => 'SECTOR CAGUA', 'region_id' => $regionCentral];
            $dependencies[] = ['name' => 'UNIDAD DE CONTRIBUYENTES ESPECIALES (MARACAY)', 'region_id' => $regionCentral];
            $dependencies[] = ['name' => 'UNIDAD SAN CARLOS', 'region_id' => $regionCentral];
            $dependencies[] = ['name' => 'GERENCIA REGIONAL DE TRIBUTOS INTERNOS REGIÓN CENTRAL', 'region_id' => $regionCentral];
            $dependencies[] = ['name' => 'UNIDAD DE CONTRIBUYENTES ESPECIALES (PUERTO CABELLO)', 'region_id' => $regionCentral];
            $dependencies[] = ['name' => 'UNIDAD DE CONTRIBUYENTES ESPECIALES (SAN CARLOS)', 'region_id' => $regionCentral];
            $dependencies[] = ['name' => 'UNIDAD DE CONTRIBUYENTES ESPECIALES (BEJUMA)', 'region_id' => $regionCentral];
            $dependencies[] = ['name' => 'UNIDAD DE CONTRIBUYENTES ESPECIALES (CAGUA)', 'region_id' => $regionCentral];
            $dependencies[] = ['name' => 'UNIDAD DE CONTRIBUYENTES ESPECIALES (LA VICTORIA)', 'region_id' => $regionCentral];
            $dependencies[] = ['name' => 'SECTOR MARACAY', 'region_id' => $regionCentral];
            $dependencies[] = ['name' => 'SEDE REGIONAL DE TRIBUTOS INTERNOS REGION CENTRAL', 'region_id' => $regionCentral];
            $dependencies[] = ['name' => 'DIVISIÓN DE ASISTENCIA AL CONTRIBUYENTE', 'region_id' => $regionCentral];
            $dependencies[] = ['name' => 'DIVISIÓN DE FISCALIZACIÓN (REG CENTRAL)', 'region_id' => $regionCentral];
            $dependencies[] = ['name' => 'DIVISIÓN DE CONTRIBUYENTES ESPECIALES (VALENCIA)', 'region_id' => $regionCentral];
            $dependencies[] = ['name' => 'DIVISIÓN DE COBRO EJECUTIVO Y MEDIDAS CAUTELARES', 'region_id' => $regionCentral];
            $dependencies[] = ['name' => 'DIVISIÓN DE RECAUDACIÓN   (REGION CENTRAL)', 'region_id' => $regionCentral];
            $dependencies[] = ['name' => 'DIVISIÓN DE SUMARIO ADMINISTRATIVO', 'region_id' => $regionCentral];
        }

        // INFORMACION NO DISPONIBLE
        $regionInfoNoDisp = $regions['Informacion No Disponible'] ?? null;
        if ($regionInfoNoDisp) {
            $dependencies[] = ['name' => 'INTERNET / NIVEL NACIONAL', 'region_id' => $regionInfoNoDisp];
        }

        // REGION DE CONTRIBUYENTES ESPECIALES
        $regionContribEspeciales = $regions['Region De Contribuyentes Especiales'] ?? null;
        if ($regionContribEspeciales) {
            $dependencies[] = ['name' => 'GERENCIA REGIONAL DE CONTRIBUYENTES ESPECIALES REGIÓN CAPITAL', 'region_id' => $regionContribEspeciales];
            $dependencies[] = ['name' => 'DIVISION FISCALIZACION DE MINAS E HIDROCARBUROS', 'region_id' => $regionContribEspeciales];
            $dependencies[] = ['name' => 'DIVISION FISCALIZACION DE MINAS E HIDROCARBUROS/COORD. MINAS Y ACTIVIDADES CONEX', 'region_id' => $regionContribEspeciales];
            $dependencies[] = ['name' => 'DIVISIÓN DE ADMINISTRACIÓN', 'region_id' => $regionContribEspeciales];
            $dependencies[] = ['name' => 'DIVISIÓN DE ASISTENCIA AL CONTRIBUYENTE', 'region_id' => $regionContribEspeciales];
            $dependencies[] = ['name' => 'DIVISIÓN DE SUMARIO ADMINISTRATIVO', 'region_id' => $regionContribEspeciales];
            $dependencies[] = ['name' => 'DIVISIÓN JURÍDICO TRIBUTARIA', 'region_id' => $regionContribEspeciales];
            $dependencies[] = ['name' => 'DIVISIÓN DE RECAUDACIÓN', 'region_id' => $regionContribEspeciales];
            $dependencies[] = ['name' => 'DIVISIÓN DE TRAMITACIONES', 'region_id' => $regionContribEspeciales];
            $dependencies[] = ['name' => 'DIVISIÓN DE FISCALIZACIÓN', 'region_id' => $regionContribEspeciales];
        }

        // REGION DE LA UNIDAD DE COBROS Y RECUPERACIONES DE ADUANAS
        $regionCobrosAduanas = $regions['Region De La Unidad De Cobros y Recuperaciones De Aduanas'] ?? null;
        if ($regionCobrosAduanas) {
            $dependencies[] = ['name' => 'UNIDAD DE COBROS Y RECUPERACIONES', 'region_id' => $regionCobrosAduanas];
        }

        // REGION LIBERTADOR
        $regionLibertador = $regions['Region Libertador'] ?? null;
        if ($regionLibertador) {
            $dependencies[] = ['name' => 'UNIDAD CARAYACA', 'region_id' => $regionLibertador];
            $dependencies[] = ['name' => 'GERENCIA REGIONAL DE TRIBUTOS INTERNOS LIBERTADOR', 'region_id' => $regionLibertador];
            $dependencies[] = ['name' => 'SEDE REGIONAL DE TRIBUTOS INTERNOS LIBERTADOR', 'region_id' => $regionLibertador];
            $dependencies[] = ['name' => 'DIVISIÓN DE ADMINISTRACIÓN', 'region_id' => $regionLibertador];
            $dependencies[] = ['name' => 'SECTOR LA GUAIRA', 'region_id' => $regionLibertador];
            $dependencies[] = ['name' => 'UNIDAD DE CONTRIBUYENTES ESPECIALES LA GUAIRA', 'region_id' => $regionLibertador];
            $dependencies[] = ['name' => 'DIVISIÓN DE ASISTENCIA AL CONTRIBUYENTE', 'region_id' => $regionLibertador];
            $dependencies[] = ['name' => 'DIVISIÓN DE FISCALIZACIÓN (LIBERTADOR)', 'region_id' => $regionLibertador];
            $dependencies[] = ['name' => 'DIVISIÓN JURÍDICO TRIBUTARIA', 'region_id' => $regionLibertador];
            $dependencies[] = ['name' => 'DIVISIÓN DE RECAUDACIÓN (LIBERTADOR)', 'region_id' => $regionLibertador];
            $dependencies[] = ['name' => 'DIVISIÓN DE SUMARIO ADMINISTRATIVO', 'region_id' => $regionLibertador];
            $dependencies[] = ['name' => 'DIVISIÓN DE TRAMITACIONES -  LIBERTADOR', 'region_id' => $regionLibertador];
            $dependencies[] = ['name' => 'DIVISIÓN DE COBRO EJECUTIVO Y MEDIDAS CAUTELARES', 'region_id' => $regionLibertador];
            $dependencies[] = ['name' => 'DIVISIÓN DE CONTRIBUYENTES ESPECIALES LIBERTADOR', 'region_id' => $regionLibertador];
            $dependencies[] = ['name' => 'CENTRO AUXILIAR NRO. 2 PROPATRIA', 'region_id' => $regionLibertador];
            $dependencies[] = ['name' => 'UNIDAD COLONIA TOVAR', 'region_id' => $regionLibertador];
            $dependencies[] = ['name' => 'CENTRO AUXILIAR TORRE SENIAT- GRTI LIBERTADOR', 'region_id' => $regionLibertador];
            $dependencies[] = ['name' => 'UNIDAD CONTRIBUYENTES ESPECIALES (CARAYACA)', 'region_id' => $regionLibertador];
            $dependencies[] = ['name' => 'UNIDAD DE CONTRIBUYENTES ESPECIALES COLONIA TOVAR', 'region_id' => $regionLibertador];
        }

        // REGION FALCON
        $regionFalcon = $regions['Region Falcón'] ?? null;
        if ($regionFalcon) {
            $dependencies[] = ['name' => 'GERENCIA REGIONAL DE TRIBUTOS INTERNOS FALCÓN', 'region_id' => $regionFalcon];
            $dependencies[] = ['name' => 'SEDE REGIONAL DE TRIBUTOS INTERNOS FALCÓN', 'region_id' => $regionFalcon];
            $dependencies[] = ['name' => 'DIVISIÓN DE ADMINISTRACIÓN', 'region_id' => $regionFalcon];
            $dependencies[] = ['name' => 'SECTOR PUNTO FIJO', 'region_id' => $regionFalcon];
            $dependencies[] = ['name' => 'DIVISIÓN DE ASISTENCIA AL CONTRIBUYENTE', 'region_id' => $regionFalcon];
            $dependencies[] = ['name' => 'DIVISIÓN DE FISCALIZACIÓN FALCÓN', 'region_id' => $regionFalcon];
            $dependencies[] = ['name' => 'DIVISIÓN JURÍDICO TRIBUTARIA', 'region_id' => $regionFalcon];
            $dependencies[] = ['name' => 'DIVISIÓN DE RECAUDACIÓN', 'region_id' => $regionFalcon];
            $dependencies[] = ['name' => 'DIVISIÓN DE SUMARIO ADMINISTRATIVO', 'region_id' => $regionFalcon];
            $dependencies[] = ['name' => 'DIVISIÓN DE TRAMITACIONES -  FALCÓN', 'region_id' => $regionFalcon];
            $dependencies[] = ['name' => 'SECTOR TUCACAS', 'region_id' => $regionFalcon];
            $dependencies[] = ['name' => 'DIVISIÓN DE COBRO EJECUTIVO Y MEDIDAS CAUTELARES', 'region_id' => $regionFalcon];
            $dependencies[] = ['name' => 'DIVISIÓN DE CONTRIBUYENTES ESPECIALES FALCÓN', 'region_id' => $regionFalcon];
            $dependencies[] = ['name' => 'UNIDAD CHURUGUARA', 'region_id' => $regionFalcon];
            $dependencies[] = ['name' => 'UNIDAD DE CONTRIBUYENTES ESPECIALES (TUCACAS)', 'region_id' => $regionFalcon];
            $dependencies[] = ['name' => 'UNIDAD DE CONTRIBUYENTES ESPECIALES (CHURUGUARA)', 'region_id' => $regionFalcon];
            $dependencies[] = ['name' => 'UNIDAD DE CONTRIBUYENTES ESPECIALES (PUNTO FIJO)', 'region_id' => $regionFalcon];
        }

        // Agregar timestamps a todas las dependencias
        foreach ($dependencies as &$dependency) {
            $dependency['created_at'] = now();
            $dependency['updated_at'] = now();
        }
        
        // Insertar todas las dependencias
        Dependency::insert($dependencies);
    }
}