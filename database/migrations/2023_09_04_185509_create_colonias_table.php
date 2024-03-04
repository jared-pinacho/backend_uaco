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
        Schema::create('colonias', function (Blueprint $table) {
            $table->bigIncrements('id_colonia');
            $table->string('nombre',60);
            $table->string('id_cp',5);
            $table->string('id_municipio',4);

            $table->foreign('id_municipio')->references('id_municipio')->on('municipios');
            $table->foreign('id_cp')->references('id_cp')->on('codigo_postal');
            $table->softDeletes();
            $table->timestamps(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colonias');
    }
};
