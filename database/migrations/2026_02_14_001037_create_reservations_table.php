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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_evento')->constrained('eventos')->ondDelete('cascade');
            $table->foreignId('id_user')->constrained('users')->ondDelete('cascade');
            $table->foreignId('id_pay')->constrained('pay')->ondDelete('cascade');

            $table->decimal('amount');
            $table->enum('payment_status', ['paid', 'pending', 'annulled'])->default('annulled');
            $table->enum('payment_method', ['card', 'yape', 'plin', 'cash'])->default('cash');
            $table->timestamp('payment_date');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
