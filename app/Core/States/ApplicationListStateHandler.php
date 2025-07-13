<?php

namespace App\Core\States;

use App\Core\States\Interface\StateHandlerInterface;
use App\Enums\ConversationStateEnum;
use App\Models\Application;
use App\Models\User;
use App\Services\Whatsapp\EvolutionApiService;
use Illuminate\Support\Str;

class ApplicationListStateHandler extends BaseStateHandler implements StateHandlerInterface
{
    public function __construct(
        private readonly EvolutionApiService $evolutionApiService,
    ) {}

    public function handle(User $user, string $message): void
    {
        $context = $user->context ?? [];
        $option = trim($message);

        if (Str::lower($option) === 'cancelar') {
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.main_menu'));
            $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);
            $user->update(['context' => null]);

            return;
        }

        if (! isset($context['application_ids'])) {
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.error_try_again'));
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.main_menu'));
            $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);
            $user->update(['context' => null]);

            return;
        }

        if (! is_numeric($option) || ! isset($context['application_ids'][$option])) {
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.invalid_option'));
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_list_prompt'));

            return;
        }

        $applicationId = $context['application_ids'][$option];
        $application = Application::query()->find($applicationId);

        if (! $application) {
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_not_found'));
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.main_menu'));
            $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);
            $user->update(['context' => null]);

            return;
        }

        $user->update([
            'context' => ['application_id_to_update' => $applicationId],
        ]);

        $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_update_menu', [
            'job_title' => $application->job_title,
            'company_name' => $application->company_name ?? 'N/A',
        ]));
        $this->updateConversationState($user, ConversationStateEnum::APPLICATION_UPDATE);
    }
}
