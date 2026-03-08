<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | INFORMATIONS PRINCIPALES
            |--------------------------------------------------------------------------
            */
            $table->string('name')->index();
            $table->string('slug')->unique();

            /*
            |--------------------------------------------------------------------------
            | HIERARCHIE
            |--------------------------------------------------------------------------
            */
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('categories')
                  ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | DESCRIPTION & IMAGE
            |--------------------------------------------------------------------------
            */
            $table->text('description')->nullable();
            $table->string('image')->nullable();

            /*
            |--------------------------------------------------------------------------
            | ORGANISATION
            |--------------------------------------------------------------------------
            */
            $table->integer('position')->default(0);
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
            | INDEX OPTIMISATION ERP
            |--------------------------------------------------------------------------
            */
            $table->index(['parent_id', 'is_active']);
            $table->index(['name', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};