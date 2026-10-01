<?php

namespace App\Mail;

use App\Models\KycVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class KycStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public KycVerification $kyc, public ?string $status = null, public ?string $comment = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Atualização do seu KYC — Elpay');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.kyc-status');
    }
}
