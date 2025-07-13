<?php

namespace App\Core;

use App\Core\States\Interface\StateHandlerInterface;
use App\Enums\ConversationStateEnum;
use App\Core\States;

class StateHandlerFactory
{
    public function make(string $state): StateHandlerInterface
    {
        return match ($state) {
            ConversationStateEnum::IDLE => app(States\IdleStateHandler::class),
            ConversationStateEnum::MAIN_MENU => app(States\MainMenuStateHandler::class),
            ConversationStateEnum::APPLICATION_CREATE => app(States\ApplicationCreateStateHandler::class),
            ConversationStateEnum::APPLICATION_LIST => app(States\ApplicationListStateHandler::class),
            ConversationStateEnum::APPLICATION_UPDATE => app(States\ApplicationUpdateStateHandler::class),
            ConversationStateEnum::APPLICATION_DELETE => app(States\ApplicationDeleteStateHandler::class),
        };
    }
}