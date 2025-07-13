<?php

namespace App\Http\Controllers\WhatsappMessage;

use App\Enums\ConversationStateEnum;
use App\Http\Controllers\Controller;
use App\Models\Apliccation;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
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
                $this->sendMessage($user->phone, __('bot_messages.application_create_start'));
                $this->updateConversationState($user, ConversationStateEnum::APPLICATION_CREATE);
                break;
            case '2':
                $this->sendMessage($user->phone, __('bot_messages.application_list'));
                $this->updateConversationState($user, ConversationStateEnum::APPLICATION_LIST);
                break;
            case '3':
                $this->sendMessage($user->phone, __('bot_messages.application_delete_start'));
                $this->updateConversationState($user, ConversationStateEnum::APPLICATION_DELETE);
                break;
            case '4':
                $this->sendMessage($user->phone, __('bot_messages.application_update_start'));
                $this->updateConversationState($user, ConversationStateEnum::APPLICATION_UPDATE);
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
        $this->sendMessage($user->phone, __('bot_messages.application_list'));
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
        $this->sendMessage($user->phone, __('bot_messages.application_update'));
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