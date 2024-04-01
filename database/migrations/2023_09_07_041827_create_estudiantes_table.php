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
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->string('matricula',20)->primary();
            $table->string('nombre',40);
            $table->string('apellido_paterno',40);
            $table->string('apellido_materno',40);
            $table->string('edad',3);
            $table->string('estado_nacimiento',4);
            $table->string('curp',20);
            $table->string('sexo',2);
            $table->date('fecha_nacimiento');
            $table->string('nivel_educativo',60);
            $table->string('telefono',10);
            $table->string('telefono_emergencia',10);
            $table->bigInteger('id_direccion')->unsigned();
            $table->bigInteger('id_nacionalidad')->unsigned();
            $table->bigInteger('id_tiposangre')->unsigned();
            $table->string('padecimiento',50);
            $table->string('discapacidad',40);
            $table->string('regular',3);
            $table->string('semestre',2);
            $table->string('estatus',10);
            $table->integer('creditos_acumulados');
            $table->bigInteger('id_lenguaindigena')->unsigned();
            $table->bigInteger('id_puebloindigena')->unsigned();
            $table->String('clave_grupo');
            $table->BigInteger('id')->unsigned();
            $table->boolean('servicio_estatus')->default(false); 
            $table->foreign('estado_nacimiento')->references('id_estado')->on('estados');
            $table->foreign('id_direccion')->references('id_direccion')->on('direcciones');
            $table->foreign('id_tiposangre')->references('id_tiposangre')->on('tipo_sangres');
            $table->foreign('id_nacionalidad')->references('id_nacionalidad')->on('nacionalidades');
            $table->foreign('id_lenguaindigena')->references('id_lenguaindigena')->on('lenguas_indigenas');
            $table->foreign('id_puebloindigena')->references('id_puebloindigena')->on('pueblos_indigenas');
            $table->foreign('clave_grupo')->references('clave_grupo')->on('grupos');
            $table->foreign('id')->references('id')->on('users');
            $table->softDeletes();
            $table->timestamps(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
