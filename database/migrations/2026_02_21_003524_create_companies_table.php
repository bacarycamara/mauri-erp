<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Informations principales
            |--------------------------------------------------------------------------
            */
            $table->string('name');
            $table->string('logo')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Informations légales
            |--------------------------------------------------------------------------
            */
            $table->string('nif')->nullable()->index();
            $table->string('rc')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Adresse
            |--------------------------------------------------------------------------
            */
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Mauritanie');

            /*
            |--------------------------------------------------------------------------
            | Paramètres financiers
            |--------------------------------------------------------------------------
            */
            $table->string('currency', 10)->default('MRU');
            $table->decimal('default_vat', 5, 2)->default(0); // Exemple: 18.00

            /*
            |--------------------------------------------------------------------------
            | Numérotation factures
            |--------------------------------------------------------------------------
            */
            $table->string('invoice_prefix')->default('FAC');
            $table->unsignedBigInteger('invoice_counter')->default(1);
            $table->string('invoice_format')->default('{prefix}-{number}');
            
            /*
            |--------------------------------------------------------------------------
            | Informations facture (PDF)
            |--------------------------------------------------------------------------
            */
            $table->text('invoice_footer')->nullable();
            $table->string('website')->nullable();
            $table->string('bank_account')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Paramètres système
            |--------------------------------------------------------------------------
            */
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};