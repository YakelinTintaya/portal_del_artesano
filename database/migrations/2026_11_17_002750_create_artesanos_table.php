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
        Schema::create('artesanos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nombre');
            $table->string('apellido');
            $table->string('direccion_arte'); // Comunidad o taller: ej. San Blas, Pisac, Chinchero, San Pedro
            $table->string('telefono')->nullable();
            $table->text('historia_taller');
            $table->string('foto_perfil')->nullable();
            $table->boolean('estado_validacion')->default(false); // Validado por el HUB logístico
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artesanos');
    }
};
