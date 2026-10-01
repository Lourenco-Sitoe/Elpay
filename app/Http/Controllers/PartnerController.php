<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\PartnerEarning;
use App\Models\Product;
use App\Models\ProductPartner;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartnerController extends Controller
{
    use LogsActivity;

    protected function myProduct(Product $product): void
    {
        abort_unless(
            (auth()->user()->type ?? '') === 'admin' || (int) $product->producer_id === (int) auth()->id(),
            403
        );
    }

    /** Parceiros de um produto (dono do produto). */
    public function index(Product $product): View
    {
        $this->myProduct($product);
        $partners = ProductPartner::with('user')->where('product_id', $product->id)->get();

        return view('partners.index', compact('product', 'partners'));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $this->myProduct($product);
        $data = $request->validate([
            'email' => 'required|email|exists:users,email',
            'share_percent' => 'required|numeric|min:1|max:90',
        ]);
        $partner = User::where('email', $data['email'])->first();
        abort_if((int) $partner->id === (int) $product->producer_id, 422, 'Não pode adicionar-se a si próprio.');

        $total = (float) ProductPartner::where('product_id', $product->id)->where('status', 'approved')->sum('share_percent');
        if ($total + (float) $data['share_percent'] > 90) {
            return back()->withErrors(['share_percent' => 'Total de parceiros não pode exceder 90%.'])->withInput();
        }

        ProductPartner::updateOrCreate(
            ['product_id' => $product->id, 'user_id' => $partner->id],
            ['share_percent' => $data['share_percent'], 'status' => 'pending']
        );

        try {
            Notification::create([
                'user_id' => $partner->id,
                'title' => 'Convite de parceria',
                'message' => 'Foste convidado para coproduzir "' . $product->title . '" com ' . $data['share_percent'] . '%.',
            ]);
        } catch (\Throwable) {
        }

        return back()->with('success', 'Convite enviado ao parceiro.');
    }

    public function approve(ProductPartner $partner): RedirectResponse
    {
        $this->myProduct($partner->product);
        $partner->status = 'approved';
        $partner->save();

        return back()->with('success', 'Parceiro aprovado.');
    }

    public function reject(ProductPartner $partner): RedirectResponse
    {
        $this->myProduct($partner->product);
        $partner->status = 'rejected';
        $partner->save();

        return back()->with('success', 'Parceiro rejeitado.');
    }

    public function destroy(ProductPartner $partner): RedirectResponse
    {
        $this->myProduct($partner->product);
        $partner->delete();

        return back()->with('success', 'Parceria removida.');
    }

    /** Ganhos como parceiro (coproduções onde sou parceiro). */
    public function earnings(): View
    {
        $earnings = PartnerEarning::with(['product', 'sale'])
            ->where('partner_id', auth()->id())
            ->latest()->paginate(15);
        $total = PartnerEarning::where('partner_id', auth()->id())->sum('amount');

        return view('partners.earnings', compact('earnings', 'total'));
    }

    public function markPaid(PartnerEarning $earning): RedirectResponse
    {
        abort_unless((auth()->user()->type ?? '') === 'admin', 403);
        $earning->status = 'paid';
        $earning->save();

        return back()->with('success', 'Ganho marcado como pago.');
    }
}
