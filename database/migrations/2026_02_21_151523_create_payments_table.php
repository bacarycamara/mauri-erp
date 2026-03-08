<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | NUMÉRO ERP UNIQUE
            |--------------------------------------------------------------------------
            */
            $table->string('payment_number')->unique();

            /*
            |--------------------------------------------------------------------------
            | DOCUMENT SOURCE (ACHAT OU VENTE)
            |--------------------------------------------------------------------------
            */
            $table->foreignId('purchase_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('sale_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | CAISSE ACTIVE
            |--------------------------------------------------------------------------
            */
            $table->foreignId('cash_register_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | TYPE
            |--------------------------------------------------------------------------
            | in  = paiement client
            | out = paiement fournisseur
            |--------------------------------------------------------------------------
            */
            $table->enum('type', ['in', 'out'])->index();

            /*
            |--------------------------------------------------------------------------
            | MONTANT
            |--------------------------------------------------------------------------
            */
            $table->decimal('amount', 15, 2);

            /*
            |--------------------------------------------------------------------------
            | MÉTHODE
            |--------------------------------------------------------------------------
            */
            $table->enum('payment_method', [
                'cash',
                'bank_transfer',
                'mobile_money',
                'check',
                'other'
            ])->index();

            /*
            |--------------------------------------------------------------------------
            | STATUT
            |--------------------------------------------------------------------------
            */
            $table->enum('status', [
                'pending',
                'confirmed',
                'cancelled'
            ])->default('confirmed')->index();

            /*
            |--------------------------------------------------------------------------
            | DATE PAIEMENT
            |--------------------------------------------------------------------------
            */
            $table->date('payment_date')->index();

            /*
            |--------------------------------------------------------------------------
            | RÉFÉRENCE & NOTES
            |--------------------------------------------------------------------------
            */
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | INDEXES ERP PERFORMANCE
            |--------------------------------------------------------------------------
            */
            $table->index(['purchase_id', 'status']);
            $table->index(['sale_id', 'status']);
            $table->index(['type', 'payment_date']);
            $table->index(['cash_register_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};