<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upsells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('main_product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('upsell_product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('special_price', 12, 2);
            $table->foreignId('downsell_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->decimal('downsell_price', 12, 2)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upsells');
    }
};
