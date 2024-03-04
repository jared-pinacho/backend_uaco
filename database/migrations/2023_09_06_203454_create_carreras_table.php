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
        Schema::create('carreras', function (Blueprint $table) {
            
            $table->string('clave_carrera',20)->primary();
            $table->string('nombre',60);
            $table->string('grado',60);
            $table->string('creditos',40);
            $table->string('periodicidad',40);
            $table->string('duracion',40);
            $table->string('modalidad',20);

            $table->softDeletes();
            $table->timestamps(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carreras');
        
    }
};
