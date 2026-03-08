<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | INFORMATIONS PRINCIPALES
            |--------------------------------------------------------------------------
            */

            $table->string('name')->index();
            $table->string('contact_person')->nullable();

            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable()->index();

            /*
            |--------------------------------------------------------------------------
            | INFORMATIONS LEGALES
            |--------------------------------------------------------------------------
            */

            $table->string('nif')->nullable()->index();
            $table->string('rc')->nullable()->index();

            /*
            |--------------------------------------------------------------------------
            | ADRESSE
            |--------------------------------------------------------------------------
            */

            $table->string('address')->nullable();
            $table->string('city')->nullable()->index();
            $table->string('country')->default('Mauritanie')->index();

            /*
            |--------------------------------------------------------------------------
            | FINANCIER
            |--------------------------------------------------------------------------
            */

            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0)->index();

            /*
            |--------------------------------------------------------------------------
            | STATUT
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')->default(true)->index();

            /*
            |--------------------------------------------------------------------------
            | NOTES
            |--------------------------------------------------------------------------
            */

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | INDEX ERP PERFORMANCE
            |--------------------------------------------------------------------------
            */

            $table->index(['name', 'is_active']);
            $table->index(['current_balance', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};