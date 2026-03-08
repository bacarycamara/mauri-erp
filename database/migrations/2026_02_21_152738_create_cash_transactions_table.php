<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_transactions', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | RELATIONS
            |--------------------------------------------------------------------------
            */

            $table->foreignId('cash_register_id')
                  ->constrained('cash_registers')
                  ->cascadeOnDelete();

            $table->foreignId('payment_id')
                  ->nullable()
                  ->constrained('payments')
                  ->nullOnDelete();

            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | TRANSACTION TYPE
            |--------------------------------------------------------------------------
            | in  = entrée
            | out = sortie
            |--------------------------------------------------------------------------
            */

            $table->string('type', 10); // Plus flexible que ENUM

            /*
            |--------------------------------------------------------------------------
            | FINANCIAL DATA
            |--------------------------------------------------------------------------
            */

            $table->decimal('amount', 15, 2);

            $table->string('reference', 100)->nullable();
            $table->string('description', 255)->nullable();

            /*
            |--------------------------------------------------------------------------
            | SOURCE MODULE (ERP FLEXIBLE)
            |--------------------------------------------------------------------------
            | sale
            | purchase
            | payment
            | expense
            | transfer
            | adjustment
            | manual
            |--------------------------------------------------------------------------
            */

            $table->string('source', 50)->default('manual');

            /*
            |--------------------------------------------------------------------------
            | OPTIONAL: polymorphic future ready (commented if needed later)
            |--------------------------------------------------------------------------
            */
            // $table->string('source_type')->nullable();
            // $table->unsignedBigInteger('source_id')->nullable();

            /*
            |--------------------------------------------------------------------------
            | AUDIT
            |--------------------------------------------------------------------------
            */

            $table->softDeletes();
            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEX OPTIMISATION (ERP PERFORMANCE)
            |--------------------------------------------------------------------------
            */

            $table->index(['cash_register_id', 'type']);
            $table->index('reference');
            $table->index('source');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};