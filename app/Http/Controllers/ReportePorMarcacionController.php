<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Modelos\DatosExportacion;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class ReportePorMarcacionController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.postcocecha.reporte_por_marcaciones.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => Planta::where('estado', '=', 1)->orderBy('nombre')->get(),
        ]);
    }

    public function listar_filtros(Request $request)
    {
        $planta = Planta::find($request->planta);
        $fecha_min = DB::table('pedido_confirmacion')
            ->select(DB::raw('min(fecha) as fecha'))
            ->where('id_planta', $planta->id_planta)
            ->where('ejecutado', 0)
            ->get()[0]->fecha;
        if ($fecha_min != '') {
            $fecha_fin = opDiasFecha('+', 2, $fecha_min);

            $pedidos = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->select('p.id_pedido', 'p.fecha_pedido')->distinct()
                ->where('v.id_planta', $planta->id_planta)
                ->where('p.estado', '=', 1)
                ->where('p.empaquetado', '=', 0)
                ->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin)
                ->orderBy('p.fecha_pedido')
                ->get();

            $ids_pedidos = $pedidos->pluck('id_pedido')->toArray();

            $pesos = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->join('clasificacion_ramo as cr', 'dee.id_clasificacion_ramo', '=', 'cr.id_clasificacion_ramo')
                ->join('unidad_medida as um_r', 'um_r.id_unidad_medida', '=', 'cr.id_unidad_medida')
                ->select(
                    'dee.id_clasificacion_ramo',
                    'cr.nombre',
                    'um_r.siglas',
                )->distinct()
                ->whereIn('p.id_pedido', $ids_pedidos)
                ->where('p.estado', '=', 1)
                ->where('p.empaquetado', '=', 0)
                ->where('v.id_planta', $request->planta)
                ->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin)
                ->orderBy('cr.nombre', 'desc')
                ->get();

            $presentaciones = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->join('empaque as emp', 'emp.id_empaque', '=', 'dee.id_empaque_p')
                ->select(
                    'dee.id_empaque_p',
                    'emp.nombre',
                )->distinct()
                ->whereIn('p.id_pedido', $ids_pedidos)
                ->where('p.estado', '=', 1)
                ->where('p.empaquetado', '=', 0)
                ->where('v.id_planta', $request->planta)
                ->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin)
                ->orderBy('emp.nombre', 'desc')
                ->get();
            $tallos = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->select(
                    'dee.tallos_x_ramos',
                )->distinct()
                ->whereIn('p.id_pedido', $ids_pedidos)
                ->where('p.estado', '=', 1)
                ->where('p.empaquetado', '=', 0)
                ->where('v.id_planta', $request->planta)
                ->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin)
                ->orderBy('dee.tallos_x_ramos', 'desc')
                ->get();
            $longitudes = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->join('unidad_medida as um_l', 'um_l.id_unidad_medida', '=', 'dee.id_unidad_medida')
                ->select(
                    'dee.longitud_ramo',
                    'dee.id_unidad_medida',
                    'um_l.siglas',
                )->distinct()
                ->whereIn('p.id_pedido', $ids_pedidos)
                ->where('p.estado', '=', 1)
                ->where('p.empaquetado', '=', 0)
                ->where('v.id_planta', $request->planta)
                ->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin)
                ->orderBy('dee.longitud_ramo', 'desc')
                ->orderBy('um_l.siglas', 'desc')
                ->get();
            $marcaciones = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('detallepedido_datoexportacion as dm', 'dm.id_detalle_pedido', '=', 'dp.id_detalle_pedido')
                ->join('dato_exportacion as de', 'de.id_dato_exportacion', '=', 'dm.id_dato_exportacion')
                ->select(
                    'de.nombre',
                    'dm.id_dato_exportacion',
                )->distinct()
                ->whereIn('p.id_pedido', $ids_pedidos)
                ->where('p.estado', '=', 1)
                ->where('p.empaquetado', '=', 0)
                ->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin)
                ->orderBy('de.nombre')
                ->get();
        }

        return view('adminlte.gestion.postcocecha.reporte_por_marcaciones.partials.listado', [
            'pesos' => isset($pesos) ? $pesos : [],
            'presentaciones' => isset($presentaciones) ? $presentaciones : [],
            'tallos' => isset($tallos) ? $tallos : [],
            'longitudes' => isset($longitudes) ? $longitudes : [],
            'marcaciones' => isset($marcaciones) ? $marcaciones : [],
            'planta' => isset($planta) ? $planta : '',
        ]);
    }

    public function listar_combinaciones(Request $request)
    {
        $planta = Planta::find($request->planta);
        $fecha_min = DB::table('pedido_confirmacion')
            ->select(DB::raw('min(fecha) as fecha'))
            ->where('id_planta', $request->planta)
            ->where('ejecutado', 0)
            ->get()[0]->fecha;

        if ($fecha_min) {
            $fecha_fin = opDiasFecha('+', 2, $fecha_min);

            $pedidos = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->select('p.id_pedido', 'p.fecha_pedido')->distinct()
                ->where('p.estado', '=', 1)
                ->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin)
                ->orderBy('p.fecha_pedido')
                ->get();

            $ids_pedidos = $pedidos->pluck('id_pedido')->toArray();

            $fechas = DB::table('pedido')
                ->select('fecha_pedido')->distinct()
                ->whereIn('id_pedido', $ids_pedidos)
                ->orderBy('fecha_pedido')->get();
            $array_fechas = $fechas->pluck('fecha_pedido')->toArray();

            $combinaciones = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->join('clasificacion_ramo as cr', 'dee.id_clasificacion_ramo', '=', 'cr.id_clasificacion_ramo')
                ->join('unidad_medida as um_r', 'um_r.id_unidad_medida', '=', 'cr.id_unidad_medida')
                ->join('unidad_medida as um_l', 'um_l.id_unidad_medida', '=', 'dee.id_unidad_medida')
                ->join('empaque as emp', 'emp.id_empaque', '=', 'dee.id_empaque_p')
                ->join('detallepedido_datoexportacion as dm', 'dm.id_detalle_pedido', '=', 'dp.id_detalle_pedido')
                ->join('dato_exportacion as de', 'de.id_dato_exportacion', '=', 'dm.id_dato_exportacion')
                ->select(
                    'dee.id_variedad',
                    'v.id_planta',
                    'v.nombre as var_nombre',
                    'v.siglas as var_siglas',
                    'dee.id_clasificacion_ramo',
                    'cr.nombre as nombre_peso',
                    'um_r.siglas as siglas_peso',
                    'dee.tallos_x_ramos',
                    'dee.longitud_ramo',
                    'dee.id_unidad_medida',
                    'um_l.siglas as siglas_longitud',
                    'dee.id_empaque_p',
                    'emp.nombre as empaque_p',
                    'dm.valor as marcacion',
                    'dm.id_dato_exportacion'
                )->distinct()
                ->whereIn('p.id_pedido', $ids_pedidos)
                ->where('p.estado', 1)
                ->where('v.id_planta', $request->planta)
                //->where('p.empaquetado', 0)
                ->where('v.assorted', 0)
                ->where('dm.id_dato_exportacion', $request->marcacion)
                ->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin);
            /*if ($request->variedad != '')
                $combinaciones = $combinaciones->where('dee.id_variedad', '=', $request->variedad);*/
            if ($request->peso != '')
                $combinaciones = $combinaciones->where('dee.id_clasificacion_ramo', $request->peso);
            if ($request->presentacion != '')
                $combinaciones = $combinaciones->where('dee.id_empaque_p', $request->presentacion);
            if ($request->tallos != '')
                $combinaciones = $combinaciones->where('dee.tallos_x_ramos', $request->tallos);
            if ($request->longitud != '') {
                $longitud_ramo = explode('|', $request->longitud)[0];
                $unidad_medida_longitud = explode('|', $request->longitud)[1];
                $combinaciones = $combinaciones->where('dee.longitud_ramo', $longitud_ramo)
                    ->where('dee.id_unidad_medida', $unidad_medida_longitud);
            }
            $combinaciones = $combinaciones->orderBy('v.orden')
                ->get();

            $combinaciones_mixtos = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->join('clasificacion_ramo as cr', 'dee.id_clasificacion_ramo', '=', 'cr.id_clasificacion_ramo')
                ->join('unidad_medida as um_r', 'um_r.id_unidad_medida', '=', 'cr.id_unidad_medida')
                ->join('unidad_medida as um_l', 'um_l.id_unidad_medida', '=', 'dee.id_unidad_medida')
                ->join('empaque as emp', 'emp.id_empaque', '=', 'dee.id_empaque_p')
                ->join('detallepedido_datoexportacion as dm', 'dm.id_detalle_pedido', '=', 'dp.id_detalle_pedido')
                ->join('dato_exportacion as de', 'de.id_dato_exportacion', '=', 'dm.id_dato_exportacion')
                ->select(
                    //DB::raw('sum(dee.cantidad * ee.cantidad * dp.cantidad) as cantidad'),
                    //'dee.id_detalle_especificacionempaque',
                    'dee.id_variedad',
                    'v.id_planta',
                    'v.nombre as var_nombre',
                    'v.siglas as var_siglas',
                    'dee.id_clasificacion_ramo',
                    'cr.nombre as nombre_peso',
                    'um_r.siglas as siglas_peso',
                    'dee.tallos_x_ramos',
                    'dee.longitud_ramo',
                    'dee.id_unidad_medida',
                    'um_l.siglas as siglas_longitud',
                    'dee.id_empaque_p',
                    'emp.nombre as empaque_p',
                    'dm.valor as marcacion',
                    'dm.id_dato_exportacion'
                )->distinct()
                ->whereIn('p.id_pedido', $ids_pedidos)
                ->where('p.estado', 1)
                //->where('p.empaquetado', 0)
                ->where('v.assorted', 1)
                ->where('dm.id_dato_exportacion', $request->marcacion)
                ->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin)
                ->where('v.id_planta', '=', $request->planta);
            if ($request->peso != '')
                $combinaciones_mixtos = $combinaciones_mixtos->where('dee.id_clasificacion_ramo', $request->peso);
            if ($request->presentacion != '')
                $combinaciones_mixtos = $combinaciones_mixtos->where('dee.id_empaque_p', $request->presentacion);
            if ($request->tallos != '')
                $combinaciones_mixtos = $combinaciones_mixtos->where('dee.tallos_x_ramos', $request->tallos);
            if ($request->longitud != '') {
                $longitud_ramo = explode('|', $request->longitud)[0];
                $unidad_medida_longitud = explode('|', $request->longitud)[1];
                $combinaciones_mixtos = $combinaciones_mixtos->where('dee.longitud_ramo', $longitud_ramo)
                    ->where('dee.id_unidad_medida', $unidad_medida_longitud);
            }
            $combinaciones_mixtos = $combinaciones_mixtos->orderBy('v.orden')
                ->get();

            $listado_mixtos = [];
            foreach ($combinaciones_mixtos as $pos => $item) {
                $query = DB::table('distribucion_mixtos as m')
                    ->join('detalle_especificacionempaque as dee', 'dee.id_detalle_especificacionempaque', '=', 'm.id_detalle_especificacionempaque')
                    ->select('m.id_planta', 'm.siglas')->distinct()
                    ->where('m.id_planta', $item->id_planta)
                    ->where('m.ramos', '>', 0)
                    ->where('dee.id_clasificacion_ramo', $item->id_clasificacion_ramo)
                    ->where('dee.tallos_x_ramos', $item->tallos_x_ramos)
                    ->where('dee.longitud_ramo', $item->longitud_ramo)
                    ->where('dee.id_unidad_medida', $item->id_unidad_medida)
                    ->where('dee.id_empaque_p', $item->id_empaque_p)
                    ->where('m.fecha', '>=', opDiasFecha('-', 1, $array_fechas[0]))
                    ->where('m.fecha', '<=', opDiasFecha('-', 1, $array_fechas[count($array_fechas) - 1]))
                    ->get();
                foreach ($query as $q) {
                    $variedad = Variedad::All()
                        ->where('id_planta', $q->id_planta)
                        ->where('siglas', $q->siglas)
                        ->first();
                    $valores = [];
                    foreach ($array_fechas as $fecha) {
                        $ramos = DB::table('distribucion_mixtos as m')
                            ->join('detalle_especificacionempaque as dee', 'dee.id_detalle_especificacionempaque', '=', 'm.id_detalle_especificacionempaque')
                            ->join('detalle_pedido as dp', 'dp.id_detalle_pedido', '=', 'm.id_detalle_pedido')
                            ->join('detallepedido_datoexportacion as dm', 'dm.id_detalle_pedido', '=', 'dp.id_detalle_pedido')
                            ->select(DB::raw('sum(m.ramos * m.piezas) as ramos'))
                            ->where('m.id_planta', $q->id_planta)
                            ->where('m.siglas', $q->siglas)
                            ->where('dee.id_clasificacion_ramo', $item->id_clasificacion_ramo)
                            ->where('dee.tallos_x_ramos', $item->tallos_x_ramos)
                            ->where('dee.longitud_ramo', $item->longitud_ramo)
                            ->where('dee.id_unidad_medida', $item->id_unidad_medida)
                            ->where('dee.id_empaque_p', $item->id_empaque_p)
                            ->where('m.fecha', opDiasFecha('-', 1, $fecha))
                            ->where('dm.id_dato_exportacion', $item->id_dato_exportacion)
                            ->where('dm.valor', $item->marcacion)
                            ->get()[0];
                        array_push($valores, [
                            'fecha' => $fecha,
                            'pedido' => $ramos != '' ? $ramos->ramos : 0,
                        ]);
                    }
                    array_push($listado_mixtos, [
                        'variedad' => $variedad,
                        'combinacion' => $item,
                        'valores' => $valores,
                    ]);
                }
            }

            $listado = [];
            foreach ($combinaciones as $pos_i => $item) {
                $existe = -1;
                foreach ($listado_mixtos as $pos_m => $m) {
                    if (
                        $item->id_variedad == $m['variedad']->id_variedad &&
                        $item->id_clasificacion_ramo == $m['combinacion']->id_clasificacion_ramo &&
                        $item->id_empaque_p == $m['combinacion']->id_empaque_p &&
                        $item->tallos_x_ramos == $m['combinacion']->tallos_x_ramos &&
                        $item->longitud_ramo == $m['combinacion']->longitud_ramo &&
                        $item->id_unidad_medida == $m['combinacion']->id_unidad_medida &&
                        $item->id_dato_exportacion == $m['combinacion']->id_dato_exportacion &&
                        $item->marcacion == $m['combinacion']->marcacion
                    ) {
                        $existe = $pos_m;
                    }
                    if ($existe != -1) {
                        break;
                    }
                }
                array_push($listado, [
                    'id_variedad' => $item->id_variedad,
                    'orden' => getVariedad($item->id_variedad)->orden,
                    'id_planta' => $item->id_planta,
                    'var_nombre' => $item->var_nombre,
                    'id_clasificacion_ramo' => $item->id_clasificacion_ramo,
                    'nombre_peso' => $item->nombre_peso,
                    'siglas_peso' => $item->siglas_peso,
                    'tallos_x_ramos' => $item->tallos_x_ramos,
                    'longitud_ramo' => $item->longitud_ramo,
                    'id_unidad_medida' => $item->id_unidad_medida,
                    'siglas_longitud' => $item->siglas_longitud,
                    'id_empaque_p' => $item->id_empaque_p,
                    'id_dato_exportacion' => $item->id_dato_exportacion,
                    'marcacion' => $item->marcacion,
                    'empaque_p' => $item->empaque_p,
                    'valores' => $existe != -1 ? $listado_mixtos[$existe]['valores'] : '',
                    'tipo' => 'N',
                ]);
            }
            foreach ($listado_mixtos as $pos_m => $m) {
                $existe = false;
                foreach ($combinaciones as $pos_i => $item) {
                    if (
                        $item->id_variedad == $m['variedad']->id_variedad &&
                        $item->id_clasificacion_ramo == $m['combinacion']->id_clasificacion_ramo &&
                        $item->id_empaque_p == $m['combinacion']->id_empaque_p &&
                        $item->tallos_x_ramos == $m['combinacion']->tallos_x_ramos &&
                        $item->longitud_ramo == $m['combinacion']->longitud_ramo &&
                        $item->id_unidad_medida == $m['combinacion']->id_unidad_medida &&
                        $item->id_dato_exportacion == $m['combinacion']->id_dato_exportacion &&
                        $item->marcacion == $m['combinacion']->marcacion
                    ) {
                        $existe = true;
                    }
                    if ($existe)
                        break;
                }
                if (!$existe)
                    array_push($listado, [
                        'id_variedad' => $m['variedad']->id_variedad,
                        'orden' => getVariedad($m['variedad']->id_variedad)->orden,
                        'id_planta' => $m['variedad']->id_planta,
                        'var_nombre' => $m['variedad']->nombre,
                        'id_clasificacion_ramo' => $m['combinacion']->id_clasificacion_ramo,
                        'nombre_peso' => $m['combinacion']->nombre_peso,
                        'siglas_peso' => $m['combinacion']->siglas_peso,
                        'tallos_x_ramos' => $m['combinacion']->tallos_x_ramos,
                        'longitud_ramo' => $m['combinacion']->longitud_ramo,
                        'id_unidad_medida' => $m['combinacion']->id_unidad_medida,
                        'siglas_longitud' => $m['combinacion']->siglas_longitud,
                        'id_empaque_p' => $m['combinacion']->id_empaque_p,
                        'empaque_p' => $m['combinacion']->empaque_p,
                        'id_dato_exportacion' => $m['combinacion']->id_dato_exportacion,
                        'marcacion' => $m['combinacion']->marcacion,
                        'valores' => $m['valores'],
                        'tipo' => 'M',
                    ]);
            }

            $longitud = count($listado);
            for ($i = 0; $i < $longitud; $i++) {
                for ($j = 0; $j < $longitud - 1; $j++) {
                    if ($listado[$j]['orden'] > $listado[$j + 1]['orden']) {
                        $temporal = $listado[$j];
                        $listado[$j] = $listado[$j + 1];
                        $listado[$j + 1] = $temporal;
                    }
                }
            }

            $datos = [
                'fecha_fin' => $fecha_fin,
                'fechas' => $fechas,
                'planta' => $planta,
                'variedad' => $request->variedad,
                'peso' => $request->peso,
                'presentacion' => $request->presentacion,
                'tallos' => $request->tallos,
                'longitud' => $request->longitud,
                'listado' => $listado,
                'ids_pedidos' => $ids_pedidos,
                'marcacion' => DatosExportacion::find($request->marcacion),
            ];
        } else {
            $datos = [
                'listado' => [],
            ];
        }

        return view('adminlte.gestion.postcocecha.reporte_por_marcaciones.partials._listado', $datos);
    }
}
