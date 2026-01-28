<?php

namespace yura\Http\Controllers\CRM;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Jobs\jobActualizarCosechaDiaria;
use yura\Modelos\CosechaDiaria;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;

class CosechaDiariaController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = DB::table('planta')
            ->select('id_planta', 'nombre')->distinct()
            ->where('estado', 1)
            ->orderBy('orden')
            ->get();
        return view('adminlte.crm.cosecha_diaria.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
            'desde' => opDiasFecha('-', 7, hoy()),
            'hasta' => opDiasFecha('-', 1, hoy()),
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $fechas = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
            ->select(DB::raw('DATE(r.fecha_ingreso) as fecha'))->distinct()
            ->where('r.fecha_ingreso', '>=', $request->desde)
            ->where('r.fecha_ingreso', '<=', $request->hasta);
        if ($request->planta != 'T')
            $fechas = $fechas->where('v.id_planta', $request->planta);
        $fechas = $fechas->orderBy('fecha')
            ->get()->pluck('fecha')->toArray();
        $plantas = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('v.id_planta', 'p.nombre')->distinct()
            ->where('r.fecha_ingreso', '>=', $request->desde)
            ->where('r.fecha_ingreso', '<=', $request->hasta);
        if ($request->planta != 'T')
            $plantas = $plantas->where('v.id_planta', $request->planta);
        $plantas = $plantas->orderBy('p.orden')
            ->get();

        $listado = [];
        $total_cosecha = 0;
        foreach ($plantas as $planta) {
            $longitudes = DB::table('desglose_recepcion as dr')
                ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                ->select('dr.longitud_ramo')->distinct()
                ->where('r.fecha_ingreso', '>=', $request->desde)
                ->where('r.fecha_ingreso', '<=', $request->hasta)
                ->where('v.id_planta', $planta->id_planta)
                ->orderBy('dr.longitud_ramo')
                ->get()->pluck('longitud_ramo')->toArray();
            $valores_longitudes = [];
            foreach ($longitudes as $long) {
                $variedades = DB::table('desglose_recepcion as dr')
                    ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                    ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                    ->select('dr.id_variedad', 'v.nombre')->distinct()
                    ->where('r.fecha_ingreso', '>=', $request->desde)
                    ->where('r.fecha_ingreso', '<=', $request->hasta)
                    ->where('v.id_planta', $planta->id_planta)
                    ->where('dr.longitud_ramo', $long)
                    ->orderBy('v.orden')
                    ->get();
                $valores_variedades = [];
                foreach ($variedades as $variedad) {
                    $valores_fecha = [];
                    foreach ($fechas as $fecha) {
                        if ($request->tipo == 'C')
                            $valor = DB::table('desglose_recepcion as dr')
                                ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                                ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
                                ->where('r.fecha_ingreso', $fecha)
                                ->where('dr.id_variedad', $variedad->id_variedad)
                                ->where('dr.longitud_ramo', $long)
                                ->get()[0]->cantidad;
                        if ($request->tipo == 'S')
                            $valor = DB::table('sobrante_recepcion as sr')
                                ->select(DB::raw('sum(cantidad) as cantidad'))
                                ->where('sr.fecha', opDiasFecha('-', 1, $fecha))
                                ->where('sr.id_variedad', $variedad->id_variedad)
                                ->where('sr.longitud', $long)
                                ->get()[0]->cantidad;
                        $valores_fecha[] = $valor;
                        $total_cosecha += $valor;
                    }
                    $valores_variedades[] = [
                        'variedad' => $variedad,
                        'valores_fecha' => $valores_fecha,
                    ];
                }
                $valores_longitudes[] = [
                    'longitud' => $long,
                    'valores_variedades' => $valores_variedades,
                ];
            }
            $listado[] = [
                'planta' => $planta,
                'valores_longitudes' => $valores_longitudes,
            ];
        }
        return view('adminlte.crm.cosecha_diaria.partials.listado', [
            'listado' => $listado,
            'fechas' => $fechas,
            'total_cosecha' => $total_cosecha,
        ]);
    }
}
