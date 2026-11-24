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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artesano_id')->constrained('artesanos')->onDelete('cascade');
            $table->string('nombre_prod');
            $table->text('descripcion_prod');
            $table->string('categoria'); // Textiles, Platería, Cerámica, Imaginería, etc.
            $table->string('subtipo')->nullable(); // Decoración, Accesorios, Ropa
            $table->decimal('precio', 10, 2); // Precio base justo
            $table->integer('stock')->default(0);
            $table->string('tamano')->nullable();
            $table->string('colores')->nullable();
            $table->decimal('puntuacion', 3, 2)->default(5.00);
            $table->text('materiales_origen'); // Ej: Lana de alpaca virgen, tintes naturales, plata 950
            $table->string('certificado_autenticidad_codigo')->unique(); // Código del certificado digital
            $table->boolean('aprobado_hub')->default(false); // Curaduría y aprobación del HUB para venta
            $table->string('imagen_url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
