<?php

namespace App\Services\Payments;

class PaymentGatewayManager
{
    public static function driver(?string $name = null): PaymentGatewayInterface
    {
        $name ??= setting('payment_driver', config('payments.default', 'mock'));
        $drivers = config('payments.drivers', []);

        return match ($name) {
            'mpesa' => new MpesaGateway($drivers['mpesa'] ?? []),
            'emola' => new EmolaGateway($drivers['emola'] ?? []),
            default => new MockPaymentGateway,
        };
    }
}
