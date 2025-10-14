<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use App\Models\Tour;
use App\Models\TourSalida;
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

        // Filtro por fecha y cupos
        if ($fecha = $req->query('fecha')) {
            $cupos = (int) $req->query('cupos', 1); // cupos solicitados
            $q->whereHas('salidas', function($s) use ($fecha, $cupos) {
                $s->whereDate('fecha', $fecha)
                  ->where('estado','programada')
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
            'actividades',
            'salidas' => fn($q) => $q->where('estado','programada')->orderBy('fecha')->orderBy('hora')
        ])->where('tipo','tour')->findOrFail($tour);
        
        // Filtrar por cupos si se envía
        if ($req->filled('cupos')) {
            $cupos = (int) $req->query('cupos', 1);
            $serv->salidas = $serv->salidas->filter(fn($s) => ($s->cupo_total - $s->cupo_reservado) >= $cupos);
        }

        return response()->json([
            'servicio' => $serv,
        ]);
    }

    /** POST /api/tours  (crea servicio + tour) */
    public function store(Request $req)
    {
        $user = Auth::user();
        if (!$user || $user->rol !== 'proveedor') abort(403, 'Solo proveedores pueden crear tours.');

        $data = $req->validate([
            'nombre'             => ['required','string','max:150'],
            'descripcion'        => ['nullable','string'],
            'ciudad'             => ['required','string','max:100'],
            'pais'               => ['required','string','max:100'],
            'imagen_url'         => ['nullable','url'],
            'activo'             => ['boolean'],
            'categoria'          => ['nullable','string','max:100'],
            'duracion'           => ['nullable','integer','min:0'],
            'precio'             => ['required','numeric','min:0'],
            'cupos'              => ['nullable','integer','min:1'],
            'cosas_que_llevar'   => ['nullable','array'],
            'cosas_que_llevar.*' => ['string'],
            'galeria_imagenes'   => ['nullable','array'],
            'galeria_imagenes.*' => ['url'],
        ]);

        $serv = DB::transaction(function() use ($data, $user) {
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

            Tour::create([
                'servicio_id' => $serv->id,
                'categoria'   => $data['categoria'] ?? null,
                'duracion'    => $data['duracion'] ?? null,
                'precio'      => $data['precio'],
                'cupos'       => $data['cupos'] ?? null,
            ]);

            return $serv->load('tour');
        });

        return response()->json([
            'message' => 'Tour creado correctamente',
            'data'    => $serv,
        ], 201)->header('Location', url("/api/tours/{$serv->id}"));
    }

    /** PUT /api/tours/{tour}  (actualiza servicio + tour) */
    public function update(Request $req, $tour)
    {
        $serv = Servicio::where('tipo','tour')->findOrFail($tour);
        $this->assertOwnership($serv);

        $data = $req->validate([
            'nombre'       => ['sometimes','string','max:150'],
            'descripcion'  => ['sometimes','nullable','string'],
            'ciudad'       => ['sometimes','string','max:100'],
            'pais'         => ['sometimes','string','max:100'],
            'imagen_url'   => ['sometimes','nullable','url'],
            'activo'       => ['sometimes','boolean'],
            'categoria'    => ['sometimes','nullable','string','max:100'],
            'duracion'     => ['sometimes','nullable','integer','min:0'],
            'precio'       => ['sometimes','numeric','min:0'],
            'cupos'        => ['sometimes','nullable','integer','min:1'],
        ]);

        DB::transaction(function() use ($serv, $data) {
            $serv->fill(array_intersect_key($data, array_flip([
                'nombre','descripcion','ciudad','pais','imagen_url','activo'
            ])))->save();

            if ($serv->tour) {
                $serv->tour->fill(array_intersect_key($data, array_flip([
                    'categoria','duracion','precio','cupos'
                ])))->save();
            }
        });

        return response()->json([
            'message' => 'Tour actualizado correctamente',
            'data'    => $serv->load('tour'),
        ]);
    }

    /** DELETE /api/tours/{tour}  (borra tour y salidas) */
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
