<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\VisitDestination;
use Illuminate\Console\Command;

class AssignUserVisitDestination extends Command
{
    protected $signature = 'visit-destinations:assign-user
                            {user : ID o email del usuario}
                            {destination : slug o ID del destino}
                            {--remove : Quitar la asignacion en lugar de agregarla}';

    protected $description = 'Asigna o quita un destino de visita a un usuario de recepcion secundaria';

    public function handle(): int
    {
        $userInput = (string) $this->argument('user');
        $destinationInput = (string) $this->argument('destination');
        $remove = (bool) $this->option('remove');

        $user = is_numeric($userInput)
            ? User::query()->find((int) $userInput)
            : User::query()->where('email', $userInput)->first();

        if (! $user) {
            $this->error('Usuario no encontrado.');
            return self::FAILURE;
        }

        $destination = is_numeric($destinationInput)
            ? VisitDestination::query()->find((int) $destinationInput)
            : VisitDestination::query()->where('slug', $destinationInput)->first();

        if (! $destination) {
            $this->error('Destino no encontrado.');
            return self::FAILURE;
        }

        if ($remove) {
            $user->visitDestinations()->detach($destination->id);
            $this->info("Destino '{$destination->name}' removido de {$user->name}.");
            return self::SUCCESS;
        }

        $user->visitDestinations()->syncWithoutDetaching([$destination->id]);
        $this->info("Destino '{$destination->name}' asignado a {$user->name} (ID {$user->id}).");

        return self::SUCCESS;
    }
}
