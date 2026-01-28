<?php

namespace yura\Http\Controllers\Postcosecha;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Clasificador;
use yura\Modelos\DistribucionPosco;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;

class DistribucionPoscoController extends Controller
{
    public function inicio(Request $request)
    {
        $clasificadores = Clasificador::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postcocecha.distribucion_posco.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => Planta::where('estado', '=', 1)->orderBy('nombre')->get(),
            'clasificadores' => $clasificadores
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $combinaciones = DB::table('distribucion_posco as d')
            ->join('variedad as v', 'v.id_variedad', '=', 'd.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('empaque as e', 'e.id_empaque', '=', 'd.id_empaque')
            ->select(
                'v.id_planta',
                'p.nombre as pta_nombre',
                'd.id_variedad',
                'v.nombre as var_nombre',
                'd.id_empaque',
                'e.nombre as emp_nombre',
                'd.tallos_x_ramo',
                'd.longitud',
                DB::raw('sum(cantidad) as cantidad')
            )->distinct()
            ->where('fecha', $request->fecha);
        if ($request->planta != '')
            $combinaciones = $combinaciones->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $combinaciones = $combinaciones->where('d.id_variedad', $request->variedad);
        if ($request->clasificador != '')
            $combinaciones = $combinaciones->where('d.id_clasificador', $request->clasificador);
        $combinaciones = $combinaciones->groupBy(
            'v.id_planta',
            'p.nombre',
            'd.id_variedad',
            'v.nombre',
            'd.id_empaque',
            'e.nombre',
            'd.tallos_x_ramo',
            'd.longitud'
        )
            ->orderBy('p.orden')
            ->orderBy('v.orden')
            ->get();
        $listado = [];
        foreach ($combinaciones as $item) {
            $valores = DistribucionPosco::where('id_variedad', $item->id_variedad)
                ->where('id_empaque', $item->id_empaque)
                ->where('tallos_x_ramo', $item->tallos_x_ramo)
                ->where('longitud', $item->longitud);
            if ($request->clasificador != '')
                $valores = $valores->where('id_clasificador', $request->clasificador);
            $valores = $valores->orderBy('cantidad')
                ->get();
            $listado[] = [
                'item' => $item,
                'valores' => $valores,
            ];
        }
        return view('adminlte.gestion.postcocecha.distribucion_posco.partials.listado', [
            'listado' => $listado
        ]);
    }
}
