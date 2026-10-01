<?php

namespace App\Http\Controllers;

use App\Models\Bump;
use App\Models\Upsell;
use Illuminate\View\View;

class ToolsController extends Controller
{
    public function index(): View
    {
        $userId = auth()->id();
        $stats = [
            'bumps' => Bump::whereHas('mainProduct', fn ($q) => $q->where('producer_id', $userId))->count(),
            'upsells' => Upsell::whereHas('mainProduct', fn ($q) => $q->where('producer_id', $userId))->count(),
            'pixels' => \App\Models\Pixel::where('producer_id', $userId)->count(),
            'webhooks' => \App\Models\Webhook::where('producer_id', $userId)->count(),
        ];

        return view('tools.index', compact('stats'));
    }
}
