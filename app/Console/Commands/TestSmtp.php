<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestSmtp extends Command
{
    protected $signature = 'app:test-smtp {email?}';
    protected $description = 'Envia email de teste SMTP';

    public function handle(): int
    {
        $email = $this->argument('email') ?? config('mail.from.address');

        Mail::raw('Teste SMTP Elpay em ' . now()->toDateTimeString(), function ($m) use ($email) {
            $m->to($email)->subject('Teste SMTP — Elpay');
        });

        $this->info("Email de teste enviado para {$email}.");

        return self::SUCCESS;
    }
}
