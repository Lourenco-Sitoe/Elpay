<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Seed mínimo para produção: admin inicial + configurações essenciais.
     * Sem dados de demonstração. Corre com:
     *   php artisan db:seed --class=Database\\Seeders\\ProductionSeeder --force
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $password = env('ADMIN_PASSWORD');

        if (! $password || strlen($password) < 12) {
            $this->command->error('Define ADMIN_EMAIL e ADMIN_PASSWORD (mín. 12 caracteres) no .env antes do seed.');

            return;
        }

        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'password' => Hash::make($password),
                'type' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $defaults = [
            'platform_fee_percent' => '5',
            'default_commission_rate' => '10',
            'min_withdrawal' => '100',
            'withdrawal_fee_percent' => '2',
            'payment_driver' => env('PAYMENT_DRIVER', 'mock'),
            'mpesa_enabled' => '1',
            'emola_enabled' => '1',
            'site_name' => env('APP_NAME', 'Elpay'),
            'support_email' => env('MAIL_FROM_ADDRESS', 'suporte@example.com'),
            'maintenance' => '0',
        ];
        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        $this->command->info("Admin '{$email}' pronto + ".count($defaults).' settings.');
    }
}
