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
        Schema::create('clase_estudiantes', function (Blueprint $table) {
            $table->string('clave_clase',20);
            $table->string('matricula',20);
            $table->string('asistencia',4);
            $table->string('acreditado',3);
            $table->string('calificacion',4);
            $table->string('calificacion_letra',30);
            $table->string('retroalimentacion',80);

            $table->foreign('clave_clase')->references('clave_clase')->on('clases');
            $table->foreign('matricula')->references('matricula')->on('estudiantes');

            $table->softDeletes();
            $table->timestamps(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clase_estudiantes');
    }
};
