<?php

namespace yura\Http\Controllers\ProyNintanga;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use yura\Http\Controllers\Controller;
use yura\Modelos\BackupMixtosDiaria;
use yura\Modelos\DetallePedido;
use yura\Modelos\DistribucionMixtos;
use yura\Modelos\DistribucionMixtosDiaria;
use yura\Modelos\DistribucionMixtosSemana;
use yura\Modelos\DistribucionVariedad;
use yura\Modelos\Pedido;
use yura\Modelos\PedidoModificacion;
use yura\Modelos\Planta;
use yura\Modelos\ProyCortes;
use yura\Modelos\ProyLongitudes;
use yura\Modelos\ProyVariedadCortes;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use yura\Jobs\jobCosechaEstimada;

class DistribucionCosechaController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = Planta::where('estado', 1)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.proyeccion_nintanga.distribucion_cosecha.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $ini_timer = date('Y-m-d H:i:s');
        $variedades = Variedad::where('id_planta', $request->planta)
            ->where('assorted', 0)
            ->where('estado', 1)
            ->orderBy('orden')
            ->get();

        $longitud = ProyLongitudes::find($request->longitud);
        $query_cortes = DB::table('proy_cortes')
            ->where('id_planta', $request->planta)
            ->where('nombre', 'like', '%' . $longitud->nombre . '%')
            ->orderBy('nombre', 'asc')
            ->get();
        $cortes = $query_cortes;
        if (count($cortes) == 0)
            $cortes = DB::table('proy_cortes')
                ->where('id_planta', $request->planta)
                ->orderBy('nombre', 'asc')
                ->get();

        /*$query_longitudes = DB::table('pedido as p')
            ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
            ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->select(
                'dee.longitud_ramo'
            )->distinct()
            ->where('p.estado', 1)
            ->where('p.fecha_pedido', opDiasFecha('+', 1, $request->fecha))
            ->where('v.assorted', 1)
            ->where('v.id_planta', $request->planta)
            ->where('dee.longitud_ramo', $longitud->nombre)
            ->whereNotNull('dee.tallos_x_ramos')
            ->get();*/

        $pedidos_mixtos = DB::table('pedido as p')
            ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
            ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
            ->join('detalle_cliente as cli', 'cli.id_cliente', '=', 'p.id_cliente')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->select(
                'p.id_cliente',
                'cli.nombre',
                'dee.longitud_ramo',
                /*'dp.id_detalle_pedido',
                'dee.id_detalle_especificacionempaque',*/
                DB::raw('sum(dee.cantidad * dee.tallos_x_ramos * dp.cantidad) as cantidad')
            )->distinct()
            ->where('p.estado', 1)
            ->where('cli.estado', 1)
            ->where('p.fecha_pedido', opDiasFecha('+', 1, $request->fecha))
            ->where('v.assorted', 1)
            ->where('v.id_planta', $request->planta)
            ->where('dee.longitud_ramo', $longitud->nombre)
            ->whereNotNull('dee.tallos_x_ramos')
            ->groupBy(
                'p.id_cliente',
                'cli.nombre',
                'dee.longitud_ramo',
                /*'dp.id_detalle_pedido',
                'dee.id_detalle_especificacionempaque',*/
            )
            ->get();

        if (date('N', strtotime($request->fecha)) < 7)
            $semana = getSemanaByDate($request->fecha);
        else
            $semana = getSemanaByDate(opDiasFecha('+', 7, $request->fecha));

        $mixtos_semana = DB::table('pedido as p')
            ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
            ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->select(
                DB::raw('sum(dee.cantidad * dee.tallos_x_ramos * dp.cantidad) as cantidad')
            )
            ->where('p.estado', 1)
            ->where('p.fecha_pedido', '>=', $semana->fecha_inicial)
            ->where('p.fecha_pedido', '<=', $semana->fecha_final)
            ->where('v.assorted', 1)
            ->where('v.id_planta', $request->planta)
            ->where('dee.longitud_ramo', $longitud->nombre)
            ->whereNotNull('dee.tallos_x_ramos')
            ->get()[0]->cantidad;
        $listado = [];
        foreach ($variedades as $var) {
            if ($request->planta != 8 || ($request->planta == 8 && $var->tipo == 'P')) {    // caso de la planta STATICE
                $valores = [];
                foreach ($cortes as $c) {
                    $query = DB::table('proy_variedad_cortes')
                        ->where('id_variedad', $var->id_variedad)
                        ->where('id_cortes', $c->id_proy_cortes)
                        ->where('fecha', $request->fecha)
                        ->get()
                        ->first();
                    array_push($valores, $query != '' ? $query->cantidad : '');
                }
                $query_solidos = DB::table('pedido as p')
                    ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                    ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                    ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                    ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                    ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                    ->select(
                        'dp.id_detalle_pedido',
                        'dee.id_detalle_especificacionempaque',
                        'dee.cantidad as ramos_x_caja',
                        'dee.tallos_x_ramos',
                        'dp.cantidad',
                    )->distinct()
                    ->where('p.estado', 1)
                    ->where('p.fecha_pedido', opDiasFecha('+', 1, $request->fecha))
                    ->where('v.siglas', '=', $var->siglas)
                    ->where('v.id_planta', $request->planta)
                    //->where('v.id_variedad', $var->id_variedad)
                    ->where('dee.longitud_ramo', $longitud->nombre)
                    ->whereNotNull('dee.tallos_x_ramos')
                    ->get();
                $pedidos_solidos = 0;
                foreach ($query_solidos as $q) {
                    $ramos_modificado = getRamosXCajaModificado($q->id_detalle_pedido, $q->id_detalle_especificacionempaque);
                    $ramos_x_caja = (isset($ramos_modificado) ? $ramos_modificado->cantidad : $q->ramos_x_caja);
                    $pedidos_solidos += $q->cantidad * $q->tallos_x_ramos * $ramos_x_caja;
                }

                $mixtos = DB::table('distribucion_mixtos')
                    ->select(
                        DB::raw('sum(tallos) as cantidad')
                    )
                    ->where('fecha', $request->fecha)
                    ->where('siglas', $var->siglas)
                    ->where('id_planta', $request->planta)
                    ->where('longitud_ramo', $longitud->nombre)
                    ->get()[0]->cantidad;
                $dist_semana = DB::table('distribucion_mixtos_semana')
                    ->select(DB::raw('sum(cantidad) as cantidad'))
                    ->where('semana', $semana->codigo)
                    ->where('id_planta', $request->planta)
                    ->where('siglas', $var->siglas)
                    ->where('longitud', $longitud->nombre)
                    ->get()[0]->cantidad;
                $dist_diaria = DB::table('distribucion_mixtos_diaria')
                    ->where('fecha', $request->fecha)
                    ->where('id_planta', $request->planta)
                    ->where('siglas', $var->siglas)
                    ->where('longitud', $longitud->nombre)
                    ->get()
                    ->first();
                $cuarto_frio = DB::table('inventario_frio')
                    ->select(DB::raw('sum(disponibles * tallos_x_ramo) as cantidad'))
                    ->where('id_variedad', $var->id_variedad)
                    ->where('longitud_ramo', $longitud->nombre)
                    ->where('estado', 1)
                    ->where('disponibilidad', 1)
                    ->where('basura', 0)
                    ->get()[0]->cantidad;
                array_push($listado, [
                    'var' => $var,
                    'pedidos_solidos' => $pedidos_solidos,
                    'pedidos_mixtos' => $mixtos,
                    'valores' => $valores,
                    'dist_semana' => $dist_semana != '' ? $dist_semana : 0,
                    'dist_diaria' => $dist_diaria,
                    'cuarto_frio' => $cuarto_frio,
                ]);
            }
        }

        $ramos_mixtos = 0;
        $listado_clientes = [];
        foreach ($pedidos_mixtos as $p) {
            $distribuido = getDistribucionByClienteLongitudFechaPlanta($p->id_cliente, $p->longitud_ramo, $request->fecha, $request->planta)->tallos;
            $mixtos_x_cliente = getMixtosByClienteLongitudFechaPlanta($p->id_cliente, $p->longitud_ramo, opDiasFecha('+', 1, $request->fecha), $request->planta)['tallos'];
            $listado_clientes[] = [
                'id_cliente' => $p->id_cliente,
                'nombre' => $p->nombre,
                'longitud_ramo' => $p->longitud_ramo,
                'mixtos_x_cliente' => $mixtos_x_cliente,
                'distribuido' => $distribuido,
            ];
            $ramos_mixtos += $mixtos_x_cliente;
        }
        $fin_timer = date('Y-m-d H:i:s');
        //dd('ok', difFechas($fin_timer, $ini_timer));

        return view('adminlte.gestion.proyeccion_nintanga.distribucion_cosecha.partials.listado', [
            'mixtos_semana' => $mixtos_semana != '' ? $mixtos_semana : 0,
            'listado' => $listado,
            'variedades' => $variedades,
            'cortes' => $cortes,
            'ramos_mixtos' => $ramos_mixtos != '' ? $ramos_mixtos : 0,
            'listado_clientes' => $listado_clientes,
            'fecha' => $request->fecha,
            'planta' => $request->planta,
            'longitud' => $longitud->nombre,
        ]);
    }

    public function get_distribuciones_pendientes(Request $request)
    {
        $fecha = hoy();
        $plantas = DB::table('proyecto as p')
            ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
            ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
            ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
            ->join('planta as pta', 'pta.id_planta', '=', 'v.id_planta')
            ->select(
                'v.id_planta',
                'pta.nombre',
            )->distinct()
            ->where('v.assorted', '=', 1)
            ->where('p.fecha', $fecha)
            ->orderBy('pta.orden')
            ->orderBy('v.orden')
            ->get();
        $listado = [];
        foreach ($plantas as $pta) {
            $pedidos_mixtos = DB::table('proyecto as p')
                ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                ->join('detalle_cliente as cli', 'cli.id_cliente', '=', 'p.id_cliente')
                ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
                ->select(
                    'p.id_cliente',
                    'cli.nombre',
                    'dc.longitud_ramo',
                )->distinct()
                ->where('cli.estado', 1)
                ->where('p.fecha', $fecha)
                ->where('v.id_planta', $pta->id_planta)
                ->orderBy('cli.nombre')
                ->orderBy('dc.longitud_ramo')
                ->get();

            $valores = [];
            foreach ($pedidos_mixtos as $p) {
                $mixtos_x_cliente = getFlorMixtosByClienteLongitudFechaPlanta($p->id_cliente, $p->longitud_ramo, $fecha, $pta->id_planta)['tallos'];
                $distribuidos = getFlorDistribuidaByClienteLongitudFechaPlanta($p->id_cliente, $p->longitud_ramo, $fecha, $pta->id_planta)->tallos;
                if ($distribuidos != $mixtos_x_cliente)
                    $valores[] = [
                        'id_cliente' => $p->id_cliente,
                        'nombre' => $p->nombre,
                        'longitud_ramo' => $p->longitud_ramo,
                        'mixtos_x_cliente' => $mixtos_x_cliente,
                        'distribuido' => $distribuidos,
                    ];
            }
            if (count($valores) > 0)
                $listado[] = [
                    'planta' => $pta,
                    'valores' => $valores,
                ];
        }
        return view('adminlte.gestion.proyeccion_nintanga.distribucion_cosecha.partials.get_distribuciones_pendientes', [
            'listado' => $listado,
            'fecha' => opDiasFecha('-', 1, $fecha),
        ]);
    }

    public function distribuir_mixtos(Request $request)
    {
        $ini_timer = date('Y-m-d H:i:s');
        $planta = Planta::find($request->planta);
        $query_mixtos = DB::table('pedido as p')
            ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
            ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
            ->join('detalle_cliente as cli', 'cli.id_cliente', '=', 'p.id_cliente')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->join('unidad_medida as um', 'um.id_unidad_medida', '=', 'dee.id_unidad_medida')
            ->select(
                'dee.id_detalle_especificacionempaque',
                'p.id_pedido',
                'dp.id_detalle_pedido',
                'p.id_cliente',
                'cli.nombre',
                'dee.longitud_ramo',
                'dee.id_unidad_medida',
                'um.siglas',
                'dee.cantidad as ramos_x_caja',
                'dee.tallos_x_ramos as tallos_x_ramos',
                DB::raw('sum(dee.cantidad * dee.tallos_x_ramos * dp.cantidad) as cantidad'),
                DB::raw('sum(dp.cantidad) as piezas')
            )
            ->where('p.estado', 1)
            ->where('cli.estado', 1)
            ->where('p.id_cliente', $request->cliente)
            ->where('dee.longitud_ramo', $request->longitud)
            ->where('p.fecha_pedido', opDiasFecha('+', 1, $request->fecha))
            ->where('v.assorted', 1)
            ->where('v.id_planta', $request->planta)
            ->whereNotNull('dee.tallos_x_ramos')
            ->groupBy(
                'dee.id_detalle_especificacionempaque',
                'p.id_pedido',
                'dp.id_detalle_pedido',
                'p.id_cliente',
                'cli.nombre',
                'dee.longitud_ramo',
                'dee.id_unidad_medida',
                'um.siglas',
                'dee.cantidad',
                'dee.tallos_x_ramos',
            )
            ->orderBy('cli.nombre')
            ->orderBy('dee.longitud_ramo', 'desc')
            ->get();
        $pagina = $request->pagina != '' ? $request->pagina : 1;
        $pos_desde = explode('-', getPaginas()[$pagina - 1])[0];
        $pos_hasta = explode('-', getPaginas()[$pagina - 1])[1];
        $cantidad_pedidos = count($query_mixtos);
        //dd($request->pagina, $pos_desde, $pos_hasta, $cantidad_pedidos, $cantidad_paginas);

        $array_valores_x_caja = [];
        $pedidos_mixtos = [];
        foreach ($query_mixtos as $pos => $p) {
            if ($pos >= $pos_desde && $pos <= $pos_hasta) {
                $getRamosXCajaModificado = getRamosXCajaModificado($p->id_detalle_pedido, $p->id_detalle_especificacionempaque);
                $array_valores_x_caja[] = isset($getRamosXCajaModificado) ? $getRamosXCajaModificado->cantidad : $p->ramos_x_caja;
                $pedidos_mixtos[] = $p;
            }
        }
        //dd($pedidos_mixtos, $array_valores_x_caja, $query_mixtos);
        $tiene_backups = false;
        $listado = [];
        foreach (json_decode($request->data) as $d) {
            $var = DB::table('variedad')
                ->where('assorted', 0)
                ->where('estado', 1)
                ->where('id_planta', $request->planta)
                ->where('siglas', $d->var)
                ->get()
                ->first();
            $valores = [];
            $backups = [];
            foreach ($pedidos_mixtos as $pos => $p) {
                $model = DB::table('distribucion_mixtos')
                    ->where('id_planta', $request->planta)
                    ->where('siglas', $d->var)
                    ->where('id_detalle_especificacionempaque', $p->id_detalle_especificacionempaque)
                    ->where('id_pedido', $p->id_pedido)
                    ->where('id_detalle_pedido', $p->id_detalle_pedido)
                    ->where('longitud_ramo', $p->longitud_ramo)
                    ->where('id_unidad_medida', $p->id_unidad_medida)
                    ->where('id_cliente', $p->id_cliente)
                    ->where('ramos_x_caja', $array_valores_x_caja[$pos])
                    //->where('piezas', $p->piezas)
                    ->where('fecha', $request->fecha)
                    ->get()
                    ->first();
                array_push($valores, [
                    'model' => $model,
                ]);
                $model = DB::table('backup_mixtos_diaria')
                    ->where('id_planta', $request->planta)
                    ->where('siglas', $d->var)
                    ->where('longitud_ramo', $p->longitud_ramo)
                    ->where('id_unidad_medida', $p->id_unidad_medida)
                    ->where('id_cliente', $p->id_cliente)
                    ->where('ramos_x_caja', $array_valores_x_caja[$pos])
                    ->where('fecha', $request->fecha)
                    ->get()
                    ->first();
                array_push($backups, [
                    'model' => $model,
                ]);
                if ($model != '') {
                    $tiene_backups = true;
                }
            }
            array_push($listado, [
                'saldo' => round($d->saldo),
                'consolidado' => $d->consolidado,
                'porcentaje' => $d->porcentaje,
                'var' => $var,
                'valores' => $valores,
                'backups' => $backups,
            ]);
        }
        $fin_timer = date('Y-m-d H:i:s');
        //dd('ok', difFechas($fin_timer, $ini_timer));
        return view('adminlte.gestion.proyeccion_nintanga.distribucion_cosecha.partials._distribuir_mixtos', [
            'pagina' => $pagina,
            'cantidad_pedidos' => $cantidad_pedidos,
            'pedidos_mixtos' => $pedidos_mixtos,
            'array_valores_x_caja' => $array_valores_x_caja,
            //'ramos_mixtos' => $request->tallos_pedidos_mixtos,
            'fecha' => $request->fecha,
            'listado' => $listado,
            'planta' => $planta,
            'tiene_backups' => $tiene_backups,
            'cliente' => $request->cliente,
            'longitud' => $request->longitud,
        ]);
    }

    public function store_distribucion(Request $request)
    {
        DB::beginTransaction();

        try {
            $resumen_variedades = [];
            $ids_det_esp = [];
            $ids_dist = [];
            foreach (json_decode($request->data) as $pos => $d) {
                $del = DistribucionMixtos::where('id_planta', $request->planta)
                    ->where('siglas', $d->var)
                    ->where('id_cliente', $request->cliente)
                    ->where('longitud_ramo', $request->longitud)
                    //->where('fecha', $request->fecha)
                    ->where('id_detalle_pedido', $d->guia_id_detalle_pedido)
                    ->where('id_detalle_especificacionempaque', $d->guia_id_detalle_especificacionempaque);
                foreach ($del->get() as $item) {
                    if (!in_array($d->guia_id_detalle_especificacionempaque, $ids_det_esp)) {
                        $ids_det_esp[] = $d->guia_id_detalle_especificacionempaque;
                    }
                    $variedad_model = $item->variedad();
                    if (
                        !in_array([
                            'variedad' => $variedad_model->id_variedad,
                            'longitud' => $request->longitud,
                            'fecha' => $request->fecha
                        ], $resumen_variedades)
                    ) {
                        $resumen_variedades[] = [
                            'variedad' => $variedad_model->id_variedad,
                            'longitud' => $request->longitud,
                            'fecha' => $request->fecha
                        ];
                    }
                }
                $del->delete();

                $model = new DistribucionMixtos();
                $model->id_planta = $request->planta;
                $model->fecha = $request->fecha;
                $model->siglas = $d->var;
                $model->ramos = $d->ramos;
                $model->porcentaje = $d->porcentaje;
                $model->tallos = $d->tallos;
                $model->id_unidad_medida = $d->guia_id_unidad_medida;
                $model->id_detalle_especificacionempaque = $d->guia_id_detalle_especificacionempaque;
                $model->id_pedido = $d->guia_id_pedido;
                $model->id_cliente = $d->guia_id_cliente;
                $model->id_detalle_pedido = $d->guia_id_detalle_pedido;
                $model->longitud_ramo = $d->guia_longitud_ramo;
                $model->ramos_x_caja = $d->guia_ramos_x_caja;
                $model->piezas = $d->guia_piezas;
                $model->save();
                $model->id_distribucion_mixtos = DB::table('distribucion_mixtos')
                    ->select(DB::raw('max(id_distribucion_mixtos) as id'))
                    ->get()[0]->id;
                $ids_dist[] = $model->id_distribucion_mixtos;

                $variedad_model = Variedad::where([
                    ['id_planta', $request->planta],
                    ['siglas', $d->var]
                ])->get()
                    ->first();
                if (
                    !in_array([
                        'variedad' => $variedad_model->id_variedad,
                        'longitud' => $request->longitud,
                        'fecha' => $request->fecha
                    ], $resumen_variedades)
                ) {
                    $resumen_variedades[] = [
                        'variedad' => $variedad_model->id_variedad,
                        'longitud' => $request->longitud,
                        'fecha' => $request->fecha
                    ];
                }

                // TABLA BACKUP_MIXTOS_DIARIA
                $backup = BackupMixtosDiaria::where('id_planta', $request->planta)
                    ->where('siglas', $d->var)
                    ->where('fecha', $request->fecha)
                    //->where('ramos', $d->ramos)
                    //->where('porcentaje', $d->porcentaje)
                    //->where('tallos', $d->tallos)
                    ->where('id_cliente', $d->guia_id_cliente)
                    ->where('longitud_ramo', $d->guia_longitud_ramo)
                    ->where('id_unidad_medida', $d->guia_id_unidad_medida)
                    ->where('ramos_x_caja', $d->guia_ramos_x_caja)
                    ->get()
                    ->first();
                if ($backup == '') {
                    $backup = new BackupMixtosDiaria();
                    $backup->id_planta = $request->planta;
                    $backup->siglas = $d->var;
                    $backup->fecha = $request->fecha;
                    $backup->id_cliente = $d->guia_id_cliente;
                    $backup->longitud_ramo = $d->guia_longitud_ramo;
                    $backup->id_unidad_medida = $d->guia_id_unidad_medida;
                    $backup->ramos_x_caja = $d->guia_ramos_x_caja;
                }
                $backup->tallos_x_ramos = $d->guia_tallos_x_ramos;
                $backup->piezas = $d->guia_piezas;
                $backup->ramos = $d->ramos;
                $backup->porcentaje = $d->porcentaje;
                $backup->tallos = $d->tallos;
                $backup->save();

                // TABLA PEDIDO_MODIFICACION
                if ($request->check_guardar_cambios == 'true' && $d->check_guardar_cambios == 'true') {
                    $fecha_pedidos = opDiasFecha('+', 1, $request->fecha);
                    if (hoy() == $fecha_pedidos || hoy() == opDiasFecha('-', 1, $fecha_pedidos)) {
                        if ($d->total_ramos != $d->total_back_ramos) {
                            $pedidoModificacion = new PedidoModificacion();
                            $pedidoModificacion->id_cliente = $d->guia_id_cliente;
                            $pedidoModificacion->id_detalle_especificacionempaque = $d->guia_id_detalle_especificacionempaque;
                            $pedidoModificacion->fecha_nuevo_pedido = $fecha_pedidos;
                            $pedidoModificacion->fecha_anterior_pedido = $fecha_pedidos;
                            $pedidoModificacion->cantidad = null;
                            $pedidoModificacion->operador = $d->total_ramos > $d->total_back_ramos ? '+' : '-';
                            $pedidoModificacion->ramos = abs($d->total_ramos - $d->total_back_ramos);
                            $pedidoModificacion->tallos = $pedidoModificacion->ramos * $d->guia_tallos_x_ramos;
                            $pedidoModificacion->id_planta = $request->planta;
                            $pedidoModificacion->siglas = $d->var;
                            $pedidoModificacion->id_usuario = Session::get('id_usuario');
                            $pedidoModificacion->save();
                        }
                    }
                }
            }

            /*BORRAR las CANCELACIONES*/
            if ($request->cantidad_paginas == 1)
                $delete = DistribucionMixtos::where('id_planta', $request->planta)
                    ->where('id_cliente', $request->cliente)
                    ->where('longitud_ramo', $request->longitud)
                    ->where('fecha', $request->fecha)
                    ->whereNotIn('id_detalle_especificacionempaque', $ids_det_esp)
                    ->whereNotIn('id_distribucion_mixtos', $ids_dist)
                    ->delete();

            foreach ($resumen_variedades as $r) {
                jobCosechaEstimada::dispatch($r['variedad'], $r['longitud'], opDiasFecha('-', 1, $r['fecha']))
                    ->onQueue('cosecha_estimada')
                    ->onConnection('database');
            }

            DB::commit();

            $success = true;
            $msg = 'Se ha <strong>GUARDADO</strong> la distribucion correctamente';
        } catch (\Exception $e) {

            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }
        //dd('TODO OK');

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function store_distribucion_mixtos_diaria(Request $request)
    {
        foreach ($request->data as $d) {
            $model = DistribucionMixtosDiaria::where('fecha', $request->fecha)
                ->where('id_planta', $request->planta)
                ->where('longitud', $request->longitud)
                ->where('siglas', $d['var'])
                ->get()
                ->first();
            if ($model == '') {
                $model = new DistribucionMixtosDiaria();
                $model->id_planta = $request->planta;
                $model->fecha = $request->fecha;
                $model->longitud = $request->longitud;
                $model->siglas = $d['var'];
                $model->cantidad = $d['saldo'];
                $model->save();
            } else {
                $model->cantidad = $d['saldo'];
                $model->save();
            }
        }
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>GUARDADO</strong> la distribucion correctamente',
        ];
    }

    public function duplicar_distribucion(Request $request)
    {
        $fecha = opDiasFecha('+', 1, $request->fecha);
        foreach ($request->data as $d) {
            $variedad = Variedad::where('id_planta', $request->planta)
                ->where('siglas', $d['var'])
                ->get()
                ->first();
            $delete = ProyVariedadCortes::where('id_variedad', $variedad->id_variedad)
                ->where('fecha', $fecha)
                ->delete();

            if (isset($d['valores']))
                foreach ($d['valores'] as $v) {
                    $model = new ProyVariedadCortes();
                    $model->id_variedad = $variedad->id_variedad;
                    $model->id_cortes = $v['corte'];
                    $model->fecha = $fecha;
                    $model->cantidad = $v['valor'];
                    $model->save();
                }
        }
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>GUARDADO</strong> la informacion correctamente',
        ];
    }

    public function eliminar_distribuciones(Request $request)
    {
        DB::beginTransaction();
        try {
            /*BORRAR las CANCELACIONES*/
            $delete = DistribucionMixtos::where('id_planta', $request->planta)
                ->where('id_cliente', $request->cliente)
                ->where('longitud_ramo', $request->longitud)
                ->where('fecha', $request->fecha)
                ->delete();

            $resumen_variedades = Variedad::where('id_planta', $request->planta)
                ->where('estado', 1)
                ->get();
            foreach ($resumen_variedades as $r) {
                jobCosechaEstimada::dispatch($r->id_variedad, $request->longitud, opDiasFecha('-', 1, $request->fecha))
                    ->onQueue('cosecha_estimada')
                    ->onConnection('database');
            }

            DB::commit();

            $success = true;
            $msg = 'Se ha <strong>GUARDADO</strong> la distribucion correctamente';
        } catch (\Exception $e) {

            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }
        //dd('TODO OK');

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }
}
