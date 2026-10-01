<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function mine(): View
    {
        $certificates = Certificate::with('product')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(12);

        return view('certificates.mine', compact('certificates'));
    }

    public function verify(string $code): View
    {
        $certificate = Certificate::with(['user', 'product'])
            ->where('code', $code)
            ->firstOrFail();

        return view('certificates.verify', compact('certificate'));
    }
}
