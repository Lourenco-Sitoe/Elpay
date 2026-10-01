<?php

namespace App\Services\Payments;

use App\Models\Sale;
use RuntimeException;

/**
 * Integração e-Mola (Movitel).
 *
 * PENDENTE: credenciais reais + endpoint. Quando `emola.api_key` estiver
 * configurado, implementar aqui a chamada de pagamento e mapear a resposta.
 * O callback do operador deve apontar para POST /api/payments/callback/emola.
 */
class EmolaGateway implements PaymentGatewayInterface
{
    public function __construct(protected array $config = []) {}

    public function initiate(Sale $sale, string $phone): PaymentResult
    {
        if (empty($this->config['api_key'])) {
            throw new RuntimeException('e-Mola não configurado (EMOLA_API_KEY em falta).');
        }

        // TODO: chamada real à API aqui.
        return new PaymentResult(status: 'pending', message: 'Aguarda confirmação no telemóvel.');
    }

    public function verifyCallback(array $payload): PaymentResult
    {
        // TODO: validar assinatura do callback aqui.
        $success = ($payload['status'] ?? '') === 'approved';

        return new PaymentResult(
            status: $success ? 'approved' : 'failed',
            reference: $payload['reference'] ?? null,
        );
    }
}
