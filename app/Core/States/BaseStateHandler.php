<?php

namespace App\Core\States;

use App\Enums\ConversationStateEnum;
use App\Models\User;

abstract class BaseStateHandler
{
    protected function updateConversationState(User $user, ConversationStateEnum $state): void
    {
        $user->update(['conversation_state' => $state]);
    }
}
