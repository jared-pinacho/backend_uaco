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
        Schema::create('cuc_carrera', function (Blueprint $table) {
            $table->string('clave_cuc',20);
            $table->string('clave_carrera',20);


            $table->foreign('clave_cuc')->references('clave_cuc')->on('cucs'); //->onDelete('cascade'); esto comentado si sse agrega nos permitira eliminacion en cascada
            $table->foreign('clave_carrera')->references('clave_carrera')->on('carreras');

            $table->softDeletes();
            $table->timestamps(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuc_carreras');
    }
};
