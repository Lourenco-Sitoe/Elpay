<?php

namespace App\Services\Payments;

class PaymentResult
{
    public function __construct(
        public string $status, // approved|pending|failed
        public ?string $reference = null,
        public ?string $message = null,
    ) {}

    public function approved(): bool
    {
        return $this->status === 'approved';
    }

    public function pending(): bool
    {
        return $this->status === 'pending';
    }
}
