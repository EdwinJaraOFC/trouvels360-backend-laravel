<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTourSalidaRequest;
use App\Http\Requests\UpdateTourSalidaRequest;
use App\Models\Tour;
use App\Models\TourSalida;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TourSalidaController extends Controller
{
    // GET /api/tours/{tour}/salidas (público)
    public function index(Tour $tour): JsonResponse
    {
        $salidas = $tour->salidas()->orderBy('fecha')->orderBy('hora')->get(['id','servicio_id','fecha','hora','cupo_total','cupo_reservado','estado']);
        return response()->json($salidas, 200);
    }

    // POST /api/tours/{tour}/salidas (proveedor dueño)
    public function store(StoreTourSalidaRequest $request, Tour $tour): JsonResponse
    {
        $user = $request->user();
        if ($user->rol !== 'proveedor' || $tour->servicio->proveedor_id !== $user->id) {
            return response()->json(['message'=>'No autorizado.'], 403);
        }

        // Validar cupos vs Tour.capacidad (opcional, como línea base)
        if ($request->integer('cupo_total') > (int)$tour->capacidad) {
            return response()->json(['message' => 'cupo_total no puede exceder la capacidad del tour.'], 422);
        }

        $salida = $tour->salidas()->create([
            'fecha'      => $request->date('fecha'),
            'hora'       => $request->input('hora'), // nullable
            'cupo_total' => $request->integer('cupo_total'),
            'estado'     => $request->input('estado', 'abierta'),
        ]);

        return response()->json([
            'message' => 'Salida creada correctamente.',
            'data'    => $salida->only('id','servicio_id','fecha','hora','cupo_total','cupo_reservado','estado'),
        ], 201);
    }

    // PUT/PATCH /api/tours/{tour}/salidas/{salida}
    public function update(UpdateTourSalidaRequest $request, Tour $tour, TourSalida $salida): JsonResponse
    {
        $user = $request->user();
        if ($user->rol !== 'proveedor' || $tour->servicio->proveedor_id !== $user->id || $salida->servicio_id !== $tour->servicio_id) {
            return response()->json(['message'=>'No autorizado.'], 403);
        }

        // Evitar poner cupo_total < cupo_reservado
        if ($request->filled('cupo_total') && $request->integer('cupo_total') < (int)$salida->cupo_reservado) {
            return response()->json(['message' => 'cupo_total no puede ser menor al cupo ya reservado.'], 422);
        }

        $salida->update($request->validated());
        $salida->refresh();

        return response()->json([
            'message' => 'Salida actualizada correctamente.',
            'data'    => $salida->only('id','servicio_id','fecha','hora','cupo_total','cupo_reservado','estado'),
        ], 200);
    }

    // DELETE /api/tours/{tour}/salidas/{salida}
    public function destroy(Request $request, Tour $tour, TourSalida $salida): JsonResponse
    {
        $user = $request->user();
        if ($user->rol !== 'proveedor' || $tour->servicio->proveedor_id !== $user->id || $salida->servicio_id !== $tour->servicio_id) {
            return response()->json(['message'=>'No autorizado.'], 403);
        }

        // (opcional) bloquear si hay reservas confirmadas
        if ($salida->reservas()->where('estado','confirmada')->exists()) {
            return response()->json(['message'=>'No se puede eliminar: existen reservas confirmadas.'], 422);
        }

        $salida->delete();
        return response()->json(null, 204);
    }
}
