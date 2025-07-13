<?php

namespace App\Core\States;

use App\Core\States\Interface\StateHandlerInterface;
use App\Enums\ConversationStateEnum;
use App\Models\Application;
use App\Models\User;
use App\Services\Whatsapp\EvolutionApiService;
use Illuminate\Support\Str;

class ApplicationDeleteStateHandler extends BaseStateHandler implements StateHandlerInterface
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

        if (isset($context['application_id_to_delete'])) {
            $applicationId = $context['application_id_to_delete'];
            if (Str::lower($option) === 'sim') {
                $application = Application::find($applicationId);
                if ($application && $user->can('delete', $application)) {
                    $application->delete();
                    $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_deleted_success'));
                } else {
                    $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_not_found'));
                }
            } else {
                $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_delete_cancelled'));
            }

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
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_list_delete_prompt'));

            return;
        }

        $applicationId = $context['application_ids'][$option];
        $application = Application::find($applicationId);

        if (! $application) {
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_not_found'));
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.main_menu'));
            $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);
            $user->update(['context' => null]);

            return;
        }

        $context['application_id_to_delete'] = $applicationId;
        $user->update(['context' => $context]);

        $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_delete_confirm', [
            'job_title' => $application->job_title,
            'company_name' => $application->company_name ?? 'N/A',
        ]));
    }
}
