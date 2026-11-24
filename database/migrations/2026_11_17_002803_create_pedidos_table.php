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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('users')->onDelete('restrict');
            $table->integer('cant_producto')->default(1);
            $table->text('direccion_envio');
            $table->string('pais_destino')->default('Perú');
            $table->string('tipo_envio'); // 'Nacional' o 'Internacional con seguro'
            $table->boolean('envoltura_regalo')->default(false);
            $table->text('mensaje_regalo')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('comision_curaduria_hub', 10, 2);
            $table->decimal('costo_envio', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2);
            $table->enum('estado_pedido', [
                'pendiente',
                'confirmado_artesano',
                'en_acopio_hub',
                'enviado_internacional',
                'entregado',
                'cancelado',
            ])->default('pendiente');
            $table->string('codigo_seguimiento')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
