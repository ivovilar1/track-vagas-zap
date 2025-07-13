<?php

namespace App\Core\States\Interface;

use App\Models\User;

interface StateHandlerInterface
{
    public function handle(User $user, string $message): void;
}