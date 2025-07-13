<?php

namespace App\Core\States;

use App\Core\States\Interface\StateHandlerInterface;
use App\Enums\ConversationStateEnum;
use App\Models\User;
use App\Services\Whatsapp\EvolutionApiService;

class IdleStateHandler extends BaseStateHandler implements StateHandlerInterface
{
    public function __construct(
        private readonly EvolutionApiService $evolutionApiService,
    ) {}

    public function handle(User $user, string $message): void
    {
        $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.welcome_new_user', ['name' => $user->name]));
        $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.main_menu'));
        $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);
    }
}
