<?php

namespace App\Http\Controllers;

use App\Models\PayoutAccount;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayoutAccountController extends Controller
{
    use LogsActivity;

    public function index(): View
    {
        $accounts = PayoutAccount::where('user_id', auth()->id())->latest()->get();

        return view('payouts.accounts', compact('accounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'method' => 'required|in:mpesa,emola,bank',
            'label' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'holder_name' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);

        if (! empty($data['is_default'])) {
            PayoutAccount::where('user_id', auth()->id())->update(['is_default' => false]);
        }

        PayoutAccount::create([
            'user_id' => auth()->id(),
            'method' => $data['method'],
            'label' => $data['label'],
            'phone' => $data['phone'] ?? null,
            'holder_name' => $data['holder_name'] ?? auth()->user()->name,
            'is_default' => ! empty($data['is_default']),
        ]);

        return back()->with('success', 'Conta de saque adicionada.');
    }

    public function update(Request $request, PayoutAccount $account): RedirectResponse
    {
        $this->own($account);
        $data = $request->validate([
            'label' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'holder_name' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
            'active' => 'nullable|boolean',
        ]);
        if (! empty($data['is_default'])) {
            PayoutAccount::where('user_id', auth()->id())->where('id', '!=', $account->id)->update(['is_default' => false]);
        }
        $account->update([
            'label' => $data['label'],
            'phone' => $data['phone'] ?? $account->phone,
            'holder_name' => $data['holder_name'] ?? $account->holder_name,
            'is_default' => ! empty($data['is_default']),
            'active' => $data['active'] ?? $account->active,
        ]);

        return back()->with('success', 'Conta atualizada.');
    }

    public function destroy(PayoutAccount $account): RedirectResponse
    {
        $this->own($account);
        $account->delete();

        return back()->with('success', 'Conta removida.');
    }

    protected function own(PayoutAccount $account): void
    {
        abort_unless((int) $account->user_id === (int) auth()->id() || (auth()->user()->type ?? '') === 'admin', 403);
    }
}
