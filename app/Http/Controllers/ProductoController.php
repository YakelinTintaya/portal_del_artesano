<?php

namespace App\Http\Controllers;

use App\Models\Artesano;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductoController extends Controller
{
    /**
     * Vista de bienvenida / Home del Portal del Artesano.
     */
    public function home(): View
    {
        // Todos los productos aprobados para el carrusel completo (ofertas y precios regulares)
        $todosLosProductos = Producto::aprobados()
            ->with('artesano')
            ->orderByDesc('en_oferta')
            ->orderByDesc('puntuacion')
            ->get();

        // Diapositivas destacadas para el Carrusel Principal del Hero: mezcla de ofertas y productos regulares
        $ofertas = $todosLosProductos->where('en_oferta', true)->take(3);
        $regulares = $todosLosProductos->where('en_oferta', false)->take(3);
        $heroSlides = $ofertas->merge($regulares);

        $destacados = $todosLosProductos->take(6);

        $artesanosDestacados = Artesano::where('estado_validacion', true)
            ->withCount('productos')
            ->take(3)
            ->get();

        $categorias = [
            [
                'nombre' => 'Ofertas & Promociones',
                'filtro' => 'Ofertas',
                'subtitulo' => 'Piezas maestras seleccionadas con descuento temporal y respeto irrestricto al comercio justo',
                'imagen' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?auto=format&fit=crop&w=800&q=80',
                'badge' => 'Hasta -20%',
            ],
            [
                'nombre' => 'Textiles',
                'filtro' => 'Textiles',
                'subtitulo' => 'Lana de alpaca virgen, hilado a mano y tintes naturales de San Pedro y Chinchero',
                'imagen' => 'https://images.unsplash.com/photo-1590736969955-71cc94801759?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Platería',
                'filtro' => 'Platería',
                'subtitulo' => 'Plata 950 labrada a mano con técnicas virreinales del barrio de San Blas',
                'imagen' => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Cerámica',
                'filtro' => 'Cerámica',
                'subtitulo' => 'Arcilla cusqueña, policromía andina y réplicas ceremoniales de Pisac',
                'imagen' => 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'nombre' => 'Imaginería',
                'filtro' => 'Imaginería',
                'subtitulo' => 'Esculturas tradicionales, Niños Manuelitos y arte sacro cusqueño',
                'imagen' => 'https://images.unsplash.com/photo-1582738411706-bfc8e691d1c2?auto=format&fit=crop&w=800&q=80',
            ],
        ];

        return view('home', compact('heroSlides', 'todosLosProductos', 'destacados', 'artesanosDestacados', 'categorias'));
    }

    /**
     * Catálogo público con filtros dinámicos y soporte para búsqueda en tiempo real (AJAX/JSON).
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Producto::aprobados()->with('artesano');

        // Búsqueda por término (nombre, descripción, materiales o nombre del artesano)
        if ($request->filled('q')) {
            $term = trim($request->input('q'));
            $query->where(function ($q) use ($term) {
                $q->where('nombre_prod', 'like', "%{$term}%")
                    ->orWhere('descripcion_prod', 'like', "%{$term}%")
                    ->orWhere('materiales_origen', 'like', "%{$term}%")
                    ->orWhereHas('artesano', function ($qa) use ($term) {
                        $qa->where('nombre', 'like', "%{$term}%")
                            ->orWhere('apellido', 'like', "%{$term}%")
                            ->orWhere('direccion_arte', 'like', "%{$term}%");
                    });
            });
        }

        // Filtro por categoría o sección de ofertas
        if ($request->filled('categoria') && $request->input('categoria') !== 'todas') {
            $cat = $request->input('categoria');
            if (in_array(strtolower($cat), ['ofertas', 'oferta', 'promociones', 'descuentos'])) {
                $query->where('en_oferta', true);
            } else {
                $query->where('categoria', $cat);
            }
        }

        if ($request->boolean('ofertas')) {
            $query->where('en_oferta', true);
        }

        // Filtro por tamaño
        if ($request->filled('tamano') && $request->input('tamano') !== 'todos') {
            $query->where('tamano', $request->input('tamano'));
        }

        // Filtro por rango de precio
        if ($request->filled('precio_min')) {
            $query->where('precio', '>=', (float) $request->input('precio_min'));
        }

        if ($request->filled('precio_max')) {
            $query->where('precio', '<=', (float) $request->input('precio_max'));
        }

        // Ordenamiento
        match ($request->input('sort')) {
            'precio_asc' => $query->orderBy('precio', 'asc'),
            'precio_desc' => $query->orderBy('precio', 'desc'),
            'puntuacion' => $query->orderByDesc('puntuacion'),
            default => $query->latest(),
        };

        $productos = $query->paginate(9)->withQueryString();

        // Si la petición es AJAX o solicita JSON, devolver formato estructurado para app.js
        if ($request->ajax() || $request->wantsJson() || $request->boolean('json')) {
            return response()->json([
                'status' => 'success',
                'count' => $productos->total(),
                'data' => $productos->items(),
                'pagination' => [
                    'current_page' => $productos->currentPage(),
                    'last_page' => $productos->lastPage(),
                    'per_page' => $productos->perPage(),
                    'total' => $productos->total(),
                ],
            ]);
        }

        $categorias = ['Textiles', 'Platería', 'Cerámica', 'Imaginería', 'Ofertas'];
        $tamanos = ['Pequeño', 'Mediano', 'Grande', 'Único'];

        return view('catalogo', compact('productos', 'categorias', 'tamanos'));
    }

    /**
     * Detalle de la pieza artesanal con ficha de trazabilidad cultural y desglose de comercio justo.
     */
    public function show(int $id): View
    {
        $producto = Producto::with(['artesano.user'])->findOrFail($id);

        // Productos similares de la misma categoría o taller
        $similares = Producto::aprobados()
            ->where('id', '!=', $producto->id)
            ->where(function ($q) use ($producto) {
                $q->where('categoria', $producto->categoria)
                    ->orWhere('artesano_id', $producto->artesano_id);
            })
            ->take(3)
            ->get();

        return view('producto-detalle', compact('producto', 'similares'));
    }
}
