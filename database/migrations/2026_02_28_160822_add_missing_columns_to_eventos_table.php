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
        Schema::table('eventos', function (Blueprint $table) {
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing('eventos');

            if (!in_array('nombre', $columns)) {
                $table->string('nombre')->after('id');
            }
            if (!in_array('precio', $columns)) {
                $table->decimal('precio', 10, 2)->default(0)->after('nombre');
            }
            if (!in_array('descripcion', $columns)) {
                $table->text('descripcion')->nullable()->after('precio');
            }
            if (!in_array('estado', $columns)) {
                $table->enum('estado', ['free', 'busy'])->default('free')->after('descripcion');
            }
            if (!in_array('tipo', $columns)) {
                $table->string('tipo')->nullable()->after('nombre');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn(['nombre', 'precio', 'descripcion', 'estado', 'tipo']);
        });
    }
};
