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
        Schema::create('coordinadores', function (Blueprint $table) {
            
            $table->string('matricula',20)->primary();
            $table->string('nombre',40);
            $table->string('apellido_paterno',40);
            $table->string('apellido_materno',40);
            $table->string('curp',20);
            $table->string('rfc',20);
            $table->string('nivel_educativo',60);

            $table->BigInteger('id')->unsigned();
            $table->foreign('id')->references('id')->on('users');
            $table->softDeletes();
            $table->timestamps(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coordinadores');
    }
};
