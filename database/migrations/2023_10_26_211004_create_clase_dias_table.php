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
        Schema::create('clase_dias', function (Blueprint $table) {
            $table->string('clave_clase',20);
            $table->string('id_dia',3);

            $table->foreign('clave_clase')->references('clave_clase')->on('clases');
            $table->foreign('id_dia')->references('id_dia')->on('dias');

            $table->softDeletes();
            $table->timestamps(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clase_dias');
    }
};
