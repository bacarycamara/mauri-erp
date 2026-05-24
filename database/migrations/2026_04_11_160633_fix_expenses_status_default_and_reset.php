<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ----------------------------------------------------------------
        // 1. Changer le statut par défaut : 'approved' → 'pending'
        // ----------------------------------------------------------------
        DB::statement("
            ALTER TABLE expenses 
            MODIFY COLUMN status ENUM('pending','approved','cancelled') 
            NOT NULL DEFAULT 'pending'
        ");

        // ----------------------------------------------------------------
        // 2. Remettre en 'pending' toutes les dépenses qui ont été
        //    auto-approuvées sans validation humaine.
        //    (celles créées avant ce correctif)
        // ----------------------------------------------------------------
        DB::table('expenses')
            ->where('status', 'approved')
            ->update(['status' => 'pending']);
    }

    public function down(): void
    {
        // Revenir à l'ancien comportement (déconseillé)
        DB::statement("
            ALTER TABLE expenses 
            MODIFY COLUMN status ENUM('pending','approved','cancelled') 
            NOT NULL DEFAULT 'approved'
        ");
    }
};