<?php
namespace App\Service;

use Stripe\StripeClient;
use Stripe\PaymentIntent;
use Stripe\Checkout\Session;
use Stripe\Webhook;
use App\Entity\Order;

class StripeService
{
    private StripeClient $stripe;

    public function __construct(string $stripeSecretKey)
    {
        $this->stripe = new StripeClient([
            'api_key' => $stripeSecretKey,
        ]);
    }

    public function createPaymentIntent(float $amount, string $currency = 'eur'): PaymentIntent
    {
        return $this->stripe->paymentIntents->create([
            'amount' => (int)($amount * 100),
            'currency' => $currency,
        ]);
    }

    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return $this->stripe->paymentIntents->retrieve($paymentIntentId);
    }

    public function retrievePaymentIntentClientSecret(string $paymentIntentId): string
    {
        $paymentIntent = $this->retrievePaymentIntent($paymentIntentId);
        return $paymentIntent->client_secret;
    }

    public function createCheckoutSession(Order $order): Session
    {
        return $this->stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Cursus E-learning',
                    ],
                    'unit_amount' => (int)($order->getAmount() * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => ($_ENV['FRONTEND_URL'] ?? 'http://localhost:4200') . '/payment/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => ($_ENV['FRONTEND_URL'] ?? 'http://localhost:4200') . '/payment/cancel',
            'metadata' => [
                'order_id' => $order->getId(),
            ],
        ]);
    }

    public function constructWebhookEvent(string $payload, string $signature): \Stripe\Event
    {
        $endpointSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';
        
        if (empty($endpointSecret)) {
            throw new \Exception('Stripe webhook secret not configured');
        }
        
        return Webhook::constructEvent($payload, $signature, $endpointSecret);
    }
}
