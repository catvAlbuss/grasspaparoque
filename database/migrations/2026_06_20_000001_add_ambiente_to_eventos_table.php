<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->enum('tipo_ambiente', ['compartido', 'propio'])->default('propio')->after('tipo');
            $table->unsignedTinyInteger('ambiente_grupo')->nullable()->after('tipo_ambiente');
        });
    }

    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn(['tipo_ambiente', 'ambiente_grupo']);
        });
    }
};
