<?php

namespace App\Http\Controllers\WhatsappMessage;

use App\Http\Controllers\Controller;
use App\Enums\ConversationStateEnum;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

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
    }

    private function handleMainMenuState(User $user, string $message): void
    {
        $this->sendMessage($user->phone, __('bot_messages.main_menu'));
        $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);
    }

    private function handleApplicationListState(User $user, string $message): void
    {
        $this->sendMessage($user->phone, __('bot_messages.application_list'));
    }

    private function handleApplicationCreateState(User $user, string $message): void
    {
        $this->sendMessage($user->phone, __('bot_messages.application_create'));
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