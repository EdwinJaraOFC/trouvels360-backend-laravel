<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTourActividadRequest;
use App\Http\Requests\UpdateTourActividadRequest;
use App\Models\Tour;
use App\Models\TourActividad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TourActividadController extends Controller
{
    // GET /api/tours/{tour}/actividades (público)
    public function index(Tour $tour): JsonResponse
    {
        $actividades = $tour->actividades()->orderBy('orden')->get();
        return response()->json($actividades, 200);
    }

    // POST /api/tours/{tour}/actividades (proveedor dueño)
    public function store(StoreTourActividadRequest $request, Tour $tour): JsonResponse
    {
        $user = $request->user();
        if ($user->rol !== 'proveedor' || $tour->servicio->proveedor_id !== $user->id) {
            return response()->json(['message'=>'No autorizado.'], 403);
        }

        $data = $request->validated();

        // Si no se envía 'orden', asignar el siguiente
        if (!isset($data['orden'])) {
            $max = $tour->actividades()->max('orden');
            $data['orden'] = (int) $max + 1;
        }

        $actividad = $tour->actividades()->create($data);

        return response()->json([
            'message' => 'Actividad creada correctamente.',
            'data'    => $actividad,
        ], 201);
    }

    // PUT/PATCH /api/tours/{tour}/actividades/{actividad}
    public function update(UpdateTourActividadRequest $request, Tour $tour, TourActividad $actividad): JsonResponse
    {
        $user = $request->user();
        if ($user->rol !== 'proveedor' || $tour->servicio->proveedor_id !== $user->id || $actividad->servicio_id !== $tour->servicio_id) {
            return response()->json(['message'=>'No autorizado.'], 403);
        }

        $data = $request->validated();

        // Si cambia orden, valida unicidad manual (por si el validador no lo cubre)
        if (isset($data['orden']) && $data['orden'] !== $actividad->orden) {
            $exists = $tour->actividades()->where('orden', $data['orden'])->exists();
            if ($exists) {
                return response()->json(['message'=>'El orden ya existe para este tour.'], 422);
            }
        }

        $actividad->update($data);
        $actividad->refresh();

        return response()->json([
            'message' => 'Actividad actualizada correctamente.',
            'data'    => $actividad,
        ], 200);
    }

    // DELETE /api/tours/{tour}/actividades/{actividad}
    public function destroy(Request $request, Tour $tour, TourActividad $actividad): JsonResponse
    {
        $user = $request->user();
        if ($user->rol !== 'proveedor' || $tour->servicio->proveedor_id !== $user->id || $actividad->servicio_id !== $tour->servicio_id) {
            return response()->json(['message'=>'No autorizado.'], 403);
        }

        $actividad->delete();
        return response()->json(null, 204);
    }
}
