<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'specialty')) {
                $table->string('specialty')->nullable()->after('avatar');
            }
            if (! Schema::hasColumn('users', 'locale')) {
                $table->string('locale', 10)->default('pt')->after('specialty');
            }
            if (! Schema::hasColumn('users', 'theme')) {
                $table->string('theme', 10)->default('light')->after('locale');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['theme', 'locale', 'specialty'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
