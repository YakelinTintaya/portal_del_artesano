<?php

namespace App\Http\Controllers;

use App\Models\Artesano;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HubAdminController extends Controller
{
    /**
     * Dashboard general de administración del HUB Logístico Cusco.
     */
    public function index(): View
    {
        $metricas = [
            'total_ventas' => Pedido::where('estado_pedido', '!=', 'cancelado')->sum('total'),
            'comisiones_hub' => Pedido::where('estado_pedido', '!=', 'cancelado')->sum('comision_curaduria_hub'),
            'pagos_artesanos' => Pedido::where('estado_pedido', '!=', 'cancelado')->sum('subtotal') - Pedido::where('estado_pedido', '!=', 'cancelado')->sum('comision_curaduria_hub'),
            'artesanos_validados' => Artesano::where('estado_validacion', true)->count(),
            'artesanos_pendientes' => Artesano::where('estado_validacion', false)->count(),
            'productos_publicados' => Producto::where('aprobado_hub', true)->count(),
            'productos_por_revisar' => Producto::where('aprobado_hub', false)->count(),
            'pedidos_totales' => Pedido::count(),
            'pedidos_en_acopio' => Pedido::whereIn('estado_pedido', ['pendiente', 'confirmado_artesano', 'en_acopio_hub'])->count(),
        ];

        $pedidosRecientes = Pedido::with(['cliente', 'productos.artesano'])
            ->latest()
            ->take(8)
            ->get();

        $artesanosPendientesList = Artesano::where('estado_validacion', false)
            ->with('user')
            ->get();

        $productosPendientesList = Producto::where('aprobado_hub', false)
            ->with('artesano')
            ->take(6)
            ->get();

        return view('admin.dashboard', compact(
            'metricas',
            'pedidosRecientes',
            'artesanosPendientesList',
            'productosPendientesList'
        ));
    }

    /**
     * Valida o revoca la habilitación de un artesano por el HUB.
     */
    public function toggleValidacionArtesano(int $id): RedirectResponse
    {
        $artesano = Artesano::findOrFail($id);
        $artesano->estado_validacion = ! $artesano->estado_validacion;
        $artesano->save();

        $accion = $artesano->estado_validacion ? 'validado y habilitado' : 'suspendido';

        return back()->with('success', "El artesano «{$artesano->nombre_completo}» ha sido {$accion} por el HUB.");
    }

    /**
     * Otorga permiso de publicación o retira una pieza artesanal del catálogo público.
     */
    public function toggleAprobacionProducto(int $id): RedirectResponse
    {
        $producto = Producto::with('artesano')->findOrFail($id);

        if (! $producto->artesano->estado_validacion && ! $producto->aprobado_hub) {
            return back()->with('error', "No se puede aprobar la pieza: el taller de {$producto->artesano->nombre_completo} aún no está validado por el HUB.");
        }

        $producto->aprobado_hub = ! $producto->aprobado_hub;
        $producto->save();

        $accion = $producto->aprobado_hub ? 'aprobado para publicación internacional' : 'retirado de vitrina';

        return back()->with('success', "El producto «{$producto->nombre_prod}» ha sido {$accion}.");
    }

    /**
     * Actualiza el estado logístico del pedido (respetando la inmutabilidad de precios históricos).
     */
    public function updateEstadoPedido(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'estado_pedido' => 'required|in:pendiente,confirmado_artesano,en_acopio_hub,enviado_internacional,entregado,cancelado',
            'codigo_seguimiento' => 'nullable|string|max:100',
        ]);

        $pedido = Pedido::findOrFail($id);

        // Restricción de negocio: una vez cerrado o entregado, no se debe manipular
        if ($pedido->estado_pedido === 'entregado' && $request->input('estado_pedido') !== 'entregado') {
            return back()->with('error', 'El pedido ya fue entregado y no puede revertirse su estado histórico.');
        }

        $pedido->estado_pedido = $request->input('estado_pedido');

        if ($request->filled('codigo_seguimiento')) {
            $pedido->codigo_seguimiento = $request->input('codigo_seguimiento');
        }

        $pedido->save();

        return back()->with('success', "Estado del pedido #{$pedido->id} actualizado a: {$pedido->estado_legible}.");
    }
}
