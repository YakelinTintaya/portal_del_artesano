<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PedidoController extends Controller
{
    /**
     * Muestra la pantalla de Checkout / Compra (Purchase).
     */
    public function checkout(): View|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login')
                ->with('info', 'Por favor inicia sesión o crea una cuenta para procesar tu compra con certificación digital de autenticidad.');
        }

        $carrito = session()->get('carrito', []);

        if (empty($carrito)) {
            return redirect()->route('catalogo.index')
                ->with('warning', 'Tu bolsa de compras está vacía. Selecciona una pieza artesanal antes de continuar.');
        }

        $envolturaRegalo = session()->get('envoltura_regalo', false);

        $subtotal = 0.00;
        $totalArtesano = 0.00;
        $totalHub = 0.00;
        $cantTotal = 0;

        foreach ($carrito as $item) {
            $cant = (int) $item['cantidad'];
            $subtotal += (float) $item['precio'] * $cant;
            $totalArtesano += round((float) $item['precio'] * 0.85, 2) * $cant;
            $totalHub += round((float) $item['precio'] * 0.15, 2) * $cant;
            $cantTotal += $cant;
        }

        $user = Auth::user();

        return view('checkout', compact(
            'carrito',
            'subtotal',
            'totalArtesano',
            'totalHub',
            'cantTotal',
            'envolturaRegalo',
            'user'
        ));
    }

    /**
     * Procesa y confirma el pedido ejecutando validación en servidor y transacción atómica en DB.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('warning', 'Sesión expirada.');
        }

        $carrito = session()->get('carrito', []);
        if (empty($carrito)) {
            return redirect()->route('catalogo.index')->with('warning', 'Bolsa de compras vacía.');
        }

        $request->validate([
            'direccion_envio' => 'required|string|max:500',
            'pais_destino' => 'required|string|max:100',
            'tipo_envio' => 'required|in:Recojo en Tienda,Cusco Urbano,Valle Sagrado y Provincias,Nacional,Internacional con seguro',
            'envoltura_regalo' => 'nullable|boolean',
            'mensaje_regalo' => 'nullable|string|max:400',
            'telefono_contacto' => 'required|string|max:30',
            'metodo_pago' => 'required|string',
            'consentimiento_datos' => 'accepted', // Cumplimiento con Ley N.º 29733 de Protección de Datos Personales
        ], [
            'consentimiento_datos.accepted' => 'Debe aceptar los términos de tratamiento de datos personales conforme a la Ley N.º 29733.',
            'direccion_envio.required' => 'La dirección de entrega o punto de recojo en Cusco es obligatoria.',
            'telefono_contacto.required' => 'Indique un teléfono para la coordinación del pedido.',
        ]);

        $envolturaRegalo = $request->boolean('envoltura_regalo', session()->get('envoltura_regalo', false));
        $costoEnvoltura = $envolturaRegalo ? 12.00 : 0.00;

        $costoEnvio = match ($request->input('tipo_envio')) {
            'Recojo en Tienda', 'Recojo en tienda' => 0.00,
            'Valle Sagrado y Provincias', 'Valle Sagrado', 'Cusco Provincias' => 20.00,
            'Internacional con seguro' => 85.00,
            'Nacional' => 15.00,
            default => 10.00, // Cusco Urbano
        };

        try {
            $pedido = DB::transaction(function () use ($request, $carrito, $envolturaRegalo, $costoEnvoltura, $costoEnvio) {
                $subtotalReal = 0.00;
                $comisionHubReal = 0.00;
                $cantTotal = 0;
                $lineasPedido = [];

                // Validar precios directamente contra la base de datos (seguridad server-side) y verificar stock
                foreach ($carrito as $id => $item) {
                    $producto = Producto::where('id', $id)->lockForUpdate()->firstOrFail();
                    $cant = (int) $item['cantidad'];

                    if ($producto->stock < $cant) {
                        throw new \Exception("Stock insuficiente para «{$producto->nombre_prod}». Solo quedan {$producto->stock} unidades disponibles en taller.");
                    }

                    $precioUnitario = (float) $producto->precio;
                    $subtotalReal += $precioUnitario * $cant;
                    $comisionHubReal += round($precioUnitario * 0.15, 2) * $cant;
                    $cantTotal += $cant;

                    $lineasPedido[] = [
                        'producto' => $producto,
                        'cantidad' => $cant,
                        'precio_unitario' => $precioUnitario,
                    ];
                }

                $totalFinal = $subtotalReal + $costoEnvio + $costoEnvoltura;

                // Crear Pedido maestro
                $nuevoPedido = Pedido::create([
                    'cliente_id' => Auth::id(),
                    'cant_producto' => $cantTotal,
                    'direccion_envio' => $request->input('direccion_envio'),
                    'pais_destino' => $request->input('pais_destino'),
                    'tipo_envio' => $request->input('tipo_envio'),
                    'envoltura_regalo' => $envolturaRegalo,
                    'mensaje_regalo' => $request->input('mensaje_regalo'),
                    'subtotal' => $subtotalReal,
                    'comision_curaduria_hub' => $comisionHubReal,
                    'costo_envio' => $costoEnvio,
                    'total' => $totalFinal,
                    'estado_pedido' => 'pendiente',
                    'codigo_seguimiento' => 'HUB-CUSCO-'.strtoupper(Str::random(8)),
                    'notas' => 'Método: '.$request->input('metodo_pago').' | Teléfono: '.$request->input('telefono_contacto'),
                ]);

                // Asociar productos e historial de precios en tabla pivote y descontar stock
                foreach ($lineasPedido as $linea) {
                    $nuevoPedido->productos()->attach($linea['producto']->id, [
                        'cantidad' => $linea['cantidad'],
                        'precio_unitario_historico' => $linea['precio_unitario'],
                    ]);

                    $linea['producto']->decrement('stock', $linea['cantidad']);
                }

                return $nuevoPedido;
            });

            // Limpiar carrito de sesión tras confirmación exitosa
            session()->forget('carrito');
            session()->forget('envoltura_regalo');

            return redirect()->route('pedidos.show', $pedido->id)
                ->with('success', '¡Tu orden ha sido registrada con éxito! El HUB logístico coordinará con los talleres de Cusco.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error al procesar el pedido: '.$e->getMessage());
        }
    }

    /**
     * Muestra la confirmación y seguimiento de trazabilidad de un pedido.
     */
    public function show(int $id): View
    {
        $pedido = Pedido::with(['productos.artesano', 'cliente'])->findOrFail($id);

        // Control de acceso: solo el cliente dueño, un artesano involucrado o el HUB pueden ver el pedido
        $user = Auth::user();
        if ($user->id !== $pedido->cliente_id && ! $user->isAdminHub() && ! $user->isArtesano()) {
            abort(403, 'No autorizado para ver este pedido.');
        }

        return view('pedidos.show', compact('pedido'));
    }

    /**
     * Historial de pedidos del cliente autenticado.
     */
    public function misPedidos(): View
    {
        $pedidos = Pedido::where('cliente_id', Auth::id())
            ->with(['productos'])
            ->latest()
            ->paginate(10);

        return view('pedidos.index', compact('pedidos'));
    }

    /**
     * Muestra la tarjeta de dedicatoria digital pública accesible mediante el escaneo del código QR.
     */
    public function dedicatoria(string $id_o_codigo): View
    {
        $pedido = Pedido::with(['productos.artesano', 'cliente'])
            ->where('codigo_seguimiento', $id_o_codigo)
            ->orWhere('id', is_numeric($id_o_codigo) ? (int) $id_o_codigo : 0)
            ->firstOrFail();

        return view('dedicatoria', compact('pedido'));
    }
}
