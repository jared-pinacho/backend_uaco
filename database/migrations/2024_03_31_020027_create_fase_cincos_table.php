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
        Schema::create('fase_cincos', function (Blueprint $table) {
            $table->bigIncrements('id_faseCinco');
            $table->string('carta_terminacion',100);
            $table->integer('estatus_envio')->default(0);
            $table->string('comentario',255)->nullable();
            $table->bigInteger('id_servicio')->unsigned();


            $table->foreign('id_servicio')->references('id_servicio')->on('servicios');
            $table->softDeletes();
            $table->timestamps(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fase_cincos');
    }
};
