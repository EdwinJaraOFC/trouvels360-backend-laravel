<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServicioRequest;
use App\Http\Requests\UpdateServicioRequest;
use App\Models\Servicio;
use App\Models\Habitacion;
use App\Models\TourSalida;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    // GET /api/servicios (público)
    public function index(Request $request): JsonResponse
    {
        $q = Servicio::query()
            ->select('id','proveedor_id','nombre','tipo','ciudad','pais','descripcion','imagen_url','activo','created_at');

        // Filtros básicos
        if ($request->filled('tipo'))   $q->where('tipo', $request->query('tipo'));      // 'hotel' | 'tour'
        if ($request->filled('ciudad')) $q->where('ciudad', $request->query('ciudad'));
        if ($request->filled('pais'))   $q->where('pais', $request->query('pais'));
        if ($request->filled('activo')) $q->where('activo', filter_var($request->query('activo'), FILTER_VALIDATE_BOOLEAN));

        if ($request->filled('q')) {
            $term = trim($request->query('q'));
            $q->where(function ($w) use ($term) {
                $w->where('nombre','like',"%{$term}%")
                  ->orWhere('descripcion','like',"%{$term}%");
            });
        }

        // Orden
        $orden = $request->query('orden', 'recientes');
        $orden === 'recientes'
            ? $q->orderByDesc('id')
            : $q->orderBy('nombre');

        // Paginación
        $perPage = max(1, min((int) $request->query('per_page', 12), 50));
        $paginator = $q->paginate($perPage);

        return response()->json($paginator, 200);
    }

    // GET /api/proveedor/servicios (privado - requiere auth)
    public function indexMine(Request $request): JsonResponse
    {
        $user = $request->user();

        $tipo   = $request->query('tipo');   // 'hotel' | 'tour' | null
        $ciudad = $request->query('ciudad');
        $pais   = $request->query('pais');

        // Solo filtrar por 'activo' si el parámetro viene en la URL
        $activo = $request->has('activo')
            ? filter_var($request->query('activo'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;

        $search = $request->query('search');

        $perPage   = min((int) $request->query('per_page', 15), 100);
        $sort      = $request->query('sort', '-created_at'); // ej: 'nombre' o '-nombre'
        $dir       = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $sortField = ltrim($sort, '-');

        $sortable = ['nombre', 'tipo', 'ciudad', 'pais', 'activo', 'created_at'];
        if (!in_array($sortField, $sortable, true)) {
            $sortField = 'created_at';
        }

        $query = Servicio::query()
            ->where('proveedor_id', $user->id)
            ->when($tipo,   fn($q) => $q->where('tipo', $tipo))
            ->when($ciudad, fn($q) => $q->where('ciudad', $ciudad))
            ->when($pais,   fn($q) => $q->where('pais', $pais))
            ->when($activo !== null, fn($q) => $q->where('activo', $activo))
            ->when($search, fn($q) => $q->where('nombre', 'like', "%{$search}%"));

        // Relaciones + métricas
        $query->with([
                // OJO: hoteles no tiene columna 'id' → NO la pidas
                'hotel:servicio_id,direccion,estrellas',
                // tours también usa PK = servicio_id; su precio es 'precio'
                'tour:servicio_id,categoria,fecha,duracion,precio',
            ])
            ->withCount([
                'habitaciones as habitaciones_count',
                'salidas as salidas_count',
            ])
            ->select('servicios.*')
            ->selectSub(
                Habitacion::selectRaw('MIN(precio_por_noche)')
                    ->whereColumn('servicio_id', 'servicios.id'),
                'tarifa_min_hotel'
            )
            ->selectSub(
                TourSalida::selectRaw('COUNT(*)')
                    ->whereColumn('servicio_id', 'servicios.id')
                    ->where('fecha', '>=', now()->toDateString()),
                'proximas_salidas_count'
            )
            ->orderBy($sortField, $dir);

        $servicios = $query->paginate($perPage)->appends($request->query());

        $data = $servicios->getCollection()->map(function ($s) {
            $base = [
                'id'          => $s->id,
                'tipo'        => $s->tipo,
                'nombre'      => $s->nombre,
                'descripcion' => $s->descripcion,
                'ciudad'      => $s->ciudad,
                'pais'        => $s->pais,
                'imagen_url'  => $s->imagen_url,
                'activo'      => (bool) $s->activo,
                'created_at'  => $s->created_at,
            ];

            if ($s->tipo === 'hotel') {
                return $base + [
                    'meta_tipo' => [
                        'direccion'           => $s->hotel->direccion ?? null,
                        'estrellas'           => $s->hotel->estrellas ?? null,
                        'habitaciones_count'  => $s->habitaciones_count,
                        'tarifa_min_desde'    => $s->tarifa_min_hotel !== null ? (float) $s->tarifa_min_hotel : null,
                    ],
                ];
            }

            // tipo === 'tour'
            return $base + [
                'meta_tipo' => [
                    'categoria'         => $s->tour->categoria ?? null,
                    'fecha_base'        => $s->tour->fecha ?? null,      // si mantienes fecha en tours
                    'duracion'          => $s->tour->duracion ?? null,
                    'precio'            => isset($s->tour->precio) ? (float) $s->tour->precio : null,
                    'salidas_count'     => $s->salidas_count,
                    'proximas_salidas'  => (int) $s->proximas_salidas_count,
                ],
            ];
        });

        return response()->json([
            'data'  => $data,
            'meta'  => [
                'total'        => $servicios->total(),
                'per_page'     => $servicios->perPage(),
                'current_page' => $servicios->currentPage(),
                'last_page'    => $servicios->lastPage(),
            ],
            'links' => [
                'first' => $servicios->url(1),
                'prev'  => $servicios->previousPageUrl(),
                'next'  => $servicios->nextPageUrl(),
                'last'  => $servicios->url($servicios->lastPage()),
            ],
        ]);
    }

    // POST /api/servicios
    public function store(StoreServicioRequest $request): JsonResponse
    {
        $servicio = Servicio::create($request->validated());

        return response()->json([
            'message' => 'Servicio creado exitosamente.',
            'data'    => $servicio->only('id','proveedor_id','nombre','tipo','ciudad','pais','activo','created_at'),
        ], 201);
    }

    // GET /api/servicios/{servicio}
    public function show(Servicio $servicio): JsonResponse
    {
        return response()->json(
            $servicio->only('id','proveedor_id','nombre','tipo','ciudad','pais','descripcion','imagen_url','activo','created_at','updated_at'),
            200
        );
    }

    // PUT/PATCH /api/servicios/{servicio}
    public function update(UpdateServicioRequest $request, Servicio $servicio): JsonResponse
    {
        // impedir cambiar 'tipo' tras crear (opcional)
        if ($request->filled('tipo') && $request->input('tipo') !== $servicio->tipo) {
            return response()->json(['message' => 'No se permite cambiar el tipo del servicio.'], 422);
        }

        $servicio->update($request->validated());
        $servicio->refresh();

        return response()->json([
            'message' => 'Servicio modificado exitosamente.',
            'data'    => $servicio->only('id','proveedor_id','nombre','tipo','ciudad','pais','activo','updated_at'),
        ], 200);
    }

    // DELETE /api/servicios/{servicio}
    public function destroy(Servicio $servicio): JsonResponse
    {
        $servicio->delete();
        return response()->json(null, 204);
    }
}
