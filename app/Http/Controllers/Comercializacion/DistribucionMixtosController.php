<?php

namespace yura\Http\Controllers\Comercializacion;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Jobs\jobCosechaEstimada;
use yura\Modelos\BackupMixtos;
use yura\Modelos\CambiosPedido;
use yura\Modelos\DetalleCliente;
use yura\Modelos\DistribucionMixtosDiaria;
use yura\Modelos\Mixtos;
use yura\Modelos\Planta;
use yura\Modelos\ProyLongitudes;
use yura\Modelos\ProyVariedadCortes;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class DistribucionMixtosController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = Planta::where('estado', 1)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.comercializacion.distribucion_mixtos.inicio', [
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

        $pedidos_mixtos = DB::table('proyecto as p')
            ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
            ->join('detalle_cliente as cli', 'cli.id_cliente', '=', 'p.id_cliente')
            ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
            ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
            ->select(
                'p.id_cliente',
                'cli.nombre',
                'dc.longitud_ramo',
                DB::raw('sum(dc.ramos_x_caja * dc.tallos_x_ramo * cp.cantidad) as cantidad')
            )->distinct()
            ->where('p.estado', 1)
            ->where('cli.estado', 1)
            ->where('p.fecha', opDiasFecha('+', 1, $request->fecha))
            ->where('v.assorted', 1)
            ->where('v.id_planta', $request->planta)
            ->where('dc.longitud_ramo', $longitud->nombre)
            ->whereNotNull('dc.tallos_x_ramo')
            ->groupBy(
                'p.id_cliente',
                'cli.nombre',
                'dc.longitud_ramo',
            )
            ->get();

        if (date('N', strtotime($request->fecha)) < 7)
            $semana = getSemanaByDate($request->fecha);
        else
            $semana = getSemanaByDate(opDiasFecha('+', 7, $request->fecha));

        $mixtos_semana = DB::table('proyecto as p')
            ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
            ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
            ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
            ->select(
                DB::raw('sum(dc.ramos_x_caja * dc.tallos_x_ramo * cp.cantidad) as cantidad')
            )
            ->where('p.estado', 1)
            ->where('p.fecha', '>=', $semana->fecha_inicial)
            ->where('p.fecha', '<=', $semana->fecha_final)
            ->where('v.assorted', 1)
            ->where('v.id_planta', $request->planta)
            ->where('dc.longitud_ramo', $longitud->nombre)
            ->whereNotNull('dc.tallos_x_ramo')
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
                $pedidos_solidos = DB::table('proyecto as p')
                    ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                    ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                    ->select(
                        DB::raw('sum(dc.ramos_x_caja * dc.tallos_x_ramo * cp.cantidad) as cantidad')
                    )->distinct()
                    ->where('p.estado', 1)
                    ->where('p.fecha', opDiasFecha('+', 1, $request->fecha))
                    ->where('dc.id_variedad', $var->id_variedad)
                    ->where('dc.longitud_ramo', $longitud->nombre)
                    ->whereNotNull('dc.tallos_x_ramo')
                    ->get()[0]->cantidad;
                $mixtos = DB::table('mixtos')
                    ->select(
                        DB::raw('sum(tallos) as cantidad')
                    )
                    ->where('fecha', $request->fecha)
                    ->where('id_variedad', $var->id_variedad)
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
                $cuarto_frio = DB::table('cuarto_frio')
                    ->select(DB::raw('sum(disponibles * tallos_x_ramo) as cantidad'))
                    ->where('id_variedad', $var->id_variedad)
                    ->where('longitud_ramo', $longitud->nombre)
                    ->where('disponibles', '>', 0)
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
            $distribuido = DB::table('mixtos')
                ->select(
                    DB::raw('sum(tallos) as tallos')
                )
                ->where('id_cliente', $p->id_cliente)
                ->where('id_planta', $request->planta)
                ->where('longitud_ramo', $p->longitud_ramo)
                ->where('fecha', $request->fecha)
                ->get()[0]->tallos;
            $mixtos_x_cliente = DB::table('proyecto as p')
                ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
                ->select(
                    DB::raw('sum(dc.ramos_x_caja * dc.tallos_x_ramo * cp.cantidad) as tallos'),
                    DB::raw('sum(dc.ramos_x_caja * cp.cantidad) as ramos')
                )
                ->where('p.estado', 1)
                ->where('p.id_cliente', $p->id_cliente)
                ->where('p.fecha', opDiasFecha('+', 1, $request->fecha))
                ->where('v.assorted', 1)
                ->where('v.id_planta', $request->planta)
                ->where('dc.longitud_ramo', $p->longitud_ramo)
                ->whereNotNull('dc.tallos_x_ramo')
                ->get()[0];
            $listado_clientes[] = [
                'id_cliente' => $p->id_cliente,
                'nombre' => $p->nombre,
                'longitud_ramo' => $p->longitud_ramo,
                'mixtos_x_cliente' => $mixtos_x_cliente,
                'distribuido' => $distribuido,
            ];
            $ramos_mixtos += $mixtos_x_cliente->tallos;
        }
        $fin_timer = date('Y-m-d H:i:s');

        return view('adminlte.gestion.comercializacion.distribucion_mixtos.partials.listado', [
            'mixtos_semana' => $mixtos_semana != '' ? $mixtos_semana : 0,
            'listado' => $listado,
            'variedades' => $variedades,
            'cortes' => $cortes,
            'ramos_mixtos' => $ramos_mixtos != '' ? $ramos_mixtos : 0,
            'listado_clientes' => $listado_clientes,
            'fecha' => $request->fecha,
            'planta' => Planta::find($request->planta),
            'longitud' => $longitud->nombre,
        ]);
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

    public function distribuir_mixtos(Request $request)
    {
        $ini_timer = date('Y-m-d H:i:s');
        $planta = Planta::find($request->planta);
        $query_mixtos = DB::table('proyecto as p')
            ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
            ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
            ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
            ->select(
                'dc.id_detalle_caja_proyecto',
                'p.id_proyecto',
                'cp.id_caja_proyecto',
                'p.id_cliente',
                'dc.longitud_ramo',
                'dc.ramos_x_caja',
                'dc.tallos_x_ramo',
                DB::raw('sum(dc.ramos_x_caja * dc.tallos_x_ramo * cp.cantidad) as cantidad'),
                DB::raw('sum(cp.cantidad) as piezas')
            )
            ->where('p.estado', 1)
            ->where('p.id_cliente', $request->cliente)
            ->where('dc.longitud_ramo', $request->longitud)
            ->where('p.fecha', opDiasFecha('+', 1, $request->fecha))
            ->where('v.assorted', 1)
            ->where('v.id_planta', $request->planta)
            ->whereNotNull('dc.tallos_x_ramo')
            ->groupBy(
                'dc.id_detalle_caja_proyecto',
                'p.id_proyecto',
                'cp.id_caja_proyecto',
                'p.id_cliente',
                'dc.longitud_ramo',
                'dc.ramos_x_caja',
                'dc.tallos_x_ramo',
            )
            ->orderBy('dc.longitud_ramo', 'desc')
            ->get();
        $pagina = $request->pagina != '' ? $request->pagina : 1;
        $pos_desde = explode('-', getPaginas()[$pagina - 1])[0];
        $pos_hasta = explode('-', getPaginas()[$pagina - 1])[1];
        $cantidad_pedidos = count($query_mixtos);

        $array_valores_x_caja = [];
        $pedidos_mixtos = [];
        foreach ($query_mixtos as $pos => $p) {
            if ($pos >= $pos_desde && $pos <= $pos_hasta) {
                $array_valores_x_caja[] = $p->ramos_x_caja;
                $pedidos_mixtos[] = $p;
            }
        }
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
                $model = DB::table('mixtos')
                    ->where('id_planta', $request->planta)
                    ->where('id_variedad', $var->id_variedad)
                    ->where('id_detalle_caja_proyecto', $p->id_detalle_caja_proyecto)
                    ->where('id_proyecto', $p->id_proyecto)
                    ->where('id_caja_proyecto', $p->id_caja_proyecto)
                    ->where('longitud_ramo', $p->longitud_ramo)
                    ->where('id_cliente', $p->id_cliente)
                    ->where('ramos_x_caja', $p->ramos_x_caja)
                    //->where('piezas', $p->piezas)
                    ->where('fecha', $request->fecha)
                    ->get()
                    ->first();
                array_push($valores, [
                    'model' => $model,
                ]);
                $model = DB::table('backup_mixtos')
                    ->where('id_planta', $request->planta)
                    ->where('id_variedad', $var->id_variedad)
                    ->where('longitud_ramo', $p->longitud_ramo)
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
        $cliente = DetalleCliente::where('id_cliente', $request->cliente)
            ->where('estado', 1)
            ->get()
            ->first();
        $fin_timer = date('Y-m-d H:i:s');
        return view('adminlte.gestion.comercializacion.distribucion_mixtos.partials._distribuir_mixtos', [
            'pagina' => $pagina,
            'cantidad_pedidos' => $cantidad_pedidos,
            'pedidos_mixtos' => $pedidos_mixtos,
            'array_valores_x_caja' => $array_valores_x_caja,
            //'ramos_mixtos' => $request->tallos_pedidos_mixtos,
            'fecha' => $request->fecha,
            'listado' => $listado,
            'planta' => $planta,
            'tiene_backups' => $tiene_backups,
            'cliente' => $cliente,
            'longitud' => $request->longitud,
        ]);
    }

    public function store_distribucion(Request $request)
    {
        DB::beginTransaction();
        try {
            $resumen_variedades = [];
            $ids_det_caj = [];
            $ids_dist = [];
            foreach (json_decode($request->data) as $pos => $d) {
                $variedad_model = Variedad::find($d->var);
                if (!in_array($d->guia_id_detalle_caja_proyecto, $ids_det_caj)) {
                    $ids_det_caj[] = $d->guia_id_detalle_caja_proyecto;
                }
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
                $del = Mixtos::where('id_planta', $request->planta)
                    ->where('id_variedad', $d->var)
                    ->where('id_cliente', $request->cliente)
                    ->where('longitud_ramo', $request->longitud)
                    //->where('fecha', $request->fecha)
                    ->where('id_caja_proyecto', $d->guia_id_caja_proyecto)
                    ->where('id_detalle_caja_proyecto', $d->guia_id_detalle_caja_proyecto)
                    ->delete();

                $model = new Mixtos();
                $model->id_planta = $request->planta;
                $model->fecha = $request->fecha;
                $model->id_variedad = $d->var;
                $model->ramos = $d->ramos;
                $model->porcentaje = $d->porcentaje;
                $model->tallos = $d->tallos;
                $model->id_detalle_caja_proyecto = $d->guia_id_detalle_caja_proyecto;
                $model->id_proyecto = $d->guia_id_proyecto;
                $model->id_cliente = $d->guia_id_cliente;
                $model->id_caja_proyecto = $d->guia_id_caja_proyecto;
                $model->longitud_ramo = $d->guia_longitud_ramo;
                $model->ramos_x_caja = $d->guia_ramos_x_caja;
                $model->piezas = $d->guia_piezas;
                $model->save();
                $model->id_mixtos = DB::table('mixtos')
                    ->select(DB::raw('max(id_mixtos) as id'))
                    ->get()[0]->id;
                $ids_dist[] = $model->id_mixtos;

                // TABLA BACKUP_MIXTOS_DIARIA
                $backup = BackupMixtos::where('id_planta', $request->planta)
                    ->where('id_variedad', $d->var)
                    ->where('fecha', $request->fecha)
                    //->where('ramos', $d->ramos)
                    //->where('porcentaje', $d->porcentaje)
                    //->where('tallos', $d->tallos)
                    ->where('id_cliente', $d->guia_id_cliente)
                    ->where('longitud_ramo', $d->guia_longitud_ramo)
                    ->where('ramos_x_caja', $d->guia_ramos_x_caja)
                    ->get()
                    ->first();
                if ($backup == '') {
                    $backup = new BackupMixtos();
                    $backup->id_planta = $request->planta;
                    $backup->id_variedad = $d->var;
                    $backup->fecha = $request->fecha;
                    $backup->id_cliente = $d->guia_id_cliente;
                    $backup->longitud_ramo = $d->guia_longitud_ramo;
                    $backup->ramos_x_caja = $d->guia_ramos_x_caja;
                }
                $backup->tallos_x_ramo = $d->guia_tallos_x_ramo;
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
                            $empaque_p = $model->detalle_caja_proyecto->id_empaque;
                            $empaque_c = $model->caja_proyecto->id_empaque;
                            $pedidoModificacion = new CambiosPedido();
                            $pedidoModificacion->id_cliente = $d->guia_id_cliente;
                            $pedidoModificacion->id_planta = $request->planta;
                            $pedidoModificacion->id_variedad = $d->var;
                            $pedidoModificacion->fecha_actual = $fecha_pedidos;
                            $pedidoModificacion->fecha_anterior = $fecha_pedidos;
                            $pedidoModificacion->piezas = null;
                            $pedidoModificacion->id_usuario = session('id_usuario');
                            $pedidoModificacion->ramos = $d->total_ramos;
                            $pedidoModificacion->tallos = $d->tallos;
                            $pedidoModificacion->ramos_x_caja = $d->guia_ramos_x_caja;
                            $pedidoModificacion->tallos_x_ramo = $d->guia_tallos_x_ramo;
                            $pedidoModificacion->longitud_ramo = $d->guia_longitud_ramo;
                            $pedidoModificacion->id_empaque_p = $empaque_p;
                            $pedidoModificacion->id_empaque_c = $empaque_c;
                            $pedidoModificacion->save();
                        }
                    }
                }
            }

            /*BORRAR las CANCELACIONES*/
            if ($request->cantidad_paginas == 1)
                $delete = Mixtos::where('id_planta', $request->planta)
                    ->where('id_cliente', $request->cliente)
                    ->where('longitud_ramo', $request->longitud)
                    ->where('fecha', $request->fecha)
                    ->whereNotIn('id_detalle_caja_proyecto', $ids_det_caj)
                    ->whereNotIn('id_mixtos', $ids_dist)
                    ->delete();

            foreach ($resumen_variedades as $r) {
                jobCosechaEstimada::dispatch($r['variedad'], $r['longitud'], opDiasFecha('+', 1, $r['fecha']))
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

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function eliminar_distribuciones(Request $request)
    {
        DB::beginTransaction();
        try {
            $delete = Mixtos::where('id_planta', $request->planta)
                ->where('id_cliente', $request->cliente)
                ->where('longitud_ramo', $request->longitud)
                ->where('fecha', $request->fecha)
                ->delete();

            $variedades = DB::table('variedad')
                ->where('id_planta', $request->planta)
                ->where('assorted', 0)
                ->where('estado', 1)
                ->get();
            foreach ($variedades as $var) {
                jobCosechaEstimada::dispatch($var->id_variedad, 0, opDiasFecha('+', 1, $request->fecha))
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
