<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    use LogsActivity;

    public function index(): View
    {
        $coupons = Coupon::where('producer_id', auth()->id())->latest()->paginate(15);
        $myProducts = \App\Models\Product::where('producer_id', auth()->id())->orderBy('title')->get(['id', 'title']);

        return view('coupons.index', compact('coupons', 'myProducts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'percent' => 'required|numeric|min:1|max:90',
            'product_id' => 'nullable|exists:products,id',
            'active' => 'nullable|boolean',
            'expires_at' => 'nullable|date',
            'max_uses' => 'nullable|integer|min:1',
        ]);

        Coupon::create([
            'producer_id' => auth()->id(),
            'product_id' => $this->ownProductId($data['product_id'] ?? null),
            'code' => strtoupper($data['code']),
            'percent' => $data['percent'],
            'active' => $data['active'] ?? true,
            'expires_at' => $data['expires_at'] ?? null,
            'max_uses' => $data['max_uses'] ?? null,
            'used_count' => 0,
        ]);

        return redirect()->route('coupons.index')->with('success', 'Cupom criado.');
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $this->authorizeOwner($coupon);
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'percent' => 'required|numeric|min:1|max:90',
            'product_id' => 'nullable|exists:products,id',
            'active' => 'nullable|boolean',
            'expires_at' => 'nullable|date',
            'max_uses' => 'nullable|integer|min:1',
        ]);

        $coupon->update([
            'code' => strtoupper($data['code']),
            'percent' => $data['percent'],
            'product_id' => $this->ownProductId($data['product_id'] ?? null),
            'active' => $data['active'] ?? $coupon->active,
            'expires_at' => $data['expires_at'] ?? null,
            'max_uses' => $data['max_uses'] ?? null,
        ]);

        return redirect()->route('coupons.index')->with('success', 'Cupom atualizado.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $this->authorizeOwner($coupon);
        $coupon->delete();

        return redirect()->route('coupons.index')->with('success', 'Cupom eliminado.');
    }

    public function toggle(Coupon $coupon): RedirectResponse
    {
        $this->authorizeOwner($coupon);
        $coupon->active = ! $coupon->active;
        $coupon->save();

        return back()->with('success', 'Estado do cupom atualizado.');
    }

    protected function authorizeOwner(Coupon $coupon): void
    {
        if ((int) $coupon->producer_id !== (int) auth()->id() && (auth()->user()->type ?? '') !== 'admin') {
            abort(403);
        }
    }

    protected function ownProductId(?int $productId): ?int
    {
        if (! $productId) {
            return null;
        }
        $product = \App\Models\Product::findOrFail($productId);
        abort_unless(
            (auth()->user()->type ?? '') === 'admin' || (int) $product->producer_id === (int) auth()->id(),
            403
        );

        return $productId;
    }
}
