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
        Schema::create('tipo_consejeros', function (Blueprint $table) {
            $table->string('matricula',20);
            $table->string('id_areaconsejero',3);

            $table->foreign('matricula')->references('matricula')->on('consejeros');
            $table->foreign('id_areaconsejero')->references('id_areaconsejero')->on('areas_consejerias');

            $table->softDeletes();
            $table->timestamps(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_consejeros');
    }
};
