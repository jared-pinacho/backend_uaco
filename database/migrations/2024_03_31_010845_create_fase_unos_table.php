<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fase_unos', function (Blueprint $table) {

            $table->bigIncrements('id_faseUno');
            $table->string('carta_presentacion', 250);
            $table->string('carta_aceptacion', 250);
            $table->integer('estatus_envio')->default(0);
            $table->integer('acep_estado')->default(0);
            $table->integer('pres_estado')->default(0);
            $table->string('com_pres', 255)->nullable();
            $table->string('come_acep', 255)->nullable();
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
        Schema::dropIfExists('fase_unos');
    }
};
