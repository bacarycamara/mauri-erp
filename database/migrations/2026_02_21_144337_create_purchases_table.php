<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | RELATION FOURNISSEUR
            |--------------------------------------------------------------------------
            */

            $table->foreignId('supplier_id')
                  ->constrained()
                  ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | INFORMATIONS FACTURE
            |--------------------------------------------------------------------------
            */

            $table->string('reference')->unique(); // ex: ACH-0001
            $table->date('purchase_date')->index();

            /*
            |--------------------------------------------------------------------------
            | MONTANTS
            |--------------------------------------------------------------------------
            */

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);

            $table->decimal('total_amount', 15, 2)->default(0)->index();
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('due_amount', 15, 2)->default(0)->index();

            /*
            |--------------------------------------------------------------------------
            | STATUT
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'draft',
                'confirmed',
                'partial',
                'paid',
                'cancelled'
            ])->default('draft')->index();

            /*
            |--------------------------------------------------------------------------
            | NOTES
            |--------------------------------------------------------------------------
            */

            $table->text('notes')->nullable();

            /*
            |--------------------------------------------------------------------------
            | TIMESTAMPS
            |--------------------------------------------------------------------------
            */

            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | INDEX ERP PERFORMANCE
            |--------------------------------------------------------------------------
            */

            $table->index(['supplier_id', 'status']);
            $table->index(['purchase_date', 'status']);
            $table->index(['status', 'purchase_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};