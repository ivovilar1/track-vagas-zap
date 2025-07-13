<?php

namespace App\Http\Controllers\WhatsappMessage;

use App\Core\StateHandlerFactory;
use App\Enums\ConversationStateEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Whatsapp\EvolutionApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Received extends Controller
{
    public function __construct(
        private readonly EvolutionApiService $evolutionApiService,
        private readonly StateHandlerFactory $stateHandlerFactory,
    ) {}

    public function __invoke(Request $request): void
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.key' => 'required|array',
            'data.key.remoteJid' => 'required|string',
            'data.pushName' => 'required|string',
            'data.message' => 'required|array',
        ]);

        if ($validator->fails()) {
            Log::error('Invalid webhook payload', [
                'errors' => $validator->errors(),
                'payload' => $request->all(),
            ]);
            return;
        }
        
        $data = $validator->validated();
        $message = $data['data']['message']['conversation'] 
            ?? $data['data']['message']['extendedTextMessage']['text'] 
            ?? null;

        if (is_null($message)) {
            Log::error('Message not found in webhook payload', ['payload' => $request->all()]);
            return;
        }

        $from = strstr($data['data']['key']['remoteJid'], '@', true);
        
        $user = User::query()->firstOrCreate(
            ['phone' => $from],
            [
                'name' => $data['data']['pushName'],
                'conversation_state' => ConversationStateEnum::IDLE,
            ]
        );
        
        $this->handleMessage($user, $message);
    }

    private function handleMessage(User $user, string $message): void
    {
        try {

            $handler = $this->stateHandlerFactory->make($user->conversation_state->value);
            $handler->handle($user, $message);

        } catch (\Throwable $th) {

            Log::error('Error handling message', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.error_try_again'));
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.main_menu'));
            $user->update(['conversation_state' => ConversationStateEnum::MAIN_MENU]);
        }
    }
}
