<?php

namespace App\Policies;

use App\Models\Apliccation;
use App\Models\User;

class ApliccationPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Apliccation $apliccation): bool
    {
        return $user->id === $apliccation->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Apliccation $apliccation): bool
    {
        return $user->id === $apliccation->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Apliccation $apliccation): bool
    {
        return $user->id === $apliccation->user_id;
    }
}
