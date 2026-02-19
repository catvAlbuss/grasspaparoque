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
            $table->foreignId('id_evento')->constrained('eventos')->cascadeOnDelete();
            $table->foreignId('id_user')->constrained('users')->cascadeOnDelete();
            $table->foreignId('id_pay')->constrained('pays')->cascadeOnDelete();
            $table->foreignId('id_customer')->constrained('customers')->cascadeOnDelete();
            $table->time('start_time');
            $table->time('end_time');
            $table->date('date');
            $table->enum('reservation_status', ['free', 'busy'])->default('free');
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
