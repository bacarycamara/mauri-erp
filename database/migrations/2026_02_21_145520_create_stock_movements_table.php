<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | RELATION PRODUIT
            |--------------------------------------------------------------------------
            */
            $table->foreignId('product_id')
                  ->constrained()
                  ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | TYPE MOUVEMENT
            |--------------------------------------------------------------------------
            */
            $table->enum('type', [
                'purchase',     // entrée achat
                'sale',         // sortie vente
                'adjustment',   // correction manuelle
                'return',       // retour
            ])->index();

            /*
            |--------------------------------------------------------------------------
            | QUANTITÉ
            |--------------------------------------------------------------------------
            */
            $table->decimal('quantity', 15, 2);

            /*
            |--------------------------------------------------------------------------
            | STOCK AVANT / APRES
            |--------------------------------------------------------------------------
            */
            $table->decimal('stock_before', 15, 2);
            $table->decimal('stock_after', 15, 2);

            /*
            |--------------------------------------------------------------------------
            | RÉFÉRENCE DOCUMENT
            |--------------------------------------------------------------------------
            */
            $table->string('reference')->nullable(); 
            // ex: ACH-0001 ou VNT-0001

            /*
            |--------------------------------------------------------------------------
            | NOTES
            |--------------------------------------------------------------------------
            */
            $table->text('notes')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEX PERFORMANCE
            |--------------------------------------------------------------------------
            */
            $table->index(['product_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};