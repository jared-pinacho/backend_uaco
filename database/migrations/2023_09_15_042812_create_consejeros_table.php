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
        Schema::create('consejeros', function (Blueprint $table) {
            $table->string('matricula',20)->primary();
            $table->string('nombre',40);
            $table->string('apellido_paterno',40);
            $table->string('apellido_materno',40);
            $table->date('fecha_nacimiento');
            $table->string('sexo',2);
            $table->string('estado_nacimiento',4);
            $table->string('curp',20);
            $table->string('rfc',20);
            $table->string('nivel_educativo',60);
            $table->string('perfil_academico',120);
            $table->string('telefono',10);
            $table->bigInteger('id_tiposangre')->unsigned();
            $table->string('telefono_emergencia',10);
            $table->string('padecimiento',50);
            $table->bigInteger('id_direccion')->unsigned();
            $table->string('clave_cuc',20);
            $table->bigInteger('id_nacionalidad')->unsigned();
            $table->id();
            $table->unsignedBigInteger('id')->nullable();



            $table->foreign('clave_cuc')->references('clave_cuc')->on('cucs');
            $table->foreign('estado_nacimiento')->references('id_estado')->on('estados');
            $table->foreign('id_direccion')->references('id_direccion')->on('direcciones');
            $table->foreign('id_tiposangre')->references('id_tiposangre')->on('tipo_sangres');
            $table->foreign('id_nacionalidad')->references('id_nacionalidad')->on('nacionalidades');
            $table->BigInteger('id')->unsigned();
            $table->foreign('id')
            ->references('id')
            ->on('users')
            ->onDelete('set null');
            
            $table->softDeletes();
            $table->timestamps(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consejeros');
    }
};
