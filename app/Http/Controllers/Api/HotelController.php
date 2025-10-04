<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHotelRequest;
use App\Http\Requests\UpdateHotelRequest;
use App\Http\Resources\HotelResource;
use App\Http\Requests\DisponibilidadHotelRequest;
use App\Models\Hotel;
use App\Models\Servicio;
use App\Models\Habitacion;
use App\Models\ReservaHabitacion;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HotelController extends Controller
{
    // GET /api/hoteles
    public function index(Request $request): JsonResponse
    {
        // --- MODO DISPONIBILIDAD GLOBAL (con check_in + check_out) ---
        if ($request->filled('check_in') && $request->filled('check_out')) {
            $checkIn  = Carbon::parse($request->query('check_in'))->toDateString();
            $checkOut = Carbon::parse($request->query('check_out'))->toDateString();

            $adultos = $request->query('adultos'); // opcional
            $ninos   = $request->query('ninos');   // opcional
            $reqHab  = (int) ($request->query('habitaciones', 1));

            // Filtros opcionales de destino (útiles para buscar por ciudad/pais)
            $pais   = $request->query('pais');
            $ciudad = $request->query('ciudad');

            // 1) Traer hoteles activos con servicio (y habitaciones) aplicando filtros de destino
            $hoteles = Hotel::with(['servicio:id,nombre,tipo,descripcion,ciudad,pais,imagen_url,activo', 'habitaciones'])
                ->whereHas('servicio', function ($q) use ($pais, $ciudad) {
                    $q->where('activo', true)
                      ->when($pais,   fn($qq) => $qq->where('pais', $pais))
                      ->when($ciudad, fn($qq) => $qq->where('ciudad', $ciudad));
                })
                ->get();

            if ($hoteles->isEmpty()) {
                return response()->json(['data' => []], 200);
            }

            // 2) Filtrar por capacidad y recolectar IDs de habitaciones candidatas
            $habitacionIds = [];
            $hoteles->each(function ($hotel) use (&$habitacionIds, $adultos, $ninos) {
                $cands = $hotel->habitaciones
                    ->when($adultos !== null, fn($c) => $c->where('capacidad_adultos', '>=', (int)$adultos))
                    ->when($ninos   !== null, fn($c) => $c->where('capacidad_ninos',   '>=', (int)$ninos));
                $habitacionIds = array_merge($habitacionIds, $cands->pluck('id')->all());
            });

            if (empty($habitacionIds)) {
                return response()->json(['data' => []], 200);
            }

            // 3) Cargar ocupación (reservas) para TODO el conjunto en el rango dado
            $ocupacion = ReservaHabitacion::query()
                ->selectRaw('habitacion_id, COALESCE(SUM(cantidad),0) as unidades_ocupadas')
                ->whereIn('estado', ['pendiente','confirmada'])
                ->whereDate('fecha_inicio', '<',  $checkOut)
                ->whereDate('fecha_fin',    '>',  $checkIn)
                ->whereIn('habitacion_id', $habitacionIds)
                ->groupBy('habitacion_id')
                ->pluck('unidades_ocupadas', 'habitacion_id');

            // 4) Construir respuesta por hotel
            $filtros = [
                'adultos'      => $adultos !== null ? (int)$adultos : null,
                'ninos'        => $ninos   !== null ? (int)$ninos   : null,
                'habitaciones' => $reqHab,
            ];

            $items = [];
            foreach ($hoteles as $hotel) {
                $cands = $hotel->habitaciones
                    ->when($adultos !== null, fn($c) => $c->where('capacidad_adultos', '>=', (int)$adultos))
                    ->when($ninos   !== null, fn($c) => $c->where('capacidad_ninos',   '>=', (int)$ninos));

                $habitacionesDisponibles = [];
                foreach ($cands as $h) {
                    $ocupadas     = (int) ($ocupacion[$h->id] ?? 0);
                    $disponibles  = max(0, (int)$h->cantidad - $ocupadas);

                    if ($disponibles >= $reqHab) {
                        $habitacionesDisponibles[] = [
                            'id'                   => $h->id,
                            'nombre'               => $h->nombre,
                            'capacidad_adultos'    => (int)$h->capacidad_adultos,
                            'capacidad_ninos'      => (int)$h->capacidad_ninos,
                            'precio_por_noche'     => (float)$h->precio_por_noche,
                            'unidades_totales'     => (int)$h->cantidad,
                            'unidades_disponibles' => $disponibles,
                            'descripcion'          => $h->descripcion,
                        ];
                    }
                }

                if (!empty($habitacionesDisponibles)) {
                    $items[] = (new HotelResource(
                        $hotel,
                        $checkIn,
                        $checkOut,
                        $filtros,
                        $habitacionesDisponibles
                    ))->toArray($request);
                }
            }

            return response()->json(['data' => $items], 200);
        }

        // --- MODO LISTA NORMAL (sin check_in/check_out) ---
        $hoteles = Hotel::with([
            'servicio:id,nombre,tipo,descripcion,ciudad,pais,imagen_url,activo',
            'habitaciones:id,servicio_id,precio_por_noche,cantidad'
        ])->get();

        return HotelResource::collection($hoteles)->response();
    }

    /**
     * POST /api/hoteles
     * Crea Servicio(tipo=hotel) + Hotel en una transacción.
     * 🔹 Ahora acepta JSON plano (sin objeto "servicio").
     */
    public function store(StoreHotelRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $hotel = DB::transaction(function () use ($data, $user) {
            // 1) Crear SERVICIO (nombre/ciudad/pais/… vienen directos del body)
            $servicio = Servicio::create([
                'proveedor_id' => $user->id,                 // dueño = usuario autenticado
                'nombre'       => $data['nombre'],
                'tipo'         => 'hotel',                   // se fuerza aquí
                'descripcion'  => $data['descripcion'] ?? null,
                'ciudad'       => $data['ciudad'],
                'pais'         => $data['pais'],
                'imagen_url'   => $data['imagen_url'] ?? null,
                'activo'       => $data['activo'] ?? true,
            ]);

            // 2) Crear HOTEL (detalle)
            return Hotel::create([
                'servicio_id' => $servicio->id,
                'direccion'   => $data['direccion'],
                'estrellas'   => $data['estrellas'] ?? null,
            ]);
        });

        return response()->json([
            'message' => 'Servicio y hotel creados correctamente.',
            'data'    => $hotel->load('servicio:id,nombre,ciudad,pais'),
        ], 201);
    }

    // GET /api/hoteles/{servicio_id}
    public function show(int $servicio_id): JsonResponse
    {
        $hotel = Hotel::with(['servicio:id,nombre,ciudad,pais','habitaciones'])->find($servicio_id);
        if (!$hotel) return response()->json(['message' => 'Hotel no encontrado'], 404);

        return response()->json([
            'hotel' => [
                'servicio_id' => $hotel->servicio_id,
                'direccion'   => $hotel->direccion,
                'estrellas'   => $hotel->estrellas,
                'created_at'  => $hotel->created_at,
                'updated_at'  => $hotel->updated_at,
                'nombre'      => $hotel->servicio->nombre ?? null,
                'ciudad'      => $hotel->servicio->ciudad ?? null,
                'pais'        => $hotel->servicio->pais ?? null,
            ],
            'habitaciones'=> $hotel->habitaciones->map(fn($h) => [
                'id'                 => $h->id,
                'nombre'             => $h->nombre,
                'capacidad_adultos'  => (int) $h->capacidad_adultos,
                'capacidad_ninos'    => (int) $h->capacidad_ninos,
                'cantidad'           => (int) $h->cantidad,
                'precio_por_noche'   => (float) $h->precio_por_noche,
            ]),
        ], 200);
    }

    // PUT /api/hoteles/{servicio_id}
    public function update(UpdateHotelRequest $request, int $servicio_id): JsonResponse
    {
        $hotel = Hotel::with('servicio')->find($servicio_id);
        if (!$hotel) {
            return response()->json(['message' => 'Hotel no encontrado'], 404);
        }

        $data = $request->validated();

        DB::transaction(function () use ($hotel, $data) {
            // Actualizar HOTEL (solo si hay datos de hotel)
            $hotelData = array_intersect_key($data, array_flip(['direccion', 'estrellas']));
            if (!empty($hotelData)) {
                $hotel->update($hotelData);
            }

            // Actualizar SERVICIO relacionado (nombre, descripción, etc.)
            $servicioData = array_intersect_key($data, array_flip([
                'nombre', 'descripcion', 'ciudad', 'pais', 'imagen_url', 'activo'
            ]));
            if (!empty($servicioData)) {
                $hotel->servicio->update($servicioData);
            }
        });

        $hotel->refresh()->load('servicio');

        return response()->json([
            'message' => 'Hotel actualizado correctamente.',
            'data'    => [
                'hotel' => $hotel,
                'servicio' => $hotel->servicio,
            ],
        ], 200);
    }

    // DELETE /api/hoteles/{servicio_id}
    public function destroy(UpdateHotelRequest $request, int $servicio_id): JsonResponse
    {
        $hotel = Hotel::find($servicio_id);
        if (!$hotel) return response()->json(['message' => 'Hotel no encontrado'], 404);

        $hotel->delete();
        return response()->json(null, 204);
    }

    // GET /api/hoteles/{servicio_id}/disponibilidad  (modo por un solo hotel)
    public function disponibilidad(DisponibilidadHotelRequest $request, int $servicio_id): JsonResponse
    {
        $hotel = Hotel::with([
            'servicio:id,nombre,tipo,descripcion,ciudad,pais,imagen_url,activo',
            'habitaciones:id,servicio_id,precio_por_noche'
        ])->where('servicio_id', $servicio_id)->first();

        if (!$hotel || !$hotel->servicio || !$hotel->servicio->activo) {
            return response()->json(['message' => 'Hotel no disponible o no encontrado.'], 404);
        }

        $checkIn  = Carbon::parse($request->input('check_in'))->toDateString();
        $checkOut = Carbon::parse($request->input('check_out'))->toDateString();

        $adultos  = $request->input('adultos');
        $ninos    = $request->input('ninos');
        $reqHab   = (int) ($request->input('habitaciones') ?? 1);

        $habitaciones = Habitacion::query()
            ->where('servicio_id', $servicio_id)
            ->when($adultos !== null, fn($q) => $q->where('capacidad_adultos', '>=', $adultos))
            ->when($ninos !== null, fn($q)   => $q->where('capacidad_ninos',   '>=', $ninos))
            ->get(['id','servicio_id','nombre','capacidad_adultos','capacidad_ninos','cantidad','precio_por_noche','descripcion']);

        $reservas = ReservaHabitacion::query()
            ->selectRaw('habitacion_id, COALESCE(SUM(cantidad),0) as unidades_ocupadas')
            ->whereIn('estado', ['pendiente','confirmada'])
            ->whereDate('fecha_inicio', '<',  $checkOut)
            ->whereDate('fecha_fin',    '>',  $checkIn)
            ->whereIn('habitacion_id', $habitaciones->pluck('id'))
            ->groupBy('habitacion_id')
            ->pluck('unidades_ocupadas', 'habitacion_id');

        $resultado = [];
        foreach ($habitaciones as $h) {
            $ocupadas = (int) ($reservas[$h->id] ?? 0);
            $disponibles = max(0, (int)$h->cantidad - $ocupadas);
            if ($disponibles < $reqHab) continue;

            $resultado[] = [
                'id'                   => $h->id,
                'nombre'               => $h->nombre,
                'capacidad_adultos'    => (int) $h->capacidad_adultos,
                'capacidad_ninos'      => (int) $h->capacidad_ninos,
                'precio_por_noche'     => (float) $h->precio_por_noche,
                'unidades_totales'     => (int) $h->cantidad,
                'unidades_disponibles' => $disponibles,
                'descripcion'          => $h->descripcion,
            ];
        }

        $filtros = [
            'adultos' => $adultos,
            'ninos' => $ninos,
            'habitaciones' => $reqHab,
        ];

        return (new HotelResource(
            $hotel,
            $checkIn,
            $checkOut,
            $filtros,
            array_values($resultado)
        ))->response();
    }
}
