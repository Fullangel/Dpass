<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\VisitDestination;
use App\Models\VisitDestinationRule;
use App\Services\VisitDestinationService;
use Illuminate\Database\Seeder;

class VisitDestinationSeeder extends Seeder
{
    public function run(): void
    {
        $destination = VisitDestination::query()->updateOrCreate(
            ['slug' => 'atencion-contribuyente-mata-coco'],
            [
                'name' => 'Atencion Contribuyente - Piso 4',
                'headquarters_id' => 1,
                'status' => Status::ACTIVE,
                'sort_order' => 1,
            ]
        );

        VisitDestinationRule::query()->updateOrCreate(
            [
                'visit_destination_id' => $destination->id,
                'rule_type' => VisitDestinationRule::TYPE_EMPLOYEE,
                'rule_value' => '99',
            ],
            [
                'status' => Status::ACTIVE,
            ]
        );

        VisitDestinationService::clearRulesCache();

        $this->command?->info('Destino configurado: ' . $destination->name . ' (employee_id=99)');
    }
}
