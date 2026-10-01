<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentCallbackController extends Controller
{
    /**
     * Webhook dos operadores: POST /api/payments/callback/{driver}
     * (mpesa|emola). Finaliza a venda quando o pagamento é confirmado.
     */
    public function handle(Request $request, string $driver): JsonResponse
    {
        abort_unless(in_array($driver, ['mpesa', 'emola'], true), 404);

        // Recusa callbacks de gateways não configurados (evita aprovações falsas).
        $configured = $driver === 'mpesa'
            ? ! empty(config('payments.drivers.mpesa.api_key'))
            : ! empty(config('payments.drivers.emola.api_key'));
        abort_unless($configured, 403, 'Gateway não configurado.');

        $sale = Sale::where('id', $request->input('sale_id'))
            ->orWhereHas('payments', fn ($q) => $q->where('transaction_ref', $request->input('reference')))
            ->first();

        if (! $sale) {
            return response()->json(['message' => 'Venda não encontrada.'], 404);
        }

        try {
            $result = PaymentGatewayManager::driver($driver)->verifyCallback($request->all());
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Assinatura inválida.'], 403);
        }

        if ($result->approved()) {
            foreach ($sale->payments()->where('status', 'pending')->get() as $payment) {
                $payment->status = 'approved';
                $payment->transaction_ref ??= $result->reference;
                $payment->approved_at = now();
                $payment->save();
            }
            app(CheckoutController::class)->finalizeSale($sale, $result->reference);

            return response()->json(['ok' => true]);
        }

        if ($result->status === 'failed') {
            $sale->status = 'cancelled';
            $sale->save();
        }

        return response()->json(['ok' => true, 'status' => $result->status]);
    }
}
