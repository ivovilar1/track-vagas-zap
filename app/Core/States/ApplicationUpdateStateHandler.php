<?php

namespace App\Core\States;

use App\Core\States\Interface\StateHandlerInterface;
use App\Enums\ConversationStateEnum;
use App\Models\Application;
use App\Models\User;
use App\Services\Whatsapp\EvolutionApiService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApplicationUpdateStateHandler extends BaseStateHandler implements StateHandlerInterface
{
    public function __construct(
        private readonly EvolutionApiService $evolutionApiService,
    ) {}

    public function handle(User $user, string $message): void
    {
        $context = $user->context ?? [];
        $applicationId = $context['application_id_to_update'] ?? null;

        if (! $applicationId) {
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.error_try_again'));
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.main_menu'));
            $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);

            return;
        }

        $application = Application::query()->find($applicationId);
        if (! $application) {
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_not_found'));
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.main_menu'));
            $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);
            $user->update(['context' => null]);

            return;
        }

        if (! isset($context['field_to_edit'])) {
            $option = trim($message);
            $fieldMap = [
                '1' => 'company_name',
                '2' => 'job_title',
                '3' => 'job_description',
                '4' => 'job_salary',
                '5' => 'job_link',
            ];

            if ($option === '6' || Str::lower($message) === 'cancelar') {
                $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.main_menu'));
                $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);
                $user->update(['context' => null]);

                return;
            }

            if (! isset($fieldMap[$option])) {
                $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.invalid_option'));
                $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_update_menu', [
                    'job_title' => $application->job_title,
                    'company_name' => $application->company_name ?? 'N/A',
                ]));

                return;
            }

            $fieldToEdit = $fieldMap[$option];
            $context['field_to_edit'] = $fieldToEdit;
            $user->update(['context' => $context]);

            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_update_prompt_new_value', ['field' => __('bot_messages.application_fields.'.$fieldToEdit)]));

            return;
        }

        $fieldToEdit = $context['field_to_edit'];
        $value = Str::lower($message) === 'pular' ? null : $message;
        $rules = [
            'company_name' => 'nullable|string|max:255',
            'job_title' => 'required|string|max:255',
            'job_description' => 'nullable|string|max:255',
            'job_salary' => 'nullable|numeric|min:0',
            'job_link' => 'nullable|url|max:255',
        ];

        $validator = Validator::make([$fieldToEdit => $value], [$fieldToEdit => $rules[$fieldToEdit]]);

        if ($validator->fails()) {
            $this->evolutionApiService->sendTextMessage($user->phone, $validator->errors()->first());
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_update_prompt_new_value', ['field' => __('bot_messages.application_fields.'.$fieldToEdit)]));

            return;
        }

        $application->update([$fieldToEdit => $value]);

        $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_updated_success'));
        $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.main_menu'));
        $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);
        $user->update(['context' => null]);
    }
}
