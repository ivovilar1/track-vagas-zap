<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApplicationPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Application $application): bool
    {
        return $user->id === $application->user_id;
    }

    public function update(User $user, Application $application): bool
    {
        return $user->id === $application->user_id;
    }

    public function delete(User $user, Application $application): bool
    {
        return $user->id === $application->user_id;
    }
}
