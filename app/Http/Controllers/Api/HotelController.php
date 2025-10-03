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
    public function index(): JsonResponse
    {
        $hoteles = Hotel::with([
            'servicio:id,nombre,tipo,descripcion,ciudad,pais,imagen_url,activo',
            'habitaciones:id,servicio_id,precio_por_noche'
        ])->get();

        return HotelResource::collection($hoteles)->response();
    }

    // POST /api/hoteles  (crea Servicio(tipo=hotel) + Hotel en una transacción)
    public function store(StoreHotelRequest $request): JsonResponse
    {
        $data = $request->validated();

        $hotel = DB::transaction(function () use ($data) {
            $servicio = Servicio::create([
                'proveedor_id' => $data['servicio']['proveedor_id'],
                'nombre'       => $data['servicio']['nombre'],
                'tipo'         => 'hotel', // se fuerza aquí
                'descripcion'  => $data['servicio']['descripcion'] ?? null,
                'ciudad'       => $data['servicio']['ciudad'],
                'pais'         => $data['servicio']['pais'],
                'imagen_url'   => $data['servicio']['imagen_url'] ?? null,
                'activo'       => $data['servicio']['activo'] ?? true,
            ]);

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
    // Usa UpdateHotelRequest para que corra authorize()
    public function destroy(UpdateHotelRequest $request, int $servicio_id): JsonResponse
    {
        $hotel = Hotel::find($servicio_id);
        if (!$hotel) return response()->json(['message' => 'Hotel no encontrado'], 404);

        $hotel->delete();
        return response()->json(null, 204);
    }

    // GET /api/hoteles/{servicio_id}/disponibilidad
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
