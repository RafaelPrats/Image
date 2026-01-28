<?php

namespace yura\Http\Controllers\CRM;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;

class ResumenCosechaController extends Controller
{
    public function inicio(Request $request)
    {
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 7, hoy()));
        $semana_desde = substr($semana_hasta->codigo, 0, 2) . '01';

        return view('adminlte.crm.resumen_cosecha.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semana_desde' => $semana_desde,
            'semana_hasta' => $semana_hasta,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $labels = [];
        if ($request->rango == 'S') {
            $labels = getSemanasByCodigos($request->desde_semanal, $request->hasta_semanal);
        } else {
            $f = $request->desde_diario;
            while ($f <= $request->hasta_diario) {
                $labels[] = $f;
                $f = opDiasFecha('+', 1, $f);
            }
        }

        $plantas = Planta::join('variedad as v', 'v.id_planta', '=', 'planta.id_planta')
            ->select('planta.*')->distinct()
            ->where('planta.estado', 1)
            ->where('v.estado', 1)
            ->where('v.receta', 0)
            ->orderBy('planta.orden')
            ->get();

        $listado = [];
        foreach ($plantas as $p) {
            $valores = [];
            foreach ($labels as $l) {
                if ($request->rango == 'S') {
                    $proyectados = DB::table('proy_variedad_semana as p')
                        ->select(DB::raw('sum(p.cantidad) as cantidad'))
                        ->where('p.id_planta', $p->id_planta)
                        ->where('p.semana', $l->codigo);
                } else {
                    $proyectados = DB::table('proy_variedad_cortes as p')
                        ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                        ->select(DB::raw('sum(p.cantidad) as cantidad'))
                        ->where('v.id_planta', $p->id_planta)
                        ->where('p.fecha', $l);
                }
                $proyectados = $proyectados->get()[0]->cantidad;

                $vendidos = DB::table('historico_ventas as h')
                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                    ->select(DB::raw('sum(h.tallos) as cantidad'))
                    ->where('v.id_planta', $p->id_planta);
                if ($request->rango == 'S') {
                    $vendidos = $vendidos->where('h.fecha', '>=', $l->fecha_inicial)
                        ->where('h.fecha', '<=', $l->fecha_final);
                } else {
                    $vendidos = $vendidos->where('h.fecha', $l);
                }
                $vendidos = $vendidos->get()[0]->cantidad;

                $valores[] = [
                    'proyectados' => $proyectados,
                    'vendidos' => $vendidos,
                ];
            }
            $listado[] = [
                'planta' => $p,
                'valores' => $valores
            ];
        }
        return view('adminlte.crm.resumen_cosecha.partials.listado', [
            'labels' => $labels,
            'listado' => $listado,
            'rango' => $request->rango,
        ]);
    }
}
