<?php

namespace App\Http\Controllers\WhatsappMessage;

use App\Http\Controllers\Controller;
use App\Http\Requests\WhatsappMessageReceivedRequest;
use Illuminate\Support\Facades\Log;

class Received extends Controller
{
    public function __invoke(WhatsappMessageReceivedRequest $request)
    {
        Log::info('passou');
        //;
    }
}