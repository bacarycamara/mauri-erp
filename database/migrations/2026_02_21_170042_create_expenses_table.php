<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Informations principales
            |--------------------------------------------------------------------------
            */

            $table->string('reference')->unique();
            $table->string('category')->nullable()->index();
            $table->date('expense_date')->index();

            /*
            |--------------------------------------------------------------------------
            | Montant
            |--------------------------------------------------------------------------
            */

            $table->decimal('amount', 15, 2)->index();

            /*
            |--------------------------------------------------------------------------
            | Paiement
            |--------------------------------------------------------------------------
            */

            $table->enum('payment_method', [
                'cash',
                'bank_transfer',
                'mobile_money',
                'check',
                'other'
            ])->default('cash')->index();

            /*
            |--------------------------------------------------------------------------
            | Lien caisse
            |--------------------------------------------------------------------------
            */

            $table->foreignId('cash_register_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Statut
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'pending',
                'approved',
                'cancelled'
            ])->default('approved')->index();

            /*
            |--------------------------------------------------------------------------
            | Description
            |--------------------------------------------------------------------------
            */

            $table->text('notes')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | INDEX ERP PERFORMANCE
            |--------------------------------------------------------------------------
            */

            $table->index(['expense_date', 'status']);
            $table->index(['category', 'status']);
            $table->index(['status', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};