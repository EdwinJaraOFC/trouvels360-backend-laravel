<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHotelRequest;
use App\Http\Requests\UpdateHotelRequest;
use App\Http\Resources\HotelResource;
use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\Servicio;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HotelController extends Controller
{
    // GET /api/hoteles
    public function index(): JsonResponse
    {
        $hoteles = Hotel::with(['servicio.proveedor:id,nombre,apellido'])->get();
        
        return HotelResource::collection($hoteles)->response();
    }

    // POST /api/hoteles
    public function store(StoreHotelRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $servicio = Servicio::create([
                'proveedor_id' => $request->validated()['proveedor_id'],
                'nombre' => $request->validated()['nombre'],
                'tipo' => 'hotel',
                'descripcion' => $request->validated()['descripcion'] ?? null,
                'ciudad' => $request->validated()['ciudad'],
                'precio' => $request->validated()['precio'],
                'horario_inicio' => $request->validated()['horario_inicio'] ?? null,
                'horario_fin' => $request->validated()['horario_fin'] ?? null,
                'imagen_url' => $request->validated()['imagen_url'] ?? null,
            ]);

            $hotel = Hotel::create([
                'servicio_id' => $servicio->id,
                'direccion' => $request->validated()['direccion'],
                'estrellas' => $request->validated()['estrellas'] ?? null,
                'precio_por_noche' => $request->validated()['precio_por_noche'],
            ]);

            DB::commit();

            return $this->respuestaHotelConRelaciones(
                $hotel, 
                'Hotel creado correctamente.', 
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear el hotel.',
            ], 500);
        }
    }

    // GET /api/hoteles/{hotel}
    public function show(Hotel $hotel): JsonResponse
    {
        return $this->respuestaHotelConRelaciones($hotel, 'Hotel encontrado.');
    }

    // PUT/PATCH /api/hoteles/{hotel}
    public function update(UpdateHotelRequest $request, Hotel $hotel): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            
            $servicioData = array_filter([
                'nombre' => $validated['nombre'] ?? null,
                'descripcion' => $validated['descripcion'] ?? null,
                'ciudad' => $validated['ciudad'] ?? null,
                'precio' => $validated['precio'] ?? null,
            ]);
            
            if (!empty($servicioData)) {
                $hotel->servicio->update($servicioData);
            }

            $hotelData = array_filter([
                'direccion' => $validated['direccion'] ?? null,
                'estrellas' => $validated['estrellas'] ?? null,
                'precio_por_noche' => $validated['precio_por_noche'] ?? null,
            ]);
            
            if (!empty($hotelData)) {
                $hotel->update($hotelData);
            }

            DB::commit();

            // Recargar el modelo para obtener los cambios actualizados
            $hotel->refresh();
            
            return $this->respuestaHotelConRelaciones($hotel, 'Hotel actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar el hotel.',
            ], 500);
        }
    }

    // DELETE /api/hoteles/{hotel}
    public function destroy(Hotel $hotel): JsonResponse
    {
        try {
            $hotel->servicio->delete();
            
            return response()->json([
                'message' => 'Hotel eliminado correctamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'No se pudo eliminar el hotel. Puede tener reservas activas.',
            ], 422);
        }
    }

    /**
     * Método helper para generar respuestas consistentes con HotelResource
     */
    private function respuestaHotelConRelaciones(Hotel $hotel, string $mensaje, int $codigoEstado = 200): JsonResponse
    {
        // Cargar las relaciones necesarias para la presentación completa
        $hotel->load(['servicio.proveedor:id,nombre,apellido']);
        
        return response()->json([
            'message' => $mensaje,
            'data' => new HotelResource($hotel),
        ], $codigoEstado);
    }
}