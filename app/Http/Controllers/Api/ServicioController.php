<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServicioRequest;
use App\Http\Requests\UpdateServicioRequest;
use Illuminate\Http\Request;
use App\Models\Servicio;  //si se va a trabajar con un modelo
use Illuminate\Http\JsonResponse;

class ServicioController extends Controller
{
    // Listar todos los servicios
    public function index(): JsonResponse
    {
        $servicios = Servicio::all();
        return response()->json($servicios);
    }

    // Show the form for creating a new resource.
    public function create()
    {
        //
    }

    // Crear un nuevo servicio
    public function store(StoreServicioRequest $request): JsonResponse
    {   
        // Crear el servicio usando Eloquent
        $servicio = Servicio::create($request->validated());

        return response()->json([
            'message'=>'Servicio creado exitosamente',  
            'data' => $servicio->only('id','proveedor_id','nombre','tipo','ciudad'),
        ],201);
    }
    // Mostrar el servicio con id=$id
    public function show(string $id): JsonResponse
    {   
        $servicio = Servicio::find($id);
        // Si no encuentra el servicio
        if(!$servicio){
            return response()->json(['message'=> 'Servicio no encontrado'],404);
        }
        return response()->json($servicio->only('id','proveedor_id','nombre','tipo','ciudad'),200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    // Update el servicio con id=$id
    public function update(UpdateServicioRequest $request, string $id): JsonResponse
    {   
        $servicio = Servicio::find($id);
        if(!$servicio){
            return response()->json(['message'=> 'Servicio no encontrado'],404);
        }
        // Si el servicio existe modificar
        $servicio->update($request->validated());

        return response()->json([
            'message'=> 'Servicio modificado exitosamente',
            'servicio'=> $servicio,
        ],200);
    }

    //  Eliminar el registro con id= $id
    public function destroy(string $id): JsonResponse
    {   
        $servicio = Servicio::find($id);
        if(!$servicio){
            return response()->json(['message'=> 'Servicio no encontrado'],404);
        }
        // Si el servicio si existe eliminar
        $servicio->delete();

        return response()->json(['message'=> 'Servicio eliminado exitosamente'],200);
    }
}
