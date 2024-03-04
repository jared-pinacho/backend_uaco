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
        Schema::create('cucs', function (Blueprint $table) {
            $table->string('clave_cuc',20)->primary();
            $table->string('nombre',100);
            $table->string('numero',3);
            $table->bigInteger('id_direccion')->unsigned();
            $table->softDeletes();
            $table->timestamps(false);

            $table->foreign('id_direccion')->references('id_direccion')->on('direcciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cucs');
    }
};
