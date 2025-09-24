<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHotelRequest;
use App\Http\Requests\UpdateHotelRequest;
use App\Models\Hotel;
use App\Models\Servicio;
use App\Models\Habitacion;
use App\Models\ReservaHabitacion;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HotelController extends Controller
{
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
            'hotel'       => $hotel->only('servicio_id','direccion','estrellas','created_at','updated_at'),
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

    // GET /api/hoteles/{servicio_id}/disponibilidad?check_in=YYYY-MM-DD&check_out=YYYY-MM-DD
    public function disponibilidad(Request $request, int $servicio_id): JsonResponse
    {
        $request->validate([
            'check_in'  => ['required','date_format:Y-m-d'],
            'check_out' => ['required','date_format:Y-m-d','after:check_in'],
        ]);

        $hotel = Hotel::with('habitaciones')->find($servicio_id);
        if (!$hotel) return response()->json(['message' => 'Hotel no encontrado'], 404);

        $in  = Carbon::parse($request->query('check_in'))->toDateString();
        $out = Carbon::parse($request->query('check_out'))->toDateString();

        $result = $hotel->habitaciones->map(function (Habitacion $h) use ($in, $out) {
            // Unidades ocupadas en el rango
            $ocupadas = $h->reservas()
                ->whereIn('estado', ['pendiente', 'confirmada'])
                ->whereDate('fecha_inicio', '<',  $out)
                ->whereDate('fecha_fin',    '>',  $in)
                ->sum('cantidad');

            $disponibles = max(0, $h->cantidad - $ocupadas);

            return [
                'id'                 => $h->id,
                'nombre'             => $h->nombre,
                'capacidad_adultos'  => $h->capacidad_adultos,
                'capacidad_ninos'    => $h->capacidad_ninos,
                'precio_por_noche'   => (float) $h->precio_por_noche,
                'unidades_disponibles' => $disponibles,
            ];
        });

        return response()->json([
            'hotel'        => $hotel->only('servicio_id','direccion','estrellas'),
            'check_in'     => $in,
            'check_out'    => $out,
            'habitaciones' => $result,
        ], 200);
    }
}
