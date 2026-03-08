<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | CLIENT
            |--------------------------------------------------------------------------
            */

            $table->foreignId('customer_id')
                  ->constrained()
                  ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | FACTURE
            |--------------------------------------------------------------------------
            */

            $table->string('reference')->unique(); // ex: VNT-0001
            $table->date('sale_date')->index();

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

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | INDEX ERP (PERFORMANCE)
            |--------------------------------------------------------------------------
            */

            $table->index(['customer_id', 'status']);
            $table->index(['sale_date', 'status']);
            $table->index(['status', 'sale_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};