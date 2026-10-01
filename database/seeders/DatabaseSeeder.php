<?php

namespace Database\Seeders;

use App\Models\Bump;
use App\Models\Coupon;
use App\Models\CourseModule;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Upsell;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@hotmartphp.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('admin123'),
                'type' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $p1 = User::firstOrCreate(
            ['email' => 'produtor1@elpay.co.mz'],
            ['name' => 'Produtor Um', 'password' => bcrypt('123456'), 'type' => 'producer', 'status' => 'active', 'email_verified_at' => now()]
        );
        $p2 = User::firstOrCreate(
            ['email' => 'produtor2@elpay.co.mz'],
            ['name' => 'Produtor Dois', 'password' => bcrypt('123456'), 'type' => 'producer', 'status' => 'active', 'email_verified_at' => now()]
        );
        User::firstOrCreate(
            ['email' => 'afiliado@elpay.co.mz'],
            ['name' => 'Afiliado', 'password' => bcrypt('123456'), 'type' => 'affiliate', 'status' => 'active', 'email_verified_at' => now()]
        );
        User::firstOrCreate(
            ['email' => 'cliente@elpay.co.mz'],
            ['name' => 'Cliente', 'password' => bcrypt('123456'), 'type' => 'client', 'status' => 'active', 'email_verified_at' => now()]
        );

        $data = [
            ['producer' => $p1, 'title' => 'Curso Completo de Marketing', 'type' => 'course', 'price' => 999, 'rate' => 30],
            ['producer' => $p1, 'title' => 'Ebook Vendas Digitais', 'type' => 'ebook', 'price' => 299, 'rate' => 20],
            ['producer' => $p2, 'title' => 'Acesso VIP Comunidade', 'type' => 'link', 'price' => 499, 'rate' => 10],
            ['producer' => $p2, 'title' => 'Pack Ficheiros Premium', 'type' => 'file', 'price' => 199, 'rate' => 15],
        ];

        $products = [];
        foreach ($data as $d) {
            $products[] = Product::firstOrCreate(
                ['slug' => Str::slug($d['title'])],
                [
                    'producer_id' => $d['producer']->id,
                    'title' => $d['title'],
                    'description' => 'Descrição de ' . $d['title'],
                    'price' => $d['price'],
                    'type' => $d['type'],
                    'commission_rate' => $d['rate'],
                    'status' => 'approved',
                ]
            );
        }

        Coupon::firstOrCreate(
            ['code' => 'PROMO10'],
            [
                'producer_id' => $p1->id,
                'product_id' => null,
                'percent' => 10,
                'active' => true,
            ]
        );

        Bump::firstOrCreate(
            ['main_product_id' => $products[0]->id, 'bump_product_id' => $products[1]->id],
            ['title' => 'Adicione o Ebook com desconto', 'price' => 199, 'active' => true]
        );

        Upsell::firstOrCreate(
            ['main_product_id' => $products[0]->id, 'upsell_product_id' => $products[2]->id],
            [
                'special_price' => 399,
                'downsell_product_id' => $products[3]->id,
                'downsell_price' => 149,
                'active' => true,
            ]
        );

        $defaults = [
            'platform_fee' => '5',
            'min_withdrawal' => '100',
            'withdrawal_fee_percent' => '2',
            'maintenance' => '0',
            'payment_driver' => 'mock',
        ];
        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        $courseModules = [
            ['Módulo 1 — Fundamentos', [['Boas-vindas', 'text'], ['Conceitos base', 'text']]],
            ['Módulo 2 — Prática', [['Primeira campanha', 'video'], ['Checklist final', 'file']]],
        ];
        foreach ($courseModules as $mi => [$title, $lessons]) {
            $mod = CourseModule::firstOrCreate(
                ['product_id' => $products[0]->id, 'title' => $title],
                ['position' => $mi + 1]
            );
            foreach ($lessons as $li => [$ltitle, $kind]) {
                $mod->lessons()->firstOrCreate(
                    ['title' => $ltitle],
                    ['kind' => $kind, 'body' => 'Conteúdo de ' . $ltitle, 'position' => $li + 1]
                );
            }
        }
    }
}
