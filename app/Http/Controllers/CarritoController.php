<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarritoController extends Controller
{
    /**
     * Muestra la vista de la bolsa de compras (Shopping basket).
     */
    public function index(): View
    {
        $carrito = session()->get('carrito', []);
        $envolturaRegalo = session()->get('envoltura_regalo', false);
        $totales = $this->calcularTotales($carrito, $envolturaRegalo);

        return view('carrito', compact('carrito', 'totales', 'envolturaRegalo'));
    }

    /**
     * Agrega un producto a la bolsa de compras.
     */
    public function add(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'nullable|integer|min:1',
        ]);

        $producto = Producto::with('artesano')->findOrFail($request->input('producto_id'));
        $cantidad = (int) ($request->input('cantidad', 1));

        if ($producto->stock < $cantidad) {
            $msg = "Lo sentimos, solo disponemos de {$producto->stock} unidades en el taller artesanal.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $carrito = session()->get('carrito', []);
        $id = $producto->id;

        if (isset($carrito[$id])) {
            $nuevaCantidad = $carrito[$id]['cantidad'] + $cantidad;
            if ($nuevaCantidad > $producto->stock) {
                $msg = "Stock máximo alcanzado ({$producto->stock} unidades disponibles).";
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['status' => 'error', 'message' => $msg], 422);
                }

                return back()->with('warning', $msg);
            }
            $carrito[$id]['cantidad'] = $nuevaCantidad;
        } else {
            $carrito[$id] = [
                'id' => $producto->id,
                'nombre' => $producto->nombre_prod,
                'precio' => (float) $producto->precio_efectivo,
                'precio_regular' => (float) $producto->precio,
                'en_oferta' => (bool) $producto->en_oferta,
                'precio_artesano' => $producto->precio_artesano,
                'comision_hub' => $producto->comision_hub,
                'imagen_url' => $producto->imagen_url,
                'artesano' => $producto->artesano->nombre_completo,
                'comunidad' => $producto->artesano->direccion_arte,
                'stock' => $producto->stock,
                'tamano' => $producto->tamano,
                'cantidad' => $cantidad,
            ];
        }

        session()->put('carrito', $carrito);

        $totales = $this->calcularTotales($carrito, session()->get('envoltura_regalo', false));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => "«{$producto->nombre_prod}» fue añadido a tu carrito de compras.",
                'totales' => $totales,
                'cart_count' => array_sum(array_column($carrito, 'cantidad')),
            ]);
        }

        return redirect()->route('carrito.index')->with('success', "«{$producto->nombre_prod}» fue añadido a tu carrito de compras.");
    }

    /**
     * Actualiza la cantidad de un ítem (- / +) o la opción de envoltura de regalo.
     */
    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'producto_id' => 'nullable|exists:productos,id',
            'accion' => 'nullable|in:incrementar,decrementar,set',
            'cantidad' => 'nullable|integer|min:0',
            'envoltura_regalo' => 'nullable|boolean',
        ]);

        $carrito = session()->get('carrito', []);

        if ($request->has('envoltura_regalo')) {
            session()->put('envoltura_regalo', (bool) $request->input('envoltura_regalo'));
        }

        if ($request->filled('producto_id')) {
            $id = $request->input('producto_id');
            if (isset($carrito[$id])) {
                $producto = Producto::find($id);
                $accion = $request->input('accion', 'set');

                if ($accion === 'incrementar') {
                    if ($carrito[$id]['cantidad'] < $producto->stock) {
                        $carrito[$id]['cantidad']++;
                    } else {
                        return response()->json(['status' => 'error', 'message' => 'Stock máximo alcanzado.'], 422);
                    }
                } elseif ($accion === 'decrementar') {
                    if ($carrito[$id]['cantidad'] > 1) {
                        $carrito[$id]['cantidad']--;
                    } else {
                        unset($carrito[$id]);
                    }
                } elseif ($accion === 'set') {
                    $cant = (int) $request->input('cantidad', 1);
                    if ($cant <= 0) {
                        unset($carrito[$id]);
                    } else {
                        $carrito[$id]['cantidad'] = min($cant, $producto->stock);
                    }
                }

                session()->put('carrito', $carrito);
            }
        }

        $envolturaRegalo = session()->get('envoltura_regalo', false);
        $totales = $this->calcularTotales($carrito, $envolturaRegalo);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'carrito' => $carrito,
                'totales' => $totales,
                'cart_count' => array_sum(array_column($carrito, 'cantidad')),
            ]);
        }

        return redirect()->route('carrito.index');
    }

    /**
     * Elimina un ítem específico de la bolsa.
     */
    public function remove(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate(['producto_id' => 'required|integer']);

        $carrito = session()->get('carrito', []);
        $id = $request->input('producto_id');

        if (isset($carrito[$id])) {
            unset($carrito[$id]);
            session()->put('carrito', $carrito);
        }

        $totales = $this->calcularTotales($carrito, session()->get('envoltura_regalo', false));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Pieza retirada del carrito.',
                'totales' => $totales,
                'cart_count' => array_sum(array_column($carrito, 'cantidad')),
            ]);
        }

        return redirect()->route('carrito.index')->with('info', 'Pieza retirada del carrito.');
    }

    /**
     * Vacía por completo el carrito de compras.
     */
    public function clear(): RedirectResponse
    {
        session()->forget('carrito');
        session()->forget('envoltura_regalo');

        return redirect()->route('carrito.index')->with('info', 'El carrito de compras ha sido vaciado.');
    }

    /**
     * Cálculo consolidado de subtotales, comisión de curaduría HUB y envoltura de regalo.
     */
    protected function calcularTotales(array $carrito, bool $envolturaRegalo): array
    {
        $subtotal = 0.00;
        $totalArtesano = 0.00;
        $totalHub = 0.00;
        $cantidadItems = 0;

        foreach ($carrito as $item) {
            $cant = (int) $item['cantidad'];
            $precio = (float) $item['precio'];
            $subtotal += $precio * $cant;
            $totalArtesano += round($precio * 0.85, 2) * $cant;
            $totalHub += round($precio * 0.15, 2) * $cant;
            $cantidadItems += $cant;
        }

        $costoEnvoltura = $envolturaRegalo ? 12.00 : 0.00;
        $total = $subtotal + $costoEnvoltura;

        return [
            'items_count' => $cantidadItems,
            'subtotal' => round($subtotal, 2),
            'total_artesano' => round($totalArtesano, 2),
            'total_hub' => round($totalHub, 2),
            'costo_envoltura' => round($costoEnvoltura, 2),
            'total' => round($total, 2),
        ];
    }
}
