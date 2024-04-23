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
        Schema::create('servicios', function (Blueprint $table) {
           
            $table->bigIncrements('id_servicio');
            $table->string('modalidad',20);
            $table->string('tipo_dep',20);
            $table->string('nombre_dep',100);
            $table->string('titular_dep',100);
            $table->string('cargo_tit',40);
            $table->string('grado_tit',20);
            $table->string('responsable',100);
            $table->string('programa',100);
            $table->string('actividad',200);
            $table->string('fecha_ini',30);
            $table->string('fecha_fin',30);
            $table->bigInteger('id_direccion')->unsigned();
            $table->string('horas',4);
            $table->string('matricula');
            $table->string('matricula_escolar')->nullable();
            $table->integer('estatus_envio')->default(1);
            $table->string('comentario',255)->nullable();
            $table->foreign('id_direccion')->references('id_direccion')->on('direcciones');

            $table->foreign('matricula_escolar')->references('matricula')->on('escolares');
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
        Schema::dropIfExists('servicios');
    }
};
