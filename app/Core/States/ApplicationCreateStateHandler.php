<?php

namespace App\Core\States;

use App\Core\States\Interface\StateHandlerInterface;
use App\Enums\ConversationStateEnum;
use App\Models\Application;
use App\Models\User;
use App\Services\Whatsapp\EvolutionApiService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApplicationCreateStateHandler extends BaseStateHandler implements StateHandlerInterface
{
    private array $script;

    public function __construct(
        private readonly EvolutionApiService $evolutionApiService,
    ) {
        $this->script = [
            [
                'field' => 'company_name',
                'prompt' => 'bot_messages.application_create_start',
                'rules' => 'nullable|string|max:255',
            ],
            [
                'field' => 'job_title',
                'prompt' => 'bot_messages.application_create_job_title',
                'rules' => 'required|string|max:255',
                'messages' => ['required' => __('bot_messages.application_create_job_title_required')],
            ],
            [
                'field' => 'job_description',
                'prompt' => 'bot_messages.application_create_job_description',
                'rules' => 'nullable|string|max:255',
            ],
            [
                'field' => 'job_salary',
                'prompt' => 'bot_messages.application_create_job_salary',
                'rules' => 'nullable|numeric|min:0',
            ],
            [
                'field' => 'job_link',
                'prompt' => 'bot_messages.application_create_job_link',
                'rules' => 'nullable|url|max:255',
            ],
        ];
    }

    public function handle(User $user, string $message): void
    {
        $context = $user->context ?? [];
        $currentStep = $this->getCurrentStep($context);

        if ($currentStep === null) {
            $this->createApplication($user, $context);
            return;
        }

        if ($currentStep['field'] !== 'company_name') {
            $value = Str::lower($message) === 'pular' ? null : $message;
        } else {
            $value = $message;
        }

        $validator = Validator::make([$currentStep['field'] => $value], [$currentStep['field'] => $currentStep['rules']], $currentStep['messages'] ?? []);

        if ($validator->fails()) {
            $this->evolutionApiService->sendTextMessage($user->phone, $validator->errors()->first());
            $this->evolutionApiService->sendTextMessage($user->phone, __($currentStep['prompt']));
            return;
        }
        
        $context[$currentStep['field']] = $value;
        $user->update(['context' => $context]);

        $nextStep = $this->getCurrentStep($context);
        if ($nextStep) {
            $this->evolutionApiService->sendTextMessage($user->phone, __($nextStep['prompt']));
        } else {
            $this->createApplication($user, $context);
        }
    }

    private function getCurrentStep(array $context): ?array
    {
        foreach ($this->script as $step) {
            if (! array_key_exists($step['field'], $context)) {
                return $step;
            }
        }
        return null;
    }

    private function createApplication(User $user, array $context): void
    {
        $context['user_id'] = $user->id;
        $context['application_date'] = now()->format('Y-m-d');
        
        Application::query()->create($context);

        $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_create_success'));
        $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.main_menu'));
        $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);
        $user->update(['context' => null]);
    }
}
