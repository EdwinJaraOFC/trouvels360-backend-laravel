<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Usuario;
use App\Models\Servicio;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    // GET /api/reviews
    public function index(Request $request): JsonResponse
    {   
        $query = Review::with(['usuario:id,nombre,apellido'])
                        ->latest(); // ORDER BY created_at DESC

        if ($request->has('servicio_id')) {
            $query->where('servicio_id', $request->servicio_id);
        }

        if ($request->has('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->has('calificacion')) {
            $query->where('calificacion', $request->calificacion);
        }

        return response()->json($query->get());
    }
    // POST /api/reviews
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $review= Review::create($request->validated());

        return response()->json([
            'message'=>"Review creada correctamente",
            'data'=> $review
        ],201);
    }

    // PUT  /api/reviews/{review}
    public function update(UpdateReviewRequest $request, Review $review): JsonResponse
    {   
        $review->update($request->validated());
        
        return response()->json([
            'message' => 'Review actualizada correctamente',
            'data' => $review
        ]);
    }

    // DELETE /api/reviews/{review}
    public function destroy(Review $review): JsonResponse
    {   
        $review->delete();
        return response()->json(null, 204);
    }
}
