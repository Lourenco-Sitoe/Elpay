<?php

namespace App\Console\Commands;

use App\Models\Commission;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductAccess;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SimulateSales extends Command
{
    protected $signature = 'simulate:sales {--count=10}';
    protected $description = 'Cria vendas fake com produtos aprovados';

    public function handle(): int
    {
        $count = (int) $this->option('count');
        $products = Product::where('status', 'approved')->get();

        if ($products->isEmpty()) {
            $this->error('Sem produtos aprovados.');

            return self::FAILURE;
        }

        $affiliate = User::where('type', 'affiliate')->first();

        for ($i = 0; $i < $count; $i++) {
            /** @var Product $product */
            $product = $products->random();
            $useAffiliate = $affiliate && rand(0, 1);
            $buyerName = fake()->name();
            $buyerEmail = fake()->unique()->safeEmail();

            $buyer = User::firstOrCreate(
                ['email' => $buyerEmail],
                [
                    'name' => $buyerName,
                    'password' => bcrypt('123456'),
                    'type' => 'client',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            $sale = Sale::create([
                'product_id' => $product->id,
                'buyer_id' => $buyer->id,
                'buyer_name' => $buyer->name,
                'buyer_email' => $buyer->email,
                'buyer_phone' => '84' . rand(1000000, 9999999),
                'producer_id' => $product->producer_id,
                'affiliate_id' => $useAffiliate ? $affiliate->id : null,
                'price' => $product->price,
                'discount' => 0,
                'final_price' => $product->price,
                'payment_method' => collect(['mpesa', 'emola'])->random(),
                'status' => 'paid',
                'affiliate_code' => $useAffiliate ? 'SIM' . rand(1000, 9999) : null,
            ]);

            Payment::create([
                'sale_id' => $sale->id,
                'amount' => $sale->final_price,
                'method' => $sale->payment_method,
                'status' => 'approved',
                'transaction_ref' => strtoupper(Str::random(10)),
                'phone' => $sale->buyer_phone,
                'approved_at' => now(),
            ]);

            ProductAccess::firstOrCreate([
                'user_id' => $buyer->id,
                'product_id' => $product->id,
                'sale_id' => $sale->id,
            ]);

            $rate = (float) $product->commission_rate;
            if ($useAffiliate) {
                $amount = commissionValue($sale->final_price, $rate);
                Commission::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'affiliate_id' => $affiliate->id,
                    'rate' => $rate,
                    'amount' => $amount,
                    'status' => 'pending',
                ]);
                $affiliate->increment('balance', $amount);
            }

            $producer = User::find($product->producer_id);
            $producer?->increment('balance', (float) $sale->final_price);
            $product->increment('sales_count');
        }

        $this->info("{$count} vendas simuladas.");

        return self::SUCCESS;
    }
}
