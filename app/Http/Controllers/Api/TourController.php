<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTourRequest;
use App\Http\Requests\UpdateTourRequest;
use App\Models\Servicio;
use App\Models\Tour;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TourController extends Controller
{
    // GET /api/tours (público, con filtros sobre Servicio)
    public function index(Request $request): JsonResponse
    {
        $q = Tour::query()
            ->with('servicio:id,proveedor_id,nombre,ciudad,descripcion,imagen_url,activo,created_at')
            ->select('servicio_id','precio_persona','capacidad_por_salida');

        if ($request->filled('ciudad')) {
            $ciudad = $request->query('ciudad');
            $q->whereHas('servicio', fn($s) => $s->where('ciudad', $ciudad));
        }

        if ($request->filled('activo')) {
            $activo = filter_var($request->query('activo'), FILTER_VALIDATE_BOOLEAN);
            $q->whereHas('servicio', fn($s) => $s->where('activo', $activo));
        } else {
            $q->whereHas('servicio', fn($s) => $s->where('activo', true));
        }

        if ($request->filled('q')) {
            $term = trim($request->query('q'));
            $q->whereHas('servicio', function ($s) use ($term) {
                $s->where('nombre','like',"%{$term}%")
                  ->orWhere('descripcion','like',"%{$term}%");
            });
        }

        $q->orderByDesc('servicio_id');

        $perPage = max(1, min((int)$request->query('per_page', 12), 50));
        $paginator = $q->paginate($perPage);

        $paginator->setCollection($paginator->getCollection()->map(function (Tour $t) {
            return [
                'servicio_id'        => $t->servicio_id,
                'nombre'             => $t->servicio->nombre,
                'ciudad'             => $t->servicio->ciudad,
                'descripcion'        => $t->servicio->descripcion,
                'imagen_url'         => $t->servicio->imagen_url,
                'precio_persona'     => (float) $t->precio_persona,
                'capacidad_por_salida'=> (int) $t->capacidad_por_salida,
                'activo'             => (bool) $t->servicio->activo,
                'created_at'         => $t->servicio->created_at,
            ];
        }));

        return response()->json($paginator, 200);
    }

    // GET /api/tours/{tour}  (binding por servicio_id)
    public function show(Tour $tour): JsonResponse
    {
        $tour->load(['servicio','salidas','actividades' => fn($q) => $q->orderBy('orden')]);

        return response()->json([
            'servicio_id'        => $tour->servicio_id,
            'nombre'             => $tour->servicio->nombre,
            'ciudad'             => $tour->servicio->ciudad,
            'descripcion'        => $tour->servicio->descripcion,
            'imagen_url'         => $tour->servicio->imagen_url,
            'activo'             => (bool) $tour->servicio->activo,
            'precio_persona'     => (float) $tour->precio_persona,
            'capacidad_por_salida'=> (int) $tour->capacidad_por_salida,
            'salidas'            => $tour->salidas->map(fn($s)=>[
                'id'             => $s->id,
                'fecha'          => $s->fecha->toDateString(),
                'hora'           => (string) $s->hora,
                'cupo_reservado' => (int) $s->cupo_reservado,
                'estado'         => $s->estado,
            ]),
            'actividades'        => $tour->actividades->map(fn($a)=>[
                'id'           => $a->id,
                'orden'        => (int) $a->orden,
                'titulo'       => $a->titulo,
                'descripcion'  => $a->descripcion,
                'duracion_min' => $a->duracion_min ? (int) $a->duracion_min : null,
                'direccion'    => $a->direccion,
                'imagen_url'   => $a->imagen_url,
            ]),
        ], 200);
    }

    // POST /api/tours  (crear detalle de tour para un servicio existente tipo='tour')
    public function store(StoreTourRequest $request): JsonResponse
    {
        $user = $request->user();

        // 1) Buscar el servicio y validar pertenencia + tipo
        $servicio = Servicio::find($request->input('servicio_id'));
        if (!$servicio || $servicio->tipo !== 'tour' || $servicio->proveedor_id !== $user->id) {
            return response()->json(['message' => 'Servicio inválido o no autorizado.'], 403);
        }

        // 2) Evitar duplicado 1:1
        if (Tour::find($servicio->id)) {
            return response()->json(['message' => 'El tour ya existe para este servicio.'], 422);
        }

        // 3) Crear detalle
        $tour = Tour::create($request->validated());

        return response()->json([
            'message' => 'Tour creado correctamente.',
            'data'    => [
                'servicio_id'        => $tour->servicio_id,
                'precio_persona'     => (float) $tour->precio_persona,
                'capacidad_por_salida'=> (int) $tour->capacidad_por_salida,
            ],
        ], 201);
    }

    // PUT/PATCH /api/tours/{tour} (actualiza SOLO campos del tour)
    public function update(UpdateTourRequest $request, Tour $tour): JsonResponse
    {
        $user = $request->user();
        if ($tour->servicio->proveedor_id !== $user->id) {
            return response()->json(['message'=>'No autorizado.'], 403);
        }

        $tour->update($request->validated());
        $tour->refresh();

        return response()->json([
            'message' => 'Tour actualizado correctamente.',
            'data'    => [
                'servicio_id'        => $tour->servicio_id,
                'precio_persona'     => (float) $tour->precio_persona,
                'capacidad_por_salida'=> (int) $tour->capacidad_por_salida,
            ],
        ], 200);
    }

    // DELETE /api/tours/{tour}
    public function destroy(Request $request, Tour $tour): JsonResponse
    {
        $user = $request->user();
        if ($tour->servicio->proveedor_id !== $user->id) {
            return response()->json(['message'=>'No autorizado.'], 403);
        }

        // Borra el Servicio para cascada (Tour, salidas, actividades, reservas)
        $tour->servicio->delete();
        return response()->json(null, 204);
    }
}
