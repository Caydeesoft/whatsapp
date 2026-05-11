<?php

namespace Caydeesoft\WhatsApp\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use CCaydeesoft\WhatsApp\Services\MetaWhatsAppClient;

class WhatsAppMessageController
{
    public function __construct(
        protected MetaWhatsAppClient $whatsApp,
    ) {
    }

    public function sendText(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'to' => ['required', 'string', 'max:32'],
            'body' => ['required', 'string', 'max:4096'],
            'preview_url' => ['sometimes', 'boolean'],
        ]);

        return response()->json($this->whatsApp->sendTextMessage($validated));
    }

    public function sendTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'to' => ['required', 'string', 'max:32'],
            'name' => ['required', 'string', 'max:512'],
            'language' => ['sometimes', 'string', 'max:16'],
            'components' => ['sometimes', 'array'],
        ]);

        return response()->json($this->whatsApp->sendTemplateMessage($validated));
    }
}
