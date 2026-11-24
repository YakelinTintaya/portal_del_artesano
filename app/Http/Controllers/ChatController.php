<?php

namespace App\Http\Controllers;

use App\Models\Artesano;
use App\Models\Conversacion;
use App\Models\Mensaje;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ChatController extends Controller
{
    /**
     * Bandeja de conversaciones del usuario autenticado (Cliente o Artesano).
     */
    public function index(): View
    {
        $user = Auth::user();

        if ($user->isArtesano()) {
            $artesano = Artesano::where('user_id', $user->id)->first();
            $conversaciones = Conversacion::where('artesano_id', $artesano?->id ?? 0)
                ->with(['cliente', 'producto', 'pedido', 'ultimoMensaje'])
                ->latest('updated_at')
                ->get();
        } else {
            $conversaciones = Conversacion::where('cliente_id', $user->id)
                ->with(['artesano.user', 'producto', 'pedido', 'ultimoMensaje'])
                ->latest('updated_at')
                ->get();
        }

        return view('chat.index', compact('conversaciones'));
    }

    /**
     * Inicia una conversación con un taller artesano (desde un producto, pedido o perfil).
     */
    public function iniciar(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'artesano_id' => 'required|exists:artesanos,id',
            'producto_id' => 'nullable|exists:productos,id',
            'pedido_id' => 'nullable|exists:pedidos,id',
            'asunto' => 'nullable|string|max:200',
            'mensaje' => 'nullable|string|max:1500',
            'categoria_consulta' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();
        $artesanoId = (int) $request->input('artesano_id');
        $productoId = $request->input('producto_id');
        $pedidoId = $request->input('pedido_id');

        // Buscar si ya existe una conversación abierta reciente para este contexto
        $conversacion = Conversacion::where('cliente_id', $user->id)
            ->where('artesano_id', $artesanoId)
            ->when($pedidoId, fn ($q) => $q->where('pedido_id', $pedidoId))
            ->when($productoId && ! $pedidoId, fn ($q) => $q->where('producto_id', $productoId))
            ->first();

        if (! $conversacion) {
            $asunto = $request->input('asunto');
            if (empty($asunto)) {
                if ($pedidoId) {
                    $pedido = Pedido::find($pedidoId);
                    $asunto = "Coordinación sobre Pedido #{$pedido?->id} ({$pedido?->codigo_seguimiento})";
                } elseif ($productoId) {
                    $prod = Producto::find($productoId);
                    $asunto = "Consulta sobre «{$prod?->nombre_prod}»";
                } else {
                    $asunto = 'Consulta personalizada para el taller artesano';
                }
            }

            $conversacion = Conversacion::create([
                'cliente_id' => $user->id,
                'artesano_id' => $artesanoId,
                'producto_id' => $productoId,
                'pedido_id' => $pedidoId,
                'asunto' => $asunto,
                'categoria_consulta' => $request->input('categoria_consulta', 'Personalización y Medidas'),
                'estado' => 'abierto',
                'ultimo_mensaje_at' => now(),
            ]);
        }

        // Si se envió un primer mensaje al iniciar
        if ($request->filled('mensaje')) {
            $nuevoMensaje = Mensaje::create([
                'conversacion_id' => $conversacion->id,
                'remitente_id' => $user->id,
                'mensaje' => $request->input('mensaje'),
            ]);

            $conversacion->actualizarResumenHub();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'conversacion_id' => $conversacion->id,
                'redirect_url' => route('chat.show', $conversacion->id),
            ]);
        }

        return redirect()->route('chat.show', $conversacion->id);
    }

    /**
     * Muestra la sala de chat en vivo de una conversación.
     */
    public function show(int $id): View
    {
        $user = Auth::user();
        $conversacion = Conversacion::with([
            'cliente',
            'artesano.user',
            'producto',
            'pedido',
            'mensajes.remitente',
        ])->findOrFail($id);

        // Control de acceso: Cliente, Artesano involucrado o Admin HUB
        $esCliente = $user->id === $conversacion->cliente_id;
        $esArtesano = ($conversacion->artesano && $user->id === $conversacion->artesano->user_id)
            || ($user->artesano && $conversacion->artesano_id === $user->artesano->id);
        $esAdmin = $user->isAdminHub();

        if (! $esCliente && ! $esArtesano && ! $esAdmin) {
            abort(403, 'No tienes permiso para acceder a esta conversación.');
        }

        // Marcar mensajes del interlocutor como leídos
        Mensaje::where('conversacion_id', $conversacion->id)
            ->where('remitente_id', '!=', $user->id)
            ->where('leido', false)
            ->update([
                'leido' => true,
                'leido_at' => now(),
            ]);

        return view('chat.show', compact('conversacion', 'esCliente', 'esArtesano', 'esAdmin'));
    }

    /**
     * Envío de mensaje en vivo por AJAX o formulario.
     */
    public function enviarMensaje(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $request->validate([
            'mensaje' => 'required|string|max:2000',
        ]);

        $user = Auth::user();
        $conversacion = Conversacion::with(['artesano'])->findOrFail($id);

        $esCliente = $user->id === $conversacion->cliente_id;
        $esArtesano = ($conversacion->artesano && $user->id === $conversacion->artesano->user_id)
            || ($user->artesano && $conversacion->artesano_id === $user->artesano->id);
        $esAdmin = $user->isAdminHub();

        if (! $esCliente && ! $esArtesano && ! $esAdmin) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'No autorizado para participar en esta conversación.'], 403);
            }
            abort(403, 'No tienes permiso para enviar mensajes en esta conversación.');
        }

        $mensaje = Mensaje::create([
            'conversacion_id' => $conversacion->id,
            'remitente_id' => $user->id,
            'mensaje' => $request->input('mensaje'),
        ]);

        // Actualizar resumen ejecutivo del HUB automáticamente (con salvaguarda)
        try {
            $conversacion->actualizarResumenHub();
        } catch (\Throwable $e) {
            Log::warning('Error actualizando resumen del HUB: '.$e->getMessage());
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'mensaje' => [
                    'id' => $mensaje->id,
                    'remitente_id' => $mensaje->remitente_id,
                    'remitente_nombre' => $user->nombre_completo,
                    'es_mio' => true,
                    'mensaje' => e($mensaje->mensaje),
                    'hora' => $mensaje->created_at->format('H:i'),
                    'fecha' => $mensaje->created_at->format('d/m/Y'),
                ],
            ]);
        }

        return back()->with('success', 'Mensaje enviado.');
    }

    /**
     * API JSON: Obtiene mensajes nuevos para el live polling de la sala de chat.
     */
    public function obtenerNuevosMensajes(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $conversacion = Conversacion::with(['artesano'])->findOrFail($id);

        $esCliente = $user->id === $conversacion->cliente_id;
        $esArtesano = ($conversacion->artesano && $user->id === $conversacion->artesano->user_id)
            || ($user->artesano && $conversacion->artesano_id === $user->artesano->id);
        $esAdmin = $user->isAdminHub();

        if (! $esCliente && ! $esArtesano && ! $esAdmin) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $ultimoId = (int) $request->query('ultimo_id', 0);

        $mensajesNuevos = Mensaje::where('conversacion_id', $conversacion->id)
            ->where('id', '>', $ultimoId)
            ->with('remitente')
            ->oldest()
            ->get();

        // Marcar como leídos los mensajes que no sean del usuario actual
        if ($mensajesNuevos->isNotEmpty()) {
            Mensaje::where('conversacion_id', $conversacion->id)
                ->where('id', '>', $ultimoId)
                ->where('remitente_id', '!=', $user->id)
                ->update(['leido' => true, 'leido_at' => now()]);
        }

        $formateados = $mensajesNuevos->map(function ($m) use ($user) {
            return [
                'id' => $m->id,
                'remitente_id' => $m->remitente_id,
                'remitente_nombre' => $m->remitente->nombre_completo ?? 'Usuario',
                'es_mio' => $m->remitente_id === $user->id,
                'mensaje' => e($m->mensaje),
                'hora' => $m->created_at->format('H:i'),
                'fecha' => $m->created_at->format('d/m/Y'),
            ];
        });

        return response()->json([
            'nuevos' => $formateados,
            'max_id' => $mensajesNuevos->max('id') ?? $ultimoId,
        ]);
    }

    /**
     * Vista exclusiva para el Administrador del HUB: Supervisión & Resumen de todos los chats.
     */
    public function adminResumenIndex(): View
    {
        $conversaciones = Conversacion::with([
            'cliente',
            'artesano.user',
            'producto',
            'pedido',
            'mensajes.remitente',
            'ultimoMensaje',
        ])
            ->latest('updated_at')
            ->paginate(15);

        // Actualizar resúmenes si están vacíos
        foreach ($conversaciones as $c) {
            if (empty($c->resumen_admin) && $c->mensajes->isNotEmpty()) {
                $c->actualizarResumenHub();
            }
        }

        $totalChats = Conversacion::count();
        $chatsAlerta = Conversacion::where('necesita_intervencion_hub', true)->count();
        $chatsResueltos = Conversacion::where('estado', 'resuelto')->count();

        return view('admin.chats-resumen', compact('conversaciones', 'totalChats', 'chatsAlerta', 'chatsResueltos'));
    }

    /**
     * Actualiza el estado o notas del resumen por parte del HUB Logístico.
     */
    public function adminActualizarEstado(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'estado' => 'required|in:abierto,en_espera,resuelto',
            'necesita_intervencion_hub' => 'nullable|boolean',
            'resumen_admin' => 'nullable|string|max:1000',
        ]);

        $conversacion = Conversacion::findOrFail($id);
        $conversacion->update([
            'estado' => $request->input('estado'),
            'necesita_intervencion_hub' => $request->boolean('necesita_intervencion_hub'),
            'resumen_admin' => $request->input('resumen_admin', $conversacion->resumen_admin),
        ]);

        return back()->with('success', "Estado del chat #{$conversacion->id} actualizado correctamente por el HUB.");
    }
}
