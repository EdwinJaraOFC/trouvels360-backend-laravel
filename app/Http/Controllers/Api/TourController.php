<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTourRequest;
use App\Http\Requests\UpdateTourRequest;
use Illuminate\Http\Request;
use App\Models\Tour;
use App\Models\Servicio;
use Illuminate\Http\JsonResponse;

class TourController extends Controller
{
    // Mostrar la lista de Tours
    public function index(): JsonResponse
    {   
        $tours = Tour::with([
            'servicio'=> function($query){
                $query->select('id','proveedor_id','nombre','descripcion','ciudad');
            }
        ])->get();
        return response()->json($tours);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTourRequest $request): JsonResponse
    {
        $validated = $request->validated();
        //crear servicio
        $servicio = Servicio::create([  
            'proveedor_id' => $validated['proveedor_id'],
            'nombre' => $validated['nombre'],
            'tipo' =>'tour',
            'ciudad' => $validated['ciudad'],
        ]);

        $tour = $servicio->tour()->create([
            'precio_adulto' => $validated['precio_adulto'],
            'precio_child' => $validated['precio_child'],
        ]);
        $tour-> load('servicio');

        return response()->json($tour,201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {   
        $tour = Tour::find($id);
        if(!$tour){
            return response()->json(['message'=> 'Tour no encontrado'],404);
        }
        // Si el tour existe
        $tour ->load([
            'servicio'=> function($query){
                $query->select('id','proveedor_id','nombre','descripcion','ciudad');
            }
        ])->get(); 
        return response()->json($tour);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTourRequest $request, string $id): JsonResponse
    {   
        $tour = Tour::find($id);
        if(!$tour){
            return response()->json(['message'=> 'Tour no encontrado'],404);
        }
        $validated = $request->validated();

        //Actualizar el Servicio asociado al Tour con id=$id
        if($tour-> servicio){
            $tour ->servicio-> update(
                array_intersect_key($validated, array_flip([
                        'proveedor_id','nombre','descripcion', 'ciudad', 'horario_inicio', 'horario_fin','imagen_url'
                ]))
            );
        }

        // Actualizar los campos de Tour
        $tour-> update(
            array_intersect_key($validated, array_flip([
                'categoria','duracion','precio_adulto', 'precio_child'
            ]))
        );
        return response()->json($tour,200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $tour = Tour::find($id);
        if (!$tour){
            return response()->json(['message'=> 'Tour no encontrado'],404);
        }
        // Si el Tour con el id existe eliminar 
        $tour->delete();

        return response()->json(['message'=> 'Tour eliminado exitosamente'],200);
    }
}
