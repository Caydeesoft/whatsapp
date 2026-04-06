<?php

use Illuminate\Support\Facades\Route;
use SocialMedia\WhatsApp\Http\Controllers\WhatsAppMessageController;
use SocialMedia\WhatsApp\Http\Controllers\WhatsAppWebhookController;

Route::prefix('api/'.trim((string) config('whatsapp.route_prefix', 'whatsapp'), '/'))
    ->middleware(config('whatsapp.middleware', ['api']))
    ->group(function (): void {
        Route::get('webhook', [WhatsAppWebhookController::class, 'verify']);
        Route::post('webhook', [WhatsAppWebhookController::class, 'handle']);

        Route::post('messages/text', [WhatsAppMessageController::class, 'sendText']);
        Route::post('messages/template', [WhatsAppMessageController::class, 'sendTemplate']);
    });
