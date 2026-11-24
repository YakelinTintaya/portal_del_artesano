<?php

namespace App\Http\Controllers;

use App\Models\Artesano;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArtesanoPanelController extends Controller
{
    /**
     * Dashboard del Artesano cusqueño.
     */
    public function index(): View
    {
        $user = Auth::user();
        $artesano = Artesano::where('user_id', $user->id)->first();

        if (! $artesano) {
            return view('artesano.registro-taller');
        }

        $productos = Producto::where('artesano_id', $artesano->id)
            ->latest()
            ->get();

        // Pedidos asociados a las piezas de este artesano
        $pedidos = Pedido::whereHas('productos', function ($q) use ($artesano) {
            $q->where('artesano_id', $artesano->id);
        })->with(['productos' => function ($q) use ($artesano) {
            $q->where('artesano_id', $artesano->id);
        }, 'cliente'])->latest()->take(10)->get();

        // Calcular métricas de ingresos justos
        $totalRecibido = 0.00;
        foreach ($pedidos as $p) {
            if ($p->estado_pedido !== 'cancelado') {
                foreach ($p->productos as $prod) {
                    $totalRecibido += round((float) $prod->pivot->precio_unitario_historico * 0.85, 2) * $prod->pivot->cantidad;
                }
            }
        }

        return view('artesano.dashboard', compact('artesano', 'productos', 'pedidos', 'totalRecibido'));
    }

    /**
     * Registra o actualiza el perfil del taller artesano.
     */
    public function registrarTaller(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'direccion_arte' => 'required|string|max:200', // Comunidad: San Blas, Pisac, Chinchero, San Pedro
            'telefono' => 'required|string|max:20',
            'historia_taller' => 'required|string|min:30',
            'foto_perfil' => 'nullable|url',
        ]);

        $user = Auth::user();

        $artesano = Artesano::updateOrCreate(
            ['user_id' => $user->id],
            [
                'nombre' => $request->input('nombre'),
                'apellido' => $request->input('apellido'),
                'direccion_arte' => $request->input('direccion_arte'),
                'telefono' => $request->input('telefono'),
                'historia_taller' => $request->input('historia_taller'),
                'foto_perfil' => $request->input('foto_perfil', 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=400&q=80'),
                'estado_validacion' => false, // Requiere validación por el HUB
            ]
        );

        // Si era cliente, actualizamos rol
        if ($user->rol !== 'artesano' && ! $user->isAdminHub()) {
            $user->rol = 'artesano';
            $user->save();
        }

        return redirect()->route('artesano.dashboard')
            ->with('success', 'Ficha del taller registrada. El HUB logístico revisará la acreditación de autenticidad.');
    }

    /**
     * Formulario para crear una nueva pieza artesanal.
     */
    public function create(): View|RedirectResponse
    {
        $user = Auth::user();
        $artesano = Artesano::where('user_id', $user->id)->first();

        if (! $artesano) {
            $artesano = Artesano::create([
                'user_id' => $user->id,
                'nombre' => $user->nombre ?: 'Maestro',
                'apellido' => $user->apellido ?: 'Artesano',
                'direccion_arte' => $user->direccion ?: 'Barrio de San Blas, Cusco',
                'telefono' => $user->telefono ?: '+51 984 000 000',
                'historia_taller' => 'Taller artesanal cusqueño dedicado al arte tradicional andino.',
                'foto_perfil' => 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=400&q=80',
                'estado_validacion' => true,
            ]);
        }

        return view('artesano.producto-form', [
            'artesano' => $artesano,
            'producto' => null,
            'modo' => 'crear',
            'dimensiones' => $this->parsearDimensiones(old('tamano')),
        ]);
    }

    /**
     * Guarda una nueva pieza artesanal.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $artesano = Artesano::where('user_id', $user->id)->first();

        if (! $artesano) {
            $artesano = Artesano::create([
                'user_id' => $user->id,
                'nombre' => $user->nombre ?: 'Maestro',
                'apellido' => $user->apellido ?: 'Artesano',
                'direccion_arte' => $user->direccion ?: 'Barrio de San Blas, Cusco',
                'telefono' => $user->telefono ?: '+51 984 000 000',
                'historia_taller' => 'Taller artesanal cusqueño dedicado al arte tradicional andino.',
                'foto_perfil' => 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=400&q=80',
                'estado_validacion' => true,
            ]);
        }

        if ($request->filled('dim_largo') || $request->filled('dim_ancho') || $request->filled('dim_alto') || $request->filled('dim_texto_libre') || $request->has('modo_tamano')) {
            $request->merge(['tamano' => $this->formatearDimensiones($request)]);
        }

        $request->validate([
            'nombre_prod' => 'required|string|max:200',
            'descripcion_prod' => 'required|string|min:20',
            'categoria' => 'required|string|in:Textiles,Platería,Cerámica,Imaginería',
            'subtipo' => 'nullable|string|max:100',
            'precio' => 'required|numeric|min:5',
            'stock' => 'required|integer|min:0',
            'tamano' => 'nullable|string|max:50',
            'colores' => 'nullable|string|max:100',
            'materiales_origen' => 'required|string|max:300',
            'imagen_archivo' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'imagen_url' => 'nullable|url',
        ]);

        $imagenFinal = null;

        if ($request->hasFile('imagen_archivo')) {
            $archivo = $request->file('imagen_archivo');
            $nombreArchivo = 'obra_'.time().'_'.Str::random(8).'.'.$archivo->getClientOriginalExtension();
            $path = $archivo->storeAs('productos', $nombreArchivo, 'public');
            $imagenFinal = 'storage/'.$path;
        } elseif ($request->filled('imagen_url')) {
            $imagenFinal = $request->input('imagen_url');
        } else {
            return back()->withInput()->withErrors([
                'imagen_archivo' => 'Debes adjuntar una fotografía de la obra artesanal o proporcionar una URL válida.',
            ]);
        }

        $codigoCertificado = 'CERT-CUSCO-'.date('Y').'-'.strtoupper(Str::random(6));

        Producto::create([
            'artesano_id' => $artesano->id,
            'nombre_prod' => $request->input('nombre_prod'),
            'descripcion_prod' => $request->input('descripcion_prod'),
            'categoria' => $request->input('categoria'),
            'subtipo' => $request->input('subtipo'),
            'precio' => $request->input('precio'),
            'stock' => $request->input('stock'),
            'tamano' => $request->input('tamano', 'Único'),
            'colores' => $request->input('colores'),
            'puntuacion' => 5.00,
            'materiales_origen' => $request->input('materiales_origen'),
            'certificado_autenticidad_codigo' => $codigoCertificado,
            'aprobado_hub' => false, // Requiere aprobación previa del HUB
            'imagen_url' => $imagenFinal,
        ]);

        return redirect()->route('artesano.dashboard')
            ->with('success', 'Pieza registrada exitosamente. Queda pendiente de curaduría y aprobación por el HUB.');
    }

    /**
     * Formulario de edición de pieza artesanal.
     */
    public function edit(int $id): View
    {
        $artesano = Artesano::where('user_id', Auth::id())->firstOrFail();
        $producto = Producto::where('artesano_id', $artesano->id)->findOrFail($id);

        return view('artesano.producto-form', [
            'artesano' => $artesano,
            'producto' => $producto,
            'modo' => 'editar',
            'dimensiones' => $this->parsearDimensiones(old('tamano', $producto->tamano)),
        ]);
    }

    /**
     * Actualiza información, precio base o stock de la pieza.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $artesano = Artesano::where('user_id', Auth::id())->firstOrFail();
        $producto = Producto::where('artesano_id', $artesano->id)->findOrFail($id);

        if ($request->filled('dim_largo') || $request->filled('dim_ancho') || $request->filled('dim_alto') || $request->filled('dim_texto_libre') || $request->has('modo_tamano')) {
            $request->merge(['tamano' => $this->formatearDimensiones($request)]);
        }

        $request->validate([
            'nombre_prod' => 'required|string|max:200',
            'descripcion_prod' => 'required|string|min:20',
            'categoria' => 'required|string|in:Textiles,Platería,Cerámica,Imaginería',
            'subtipo' => 'nullable|string|max:100',
            'precio' => 'required|numeric|min:5',
            'stock' => 'required|integer|min:0',
            'tamano' => 'nullable|string|max:50',
            'colores' => 'nullable|string|max:100',
            'materiales_origen' => 'required|string|max:300',
            'imagen_archivo' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'imagen_url' => 'nullable|url',
        ]);

        $imagenFinal = $producto->getRawOriginal('imagen_url') ?? $producto->imagen_url;

        if ($request->hasFile('imagen_archivo')) {
            $archivo = $request->file('imagen_archivo');
            $nombreArchivo = 'obra_'.time().'_'.Str::random(8).'.'.$archivo->getClientOriginalExtension();
            $path = $archivo->storeAs('productos', $nombreArchivo, 'public');
            $imagenFinal = 'storage/'.$path;
        } elseif ($request->filled('imagen_url')) {
            $imagenFinal = $request->input('imagen_url');
        }

        $cambioPrecio = (float) $producto->precio !== (float) $request->input('precio');

        $producto->update([
            'nombre_prod' => $request->input('nombre_prod'),
            'descripcion_prod' => $request->input('descripcion_prod'),
            'categoria' => $request->input('categoria'),
            'subtipo' => $request->input('subtipo'),
            'precio' => $request->input('precio'),
            'stock' => $request->input('stock'),
            'tamano' => $request->input('tamano'),
            'colores' => $request->input('colores'),
            'materiales_origen' => $request->input('materiales_origen'),
            'imagen_url' => $imagenFinal,
            // Si cambia el precio, el HUB debe revalidar el precio de venta justo
            'aprobado_hub' => $cambioPrecio ? false : $producto->aprobado_hub,
        ]);

        $msg = $cambioPrecio
            ? 'Pieza actualizada. Debido al cambio de precio base, el HUB deberá revalidar la pieza.'
            : 'Pieza actualizada correctamente.';

        return redirect()->route('artesano.dashboard')->with('success', $msg);
    }

    /**
     * Confirmación de preparación de pedido por el artesano en taller.
     */
    public function confirmarPreparacion(int $pedidoId): RedirectResponse
    {
        $artesano = Artesano::where('user_id', Auth::id())->firstOrFail();

        $pedido = Pedido::whereHas('productos', function ($q) use ($artesano) {
            $q->where('artesano_id', $artesano->id);
        })->findOrFail($pedidoId);

        if ($pedido->estado_pedido === 'pendiente') {
            $pedido->estado_pedido = 'confirmado_artesano';
            $pedido->save();
        }

        return back()->with('success', "Pedido #{$pedido->id} marcado como 'En preparación por artesano'. El HUB pasará a acopio.");
    }

    /**
     * API JSON: Verifica si hay nuevos pedidos pendientes para las piezas del artesano.
     */
    public function verificarNuevosPedidos(Request $request): JsonResponse
    {
        $user = Auth::user();
        $artesano = Artesano::where('user_id', $user->id)->first();

        if (! $artesano) {
            return response()->json([
                'hay_nuevos' => false,
                'total_pendientes' => 0,
                'pedidos' => [],
                'max_id' => 0,
            ]);
        }

        $ultimoId = (int) $request->query('ultimo_id', 0);

        // Pedidos pendientes que contienen piezas de este taller
        $query = Pedido::whereHas('productos', function ($q) use ($artesano) {
            $q->where('artesano_id', $artesano->id);
        })->with(['productos' => function ($q) use ($artesano) {
            $q->where('artesano_id', $artesano->id);
        }, 'cliente'])->where('estado_pedido', 'pendiente');

        $totalPendientes = (clone $query)->count();

        if ($ultimoId > 0) {
            $query->where('id', '>', $ultimoId);
        }

        $pedidosNuevos = $query->latest()->get();

        $lista = $pedidosNuevos->map(function ($p) {
            $piezas = $p->productos->map(function ($prod) {
                return $prod->pivot->cantidad.'x '.$prod->nombre_prod;
            })->implode(', ');

            return [
                'id' => $p->id,
                'codigo_seguimiento' => $p->codigo_seguimiento,
                'cliente_nombre' => $p->cliente ? $p->cliente->nombre_completo : 'Comprador Internacional',
                'pais_destino' => $p->pais_destino,
                'piezas' => $piezas,
                'total' => number_format((float) $p->total, 2),
                'envoltura_regalo' => (bool) $p->envoltura_regalo,
                'mensaje_regalo' => $p->mensaje_regalo,
                'qr_url' => $p->qr_dedicatoria_url,
                'url_dedicatoria' => $p->url_dedicatoria_digital,
                'url_confirmar' => route('artesano.pedidos.confirmar', $p->id),
                'fecha' => $p->created_at->format('d/m/Y H:i'),
            ];
        });

        $maxId = Pedido::whereHas('productos', function ($q) use ($artesano) {
            $q->where('artesano_id', $artesano->id);
        })->max('id') ?? $ultimoId;

        return response()->json([
            'hay_nuevos' => $pedidosNuevos->isNotEmpty(),
            'total_pendientes' => $totalPendientes,
            'pedidos' => $lista,
            'max_id' => $maxId,
        ]);
    }

    /**
     * Extrae las partes dimensionales de una cadena de tamaño.
     *
     * @return array{largo: ?string, ancho: ?string, alto: ?string, unidad: string, texto_libre: ?string, es_personalizado: bool}
     */
    private function parsearDimensiones(?string $tamano): array
    {
        $res = [
            'largo' => null,
            'ancho' => null,
            'alto' => null,
            'unidad' => 'cm',
            'texto_libre' => null,
            'es_personalizado' => false,
        ];

        if (empty($tamano)) {
            return $res;
        }

        $tamano = trim($tamano);

        if (preg_match('/^(\d+(?:\.\d+)?)\s*[xX*]\s*(\d+(?:\.\d+)?)(?:\s*[xX*]\s*(\d+(?:\.\d+)?))?\s*([a-zA-Z]+)?$/', $tamano, $m)) {
            $res['largo'] = $m[1] ?? null;
            $res['ancho'] = $m[2] ?? null;
            $res['alto'] = ! empty($m[3]) ? $m[3] : null;
            $res['unidad'] = ! empty($m[4]) ? strtolower($m[4]) : 'cm';

            return $res;
        }

        if (preg_match('/Alto\s*(\d+(?:\.\d+)?)/i', $tamano, $mAlto)) {
            $res['alto'] = $mAlto[1];
            if (preg_match('/(?:Ancho|Base|Largo)\s*(\d+(?:\.\d+)?)/i', $tamano, $mSec)) {
                $res['ancho'] = $mSec[1];
            }
            if (preg_match('/(cm|m|mm|in|pulg)/i', $tamano, $mU)) {
                $res['unidad'] = strtolower($mU[1]);
            }

            return $res;
        }

        $res['texto_libre'] = $tamano;
        $res['es_personalizado'] = true;

        return $res;
    }

    /**
     * Construye la cadena representativa de dimensiones a partir del request.
     */
    private function formatearDimensiones(Request $request): ?string
    {
        if ($request->input('modo_tamano') === 'libre') {
            if ($request->filled('dim_texto_libre')) {
                return mb_substr(trim((string) $request->input('dim_texto_libre')), 0, 50);
            }
            if ($request->filled('tamano')) {
                return mb_substr(trim((string) $request->input('tamano')), 0, 50);
            }

            return 'Único';
        }

        $largo = $request->input('dim_largo');
        $ancho = $request->input('dim_ancho');
        $alto = $request->input('dim_alto');
        $unidad = $request->input('dim_unidad', 'cm');

        $partes = [];
        if ($largo !== null && $largo !== '') {
            $partes[] = trim((string) $largo);
        }
        if ($ancho !== null && $ancho !== '') {
            $partes[] = trim((string) $ancho);
        }
        if ($alto !== null && $alto !== '') {
            $partes[] = trim((string) $alto);
        }

        if (! empty($partes)) {
            return mb_substr(implode(' x ', $partes).' '.$unidad, 0, 50);
        }

        if ($request->filled('tamano')) {
            return mb_substr(trim((string) $request->input('tamano')), 0, 50);
        }

        return 'Único';
    }
}
