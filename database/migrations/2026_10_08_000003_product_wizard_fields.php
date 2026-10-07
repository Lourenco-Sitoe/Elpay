<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'delivery')) {
                $table->string('delivery', 20)->default('internal')->after('external_url');
            }
            if (! Schema::hasColumn('products', 'warranty_days')) {
                $table->unsignedSmallInteger('warranty_days')->default(7)->after('delivery');
            }
            if (! Schema::hasColumn('products', 'payment_methods')) {
                $table->json('payment_methods')->nullable()->after('warranty_days');
            }
            if (! Schema::hasColumn('products', 'support_email')) {
                $table->string('support_email')->nullable()->after('payment_methods');
            }
            if (! Schema::hasColumn('products', 'support_phone')) {
                $table->string('support_phone', 30)->nullable()->after('support_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            foreach (['support_phone', 'support_email', 'payment_methods', 'warranty_days', 'delivery'] as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
