<?php

namespace App\Http\Controllers\WhatsappMessage;

use App\Enums\ConversationStateEnum;
use App\Http\Controllers\Controller;
use App\Models\Apliccation;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Received extends Controller
{
    public function __invoke(Request $request): void
    {
        $data = $request->all();
        $from = strstr($data['data']['key']['remoteJid'], '@', true);
        $message = $data['data']['message']['conversation'] ?? $data['data']['message']['extendedTextMessage']['text'];
        
        $user = User::query()->where('phone', $from)->first();
        if (!$user) {
            $user = User::create([
                'phone' => $from,
                'name' => $data['data']['pushName'],
                'conversation_state' => ConversationStateEnum::IDLE,
            ]);
        }
        $this->handleMessage($user, $message);
    }

    private function sendMessage(string $from, string $message): void
    {
        try {
            $response = Http::withHeaders([
                'apikey' => config('services.evolution.instance_token'),
            ])->post(config('services.evolution.server_url') . '/message/sendText/' . config('services.evolution.instance_name'), [
                'number' => $from,
                'text' => $message,
            ]);
            
            if (!$response->successful()) {
                Log::error('Failed to send WhatsApp message', [
                    'phone' => $from,
                    'response' => $response->body()
                ]);
            }
        } catch (Exception $e) {
            Log::error('WhatsApp API error', [
                'phone' => $from,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function handleMessage(User $user, string $message): void
    {
        $messageLower = strtolower(trim($message));

        if (in_array($messageLower, ['cancelar', 'sair', 'menu'])) {
            $this->sendMessage($user->phone, __('bot_messages.application_handle_cancel'));
            $this->sendMessage($user->phone, __('bot_messages.main_menu'));
            $user->update([
                'conversation_state' => ConversationStateEnum::MAIN_MENU,
                'context' => []
            ]);
            return;
        }
        match ($user->conversation_state) {
            ConversationStateEnum::IDLE => $this->handleIdleState($user, $message),
            ConversationStateEnum::MAIN_MENU => $this->handleMainMenuState($user, $message),
            ConversationStateEnum::APPLICATION_LIST => $this->handleApplicationListState($user, $message),
            ConversationStateEnum::APPLICATION_CREATE => $this->handleApplicationCreateState($user, $message),
            ConversationStateEnum::APPLICATION_UPDATE => $this->handleApplicationUpdateState($user, $message),
            ConversationStateEnum::APPLICATION_DELETE => $this->handleApplicationDeleteState($user, $message),
        };
    }

    private function handleIdleState(User $user, string $message): void
    {
        $this->sendMessage($user->phone, __('bot_messages.welcome_new_user', ['name' => $user->name]));
        $this->sendMessage($user->phone, __('bot_messages.main_menu'));
        $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);
    }

    private function handleMainMenuState(User $user, string $message): void
    {
        $option = trim($message);

        switch ($option) {
            case '1':
                $this->sendApplicationList($user);
                break;
            case '2':
                $this->sendMessage($user->phone, __('bot_messages.application_create_start'));
                $this->updateConversationState($user, ConversationStateEnum::APPLICATION_CREATE);
                break;
            case '3':
                $this->sendMessage($user->phone, __('bot_messages.application_delete_start'));
                $this->updateConversationState($user, ConversationStateEnum::APPLICATION_DELETE);
                break;
            case '4':
                $this->sendMessage($user->phone, __('bot_messages.application_end_conversation'));
                $this->updateConversationState($user, ConversationStateEnum::IDLE);
                break;
            default:
                $this->sendMessage($user->phone, __('bot_messages.invalid_option'));
                $this->sendMessage($user->phone, __('bot_messages.main_menu'));
                $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);
                break;
        }
    }

    private function handleApplicationListState(User $user, string $message): void
    {
        $context = $user->context ?? [];
        $option = trim($message);

        if (Str::lower($option) === 'cancelar') {
            $this->sendMessage($user->phone, __('bot_messages.main_menu'));
            $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);
            $user->update(['context' => null]);
            return;
        }

        if (!isset($context['application_ids'])) {
            $this->sendMessage($user->phone, __('bot_messages.error_try_again'));
            $this->sendMessage($user->phone, __('bot_messages.main_menu'));
            $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);
            $user->update(['context' => null]);
            return;
        }

        if (! is_numeric($option) || ! isset($context['application_ids'][$option])) {
            $this->sendMessage($user->phone, __('bot_messages.invalid_option'));
            $this->sendMessage($user->phone, __('bot_messages.application_list_prompt'));
            return;
        }

        $applicationId = $context['application_ids'][$option];
        $application = Apliccation::find($applicationId);

        if (!$application) {
            $this->sendMessage($user->phone, __('bot_messages.application_not_found'));
            $this->sendMessage($user->phone, __('bot_messages.main_menu'));
            $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);
            $user->update(['context' => null]);
            return;
        }

        $user->update([
            'context' => ['application_id_to_update' => $applicationId]
        ]);

        $this->sendMessage($user->phone, __('bot_messages.application_update_menu', [
            'job_title' => $application->job_title,
            'company_name' => $application->company_name ?? 'N/A'
        ]));
        $this->updateConversationState($user, ConversationStateEnum::APPLICATION_UPDATE);
    }

    private function sendApplicationList(User $user): void
    {
        $applications = $user->applications()->latest()->get();

        if ($applications->isEmpty()) {
            $this->sendMessage($user->phone, __('bot_messages.application_list_empty'));
            $this->sendMessage($user->phone, __('bot_messages.main_menu'));
            $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);
            return;
        }

        $applicationListFormatted = [];
        $applicationIds = [];
        foreach ($applications as $index => $application) {
            $data = [
                'index' => $index + 1,
                'job_title' => $application->job_title,
                'company_name' => $application->company_name ?? 'N/A',
                'job_description' => $application->job_description ?? 'N/A',
                'job_salary' => $application->job_salary ? 'R$ ' . number_format($application->job_salary, 2, ',', '.') : 'N/A',
                'job_link' => $application->job_link ?? 'N/A',
                'application_date' => Carbon::parse($application->application_date)->format('d/m/Y'),
            ];

            $applicationListFormatted[] = __('bot_messages.application_list_item_details', $data);
            $applicationIds[$index + 1] = $application->id;
        }

        $this->sendMessage($user->phone, __('bot_messages.application_list_header'));
        $this->sendMessage($user->phone, implode("\n\n", $applicationListFormatted));
        $this->sendMessage($user->phone, __('bot_messages.application_list_prompt'));

        $user->update(['context' => ['application_ids' => $applicationIds]]);
        $this->updateConversationState($user, ConversationStateEnum::APPLICATION_LIST);
    }

    private function handleApplicationCreateState(User $user, string $message): void
    {
        $context = $user->context ?? [];
        $value = Str::lower($message) === 'pular' ? null : $message;

        if (! array_key_exists('company_name', $context)) {
            $validator = Validator::make(['company_name' => $value], ['company_name' => 'nullable|string|max:255']);
            if ($validator->fails()) {
                $this->sendMessage($user->phone, $validator->errors()->first());
                $this->sendMessage($user->phone, __('bot_messages.application_create_start'));
                return;
            }
            $context['company_name'] = $value;
            $user->update(['context' => $context]);
            $this->sendMessage($user->phone, __('bot_messages.application_create_job_title'));
            return;
        }

        if (! array_key_exists('job_title', $context)) {
            $validator = Validator::make(['job_title' => $value], ['job_title' => 'required|string|max:255'], [
                'required' => __('bot_messages.application_create_job_title_required'),
            ]);
            if ($validator->fails()) {
                $this->sendMessage($user->phone, $validator->errors()->first());
                $this->sendMessage($user->phone, __('bot_messages.application_create_job_title'));
                return;
            }
            $context['job_title'] = $value;
            $user->update(['context' => $context]);
            $this->sendMessage($user->phone, __('bot_messages.application_create_job_description'));
            return;
        }

        if (! array_key_exists('job_description', $context)) {
            $validator = Validator::make(['job_description' => $value], ['job_description' => 'nullable|string|max:255']);
            if ($validator->fails()) {
                $this->sendMessage($user->phone, $validator->errors()->first());
                $this->sendMessage($user->phone, __('bot_messages.application_create_job_description'));
                return;
            }
            $context['job_description'] = $value;
            $user->update(['context' => $context]);
            $this->sendMessage($user->phone, __('bot_messages.application_create_job_salary'));
            return;
        }

        if (! array_key_exists('job_salary', $context)) {
            $validator = Validator::make(['job_salary' => $value], ['job_salary' => 'nullable|numeric|min:0']);
            if ($validator->fails()) {
                $this->sendMessage($user->phone, $validator->errors()->first());
                $this->sendMessage($user->phone, __('bot_messages.application_create_job_salary'));
                return;
            }
            $context['job_salary'] = $value;
            $user->update(['context' => $context]);
            $this->sendMessage($user->phone, __('bot_messages.application_create_job_link'));
            return;
        }

        if (! array_key_exists('job_link', $context)) {
            $validator = Validator::make(['job_link' => $value], ['job_link' => 'nullable|url|max:255']);
            if ($validator->fails()) {
                $this->sendMessage($user->phone, $validator->errors()->first());
                $this->sendMessage($user->phone, __('bot_messages.application_create_job_link'));
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

            $this->sendMessage($user->phone, __('bot_messages.application_create_success'));
            $this->sendMessage($user->phone, __('bot_messages.main_menu'));
            $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);
            $user->update(['context' => null]);
            return;
        }
    }

    private function handleApplicationUpdateState(User $user, string $message): void
    {
        $context = $user->context ?? [];
        $applicationId = $context['application_id_to_update'] ?? null;

        if (!$applicationId) {
            $this->sendMessage($user->phone, __('bot_messages.error_try_again'));
            $this->sendMessage($user->phone, __('bot_messages.main_menu'));
            $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);
            return;
        }
        
        $application = Apliccation::query()->find($applicationId);
        if (!$application) {
            $this->sendMessage($user->phone, __('bot_messages.application_not_found'));
            $this->sendMessage($user->phone, __('bot_messages.main_menu'));
            $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);
            $user->update(['context' => null]);
            return;
        }

        if (!isset($context['field_to_edit'])) {
            $option = trim($message);
            $fieldMap = [
                '1' => 'company_name',
                '2' => 'job_title',
                '3' => 'job_description',
                '4' => 'job_salary',
                '5' => 'job_link',
            ];

            if ($option === '6' || Str::lower($message) === 'cancelar') {
                $this->sendMessage($user->phone, __('bot_messages.main_menu'));
                $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);
                $user->update(['context' => null]);
                return;
            }
            
            if (!isset($fieldMap[$option])) {
                $this->sendMessage($user->phone, __('bot_messages.invalid_option'));
                $this->sendMessage($user->phone, __('bot_messages.application_update_menu', [
                    'job_title' => $application->job_title,
                    'company_name' => $application->company_name ?? 'N/A'
                ]));
                return;
            }
            
            $fieldToEdit = $fieldMap[$option];
            $context['field_to_edit'] = $fieldToEdit;
            $user->update(['context' => $context]);

            $this->sendMessage($user->phone, __('bot_messages.application_update_prompt_new_value', ['field' => __("bot_messages.application_fields.$fieldToEdit")]));
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
            $this->sendMessage($user->phone, $validator->errors()->first());
            $this->sendMessage($user->phone, __('bot_messages.application_update_prompt_new_value', ['field' => __("bot_messages.application_fields.$fieldToEdit")]));
            return;
        }

        $application->update([$fieldToEdit => $value]);

        $this->sendMessage($user->phone, __('bot_messages.application_updated_success'));
        $this->sendMessage($user->phone, __('bot_messages.main_menu'));
        $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);
        $user->update(['context' => null]);
    }

    private function handleApplicationDeleteState(User $user, string $message): void
    {
        $this->sendMessage($user->phone, __('bot_messages.application_delete'));
    }

    private function updateConversationState(User $user, ConversationStateEnum $state): void
    {
        $user->update(['conversation_state' => $state]);
    }
}