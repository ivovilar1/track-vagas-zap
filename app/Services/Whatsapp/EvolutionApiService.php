<?php

namespace App\Services\Whatsapp;

use Exception;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionApiService
{
    public function __construct(
        #[Config('services.evolution.instance_token')]
        private readonly string $instanceToken,

        #[Config('services.evolution.server_url')]
        private readonly string $serverUrl,

        #[Config('services.evolution.instance_name')]
        private readonly string $instanceName,
    ) {}

    public function sendTextMessage(string $to, string $message): void
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->instanceToken,
            ])->post($this->serverUrl.'/message/sendText/'.$this->instanceName, [
                'number' => $to,
                'text' => $message,
            ]);

            if (! $response->successful()) {
                Log::error('Failed to send WhatsApp message', [
                    'phone' => $to,
                    'response' => $response->body(),
                ]);
            }
        } catch (Exception $e) {
            Log::error('WhatsApp API error', [
                'phone' => $to,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
