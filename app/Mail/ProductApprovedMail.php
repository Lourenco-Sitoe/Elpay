<?php

namespace App\Mail;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProductApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Product $product, public ?string $status = null, public ?string $reason = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: ($this->status === 'rejected' ? 'Produto não aprovado — Elpay' : 'Produto aprovado — Elpay'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.product-approved');
    }
}
