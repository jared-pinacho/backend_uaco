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
        Schema::create('fase_cuatros', function (Blueprint $table) {
            $table->bigIncrements('id_faseCuatro');
            $table->string('reporte_tres',100);
            $table->boolean('estatus')->default(false);
            $table->string('comentario',100);
            $table->bigInteger('id_servicio')->unsigned();


            $table->foreign('id_servicio')->references('id_servicio')->on('servicios');
            $table->timestamps(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fase_cuatros');
    }
};
