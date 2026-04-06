# Meta WhatsApp Laravel Package

Direct Meta WhatsApp Cloud API integration for Laravel.

## Features

- Webhook verification endpoint
- Inbound webhook handler
- Outbound text message endpoint
- Outbound template message endpoint
- HMAC validation with `X-Hub-Signature-256`
- Event dispatching for inbound webhook payloads
- Publishable config file

## Package Structure

- `src/WhatsAppServiceProvider.php`: package bootstrapping
- `src/Services/MetaWhatsAppClient.php`: direct Meta Graph API client
- `src/Http/Controllers/WhatsAppWebhookController.php`: webhook verification and inbound handling
- `src/Http/Controllers/WhatsAppMessageController.php`: outbound message endpoints
- `src/Events/WhatsAppWebhookReceived.php`: event fired for inbound webhook payloads
- `config/whatsapp.php`: package configuration
- `routes/api.php`: package routes

## Configuration

Environment variables used by the package:

```env
WHATSAPP_BASE_URL=https://graph.facebook.com
WHATSAPP_GRAPH_VERSION=v24.0
WHATSAPP_ACCESS_TOKEN=
WHATSAPP_PHONE_NUMBER_ID=
WHATSAPP_BUSINESS_ACCOUNT_ID=
WHATSAPP_APP_SECRET=
WHATSAPP_WEBHOOK_VERIFY_TOKEN=
WHATSAPP_ROUTE_PREFIX=whatsapp
```

You can publish the config into the host Laravel app with:

```bash
php artisan vendor:publish --tag=whatsapp-config
```

Published config path:

```text
config/whatsapp.php
```

## Routes

By default the package exposes these routes:

- `GET /api/whatsapp/webhook`
- `POST /api/whatsapp/webhook`
- `POST /api/whatsapp/messages/text`
- `POST /api/whatsapp/messages/template`

The `whatsapp` segment is configurable through `WHATSAPP_ROUTE_PREFIX`.

## Usage

### Verify webhook

Meta will call:

```text
GET /api/whatsapp/webhook?hub.mode=subscribe&hub.verify_token=...&hub.challenge=...
```

The package validates the verify token and returns the challenge string.

### Receive inbound webhooks

Meta will send webhook payloads to:

```text
POST /api/whatsapp/webhook
```

If `WHATSAPP_APP_SECRET` is configured, the package validates the `X-Hub-Signature-256` signature before accepting the request.

The package then dispatches:

```php
SocialMedia\WhatsApp\Events\WhatsAppWebhookReceived
```

### Send a text message

Request:

```json
POST /api/whatsapp/messages/text
{
  "to": "254700000000",
  "body": "Hello from Laravel"
}
```

### Send a template message

Request:

```json
POST /api/whatsapp/messages/template
{
  "to": "254700000000",
  "name": "hello_world",
  "language": "en_US"
}
```

## Extending in the Host App

Listen for the webhook event in your app to process messages, statuses, and delivery updates:

```php
use SocialMedia\WhatsApp\Events\WhatsAppWebhookReceived;

Event::listen(WhatsAppWebhookReceived::class, function (WhatsAppWebhookReceived $event) {
    // Handle $event->payload
});
```

## Meta Setup Checklist

1. Create a Meta app and enable the WhatsApp product.
2. Get an access token.
3. Get your Phone Number ID and WhatsApp Business Account ID.
4. Set the Meta webhook callback URL to your app's `/api/whatsapp/webhook` endpoint.
5. Set Meta's verify token to match `WHATSAPP_WEBHOOK_VERIFY_TOKEN`.
6. Subscribe the app to your WABA.

## References

- [Meta WhatsApp Cloud API Overview](https://developers.facebook.com/docs/whatsapp/cloud-api/overview)
- [Meta WhatsApp Cloud API Get Started](https://developers.facebook.com/docs/whatsapp/cloud-api/get-started)
- [Meta WhatsApp Cloud API Postman Collection](https://www.postman.com/meta/whatsapp-business-platform/documentation/wlk6lh4/whatsapp-cloud-api)
