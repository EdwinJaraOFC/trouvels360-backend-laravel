<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TourController extends Controller
{
    /** GET /api/tours?q=&ciudad=&categoria=&proveedor_id=&activo=&fecha=&sort=&page&per_page */
    public function index(Request $req)
    {
        $q = Servicio::query()
            ->where('tipo', 'tour')
            ->with('tour')
            ->select(['id','proveedor_id','nombre','tipo','descripcion','ciudad','imagen_url','activo','created_at']);

        // Filtros básicos
        if ($term = $req->query('q')) {
            $q->where(function($qq) use ($term) {
                $qq->where('nombre','like',"%$term%")
                   ->orWhere('descripcion','like',"%$term%")
                   ->orWhere('ciudad','like',"%$term%");
            });
        }
        if ($req->filled('ciudad'))       $q->where('ciudad', $req->query('ciudad'));
        if ($req->filled('proveedor_id')) $q->where('proveedor_id', $req->query('proveedor_id'));
        if ($req->has('activo'))          $q->where('activo', filter_var($req->query('activo'), FILTER_VALIDATE_BOOLEAN));

        // Filtro por categoría (columna en tabla tours)
        if ($cat = $req->query('categoria')) {
            $q->whereHas('tour', fn($t)=> $t->where('categoria',$cat));
        }

        // Filtro por fecha con disponibilidad (>0)
        if ($fecha = $req->query('fecha')) {
            $q->whereHas('salidas', function($s) use ($fecha) {
                $s->whereDate('fecha',$fecha)
                  ->where('estado','programada')
                  ->whereColumn('cupo_reservado','<','cupo_total');
            });
        }

        // Orden
        if ($sort = $req->query('sort')) {
            foreach (explode(',', $sort) as $s) {
                $dir = str_starts_with($s, '-') ? 'desc' : 'asc';
                $col = ltrim($s, '-');
                if (in_array($col, ['nombre','ciudad','created_at'])) $q->orderBy($col, $dir);
                if ($col === 'precio') { // ordenar por tours.precio_persona
                    $q->join('tours','tours.servicio_id','=','servicios.id')
                      ->orderBy('tours.precio_persona', $dir)
                      ->select('servicios.*');
                }
            }
        } else {
            $q->latest('created_at');
        }

        $perPage = min((int)$req->query('per_page', 15), 100);
        return $q->paginate($perPage)->appends($req->query());
    }

    /** GET /api/tours/{tour}  ({tour}=servicio_id) */
    public function show($tour)
    {
        $serv = Servicio::with([
            'tour',
            'actividades',
            'salidas' => fn($q)=> $q->where('estado','programada')->orderBy('fecha')->orderBy('hora')
        ])->where('tipo','tour')->findOrFail($tour);

        return $serv;
    }

    /** POST /api/tours  (crea servicio + tour) - requiere proveedor autenticado */
    public function store(Request $req)
    {
        $user = Auth::user();
        if (!$user || $user->rol !== 'proveedor') {
            abort(403, 'Solo proveedores pueden crear tours.');
        }

        $data = $req->validate([
            'nombre'       => ['required','string','max:150'],
            'descripcion'  => ['nullable','string'],
            'ciudad'       => ['required','string','max:100'],
            'imagen_url'   => ['nullable','url'],
            'activo'       => ['boolean'],

            'categoria'              => ['nullable','string','max:100'],
            'duracion_min'           => ['nullable','integer','min:0'],
            'precio_persona'         => ['required','numeric','min:0'],
            'capacidad_por_salida'   => ['nullable','integer','min:1'],
        ]);

        $serv = DB::transaction(function() use ($data, $user) {
            $serv = Servicio::create([
                'proveedor_id' => $user->id,
                'nombre'       => $data['nombre'],
                'tipo'         => 'tour',
                'descripcion'  => $data['descripcion'] ?? null,
                'ciudad'       => $data['ciudad'],
                'imagen_url'   => $data['imagen_url'] ?? null,
                'activo'       => $data['activo'] ?? true,
            ]);

            Tour::create([
                'servicio_id'          => $serv->id,
                'categoria'            => $data['categoria'] ?? null,
                'duracion_min'         => $data['duracion_min'] ?? null,
                'precio_persona'       => $data['precio_persona'],
                'capacidad_por_salida' => $data['capacidad_por_salida'] ?? null,
            ]);

            return $serv->load('tour');
        });

        return response()
            ->json([
                'message' => 'Tour creado correctamente',
                'data'    => $serv,
            ], 201)
            ->header('Location', url("/api/tours/{$serv->id}"));
    }

    /** PUT/PATCH /api/tours/{tour}  (actualiza servicio + tour) */
    public function update(Request $req, $tour)
    {
        $serv = Servicio::where('tipo','tour')->findOrFail($tour);
        $this->assertOwnership($serv);

        $data = $req->validate([
            'nombre'       => ['sometimes','string','max:150'],
            'descripcion'  => ['sometimes','nullable','string'],
            'ciudad'       => ['sometimes','string','max:100'],
            'imagen_url'   => ['sometimes','nullable','url'],
            'activo'       => ['sometimes','boolean'],

            'categoria'              => ['sometimes','nullable','string','max:100'],
            'duracion_min'           => ['sometimes','nullable','integer','min:0'],
            'precio_persona'         => ['sometimes','numeric','min:0'],
            'capacidad_por_salida'   => ['sometimes','nullable','integer','min:1'],
        ]);

        DB::transaction(function() use ($serv, $data) {
            $serv->fill(array_intersect_key($data, array_flip([
                'nombre','descripcion','ciudad','imagen_url','activo'
            ])))->save();

            if ($serv->tour) {
                $serv->tour->fill(array_intersect_key($data, array_flip([
                    'categoria','duracion_min','precio_persona','capacidad_por_salida'
                ])))->save();
            }
        });

        $serv->load('tour');

        return response()->json([
            'message' => 'Tour actualizado correctamente',
            'data'    => $serv,
        ]);
    }

    /** DELETE /api/tours/{tour}  (cascade borra tour/salidas/actividades) */
    public function destroy($tour)
    {
        $serv = Servicio::where('tipo','tour')->findOrFail($tour);
        $this->assertOwnership($serv);

        $serv->delete();

        return response()->json([
            'message' => 'Tour eliminado'
        ]);
    }

    /** --- Helpers --- */
    private function assertOwnership(Servicio $serv): void
    {
        $user = Auth::user();
        if (!$user || $user->rol !== 'proveedor' || (int)$user->id !== (int)$serv->proveedor_id) {
            abort(403, 'No autorizado: no eres el proveedor dueño de este tour.');
        }
    }
}
