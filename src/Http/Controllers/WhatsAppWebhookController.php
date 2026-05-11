<?php

namespace Caydeesoft\WhatsApp\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Caydeesoft\WhatsApp\Events\WhatsAppWebhookReceived;
use Caydeesoft\WhatsApp\Services\MetaWhatsAppClient;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class WhatsAppWebhookController
{
    public function __construct(
        protected MetaWhatsAppClient $whatsApp,
    ) {
    }

    public function verify(Request $request): Response
    {
        $verifyToken = (string) config('whatsapp.webhook_verify_token');
        $mode = (string) ($request->query('hub.mode') ?? $request->query('hub_mode'));
        $challenge = (string) ($request->query('hub.challenge') ?? $request->query('hub_challenge'));
        $providedToken = (string) ($request->query('hub.verify_token') ?? $request->query('hub_verify_token'));

        if ($mode === 'subscribe' && hash_equals($verifyToken, $providedToken)) {
            return response($challenge, SymfonyResponse::HTTP_OK)
                ->header('Content-Type', 'text/plain');
        }

        abort(SymfonyResponse::HTTP_FORBIDDEN, 'Invalid webhook verification token.');
    }

    public function handle(Request $request): JsonResponse
    {
        $signature = $request->header('X-Hub-Signature-256');
        $rawPayload = $request->getContent();
        $appSecret = (string) config('whatsapp.app_secret');

        if ($appSecret !== '' && ! $this->whatsApp->verifySignature($signature, $rawPayload)) {
            return response()->json([
                'message' => 'Invalid webhook signature.',
            ], SymfonyResponse::HTTP_UNAUTHORIZED);
        }

        $payload = $request->json()->all();

        event(new WhatsAppWebhookReceived($payload));

        logger()->info('WhatsApp webhook received.', [
            'object' => $payload['object'] ?? null,
            'entry_count' => count($payload['entry'] ?? []),
        ]);

        return response()->json([
            'received' => true,
        ]);
    }
}
