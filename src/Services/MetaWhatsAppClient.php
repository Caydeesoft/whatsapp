<?php

namespace Caydeesoft\WhatsApp\Services;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class MetaWhatsAppClient
{
    public function __construct(
        protected HttpFactory $http,
        protected ConfigRepository $config,
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function sendTextMessage(array $payload): array
    {
        return $this->sendMessage([
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => Arr::get($payload, 'to'),
            'type' => 'text',
            'preview_url' => (bool) Arr::get($payload, 'preview_url', false),
            'text' => [
                'body' => Arr::get($payload, 'body'),
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function sendTemplateMessage(array $payload): array
    {
        return $this->sendMessage([
            'messaging_product' => 'whatsapp',
            'to' => Arr::get($payload, 'to'),
            'type' => 'template',
            'template' => [
                'name' => Arr::get($payload, 'name'),
                'language' => [
                    'code' => Arr::get($payload, 'language', 'en_US'),
                ],
                'components' => Arr::get($payload, 'components', []),
            ],
        ]);
    }

    public function subscribeApp(): array
    {
        $businessAccountId = $this->configValue('business_account_id', 'WHATSAPP_BUSINESS_ACCOUNT_ID');

        return $this->request('POST', sprintf('/%s/subscribed_apps', $businessAccountId))->json();
    }

    public function verifySignature(?string $signature, string $payload): bool
    {
        $appSecret = (string) $this->config->get('whatsapp.app_secret', '');

        if ($appSecret === '' || $signature === null || ! str_starts_with($signature, 'sha256=')) {
            return false;
        }

        $expected = 'sha256='.hash_hmac('sha256', $payload, $appSecret);

        return hash_equals($expected, $signature);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function sendMessage(array $payload): array
    {
        $phoneNumberId = $this->configValue('phone_number_id', 'WHATSAPP_PHONE_NUMBER_ID');

        return $this->request('POST', sprintf('/%s/messages', $phoneNumberId), $payload)->json();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function request(string $method, string $path, array $payload = []): Response
    {
        $version = trim((string) $this->config->get('whatsapp.graph_version', 'v24.0'), '/');
        $baseUrl = rtrim((string) $this->config->get('whatsapp.base_url', 'https://graph.facebook.com'), '/');
        $accessToken = $this->configValue('access_token', 'WHATSAPP_ACCESS_TOKEN');

        return $this->http
            ->baseUrl(sprintf('%s/%s', $baseUrl, $version))
            ->acceptJson()
            ->asJson()
            ->withToken($accessToken)
            ->throw()
            ->send($method, ltrim($path, '/'), [
                'json' => $payload,
            ]);
    }

    protected function configValue(string $key, string $envKey): string
    {
        $value = (string) $this->config->get(sprintf('whatsapp.%s', $key), '');

        if ($value === '') {
            throw new InvalidArgumentException(sprintf('%s is not configured.', $envKey));
        }

        return $value;
    }
}
