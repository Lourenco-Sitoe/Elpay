<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // O SQLite (usado nos testes) não impõe ENUM nem suporta MODIFY COLUMN:
        // a validação dos novos valores é feita na aplicação.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Formatos: adiciona "app" (aplicação ou software) aos existentes.
        DB::statement("ALTER TABLE products MODIFY COLUMN type ENUM('course','ebook','file','link','app') NOT NULL");
        // Estados estilo Tutora: draft (Rascunho), pending (Em análise), approved (Ativo),
        // rejected (Recusado), suspended (Suspenso), banned (Banido).
        DB::statement("ALTER TABLE products MODIFY COLUMN status ENUM('draft','pending','approved','rejected','suspended','banned') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("UPDATE products SET status = 'pending' WHERE status IN ('draft','suspended','banned')");
        DB::statement("UPDATE products SET type = 'file' WHERE type = 'app'");
        DB::statement("ALTER TABLE products MODIFY COLUMN status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE products MODIFY COLUMN type ENUM('course','ebook','link','file') NOT NULL");
    }
};
