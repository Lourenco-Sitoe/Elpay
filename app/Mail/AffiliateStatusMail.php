<?php

namespace App\Mail;

use App\Models\Affiliate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AffiliateStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Affiliate $affiliate, public ?string $status = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Atualização da sua afiliação — Elpay');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.affiliate-status');
    }
}
