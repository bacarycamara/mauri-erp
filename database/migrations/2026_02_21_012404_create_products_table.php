<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | INFORMATIONS PRINCIPALES
            |--------------------------------------------------------------------------
            */

            $table->string('name')->index();
            $table->string('sku')->unique()->index();
            $table->string('barcode')->nullable()->index();
            $table->string('photo')->nullable();

            /*
            |--------------------------------------------------------------------------
            | CATEGORIE
            |--------------------------------------------------------------------------
            */

            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete()
                  ->index();

            /*
            |--------------------------------------------------------------------------
            | TYPE PRODUIT
            |--------------------------------------------------------------------------
            */

            $table->enum('type', ['physical', 'service'])
                  ->default('physical')
                  ->index();

            /*
            |--------------------------------------------------------------------------
            | UNITE
            |--------------------------------------------------------------------------
            */

            $table->string('unit')->default('Pièce');

            /*
            |--------------------------------------------------------------------------
            | DESCRIPTION
            |--------------------------------------------------------------------------
            */

            $table->text('description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | PRIX & TVA
            |--------------------------------------------------------------------------
            */

            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->index();
            $table->decimal('profit_margin', 8, 2)->nullable();

            $table->decimal('vat_rate', 5, 2)->nullable();

            /*
            |--------------------------------------------------------------------------
            | STOCK
            |--------------------------------------------------------------------------
            */

            $table->integer('stock_quantity')->default(0)->index();
            $table->integer('minimum_stock')->default(0)->index();

            /*
            |--------------------------------------------------------------------------
            | FOURNISSEUR
            |--------------------------------------------------------------------------
            */

            $table->string('supplier_reference')->nullable()->index();

            /*
            |--------------------------------------------------------------------------
            | STATUT
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')->default(true)->index();

            /*
            |--------------------------------------------------------------------------
            | TRACKING
            |--------------------------------------------------------------------------
            */

            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | INDEX ERP PERFORMANCE
            |--------------------------------------------------------------------------
            */

            $table->index(['name', 'is_active']);
            $table->index(['category_id', 'is_active']);
            $table->index(['stock_quantity', 'minimum_stock']);
            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};