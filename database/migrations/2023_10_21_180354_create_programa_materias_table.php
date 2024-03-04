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
        Schema::create('programa_materias', function (Blueprint $table) {
            $table->string('clave_carrera',20);
            $table->string('clave_materia',10);

            $table->foreign('clave_carrera')->references('clave_carrera')->on('carreras');
            $table->foreign('clave_materia')->references('clave_materia')->on('materias');

            $table->softDeletes();
            $table->timestamps(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programa_materias');
    }
};
