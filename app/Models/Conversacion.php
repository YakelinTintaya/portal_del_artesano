<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Conversacion extends Model
{
    use HasFactory;

    protected $table = 'conversaciones';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'cliente_id',
        'artesano_id',
        'pedido_id',
        'producto_id',
        'asunto',
        'categoria_consulta',
        'estado',
        'resumen_admin',
        'necesita_intervencion_hub',
        'ultimo_mensaje_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'necesita_intervencion_hub' => 'boolean',
            'ultimo_mensaje_at' => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function artesano(): BelongsTo
    {
        return $this->belongsTo(Artesano::class, 'artesano_id');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(Mensaje::class, 'conversacion_id')->oldest();
    }

    public function ultimoMensaje(): HasOne
    {
        return $this->hasOne(Mensaje::class, 'conversacion_id')->latestOfMany();
    }

    /**
     * Genera o actualiza el resumen ejecutivo inteligente para la supervisión del HUB.
     */
    public function actualizarResumenHub(): void
    {
        $mensajes = $this->mensajes()->with('remitente')->get();

        if ($mensajes->isEmpty()) {
            return;
        }

        $totalMsgs = $mensajes->count();
        $primerMsg = $mensajes->first();
        $ultimoMsg = $mensajes->last();

        $clienteNombre = $this->cliente?->nombre_completo ?? 'Cliente';
        $artesanoNombre = $this->artesano?->nombre_completo ?? 'Artesano';

        // Detección de palabras clave para determinar si requiere intervención del HUB
        $textCompleto = mb_strtolower($mensajes->pluck('mensaje')->implode(' '));
        $palabrasAlerta = ['demora', 'retraso', 'queja', 'reclamo', 'problema', 'dañado', 'aduana', 'urgente', 'cancelar', 'roto', 'devolución'];

        $intervencion = false;
        foreach ($palabrasAlerta as $palabra) {
            if (str_contains($textCompleto, $palabra)) {
                $intervencion = true;
                break;
            }
        }

        // Síntesis de consulta y respuesta
        $preguntaInicial = Str::limit($primerMsg->mensaje, 120);
        $respuestaReciente = Str::limit($ultimoMsg->mensaje, 120);

        $resumen = "💬 [{$totalMsgs} mensajes intercambiados] • {$clienteNombre} inició consultando: «{$preguntaInicial}». ";
        if ($ultimoMsg->remitente_id === $this->cliente_id) {
            $resumen .= 'Último mensaje del cliente a la espera de respuesta del taller.';
        } else {
            $resumen .= "El maestro {$artesanoNombre} respondió: «{$respuestaReciente}».";
        }

        if ($this->pedido) {
            $resumen .= " Vinculado al Pedido #{$this->pedido->id} ({$this->pedido->codigo_seguimiento}).";
        }

        $this->update([
            'resumen_admin' => $resumen,
            'necesita_intervencion_hub' => $intervencion,
            'ultimo_mensaje_at' => $ultimoMsg->created_at,
        ]);
    }
}
