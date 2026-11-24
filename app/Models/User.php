<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'apellido',
        'name',
        'telefono',
        'correo',
        'email',
        'direccion',
        'password',
        'rol',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if (empty($user->name) && (! empty($user->nombre) || ! empty($user->apellido))) {
                $user->name = trim("{$user->nombre} {$user->apellido}");
            }
            if (empty($user->email) && ! empty($user->correo)) {
                $user->email = $user->correo;
            }
            if (empty($user->correo) && ! empty($user->email)) {
                $user->correo = $user->email;
            }
        });
    }

    /**
     * Perfil de artesano asociado si el usuario tiene rol de artesano.
     */
    public function artesano(): HasOne
    {
        return $this->hasOne(Artesano::class, 'user_id');
    }

    /**
     * Pedidos realizados por el cliente.
     */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'cliente_id');
    }

    public function isAdminHub(): bool
    {
        return $this->rol === 'admin_hub';
    }

    public function isArtesano(): bool
    {
        return $this->rol === 'artesano';
    }

    public function isCliente(): bool
    {
        return $this->rol === 'cliente';
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellido}");
    }
}
