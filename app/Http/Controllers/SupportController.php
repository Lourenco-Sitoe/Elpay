<?php

namespace App\Http\Controllers;

use App\Models\SupportMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(): View
    {
        $messages = SupportMessage::where('user_id', auth()->id())->latest()->paginate(10);

        return view('support.index', compact('messages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        SupportMessage::create([
            'user_id' => auth()->id(),
            'subject' => $data['subject'],
            'message' => $data['message'],
            'status' => 'open',
        ]);

        return back()->with('success', 'Mensagem enviada. Responderemos em breve.');
    }

    public function show(SupportMessage $message): View
    {
        abort_unless((int) $message->user_id === (int) auth()->id() || (auth()->user()->type ?? '') === 'admin', 403);

        return view('support.show', compact('message'));
    }
}
