<?php

namespace App\Services\Payments;

use App\Models\Sale;
use RuntimeException;

/**
 * Integração M-Pesa (Vodacom Moçambique).
 *
 * PENDENTE: credenciais reais + endpoint. Quando `mpesa.api_key` estiver
 * configurado, implementar aqui a chamada C2B (Authorization Bearer,
 * POST {endpoint}/c2b/payment) e mapear a resposta para PaymentResult.
 * O callback do operador deve apontar para POST /api/payments/callback/mpesa.
 */
class MpesaGateway implements PaymentGatewayInterface
{
    public function __construct(protected array $config = []) {}

    public function initiate(Sale $sale, string $phone): PaymentResult
    {
        if (empty($this->config['api_key'])) {
            throw new RuntimeException('M-Pesa não configurado (MPESA_API_KEY em falta).');
        }

        // TODO: chamada real à API C2B aqui.
        return new PaymentResult(status: 'pending', message: 'Aguarda confirmação no telemóvel.');
    }

    public function verifyCallback(array $payload): PaymentResult
    {
        // TODO: validar assinatura/HMAC do callback aqui.
        $success = ($payload['output_ResponseCode'] ?? '') === 'INS-0'
            || ($payload['status'] ?? '') === 'approved';

        return new PaymentResult(
            status: $success ? 'approved' : 'failed',
            reference: $payload['output_TransactionID'] ?? $payload['reference'] ?? null,
        );
    }
}
