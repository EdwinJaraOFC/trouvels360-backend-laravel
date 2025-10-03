<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServicioRequest;
use App\Http\Requests\UpdateServicioRequest;
use App\Models\Servicio;
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
