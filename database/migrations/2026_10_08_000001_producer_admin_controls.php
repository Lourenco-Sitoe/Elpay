<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'affiliation_mode')) {
                $table->string('affiliation_mode', 20)->default('automatic')->after('commission_rate');
            }
            if (! Schema::hasColumn('products', 'affiliation_rules')) {
                $table->text('affiliation_rules')->nullable()->after('affiliation_mode');
            }
            if (! Schema::hasColumn('products', 'category')) {
                $table->string('category', 100)->nullable()->after('type');
            }
            if (! Schema::hasColumn('products', 'currency')) {
                $table->string('currency', 10)->default('MZN')->after('price');
            }
            if (! Schema::hasColumn('products', 'sales_page_url')) {
                $table->string('sales_page_url', 2048)->nullable()->after('external_url');
            }
        });

        Schema::table('affiliates', function (Blueprint $table) {
            if (! Schema::hasColumn('affiliates', 'clicks')) {
                $table->unsignedBigInteger('clicks')->default(0)->after('total_sales');
            }
        });
    }

    public function down(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            if (Schema::hasColumn('affiliates', 'clicks')) {
                $table->dropColumn('clicks');
            }
        });
        Schema::table('products', function (Blueprint $table) {
            foreach (['sales_page_url', 'currency', 'category', 'affiliation_rules', 'affiliation_mode'] as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
