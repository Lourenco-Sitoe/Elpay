<?php

namespace App\Http\Controllers;

use App\Models\Pixel;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PixelController extends Controller
{
    use LogsActivity;

    public function index(): View
    {
        $pixels = Pixel::where('producer_id', auth()->id())->latest()->paginate(15);

        return view('pixels.index', compact('pixels'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'platform' => 'required|in:facebook,tiktok,google',
            'pixel_id' => 'required|string|max:100',
            'active' => 'nullable|boolean',
        ]);

        Pixel::create([
            'producer_id' => auth()->id(),
            'name' => $data['name'],
            'platform' => $data['platform'],
            'pixel_id' => $data['pixel_id'],
            'active' => $data['active'] ?? true,
        ]);

        return back()->with('success', 'Pixel adicionado.');
    }

    public function update(Request $request, Pixel $pixel): RedirectResponse
    {
        $this->own($pixel);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'platform' => 'required|in:facebook,tiktok,google',
            'pixel_id' => 'required|string|max:100',
            'active' => 'nullable|boolean',
        ]);
        $pixel->update([
            'name' => $data['name'],
            'platform' => $data['platform'],
            'pixel_id' => $data['pixel_id'],
            'active' => $data['active'] ?? $pixel->active,
        ]);

        return back()->with('success', 'Pixel atualizado.');
    }

    public function destroy(Pixel $pixel): RedirectResponse
    {
        $this->own($pixel);
        $pixel->delete();

        return back()->with('success', 'Pixel removido.');
    }

    public function toggle(Pixel $pixel): RedirectResponse
    {
        $this->own($pixel);
        $pixel->active = ! $pixel->active;
        $pixel->save();

        return back()->with('success', 'Pixel ' . ($pixel->active ? 'ativado' : 'desativado') . '.');
    }

    protected function own(Pixel $pixel): void
    {
        abort_unless((int) $pixel->producer_id === (int) auth()->id() || (auth()->user()->type ?? '') === 'admin', 403);
    }
}
