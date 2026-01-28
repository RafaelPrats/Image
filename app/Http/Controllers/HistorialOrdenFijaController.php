<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Jobs\jobAgregarNuevoPedidoAOrdenFija;
use yura\Jobs\jobCosechaEstimada;
use yura\Jobs\jobUpdateOrdenFija;
use yura\Modelos\ClienteConsignatario;
use yura\Modelos\ClientePedidoEspecificacion;
use yura\Modelos\DetalleCliente;
use yura\Modelos\DetalleEspecificacionEmpaque;
use yura\Modelos\DetallePedido;
use yura\Modelos\DetallePedidoDatoExportacion;
use yura\Modelos\Envio;
use yura\Modelos\Especificacion;
use yura\Modelos\EspecificacionEmpaque;
use yura\Modelos\Pedido;
use yura\Modelos\PedidoCancelado;
use yura\Modelos\PedidoUnificado;
use yura\Modelos\RenovarOrdenFija;
use yura\Modelos\Submenu;

class HistorialOrdenFijaController extends Controller
{
    public function inicio(Request $request)
    {
        $clientes = DetalleCliente::where('estado', 1)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.postcocecha.historial_ordenes_fija.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'clientes' => $clientes,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $pedidos = DB::table('pedido')
            ->select('orden_fija', 'id_cliente')->distinct()
            ->whereIn('fecha_pedido', [$request->fecha, opDiasFecha('+', 7, $request->fecha)])
            ->where('tipo_pedido', 'STANDING ORDER')
            ->whereNotNull('orden_fija');
        if ($request->cliente != '')
            $pedidos = $pedidos->where('id_cliente', $request->cliente);
        $pedidos = $pedidos->orderBy('orden_fija')
            ->get();

        $f = $request->fecha;
        $fechas = [$f];
        while (count($fechas) < 10) {
            $f = opDiasFecha('+', 7, $f);
            $fechas[] = $f;
        }

        //$ini_timer = date('Y-m-d H:i:s');
        $listado = [];
        foreach ($pedidos as $p) {
            $valores = [];
            $cancelados = [];
            $unificados = [];
            foreach ($fechas as $f) {
                $valor = DB::table('pedido as p')
                    ->join('detalle_pedido as d', 'd.id_pedido', '=', 'p.id_pedido')
                    ->select(
                        DB::raw('sum(d.cantidad) as cajas')
                    )
                    ->where('p.fecha_pedido', $f)
                    ->where('p.id_cliente', $p->id_cliente)
                    ->where('p.orden_fija', $p->orden_fija)
                    ->get()[0]->cajas;
                $valores[] = $valor;
                if ($valor == null) {
                    $getCancelado = DB::table('pedido_cancelado')
                        ->where('orden_fija', $p->orden_fija)
                        ->where('fecha', $f)
                        ->get()
                        ->first();
                    $cancelados[] = $getCancelado;

                    $getUnificado = DB::table('pedido_unificado')
                        ->where('orden_fija', $p->orden_fija)
                        ->where('fecha', $f)
                        ->get()
                        ->first();
                    $unificados[] = $getUnificado;
                } else {
                    $cancelados[] = null;
                    $unificados[] = null;
                }
            }
            $existe_pedido = DB::table('pedido')
                ->where('orden_fija', $p->orden_fija)
                ->where('fecha_pedido', $request->fecha)
                ->get();
            if (count($existe_pedido) == 0) {
                $pedido = DB::table('pedido as p')
                    ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'p.id_cliente')
                    ->select('p.*', 'dc.nombre as nombre_cliente')->distinct()
                    ->where('p.orden_fija', $p->orden_fija)
                    ->where('p.fecha_pedido', opDiasFecha('+', 7, $request->fecha))
                    ->get()
                    ->first();
            } else {
                $pedido = DB::table('pedido as p')
                    ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'p.id_cliente')
                    ->select('p.*', 'dc.nombre as nombre_cliente')->distinct()
                    ->where('p.id_pedido', $existe_pedido[0]->id_pedido)
                    ->get()
                    ->first();
            }
            $renovacion = RenovarOrdenFija::where('orden_fija', $p->orden_fija)
                ->get()
                ->first();
            $listado[] = [
                'pedido' => $pedido,
                'valores' => $valores,
                'cancelados' => $cancelados,
                'unificados' => $unificados,
                'renovacion' => $renovacion,
            ];
        }
        /*$fin_timer = date('Y-m-d H:i:s');
        dd('ok', difFechas($fin_timer, $ini_timer));*/

        return view('adminlte.gestion.postcocecha.historial_ordenes_fija.partials.listado', [
            'fecha' => $request->fecha,
            'listado' => $listado,
            'fechas' => $fechas,
        ]);
    }

    public function ver_toda_orden(Request $request)
    {
        $listado = DB::table('pedido as p')
            ->join('detalle_pedido as d', 'd.id_pedido', '=', 'p.id_pedido')
            ->select(
                'p.fecha_pedido',
                DB::raw('sum(d.cantidad) as cajas')
            )
            ->where('p.tipo_pedido', 'STANDING ORDER')
            ->where('p.fecha_pedido', '>=', $request->fecha)
            ->where('p.orden_fija', $request->orden_fija)
            ->groupBy('p.fecha_pedido')
            ->get();
        $renovacion = RenovarOrdenFija::where('orden_fija', $request->orden_fija)
            ->get()
            ->first();

        return view('adminlte.gestion.postcocecha.historial_ordenes_fija.partials.ver_toda_orden', [
            'orden_fija' => $request->orden_fija,
            'fecha' => $request->fecha,
            'listado' => $listado,
            'renovacion' => $renovacion,
        ]);
    }

    public function update_orden_fija(Request $request)
    {
        DB::beginTransaction();
        try {
            $pedido = Pedido::where('orden_fija', $request->orden_fija)
                ->where('fecha_pedido', $request->fecha)
                ->get()
                ->first();

            if ($request->has('fechas'))
                $fechas_futuras = json_decode($request->fechas);
            else
                $fechas_futuras = DB::table('pedido')
                    ->select('fecha_pedido')->distinct()
                    ->where('fecha_pedido', '>', $pedido->fecha_pedido)
                    ->where('orden_fija', $pedido->orden_fija)
                    ->orderBy('fecha_pedido')
                    ->get()->pluck('fecha_pedido')->toArray();

            $queue = getQueueForUpdateOrdenFija($pedido->id_pedido);
            $pos_progreso = 1;
            foreach ($fechas_futuras as $pos_f => $f) {
                foreach ($pedido->detalles as $pos_d => $det) {
                    jobUpdateOrdenFija::dispatch(
                        $pedido->id_pedido,
                        $det->id_detalle_pedido,
                        $f,
                        $pos_d == 0 ? true : false,
                        $pos_progreso,
                        count($fechas_futuras) * count($pedido->detalles),
                        session('id_usuario')
                    )->onQueue($queue);
                    $pos_progreso++;
                }
            }

            DB::commit();
            $success = true;
            $msg = 'Se esta procesando la información en un segundo plano';
        } catch (\Exception $e) {

            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function agregar_nueva_fecha(Request $request)
    {
        $pedido = Pedido::All()
            ->where('tipo_pedido', 'STANDING ORDER')
            ->where('fecha_pedido', $request->fecha)
            ->where('orden_fija', $request->orden_fija)
            ->first();
        if ($pedido == '') {
            $pedido = Pedido::All()
                ->where('tipo_pedido', 'STANDING ORDER')
                ->where('fecha_pedido', opDiasFecha('+', 7, $request->fecha))
                ->where('orden_fija', $request->orden_fija)
                ->first();
        }

        return view('adminlte.gestion.postcocecha.historial_ordenes_fija.partials.agregar_nueva_fecha', [
            'orden_fija' => $request->orden_fija,
            'fecha' => $request->fecha,
            'pedido' => $pedido,
        ]);
    }

    public function store_agregar_nueva_fecha(Request $request)
    {
        try {
            DB::beginTransaction();
            foreach (json_decode($request->data) as $pos => $d) {
                jobAgregarNuevoPedidoAOrdenFija::dispatch(
                    $request->id_ped,
                    $d,
                    count(json_decode($request->data)),
                    session('id_usuario')
                )->onQueue('agregar_pedido_a_orden_fija');
            }

            $success = true;
            $msg = 'Se estan <b>AGREGANDO</b> en un segundo plano';
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function eliminar_pedido_orden_fija(Request $request)
    {
        try {
            $pedidos = Pedido::where('orden_fija', $request->orden_fija)
                ->where('fecha_pedido', $request->fecha)
                ->get();
            if (count($pedidos) > 0) {
                $resumen_variedades = [];
                foreach ($pedidos as $p) {
                    bitacora('pedido', $p->id_pedido, 'E', 'Eliminacion del pedido con fecha ' . $p->fecha_pedido . ', de la orden #' . $p->orden_fija . ', desde el HISTORIAL DE ORDENES FIJAS');

                    /* REGISTRAR PEDIDO_CANCELADO */
                    $pedido_cancelado = new PedidoCancelado();
                    $pedido_cancelado->id_cliente = $p->id_cliente;
                    $pedido_cancelado->fecha = $p->fecha_pedido;
                    $pedido_cancelado->orden_fija = $p->orden_fija;
                    $pedido_cancelado->id_usuario = session('id_usuario');
                    $pedido_cancelado->save();

                    foreach ($p->detalles as $det_ped)
                        foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp)
                            foreach ($esp_emp->detalles as $det_esp) {
                                if (!in_array([
                                    'variedad' => $det_esp->id_variedad,
                                    'longitud' => $det_esp->longitud_ramo,
                                    'fecha' => $p->fecha_pedido
                                ], $resumen_variedades)) {
                                    $resumen_variedades[] = [
                                        'variedad' => $det_esp->id_variedad,
                                        'longitud' => $det_esp->longitud_ramo,
                                        'fecha' => $p->fecha_pedido
                                    ];
                                }
                            }
                    $p->delete();
                }

                foreach ($resumen_variedades as $r) {
                    jobCosechaEstimada::dispatch($r['variedad'], $r['longitud'], opDiasFecha('-', 1, $r['fecha']))
                        ->onQueue('cosecha_estimada')
                        ->onConnection('database');
                }
            }

            $success = true;
            $msg = 'Se ha <b>ELIMINADO</b> el pedido';
        } catch (\Exception $e) {

            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function store_renovacion(Request $request)
    {
        try {
            $model = RenovarOrdenFija::where('orden_fija', $request->orden_fija)
                ->get()
                ->first();
            if ($model == '') {
                $model = new RenovarOrdenFija();
                $model->orden_fija = $request->orden_fija;
            }
            $model->renovacion = $request->renovacion;
            $model->save();

            $success = true;
            $msg = 'Se ha <b>ACTUALIZADO</b> la renovacion de la orden fija';
        } catch (\Exception $e) {

            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }
}
