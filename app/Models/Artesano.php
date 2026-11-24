<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Artesano extends Model
{
    use HasFactory;

    protected $table = 'artesanos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'nombre',
        'apellido',
        'direccion_arte',
        'telefono',
        'historia_taller',
        'foto_perfil',
        'estado_validacion',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estado_validacion' => 'boolean',
        ];
    }

    /**
     * Usuario vinculado a este perfil de artesano.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Productos creados por este artesano (Relación 1:N "Artesano tiene Productos").
     */
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'artesano_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellido}");
    }

    public function estaValidado(): bool
    {
        return (bool) $this->estado_validacion;
    }

    public function getDireccionCompletaAttribute(): string
    {
        return $this->user?->direccion ?: $this->direccion_arte;
    }

    public function getUrlGoogleMapsAttribute(): string
    {
        return 'https://www.google.com/maps/search/?api=1&query='.urlencode($this->direccion_completa.', Cusco, Peru');
    }

    public function getUrlEmbedMapsAttribute(): string
    {
        return 'https://maps.google.com/maps?q='.urlencode($this->direccion_completa.', Cusco, Peru').'&t=&z=16&ie=UTF8&iwloc=&output=embed';
    }
}
