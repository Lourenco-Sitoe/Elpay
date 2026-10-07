<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('banners')) {
            Schema::create('banners', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('image_url', 2048)->nullable();
                $table->string('link_url', 2048)->nullable();
                $table->string('position', 50)->default('home_top');
                $table->boolean('active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('affiliate_materials')) {
            Schema::create('affiliate_materials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('title');
                $table->string('kind', 30)->default('text');
                $table->text('content')->nullable();
                $table->string('file_url', 2048)->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('course_quizzes')) {
            Schema::create('course_quizzes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lesson_id')->constrained('course_lessons')->cascadeOnDelete();
                $table->string('title');
                $table->json('questions')->nullable();
                $table->unsignedTinyInteger('passing_score')->default(70);
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('quiz_attempts')) {
            Schema::create('quiz_attempts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quiz_id')->constrained('course_quizzes')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedTinyInteger('score')->default(0);
                $table->boolean('passed')->default(false);
                $table->json('answers')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('course_quizzes');
        Schema::dropIfExists('affiliate_materials');
        Schema::dropIfExists('banners');
    }
};
