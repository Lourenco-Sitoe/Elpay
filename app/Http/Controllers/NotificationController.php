<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = Notification::where('user_id', auth()->id())->latest()->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(int $id): RedirectResponse
    {
        $n = Notification::where('user_id', auth()->id())->findOrFail($id);
        $n->read_at = now();
        $n->save();

        return back();
    }

    public function markAllRead(): RedirectResponse
    {
        Notification::where('user_id', auth()->id())->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'Todas marcadas como lidas.');
    }
}
