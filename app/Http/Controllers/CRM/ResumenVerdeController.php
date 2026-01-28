<?php

namespace yura\Http\Controllers\CRM;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\ClasificacionUnitaria;
use yura\Modelos\Submenu;

class ResumenVerdeController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.crm.resumen_verde.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'desde' => getSemanaByDate(opDiasFecha('-', 14, hoy())),
            'hasta' => getSemanaByDate(hoy()),
        ]);
    }

    public function buscar_resumen_verde(Request $request)
    {
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('codigo', '>=', $request->desde)
            ->where('codigo', '<=', $request->hasta)
            ->orderBy('codigo')
            ->get();
        if (count($semanas) > 0) {
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];
            $c_unitarias = DB::table('detalle_clasificacion_verde as d')
                ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
                ->select('d.id_clasificacion_unitaria')->distinct()
                ->where('d.estado', 1)
                ->where('v.estado', 1)
                ->where('v.fecha_ingreso', '>=', $desde->fecha_inicial)
                ->where('v.fecha_ingreso', '<=', $hasta->fecha_final)
                ->get();
            $variedades = DB::table('detalle_clasificacion_verde as d')
                ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
                ->join('variedad as var', 'var.id_variedad', '=', 'd.id_variedad')
                ->select('d.id_variedad', 'var.siglas')->distinct()
                ->where('d.estado', 1)
                ->where('v.estado', 1)
                ->where('v.fecha_ingreso', '>=', $desde->fecha_inicial)
                ->where('v.fecha_ingreso', '<=', $hasta->fecha_final)
                ->orderBy('var.nombre')
                ->get();
            $array_cu = [];
            foreach ($c_unitarias as $cu)
                array_push($array_cu, $cu->id_clasificacion_unitaria);
            $c_unitarias = DB::table('clasificacion_unitaria as c')
                ->join('unidad_medida as u', 'u.id_unidad_medida', '=', 'c.id_unidad_medida')
                ->select('c.id_clasificacion_unitaria', 'c.nombre', 'u.siglas', 'c.color', 'c.precio_venta')->distinct()
                ->whereIn('c.id_clasificacion_unitaria', $array_cu)
                ->orderBy('c.nombre')
                ->get();
            $data = [];
            $total_cosechados = [];
            foreach ($c_unitarias as $pos_cu => $cu) {
                $valores = [];
                foreach ($semanas as $sem) {
                    $var_array = [];
                    if ($pos_cu == 0)
                        $cosecha_x_variedad = [];
                    foreach ($variedades as $var) {
                        $val = DB::table('detalle_clasificacion_verde as d')
                            ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
                            ->select(DB::raw('sum(cantidad_ramos * tallos_x_ramos) as tallos'))
                            ->where('d.estado', 1)
                            ->where('v.estado', 1)
                            ->where('d.id_clasificacion_unitaria', $cu->id_clasificacion_unitaria)
                            ->where('d.id_variedad', $var->id_variedad)
                            ->where('v.fecha_ingreso', '>=', $sem->fecha_inicial)
                            ->where('v.fecha_ingreso', '<=', $sem->fecha_final)
                            ->get()[0]->tallos;
                        array_push($var_array, [
                            'variedad' => $var,
                            'tallos' => $val,
                        ]);
                        if ($pos_cu == 0) {
                            $cosecha = DB::table('desglose_recepcion as d')
                                ->join('recepcion as r', 'r.id_recepcion', '=', 'd.id_recepcion')
                                ->select(DB::raw('sum(cantidad_mallas * tallos_x_malla) as cantidad'))
                                ->where('d.estado', 1)
                                ->where('r.estado', 1)
                                ->where('r.fecha_ingreso', '>=', $sem->fecha_inicial . ' 00:00:00')
                                ->where('r.fecha_ingreso', '<=', $sem->fecha_final . ' 23:59:59')
                                ->where('d.id_variedad', $var->id_variedad)
                                ->get()[0]->cantidad;
                            $calibre = getCalibreByRangoVariedad($sem->fecha_inicial, $sem->fecha_final, $var->id_variedad);
                            $cosecha_x_variedad[] = [
                                'cosecha' => $cosecha,
                                'calibre' => $calibre,
                            ];
                        }
                    }
                    array_push($valores, $var_array);
                    if ($pos_cu == 0)
                        $total_cosechados[] = $cosecha_x_variedad;
                }
                array_push($data, [
                    'unitaria' => $cu,
                    'valores' => $valores,
                ]);
            }
            $totales_sem = [];
            $calibres = [];
            $array_var = [];
            foreach ($variedades as $v)
                array_push($array_var, $v->id_variedad);
            foreach ($semanas as $sem) {
                $val = DB::table('detalle_clasificacion_verde as d')
                    ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
                    ->select(DB::raw('sum(cantidad_ramos * tallos_x_ramos) as tallos'))
                    ->where('d.estado', 1)
                    ->where('v.estado', 1)
                    ->whereIn('d.id_clasificacion_unitaria', $array_cu)
                    ->whereIn('d.id_variedad', $array_var)
                    ->where('v.fecha_ingreso', '>=', $sem->fecha_inicial)
                    ->where('v.fecha_ingreso', '<=', $sem->fecha_final)
                    ->get()[0]->tallos;
                $totales_sem[] = $val;
                $calibre = getCalibreByRangoVariedad($sem->fecha_inicial, $sem->fecha_final, 'T');
                $calibres[] = $calibre;
            }
            return view('adminlte.crm.resumen_verde.partials.listado', [
                'semanas' => $semanas,
                'variedades' => $variedades,
                'data' => $data,
                'unitarias' => $c_unitarias,
                'reporte' => $request->reporte,
                'totales_sem' => $totales_sem,
                'total_cosechados' => $total_cosechados,
                'calibres' => $calibres,
            ]);
        } else {
            return '<div class="alert alert-warning text-center">El rango de semanas está incorrecto</div>';
        }
    }

    public function update_precio(Request $request)
    {
        if ($request->precio != '' && $request->precio >= 0) {
            $model = ClasificacionUnitaria::find($request->unitaria);
            $model->precio_venta = $request->precio;
            $model->save();
            return [
                'success' => true,
                'mensaje' => '<div class="alert alert-success text-center">Se ha actualizado el precio de venta satisfactoriamente</div>',
            ];
        } else
            return [
                'success' => false,
                'mensaje' => '<div class="alert alert-danger text-center">El precio no puede ser vacío</div>',
            ];
    }

    public function listar_resumen_verde_semanal(Request $request)
    {
        $view = 'listado_semanal';
        $fechas = DB::table('clasificacion_verde')
            ->select('fecha_ingreso as fecha')->distinct()
            ->where('fecha_ingreso', '>=', $request->desde)
            ->where('fecha_ingreso', '<=', $request->hasta)
            ->where('estado', 1)
            ->orderBy('fecha_ingreso')
            ->get();
        $q_variedades = DB::table('detalle_clasificacion_verde as d')
            ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
            ->join('variedad as var', 'var.id_variedad', '=', 'd.id_variedad')
            ->select('d.id_variedad', 'var.nombre')->distinct()
            ->where('d.estado', 1)
            ->where('v.estado', 1)
            ->where('v.fecha_ingreso', '>=', $request->desde)
            ->where('v.fecha_ingreso', '<=', $request->hasta)
            ->orderBy('var.nombre')
            ->get();
        $c_unitarias = DB::table('detalle_clasificacion_verde as d')
            ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
            ->select('d.id_clasificacion_unitaria')->distinct()
            ->where('d.estado', 1)
            ->where('v.estado', 1)
            ->where('v.fecha_ingreso', '>=', $request->desde)
            ->where('v.fecha_ingreso', '<=', $request->hasta)
            ->get();
        $array_cu = [];
        foreach ($c_unitarias as $cu)
            array_push($array_cu, $cu->id_clasificacion_unitaria);
        $q_unitarias = DB::table('clasificacion_unitaria as c')
            ->join('unidad_medida as u', 'u.id_unidad_medida', '=', 'c.id_unidad_medida')
            ->select('c.id_clasificacion_unitaria', 'c.nombre', 'u.siglas', 'c.color', 'c.precio_venta')->distinct()
            ->whereIn('c.id_clasificacion_unitaria', $array_cu)
            ->orderBy('c.nombre')
            ->get();
        $data = [];
        foreach ($fechas as $f) {
            $variedades = [];
            foreach ($q_variedades as $var) {
                $unitarias = [];
                foreach ($q_unitarias as $u) {
                    $tallos = DB::table('detalle_clasificacion_verde as d')
                        ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
                        ->select(DB::raw('sum(cantidad_ramos * tallos_x_ramos) as tallos'))
                        ->where('d.estado', 1)
                        ->where('v.estado', 1)
                        ->where('d.id_clasificacion_unitaria', $u->id_clasificacion_unitaria)
                        ->where('d.id_variedad', $var->id_variedad)
                        ->where('v.fecha_ingreso', $f->fecha)
                        ->get()[0]->tallos;
                    $unitarias[] = [
                        'unitaria' => $u,
                        'tallos' => $tallos,
                    ];
                }
                $calibre = getCalibreByRangoVariedad($f->fecha, $f->fecha, $var->id_variedad);
                $calibre_proy = getCalibreMonitoreoByRangoVariedad($f->fecha, $f->fecha, $var->id_variedad);
                $variedades[] = [
                    'variedad' => $var,
                    'unitarias' => $unitarias,
                    'calibre_real' => $calibre,
                    'calibre_proy' => $calibre_proy,
                ];
            }
            $calibre = getCalibreByRangoVariedad($f->fecha, $f->fecha, 'T');
            $calibre_proy = getCalibreMonitoreoByRangoVariedad($f->fecha, $f->fecha, 'T');
            $data[] = [
                'fecha' => $f->fecha,
                'variedades' => $variedades,
                'calibre_real' => $calibre,
                'calibre_proy' => $calibre_proy,
            ];
        }

        $totales_f = [];
        if ($request->reporte == 1) {
            foreach ($q_variedades as $v) {
                $calibre = getCalibreByRangoVariedad($request->desde, $request->hasta, $v->id_variedad);
                $calibre_proy = getCalibreMonitoreoByRangoVariedad($request->desde, $request->hasta, $v->id_variedad);
                array_push($totales_f, [
                    'var' => $v,
                    'calibre_real' => $calibre,
                    'calibre_proy' => $calibre_proy,
                ]);
            }
            $total_calibre = getCalibreByRangoVariedad($request->desde, $request->hasta, 'T');
            $total_calibre_proy = getCalibreMonitoreoByRangoVariedad($request->desde, $request->hasta, 'T');
        }
        if ($request->reporte == 2) {
            $view = 'listado_semanal_produccion';
            $array_var = [];
            foreach ($q_variedades as $v)
                array_push($array_var, $v->id_variedad);
            foreach ($fechas as $f) {
                $val = DB::table('detalle_clasificacion_verde as d')
                    ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
                    ->select(DB::raw('sum(cantidad_ramos * tallos_x_ramos) as tallos'))
                    ->where('d.estado', 1)
                    ->where('v.estado', 1)
                    ->whereIn('d.id_clasificacion_unitaria', $array_cu)
                    ->whereIn('d.id_variedad', $array_var)
                    ->where('v.fecha_ingreso', $f->fecha)
                    ->get()[0]->tallos;
                $totales_f[] = $val;
            }
        }
        return view('adminlte.crm.resumen_verde.partials.' . $view, [
            'variedades' => $q_variedades,
            'unitarias' => $q_unitarias,
            'data' => $data,
            'reporte' => $request->reporte,
            'totales_f' => $totales_f,
            'total_calibre' => $total_calibre,
            'total_calibre_proy' => $total_calibre_proy,
        ]);
    }
}