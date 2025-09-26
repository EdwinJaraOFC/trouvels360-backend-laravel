<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservaTourRequest;
use App\Models\ReservaTour;
use App\Models\TourSalida;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon; // <-- importa Carbon


class ReservaTourController extends Controller
{
    // POST /api/tours/salidas/{salida}/reservas  (viajero)
    public function store(StoreReservaTourRequest $request, TourSalida $salida): JsonResponse
    {
        $user = $request->user();

        if ($user->rol !== 'viajero') {
            return response()->json(['message'=>'No autorizado: solo viajeros pueden reservar.'], 403);
        }

        if ($salida->estado !== 'abierta') {
            return response()->json(['message'=>'La salida no está abierta a reservas.'], 422);
        }

        $personas = (int) $request->input('personas');

        // verificar cupo
        $disponible = (int)$salida->cupo_total - (int)$salida->cupo_reservado;
        if ($personas > $disponible) {
            return response()->json(['message'=>"Cupo insuficiente. Disponible: {$disponible}"], 422);
        }

        // snapshot precio desde Tour
        $precioUnit = (float) $salida->tour->precio_persona;

        $reserva = ReservaTour::create([
            'codigo_reserva'  => strtoupper(Str::random(10)),
            'usuario_id'      => $user->id,
            'salida_id'       => $salida->id,
            'personas'        => $personas,
            'estado'          => 'pendiente',
            'precio_unitario' => number_format($precioUnit, 2, '.', ''),
            'total'           => number_format($precioUnit * $personas, 2, '.', ''),
        ]);

        // incrementar cupo_reservado
        $salida->cupo_reservado = min($salida->cupo_total, $salida->cupo_reservado + $personas);
        $salida->save();

        return response()->json([
            'message' => 'Reserva creada correctamente.',
            'data'    => $reserva->only('id','codigo_reserva','usuario_id','salida_id','personas','estado','precio_unitario','total','created_at'),
        ], 201);
    }

    // POST /api/tours/reservas/{reserva}/cancelar  (viajero dueño)
    public function cancelar(Request $request, ReservaTour $reserva): JsonResponse
    {
        $user = $request->user();

        if ($user->id !== $reserva->usuario_id) {
            return response()->json(['message'=>'No autorizado.'], 403);
        }

        if ($reserva->estado === 'cancelada') {
            return response()->json(['message'=>'La reserva ya está cancelada.'], 422);
        }

        // liberar cupos
        $salida = $reserva->salida;
        $salida->cupo_reservado = max(0, $salida->cupo_reservado - (int)$reserva->personas);
        $salida->save();

        $reserva->estado = 'cancelada';
        $reserva->save();

        return response()->json(['message'=>'Reserva cancelada.'], 200);
    }

    // GET /api/tours/mis-reservas  (viajero)
    public function misReservas(Request $request): JsonResponse
    {
        $user = $request->user();

        $res = ReservaTour::with(['salida.tour.servicio'])
            ->where('usuario_id', $user->id)
            ->orderByDesc('id')
            ->get()
            ->map(function (ReservaTour $r) {
                $fecha = $r->salida->fecha
                    ? Carbon::parse($r->salida->fecha)->toDateString()
                    : null;

                $hora = $r->salida->hora
                    ? Carbon::parse($r->salida->hora)->format('H:i')
                    : null;

                return [
                    'id'             => $r->id,
                    'codigo_reserva' => $r->codigo_reserva,
                    'estado'         => $r->estado,
                    'personas'       => (int) $r->personas,
                    'precio_unitario'=> (float) $r->precio_unitario,
                    'total'          => (float) $r->total,
                    'salida'         => [
                        'id'    => $r->salida->id,
                        'fecha' => $fecha,
                        'hora'  => $hora,
                        'tour'  => [
                            'servicio_id'    => $r->salida->tour->servicio_id,
                            'nombre'         => $r->salida->tour->servicio->nombre,
                            'ciudad'         => $r->salida->tour->servicio->ciudad,
                            'precio_persona' => (float) $r->salida->tour->precio_persona,
                        ],
                    ],
                    'created_at'     => $r->created_at,
                ];
            });

        return response()->json($res, 200);
    }
}
