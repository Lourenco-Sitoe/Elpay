<?php

namespace App\Http\Controllers;

use App\Mail\AffiliateStatusMail;
use App\Mail\ProductApprovedMail;
use App\Mail\UserStatusMail;
use App\Models\ActivityLog;
use App\Models\Affiliate;
use App\Models\Commission;
use App\Models\KycVerification;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\SupportMessage;
use App\Models\User;
use App\Models\Withdrawal;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AdminController extends Controller
{
    use LogsActivity;

    public function dashboard()
    {
        return app(DashboardController::class)->adminDashboard();
    }

    // ---- Users ----
    public function users(Request $request): View
    {
        $users = User::when($request->type, fn ($q) => $q->where('type', $request->type))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->q, fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$request->q}%")->orWhere('email', 'like', "%{$request->q}%")))
            ->latest()->paginate(15);

        return view('admin.users', compact('users'));
    }

    public function userUpdateStatus(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['status' => 'required|in:active,inactive,banned']);
        $user->status = $data['status'];
        $user->save();

        try {
            Notification::create(['user_id' => $user->id, 'title' => 'Estado da conta', 'message' => 'A sua conta passou a: ' . $data['status']]);
            Mail::to($user->email)->send(new UserStatusMail($user, $data['status']));
        } catch (\Throwable) {
        }
        $this->logActivity('admin.user.status', "Utilizador #{$user->id} -> {$data['status']}");

        return back()->with('success', 'Estado atualizado.');
    }

    public function userDestroy(User $user): RedirectResponse
    {
        $user->delete();
        $this->logActivity('admin.user.deleted', "Utilizador #{$user->id} eliminado.");

        return back()->with('success', 'Utilizador eliminado.');
    }

    // ---- Products ----
    public function products(Request $request): View
    {
        $products = Product::with('producer')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()->paginate(15);

        return view('admin.products', compact('products'));
    }

    public function productApprove(Product $product): RedirectResponse
    {
        $product->status = 'approved';
        $product->rejection_reason = null;
        $product->save();
        try {
            Mail::to($product->producer->email)->send(new ProductApprovedMail($product, 'approved'));
        } catch (\Throwable) {
        }
        $this->logActivity('admin.product.approved', "Produto #{$product->id} aprovado.");

        return back()->with('success', 'Produto aprovado.');
    }

    public function productReject(Request $request, Product $product): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        $product->status = 'rejected';
        $product->rejection_reason = $request->reason;
        $product->save();
        try {
            Mail::to($product->producer->email)->send(new ProductApprovedMail($product, 'rejected', $request->reason));
        } catch (\Throwable) {
        }
        $this->logActivity('admin.product.rejected', "Produto #{$product->id} rejeitado.");

        return back()->with('success', 'Produto rejeitado.');
    }

    // ---- Sales ----
    public function sales(Request $request): View
    {
        $query = Sale::with(['product', 'buyer']);
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($w) => $w->where('buyer_name', 'like', "%{$q}%")->orWhere('buyer_email', 'like', "%{$q}%")->orWhere('id', $q));
        }
        if ($request->filled('status') && in_array($request->status, ['pending', 'paid', 'cancelled', 'refunded'])) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        $sales = (clone $query)->latest()->paginate(15)->withQueryString();

        $base = Sale::query();
        $totalCount = (clone $base)->count();
        $paidCount = (clone $base)->where('status', 'paid')->count();
        $failCount = (clone $base)->whereIn('status', ['cancelled', 'refunded'])->count();
        $moved = (float) (clone $base)->sum('final_price');
        $received = (float) (clone $base)->where('status', 'paid')->sum('final_price');
        $lost = (float) (clone $base)->whereIn('status', ['cancelled', 'refunded'])->sum('final_price');

        return view('admin.sales', compact('sales', 'totalCount', 'paidCount', 'failCount', 'moved', 'received', 'lost'));
    }

    public function saleCancel(Sale $sale): RedirectResponse
    {
        $sale->status = 'cancelled';
        $sale->save();
        $this->logActivity('admin.sale.cancelled', "Venda #{$sale->id} cancelada.");

        return back()->with('success', 'Venda cancelada.');
    }

    public function saleRefund(Sale $sale): RedirectResponse
    {
        $sale->status = 'refunded';
        $sale->save();
        $this->logActivity('admin.sale.refunded', "Venda #{$sale->id} reembolsada.");

        return back()->with('success', 'Venda reembolsada.');
    }

    // ---- Payments ----
    public function payments(): View
    {
        $payments = Payment::with('sale')->latest()->paginate(15);

        return view('admin.payments', compact('payments'));
    }

    public function paymentApprove(Payment $payment): RedirectResponse
    {
        $payment->status = 'approved';
        $payment->save();

        return back()->with('success', 'Pagamento aprovado.');
    }

    public function paymentReject(Payment $payment): RedirectResponse
    {
        $payment->status = 'rejected';
        $payment->save();

        return back()->with('success', 'Pagamento rejeitado.');
    }

    // ---- Commissions ----
    public function commissions(): View
    {
        $commissions = Commission::with(['sale', 'product'])->latest()->paginate(15);
        $partnerEarnings = \App\Models\PartnerEarning::with(['product', 'partner'])->latest()->paginate(15, ['*'], 'ppage');

        return view('admin.commissions', compact('commissions', 'partnerEarnings'));
    }

    public function commissionMarkPaid(Commission $commission): RedirectResponse
    {
        $commission->status = 'paid';
        $commission->save();

        return back()->with('success', 'Comissão marcada como paga.');
    }

    // ---- Affiliates ----
    public function affiliatesAdmin(): View
    {
        $affiliates = Affiliate::with(['user', 'product'])->latest()->paginate(15);

        return view('admin.affiliates', compact('affiliates'));
    }

    public function affiliateApprove(Affiliate $affiliate): RedirectResponse
    {
        $affiliate->status = 'approved';
        $affiliate->save();
        try {
            Mail::to($affiliate->user->email)->send(new AffiliateStatusMail($affiliate, 'approved'));
        } catch (\Throwable) {
        }

        return back()->with('success', 'Afiliado aprovado.');
    }

    public function affiliateBlock(Request $request, Affiliate $affiliate): RedirectResponse
    {
        $request->validate(['custom_rate' => 'nullable|numeric|min:0|max:90']);
        $affiliate->status = 'blocked';
        if ($request->filled('custom_rate')) {
            $affiliate->custom_rate = $request->custom_rate;
        }
        $affiliate->save();

        return back()->with('success', 'Afiliado bloqueado.');
    }

    // ---- Withdrawals ----
    public function withdrawalsAdmin(): View
    {
        $withdrawals = Withdrawal::with('user')->latest()->paginate(15);

        return view('admin.withdrawals', compact('withdrawals'));
    }

    public function withdrawalApprove(Withdrawal $withdrawal): RedirectResponse
    {
        $withdrawal->status = 'approved';
        $withdrawal->processed_at = now();
        $withdrawal->save();
        $this->logActivity('admin.withdrawal.approved', "Levantamento #{$withdrawal->id} aprovado.");
        \App\Services\WebhookDispatcher::dispatch('withdrawal.approved', [
            'withdrawal_id' => $withdrawal->id,
            'user_id' => $withdrawal->user_id,
            'amount' => $withdrawal->amount,
        ], $withdrawal->user_id);

        return back()->with('success', 'Levantamento aprovado.');
    }

    public function withdrawalReject(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        $withdrawal->status = 'rejected';
        $withdrawal->rejection_reason = $request->reason;
        $withdrawal->save();

        $user = User::find($withdrawal->user_id);
        $user?->increment('balance', $withdrawal->amount);

        $this->logActivity('admin.withdrawal.rejected', "Levantamento #{$withdrawal->id} rejeitado + estorno.");

        return back()->with('success', 'Levantamento rejeitado e valor estornado.');
    }

    // ---- KYC ----
    public function kycAdmin(): View
    {
        $kycs = KycVerification::with('user')->latest()->paginate(15);

        return view('admin.kyc', compact('kycs'));
    }

    // ---- Reports ----
    public function reports(): View
    {
        $revenue = Sale::where('status', 'paid')->sum('final_price');
        $salesCount = Sale::where('status', 'paid')->count();
        $usersCount = User::count();
        $avg = $salesCount ? round($revenue / $salesCount, 2) : 0;
        $byMethod = Sale::where('status', 'paid')->groupBy('payment_method')->selectRaw('payment_method, SUM(final_price) as total')->pluck('total', 'payment_method');
        $byType = Product::groupBy('type')->selectRaw('type, COUNT(*) as c')->pluck('c', 'type');

        return view('admin.reports', [
            'revenue' => $revenue,
            'byMethod' => $byMethod,
            'byType' => $byType,
            'stats' => ['revenue' => round($revenue, 2), 'sales' => $salesCount, 'users' => $usersCount, 'avg' => $avg],
            'monthly' => collect([]),
            'mpesa' => (float) ($byMethod['mpesa'] ?? 0),
            'emola' => (float) ($byMethod['emola'] ?? 0),
        ]);
    }

    // ---- Communication ----
    public function communication(): View
    {
        return view('admin.communication');
    }

    public function sendMass(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'target' => 'required|in:all,admin,producer,affiliate,client',
        ]);

        $query = User::query();
        if ($data['target'] !== 'all') {
            $query->where('type', $data['target']);
        }
        foreach ($query->pluck('id') as $uid) {
            try {
                Notification::create(['user_id' => $uid, 'title' => $data['title'], 'message' => $data['message']]);
            } catch (\Throwable) {
                break;
            }
        }
        $this->logActivity('admin.broadcast', 'Mensagem em massa: ' . $data['title']);

        return back()->with('success', 'Mensagem enviada.');
    }

    // ---- Messages ----
    public function messages(): View
    {
        $messages = SupportMessage::with('user')->latest()->paginate(15);

        return view('admin.messages', compact('messages'));
    }

    public function reply(Request $request, SupportMessage $message): RedirectResponse
    {
        $request->validate(['reply' => 'required|string']);
        $message->admin_reply = $request->reply;
        $message->status = 'answered';
        $message->save();

        return back()->with('success', 'Resposta enviada.');
    }

    // ---- Logs ----
    public function logs(): View
    {
        $logs = ActivityLog::with('user')->latest()->paginate(20);

        return view('admin.logs', compact('logs'));
    }

    // ---- Settings ----
    public function settings(): View
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        return view('admin.settings', compact('settings'));
    }

    public function settingsUpdate(Request $request): RedirectResponse
    {
        $data = $request->validate(['settings' => 'required|array']);
        foreach ($data['settings'] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Definições guardadas.');
    }
}
