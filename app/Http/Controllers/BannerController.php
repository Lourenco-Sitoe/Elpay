<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BannerController extends Controller
{
    use LogsActivity;

    public function index(): View
    {
        $banners = Banner::latest()->paginate(15);

        return view('admin.banners', compact('banners'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url|max:2048',
            'link_url' => 'nullable|url|max:2048',
            'position' => 'required|in:home_top,home_mid,checkout_top,app_home',
            'sort_order' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);
        Banner::create([...$data, 'active' => true]);
        $this->logActivity('admin.banner.created', 'Banner criado: '.$data['title']);

        return back()->with('success', 'Banner criado.');
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url|max:2048',
            'link_url' => 'nullable|url|max:2048',
            'position' => 'required|in:home_top,home_mid,checkout_top,app_home',
            'sort_order' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);
        $banner->fill($data);
        $banner->save();

        return back()->with('success', 'Banner atualizado.');
    }

    public function toggle(Banner $banner): RedirectResponse
    {
        $banner->active = ! $banner->active;
        $banner->save();

        return back()->with('success', 'Banner '.($banner->active ? 'ativado' : 'desativado').'.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $banner->delete();
        $this->logActivity('admin.banner.deleted', "Banner #{$banner->id} removido.");

        return back()->with('success', 'Banner removido.');
    }
}
