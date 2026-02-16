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
        Schema::create('products', function (Blueprint $prod) {
            $prod->id();
            $prod -> string('name');
            $prod -> text('description');
            $prod -> integer('stock');
            $prod -> decimal('price_unit', 10,2);
            $prod -> decimal('price_higher', 10,2);
            $prod -> date('expiration_date');
            $prod->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
