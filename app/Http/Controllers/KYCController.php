<?php

namespace App\Http\Controllers;

use App\Mail\KycStatusMail;
use App\Models\KycVerification;
use App\Models\Notification;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class KYCController extends Controller
{
    use LogsActivity;

    public function index(): View
    {
        $kyc = KycVerification::where('user_id', auth()->id())->latest()->first();

        return view('kyc.index', compact('kyc'));
    }

    public function create(): View
    {
        $kyc = KycVerification::where('user_id', auth()->id())->latest()->first();

        return view('kyc.create', compact('kyc'));
    }

    public function store(Request $request): RedirectResponse
    {
        // Uploads cortados pelo limite do servidor (post_max_size) chegam sem
        // ficheiros nenhuns — explica em vez de devolver 3 erros "required".
        if ($request->isMethod('post') && empty($request->allFiles()) && (int) ($request->server('CONTENT_LENGTH') ?? 0) > 0) {
            return back()
                ->withErrors(['document_front' => 'Os ficheiros são demasiado grandes para o servidor. Reduz o tamanho (máx. ' . number_format((config('uploads.max_doc_kb', 4096) / 1024), 0) . ' MB cada) e tenta de novo.'])
                ->withInput();
        }

        $data = $request->validate([
            'document_type' => 'required|in:bi,passport,dirigir',
            'document_front' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:'.config('uploads.max_doc_kb')],
            'document_back' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:'.config('uploads.max_doc_kb')],
            'selfie' => ['required', 'image', 'max:'.config('uploads.max_image_kb')],
        ]);

        $payload = [
            'user_id' => auth()->id(),
            'document_type' => $data['document_type'],
            'document_front' => $request->file('document_front')->store('kyc', 'public'),
            'document_back' => $request->file('document_back')->store('kyc', 'public'),
            'selfie' => $request->file('selfie')->store('kyc', 'public'),
            'status' => 'pending',
        ];

        $existing = KycVerification::where('user_id', auth()->id())->latest()->first();
        if ($existing) {
            $existing->update($payload);
        } else {
            KycVerification::create($payload);
        }

        $this->logActivity('kyc.submitted', 'KYC submetido.');

        return redirect()->route('kyc.index')->with('success', 'Documentos enviados. Aguarde aprovação.');
    }

    // ---- Admin ----

    public function adminApprove(int $id): RedirectResponse
    {
        $kyc = KycVerification::findOrFail($id);
        $kyc->status = 'approved';
        $kyc->reviewed_at = now();
        $kyc->save();

        $this->notifyKyc($kyc, 'approved');
        $this->logActivity('kyc.approved', 'KYC #'.$id.' aprovado.');

        return back()->with('success', 'KYC aprovado.');
    }

    public function adminReject(Request $request, int $id): RedirectResponse
    {
        $request->validate(['reason' => 'nullable|string|max:1000']);
        $kyc = KycVerification::findOrFail($id);
        $kyc->status = 'rejected';
        $kyc->admin_comment = $request->get('reason');
        $kyc->reviewed_at = now();
        $kyc->save();

        $this->notifyKyc($kyc, 'rejected', $request->get('reason'));
        $this->logActivity('kyc.rejected', 'KYC #'.$id.' rejeitado.');

        return back()->with('success', 'KYC rejeitado.');
    }

    public function adminComment(Request $request, int $id): RedirectResponse
    {
        $request->validate(['comment' => 'required|string|max:1000']);
        $kyc = KycVerification::findOrFail($id);
        $kyc->admin_comment = $request->comment;
        $kyc->save();

        $this->notifyKyc($kyc, $kyc->status ?? 'pending', $request->comment);

        return back()->with('success', 'Comentário adicionado.');
    }

    protected function notifyKyc($kyc, string $status, ?string $comment = null): void
    {
        try {
            Notification::create([
                'user_id' => $kyc->user_id,
                'title' => 'KYC '.$status,
                'message' => $comment ?? ('O seu KYC foi '.$status.'.'),
            ]);
        } catch (\Throwable) {
        }
        try {
            $user = $kyc->user;
            if ($user) {
                Mail::to($user->email)->send(new KycStatusMail($kyc, $status, $comment));
            }
        } catch (\Throwable) {
        }
    }
}
