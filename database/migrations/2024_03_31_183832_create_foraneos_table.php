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
            $table->string('telefono',15);
            $table->string('correo',40);
            $table->string('semestre',2);
            $table->string('discapacidad',20);
            $table->string('lengua',20);
            $table->string('institucion',20);
            $table->string('matricula_escolar',20);
            $table->string('licenciatura',20);
            $table->string('programa',20);
            $table->string('titular_dep',100);
            $table->string('cargo_titular',100);
            $table->string('grado_titular',20);
            $table->string('resp_seg',100);
            $table->string('CUC',100);
            $table->date('fecha_inicio');
            $table->date('fecha_final');
            $table->string('matricula');


            $table->foreign('matricula')->references('matricula')->on('escolares');

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
