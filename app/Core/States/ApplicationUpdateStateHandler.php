<?php

namespace App\Core\States;

use App\Core\States\Interface\StateHandlerInterface;
use App\Models\User;
use App\Services\Whatsapp\EvolutionApiService;

class ApplicationUpdateStateHandler implements StateHandlerInterface
{
    public function __construct(
        private readonly EvolutionApiService $evolutionApiService,
    ) {}

    public function handle(User $user, string $message): void
    {
        // TODO: Implement handle() method.
    }
}