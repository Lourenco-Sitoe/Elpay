<?php

namespace App\Services\Payments;

use App\Models\Sale;

interface PaymentGatewayInterface
{
    public function initiate(Sale $sale, string $phone): PaymentResult;

    /** Verifica callback/webhook do operador. Deve lançar em caso de assinatura inválida. */
    public function verifyCallback(array $payload): PaymentResult;
}
