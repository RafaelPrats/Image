<?php

namespace yura\Http\Controllers\Campo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\AplicacionCampo;
use yura\Modelos\AplicacionMatriz;
use yura\Modelos\Submenu;

class ReporteLaboresController extends Controller
{
    public function inicio(Request $request)
    {
        $fechas = DB::table('aplicacion_campo')
            ->select(DB::raw('min(fecha) as min_fecha'), DB::raw('max(fecha) as max_fecha'))
            ->get()[0];
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('fecha_inicial', '<=', $fechas->max_fecha)
            ->where('fecha_final', '>=', $fechas->min_fecha)
            ->orderBy('codigo', 'desc')
            ->get();
        $semana_actual = getSemanaByDate(hoy());
        return view('adminlte.gestion.campo.reporte_labores.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semanas' => $semanas,
            'semana_actual' => $semana_actual,
        ]);
    }

    public function seleccionar_tipo_labor(Request $request)
    {
        $labores = AplicacionMatriz::where('tipo', $request->tipo)->orderBy('nombre')->get();
        return [
            'labores' => $labores
        ];
    }

    /* ----------------------- LISTAR -------------------------- */
    public function listar_reporte(Request $request)
    {
        $app_matriz = AplicacionMatriz::find($request->labor);
        if ($app_matriz->nombre == 'ACIDO GIBERELICO')
            return view('adminlte.gestion.campo.reporte_labores.partials.listado_giberelico',
                self::listar_acido_giberelico($request, $app_matriz));
        if ($app_matriz->nombre == 'DESBROTE')
            return view('adminlte.gestion.campo.reporte_labores.partials.listado_desbrote',
                self::listar_desbrote($request, $app_matriz));
    }

    static function listar_acido_giberelico($request, $app_matriz)
    {
        $semana_req = getObjSemana($request->semana);
        $ids_app = [];
        foreach ($app_matriz->aplicaciones as $app)
            array_push($ids_app, $app->id_aplicacion);
        $listado = AplicacionCampo::whereIn('id_aplicacion', $ids_app)
            ->where('fecha', '>=', $semana_req->fecha_inicial)
            ->where('fecha', '<=', $semana_req->fecha_final)
            ->orderBy('fecha')
            ->get();

        $productos = [];
        $mano_obras = [];
        foreach ($listado as $labor)
            foreach ($labor->detalles as $det) {
                if ($det->id_producto != '' && !in_array($det->producto, $productos))
                    array_push($productos, $det->producto);
                if ($det->id_mano_obra != '' && !in_array($det->mano_obra, $mano_obras))
                    array_push($mano_obras, $det->mano_obra);
            }
        return [
            'listado' => $listado,
            'semana_req' => $semana_req,
            'app_matriz' => $app_matriz,
            'productos' => $productos,
            'mano_obras' => $mano_obras,
        ];
    }

    static function listar_desbrote($request, $app_matriz)
    {
        $semana_req = getObjSemana($request->semana);
        $mezclas = $app_matriz->mezclas;
        $mano_obras = [];
        foreach ($mezclas as $mezcla)
            foreach ($mezcla->detalles as $det) {
                if ($det->id_mano_obra != '' && !in_array($det->mano_obra, $mano_obras))
                    array_push($mano_obras, $det->mano_obra);
            }
        $ids_app = [];
        foreach ($app_matriz->aplicaciones as $app)
            array_push($ids_app, $app->id_aplicacion);
        $listado = AplicacionCampo::whereIn('id_aplicacion', $ids_app)
            ->where('fecha', '>=', $semana_req->fecha_inicial)
            ->where('fecha', '<=', $semana_req->fecha_final)
            ->orderBy('fecha')
            ->get();
        return [
            'listado' => $listado,
            'semana_req' => $semana_req,
            'app_matriz' => $app_matriz,
            'mano_obras' => $mano_obras,
        ];
    }
}
