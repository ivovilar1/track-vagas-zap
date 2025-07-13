<?php

namespace App\Http\Controllers\WhatsappMessage;

use App\Core\StateHandlerFactory;
use App\Enums\ConversationStateEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Whatsapp\EvolutionApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Received extends Controller
{
    public function __construct(
        private readonly EvolutionApiService $evolutionApiService,
        private readonly StateHandlerFactory $stateHandlerFactory,
    ) {}

    public function __invoke(Request $request): void
    {
        $data = $request->all();
        $from = strstr($data['data']['key']['remoteJid'], '@', true);
        $message = $data['data']['message']['conversation'] ?? $data['data']['message']['extendedTextMessage']['text'];

        $user = User::query()->where('phone', $from)->first();
        if (! $user) {
            $user = User::create([
                'phone' => $from,
                'name' => $data['data']['pushName'],
                'conversation_state' => ConversationStateEnum::IDLE,
            ]);
        }
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
