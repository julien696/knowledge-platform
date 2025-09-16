<?php
namespace App\Service;

use Stripe\StripeClient;
use Stripe\PaymentIntent;

class StripeService
{
    public function __construct(private StripeClient $stripe)
    {
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
}
