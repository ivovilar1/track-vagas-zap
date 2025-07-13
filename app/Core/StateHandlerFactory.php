<?php

namespace App\Core;

use App\Core\States\Interface\StateHandlerInterface;
use App\Enums\ConversationStateEnum;

class StateHandlerFactory
{
    public function make(string $state): StateHandlerInterface
    {
        return match ($state) {
            ConversationStateEnum::IDLE->value => app(States\IdleStateHandler::class),
            ConversationStateEnum::MAIN_MENU->value => app(States\MainMenuStateHandler::class),
            ConversationStateEnum::APPLICATION_CREATE->value => app(States\ApplicationCreateStateHandler::class),
            ConversationStateEnum::APPLICATION_LIST->value => app(States\ApplicationListStateHandler::class),
            ConversationStateEnum::APPLICATION_UPDATE->value => app(States\ApplicationUpdateStateHandler::class),
            ConversationStateEnum::APPLICATION_DELETE->value => app(States\ApplicationDeleteStateHandler::class),
        };
    }
}
