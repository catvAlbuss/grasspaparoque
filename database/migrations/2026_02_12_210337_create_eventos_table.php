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
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            // $table->foreingId('id_eventos')->constrained('eventos')->ondDelete('cascade');
            // $table->foreingId('user_id')->constrained('users')->ondDelete('cascade');
            // $table->enum('estado_pago',['pagado','pend'])->default('rechazado');
            $table->string('nombre');
            $table->decimal('precio', 10,2);
            $table->text('descripcion');
            $table->boolean('estado')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Pay');
    }
};
