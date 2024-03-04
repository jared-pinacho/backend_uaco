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
        Schema::create('estado_reporte_grupos', function (Blueprint $table) {
            $table->string('clave_grupo',20);
            $table->bigInteger('id_periodo')->unsigned();
            $table->boolean('aprobado_inicio');
            $table->boolean('aprobado_final');
            $table->string('semestre',2);
            $table->foreign('clave_grupo')->references('clave_grupo')->on('grupos');
            $table->foreign('id_periodo')->references('id_periodo')->on('periodos');
            
            $table->softDeletes();
            $table->timestamps(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estado_reporte_grupos');
    }
};
