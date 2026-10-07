<?php

namespace App\Http\Controllers;

use App\Mail\AffiliateStatusMail;
use App\Models\Affiliate;
use App\Models\Commission;
use App\Models\Product;
use App\Models\Sale;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ProducerAffiliateController extends Controller
{
    use LogsActivity;

    protected function myProductOrFail(int $id): Product
    {
        $product = Product::findOrFail($id);
        abort_unless((int) $product->producer_id === (int) auth()->id(), 403);

        return $product;
    }

    /** Gestão de afiliados dos produtos do produtor. */
    public function index(Request $request): View
    {
        $productIds = Product::where('producer_id', auth()->id())->pluck('id');
        $query = Affiliate::with(['user', 'product'])->whereIn('product_id', $productIds);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'blocked'])) {
            $query->where('status', $request->status);
        }

        $affiliates = $query->latest()->paginate(15)->withQueryString();
        $products = Product::where('producer_id', auth()->id())->orderBy('title')->get();

        // Totais por afiliado: vendas + cliques + conversão.
        $stats = [];
        foreach ($affiliates as $a) {
            $sales = Sale::where('product_id', $a->product_id)
                ->where('affiliate_code', $a->affiliate_code)
                ->where('status', 'paid');
            $stats[$a->id] = [
                'sales' => (clone $sales)->count(),
                'revenue' => (float) (clone $sales)->sum('final_price'),
                'clicks' => (int) ($a->clicks ?? 0),
                'conversion' => ($a->clicks ?? 0) > 0 ? round(($a->total_sales / max(1, $a->clicks)) * 100, 2) : 0,
                'earned' => (float) Commission::where('product_id', $a->product_id)->where('affiliate_id', $a->user_id)->sum('amount'),
            ];
        }

        return view('producer.affiliates', compact('affiliates', 'products', 'stats'));
    }

    public function approve(Affiliate $affiliate): RedirectResponse
    {
        $this->myProductOrFail($affiliate->product_id);
        $affiliate->status = 'approved';
        $affiliate->save();
        try {
            Mail::to($affiliate->user->email)->send(new AffiliateStatusMail($affiliate, 'approved'));
        } catch (\Throwable) {
        }
        $this->logActivity('producer.affiliate.approved', "Afiliado #{$affiliate->id} aprovado.");

        return back()->with('success', 'Afiliado aprovado.');
    }

    public function reject(Affiliate $affiliate): RedirectResponse
    {
        $this->myProductOrFail($affiliate->product_id);
        $affiliate->status = 'blocked';
        $affiliate->save();
        $this->logActivity('producer.affiliate.rejected', "Afiliado #{$affiliate->id} rejeitado/bloqueado.");

        return back()->with('success', 'Afiliado rejeitado.');
    }

    public function unblock(Affiliate $affiliate): RedirectResponse
    {
        $this->myProductOrFail($affiliate->product_id);
        $affiliate->status = 'approved';
        $affiliate->save();

        return back()->with('success', 'Afiliado desbloqueado.');
    }

    public function updateRate(Request $request, Affiliate $affiliate): RedirectResponse
    {
        $this->myProductOrFail($affiliate->product_id);
        $data = $request->validate(['custom_rate' => 'nullable|numeric|min:0|max:90']);
        $affiliate->custom_rate = $data['custom_rate'];
        $affiliate->save();
        $this->logActivity('producer.affiliate.rate', "Afiliado #{$affiliate->id} comissão -> {$data['custom_rate']}%");

        return back()->with('success', 'Comissão atualizada.');
    }

    /** Define modo de afiliação do produto (automatic | manual) + regras. */
    public function updateMode(Request $request, Product $product): RedirectResponse
    {
        $this->myProductOrFail($product->id);
        $data = $request->validate([
            'affiliation_mode' => 'required|in:automatic,manual',
            'commission_rate' => 'nullable|numeric|min:0|max:90',
            'affiliation_rules' => 'nullable|string|max:5000',
        ]);
        $product->fill($data);
        $product->save();
        $this->logActivity('producer.affiliation.mode', "Produto #{$product->id} modo -> {$data['affiliation_mode']}");

        return back()->with('success', 'Regras de afiliação guardadas.');
    }
}
