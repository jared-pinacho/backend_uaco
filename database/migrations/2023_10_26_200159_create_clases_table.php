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
        Schema::create('clases', function (Blueprint $table) {
            $table->string('clave_clase',20)->primary();
            $table->string('nombre',40);
            $table->string('salon',50);
            $table->bigInteger('id_periodo')->unsigned();
            $table->string('clave_materia',10);
            $table->string('matricula',20);
            $table->string('hora_inicio',10);
            $table->string('hora_final',10);
            $table->string('clave_cuc',20);
            $table->string('clave_carrera',20);
            $table->boolean('status_escolar');
            $table->boolean('status_facilitador');

            $table->foreign('id_periodo')->references('id_periodo')->on('periodos');
            $table->foreign('clave_materia')->references('clave_materia')->on('materias');
            $table->foreign('matricula')->references('matricula')->on('facilitadores');
            $table->foreign('clave_cuc')->references('clave_cuc')->on('cucs');
            $table->foreign('clave_carrera')->references('clave_carrera')->on('carreras');


            $table->softDeletes();
            $table->timestamps(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clases');
    }
};
