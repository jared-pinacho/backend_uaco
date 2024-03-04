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
        Schema::create('cuc_facilitadores', function (Blueprint $table) {
            $table->string('clave_cuc',20);
            $table->string('matricula',20);

            $table->foreign('clave_cuc')->references('clave_cuc')->on('cucs'); //->onDelete('cascade'); esto comentado si sse agrega nos permitira eliminacion en cascada
            $table->foreign('matricula')->references('matricula')->on('facilitadores');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuc_facilitadores');
    }
};
