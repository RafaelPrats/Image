<?php

namespace yura\Http\Controllers\CRM;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\ClasificacionVerde;
use yura\Modelos\Cosecha;
use yura\Modelos\ResumenSemanaCosecha;
use yura\Modelos\Semana;
use yura\Modelos\Submenu;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Worksheet;
use PHPExcel_Worksheet_MemoryDrawing;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Style_Color;
use PHPExcel_Style_Alignment;
use yura\Modelos\Planta;
use yura\Modelos\Variedad;

class crmPostocechaController extends Controller
{
    public function inicio(Request $request)
    {
        /* ======= INDICADORES ======= */
        $semana_desde = getSemanaByDate(opDiasFecha('-', 28, hoy()));
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 7, hoy()));
        $semanas_indicadores = getSemanasByCodigos($semana_desde->codigo, $semana_hasta->codigo);
        $indicadores = [];
        foreach ($semanas_indicadores as $sem) {
            $cosecha = DB::table('desglose_recepcion as dr')
                ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                ->select(DB::raw('sum(dr.tallos_x_malla * dr.cantidad_mallas) as cantidad'))
                ->where('dr.estado', 1)
                ->where('r.fecha_ingreso', '>=', $sem->fecha_inicial)
                ->where('r.fecha_ingreso', '<=', $sem->fecha_final)
                ->get()[0]->cantidad;
            $postcosecha = DB::table('inventario_frio as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->select(DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad'))
                ->where('i.estado', 1)
                ->where('i.basura', 0)
                ->where('i.fecha_ingreso', '>=', $sem->fecha_inicial)
                ->where('i.fecha_ingreso', '<=', $sem->fecha_final)
                ->where('v.receta', 0)
                ->get()[0]->cantidad;
            $basura = DB::table('inventario_frio as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->select(DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad'))
                ->where('i.estado', 1)
                ->where('i.basura', 1)
                ->where('i.fecha_ingreso', '>=', $sem->fecha_inicial)
                ->where('i.fecha_ingreso', '<=', $sem->fecha_final)
                ->where('v.receta', 0)
                ->get()[0]->cantidad;
            $indicadores[] = [
                'semana' => $sem,
                'cosecha' => $cosecha,
                'postcosecha' => $postcosecha,
                'basura' => $basura,
            ];
        }

        /* ======= GRAFICAS ======= */
        $annos = DB::table('historico_ventas')
            ->select('anno')->distinct()
            ->orderBy('anno', 'desc')
            ->get();
        $plantas = Planta::where('estado', 1)
            ->orderBy('orden')
            ->get();
        return view('adminlte.crm.postcocecha.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'annos' => $annos,
            'indicadores' => $indicadores,
            'plantas' => $plantas,
            'clientes' => getClientes(),
        ]);
    }

    public function listar_graficas(Request $request)
    {
        if ($request->annos == '') {
            $view = 'graficas_rango';

            if ($request->rango == 'D') {   // diario
                $labels_cosecha = DB::table('desglose_recepcion as dr')
                    ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                    ->select('r.fecha_ingreso')->distinct()
                    ->where('dr.estado', 1)
                    ->where('r.fecha_ingreso', '>=', $request->desde)
                    ->where('r.fecha_ingreso', '<=', $request->hasta)
                    ->orderBy('r.fecha_ingreso')
                    ->get()->pluck('fecha_ingreso')->toArray();
                $labels_postcosecha = DB::table('inventario_frio')
                    ->select('fecha_ingreso')->distinct()
                    ->where('estado', 1)
                    ->where('basura', 0)
                    ->where('fecha_ingreso', '>=', $request->desde)
                    ->where('fecha_ingreso', '<=', $request->hasta)
                    ->orderBy('fecha_ingreso')
                    ->get()->pluck('fecha_ingreso')->toArray();
                $labels_desecho = DB::table('inventario_frio')
                    ->select('fecha_ingreso')->distinct()
                    ->where('estado', 1)
                    ->where('basura', 1)
                    ->where('fecha_ingreso', '>=', $request->desde)
                    ->where('fecha_ingreso', '<=', $request->hasta)
                    ->orderBy('fecha_ingreso')
                    ->get()->pluck('fecha_ingreso')->toArray();

                $data_cosecha = [];
                foreach ($labels_cosecha as $l) {
                    $query = DB::table('desglose_recepcion as dr')
                        ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                        ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                        ->select(
                            DB::raw('sum(dr.tallos_x_malla * dr.cantidad_mallas) as cantidad')
                        )
                        ->where('dr.estado', 1)
                        ->where('r.fecha_ingreso', $l);
                    if ($request->planta != 'T')
                        $query = $query->where('v.id_planta', $request->planta);
                    $query = $query->get()[0];

                    $data_cosecha[] = $query;
                }
                $data_postcosecha = [];
                foreach ($labels_postcosecha as $l) {
                    $query = DB::table('inventario_frio as i')
                        ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                        ->select(
                            DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad')
                        )
                        ->where('i.estado', 1)
                        ->where('i.basura', 0)
                        ->where('i.fecha_ingreso', $l)
                        ->where('v.receta', 0);
                    if ($request->planta != 'T')
                        $query = $query->where('v.id_planta', $request->planta);
                    $query = $query->get()[0];

                    $data_postcosecha[] = $query;
                }
                $data_desecho = [];
                foreach ($labels_desecho as $l) {
                    $query = DB::table('inventario_frio as i')
                        ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                        ->select(
                            DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad')
                        )
                        ->where('i.estado', 1)
                        ->where('i.basura', 1)
                        ->where('i.fecha_ingreso', $l)
                        ->where('v.receta', 0);
                    if ($request->planta != 'T')
                        $query = $query->where('v.id_planta', $request->planta);
                    $query = $query->get()[0];

                    $data_desecho[] = $query;
                }
            } else if ($request->rango == 'M') {   // mensual
                $labels_cosecha = DB::table('desglose_recepcion as dr')
                    ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                    ->select(DB::raw('DISTINCT DATE_FORMAT(r.fecha_ingreso, "%Y-%m") AS mes'))
                    ->where('r.fecha_ingreso', '>=', $request->desde)
                    ->where('r.fecha_ingreso', '<=', $request->hasta)
                    ->where('dr.estado', 1)
                    ->orderBy('r.fecha_ingreso')
                    ->groupBy('mes', 'r.fecha_ingreso')
                    ->get()
                    ->pluck('mes')
                    ->toArray();
                $labels_postcosecha = DB::table('inventario_frio')
                    ->select(DB::raw('DISTINCT DATE_FORMAT(fecha_ingreso, "%Y-%m") AS mes'))
                    ->where('fecha_ingreso', '>=', $request->desde)
                    ->where('fecha_ingreso', '<=', $request->hasta)
                    ->where('estado', 1)
                    ->where('basura', 0)
                    ->orderBy('fecha_ingreso')
                    ->groupBy('mes', 'fecha_ingreso')
                    ->get()
                    ->pluck('mes')
                    ->toArray();
                $labels_desecho = DB::table('inventario_frio')
                    ->select(DB::raw('DISTINCT DATE_FORMAT(fecha_ingreso, "%Y-%m") AS mes'))
                    ->where('fecha_ingreso', '>=', $request->desde)
                    ->where('fecha_ingreso', '<=', $request->hasta)
                    ->where('estado', 1)
                    ->where('basura', 1)
                    ->orderBy('fecha_ingreso')
                    ->groupBy('mes', 'fecha_ingreso')
                    ->get()
                    ->pluck('mes')
                    ->toArray();

                $data_cosecha = [];
                foreach ($labels_cosecha as $l) {
                    $query = DB::table('desglose_recepcion as dr')
                        ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                        ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                        ->select(
                            DB::raw('sum(dr.tallos_x_malla * dr.cantidad_mallas) as cantidad')
                        )
                        ->where('dr.estado', 1)
                        ->whereMonth('r.fecha_ingreso', '=', date('m', strtotime($l)))
                        ->whereYear('r.fecha_ingreso', '=', date('Y', strtotime($l)));
                    if ($request->planta != 'T')
                        $query = $query->where('v.id_planta', $request->planta);
                    $query = $query->get()[0];

                    $data_cosecha[] = $query;
                }
                $data_postcosecha = [];
                foreach ($labels_postcosecha as $l) {
                    $query = DB::table('inventario_frio as i')
                        ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                        ->select(
                            DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad')
                        )
                        ->where('i.estado', 1)
                        ->where('i.basura', 0)
                        ->whereMonth('i.fecha_ingreso', '=', date('m', strtotime($l)))
                        ->whereYear('i.fecha_ingreso', '=', date('Y', strtotime($l)))
                        ->where('v.receta', 0);
                    if ($request->planta != 'T')
                        $query = $query->where('v.id_planta', $request->planta);
                    $query = $query->get()[0];

                    $data_postcosecha[] = $query;
                }
                $data_desecho = [];
                foreach ($labels_desecho as $l) {
                    $query = DB::table('inventario_frio as i')
                        ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                        ->select(
                            DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad')
                        )
                        ->where('i.estado', 1)
                        ->where('i.basura', 1)
                        ->whereMonth('i.fecha_ingreso', '=', date('m', strtotime($l)))
                        ->whereYear('i.fecha_ingreso', '=', date('Y', strtotime($l)))
                        ->where('v.receta', 0);
                    if ($request->planta != 'T')
                        $query = $query->where('v.id_planta', $request->planta);
                    $query = $query->get()[0];

                    $data_desecho[] = $query;
                }
            } else {    // semanal
                $labels_cosecha = $labels_postcosecha = $labels_desecho = DB::table('semana')
                    ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
                    ->where('fecha_final', '>=', $request->desde)
                    ->where('fecha_final', '<=', $request->hasta)
                    ->orderBy('codigo')
                    ->get();

                $data_cosecha = [];
                $data_postcosecha = [];
                $data_desecho = [];
                foreach ($labels_cosecha as $l) {
                    $query = DB::table('desglose_recepcion as dr')
                        ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                        ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                        ->select(
                            DB::raw('sum(dr.tallos_x_malla * dr.cantidad_mallas) as cantidad')
                        )
                        ->where('v.estado', 1)
                        ->where('dr.estado', 1)
                        ->where('r.fecha_ingreso', '>=', $l->fecha_inicial)
                        ->where('r.fecha_ingreso', '<=', $l->fecha_final);
                    if ($request->planta != 'T')
                        $query = $query->where('v.id_planta', $request->planta);
                    $query = $query->get()[0];
                    $data_cosecha[] = $query;

                    $query = DB::table('inventario_frio as i')
                        ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                        ->select(
                            DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad')
                        )
                        ->where('v.estado', 1)
                        ->where('i.estado', 1)
                        ->where('i.basura', 0)
                        ->where('i.fecha_ingreso', '>=', $l->fecha_inicial)
                        ->where('i.fecha_ingreso', '<=', $l->fecha_final)
                        ->where('v.receta', 0);
                    if ($request->planta != 'T')
                        $query = $query->where('v.id_planta', $request->planta);
                    $query = $query->get()[0];
                    $data_postcosecha[] = $query;

                    $query = DB::table('inventario_frio as i')
                        ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                        ->select(
                            DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad')
                        )
                        ->where('v.estado', 1)
                        ->where('i.estado', 1)
                        ->where('i.basura', 1)
                        ->where('i.fecha_ingreso', '>=', $l->fecha_inicial)
                        ->where('i.fecha_ingreso', '<=', $l->fecha_final)
                        ->where('v.receta', 0);
                    if ($request->planta != 'T')
                        $query = $query->where('v.id_planta', $request->planta);
                    $query = $query->get()[0];
                    $data_desecho[] = $query;
                }
            }
            if ($request->tipo_grafica == 'line') {
                $tipo_grafica = 'line';
                $fill_grafica = 'false';
            } else if ($request->tipo_grafica == 'area') {
                $tipo_grafica = 'line';
                $fill_grafica = 'true';
            } else {
                $tipo_grafica = 'bar';
                $fill_grafica = 'true';
            }
            $datos = [
                'labels_cosecha' => $labels_cosecha,
                'labels_postcosecha' => $labels_postcosecha,
                'labels_desecho' => $labels_desecho,
                'data_cosecha' => $data_cosecha,
                'data_postcosecha' => $data_postcosecha,
                'data_desecho' => $data_desecho,
                'tipo_grafica' => $tipo_grafica,
                'fill_grafica' => $fill_grafica,
                'rango' => $request->rango,
            ];
        } else {
            $view = 'graficas_annos';
            $annos = explode(' - ', $request->annos);

            en_desarrollo();
        }

        return view('adminlte.crm.postcocecha.partials.' . $view, $datos);
    }

    public function listar_ranking(Request $request)
    {
        if ($request->criterio_ranking == 'C') {  // Cosecha
            if ($request->tipo_ranking == 'P') {
                $query = DB::table('desglose_recepcion as dr')
                    ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                    ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                    ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                    ->select(
                        'v.id_planta',
                        'p.nombre',
                        DB::raw('sum(dr.tallos_x_malla * dr.cantidad_mallas) as cantidad'),
                    )
                    ->where('dr.estado', 1)
                    ->where('r.fecha_ingreso', '>=', $request->desde)
                    ->where('r.fecha_ingreso', '<=', $request->hasta)
                    ->groupBy(
                        'v.id_planta',
                        'p.nombre'
                    )
                    ->orderBy('cantidad', 'desc')
                    ->limit(4)
                    ->get();
            } else {
                $query = DB::table('desglose_recepcion as dr')
                    ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                    ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                    ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                    ->select(
                        DB::raw('CONCAT(p.nombre, "-", v.nombre) as nombre'),
                        DB::raw('sum(dr.tallos_x_malla * dr.cantidad_mallas) as cantidad'),
                    )
                    ->where('dr.estado', 1)
                    ->where('r.fecha_ingreso', '>=', $request->desde)
                    ->where('r.fecha_ingreso', '<=', $request->hasta)
                    ->groupBy(
                        'dr.id_variedad',
                        'nombre',
                    )
                    ->orderBy('cantidad', 'desc')
                    ->limit(4)
                    ->get();
            }
        }
        if ($request->criterio_ranking == 'P') {  // Postcosecha
            if ($request->tipo_ranking == 'P') {    // por plantas
                $query = DB::table('inventario_frio as i')
                    ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                    ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                    ->select(
                        'v.id_planta',
                        'p.nombre',
                        DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad'),
                    )
                    ->where('i.estado', 1)
                    ->where('i.basura', 0)
                    ->where('i.fecha_ingreso', '>=', $request->desde)
                    ->where('i.fecha_ingreso', '<=', $request->hasta)
                    ->where('v.receta', 0)
                    ->groupBy(
                        'v.id_planta',
                        'p.nombre'
                    )
                    ->orderBy('cantidad', 'desc')
                    ->limit(4)
                    ->get();
            } else {    // por variedades
                $query = DB::table('inventario_frio as i')
                    ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                    ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                    ->select(
                        DB::raw('CONCAT(p.nombre, "-", v.nombre) as nombre'),
                        DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad'),
                    )
                    ->where('i.estado', 1)
                    ->where('i.basura', 0)
                    ->where('i.fecha_ingreso', '>=', $request->desde)
                    ->where('i.fecha_ingreso', '<=', $request->hasta)
                    ->where('v.receta', 0)
                    ->groupBy(
                        'nombre'
                    )
                    ->orderBy('cantidad', 'desc')
                    ->limit(4)
                    ->get();
            }
        }
        if ($request->criterio_ranking == 'D') {  // Desecho
            if ($request->tipo_ranking == 'P') {    // por plantas
                $query = DB::table('inventario_frio as i')
                    ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                    ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                    ->select(
                        'v.id_planta',
                        'p.nombre',
                        DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad'),
                    )
                    ->where('i.estado', 1)
                    ->where('i.basura', 1)
                    ->where('i.fecha_ingreso', '>=', $request->desde)
                    ->where('i.fecha_ingreso', '<=', $request->hasta)
                    ->where('v.receta', 0)
                    ->groupBy(
                        'v.id_planta',
                        'p.nombre'
                    )
                    ->orderBy('cantidad', 'desc')
                    ->limit(4)
                    ->get();
            } else {    // por variedades
                $query = DB::table('inventario_frio as i')
                    ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                    ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                    ->select(
                        DB::raw('CONCAT(p.nombre, "-", v.nombre) as nombre'),
                        DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad'),
                    )
                    ->where('i.estado', 1)
                    ->where('i.basura', 1)
                    ->where('i.fecha_ingreso', '>=', $request->desde)
                    ->where('i.fecha_ingreso', '<=', $request->hasta)
                    ->where('v.receta', 0)
                    ->groupBy(
                        'nombre'
                    )
                    ->orderBy('cantidad', 'desc')
                    ->limit(4)
                    ->get();
            }
        }
        return view('adminlte.crm.postcocecha.partials.listar_ranking', [
            'query' => $query,
            'criterio' => $request->criterio_ranking,
        ]);
    }
}
