<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\Commission;
use App\Models\Notification;
use App\Models\PayoutAccount;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\WebhookDispatcher;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    use LogsActivity;

    public function index(): View
    {
        $user = User::find(auth()->id());
        $balance = (float) ($user->balance ?? 0);
        $affiliateBalance = 0;
        try {
            $affiliateBalance = (float) Affiliate::where('user_id', $user->id)->sum('balance');
        } catch (\Throwable) {
        }
        $withdrawals = Withdrawal::where('user_id', $user->id)->latest()->paginate(10);
        // Saldo pendente = saques pendentes + comissões pendentes (não pode sacar).
        $pendingWithdrawals = (float) Withdrawal::where('user_id', $user->id)->where('status', 'pending')->sum('amount');
        $pendingCommissions = 0;
        try {
            $pendingCommissions = (float) Commission::where('affiliate_id', $user->id)->where('status', 'pending')->sum('amount');
        } catch (\Throwable) {
        }

        return view('wallet.index', compact('balance', 'affiliateBalance', 'withdrawals', 'pendingWithdrawals', 'pendingCommissions'));
    }

    public function requestWithdrawal(): View
    {
        $balance = (float) (auth()->user()->balance ?? 0);
        $accounts = PayoutAccount::where('user_id', auth()->id())->where('active', true)->get();

        return view('wallet.request', compact('balance', 'accounts'));
    }

    public function storeWithdrawal(Request $request): RedirectResponse
    {
        $min = (float) setting('min_withdrawal', 100);
        $feePercent = (float) setting('withdrawal_fee_percent', 2);

        $data = $request->validate([
            'amount' => 'required|numeric|min:'.$min,
            'method' => 'required|in:mpesa,emola',
            'phone' => 'nullable|string|max:30',
            'payout_account_id' => 'nullable|exists:payout_accounts,id',
        ]);

        // Conta guardada preenche método + número.
        if (! empty($data['payout_account_id'])) {
            $account = PayoutAccount::where('user_id', auth()->id())
                ->where('active', true)->findOrFail($data['payout_account_id']);
            $data['method'] = $account->method === 'bank' ? $data['method'] : $account->method;
            $data['phone'] = $account->phone ?? $data['phone'];
        }
        if (empty($data['phone'])) {
            return back()->withErrors(['phone' => 'Indica o número destino ou escolhe uma conta.'])->withInput();
        }

        $user = User::find(auth()->id());
        $balance = (float) ($user->balance ?? 0);

        if ($data['amount'] > $balance) {
            return back()->withErrors(['amount' => 'Saldo insuficiente.'])->withInput();
        }

        $fee = round($data['amount'] * ($feePercent / 100), 2);
        $net = round($data['amount'] - $fee, 2);

        $user->decrement('balance', $data['amount']);

        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            'amount' => $data['amount'],
            'fee' => $fee,
            'net' => $net,
            'method' => $data['method'],
            'phone' => $data['phone'],
            'status' => 'pending',
        ]);

        try {
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Levantamento solicitado',
                'message' => 'Pedido de '.$data['amount'].' MZN recebido. Taxa: '.$fee.' MZN.',
            ]);
        } catch (\Throwable) {
        }

        $this->logActivity('withdrawal.requested', 'Levantamento #'.$withdrawal->id.' de '.$data['amount']);

        WebhookDispatcher::dispatch('withdrawal.requested', [
            'withdrawal_id' => $withdrawal->id,
            'user_id' => $user->id,
            'amount' => $data['amount'],
            'method' => $data['method'],
        ], $user->id);

        return redirect()->route('wallet.index')->with('success', 'Pedido de levantamento registado.');
    }
}
