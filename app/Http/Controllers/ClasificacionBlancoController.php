<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Modelos\ClasificacionBlanco;
use yura\Modelos\ClasificacionRamo;
use yura\Modelos\InventarioFrio;
use yura\Modelos\Pedido;
use yura\Modelos\StockEmpaquetado;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use Validator;
use yura\Modelos\Planta;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Modelos\Clasificador;
use yura\Modelos\PedidoConfirmacion;
use yura\Modelos\PedidoModificacion;
use yura\Modelos\DetalleEspecificacionEmpaque;
use yura\Modelos\DetallePedido;
use yura\Modelos\DistribucionPosco;
use yura\Modelos\DistribucionRecetas;
use yura\Modelos\Empaque;
use yura\Modelos\UsoInventarioFrio;

class ClasificacionBlancoController extends Controller
{
    public function inicio(Request $request)
    {
        $blanco = ClasificacionBlanco::All()->where('fecha_ingreso', hoy())->first();

        return view('adminlte.gestion.postcocecha.clasificacion_blanco.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => Planta::where('estado', '=', 1)->orderBy('nombre')->get(),
            'blanco' => $blanco,
        ]);
    }

    public function listar_clasificacion_blanco(Request $request)
    {
        $planta = Planta::find($request->planta);
        $fecha_min = DB::table('pedido_confirmacion')
            ->select(DB::raw('min(fecha) as fecha'))
            ->where('id_planta', $planta->id_planta)
            ->where('ejecutado', 0)
            ->get()[0]->fecha;
        if ($fecha_min != '') {
            $fecha_fin = opDiasFecha('+', ($request->dias - 1), $fecha_min);

            $pedidos = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->select('p.id_pedido', 'p.fecha_pedido')->distinct()
                ->where('v.id_planta', $planta->id_planta)
                ->where('p.estado', '=', 1)
                ->where('p.empaquetado', '=', 0);
            /*if ($request->variedad != '')
                $pedidos = $pedidos->where('dee.id_variedad', '=', $request->variedad);*/
            $pedidos = $pedidos->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin)
                ->orderBy('p.fecha_pedido')
                ->get();

            $ids_pedidos = $pedidos->pluck('id_pedido')->toArray();

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

            $blanco = ClasificacionBlanco::where('fecha_ingreso', $request->fecha_blanco)
                ->get()
                ->first();

            if ($blanco == '') {
                $blanco = new ClasificacionBlanco();
                $blanco->fecha_ingreso = $request->fecha_blanco;

                if ($blanco->save()) {
                    $id = DB::table('clasificacion_blanco')
                        ->select(DB::raw('max(id_clasificacion_blanco) as id'))
                        ->get()[0]->id;
                    $blanco->id_clasificacion_blanco = $id;
                    bitacora('clasificacion_blanco', $blanco->id_clasificacion_blanco, 'I', 'Creacion de una nueva clasificacion en blanco');
                } else {
                    return '<div class="alert alert-danger text-center">Ha ocurrido un problema al guardar la informacion</div>';
                }
            }
        }

        return view('adminlte.gestion.postcocecha.clasificacion_blanco.partials.listado', [
            'blanco' => isset($blanco) ? $blanco : '',
            'presentaciones' => isset($presentaciones) ? $presentaciones : [],
            'tallos' => isset($tallos) ? $tallos : [],
            'longitudes' => isset($longitudes) ? $longitudes : [],
            'planta' => isset($planta) ? $planta : '',
        ]);
    }

    public function listar_combinaciones(Request $request)
    {
        $ini_timer = date('Y-m-d H:i:s');
        $planta = Planta::find($request->planta);
        $variedad_req = Variedad::find($request->variedad);
        $fecha_min = DB::table('pedido_confirmacion')
            ->select(DB::raw('min(fecha) as fecha'))
            ->where('id_planta', $request->planta)
            ->where('ejecutado', 0)
            ->get()[0]->fecha;

        if ($fecha_min) {
            $fecha_fin = opDiasFecha('+', ($request->dias - 1), $fecha_min);

            $pedidos = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->select('p.id_pedido', 'p.fecha_pedido')->distinct()
                ->where('p.estado', '=', 1);
            if ($request->planta != '')
                $pedidos = $pedidos->where('v.id_planta', '=', $request->planta);
            $pedidos = $pedidos->where('p.fecha_pedido', '>=', $fecha_min)
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
                ->join('unidad_medida as um_l', 'um_l.id_unidad_medida', '=', 'dee.id_unidad_medida')
                ->join('empaque as emp', 'emp.id_empaque', '=', 'dee.id_empaque_p')
                ->select(
                    'dee.id_variedad',
                    'v.id_planta',
                    'v.nombre as var_nombre',
                    'v.siglas as var_siglas',
                    'dee.tallos_x_ramos',
                    'dee.longitud_ramo',
                    'dee.id_unidad_medida',
                    'um_l.siglas as siglas_longitud',
                    'dee.id_empaque_p',
                    'emp.nombre as empaque_p',
                )->distinct()
                ->whereIn('p.id_pedido', $ids_pedidos)
                ->where('p.estado', 1)
                ->where('v.id_planta', $request->planta)
                //->where('p.empaquetado', 0)
                ->where('v.assorted', 0)
                ->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin);
            if ($request->planta != '')
                $combinaciones = $combinaciones->where('v.id_planta', '=', $request->planta);
            if ($request->variedad != '')
                $combinaciones = $combinaciones->where('dee.id_variedad', '=', $request->variedad);
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
                ->join('unidad_medida as um_l', 'um_l.id_unidad_medida', '=', 'dee.id_unidad_medida')
                ->join('empaque as emp', 'emp.id_empaque', '=', 'dee.id_empaque_p')
                ->select(
                    //DB::raw('sum(dee.cantidad * ee.cantidad * dp.cantidad) as cantidad'),
                    //'dee.id_detalle_especificacionempaque',
                    'dee.id_variedad',
                    'v.id_planta',
                    'v.nombre as var_nombre',
                    'v.siglas as var_siglas',
                    'dee.tallos_x_ramos',
                    'dee.longitud_ramo',
                    'dee.id_unidad_medida',
                    'um_l.siglas as siglas_longitud',
                    'dee.id_empaque_p',
                    'emp.nombre as empaque_p',
                )->distinct()
                ->whereIn('p.id_pedido', $ids_pedidos)
                ->where('p.estado', 1)
                //->where('p.empaquetado', 0)
                ->where('v.assorted', 1)
                ->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin)
                ->where('v.id_planta', '=', $request->planta);
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
                    ->where('dee.tallos_x_ramos', $item->tallos_x_ramos)
                    ->where('dee.longitud_ramo', $item->longitud_ramo)
                    ->where('dee.id_unidad_medida', $item->id_unidad_medida)
                    ->where('dee.id_empaque_p', $item->id_empaque_p)
                    ->where('m.fecha', '>=', opDiasFecha('-', 1, $array_fechas[0]))
                    ->where('m.fecha', '<=', opDiasFecha('-', 1, $array_fechas[count($array_fechas) - 1]));
                if ($variedad_req != '')
                    $query = $query->where('m.siglas', $variedad_req->siglas);
                $query = $query->get();
                foreach ($query as $q) {
                    $variedad = DB::table('variedad')
                        ->where('id_planta', $q->id_planta)
                        ->where('siglas', $q->siglas)
                        ->get()
                        ->first();
                    $valores = [];
                    foreach ($array_fechas as $fecha) {
                        $ramos = DB::table('distribucion_mixtos as m')
                            ->join('detalle_especificacionempaque as dee', 'dee.id_detalle_especificacionempaque', '=', 'm.id_detalle_especificacionempaque')
                            ->select(DB::raw('sum(m.ramos * m.piezas) as ramos'))
                            ->where('m.id_planta', $q->id_planta)
                            ->where('m.siglas', $q->siglas)
                            ->where('dee.tallos_x_ramos', $item->tallos_x_ramos)
                            ->where('dee.longitud_ramo', $item->longitud_ramo)
                            ->where('dee.id_unidad_medida', $item->id_unidad_medida)
                            ->where('dee.id_empaque_p', $item->id_empaque_p)
                            ->where('m.fecha', opDiasFecha('-', 1, $fecha))
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
                        $item->id_empaque_p == $m['combinacion']->id_empaque_p &&
                        $item->tallos_x_ramos == $m['combinacion']->tallos_x_ramos &&
                        $item->longitud_ramo == $m['combinacion']->longitud_ramo &&
                        $item->id_unidad_medida == $m['combinacion']->id_unidad_medida
                    ) {
                        $existe = $pos_m;
                    }
                    if ($existe != -1) {
                        break;
                    }
                }
                $model_var = getVariedad($item->id_variedad);
                array_push($listado, [
                    'id_variedad' => $item->id_variedad,
                    'orden' => $model_var->orden,
                    'receta' => $model_var->receta,
                    'id_planta' => $item->id_planta,
                    'var_nombre' => $item->var_nombre,
                    'tallos_x_ramos' => $item->tallos_x_ramos,
                    'longitud_ramo' => $item->longitud_ramo,
                    'id_unidad_medida' => $item->id_unidad_medida,
                    'siglas_longitud' => $item->siglas_longitud,
                    'id_empaque_p' => $item->id_empaque_p,
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
                        $item->id_empaque_p == $m['combinacion']->id_empaque_p &&
                        $item->tallos_x_ramos == $m['combinacion']->tallos_x_ramos &&
                        $item->longitud_ramo == $m['combinacion']->longitud_ramo &&
                        $item->id_unidad_medida == $m['combinacion']->id_unidad_medida
                    ) {
                        $existe = true;
                    }
                    if ($existe)
                        break;
                }
                if (!$existe) {
                    $model_var = getVariedad($m['variedad']->id_variedad);
                    array_push($listado, [
                        'id_variedad' => $m['variedad']->id_variedad,
                        'orden' => $model_var->orden,
                        'receta' => $model_var->receta,
                        'id_planta' => $m['variedad']->id_planta,
                        'var_nombre' => $m['variedad']->nombre,
                        'tallos_x_ramos' => $m['combinacion']->tallos_x_ramos,
                        'longitud_ramo' => $m['combinacion']->longitud_ramo,
                        'id_unidad_medida' => $m['combinacion']->id_unidad_medida,
                        'siglas_longitud' => $m['combinacion']->siglas_longitud,
                        'id_empaque_p' => $m['combinacion']->id_empaque_p,
                        'empaque_p' => $m['combinacion']->empaque_p,
                        'valores' => $m['valores'],
                        'tipo' => 'M',
                    ]);
                }
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
            $all_filtros = $request->variedad == '' && $request->presentacion == '' && $request->tallos == '' && $request->longitud == '';

            $datos = [
                'fecha_fin' => $fecha_fin,
                'fechas' => $fechas,
                'planta' => $planta,
                'variedad' => $request->variedad,
                'presentacion' => $request->presentacion,
                'tallos' => $request->tallos,
                'longitud' => $request->longitud,
                'listado' => $listado,
                'ids_pedidos' => $ids_pedidos,
                'all_filtros' => $all_filtros,
            ];
        } else {
            $datos = [
                'listado' => [],
            ];
        }

        $fin_timer = date('Y-m-d H:i:s');
        //dd('ok', difFechas($fin_timer, $ini_timer));
        return view('adminlte.gestion.postcocecha.clasificacion_blanco.partials._listado', $datos);
    }

    public function confirmar_pedidos(Request $request)
    {
        $confirmado = PedidoConfirmacion::where('fecha', $request->fecha_pedidos)
            ->where('id_planta', $request->planta)
            ->where('ejecutado', 1)
            ->get()
            ->first();
        if ($confirmado == '') {
            $success = true;
            $msg = '';
            if ($request->ramos_armados > 0 || true) {
                /* ============ TABLA INVENTARIO_FRIO ===============*/
                foreach (json_decode($request->arreglo) as $item) {
                    $inventarios = DB::table('inventario_frio as if')
                        ->select('if.id_inventario_frio', 'if.fecha_registro', 'if.disponibles')
                        ->where('estado', '=', 1)
                        ->where('disponibilidad', '=', 1)
                        ->where('disponibles', '>', 0)
                        ->where('id_variedad', '=', $item->variedad)
                        ->where('id_empaque_p', '=', $item->id_empaque_p)
                        ->where('tallos_x_ramo', '=', $item->tallos_x_ramo)
                        ->where('longitud_ramo', '=', $item->longitud_ramo)
                        ->where('id_unidad_medida', '=', $item->id_unidad_medida)
                        ->orderBy('if.fecha_ingreso', 'asc')
                        ->get();

                    $pedido = $item->pedido;
                    foreach ($inventarios as $l) {
                        if ($pedido >= 0) {
                            $disponible = $l->disponibles;
                            $disponibilidad = 1;
                            $usado = 0;
                            if ($pedido >= $disponible) {
                                $pedido = $pedido - $disponible;
                                $usado = $disponible;
                                $disponible = 0;
                            } else {
                                $disponible = $disponible - $pedido;
                                $usado = $pedido;
                                $pedido = 0;
                            }
                            if ($disponible == 0)
                                $disponibilidad = 0;

                            $model = InventarioFrio::find($l->id_inventario_frio);
                            $model->disponibles = $disponible;
                            $model->disponibilidad = $disponibilidad;

                            if ($model->save()) {
                                bitacora('inventario_frio', $model->id_inventario_frio, 'U', 'CONFIRMAR_PEDIDO ' . $usado . ' ramos');

                                if ($usado > 0) {
                                    $uso_inventario = new UsoInventarioFrio();
                                    $uso_inventario->id_inventario_frio = $model->id_inventario_frio;
                                    $uso_inventario->fecha_pedido = $request->fecha_pedidos;
                                    $uso_inventario->ramos = $usado;
                                    $uso_inventario->save();
                                }
                            } else {
                                $success = false;
                                $msg .= '<div class="alert alert-warning text-center">' .
                                    'Ha ocurrido un problema al actualizar las cantidades disponibles de los armados de "' . $item->texto .
                                    '"</div>';
                            }
                        }
                    }
                }

                /* ============ TABLA PEDIDO_CONFIRMACION ===============*/
                $confirmacion = PedidoConfirmacion::where('fecha', '<=', $request->fecha_pedidos)
                    ->where('id_planta', $request->planta)
                    ->where('ejecutado', 0)
                    ->get();
                foreach ($confirmacion as $conf) {
                    $conf->ejecutado = 1;
                    $conf->save();
                    $id = $conf->id_pedido_confirmacion;
                    bitacora('pedido_confirmacion', $id, 'U', 'CONFIRMAR_PEDIDO');
                }

                if ($success) {
                    $msg = '<div class="alert alert-success text-center">Se ha guardado toda la informacion satisfactoriamente</div>';
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> La cantidad de ramos armados no puede ser cero</p>'
                    . '</div>';
            }
        } else {
            $success = false;
            $msg = '<div class="alert alert-warning text-center">' .
                '<p>Ya se <b>CONFIRMO</b> la fecha <b>"' . $request->fecha_pedidos . '"</b> para la <b>flor indicada</b>, por favor, refresque la pagina</p>'
                . '</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function store_armar(Request $request)
    {
        $success = true;
        $msg = '';
        foreach (json_decode($request->arreglo) as $item) {
            if ($item->armar > 0) {
                $inventario = new InventarioFrio();
                $inventario->id_variedad = $item->variedad;
                $inventario->id_empaque_p = $item->id_empaque_p;
                $inventario->tallos_x_ramo = $item->tallos_x_ramo;
                $inventario->longitud_ramo = $item->longitud_ramo;
                $inventario->id_unidad_medida = $item->id_unidad_medida;
                $inventario->fecha_ingreso = $request->fecha;
                $inventario->cantidad = $item->armar;
                $inventario->disponibles = $item->armar;
                $inventario->descripcion = $item->texto;
                $inventario->id_clasificacion_blanco = $request->blanco;
                //$inventario->mesa = isset($item->mesa) ? $item->mesa : null;

                if ($inventario->save()) {
                    $id = InventarioFrio::All()->last()->id_inventario_frio;
                    bitacora('inventario_frio', $id, 'I', 'ARMAR_INVENTARIO_MASIVO');
                } else {
                    $success = false;
                    $msg .= '<div class="alert alert-warning text-center">' .
                        'Ha ocurrido un problema con los armados de "' . $item->texto . '"</div>';
                }
            }
        }

        if ($success) {
            $msg = '<div class="alert alert-success text-center">Se ha guardado toda la informacion satisfactoriamente</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function store_armar_row(Request $request)
    {
        $success = true;
        $msg = '';
        if ($request->armar > 0) {
            $inventario = new InventarioFrio();
            $inventario->id_variedad = $request->variedad;
            $inventario->id_empaque_p = $request->id_empaque_p;
            $inventario->tallos_x_ramo = $request->tallos_x_ramo;
            $inventario->longitud_ramo = $request->longitud_ramo;
            $inventario->id_unidad_medida = $request->id_unidad_medida;
            $inventario->fecha_ingreso = $request->fecha;
            $inventario->cantidad = $request->armar;
            $inventario->disponibles = $request->armar;
            $inventario->descripcion = $request->texto;
            $inventario->id_clasificacion_blanco = $request->blanco;
            $inventario->mesa = $request->mesa;
            $inventario->id_dato_exportacion = $request->id_marcacion;
            $inventario->valor_marcacion = $request->valor_marcacion;

            if ($inventario->save()) {
                $id = DB::table('inventario_frio')
                    ->select(DB::raw('max(id_inventario_frio) as id'))
                    ->get()[0]->id;
                bitacora('inventario_frio', $id, 'I', 'ARMAR_INVENTARIO_FILA');
            } else {
                $success = false;
                $msg .= '<div class="alert alert-warning text-center">' .
                    'Ha ocurrido un problema con los armados de "' . $request->texto .
                    '"</div>';
            }
        }

        if ($success) {
            $msg = 'Se ha <strong>GUARDADO</strong> toda la informacion satisfactoriamente';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function modal_armar_row(Request $request)
    {
        $fecha_min = DB::table('pedido_confirmacion')
            ->select(DB::raw('min(fecha) as fecha'))
            ->where('id_planta', $request->planta)
            ->where('ejecutado', 0)
            ->get()[0]->fecha;
        $marcaciones = [];
        if ($fecha_min) {
            $variedad = getVariedad($request->variedad);
            $fecha_fin = opDiasFecha('+', ($request->dias - 1), $fecha_min);
            $pedidos = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->select('p.id_pedido', 'p.fecha_pedido')->distinct()
                ->where('p.estado', '=', 1)
                ->where('v.id_planta', '=', $request->planta)
                ->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin)
                ->orderBy('p.fecha_pedido')
                ->get();
            $ids_pedidos = $pedidos->pluck('id_pedido')->toArray();

            $marcaciones_solidos = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->join('detallepedido_datoexportacion as dm', 'dm.id_detalle_pedido', '=', 'dp.id_detalle_pedido')
                ->join('dato_exportacion as de', 'de.id_dato_exportacion', '=', 'dm.id_dato_exportacion')
                ->select(
                    'dm.valor as marcacion',
                    'dm.id_dato_exportacion',
                    'de.nombre',
                )->distinct()
                ->whereIn('p.id_pedido', $ids_pedidos)
                ->where('p.estado', 1)
                ->where('v.assorted', 0)
                ->where('dee.id_variedad', $request->variedad)
                ->where('dm.id_dato_exportacion', 1)
                ->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin)
                ->where('dee.id_empaque_p', $request->id_empaque_p)
                ->where('dee.tallos_x_ramos', $request->tallos_x_ramo)
                ->where('dee.longitud_ramo', $request->longitud_ramo)
                ->get();
            $marcaciones_mixtos = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->join('detallepedido_datoexportacion as dm', 'dm.id_detalle_pedido', '=', 'dp.id_detalle_pedido')
                ->join('dato_exportacion as de', 'de.id_dato_exportacion', '=', 'dm.id_dato_exportacion')
                ->join('distribucion_mixtos as dist', 'dist.id_detalle_especificacionempaque', '=', 'dee.id_detalle_especificacionempaque')
                ->select(
                    'dm.valor as marcacion',
                    'dm.id_dato_exportacion',
                    'de.nombre',
                )->distinct()
                ->whereIn('p.id_pedido', $ids_pedidos)
                ->where('p.estado', 1)
                ->where('v.assorted', 1)
                ->where('v.id_planta', $request->planta)
                ->where('dist.siglas', $variedad->siglas)
                ->where('dm.id_dato_exportacion', 1)
                ->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin)
                ->where('dee.id_empaque_p', $request->id_empaque_p)
                ->where('dee.tallos_x_ramos', $request->tallos_x_ramo)
                ->where('dee.longitud_ramo', $request->longitud_ramo)
                ->get();
            $marcaciones = $marcaciones_solidos->merge($marcaciones_mixtos)->sortBy('nombre');

            $listado = [];
            foreach ($marcaciones as $pos_m => $mar) {
                $query = DB::table('pedido as p')
                    ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                    ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                    ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                    ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                    ->join('detallepedido_datoexportacion as dm', 'dm.id_detalle_pedido', '=', 'dp.id_detalle_pedido')
                    //->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                    ->select(
                        'dp.id_detalle_pedido',
                        'dp.cantidad as piezas',
                        'dee.id_detalle_especificacionempaque',
                        'dee.tallos_x_ramos',
                        DB::raw('sum(dee.cantidad * dp.cantidad) as ramos')
                    )
                    ->whereIn('p.id_pedido', $ids_pedidos)
                    ->where('p.estado', '=', 1)
                    ->where('p.fecha_pedido', '>=', $fecha_min)
                    ->where('p.fecha_pedido', '<=', $fecha_fin)
                    ->where('dee.id_variedad', '=', $variedad->id_variedad)
                    ->where('dm.id_dato_exportacion', '=', $mar->id_dato_exportacion)
                    ->where('dm.valor', '=', $mar->marcacion)
                    ->where('dee.id_empaque_p', '=', $request->id_empaque_p);
                if ($request->tallos_x_ramo != '')
                    $query = $query->where('dee.tallos_x_ramos', '=', $request->tallos_x_ramo);
                if ($request->longitud_ramo != '')
                    $query = $query->where('dee.longitud_ramo', '=', $request->longitud_ramo);
                $query = $query->groupBy(
                    'dp.id_detalle_pedido',
                    'dp.cantidad',
                    'dee.id_detalle_especificacionempaque',
                    'dee.tallos_x_ramos',
                )
                    ->get();
                $pedido = 0;
                foreach ($query as $q) {
                    $getRamosXCajaModificado = getRamosXCajaModificado($q->id_detalle_pedido, $q->id_detalle_especificacionempaque);
                    $pedido += isset($getRamosXCajaModificado) ? ($getRamosXCajaModificado->cantidad * $q->piezas) : $q->ramos;
                }

                $pedido += getCantidadRamosPedidosDistribucionForCBByMarcacionRangoFecha(
                    $fecha_min,
                    $fecha_fin,
                    $variedad->id_variedad,
                    $request->id_empaque_p,
                    $request->tallos_x_ramo,
                    $request->longitud_ramo,
                    $mar->marcacion,
                    $mar->id_dato_exportacion
                );

                $armados = getInventarioFrioByMarcacion($variedad->id_variedad, $mar->id_dato_exportacion, $mar->marcacion, $request->id_empaque_p, $request->tallos_x_ramo, $request->longitud_ramo);
                $listado[] = [
                    'marcacion' => $mar,
                    'pedidos' => $pedido,
                    'armados' => $armados,
                ];
            }
        } else {
            return '<div class="alert alert-warning text-center">No se han encontrado resultados que mostrar</div>';
        }
        //dd($listado);
        return view('adminlte.gestion.postcocecha.clasificacion_blanco.forms.modal_armar_row', [
            'pos_comb' => $request->pos_comb,
            'armar' => $request->armar,
            'variedad' => $variedad,
            'tallos_x_ramo' => $request->tallos_x_ramo,
            'longitud_ramo' => $request->longitud_ramo,
            'presentacion' => Empaque::find($request->id_empaque_p),
            'marcaciones' => $marcaciones,
            'listado' => $listado,
        ]);
    }

    public function maduracion(Request $request)
    {
        $variedad = Variedad::find($request['variedad']);
        $fecha_min = DB::table('pedido_confirmacion')
            ->select(DB::raw('min(fecha) as fecha'))
            ->where('id_planta', $variedad->id_planta)
            ->where('ejecutado', 0)
            ->get()[0]->fecha;

        if ($fecha_min) {
            $fecha_fin = opDiasFecha('+', ($request->dias - 1), $fecha_min);

            $inventarios = DB::table('inventario_frio')
                ->select(DB::raw('sum(disponibles) as cantidad'), 'fecha_ingreso')
                ->where('estado', '=', 1)
                ->where('disponibilidad', '=', 1)
                ->where('id_variedad', '=', $request['variedad'])
                ->where('id_empaque_p', '=', $request['id_empaque_p'])
                ->where('tallos_x_ramo', '=', $request['tallos_x_ramo'])
                ->where('longitud_ramo', '=', $request['longitud_ramo'])
                ->where('id_unidad_medida', '=', $request['id_unidad_medida'])
                ->groupBy('fecha_ingreso')
                ->orderBy('fecha_registro')
                ->get();

            $pedidos = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->select('p.id_pedido', 'p.fecha_pedido')->distinct()
                ->where('p.estado', '=', 1)
                ->where('p.empaquetado', '=', 0)
                ->where('v.id_planta', '=', $variedad->id_planta)
                ->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin)
                ->orderBy('p.fecha_pedido')
                ->get();

            $ids_pedidos = $pedidos->pluck('id_pedido')->toArray();

            $fechas = DB::table('pedido')
                ->select('fecha_pedido')->distinct()
                ->whereIn('id_pedido', $ids_pedidos)
                ->orderBy('fecha_pedido')->get();
            $array_fechas = [];
            foreach ($fechas as $f) {
                array_push($array_fechas, $f->fecha_pedido);
            }
        }
        return view('adminlte.gestion.postcocecha.clasificacion_blanco.partials.maduracion', [
            'listado' => $inventarios,
            'ids_pedidos' => $ids_pedidos,
            'fechas' => $array_fechas,
            'id_variedad' => $request['variedad'],
            'texto' => $request->texto,
            'resto' => $request->arreglo != '' ? json_decode($request->arreglo) : [],
            'tallos_x_ramo' => $request->tallos_x_ramo,
            'longitud_ramo' => $request->longitud_ramo,
            'id_empaque_p' => $request->id_empaque_p,
            'id_unidad_medida' => $request->id_unidad_medida,
        ]);
    }

    public function update_inventario(Request $request)
    {
        $success = true;
        $msg = '';

        foreach ($request->arreglo as $item) {
            if ($item['editar'] > 0) {
                $inventario = new InventarioFrio();
                $inventario->id_clasificacion_blanco = $request->id_blanco;
                $inventario->id_empaque_p = $item['id_empaque_p'];
                $inventario->tallos_x_ramo = $item['tallos_x_ramo'];
                $inventario->longitud_ramo = $item['longitud_ramo'];
                $inventario->id_unidad_medida = $item['id_unidad_medida'];
                $inventario->fecha_ingreso = $request->fecha_inventario_frio; // consultar dias de ingreso-dias_maduracion
                $inventario->cantidad = $item['editar'];
                if ($item['basura'] == 1) {
                    $inventario->id_variedad = $request->id_variedad;
                    $inventario->disponibles = 0;
                    $inventario->disponibilidad = 0;
                    $inventario->basura = 1;
                } else {
                    $inventario->id_variedad = $item['variedad'];
                    $inventario->disponibles = $item['editar'];
                }
                $inventario->descripcion = $item['texto'];

                if ($inventario->save()) {
                    $id = DB::table('inventario_frio')
                        ->select(DB::raw('max(id_inventario_frio) as id'))
                        ->get()[0]->id;
                    bitacora('inventario_frio', $id, 'I', 'RE-ARMAR_INVENTARIO');
                } else {
                    $success = false;
                    $msg .= '<div class="alert alert-warning text-center">' .
                        'Ha ocurrido un problema con los armados de "' . $item['texto'] .
                        '"</div>';
                }
            }
        }

        $models = InventarioFrio::where('disponibilidad', 1)
            ->where('estado', 1)
            ->where('basura', 0)
            ->where('id_variedad', $request->id_variedad)
            ->where('fecha_ingreso', $request->fecha_inventario_frio)
            ->where('tallos_x_ramo', $request->tallos_x_ramo)
            ->where('longitud_ramo', $request->longitud_ramo)
            ->where('id_empaque_p', $request->id_empaque_p)
            ->where('id_unidad_medida', $request->id_unidad_medida)
            ->get();

        $meta = $request->editar;

        foreach ($models as $pos => $model) {
            if ($meta > 0) {
                if ($model->disponibles >= $meta) {
                    $model->disponibles = $model->disponibles - $meta;
                    $meta = 0;
                } else {
                    $meta -= $model->disponibles;
                    $model->disponibles = 0;
                }

                if ($model->disponibles == 0)
                    $model->disponibilidad = 0;

                if ($model->save()) {
                    $id = $model->id_inventario_frio;
                    bitacora('inventario_frio', $id, 'U', 'RE-ARMAR_INVENTARIO');
                } else {
                    $success = false;
                    $msg .= '<div class="alert alert-warning text-center">' .
                        'Ha ocurrido un problema al actualzar el inventario seleccionado' .
                        '</div>';
                }
            }
        }

        if ($success) {
            $msg = '<div class="alert alert-success text-center">Se ha guardado toda la informacion satisfactoriamente</div>';
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function update_calsificacion_blanco(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'blanco' => 'required|',
            'personal' => 'required|',
            'hora_inicio' => 'required|',
        ], [
            'blanco.required' => 'La clasificación en blanco es obligatoria',
            'personal.required' => 'La cantidad de trabajadores es obligatoria',
            'hora_inicio.required' => 'La hora de inicio es obligatoria',
        ]);
        if (!$valida->fails()) {
            $blanco = ClasificacionBlanco::find($request->blanco);
            $blanco->personal = $request->personal;
            $blanco->hora_inicio = $request->hora_inicio;

            if ($blanco->save()) {
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se ha guardado satisfactoriamente</p>'
                    . '</div>';
                bitacora('clasificacion_blanco', $blanco->id_clasificacion_blanco, 'U', 'Actualizacion satisfactoria de una clasificacion_blanco');
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> Ha ocurrido un problema al guardar la informacion de la calsificación en blanco en el sistema</p>'
                    . '</div>';
            }
        } else {
            $success = false;
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function store_blanco(Request $request)
    {
        $blanco = ClasificacionBlanco::All()->where('fecha_ingreso', date('Y-m-d'))->first();
        if ($blanco == '') {
            $blanco = new ClasificacionBlanco();
            $blanco->fecha_ingreso = date('Y-m-d');

            if ($blanco->save()) {
                $id = DB::table('clasificacion_blanco')
                    ->select(DB::raw('max(id_clasificacion_blanco) as id'))
                    ->get()[0]->id;
                bitacora('clasificacion_blanco', $id, 'I', 'Creacion de una nueva clasificacion en blanco');

                return [
                    'mensaje' => '<div class="alert alert-success text-center">Se ha creado satisfactoriamente la clasificación en blanco</div>',
                    'success' => true,
                ];
            } else {
                return [
                    'mensaje' => '<div class="alert alert-danger text-center">Ha ocurrido un problema al guardar la informacion</div>',
                    'success' => false,
                ];
            }
        } else {
            return [
                'mensaje' => '<div class="alert alert-info text-center">Ya existe una clasificación en blanco para hoy</div>',
                'success' => true,
            ];
        }
    }

    public function distribuir_trabajo(Request $request)
    {
        $clasificadores = Clasificador::where('estado', 1)->get();
        $variedad = Variedad::find($request->variedad);
        $empaque = Empaque::find($request->empaque);
        $distribuciones = DistribucionPosco::where('fecha', $request->fecha)
            ->where('id_variedad', $request->variedad)
            ->where('id_empaque', $request->empaque)
            ->where('tallos_x_ramo', $request->tallos_x_ramo)
            ->where('longitud', $request->longitud)
            ->orderBy('cantidad')
            ->get();
        $marcaciones_solidos = DB::table('pedido as p')
            ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
            ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->join('detallepedido_datoexportacion as dm', 'dm.id_detalle_pedido', '=', 'dp.id_detalle_pedido')
            ->join('dato_exportacion as de', 'de.id_dato_exportacion', '=', 'dm.id_dato_exportacion')
            ->select(
                'dm.valor as marcacion',
                'dm.id_dato_exportacion',
                'de.nombre',
            )->distinct()
            ->where('p.estado', 1)
            ->where('v.assorted', 0)
            ->where('dee.id_variedad', $request->variedad)
            ->where('dm.id_dato_exportacion', 1)
            ->where('p.fecha_pedido', $request->fecha)
            ->where('dee.id_empaque_p', $request->empaque)
            ->where('dee.tallos_x_ramos', $request->tallos_x_ramo)
            ->where('dee.longitud_ramo', $request->longitud)
            ->get();
        $marcaciones_mixtos = DB::table('pedido as p')
            ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
            ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->join('detallepedido_datoexportacion as dm', 'dm.id_detalle_pedido', '=', 'dp.id_detalle_pedido')
            ->join('dato_exportacion as de', 'de.id_dato_exportacion', '=', 'dm.id_dato_exportacion')
            ->join('distribucion_mixtos as dist', 'dist.id_detalle_especificacionempaque', '=', 'dee.id_detalle_especificacionempaque')
            ->select(
                'dm.valor as marcacion',
                'dm.id_dato_exportacion',
                'de.nombre',
            )->distinct()
            ->where('p.estado', 1)
            ->where('v.assorted', 1)
            ->where('v.id_planta', $variedad->id_planta)
            ->where('dist.siglas', $variedad->siglas)
            ->where('dm.id_dato_exportacion', 1)
            ->where('p.fecha_pedido', $request->fecha)
            ->where('dee.id_empaque_p', $request->empaque)
            ->where('dee.tallos_x_ramos', $request->tallos_x_ramo)
            ->where('dee.longitud_ramo', $request->longitud)
            ->get();
        $marcaciones = $marcaciones_solidos->merge($marcaciones_mixtos)->sortBy('nombre');
        return view('adminlte.gestion.postcocecha.clasificacion_blanco.forms.distribuir_trabajo', [
            'distribuciones' => $distribuciones,
            'clasificadores' => $clasificadores,
            'planta' => $variedad->planta,
            'variedad' => $variedad,
            'empaque' => $empaque,
            'tallos_x_ramo' => $request->tallos_x_ramo,
            'longitud' => $request->longitud,
            'pedidos' => $request->pedidos,
            'actuales' => $request->actuales,
            'saldo' => $request->saldo,
            'fecha' => $request->fecha,
            'pos_comb' => $request->pos_comb,
            'pos_fecha' => $request->pos_fecha,
            'marcaciones' => $marcaciones,
        ]);
    }

    public function ver_rendimiento(Request $request)
    {
        $blanco = ClasificacionBlanco::find($request->blanco);
        return view('adminlte.gestion.postcocecha.clasificacion_blanco.partials.rendimiento', [
            'blanco' => $blanco
        ]);
    }

    public function rendimiento_mesas(Request $request)
    {
        $blanco = ClasificacionBlanco::All()
            ->where('estado', 1)
            ->where('fecha_ingreso', $request->fecha_verde)
            ->first();
        $ramos = DB::table('inventario_frio')
            ->select(DB::raw('sum(cantidad) as cant'))
            ->where('estado', 1)
            ->where('fecha_ingreso', 'like', $request->fecha_verde . '%')
            ->get()[0]->cant;
        $query = DB::table('inventario_frio')
            ->where('estado', 1)
            ->where('fecha_ingreso', 'like', $request->fecha_verde . '%')
            ->get();

        return view('adminlte.gestion.postcocecha.clasificacion_blanco.partials.rendimiento_mesas', [
            'blanco' => $blanco,
            'ramos' => $ramos,
            'query' => $query,
            'getCantidadHorasTrabajoBlanco' => getCantidadHorasTrabajoBlanco($request->fecha_verde),
            'fecha_blanco' => $request->fecha_blanco,
        ]);
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);

        $fileName = "Postcosecha.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_reporte($spread, $request)
    {
        $planta = Planta::find($request->planta);
        $variedad_req = Variedad::find($request->variedad);

        $pedidos = DB::table('pedido as p')
            ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
            ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->select('p.id_pedido', 'p.fecha_pedido')->distinct()
            ->where('p.estado', '=', 1);
        if ($request->planta != '')
            $pedidos = $pedidos->where('v.id_planta', '=', $request->planta);
        $pedidos = $pedidos->where('p.fecha_pedido', $request->fecha)
            ->orderBy('p.fecha_pedido')
            ->get();

        $ids_pedidos = $pedidos->pluck('id_pedido')->toArray();

        $combinaciones = DB::table('pedido as p')
            ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
            ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->join('unidad_medida as um_l', 'um_l.id_unidad_medida', '=', 'dee.id_unidad_medida')
            ->join('empaque as emp', 'emp.id_empaque', '=', 'dee.id_empaque_p')
            ->select(
                'dee.id_variedad',
                'v.id_planta',
                'v.nombre as var_nombre',
                'v.siglas as var_siglas',
                'dee.tallos_x_ramos',
                'dee.longitud_ramo',
                'dee.id_unidad_medida',
                'um_l.siglas as siglas_longitud',
                'dee.id_empaque_p',
                'emp.nombre as empaque_p',
            )->distinct()
            ->where('p.estado', 1)
            ->where('v.id_planta', $request->planta)
            ->where('v.assorted', 0)
            ->where('p.fecha_pedido', $request->fecha);
        if ($request->variedad != '')
            $combinaciones = $combinaciones->where('dee.id_variedad', '=', $request->variedad);
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
        $combinaciones = $combinaciones->orderBy('v.nombre')
            ->get();

        $combinaciones_mixtos = DB::table('pedido as p')
            ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
            ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->join('unidad_medida as um_l', 'um_l.id_unidad_medida', '=', 'dee.id_unidad_medida')
            ->join('empaque as emp', 'emp.id_empaque', '=', 'dee.id_empaque_p')
            ->select(
                'dee.id_variedad',
                'v.id_planta',
                'v.nombre as var_nombre',
                'v.siglas as var_siglas',
                'dee.tallos_x_ramos',
                'dee.longitud_ramo',
                'dee.id_unidad_medida',
                'um_l.siglas as siglas_longitud',
                'dee.id_empaque_p',
                'emp.nombre as empaque_p',
            )->distinct()
            ->where('p.estado', 1)
            ->where('v.id_planta', $request->planta)
            ->where('v.assorted', 1)
            ->where('p.fecha_pedido', $request->fecha);
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
        $combinaciones_mixtos = $combinaciones_mixtos->orderBy('v.nombre')
            ->get();
        $listado_mixtos = [];
        foreach ($combinaciones_mixtos as $pos => $item) {
            $query = DB::table('distribucion_mixtos as m')
                ->join('detalle_especificacionempaque as dee', 'dee.id_detalle_especificacionempaque', '=', 'm.id_detalle_especificacionempaque')
                ->select('m.id_planta', 'm.siglas')->distinct()
                ->where('m.id_planta', $item->id_planta)
                ->where('m.ramos', '>', 0)
                ->where('dee.tallos_x_ramos', $item->tallos_x_ramos)
                ->where('dee.longitud_ramo', $item->longitud_ramo)
                ->where('dee.id_unidad_medida', $item->id_unidad_medida)
                ->where('dee.id_empaque_p', $item->id_empaque_p)
                ->where('m.fecha', opDiasFecha('-', 1, $request->fecha));
            if ($variedad_req != '')
                $query = $query->where('m.siglas', $variedad_req->siglas);
            $query = $query->get();
            foreach ($query as $q) {
                $variedad = DB::table('variedad')
                    ->where('id_planta', $q->id_planta)
                    ->where('siglas', $q->siglas)
                    ->get()
                    ->first();
                $valores = [];
                $ramos = DB::table('distribucion_mixtos as m')
                    ->join('detalle_especificacionempaque as dee', 'dee.id_detalle_especificacionempaque', '=', 'm.id_detalle_especificacionempaque')
                    ->select(DB::raw('sum(m.ramos * m.piezas) as ramos'))
                    ->where('m.id_planta', $q->id_planta)
                    ->where('m.siglas', $q->siglas)
                    ->where('dee.tallos_x_ramos', $item->tallos_x_ramos)
                    ->where('dee.longitud_ramo', $item->longitud_ramo)
                    ->where('dee.id_unidad_medida', $item->id_unidad_medida)
                    ->where('dee.id_empaque_p', $item->id_empaque_p)
                    ->where('m.fecha', opDiasFecha('-', 1, $request->fecha))
                    ->get()[0];
                array_push($valores, [
                    'fecha' => $request->fecha,
                    'pedido' => $ramos != '' ? $ramos->ramos : 0,
                ]);
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
                    $item->id_empaque_p == $m['combinacion']->id_empaque_p &&
                    $item->tallos_x_ramos == $m['combinacion']->tallos_x_ramos &&
                    $item->longitud_ramo == $m['combinacion']->longitud_ramo &&
                    $item->id_unidad_medida == $m['combinacion']->id_unidad_medida
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
                'tallos_x_ramos' => $item->tallos_x_ramos,
                'longitud_ramo' => $item->longitud_ramo,
                'id_unidad_medida' => $item->id_unidad_medida,
                'siglas_longitud' => $item->siglas_longitud,
                'id_empaque_p' => $item->id_empaque_p,
                'empaque_p' => $item->empaque_p,
                'valores' => $existe != -1 ? $listado_mixtos[$existe]['valores'] : '',
                'tipo' => 'N'
            ]);
        }
        foreach ($listado_mixtos as $pos_m => $m) {
            $existe = false;
            foreach ($combinaciones as $pos_i => $item) {
                if (
                    $item->id_variedad == $m['variedad']->id_variedad &&
                    $item->id_empaque_p == $m['combinacion']->id_empaque_p &&
                    $item->tallos_x_ramos == $m['combinacion']->tallos_x_ramos &&
                    $item->longitud_ramo == $m['combinacion']->longitud_ramo &&
                    $item->id_unidad_medida == $m['combinacion']->id_unidad_medida
                ) {
                    $existe = true;
                }
                if ($existe)
                    break;
            }
            if (!$existe) {
                array_push($listado, [
                    'id_variedad' => $m['variedad']->id_variedad,
                    'orden' => getVariedad($m['variedad']->id_variedad)->orden,
                    'id_planta' => $m['variedad']->id_planta,
                    'var_nombre' => $m['variedad']->nombre,
                    'tallos_x_ramos' => $m['combinacion']->tallos_x_ramos,
                    'longitud_ramo' => $m['combinacion']->longitud_ramo,
                    'id_unidad_medida' => $m['combinacion']->id_unidad_medida,
                    'siglas_longitud' => $m['combinacion']->siglas_longitud,
                    'id_empaque_p' => $m['combinacion']->id_empaque_p,
                    'empaque_p' => $m['combinacion']->empaque_p,
                    'valores' => $m['valores'],
                    'tipo' => 'M',
                ]);
            }
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

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle($planta->nombre);
        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Color');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Presentación');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Longitud');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, getDias(TP_ABREVIADO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime($request->fecha)))] . ' ' . $request->fecha);
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Cambios');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, '');
        setBgToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $item) {
            if ($item['longitud_ramo'] != '' && $item['id_unidad_medida'] != '') {
                $longitud_ramo = $item['longitud_ramo'] . ' ' . $item['siglas_longitud'] . ' ';
            } else {
                $longitud_ramo = '';
            }
            $cant_pedido = getCantidadRamosPedidosForCB($request->fecha, $item['id_variedad'], $item['id_empaque_p'], $item['tallos_x_ramos'], $item['longitud_ramo'], $item['id_unidad_medida'], $ids_pedidos);
            $pedido = $cant_pedido['cant_pedido'];
            if ($item['valores'] != '') {
                $pedido += $item['valores'][0]['pedido'];
            }

            if ($cant_pedido['cant_mod'] > 0)
                $val_mod = '+';
            else
                $val_mod = '-';

            if ($pedido > 0 || $cant_pedido['cant_mod'] != 0) {
                $row++;
                $col = 0;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['var_nombre']);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['empaque_p']);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['tallos_x_ramos']);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $longitud_ramo);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pedido);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $cant_pedido['cant_mod'] != 0 ? $val_mod : '');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $cant_pedido['cant_mod'] != 0 ? $cant_pedido['cant_mod'] : '');
            }
        }
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function ver_receta(Request $request)
    {
        $variedad = Variedad::find($request->variedad);
        $planta = Planta::find($variedad->id_planta);
        $fecha_min = DB::table('pedido_confirmacion')
            ->select(DB::raw('min(fecha) as fecha'))
            ->where('id_planta', $planta->id_planta)
            ->where('ejecutado', 0)
            ->get()[0]->fecha;
        $fecha_fin = opDiasFecha('+', 2, $fecha_min);

        $pedidos = DB::table('pedido as p')
            ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
            ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->select(
                'p.id_cliente',
                'p.id_pedido',
                'dp.id_detalle_pedido',
                'dee.id_detalle_especificacionempaque',
            )->distinct()
            ->where('p.estado', 1)
            ->where('v.receta', 1)
            ->where('v.id_planta', $planta->id_planta)
            ->where('dee.id_variedad', $variedad->id_variedad)
            ->where('p.fecha_pedido', '>=', $fecha_min)
            ->where('p.fecha_pedido', '<=', $fecha_fin)
            ->where('dee.id_empaque_p', $request->empaque)
            ->where('dee.tallos_x_ramos', $request->tallos_x_ramo)
            ->where('dee.longitud_ramo', $request->longitud)
            ->orderBy('p.fecha_pedido')
            ->get();
        $listado = [];
        foreach ($pedidos as $p) {
            $det_esp = DetalleEspecificacionEmpaque::find($p->id_detalle_especificacionempaque);
            $det_ped = DetallePedido::find($p->id_detalle_pedido);

            $getRamosXCajaModificado = getRamosXCajaModificado($p->id_detalle_pedido, $p->id_detalle_especificacionempaque);
            $ramos_x_caja = isset($getRamosXCajaModificado) ? $getRamosXCajaModificado->cantidad : $det_esp->cantidad;

            $item_recetas = DistribucionRecetas::where('id_detalle_pedido', $det_ped->id_detalle_pedido)
                ->where('id_detalle_especificacionempaque', $det_esp->id_detalle_especificacionempaque)
                ->get();

            $listado[] = [
                'pedido' => Pedido::find($p->id_pedido),
                'ramos_x_caja' => $ramos_x_caja,
                'det_ped' => $det_ped,
                'det_esp' => $det_esp,
                'item_recetas' => $item_recetas,
            ];
        }
        //dd($listado);
        return view('adminlte.gestion.postcocecha.clasificacion_blanco.partials.ver_receta', [
            'listado' => $listado,
            'planta' => $planta,
            'variedad' => $variedad,
        ]);
    }

    public function ver_cambios(Request $request)
    {
        $listado = [];
        $modificaciones_solidas = PedidoModificacion::join('detalle_especificacionempaque as dee', 'dee.id_detalle_especificacionempaque', '=', 'pedido_modificacion.id_detalle_especificacionempaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            //->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('pedido_modificacion.*')->distinct()
            ->where('dee.id_variedad', '=', $request->variedad)
            ->where('pedido_modificacion.fecha_anterior_pedido', $request->fecha)
            ->where('pedido_modificacion.usar', 1)
            ->where('dee.id_empaque_p', '=', $request->empaque)
            ->where('dee.tallos_x_ramos', '=', $request->tallos_x_ramo)
            ->where('dee.longitud_ramo', '=', $request->longitud)
            ->get();

        $variedad_model = Variedad::find($request->variedad);
        $modificaciones_mixtas = PedidoModificacion::join('detalle_especificacionempaque as dee', 'dee.id_detalle_especificacionempaque', '=', 'pedido_modificacion.id_detalle_especificacionempaque')
            ->select('pedido_modificacion.*')->distinct()
            ->where('pedido_modificacion.siglas', '=', $variedad_model->siglas)
            ->where('pedido_modificacion.id_planta', '=', $variedad_model->id_planta)
            ->where('pedido_modificacion.fecha_anterior_pedido', $request->fecha)
            ->where('pedido_modificacion.usar', 1)
            ->where('dee.id_empaque_p', '=', $request->empaque)
            ->where('dee.tallos_x_ramos', '=', $request->tallos_x_ramo)
            ->where('dee.longitud_ramo', '=', $request->longitud)
            ->get();

        $listado = $modificaciones_solidas->merge($modificaciones_mixtas);
        return view('adminlte.gestion.postcocecha.clasificacion_blanco.partials.ver_cambios', [
            'listado' => $listado,
        ]);
    }

    public function listar_combinaciones_row(Request $request)
    {
        $variedad = Variedad::find($request->variedad);
        $presentacion = Empaque::find($request->id_empaque_p);
        $fechas = json_decode($request->fechas);

        $pedidos = DB::table('pedido as p')
            ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
            ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->select('p.id_pedido', 'p.fecha_pedido')->distinct()
            ->where('p.estado', '=', 1)
            ->where('v.id_planta', '=', $variedad->id_planta)
            ->where('p.fecha_pedido', '>=', $fechas[0])
            ->where('p.fecha_pedido', '<=', $fechas[count($fechas) - 1])
            ->orderBy('p.fecha_pedido')
            ->get();

        $ids_pedidos = $pedidos->pluck('id_pedido')->toArray();

        $valores = [];
        foreach ($fechas as $fecha) {
            $ramos = DB::table('distribucion_mixtos as m')
                ->join('detalle_especificacionempaque as dee', 'dee.id_detalle_especificacionempaque', '=', 'm.id_detalle_especificacionempaque')
                ->select(DB::raw('sum(m.ramos * m.piezas) as ramos'))
                ->where('m.id_planta', $variedad->id_planta)
                ->where('m.siglas', $variedad->siglas)
                ->where('dee.tallos_x_ramos', $request->tallos_x_ramo)
                ->where('dee.longitud_ramo', $request->longitud_ramo)
                ->where('dee.id_unidad_medida', 1)
                ->where('dee.id_empaque_p', $request->id_empaque_p)
                ->where('m.fecha', opDiasFecha('-', 1, $fecha))
                ->get()[0];
            array_push($valores, [
                'fecha' => $fecha,
                'pedido' => $ramos != '' ? $ramos->ramos : 0,
            ]);
        }
        return view('adminlte.gestion.postcocecha.clasificacion_blanco.partials._listar_combinaciones_row', [
            'variedad' => $variedad,
            'presentacion' => $presentacion,
            'tallos_x_ramo' => $request->tallos_x_ramo,
            'longitud_ramo' => $request->longitud_ramo,
            'pos_comb' => $request->pos_comb,
            'ids_pedidos' => $ids_pedidos,
            'valores' => $valores,
            'fechas' => $fechas,
        ]);
    }

    public function exportar_combinaciones(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_combinaciones($spread, $request);

        $fileName = "Postcosecha.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_combinaciones($spread, $request)
    {
        $request = json_decode($request->datos);
        $planta = Planta::find($request->planta);
        $variedad_req = Variedad::find($request->variedad);
        $fecha_min = DB::table('pedido_confirmacion')
            ->select(DB::raw('min(fecha) as fecha'))
            ->where('id_planta', $request->planta)
            ->where('ejecutado', 0)
            ->get()[0]->fecha;

        if ($fecha_min) {
            $fecha_fin = opDiasFecha('+', ($request->dias - 1), $fecha_min);

            $pedidos = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->select('p.id_pedido', 'p.fecha_pedido')->distinct()
                ->where('p.estado', '=', 1);
            if ($request->planta != '')
                $pedidos = $pedidos->where('v.id_planta', '=', $request->planta);
            $pedidos = $pedidos->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin)
                ->orderBy('p.fecha_pedido')
                ->get();

            $ids_pedidos = $pedidos->pluck('id_pedido')->toArray();

            $fechas = DB::table('pedido')
                ->select('fecha_pedido')->distinct()
                ->whereIn('id_pedido', $ids_pedidos)
                ->orderBy('fecha_pedido')->get();
            $array_fechas = [];
            foreach ($fechas as $f) {
                /*$ped_conf = PedidoConfirmacion::All()
                    ->where('id_planta', $request->planta)
                    ->where('fecha', $f->fecha_pedido)
                    ->where('ejecutado', 0)
                    ->first();
                if ($ped_conf != '')*/
                array_push($array_fechas, $f->fecha_pedido);
            }

            $combinaciones = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->join('unidad_medida as um_l', 'um_l.id_unidad_medida', '=', 'dee.id_unidad_medida')
                ->join('empaque as emp', 'emp.id_empaque', '=', 'dee.id_empaque_p')
                ->select(
                    'dee.id_variedad',
                    'v.id_planta',
                    'v.nombre as var_nombre',
                    'v.siglas as var_siglas',
                    'dee.tallos_x_ramos',
                    'dee.longitud_ramo',
                    'dee.id_unidad_medida',
                    'um_l.siglas as siglas_longitud',
                    'dee.id_empaque_p',
                    'emp.nombre as empaque_p',
                )->distinct()
                ->whereIn('p.id_pedido', $ids_pedidos)
                ->where('p.estado', 1)
                ->where('v.id_planta', $request->planta)
                ->where('v.assorted', 0)
                ->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin);
            if ($request->planta != '')
                $combinaciones = $combinaciones->where('v.id_planta', '=', $request->planta);
            if ($request->variedad != '')
                $combinaciones = $combinaciones->where('dee.id_variedad', '=', $request->variedad);
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
                ->join('unidad_medida as um_l', 'um_l.id_unidad_medida', '=', 'dee.id_unidad_medida')
                ->join('empaque as emp', 'emp.id_empaque', '=', 'dee.id_empaque_p')
                ->select(
                    //DB::raw('sum(dee.cantidad * ee.cantidad * dp.cantidad) as cantidad'),
                    //'dee.id_detalle_especificacionempaque',
                    'dee.id_variedad',
                    'v.id_planta',
                    'v.nombre as var_nombre',
                    'v.siglas as var_siglas',
                    'dee.tallos_x_ramos',
                    'dee.longitud_ramo',
                    'dee.id_unidad_medida',
                    'um_l.siglas as siglas_longitud',
                    'dee.id_empaque_p',
                    'emp.nombre as empaque_p',
                )->distinct()
                ->whereIn('p.id_pedido', $ids_pedidos)
                ->where('p.estado', 1)
                ->where('v.assorted', 1)
                ->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin)
                ->where('v.id_planta', '=', $request->planta);
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
                    ->where('dee.tallos_x_ramos', $item->tallos_x_ramos)
                    ->where('dee.longitud_ramo', $item->longitud_ramo)
                    ->where('dee.id_unidad_medida', $item->id_unidad_medida)
                    ->where('dee.id_empaque_p', $item->id_empaque_p)
                    ->where('m.fecha', '>=', opDiasFecha('-', 1, $array_fechas[0]))
                    ->where('m.fecha', '<=', opDiasFecha('-', 1, $array_fechas[count($array_fechas) - 1]));
                if ($variedad_req != '')
                    $query = $query->where('m.siglas', $variedad_req->siglas);
                $query = $query->get();
                foreach ($query as $q) {
                    $variedad = DB::table('variedad')
                        ->where('id_planta', $q->id_planta)
                        ->where('siglas', $q->siglas)
                        ->get()
                        ->first();
                    $valores = [];
                    foreach ($array_fechas as $fecha) {
                        $ramos = DB::table('distribucion_mixtos as m')
                            ->join('detalle_especificacionempaque as dee', 'dee.id_detalle_especificacionempaque', '=', 'm.id_detalle_especificacionempaque')
                            ->select(DB::raw('sum(m.ramos * m.piezas) as ramos'))
                            ->where('m.id_planta', $q->id_planta)
                            ->where('m.siglas', $q->siglas)
                            ->where('dee.tallos_x_ramos', $item->tallos_x_ramos)
                            ->where('dee.longitud_ramo', $item->longitud_ramo)
                            ->where('dee.id_unidad_medida', $item->id_unidad_medida)
                            ->where('dee.id_empaque_p', $item->id_empaque_p)
                            ->where('m.fecha', opDiasFecha('-', 1, $fecha))
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
                        $item->id_empaque_p == $m['combinacion']->id_empaque_p &&
                        $item->tallos_x_ramos == $m['combinacion']->tallos_x_ramos &&
                        $item->longitud_ramo == $m['combinacion']->longitud_ramo &&
                        $item->id_unidad_medida == $m['combinacion']->id_unidad_medida
                    ) {
                        $existe = $pos_m;
                    }
                    if ($existe != -1) {
                        break;
                    }
                }
                $model_var = getVariedad($item->id_variedad);
                array_push($listado, [
                    'id_variedad' => $item->id_variedad,
                    'orden' => $model_var->orden,
                    'receta' => $model_var->receta,
                    'id_planta' => $item->id_planta,
                    'var_nombre' => $item->var_nombre,
                    'tallos_x_ramos' => $item->tallos_x_ramos,
                    'longitud_ramo' => $item->longitud_ramo,
                    'id_unidad_medida' => $item->id_unidad_medida,
                    'siglas_longitud' => $item->siglas_longitud,
                    'id_empaque_p' => $item->id_empaque_p,
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
                        $item->id_empaque_p == $m['combinacion']->id_empaque_p &&
                        $item->tallos_x_ramos == $m['combinacion']->tallos_x_ramos &&
                        $item->longitud_ramo == $m['combinacion']->longitud_ramo &&
                        $item->id_unidad_medida == $m['combinacion']->id_unidad_medida
                    ) {
                        $existe = true;
                    }
                    if ($existe)
                        break;
                }
                if (!$existe) {
                    $model_var = getVariedad($m['variedad']->id_variedad);
                    array_push($listado, [
                        'id_variedad' => $m['variedad']->id_variedad,
                        'orden' => $model_var->orden,
                        'receta' => $model_var->receta,
                        'id_planta' => $m['variedad']->id_planta,
                        'var_nombre' => $m['variedad']->nombre,
                        'tallos_x_ramos' => $m['combinacion']->tallos_x_ramos,
                        'longitud_ramo' => $m['combinacion']->longitud_ramo,
                        'id_unidad_medida' => $m['combinacion']->id_unidad_medida,
                        'siglas_longitud' => $m['combinacion']->siglas_longitud,
                        'id_empaque_p' => $m['combinacion']->id_empaque_p,
                        'empaque_p' => $m['combinacion']->empaque_p,
                        'valores' => $m['valores'],
                        'tipo' => 'M',
                    ]);
                }
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

            $fecha_fin = $fecha_fin;
            $fechas = $fechas;
            $planta = $planta;
            $variedad = $request->variedad;
            $presentacion = $request->presentacion;
            $tallos = $request->tallos;
            $longitud = $request->longitud;
            $listado = $listado;
            $ids_pedidos = $ids_pedidos;
        } else {
            $listado = [];
        }

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('CLASIFICACION');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Color');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Presentación');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Longitud');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));
        $totales_fecha = [];
        $pos_fecha = 1;
        foreach ($fechas as $fecha) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, getDias(TP_ABREVIADO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime($fecha->fecha_pedido)))] . ' ' . $fecha->fecha_pedido);
            $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col + 3] . $row);
            $totales_fecha[] = [
                'pedidos' => 0,
                'actuales' => 0,
                'cambios' => 0,
            ];
            $pos_fecha++;
            $col += 3;
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Cuarto Frio');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));

        $row++;
        $col = 4;
        foreach ($fechas as $fecha) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Pedidos');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Actuales');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Por Armar');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Cambios');
        }
        $col++;

        setBgToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $item) {
            if (($item['longitud_ramo'] != '' || $item['longitud_ramo'] >= 0) && $item['id_unidad_medida'] != '') {
                $longitud_ramo = $item['longitud_ramo'] . $item['siglas_longitud'];
            } else {
                $longitud_ramo = '';
            }
            $tieneDistribucionesPendientes = tieneDistribucionesPendientes($fechas[0]->fecha_pedido, $item['id_empaque_p'], $item['tallos_x_ramos'], $item['longitud_ramo'], $item['id_unidad_medida'], $ids_pedidos);

            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $planta->nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['var_nombre']);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['empaque_p']);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['tallos_x_ramos']);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $longitud_ramo);

            if (!$tieneDistribucionesPendientes) {
                setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'FF0000');
            }
            $total_inventario = getDisponibleInventarioFrio($item['id_variedad'], $item['id_empaque_p'], $item['tallos_x_ramos'], $item['longitud_ramo'], $item['id_unidad_medida']);
            $acumulado_pedido = 0;
            foreach ($fechas as $pos_f => $fecha) {
                $cant_pedido = getCantidadRamosPedidosForCB($fecha->fecha_pedido, $item['id_variedad'], $item['id_empaque_p'], $item['tallos_x_ramos'], $item['longitud_ramo'], $item['id_unidad_medida'], $ids_pedidos);
                $pedido = $cant_pedido['cant_pedido'];
                $val_ped = '';
                if ($item['valores'] != '') {
                    $pedido = $cant_pedido['cant_pedido'] + $item['valores'][$pos_f]['pedido'];
                    $val_ped = $item['valores'][$pos_f]['pedido'];
                }
                $acumulado_pedido += $pedido + $cant_pedido['cant_mod'];
                $saldo = $total_inventario - $acumulado_pedido;
                $totales_fecha[$pos_f]['pedidos'] += $pedido;
                $totales_fecha[$pos_f]['actuales'] += $pedido + $cant_pedido['cant_mod'];

                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pedido);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pedido + $cant_pedido['cant_mod']);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $saldo);

                if ($cant_pedido['cant_mod'] > 0) {
                    $totales_fecha[$pos_f]['cambios'] += $cant_pedido['cant_mod'];
                } else {
                    $totales_fecha[$pos_f]['cambios'] -= $cant_pedido['cant_mod'] * -1;
                }
                $col++;
                if ($cant_pedido['cant_mod'] != 0) {
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $cant_pedido['cant_mod']);
                }
            }
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_inventario);
        }

        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col + 4] . $row);
        $col += 4;
        foreach ($fechas as $pos_f => $fecha) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $totales_fecha[$pos_f]['pedidos']);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $totales_fecha[$pos_f]['actuales']);
            $col++;
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $totales_fecha[$pos_f]['cambios']);
        }
        $col++;
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function store_distribucion(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach (json_decode($request->data) as $d) {
                $model = new DistribucionPosco();
                $model->id_variedad = $request->variedad;
                $model->id_empaque = $request->empaque;
                $model->tallos_x_ramo = $request->tallos_x_ramo;
                $model->longitud = $request->longitud;
                $model->fecha = $request->fecha;
                $model->id_clasificador = $d->clasificador;
                $model->cantidad = $d->cantidad;
                $model->id_dato_exportacion = $d->id_marcacion != '' ? $d->id_marcacion : null;
                $model->valor_marcacion = $d->valor_marcacion != '' ? $d->valor_marcacion : null;
                $model->save();
                $model->id_distribucion_posco = DB::table('distribucion_posco')
                    ->select(DB::raw('max(id_distribucion_posco) as id'))
                    ->get()[0]->id;
                bitacora('distribucion_posco', $model->id_distribucion_posco, 'I', 'STORE_DISTRIBUCION');
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'mensaje' => $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine()
            ];
        }
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>GUARDADO</strong> la distribucion correctamente'
        ];
    }

    public function delete_distribucion(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = DistribucionPosco::find($request->id);
            $model->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'mensaje' => $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine()
            ];
        }
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>ELIMINADO</strong> la distribucion correctamente'
        ];
    }

    public function modal_inventario_row(Request $request)
    {
        $variedad = getVariedad($request->variedad);
        $listado = InventarioFrio::where('id_variedad', $request->variedad)
            ->where('tallos_x_ramo', $request->tallos_x_ramo)
            ->where('longitud_ramo', $request->longitud_ramo)
            ->where('id_empaque_p', $request->id_empaque_p)
            ->where('estado', 1)
            ->where('basura', 0)
            ->where('disponibles', '>', 0)
            ->orderBy('fecha_ingreso', 'desc')
            ->get();

        $fecha_min = DB::table('pedido_confirmacion')
            ->select(DB::raw('min(fecha) as fecha'))
            ->where('id_planta', $request->planta)
            ->where('ejecutado', 0)
            ->get()[0]->fecha;
        $marcaciones = [];
        $listado_marcaciones = [];
        $array_marcaciones = [];
        if ($fecha_min) {
            $fecha_fin = opDiasFecha('+', ($request->dias - 1), $fecha_min);
            $pedidos = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->select('p.id_pedido', 'p.fecha_pedido')->distinct()
                ->where('p.estado', '=', 1)
                ->where('v.id_planta', '=', $variedad->id_planta)
                ->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin)
                ->orderBy('p.fecha_pedido')
                ->get();
            $ids_pedidos = $pedidos->pluck('id_pedido')->toArray();

            $marcaciones_solidos = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->join('detallepedido_datoexportacion as dm', 'dm.id_detalle_pedido', '=', 'dp.id_detalle_pedido')
                ->join('dato_exportacion as de', 'de.id_dato_exportacion', '=', 'dm.id_dato_exportacion')
                ->select(
                    'dm.valor as marcacion',
                    'dm.id_dato_exportacion',
                    'de.nombre',
                )->distinct()
                ->whereIn('p.id_pedido', $ids_pedidos)
                ->where('p.estado', 1)
                ->where('v.assorted', 0)
                ->where('dee.id_variedad', $request->variedad)
                ->where('dm.id_dato_exportacion', 1)
                ->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin)
                ->where('dee.id_empaque_p', $request->id_empaque_p)
                ->where('dee.tallos_x_ramos', $request->tallos_x_ramo)
                ->where('dee.longitud_ramo', $request->longitud_ramo)
                ->get();
            $marcaciones_mixtos = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->join('detallepedido_datoexportacion as dm', 'dm.id_detalle_pedido', '=', 'dp.id_detalle_pedido')
                ->join('dato_exportacion as de', 'de.id_dato_exportacion', '=', 'dm.id_dato_exportacion')
                ->join('distribucion_mixtos as dist', 'dist.id_detalle_especificacionempaque', '=', 'dee.id_detalle_especificacionempaque')
                ->select(
                    'dm.valor as marcacion',
                    'dm.id_dato_exportacion',
                    'de.nombre',
                )->distinct()
                ->whereIn('p.id_pedido', $ids_pedidos)
                ->where('p.estado', 1)
                ->where('v.assorted', 1)
                ->where('v.id_planta', $request->planta)
                ->where('dist.siglas', $variedad->siglas)
                ->where('dm.id_dato_exportacion', 1)
                ->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin)
                ->where('dee.id_empaque_p', $request->id_empaque_p)
                ->where('dee.tallos_x_ramos', $request->tallos_x_ramo)
                ->where('dee.longitud_ramo', $request->longitud_ramo)
                ->get();
            $marcaciones = $marcaciones_solidos->merge($marcaciones_mixtos)->sortBy('nombre');

            foreach ($marcaciones as $pos_m => $mar) {
                $query = DB::table('pedido as p')
                    ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                    ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                    ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                    ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                    ->join('detallepedido_datoexportacion as dm', 'dm.id_detalle_pedido', '=', 'dp.id_detalle_pedido')
                    //->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                    ->select(
                        'dp.id_detalle_pedido',
                        'dp.cantidad as piezas',
                        'dee.id_detalle_especificacionempaque',
                        'dee.tallos_x_ramos',
                        DB::raw('sum(dee.cantidad * dp.cantidad) as ramos')
                    )
                    ->whereIn('p.id_pedido', $ids_pedidos)
                    ->where('p.estado', '=', 1)
                    ->where('p.fecha_pedido', '>=', $fecha_min)
                    ->where('p.fecha_pedido', '<=', $fecha_fin)
                    ->where('dee.id_variedad', '=', $variedad->id_variedad)
                    ->where('dm.id_dato_exportacion', '=', $mar->id_dato_exportacion)
                    ->where('dm.valor', '=', $mar->marcacion)
                    ->where('dee.id_empaque_p', '=', $request->id_empaque_p);
                if ($request->tallos_x_ramo != '')
                    $query = $query->where('dee.tallos_x_ramos', '=', $request->tallos_x_ramo);
                if ($request->longitud_ramo != '')
                    $query = $query->where('dee.longitud_ramo', '=', $request->longitud_ramo);
                $query = $query->groupBy(
                    'dp.id_detalle_pedido',
                    'dp.cantidad',
                    'dee.id_detalle_especificacionempaque',
                    'dee.tallos_x_ramos',
                )
                    ->get();
                $pedido = 0;
                foreach ($query as $q) {
                    $getRamosXCajaModificado = getRamosXCajaModificado($q->id_detalle_pedido, $q->id_detalle_especificacionempaque);
                    $pedido += isset($getRamosXCajaModificado) ? ($getRamosXCajaModificado->cantidad * $q->piezas) : $q->ramos;
                }

                $pedido += getCantidadRamosPedidosDistribucionForCBByMarcacionRangoFecha(
                    $fecha_min,
                    $fecha_fin,
                    $variedad->id_variedad,
                    $request->id_empaque_p,
                    $request->tallos_x_ramo,
                    $request->longitud_ramo,
                    $mar->marcacion,
                    $mar->id_dato_exportacion
                );

                $armados = getInventarioFrioByMarcacion($variedad->id_variedad, $mar->id_dato_exportacion, $mar->marcacion, $request->id_empaque_p, $request->tallos_x_ramo, $request->longitud_ramo);
                if ($pedido > 0) {
                    $listado_marcaciones[] = [
                        'marcacion' => $mar,
                        'pedidos' => $pedido,
                        'armados' => $armados,
                    ];
                    $array_marcaciones[] = $mar->marcacion;
                }
            }
        }
        return view('adminlte.gestion.postcocecha.clasificacion_blanco.forms.modal_inventario_row', [
            'listado' => $listado,
            'listado_marcaciones' => $listado_marcaciones,
            'array_marcaciones' => $array_marcaciones,
            'variedad' => Variedad::find($request->variedad),
            'tallos_x_ramo' => $request->tallos_x_ramo,
            'longitud_ramo' => $request->longitud_ramo,
            'presentacion' => Empaque::find($request->id_empaque_p),
        ]);
    }

    public function update_marcacion_inventario(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = InventarioFrio::find($request->id);
            $model->valor_marcacion = $request->valor;
            $model->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'mensaje' => $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine()
            ];
        }
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>MODIFCADO</strong> la marcacion correctamente'
        ];
    }

    public function modal_inventario_color(Request $request)
    {
        $variedad = Variedad::find($request->id_var);
        $planta = $variedad->planta;
        $inventarios = DB::table('inventario_frio as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('empaque as p', 'p.id_empaque', '=', 'i.id_empaque_p')
            ->select(
                'i.id_variedad',
                'v.nombre as var_nombre',
                'i.id_empaque_p',
                'p.nombre as presentacion',
                'i.tallos_x_ramo',
                'i.longitud_ramo',
                'i.fecha_ingreso',
                DB::raw('sum(i.disponibles) as cantidad')
            )
            ->where('i.id_variedad', $request->id_var)
            ->where('i.disponibles', '>', 0)
            ->where('i.disponibilidad', 1)
            ->where('i.basura', 0)
            ->groupBy(
                'i.id_variedad',
                'v.nombre',
                'i.id_empaque_p',
                'p.nombre',
                'i.tallos_x_ramo',
                'i.longitud_ramo',
                'i.fecha_ingreso',
            )
            ->orderBy('i.fecha_ingreso')
            ->get();

        $fecha_min = DB::table('pedido_confirmacion')
            ->select(DB::raw('min(fecha) as fecha'))
            ->where('id_planta', $variedad->id_planta)
            ->where('ejecutado', 0)
            ->get()[0]->fecha;
        $listado = [];
        if ($fecha_min) {
            $fecha_fin = opDiasFecha('+', ($request->dias - 1), $fecha_min);

            $pedidos = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->select('p.id_pedido', 'p.fecha_pedido')->distinct()
                ->where('p.estado', '=', 1)
                ->where('v.id_planta', '=', $variedad->id_planta)
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
                ->join('unidad_medida as um_l', 'um_l.id_unidad_medida', '=', 'dee.id_unidad_medida')
                ->join('empaque as emp', 'emp.id_empaque', '=', 'dee.id_empaque_p')
                ->select(
                    'dee.id_variedad',
                    'v.id_planta',
                    'v.nombre as var_nombre',
                    'v.siglas as var_siglas',
                    'dee.tallos_x_ramos',
                    'dee.longitud_ramo',
                    'dee.id_unidad_medida',
                    'um_l.siglas as siglas_longitud',
                    'dee.id_empaque_p',
                    'emp.nombre as empaque_p',
                )->distinct()
                ->whereIn('p.id_pedido', $ids_pedidos)
                ->where('p.estado', 1)
                ->where('dee.id_variedad', $request->id_var)
                ->where('v.assorted', 0)
                ->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin)
                ->orderBy('v.orden')
                ->get();

            $combinaciones_mixtos = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->join('unidad_medida as um_l', 'um_l.id_unidad_medida', '=', 'dee.id_unidad_medida')
                ->join('empaque as emp', 'emp.id_empaque', '=', 'dee.id_empaque_p')
                ->select(
                    'dee.id_variedad',
                    'v.id_planta',
                    'v.nombre as var_nombre',
                    'v.siglas as var_siglas',
                    'dee.tallos_x_ramos',
                    'dee.longitud_ramo',
                    'dee.id_unidad_medida',
                    'um_l.siglas as siglas_longitud',
                    'dee.id_empaque_p',
                    'emp.nombre as empaque_p',
                )->distinct()
                ->whereIn('p.id_pedido', $ids_pedidos)
                ->where('p.estado', 1)
                ->where('v.assorted', 1)
                ->where('p.fecha_pedido', '>=', $fecha_min)
                ->where('p.fecha_pedido', '<=', $fecha_fin)
                ->where('v.id_planta', '=', $variedad->id_planta)
                ->orderBy('v.orden')
                ->get();

            $listado_mixtos = [];
            foreach ($combinaciones_mixtos as $pos => $item) {
                $query = DB::table('distribucion_mixtos as m')
                    ->join('detalle_especificacionempaque as dee', 'dee.id_detalle_especificacionempaque', '=', 'm.id_detalle_especificacionempaque')
                    ->select('m.id_planta', 'm.siglas')->distinct()
                    ->where('m.id_planta', $item->id_planta)
                    ->where('m.siglas', $variedad->siglas)
                    ->where('m.ramos', '>', 0)
                    ->where('dee.tallos_x_ramos', $item->tallos_x_ramos)
                    ->where('dee.longitud_ramo', $item->longitud_ramo)
                    ->where('dee.id_unidad_medida', $item->id_unidad_medida)
                    ->where('dee.id_empaque_p', $item->id_empaque_p)
                    ->where('m.fecha', '>=', opDiasFecha('-', 1, $array_fechas[0]))
                    ->where('m.fecha', '<=', opDiasFecha('-', 1, $array_fechas[count($array_fechas) - 1]))
                    ->get();
                foreach ($query as $q) {
                    $valores = [];
                    foreach ($array_fechas as $fecha) {
                        $ramos = DB::table('distribucion_mixtos as m')
                            ->join('detalle_especificacionempaque as dee', 'dee.id_detalle_especificacionempaque', '=', 'm.id_detalle_especificacionempaque')
                            ->select(DB::raw('sum(m.ramos * m.piezas) as ramos'))
                            ->where('m.id_planta', $q->id_planta)
                            ->where('m.siglas', $q->siglas)
                            ->where('dee.tallos_x_ramos', $item->tallos_x_ramos)
                            ->where('dee.longitud_ramo', $item->longitud_ramo)
                            ->where('dee.id_unidad_medida', $item->id_unidad_medida)
                            ->where('dee.id_empaque_p', $item->id_empaque_p)
                            ->where('m.fecha', opDiasFecha('-', 1, $fecha))
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

            foreach ($combinaciones as $pos_i => $item) {
                $existe = -1;
                foreach ($listado_mixtos as $pos_m => $m) {
                    if (
                        $item->id_variedad == $m['variedad']->id_variedad &&
                        $item->id_empaque_p == $m['combinacion']->id_empaque_p &&
                        $item->tallos_x_ramos == $m['combinacion']->tallos_x_ramos &&
                        $item->longitud_ramo == $m['combinacion']->longitud_ramo &&
                        $item->id_unidad_medida == $m['combinacion']->id_unidad_medida
                    ) {
                        $existe = $pos_m;
                    }
                    if ($existe != -1) {
                        break;
                    }
                }
                $model_var = $variedad;
                array_push($listado, [
                    'id_variedad' => $item->id_variedad,
                    'orden' => $model_var->orden,
                    'receta' => $model_var->receta,
                    'id_planta' => $item->id_planta,
                    'var_nombre' => $item->var_nombre,
                    'tallos_x_ramos' => $item->tallos_x_ramos,
                    'longitud_ramo' => $item->longitud_ramo,
                    'id_unidad_medida' => $item->id_unidad_medida,
                    'siglas_longitud' => $item->siglas_longitud,
                    'id_empaque_p' => $item->id_empaque_p,
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
                        $item->id_empaque_p == $m['combinacion']->id_empaque_p &&
                        $item->tallos_x_ramos == $m['combinacion']->tallos_x_ramos &&
                        $item->longitud_ramo == $m['combinacion']->longitud_ramo &&
                        $item->id_unidad_medida == $m['combinacion']->id_unidad_medida
                    ) {
                        $existe = true;
                    }
                    if ($existe)
                        break;
                }
                if (!$existe) {
                    $model_var = $variedad;
                    array_push($listado, [
                        'id_variedad' => $m['variedad']->id_variedad,
                        'orden' => $model_var->orden,
                        'receta' => $model_var->receta,
                        'id_planta' => $m['variedad']->id_planta,
                        'var_nombre' => $m['variedad']->nombre,
                        'tallos_x_ramos' => $m['combinacion']->tallos_x_ramos,
                        'longitud_ramo' => $m['combinacion']->longitud_ramo,
                        'id_unidad_medida' => $m['combinacion']->id_unidad_medida,
                        'siglas_longitud' => $m['combinacion']->siglas_longitud,
                        'id_empaque_p' => $m['combinacion']->id_empaque_p,
                        'empaque_p' => $m['combinacion']->empaque_p,
                        'valores' => $m['valores'],
                        'tipo' => 'M',
                    ]);
                }
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
            $all_filtros = $request->variedad == '' && $request->presentacion == '' && $request->tallos == '' && $request->longitud == '';
        }
        return view('adminlte.gestion.postcocecha.clasificacion_blanco.partials.modal_inventario_color', [
            'inventarios' => $inventarios,
            /* ------------------------------------------------ */
            'fecha_fin' => $fecha_fin,
            'fechas' => $fechas,
            'planta' => $planta,
            'variedad' => $variedad,
            'presentacion' => $request->presentacion,
            'tallos' => $request->tallos,
            'longitud' => $request->longitud,
            'listado' => $listado,
            'ids_pedidos' => $ids_pedidos,
            'all_filtros' => $all_filtros,
        ]);
    }

    public function store_cambiar_presentacion(Request $request)
    {
        if (
            $request->inventario['empaque'] != $request->cambio['empaque'] ||
            $request->inventario['tallos_x_ramo'] != $request->cambio['tallos_x_ramo'] ||
            $request->inventario['longitud_ramo'] != $request->cambio['longitud_ramo']
        ) {
            DB::beginTransaction();
            try {
                /* QUITAR DEL INVENTARIO */
                $models = InventarioFrio::where('disponibilidad', 1)
                    ->where('estado', 1)
                    ->where('basura', 0)
                    ->where('disponibles', '>', 0)
                    ->where('id_variedad', $request->variedad)
                    ->where('fecha_ingreso', $request->inventario['fecha_ingreso'])
                    ->where('tallos_x_ramo', $request->inventario['tallos_x_ramo'])
                    ->where('longitud_ramo', $request->inventario['longitud_ramo'])
                    ->where('id_empaque_p', $request->inventario['empaque'])
                    //->where('id_unidad_medida', $request->id_unidad_medida)
                    ->get();

                $meta = $request->inventario['cantidad'];
                foreach ($models as $pos => $model) {
                    if ($meta > 0) {
                        if ($model->disponibles >= $meta) {
                            $model->disponibles = $model->disponibles - $meta;
                            $meta = 0;
                        } else {
                            $meta -= $model->disponibles;
                            $model->disponibles = 0;
                        }

                        if ($model->disponibles == 0)
                            $model->disponibilidad = 0;

                        $model->save();
                        $id = $model->id_inventario_frio;
                        bitacora('inventario_frio', $id, 'U', 'CAMBIAR PRESENTACION');
                    }
                }

                /* AGREGAR INVENTARIO */
                $blanco = ClasificacionBlanco::where('fecha_ingreso', $request->inventario['fecha_ingreso'])
                    ->get()
                    ->first();
                $inventario = new InventarioFrio();
                $inventario->id_clasificacion_blanco = $blanco->id_clasificacion_blanco;
                $inventario->id_empaque_p = $request->cambio['empaque'];
                $inventario->tallos_x_ramo = $request->cambio['tallos_x_ramo'];
                $inventario->longitud_ramo = $request->cambio['longitud_ramo'];
                $inventario->id_unidad_medida = 1;
                $inventario->fecha_ingreso = $request->inventario['fecha_ingreso'];
                $inventario->cantidad = $request->inventario['cantidad'];
                $inventario->id_variedad = $request->variedad;
                $inventario->disponibles = $request->inventario['cantidad'];
                $inventario->descripcion = '';
                $inventario->save();
                $id = DB::table('inventario_frio')
                    ->select(DB::raw('max(id_inventario_frio) as id'))
                    ->get()[0]->id;
                bitacora('inventario_frio', $id, 'I', 'CAMBIAR PRESENTACION');

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return [
                    'success' => false,
                    'mensaje' => $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine()
                ];
            }
            return [
                'success' => true,
                'mensaje' => 'Se ha <strong>CAMBIADO</strong> la presentacion correctamente'
            ];
        } else {
            return [
                'success' => false,
                'mensaje' => '<div class="alert alert-warning text-center"><h4>Debe seleccionar una presentación diferente</h4></div>'
            ];
        }
    }
}
