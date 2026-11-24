<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'artesano_id',
        'nombre_prod',
        'descripcion_prod',
        'categoria',
        'subtipo',
        'precio',
        'en_oferta',
        'precio_oferta',
        'descuento_porcentaje',
        'etiqueta_promocion',
        'stock',
        'tamano',
        'colores',
        'puntuacion',
        'materiales_origen',
        'certificado_autenticidad_codigo',
        'aprobado_hub',
        'imagen_url',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'en_oferta' => 'boolean',
            'precio_oferta' => 'decimal:2',
            'descuento_porcentaje' => 'integer',
            'stock' => 'integer',
            'puntuacion' => 'decimal:2',
            'aprobado_hub' => 'boolean',
        ];
    }

    /**
     * Artesano creador del producto (Relación N:1).
     */
    public function artesano(): BelongsTo
    {
        return $this->belongsTo(Artesano::class, 'artesano_id');
    }

    /**
     * Pedidos que incluyen este producto (Relación N:M "Pedido posee Productos").
     */
    public function pedidos(): BelongsToMany
    {
        return $this->belongsToMany(Pedido::class, 'pedido_producto')
            ->withPivot('cantidad', 'precio_unitario_historico')
            ->withTimestamps();
    }

    /**
     * Scope para productos activos, aprobados por el HUB y con artesano validado.
     */
    public function scopeAprobados(Builder $query): Builder
    {
        return $query->where('aprobado_hub', true)
            ->whereHas('artesano', function (Builder $q) {
                $q->where('estado_validacion', true);
            });
    }

    /**
     * Scope para productos con descuento u oferta activa.
     */
    public function scopeOfertas(Builder $query): Builder
    {
        return $query->where('en_oferta', true);
    }

    /**
     * Obtiene el precio final aplicable (precio de oferta si está activo, de lo contrario precio regular).
     */
    public function getPrecioEfectivoAttribute(): float
    {
        if ($this->en_oferta && $this->precio_oferta && (float) $this->precio_oferta < (float) $this->precio) {
            return (float) $this->precio_oferta;
        }

        return (float) $this->precio;
    }

    /**
     * Calcula o retorna el porcentaje de descuento promocional.
     */
    public function getPorcentajeDescuentoCalculadoAttribute(): ?int
    {
        if (! $this->en_oferta) {
            return null;
        }

        if ($this->descuento_porcentaje) {
            return (int) $this->descuento_porcentaje;
        }

        if ($this->precio_oferta && (float) $this->precio > 0 && (float) $this->precio_oferta < (float) $this->precio) {
            return (int) round((1 - ((float) $this->precio_oferta / (float) $this->precio)) * 100);
        }

        return null;
    }

    /**
     * Desglose transparente de comercio justo:
     * El 85% del precio base va directamente al artesano.
     */
    public function getPrecioArtesanoAttribute(): float
    {
        return round($this->precio_efectivo * 0.85, 2);
    }

    /**
     * El 15% corresponde a la curaduría, certificación digital y mantenimiento del HUB logístico.
     */
    public function getComisionHubAttribute(): float
    {
        return round($this->precio_efectivo * 0.15, 2);
    }

    public function getPorcentajeArtesanoAttribute(): int
    {
        return 85;
    }

    public function getPorcentajeHubAttribute(): int
    {
        return 15;
    }

    /**
     * Resuelve la URL pública de la fotografía de la obra (local o remota).
     */
    public function getImagenUrlAttribute(?string $value): string
    {
        if (empty($value)) {
            return 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=800&q=80';
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return asset(ltrim($value, '/'));
    }
}
