<?php

namespace App\Services\Payments;

use App\Models\Sale;
use Illuminate\Support\Str;

/**
 * Gateway mock: aprova de imediato (comportamento atual da plataforma).
 * Substitui-se por mpesa/emola configurando payment_driver.
 */
class MockPaymentGateway implements PaymentGatewayInterface
{
    public function initiate(Sale $sale, string $phone): PaymentResult
    {
        return new PaymentResult(
            status: 'approved',
            reference: 'MOCK-' . strtoupper(Str::random(10)),
        );
    }

    public function verifyCallback(array $payload): PaymentResult
    {
        return new PaymentResult(
            status: $payload['status'] ?? 'pending',
            reference: $payload['reference'] ?? null,
        );
    }
}
