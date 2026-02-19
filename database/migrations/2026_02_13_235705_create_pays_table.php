<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_products')->constrained('products')->cascadeOnDelete();
            $table->decimal('amount');
            $table->enum('payment_status', ['paid', 'pending', 'annulled'])->default('annulled');
            $table->enum('payment_method', ['card', 'yape', 'plin', 'cash'])->default('cash');
            $table->date('payment_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pays');
    }
};
