<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTourRequest;
use App\Http\Requests\UpdateTourRequest;
use App\Models\Servicio;
use App\Models\ServicioImagen;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TourController extends Controller
{
    /** GET /api/tours?q=&ciudad=&pais=&categoria=&proveedor_id=&activo=&fecha=&cupos=&sort=&page=&per_page */
    public function index(Request $req)
    {
        $q = Servicio::query()
            ->where('tipo', 'tour')
            ->with('tour')
            ->select(['id','proveedor_id','nombre','tipo','descripcion','ciudad','pais','imagen_url','activo','created_at']);

        // Filtros básicos
        if ($term = $req->query('q')) {
            $q->where(function($qq) use ($term) {
                $qq->where('nombre','like',"%$term%")
                   ->orWhere('descripcion','like',"%$term%")
                   ->orWhere('ciudad','like',"%$term%")
                   ->orWhere('pais','like',"%$term%");
            });
        }
        if ($req->filled('ciudad'))       $q->where('ciudad', $req->query('ciudad'));
        if ($req->filled('pais'))         $q->where('pais', $req->query('pais'));
        if ($req->filled('proveedor_id')) $q->where('proveedor_id', $req->query('proveedor_id'));
        if ($req->has('activo'))          $q->where('activo', filter_var($req->query('activo'), FILTER_VALIDATE_BOOLEAN));

        // Filtro por categoría (tabla tours)
        if ($cat = $req->query('categoria')) {
            $q->whereHas('tour', fn($t)=> $t->where('categoria',$cat));
        }

        // Filtro por rango de fechas y cupos (usa salidas)
        if ($req->filled('checkIn')) {
            $fechaInicio = $req->query('checkIn');
            $fechaFin    = $req->query('checkOut', $fechaInicio);
            $cupos       = (int) $req->query('cupos', 1);

            $q->whereHas('salidas', function($s) use ($fechaInicio, $fechaFin, $cupos) {
                $s->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->where('estado', 'programada')
                ->whereRaw('cupo_total - cupo_reservado >= ?', [$cupos]);
            });
        }

        // Orden
        if ($sort = $req->query('sort')) {
            foreach (explode(',', $sort) as $s) {
                $dir = str_starts_with($s, '-') ? 'desc' : 'asc';
                $col = ltrim($s, '-');
                if (in_array($col, ['nombre','ciudad','pais','created_at'])) $q->orderBy($col, $dir);
                if ($col === 'precio') {
                    $q->join('tours','tours.servicio_id','=','servicios.id')
                      ->orderBy('tours.precio', $dir)
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
    public function show(Request $req, $tour)
    {
        $serv = Servicio::with([
            'tour',
            'imagenes:id,servicio_id,url,alt',  // 👈 incluir galería simple
            'actividades',
            'salidas' => fn($q) => $q->where('estado','programada')->orderBy('fecha')->orderBy('hora'),
            'reviews' => function ($q) {
                $q->with('usuario:id,nombre,apellido')
                  ->latest()
                  ->limit(10); // Últimas 10 reviews
            }
        ])->where('tipo','tour')->findOrFail($tour);

        // Filtrar por cupos si se envía
        if ($req->filled('cupos')) {
            $cupos = (int) $req->query('cupos', 1);
            $serv->salidas = $serv->salidas->filter(fn($s) => ($s->cupo_total - $s->cupo_reservado) >= $cupos);
        }

        // Calcular estadísticas de reviews
        $promedioCalificacion = $serv->promedio_calificacion;
        $cantidadReviews = $serv->cantidad_reviews;

        // Formatear reviews
        $reviewsFormateadas = $serv->reviews->map(fn($r) => [
            'id' => $r->id,
            'usuario' => [
                'id' => $r->usuario_id,
                'nombre' => $r->usuario?->nombre,
                'apellido' => $r->usuario?->apellido,
                'nombre_completo' => $r->usuario 
                    ? trim(($r->usuario->nombre ?? '') . ' ' . ($r->usuario->apellido ?? '')) ?: 'Usuario Anónimo'
                    : 'Usuario Anónimo',
            ],
            'comentario' => $r->comentario,
            'calificacion' => (int) $r->calificacion,
            'created_at' => $r->created_at?->toISOString(),
            'fecha_formateada' => $r->created_at?->locale('es')->diffForHumans(),
        ]);

        // Construir respuesta
        $data = $serv->toArray();
        $data['calificacion'] = [
            'promedio' => $promedioCalificacion,
            'cantidad' => $cantidadReviews,
        ];
        $data['reviews'] = $reviewsFormateadas;

        return response()->json(['servicio' => $data]);
    }

    /** POST /api/tours  (crea servicio + tour) */
    public function store(StoreTourRequest $req)
    {
        $user = Auth::user(); // la policy ya valida rol proveedor en el request

        $data = $req->validated();

        $serv = DB::transaction(function() use ($data, $user) {
            // 1) Servicio (portada en imagen_url)
            $serv = Servicio::create([
                'proveedor_id' => $user->id,
                'nombre'       => $data['nombre'],
                'tipo'         => 'tour',
                'descripcion'  => $data['descripcion'] ?? null,
                'ciudad'       => $data['ciudad'],
                'pais'         => $data['pais'],
                'imagen_url'   => $data['imagen_url'] ?? null,
                'activo'       => $data['activo'] ?? true,
            ]);

            // 2) Tour (detalle)
            Tour::create([
                'servicio_id'        => $serv->id,
                'categoria'          => $data['categoria'] ?? null,
                'duracion'           => $data['duracion'] ?? null,
                'precio'             => $data['precio'],
                'cupos'              => $data['cupos'] ?? null,
                'cosas_para_llevar'  => $data['cosas_para_llevar'] ?? null,
            ]);

            // 3) Galería simple (opcional, máx 5)
            if (!empty($data['imagenes'])) {
                $imgs = collect($data['imagenes'])->take(5)->map(function ($item) {
                    if (is_string($item)) return ['url' => $item, 'alt' => null];
                    return ['url' => $item['url'] ?? null, 'alt' => $item['alt'] ?? null];
                })->filter(fn($x) => !empty($x['url']))->values()->all();

                if (!empty($imgs)) {
                    $serv->imagenes()->createMany($imgs);
                }
            }

            return $serv->load('tour');
        });

        return response()->json([
            'message' => 'Tour creado correctamente',
            'data'    => $serv,
        ], 201)->header('Location', url("/api/tours/{$serv->id}"));
    }

    /** PUT /api/tours/{tour}  (actualiza servicio + tour) */
    public function update(UpdateTourRequest $req, $tour)
    {
        $serv = Servicio::where('tipo','tour')->findOrFail($tour);
        $this->assertOwnership($serv);

        $data = $req->validated();

        DB::transaction(function() use ($serv, $data) {
            // 1) Servicio
            $serv->fill(array_intersect_key($data, array_flip([
                'nombre','descripcion','ciudad','pais','imagen_url','activo'
            ])))->save();

            // 2) Tour
            if ($serv->tour) {
                $serv->tour->fill(array_intersect_key($data, array_flip([
                    'categoria','duracion','precio','cupos','cosas_para_llevar'
                ])))->save();
            }

            // 3) Si viene 'imagenes', reemplazar galería (simple)
            if (array_key_exists('imagenes', $data)) {
                $serv->imagenes()->delete();

                $imgs = collect($data['imagenes'] ?? [])->take(5)->map(function ($item) {
                    if (is_string($item)) return ['url' => $item, 'alt' => null];
                    return ['url' => $item['url'] ?? null, 'alt' => $item['alt'] ?? null];
                })->filter(fn($x) => !empty($x['url']))->values()->all();

                if (!empty($imgs)) {
                    $serv->imagenes()->createMany($imgs);
                }
            }
        });

        return response()->json([
            'message' => 'Tour actualizado correctamente',
            'data'    => $serv->load(['tour','imagenes:id,servicio_id,url,alt']),
        ]);
    }

    /** DELETE /api/tours/{tour} */
    public function destroy($tour)
    {
        $serv = Servicio::where('tipo','tour')->findOrFail($tour);
        $this->assertOwnership($serv);

        $serv->delete();

        return response()->json(['message' => 'Tour eliminado']);
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
