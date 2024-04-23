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
        Schema::create('anuncios', function (Blueprint $table) {
            $table->bigIncrements('id_anuncio');
            $table->string('titulo',100);
            $table->string('descripcion',250);
            $table->string('fecha',30);
            $table->string('matricula');
           

            $table->foreign('matricula')->references('matricula')->on('escolares');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anuncios');
    }
};
