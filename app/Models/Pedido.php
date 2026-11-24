<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'cliente_id',
        'cant_producto',
        'direccion_envio',
        'pais_destino',
        'tipo_envio',
        'envoltura_regalo',
        'mensaje_regalo',
        'subtotal',
        'comision_curaduria_hub',
        'costo_envio',
        'total',
        'estado_pedido',
        'codigo_seguimiento',
        'notas',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'comision_curaduria_hub' => 'decimal:2',
            'costo_envio' => 'decimal:2',
            'total' => 'decimal:2',
            'envoltura_regalo' => 'boolean',
        ];
    }

    /**
     * Cliente que realizó el pedido (Relación N:1).
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    /**
     * Productos que componen el pedido (Relación N:M "Pedido posee Productos").
     */
    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'pedido_producto')
            ->withPivot('cantidad', 'precio_unitario_historico')
            ->withTimestamps();
    }

    /**
     * Indica si el pedido es modificable por el artesano o HUB antes de cerrarse.
     */
    public function esCerrado(): bool
    {
        return in_array($this->estado_pedido, ['enviado_internacional', 'entregado', 'cancelado'], true);
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado_pedido) {
            'pendiente' => 'badge-warning',
            'confirmado_artesano' => 'badge-info',
            'en_acopio_hub' => 'badge-primary',
            'enviado_internacional' => 'badge-accent',
            'entregado' => 'badge-success',
            'cancelado' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    public function getEstadoLegibleAttribute(): string
    {
        return match ($this->estado_pedido) {
            'pendiente' => 'Pendiente de confirmación',
            'confirmado_artesano' => 'En preparación por artesano',
            'en_acopio_hub' => 'En acopio y certificación HUB',
            'enviado_internacional' => 'Enviado internacional',
            'entregado' => 'Entregado a destino',
            'cancelado' => 'Cancelado',
            default => ucfirst(str_replace('_', ' ', $this->estado_pedido)),
        };
    }

    /**
     * URL pública de la dedicatoria digital asociada al pedido (para escaneo QR).
     */
    public function getUrlDedicatoriaDigitalAttribute(): string
    {
        return route('pedidos.dedicatoria', $this->codigo_seguimiento ?? $this->id);
    }

    /**
     * URL de la imagen del código QR que apunta a la dedicatoria digital.
     */
    public function getQrDedicatoriaUrlAttribute(): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&format=png&data='.urlencode($this->url_dedicatoria_digital);
    }
}
