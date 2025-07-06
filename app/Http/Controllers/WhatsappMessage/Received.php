<?php

namespace App\Http\Controllers\WhatsappMessage;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Received extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->all();
        $from = strstr($data['data']['key']['remoteJid'], '@', true);
        $message = $data['data']['message']['conversation'] ?? $data['data']['message']['extendedTextMessage']['text'];
        
        $user = User::query()->firstOrCreate(['phone' => $from], [
            'name' => $data['data']['pushName'],
        ]);

        $response = Http::withHeaders([
            'apikey' => config('services.evolution.instance_token'),
        ])->post(config('services.evolution.server_url') . '/message/sendText/' . config('services.evolution.instance_name'), [
            'number' => $from,
            'text' => __('bot_messages.welcome_new_user', ['name' => $user->name]),
        ]);
        Log::info($response->body());
    }
}