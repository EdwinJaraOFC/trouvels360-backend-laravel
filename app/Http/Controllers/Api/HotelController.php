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

class HotelController extends Controller
{

    // GET /api/hoteles (listar todos los hoteles con su servicio y proveedor)
    public function index(): JsonResponse
    {
        $hoteles = Hotel::with([
            'servicio:id,nombre,tipo,descripcion,ciudad,imagen_url,activo',
            'habitaciones:id,servicio_id,precio_por_noche'
        ])->get();
        
        return HotelResource::collection($hoteles)->response();
    }

    // POST /api/hoteles  (crear detalle para servicio tipo=hotel)
    public function store(StoreHotelRequest $request): JsonResponse
    {
        // Evita duplicar 1:1
        if (Hotel::find($request->input('servicio_id'))) {
            return response()->json(['message' => 'El hotel ya existe para este servicio.'], 422);
        }

        $hotel = Hotel::create($request->validated());

        return response()->json([
            'message' => 'Hotel creado correctamente.',
            'data'    => $hotel,
        ], 201);
    }

    // GET /api/hoteles/{servicio_id}
    public function show(int $servicio_id): JsonResponse
    {
        $hotel = Hotel::with('habitaciones')->find($servicio_id);
        if (!$hotel) return response()->json(['message' => 'Hotel no encontrado'], 404);

        return response()->json([
            'hotel'       => $hotel->only('servicio_id','nombre','direccion','estrellas','created_at','updated_at'),
            'habitaciones'=> $hotel->habitaciones->map(fn($h) => [
                'id'                 => $h->id,
                'nombre'             => $h->nombre,
                'capacidad_adultos'  => $h->capacidad_adultos,
                'capacidad_ninos'    => $h->capacidad_ninos,
                'cantidad'           => $h->cantidad,
                'precio_por_noche'   => (float) $h->precio_por_noche,
            ]),
        ], 200);
    }

    // PUT /api/hoteles/{servicio_id}
    public function update(UpdateHotelRequest $request, int $servicio_id): JsonResponse
    {
        $hotel = Hotel::find($servicio_id);
        if (!$hotel) return response()->json(['message' => 'Hotel no encontrado'], 404);

        $hotel->update($request->validated());
        $hotel->refresh();

        return response()->json([
            'message' => 'Hotel actualizado correctamente.',
            'data'    => $hotel,
        ], 200);
    }

    // DELETE /api/hoteles/{servicio_id}
    public function destroy(int $servicio_id): JsonResponse
    {
        $hotel = Hotel::find($servicio_id);
        if (!$hotel) return response()->json(['message' => 'Hotel no encontrado'], 404);

        $hotel->delete(); // cascada borra habitaciones y reservas
        return response()->json(null, 204);
    }

    /**
     * GET /api/hoteles/{servicio_id}/disponibilidad
     * Query: check_in=YYYY-MM-DD&check_out=YYYY-MM-DD&adultos=&ninos=&habitaciones=
     */
    public function disponibilidad(DisponibilidadHotelRequest $request, int $servicio_id): JsonResponse
    {
        // 1) Validar que exista el hotel
        $hotel = Hotel::with([
            'servicio:id,nombre,tipo,descripcion,ciudad,imagen_url,activo',
            'habitaciones:id,servicio_id,precio_por_noche'
        ])->where('servicio_id', $servicio_id)->first();

        if (!$hotel || !$hotel->servicio || !$hotel->servicio->activo) {
            return response()->json(['message' => 'Hotel no disponible o no encontrado.'], 404);
        }

        // 2) Tomar parámetros
        $checkIn  = Carbon::parse($request->input('check_in'))->startOfDay();
        $checkOut = Carbon::parse($request->input('check_out'))->endOfDay();

        $adultos       = $request->input('adultos');       // opcional
        $ninos         = $request->input('ninos');         // opcional
        $reqHabitaciones = (int) ($request->input('habitaciones') ?? 1); // por defecto 1

        // 3) Traer habitaciones del hotel (por servicio_id)
        $habitaciones = Habitacion::query()
            ->where('servicio_id', $servicio_id)
            // Filtro por capacidad si se envía
            ->when($adultos !== null, fn($q) => $q->where('capacidad_adultos', '>=', $adultos))
            ->when($ninos !== null, fn($q)   => $q->where('capacidad_ninos',   '>=', $ninos))
            ->get([
                'id','servicio_id','nombre','capacidad_adultos','capacidad_ninos','cantidad','precio_por_noche','descripcion'
            ]);

        // 4) Computar reservas solapadas por habitación en el rango
        //    Regla de solape: (res.fecha_inicio <= check_out) AND (res.fecha_fin >= check_in)
        $reservas = ReservaHabitacion::query()
            ->selectRaw('habitacion_id, COALESCE(SUM(cantidad),0) as unidades_ocupadas')
            ->whereIn('estado', ['pendiente','confirmada'])
            ->whereDate('fecha_inicio', '<=', $checkOut->toDateString())
            ->whereDate('fecha_fin',    '>=', $checkIn->toDateString())
            ->whereIn('habitacion_id', $habitaciones->pluck('id'))
            ->groupBy('habitacion_id')
            ->pluck('unidades_ocupadas', 'habitacion_id'); // [habitacion_id => ocupadas]

        // 5) Construir payload con disponibilidad (cantidad - ocupadas)
        $resultado = [];
        foreach ($habitaciones as $h) {
            $ocupadas = (int) ($reservas[$h->id] ?? 0);
            $disponibles = max(0, (int)$h->cantidad - $ocupadas);

            // Si el cliente pide n habitaciones, filtra las que tienen suficientes unidades
            if ($disponibles < $reqHabitaciones) {
                continue;
            }

            $resultado[] = [
                'id'                => $h->id,
                'nombre'            => $h->nombre,
                'capacidad_adultos' => (int) $h->capacidad_adultos,
                'capacidad_ninos'   => (int) $h->capacidad_ninos,
                'precio_por_noche'  => (float) $h->precio_por_noche,
                'unidades_totales'  => (int) $h->cantidad,
                'unidades_disponibles' => $disponibles,
                'descripcion'       => $h->descripcion,
            ];
        }

        // 6) Respuesta usando el HotelResource con información de disponibilidad
        $filtros = [
            'adultos' => $adultos,
            'ninos' => $ninos,
            'habitaciones' => $reqHabitaciones,
        ];

        return (new HotelResource(
            $hotel,
            $checkIn->toDateString(),
            $checkOut->toDateString(),
            $filtros,
            array_values($resultado)
        ))->response();
    }
}
