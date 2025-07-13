<?php

namespace App\Core\States;

use App\Core\States\Interface\StateHandlerInterface;
use App\Enums\ConversationStateEnum;
use App\Models\Apliccation;
use App\Models\User;
use App\Services\Whatsapp\EvolutionApiService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class ApplicationCreateStateHandler implements StateHandlerInterface
{
    public function __construct(
        private readonly EvolutionApiService $evolutionApiService,
    ) {}

    public function handle(User $user, string $message): void
    {
        $context = $user->context ?? [];
        $value = Str::lower($message) === 'pular' ? null : $message;

        if (! array_key_exists('company_name', $context)) {
            $validator = Validator::make(['company_name' => $value], ['company_name' => 'nullable|string|max:255']);
            if ($validator->fails()) {
                $this->evolutionApiService->sendTextMessage($user->phone, $validator->errors()->first());
                $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_create_start'));
                return;
            }
            $context['company_name'] = $value;
            $user->update(['context' => $context]);
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_create_job_title'));
            return;
        }

        if (! array_key_exists('job_title', $context)) {
            $validator = Validator::make(['job_title' => $value], ['job_title' => 'required|string|max:255'], [
                'required' => __('bot_messages.application_create_job_title_required'),
            ]);
            if ($validator->fails()) {
                $this->evolutionApiService->sendTextMessage($user->phone, $validator->errors()->first());
                $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_create_job_title'));
                return;
            }
            $context['job_title'] = $value;
            $user->update(['context' => $context]);
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_create_job_description'));
            return;
        }

        if (! array_key_exists('job_description', $context)) {
            $validator = Validator::make(['job_description' => $value], ['job_description' => 'nullable|string|max:255']);
            if ($validator->fails()) {
                $this->evolutionApiService->sendTextMessage($user->phone, $validator->errors()->first());
                $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_create_job_description'));
                return;
            }
            $context['job_description'] = $value;
            $user->update(['context' => $context]);
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_create_job_salary'));
            return;
        }

        if (! array_key_exists('job_salary', $context)) {
            $validator = Validator::make(['job_salary' => $value], ['job_salary' => 'nullable|numeric|min:0']);
            if ($validator->fails()) {
                $this->evolutionApiService->sendTextMessage($user->phone, $validator->errors()->first());
                $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_create_job_salary'));
                return;
            }
            $context['job_salary'] = $value;
            $user->update(['context' => $context]);
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_create_job_link'));
            return;
        }

        if (! array_key_exists('job_link', $context)) {
            $validator = Validator::make(['job_link' => $value], ['job_link' => 'nullable|url|max:255']);
            if ($validator->fails()) {
                $this->evolutionApiService->sendTextMessage($user->phone, $validator->errors()->first());
                $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_create_job_link'));
                return;
            }
            $context['job_link'] = $value;

            Apliccation::query()->create([
                'user_id' => $user->id,
                'company_name' => $context['company_name'],
                'job_title' => $context['job_title'],
                'job_description' => $context['job_description'],
                'job_salary' => $context['job_salary'],
                'job_link' => $context['job_link'],
                'application_date' => now()->format('Y-m-d'),
            ]);

            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_create_success'));
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.main_menu'));
            $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);
            $user->update(['context' => null]);
            return;
        }
    }

    private function updateConversationState(User $user, ConversationStateEnum $state): void
    {
        $user->update(['conversation_state' => $state]);
    }
}