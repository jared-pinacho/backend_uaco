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
        Schema::create('estado_documentacions', function (Blueprint $table) {
            $table->bigIncrements('id_documento');
            $table->string('matricula',20);
            $table->boolean('certificado_terminacion_estudios');
            $table->boolean('acta_examen');
            $table->boolean('titulo_electronico');
            $table->boolean('liberacion_servicio_social');

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
        Schema::dropIfExists('estado_documentacions');
    }
};
