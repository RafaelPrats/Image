<?php

namespace yura\Http\Controllers\CRM;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\ClasificacionVerde;
use yura\Modelos\Cosecha;
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

class tblPostcosechaController extends Controller
{
    public function inicio(Request $request)
    {
        $annos = DB::table('recepcion')
            ->select(DB::raw('YEAR(fecha_ingreso) as anno'))->distinct()
            ->orderBy('anno')
            ->get()->pluck('anno')->toArray();
        $plantas = Planta::where('estado', 1)
            ->orderBy('orden')
            ->get();
        $semana_pasada = getSemanaByDate(opDiasFecha('-', 7, hoy()));

        return view('adminlte.crm.tbl_postcosecha.inicio', [
            'annos' => $annos,
            'plantas' => $plantas,
            'semana_pasada' => $semana_pasada,

            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function filtrar_tablas(Request $request)
    {
        if ($request->annos == '')
            $annos = [date('Y')];
        else
            $annos = explode(' - ', $request->annos);

        $listado = [];
        if ($request->rango == 'S') { // SEMANAL
            if ($request->desde_semanal != '' && $request->hasta_semanal != '') {
                $listado_annos = [];
                foreach ($annos as $a) {
                    $desde_semanal = strlen($request->desde_semanal) < 2 ? '0' . $request->desde_semanal : $request->desde_semanal;
                    $desde_semanal = substr($a, 2, 2) . $desde_semanal;
                    $hasta_semanal = strlen($request->hasta_semanal) < 2 ? '0' . $request->hasta_semanal : $request->hasta_semanal;
                    $hasta_semanal = substr($a, 2, 2) . $hasta_semanal;
                    $semanas = getSemanasByCodigos($desde_semanal, $hasta_semanal);
                    $listado_annos[] = [
                        'anno' => $a,
                        'semanas' => $semanas,
                    ];
                }
                $fecha_desde = $listado_annos[0]['semanas'][0]->fecha_inicial;
                $fecha_hasta = $listado_annos[count($listado_annos) - 1]['semanas'][count($listado_annos[count($listado_annos) - 1]['semanas']) - 1]->fecha_final;
                if ($request->tipo_listado == 'V') {    // por variedades
                    $view = 'semanal_variedades';
                    if ($request->criterio == 'C') {
                        $variedades = DB::table('desglose_recepcion as dr')
                            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                            ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select(
                                'dr.id_variedad',
                                DB::raw('CONCAT(p.nombre, "-", v.nombre) as nombre')
                            )->distinct()
                            ->where('r.fecha_ingreso', '>=', $fecha_desde)
                            ->where('r.fecha_ingreso', '<=', $fecha_hasta);
                        if ($request->planta != 'T')
                            $variedades = $variedades->where('v.id_planta', $request->planta);
                        $variedades = $variedades->orderBy('p.orden')
                            ->orderBy('v.orden')
                            ->get();
                    } else if ($request->criterio == 'P') {
                        $variedades = DB::table('inventario_frio as i')
                            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select(
                                'i.id_variedad',
                                DB::raw('CONCAT(p.nombre, "-", v.nombre) as nombre')
                            )->distinct()
                            ->where('i.fecha_ingreso', '>=', $fecha_desde)
                            ->where('i.fecha_ingreso', '<=', $fecha_hasta)
                            ->where('i.estado', 1)
                            ->where('i.basura', 0)
                            ->where('v.receta', 0);
                        if ($request->planta != 'T')
                            $variedades = $variedades->where('v.id_planta', $request->planta);
                        $variedades = $variedades->orderBy('p.orden')
                            ->orderBy('v.orden')
                            ->get();
                    } else if ($request->criterio == 'D') {
                        $variedades = DB::table('inventario_frio as i')
                            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select(
                                'i.id_variedad',
                                DB::raw('CONCAT(p.nombre, "-", v.nombre) as nombre')
                            )->distinct()
                            ->where('i.fecha_ingreso', '>=', $fecha_desde)
                            ->where('i.fecha_ingreso', '<=', $fecha_hasta)
                            ->where('i.estado', 1)
                            ->where('i.basura', 1)
                            ->where('v.receta', 0);
                        if ($request->planta != 'T')
                            $variedades = $variedades->where('v.id_planta', $request->planta);
                        $variedades = $variedades->orderBy('p.orden')
                            ->orderBy('v.orden')
                            ->get();
                    }
                    foreach ($variedades as $var) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_semanas = [];
                            foreach ($a['semanas'] as $sem) {
                                $valor = 0;
                                if ($request->criterio == 'C') {
                                    $query = DB::table('desglose_recepcion as dr')
                                        ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                                        ->select(
                                            DB::raw('sum(dr.tallos_x_malla * dr.cantidad_mallas) as cantidad')
                                        )
                                        ->where('dr.estado', 1)
                                        ->where('r.fecha_ingreso', '>=', $sem->fecha_inicial)
                                        ->where('r.fecha_ingreso', '<=', $sem->fecha_final)
                                        ->where('dr.id_variedad', $var->id_variedad)
                                        ->get()[0]->cantidad;
                                    $valor = $query;
                                }
                                if ($request->criterio == 'P') {
                                    $query = DB::table('inventario_frio as i')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                                        ->select(
                                            DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad')
                                        )
                                        ->where('i.estado', 1)
                                        ->where('i.basura', 0)
                                        ->where('i.fecha_ingreso', '>=', $sem->fecha_inicial)
                                        ->where('i.fecha_ingreso', '<=', $sem->fecha_final)
                                        ->where('i.id_variedad', $var->id_variedad)
                                        ->get()[0]->cantidad;
                                    $valor = $query;
                                }
                                if ($request->criterio == 'D') {
                                    $query = DB::table('inventario_frio as i')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                                        ->select(
                                            DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad')
                                        )
                                        ->where('i.estado', 1)
                                        ->where('i.basura', 1)
                                        ->where('i.fecha_ingreso', '>=', $sem->fecha_inicial)
                                        ->where('i.fecha_ingreso', '<=', $sem->fecha_final)
                                        ->where('i.id_variedad', $var->id_variedad)
                                        ->get()[0]->cantidad;
                                    $valor = $query;
                                }

                                $valores_semanas[] = [
                                    'semana' => $sem->codigo,
                                    'valor' => $valor != '' ? $valor : 0,
                                ];
                            }
                            $valores_anno[] = [
                                'anno' => $a['anno'],
                                'valores_semanas' => $valores_semanas
                            ];
                        }
                        $listado[] = [
                            'variedad' => $var,
                            'valores_anno' => $valores_anno
                        ];
                    }
                } else {    // por plantas
                    $view = 'semanal_plantas';
                    if ($request->criterio == 'C') {
                        $plantas = DB::table('desglose_recepcion as dr')
                            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                            ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select('v.id_planta', 'p.nombre')->distinct()
                            ->where('r.fecha_ingreso', '>=', $fecha_desde)
                            ->where('r.fecha_ingreso', '<=', $fecha_hasta);
                        if ($request->planta != 'T')
                            $plantas = $plantas->where('v.id_planta', $request->planta);
                        $plantas = $plantas->orderBy('p.orden')
                            ->get();
                    } else if ($request->criterio == 'P') {
                        $plantas = DB::table('inventario_frio as i')
                            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select('v.id_planta', 'p.nombre')->distinct()
                            ->where('i.fecha_ingreso', '>=', $fecha_desde)
                            ->where('i.fecha_ingreso', '<=', $fecha_hasta)
                            ->where('i.estado', 1)
                            ->where('i.basura', 0)
                            ->where('v.receta', 0);
                        if ($request->planta != 'T')
                            $plantas = $plantas->where('v.id_planta', $request->planta);
                        $plantas = $plantas->orderBy('p.orden')
                            ->get();
                    } else if ($request->criterio == 'D') {
                        $plantas = DB::table('inventario_frio as i')
                            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select('v.id_planta', 'p.nombre')->distinct()
                            ->where('i.fecha_ingreso', '>=', $fecha_desde)
                            ->where('i.fecha_ingreso', '<=', $fecha_hasta)
                            ->where('i.estado', 1)
                            ->where('i.basura', 1)
                            ->where('v.receta', 0);
                        if ($request->planta != 'T')
                            $plantas = $plantas->where('v.id_planta', $request->planta);
                        $plantas = $plantas->orderBy('p.orden')
                            ->get();
                    }
                    foreach ($plantas as $p) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_semanas = [];
                            foreach ($a['semanas'] as $sem) {
                                $valor = 0;
                                if ($request->criterio == 'C') {
                                    $query = DB::table('desglose_recepcion as dr')
                                        ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                                        ->select(
                                            DB::raw('sum(dr.tallos_x_malla * dr.cantidad_mallas) as cantidad')
                                        )
                                        ->where('dr.estado', 1)
                                        ->where('r.fecha_ingreso', '>=', $sem->fecha_inicial)
                                        ->where('r.fecha_ingreso', '<=', $sem->fecha_final)
                                        ->where('v.id_planta', $p->id_planta)
                                        ->get()[0]->cantidad;
                                    $valor = $query;
                                }
                                if ($request->criterio == 'P') {
                                    $query = DB::table('inventario_frio as i')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                                        ->select(
                                            DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad')
                                        )
                                        ->where('i.estado', 1)
                                        ->where('i.basura', 0)
                                        ->where('i.fecha_ingreso', '>=', $sem->fecha_inicial)
                                        ->where('i.fecha_ingreso', '<=', $sem->fecha_final)
                                        ->where('v.id_planta', $p->id_planta)
                                        ->get()[0]->cantidad;
                                    $valor = $query;
                                }
                                if ($request->criterio == 'D') {
                                    $query = DB::table('inventario_frio as i')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                                        ->select(
                                            DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad')
                                        )
                                        ->where('i.estado', 1)
                                        ->where('i.basura', 1)
                                        ->where('i.fecha_ingreso', '>=', $sem->fecha_inicial)
                                        ->where('i.fecha_ingreso', '<=', $sem->fecha_final)
                                        ->where('v.id_planta', $p->id_planta)
                                        ->get()[0]->cantidad;
                                    $valor = $query;
                                }

                                $valores_semanas[] = [
                                    'semana' => $sem->codigo,
                                    'valor' => $valor != '' ? $valor : 0,
                                ];
                            }
                            if (count($valores_semanas))
                                $valores_anno[] = [
                                    'anno' => $a['anno'],
                                    'valores_semanas' => $valores_semanas
                                ];
                        }
                        if (count($valores_anno))
                            $listado[] = [
                                'planta' => $p,
                                'valores_anno' => $valores_anno
                            ];
                    }
                }
            } else {
                return '<div class="alert alert-warning text-center">Las Semanas "desde" y "hasta" son incorrectas.</div>';
            }
        } else {    // MENSUAL
            if ($request->desde_mensual != '' && $request->hasta_mensual != '') {
                $listado_annos = [];
                foreach ($annos as $a) {
                    $meses = [];
                    for ($m = $request->desde_mensual; $m <= $request->hasta_mensual; $m++) {
                        $meses[] = strlen($m) == 1 ? '0' . $m : $m;
                    }
                    $listado_annos[] = [
                        'anno' => $a,
                        'meses' => $meses,
                    ];
                }
                $mes_desde = $listado_annos[0]['anno'] . '-' . $listado_annos[0]['meses'][0];
                $mes_hasta = $listado_annos[count($listado_annos) - 1]['anno'] . '-' . $listado_annos[count($listado_annos) - 1]['meses'][count($listado_annos[count($listado_annos) - 1]['meses']) - 1];

                if ($request->tipo_listado == 'V') {    // por variedades
                    $view = 'mensual_variedades';
                    if ($request->criterio == 'C') {
                        $variedades = DB::table('desglose_recepcion as dr')
                            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                            ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select(
                                'dr.id_variedad',
                                DB::raw('CONCAT(p.nombre, "-", v.nombre) as nombre')
                            )->distinct()
                            ->where('r.fecha_ingreso', '>=', $mes_desde . '-01')
                            ->where('r.fecha_ingreso', '<=', $mes_hasta . '-31');
                        if ($request->planta != 'T')
                            $variedades = $variedades->where('v.id_planta', $request->planta);
                        $variedades = $variedades->orderBy('p.orden')
                            ->orderBy('v.orden')
                            ->get();
                    } else if ($request->criterio == 'P') {
                        $variedades = DB::table('inventario_frio as i')
                            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select(
                                'i.id_variedad',
                                DB::raw('CONCAT(p.nombre, "-", v.nombre) as nombre')
                            )->distinct()
                            ->where('i.fecha_ingreso', '>=', $mes_desde . '-01')
                            ->where('i.fecha_ingreso', '<=', $mes_hasta . '-31')
                            ->where('i.estado', 1)
                            ->where('i.basura', 0)
                            ->where('v.receta', 0);
                        if ($request->planta != 'T')
                            $variedades = $variedades->where('v.id_planta', $request->planta);
                        $variedades = $variedades->orderBy('p.orden')
                            ->orderBy('v.orden')
                            ->get();
                    } else if ($request->criterio == 'D') {
                        $variedades = DB::table('inventario_frio as i')
                            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select(
                                'i.id_variedad',
                                DB::raw('CONCAT(p.nombre, "-", v.nombre) as nombre')
                            )->distinct()
                            ->where('i.fecha_ingreso', '>=', $mes_desde . '-01')
                            ->where('i.fecha_ingreso', '<=', $mes_hasta . '-31')
                            ->where('i.estado', 1)
                            ->where('i.basura', 1)
                            ->where('v.receta', 0);
                        if ($request->planta != 'T')
                            $variedades = $variedades->where('v.id_planta', $request->planta);
                        $variedades = $variedades->orderBy('p.orden')
                            ->orderBy('v.orden')
                            ->get();
                    }
                    foreach ($variedades as $var) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_meses = [];
                            foreach ($a['meses'] as $mes) {
                                $fecha_desde = $a['anno'] . '-' . $mes . '-01';
                                $fecha_hasta = $a['anno'] . '-' . $mes . '-31';
                                $valor = 0;
                                if ($request->criterio == 'C') {
                                    $query = DB::table('desglose_recepcion as dr')
                                        ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                                        ->select(
                                            DB::raw('sum(dr.tallos_x_malla * dr.cantidad_mallas) as cantidad')
                                        )
                                        ->where('dr.estado', 1)
                                        ->where('r.fecha_ingreso', '>=', $fecha_desde)
                                        ->where('r.fecha_ingreso', '<=', $fecha_hasta)
                                        ->where('dr.id_variedad', $var->id_variedad)
                                        ->get()[0]->cantidad;
                                    $valor = $query;
                                }
                                if ($request->criterio == 'P') {
                                    $query = DB::table('inventario_frio as i')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                                        ->select(
                                            DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad')
                                        )
                                        ->where('i.estado', 1)
                                        ->where('i.basura', 0)
                                        ->where('i.fecha_ingreso', '>=', $fecha_desde)
                                        ->where('i.fecha_ingreso', '<=', $fecha_hasta)
                                        ->where('i.id_variedad', $var->id_variedad)
                                        ->get()[0]->cantidad;
                                    $valor = $query;
                                }
                                if ($request->criterio == 'D') {
                                    $query = DB::table('inventario_frio as i')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                                        ->select(
                                            DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad')
                                        )
                                        ->where('i.estado', 1)
                                        ->where('i.basura', 1)
                                        ->where('i.fecha_ingreso', '>=', $fecha_desde)
                                        ->where('i.fecha_ingreso', '<=', $fecha_hasta)
                                        ->where('i.id_variedad', $var->id_variedad)
                                        ->get()[0]->cantidad;
                                    $valor = $query;
                                }

                                $valores_meses[] = [
                                    'mes' => $mes,
                                    'valor' => $valor != '' ? $valor : 0,
                                ];
                            }
                            $valores_anno[] = [
                                'anno' => $a['anno'],
                                'valores_meses' => $valores_meses
                            ];
                        }
                        $listado[] = [
                            'variedad' => $var,
                            'valores_anno' => $valores_anno
                        ];
                    }
                } else {    // por plantas
                    $view = 'mensual_plantas';
                    if ($request->criterio == 'C') {
                        $plantas = DB::table('desglose_recepcion as dr')
                            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                            ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select('v.id_planta', 'p.nombre')->distinct()
                            ->where('r.fecha_ingreso', '>=', $mes_desde . '-01')
                            ->where('r.fecha_ingreso', '<=', $mes_hasta . '-31');
                        if ($request->planta != 'T')
                            $plantas = $plantas->where('v.id_planta', $request->planta);
                        $plantas = $plantas->orderBy('p.orden')
                            ->get();
                    } else if ($request->criterio == 'P') {
                        $plantas = DB::table('inventario_frio as i')
                            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select('v.id_planta', 'p.nombre')->distinct()
                            ->where('i.fecha_ingreso', '>=', $mes_desde . '-01')
                            ->where('i.fecha_ingreso', '<=', $mes_hasta . '-31')
                            ->where('i.estado', 1)
                            ->where('i.basura', 0)
                            ->where('v.receta', 0);
                        if ($request->planta != 'T')
                            $plantas = $plantas->where('v.id_planta', $request->planta);
                        $plantas = $plantas->orderBy('p.orden')
                            ->get();
                    } else if ($request->criterio == 'D') {
                        $plantas = DB::table('inventario_frio as i')
                            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select('v.id_planta', 'p.nombre')->distinct()
                            ->where('i.fecha_ingreso', '>=', $mes_desde . '-01')
                            ->where('i.fecha_ingreso', '<=', $mes_hasta . '-31')
                            ->where('i.estado', 1)
                            ->where('i.basura', 1)
                            ->where('v.receta', 0);
                        if ($request->planta != 'T')
                            $plantas = $plantas->where('v.id_planta', $request->planta);
                        $plantas = $plantas->orderBy('p.orden')
                            ->get();
                    }
                    foreach ($plantas as $p) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_meses = [];
                            foreach ($a['meses'] as $mes) {
                                $fecha_desde = $a['anno'] . '-' . $mes . '-01';
                                $fecha_hasta = $a['anno'] . '-' . $mes . '-31';
                                $valor = 0;
                                if ($request->criterio == 'C') {
                                    $query = DB::table('desglose_recepcion as dr')
                                        ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                                        ->select(
                                            DB::raw('sum(dr.tallos_x_malla * dr.cantidad_mallas) as cantidad')
                                        )
                                        ->where('dr.estado', 1)
                                        ->where('r.fecha_ingreso', '>=', $fecha_desde)
                                        ->where('r.fecha_ingreso', '<=', $fecha_hasta)
                                        ->where('v.id_planta', $p->id_planta)
                                        ->get()[0]->cantidad;
                                    $valor = $query;
                                }
                                if ($request->criterio == 'P') {
                                    $query = DB::table('inventario_frio as i')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                                        ->select(
                                            DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad')
                                        )
                                        ->where('i.estado', 1)
                                        ->where('i.basura', 0)
                                        ->where('i.fecha_ingreso', '>=', $fecha_desde)
                                        ->where('i.fecha_ingreso', '<=', $fecha_hasta)
                                        ->where('v.id_planta', $p->id_planta)
                                        ->get()[0]->cantidad;
                                    $valor = $query;
                                }
                                if ($request->criterio == 'D') {
                                    $query = DB::table('inventario_frio as i')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                                        ->select(
                                            DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad')
                                        )
                                        ->where('i.estado', 1)
                                        ->where('i.basura', 1)
                                        ->where('i.fecha_ingreso', '>=', $fecha_desde)
                                        ->where('i.fecha_ingreso', '<=', $fecha_hasta)
                                        ->where('v.id_planta', $p->id_planta)
                                        ->get()[0]->cantidad;
                                    $valor = $query;
                                }

                                $valores_meses[] = [
                                    'mes' => $mes,
                                    'valor' => $valor != '' ? $valor : 0,
                                ];
                            }
                            if (count($valores_meses))
                                $valores_anno[] = [
                                    'anno' => $a['anno'],
                                    'valores_meses' => $valores_meses
                                ];
                        }
                        if (count($valores_anno))
                            $listado[] = [
                                'planta' => $p,
                                'valores_anno' => $valores_anno
                            ];
                    }
                }
            } else {
                return '<div class="alert alert-warning text-center">Los Meses "desde" y "hasta" son incorrectos.</div>';
            }
        }

        return view('adminlte.crm.tbl_postcosecha.partials.' . $view, [
            'listado' => $listado,
            'listado_annos' => $listado_annos,
            'criterio' => $request->criterio,
        ]);
    }

    public function select_planta_semanal(Request $request)
    {
        if ($request->annos == '')
            $annos = [date('Y')];
        else
            $annos = explode(' - ', $request->annos);

        $listado = [];
        if ($request->desde_semanal != '' && $request->hasta_semanal != '') {
            $listado_annos = [];
            foreach ($annos as $a) {
                $request->desde_semanal = strlen($request->desde_semanal) < 2 ? '0' . $request->desde_semanal : $request->desde_semanal;
                $request->desde_semanal = substr($a, 2, 2) . $request->desde_semanal;
                $request->hasta_semanal = strlen($request->hasta_semanal) < 2 ? '0' . $request->hasta_semanal : $request->hasta_semanal;
                $request->hasta_semanal = substr($a, 2, 2) . $request->hasta_semanal;
                $semanas = getSemanasByCodigos($request->desde_semanal, $request->hasta_semanal);
                $listado_annos[] = [
                    'anno' => $a,
                    'semanas' => $semanas,
                ];
            }
            $fecha_desde = $listado_annos[0]['semanas'][0]->fecha_inicial;
            $fecha_hasta = $listado_annos[count($listado_annos) - 1]['semanas'][count($listado_annos[count($listado_annos) - 1]['semanas']) - 1]->fecha_final;

            if ($request->criterio == 'C') {    // cosecha
                $longitudes = DB::table('desglose_recepcion as dr')
                    ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                    ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                    ->select('dr.longitud_ramo')->distinct()
                    ->where('v.id_planta', $request->planta)
                    ->where('r.fecha_ingreso', '>=', $fecha_desde)
                    ->where('r.fecha_ingreso', '<=', $fecha_hasta)
                    ->orderBy('dr.longitud_ramo', 'desc')
                    ->get()->pluck('longitud_ramo')->toArray();
            }
            if ($request->criterio == 'P') {    // postcosecha
                $longitudes = DB::table('inventario_frio as i')
                    ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                    ->select('i.longitud_ramo')->distinct()
                    ->where('i.estado', 1)
                    ->where('i.basura', 0)
                    ->where('v.id_planta', $request->planta)
                    ->where('i.fecha_ingreso', '>=', $fecha_desde)
                    ->where('i.fecha_ingreso', '<=', $fecha_hasta)
                    ->orderBy('i.longitud_ramo', 'desc')
                    ->get()->pluck('longitud_ramo')->toArray();
            }
            if ($request->criterio == 'D') {    // flor de baja
                $longitudes = DB::table('inventario_frio as i')
                    ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                    ->select('i.longitud_ramo')->distinct()
                    ->where('i.estado', 1)
                    ->where('i.basura', 1)
                    ->where('v.id_planta', $request->planta)
                    ->where('i.fecha_ingreso', '>=', $fecha_desde)
                    ->where('i.fecha_ingreso', '<=', $fecha_hasta)
                    ->orderBy('i.longitud_ramo', 'desc')
                    ->get()->pluck('longitud_ramo')->toArray();
            }

            foreach ($longitudes as $l) {
                if ($request->criterio == 'C') {    // cosecha
                    $variedades = DB::table('desglose_recepcion as dr')
                        ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                        ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                        ->select('dr.id_variedad', 'v.nombre')->distinct()
                        ->where('v.id_planta', $request->planta)
                        ->where('dr.longitud_ramo', $l)
                        ->where('r.fecha_ingreso', '>=', $fecha_desde)
                        ->where('r.fecha_ingreso', '<=', $fecha_hasta)
                        ->orderBy('v.orden')
                        ->get();
                }
                if ($request->criterio == 'P') {    // postcosecha
                    $variedades = DB::table('inventario_frio as i')
                        ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                        ->select('i.id_variedad', 'v.nombre')->distinct()
                        ->where('i.estado', 1)
                        ->where('i.basura', 0)
                        ->where('v.id_planta', $request->planta)
                        ->where('i.longitud_ramo', $l)
                        ->where('i.fecha_ingreso', '>=', $fecha_desde)
                        ->where('i.fecha_ingreso', '<=', $fecha_hasta)
                        ->orderBy('v.orden')
                        ->get();
                }
                if ($request->criterio == 'D') {    // flor de baja
                    $variedades = DB::table('inventario_frio as i')
                        ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                        ->select('i.id_variedad', 'v.nombre')->distinct()
                        ->where('i.estado', 1)
                        ->where('i.basura', 1)
                        ->where('v.id_planta', $request->planta)
                        ->where('i.longitud_ramo', $l)
                        ->where('i.fecha_ingreso', '>=', $fecha_desde)
                        ->where('i.fecha_ingreso', '<=', $fecha_hasta)
                        ->orderBy('v.orden')
                        ->get();
                }
                $valores_variedades = [];
                foreach ($variedades as $var) {
                    $valores_anno = [];
                    foreach ($listado_annos as $a) {
                        $valores_semanas = [];
                        foreach ($a['semanas'] as $sem) {
                            $valor = 0;
                            if ($request->criterio == 'C') {    // cosecha
                                $query = DB::table('desglose_recepcion as dr')
                                    ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                                    ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                                    ->select(
                                        DB::raw('sum(dr.tallos_x_malla * dr.cantidad_mallas) as cantidad')
                                    )
                                    ->where('dr.estado', 1)
                                    ->where('r.fecha_ingreso', '>=', $sem->fecha_inicial)
                                    ->where('r.fecha_ingreso', '<=', $sem->fecha_final)
                                    ->where('dr.id_variedad', $var->id_variedad)
                                    ->where('dr.longitud_ramo', $l)
                                    ->get()[0]->cantidad;
                                $valor = $query;
                            }
                            if ($request->criterio == 'P') {    // postcosecha
                                $query = DB::table('inventario_frio as i')
                                    ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                                    ->select(
                                        DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad')
                                    )
                                    ->where('i.estado', 1)
                                    ->where('i.basura', 0)
                                    ->where('i.fecha_ingreso', '>=', $sem->fecha_inicial)
                                    ->where('i.fecha_ingreso', '<=', $sem->fecha_final)
                                    ->where('i.id_variedad', $var->id_variedad)
                                    ->where('i.longitud_ramo', $l)
                                    ->get()[0]->cantidad;
                                $valor = $query;
                            }
                            if ($request->criterio == 'D') {    // flor de baja
                                $query = DB::table('inventario_frio as i')
                                    ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                                    ->select(
                                        DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad')
                                    )
                                    ->where('i.estado', 1)
                                    ->where('i.basura', 1)
                                    ->where('i.fecha_ingreso', '>=', $sem->fecha_inicial)
                                    ->where('i.fecha_ingreso', '<=', $sem->fecha_final)
                                    ->where('i.id_variedad', $var->id_variedad)
                                    ->where('i.longitud_ramo', $l)
                                    ->get()[0]->cantidad;
                                $valor = $query;
                            }

                            $valores_semanas[] = [
                                'semana' => $sem->codigo,
                                'valor' => $valor != '' ? $valor : 0,
                            ];
                        }
                        $valores_anno[] = [
                            'anno' => $a['anno'],
                            'valores_semanas' => $valores_semanas
                        ];
                    }
                    $valores_variedades[] = [
                        'variedad' => $var,
                        'valores_anno' => $valores_anno
                    ];
                }
                $listado[] = [
                    'longitud' => $l,
                    'valores_variedades' => $valores_variedades
                ];
            }
            return view('adminlte.crm.tbl_postcosecha.partials.detalles.select_planta_semanal', [
                'listado' => $listado,
                'listado_annos' => $listado_annos,
                'criterio' => $request->criterio,
                'planta' => Planta::find($request->planta),
            ]);
        } else {
            return '<div class="alert alert-warning text-center">Las Semanas "desde" y "hasta" son incorrectas.</div>';
        }
    }

    public function select_planta_diario(Request $request)
    {
        $semana = getSemanaByCodigo($request->semana);
        if ($request->criterio == 'C') {  // cosecha
            $fechas = DB::table('desglose_recepcion as dr')
                ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                ->select(DB::raw('DATE(fecha_ingreso) as fecha'))->distinct()
                ->where('v.id_planta', $request->planta)
                ->where('r.fecha_ingreso', '>=', $semana->fecha_inicial)
                ->where('r.fecha_ingreso', '<=', $semana->fecha_final)
                ->orderBy('fecha')
                ->get()->pluck('fecha')->toArray();
            $longitudes = DB::table('desglose_recepcion as dr')
                ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                ->select('dr.longitud_ramo')->distinct()
                ->where('v.id_planta', $request->planta)
                ->where('r.fecha_ingreso', '>=', $semana->fecha_inicial)
                ->where('r.fecha_ingreso', '<=', $semana->fecha_final)
                ->orderBy('dr.longitud_ramo', 'desc')
                ->get()->pluck('longitud_ramo')->toArray();
        }
        if ($request->criterio == 'P') {  // postcosecha
            $fechas = DB::table('inventario_frio as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->select(DB::raw('DATE(fecha_ingreso) as fecha'))->distinct()
                ->where('i.estado', 1)
                ->where('i.basura', 0)
                ->where('v.id_planta', $request->planta)
                ->where('i.fecha_ingreso', '>=', $semana->fecha_inicial)
                ->where('i.fecha_ingreso', '<=', $semana->fecha_final)
                ->orderBy('fecha')
                ->get()->pluck('fecha')->toArray();
            $longitudes = DB::table('inventario_frio as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->select('i.longitud_ramo')->distinct()
                ->where('i.estado', 1)
                ->where('i.basura', 0)
                ->where('v.id_planta', $request->planta)
                ->where('i.fecha_ingreso', '>=', $semana->fecha_inicial)
                ->where('i.fecha_ingreso', '<=', $semana->fecha_final)
                ->orderBy('i.longitud_ramo', 'desc')
                ->get()->pluck('longitud_ramo')->toArray();
        }
        if ($request->criterio == 'D') {  // flor de baja
            $fechas = DB::table('inventario_frio as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->select(DB::raw('DATE(fecha_ingreso) as fecha'))->distinct()
                ->where('i.estado', 1)
                ->where('i.basura', 1)
                ->where('v.id_planta', $request->planta)
                ->where('i.fecha_ingreso', '>=', $semana->fecha_inicial)
                ->where('i.fecha_ingreso', '<=', $semana->fecha_final)
                ->orderBy('fecha')
                ->get()->pluck('fecha')->toArray();
            $longitudes = DB::table('inventario_frio as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->select('i.longitud_ramo')->distinct()
                ->where('i.estado', 1)
                ->where('i.basura', 1)
                ->where('v.id_planta', $request->planta)
                ->where('i.fecha_ingreso', '>=', $semana->fecha_inicial)
                ->where('i.fecha_ingreso', '<=', $semana->fecha_final)
                ->orderBy('i.longitud_ramo', 'desc')
                ->get()->pluck('longitud_ramo')->toArray();
        }

        $listado = [];
        foreach ($longitudes as $l) {
            if ($request->criterio == 'C') {    // cosecha
                $variedades = DB::table('desglose_recepcion as dr')
                    ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                    ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                    ->select('dr.id_variedad', 'v.nombre')->distinct()
                    ->where('v.id_planta', $request->planta)
                    ->where('r.fecha_ingreso', '>=', $semana->fecha_inicial)
                    ->where('r.fecha_ingreso', '<=', $semana->fecha_final)
                    ->where('dr.longitud_ramo', $l)
                    ->orderBy('v.orden')
                    ->get();
            }
            if ($request->criterio == 'P') {    // postcosecha
                $variedades = DB::table('inventario_frio as i')
                    ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                    ->select('i.id_variedad', 'v.nombre')->distinct()
                    ->where('i.estado', 1)
                    ->where('i.basura', 0)
                    ->where('v.id_planta', $request->planta)
                    ->where('i.fecha_ingreso', '>=', $semana->fecha_inicial)
                    ->where('i.fecha_ingreso', '<=', $semana->fecha_final)
                    ->where('i.longitud_ramo', $l)
                    ->orderBy('v.orden')
                    ->get();
            }
            if ($request->criterio == 'D') {
                $variedades = DB::table('inventario_frio as i')
                    ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                    ->select('i.id_variedad', 'v.nombre')->distinct()
                    ->where('i.estado', 1)
                    ->where('i.basura', 1)
                    ->where('v.id_planta', $request->planta)
                    ->where('i.fecha_ingreso', '>=', $semana->fecha_inicial)
                    ->where('i.fecha_ingreso', '<=', $semana->fecha_final)
                    ->where('i.longitud_ramo', $l)
                    ->orderBy('v.orden')
                    ->get();
            }
            $valores_variedades = [];
            foreach ($variedades as $v) {
                $valores_fechas = [];
                foreach ($fechas as $f) {
                    if ($request->criterio == 'C') {    // cosecha
                        $valor = DB::table('desglose_recepcion as dr')
                            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                            ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                            ->select(
                                DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad')
                            )
                            ->where('v.id_planta', $request->planta)
                            ->where('dr.id_variedad', $v->id_variedad)
                            ->where('dr.longitud_ramo', $l)
                            ->where('r.fecha_ingreso', $f)
                            ->get()[0]->cantidad;
                    }
                    if ($request->criterio == 'P') {    // postcosecha
                        $valor = DB::table('inventario_frio as i')
                            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                            ->select(
                                DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad')
                            )
                            ->where('i.estado', 1)
                            ->where('i.basura', 0)
                            ->where('v.id_planta', $request->planta)
                            ->where('i.id_variedad', $v->id_variedad)
                            ->where('i.longitud_ramo', $l)
                            ->where('i.fecha_ingreso', $f)
                            ->get()[0]->cantidad;
                    }
                    if ($request->criterio == 'D') {    // flor de baja
                        $valor = DB::table('inventario_frio as i')
                            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                            ->select(
                                DB::raw('sum(i.tallos_x_ramo * i.cantidad) as cantidad')
                            )
                            ->where('i.estado', 1)
                            ->where('i.basura', 1)
                            ->where('v.id_planta', $request->planta)
                            ->where('i.id_variedad', $v->id_variedad)
                            ->where('i.longitud_ramo', $l)
                            ->where('i.fecha_ingreso', $f)
                            ->get()[0]->cantidad;
                    }

                    $valores_fechas[] = [
                        'fecha' => $f,
                        'valor' => $valor != '' ? $valor : 0,
                    ];
                }
                $valores_variedades[] = [
                    'variedad' => $v,
                    'valores_fechas' => $valores_fechas
                ];
            }
            $listado[] = [
                'longitud' => $l,
                'valores_variedades' => $valores_variedades
            ];
        }
        return view('adminlte.crm.tbl_postcosecha.partials.detalles.select_planta_diario', [
            'listado' => $listado,
            'fechas' => $fechas,
            'criterio' => $request->criterio,
            'planta' => Planta::find($request->planta),
        ]);
    }
}
