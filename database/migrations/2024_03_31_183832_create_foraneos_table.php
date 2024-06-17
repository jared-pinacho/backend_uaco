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
        Schema::create('foraneos', function (Blueprint $table) {
            $table->bigIncrements('id_foraneo');
            $table->string('nombre',40);
            $table->string('apellido_paterno',40);
            $table->string('apellido_materno',40);
            $table->string('edad',3);
            $table->string('sexo',2);
            $table->string('telefono',15);
            $table->string('correo',40);
            $table->string('semestre',2);
            $table->string('discapacidad',60);
            $table->string('institucion',60);
            $table->string('matricula_escolar',20);
            $table->string('licenciatura',60);
            $table->string('programa',60);
            $table->string('titular_dep',100);
            $table->string('cargo_titular',200);
            $table->string('grado_titular',20);
            $table->string('resp_seg',200);
            $table->string('CUC',100);
            $table->date('fecha_inicio');
            $table->string('horas',4);
            $table->date('fecha_final');
            $table->string('matricula');
            $table->integer('estatus')->default(0);
            $table->bigInteger('id_lenguaindigena')->unsigned();

            $table->foreign('matricula')->references('matricula')->on('escolares');
            $table->foreign('id_lenguaindigena')->references('id_lenguaindigena')->on('lenguas_indigenas');
            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foraneos');
    }
};
