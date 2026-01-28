<?php

namespace yura\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use yura\Console\Commands\NotificacionesSistema;
use yura\Modelos\Bitacora;
use yura\Modelos\Cliente;
use yura\Modelos\ClientePedidoEspecificacion;
use yura\Modelos\DetalleEspecificacionEmpaque;
use yura\Modelos\DetallePedido;
use yura\Modelos\DetallePedidoDatoExportacion;
use yura\Modelos\Envio;
use yura\Modelos\Especificacion;
use yura\Modelos\EspecificacionEmpaque;
use yura\Modelos\Notificacion;
use yura\Modelos\Pedido;
use yura\Modelos\PedidoConfirmacion;
use yura\Modelos\PedidoModificacion;
use yura\Modelos\RenovarOrdenFija;
use yura\Modelos\UserNotification;

class jobGrabarPedido implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $request;
    public function __construct($tipo, $fecha, $cliente, $consignatario, $agencia, $detalles_pedido, $usuario, $ip)
    {
        $this->request = [
            'tipo' => $tipo,
            'fecha' => $fecha,
            'cliente' => $cliente,
            'consignatario' => $consignatario,
            'agencia' => $agencia,
            'detalles_pedido' => $detalles_pedido,
            'usuario' => $usuario,
            'ip' => $ip,
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $request = $this->request;

        try {
            DB::beginTransaction();
            $renovacion = '';
            if ($request['tipo'] == 'STANDING ORDER') {
                $fechas = [];
                if ($request['fecha']['opcion_pedido_fijo'] == 1 || $request['fecha']['opcion_pedido_fijo'] == 2) {
                    $f = $request['fecha']['desde'];
                    while ($f <= $request['fecha']['hasta']) {
                        if ($request['fecha']['opcion_pedido_fijo'] == 1 && date('N', strtotime($f)) == $request['fecha']['dia_semana'])
                            $fechas[] = $f;

                        if ($request['fecha']['opcion_pedido_fijo'] == 2 && substr($f, 8, 2) == $request['fecha']['dia_mes'])
                            $fechas[] = $f;
                        $f = opDiasFecha('+', 1, $f);
                    }
                    if ($request['fecha']['intervalo'] == 2) {
                        foreach ($fechas as $pos => $f) {
                            if ($pos % 2 == 1)
                                unset($fechas[$pos]);
                        }
                    }
                    $renovacion = [
                        'renovar' => $request['fecha']['renovar'],
                        'intervalo' => $request['fecha']['intervalo'] == 1 ? 7 : 14
                    ];
                } else {
                    $fechas = $request['fecha']['fechas'];
                }
                $fecha = $fechas[0];
            } else {
                $fecha = $request['fecha'];
            }

            dump('PEDIDO del dia: ' . $fecha);
            $ped = new Pedido();
            $ped->id_cliente = $request['cliente'];
            $ped->descripcion = '';
            $ped->tipo_pedido = $request['tipo'];
            if ($request['tipo'] == 'STANDING ORDER') {
                $numeroOrdenFija = DB::table('pedido')
                    ->select(DB::raw('max(orden_fija) as cantidad'))
                    ->get()[0]->cantidad;
                $numeroOrdenFija = $numeroOrdenFija != '' ? ($numeroOrdenFija + 1) : 1;
                $ped->orden_fija = $numeroOrdenFija;
            }
            $ped->fecha_pedido = $fecha;
            $ped->id_configuracion_empresa = 1;
            $ped->variedad = '';
            $ped->save();
            $id = DB::table('pedido')
                ->select(DB::raw('max(id_pedido) as id'))
                ->get()[0]->id;
            $ped->id_pedido = $id;

            $envio = new Envio();
            $envio->fecha_envio = $ped->fecha_pedido;
            $envio->id_pedido = $ped->id_pedido;
            $envio->id_consignatario = $request['consignatario'];
            $envio->save();

            foreach (json_decode($request['detalles_pedido']) as $pos_detalle => $d) {
                dump('Creando ESPECIFICACION');
                $esp = new Especificacion();
                $esp->estado = 1;
                $esp->tipo = 'N';
                $esp->creada = 'EJECUCION';
                $esp->save();
                dump('Fin de crear ESPECIFICACION');
                $id = DB::table('especificacion')
                    ->select(DB::raw('max(id_especificacion) as id'))
                    ->get()[0]->id;
                $esp->id_especificacion = $id;

                dump('Creando CLIENTE_PEDIDO_ESPECIFICACION');
                $cli_ped = new ClientePedidoEspecificacion();
                $cli_ped->id_especificacion = $esp->id_especificacion;
                $cli_ped->id_cliente = $request['cliente'];
                $cli_ped->estado = 1;
                $cli_ped->save();
                dump('Fin de crear CLIENTE_PEDIDO_ESPECIFICACION');
                $id = DB::table('cliente_pedido_especificacion')
                    ->select(DB::raw('max(id_cliente_pedido_especificacion) as id'))
                    ->get()[0]->id;
                $cli_ped->id_cliente_pedido_especificacion = $id;

                dump('Creando ESPECIFICACION_EMPAQUE');
                $esp_emp = new EspecificacionEmpaque();
                $esp_emp->id_especificacion = $esp->id_especificacion;
                $esp_emp->id_empaque = $d->caja;
                $esp_emp->cantidad = 1;
                $esp_emp->save();
                dump('Fin de crear ESPECIFICACION_EMPAQUE');
                $id = DB::table('especificacion_empaque')
                    ->select(DB::raw('max(id_especificacion_empaque) as id'))
                    ->get()[0]->id;
                $esp_emp->id_especificacion_empaque = $id;

                $precio = '';
                foreach ($d->detalles_combo as $pos_det_esp => $det_combo) {
                    dump('Creando DETALLE_ESPECIFICACION');
                    $det_esp = new DetalleEspecificacionEmpaque();
                    $det_esp->id_especificacion_empaque = $esp_emp->id_especificacion_empaque;
                    $det_esp->id_variedad = $det_combo->variedad;
                    $det_esp->id_clasificacion_ramo = 31;
                    $det_esp->cantidad = $det_combo->ramos_x_caja;
                    $det_esp->id_empaque_p = $det_combo->presentacion;
                    $det_esp->tallos_x_ramos = $det_combo->tallos_x_ramos;
                    $det_esp->longitud_ramo = $det_combo->longitud;
                    $det_esp->id_unidad_medida = 1;
                    $det_esp->save();
                    dump('Fin de crear DETALLE_ESPECIFICACION');
                    $id = DB::table('detalle_especificacionempaque')
                        ->select(DB::raw('max(id_detalle_especificacionempaque) as id'))
                        ->get()[0]->id;
                    $det_esp->id_detalle_especificacionempaque = $id;

                    jobCosechaEstimada::dispatch($det_esp->id_variedad, $det_esp->longitud_ramo, opDiasFecha('-', 1, $fecha))
                        ->onQueue('cosecha_estimada')
                        ->onConnection('database');

                    if ($pos_det_esp == 0) {
                        $precio = $det_combo->precio_ped . ';' . $det_esp->id_detalle_especificacionempaque;
                    } else {
                        $precio .= '|' . $det_combo->precio_ped . ';' . $det_esp->id_detalle_especificacionempaque;
                    }

                    /* TABLA PEDIDO_CONFIRMACION */
                    $ped_conf = PedidoConfirmacion::where('id_planta', $det_esp->variedad->id_planta)
                        ->where('fecha', $fecha)
                        ->first();
                    if ($ped_conf == '') {
                        $ped_conf = new PedidoConfirmacion();
                        $ped_conf->id_planta = $det_esp->variedad->id_planta;
                        $ped_conf->fecha = $fecha;
                        $ped_conf->ejecutado = 0;
                        $ped_conf->save();
                    }
                }

                dump('Creando DETALLE_PEDIDO');
                $det_ped = new DetallePedido();
                $det_ped->id_pedido = $ped->id_pedido;
                $det_ped->id_cliente_especificacion = $cli_ped->id_cliente_pedido_especificacion;
                $det_ped->id_agencia_carga = $request['agencia'];
                $det_ped->cantidad = $d->piezas;
                $det_ped->orden = $pos_detalle + 1;
                $det_ped->precio = $precio;
                $det_ped->estado = 1;
                $det_ped->save();
                dump('Fin de crear DETALLE_PEDIDO');
                $id = DB::table('detalle_pedido')
                    ->select(DB::raw('max(id_detalle_pedido) as id'))
                    ->get()[0]->id;
                $det_ped->id_detalle_pedido = $id;

                foreach ($d->valores_marcaciones as $pos_det_exp => $dato_exp) {
                    dump('Creando DETALLE_PEDIDO_DATO_EXPORTACION');
                    if ($dato_exp->valor_marcacion != '') {
                        $det_ped_exp = new DetallePedidoDatoExportacion();
                        $det_ped_exp->id_detalle_pedido = $det_ped->id_detalle_pedido;
                        $det_ped_exp->id_dato_exportacion = $dato_exp->id_marcacion;
                        $det_ped_exp->valor = $dato_exp->valor_marcacion;
                        $det_ped_exp->save();
                    }
                }
            }

            dump('* CREAR EL RESTO DE LA ORDEN FIJA *');
            /* CREAR EL RESTO DE LA ORDEN FIJA */
            if ($request['tipo'] == 'STANDING ORDER') {
                foreach ($fechas as $pos => $f) {
                    if ($pos > 0) {
                        jobCrearOrdenFija::dispatch($ped->id_pedido, $f)->onQueue('crear_orden_fija');
                    }
                }

                /* CREAR RENOVACION */
                if ($renovacion['renovar'] == true) {
                    $model_renovar = new RenovarOrdenFija();
                    $model_renovar->orden_fija = $numeroOrdenFija;
                    $model_renovar->renovacion = $renovacion['intervalo'];
                    $model_renovar->save();
                }
            }

            /* GUARDAR EN LA TABLA PEDIDO_MODIFICACION */
            if (hoy() == $fecha || hoy() == opDiasFecha('-', 1, $fecha)) {
                foreach ($ped->detalles as $x => $det_ped) {
                    foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                        foreach ($esp_emp->detalles as $det_esp_emp) {
                            $pedidoModificacion = new PedidoModificacion();
                            $pedidoModificacion->id_cliente = $ped->id_cliente;
                            $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                            $pedidoModificacion->fecha_nuevo_pedido = $fecha;
                            $pedidoModificacion->fecha_anterior_pedido = $fecha;
                            $pedidoModificacion->cantidad = $det_ped->cantidad; // piezas
                            $pedidoModificacion->operador = '+';
                            $pedidoModificacion->id_usuario = $request['usuario'];
                            $ramos_x_caja = $det_esp_emp->cantidad;
                            $pedidoModificacion->ramos_x_caja = $ramos_x_caja;
                            $pedidoModificacion->save();
                        }
                    }
                }
            }

            /* ======= ACTUALIZAR LA TABLA DISTRIBUCION_RECETAS ========== */
            DistribucionRecetas::dispatch($ped->id_pedido)->onQueue('buquets');

            $bitacora = new Bitacora();
            $bitacora->tabla = 'PEDIDO';
            $bitacora->codigo = $ped->id_pedido;
            $bitacora->accion = 'I';
            $bitacora->id_usuario = $request['usuario'];
            $bitacora->observacion = 'CREACION del pedido con fecha ' . $ped->fecha_pedido . ', del cliente: ' . $ped->cliente->detalle()->nombre . ', tipo: ' . $ped->tipo_pedido . ' desde el FORMULARIO_NUEVO_DE_CREACION';
            $bitacora->ip = $request['ip'];
            $bitacora->fecha_registro = date('Y-m-d H:i:s');
            $bitacora->save();

            /* ------------ ACTUALIZR NOTIFICACION exito_grabar_pedido --------------- */
            $not = Notificacion::All()
                ->where('estado', 1)
                ->where('nombre', 'exito_grabar_pedido')
                ->first();
            foreach ($not->usuarios as $not_user) {
                $model = new UserNotification();
                $model->id_notificacion = $not->id_notificacion;
                $model->id_usuario = $not_user->id_usuario;
                $model->titulo = 'EXITO al grabar el pedido del cliente: ' . Cliente::find($request['cliente'])->detalle()->nombre . '; fecha: ' . $fecha . '; tipo: ' . $request['tipo'];
                $model->texto = 'Se GRABO correctamente, el pedido del cliente: ' . Cliente::find($request['cliente'])->detalle()->nombre . '; fecha: ' . $fecha . '; tipo: ' . $request['tipo'];
                $model->url = 'pedidos';
                $model->save();
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $fallo = 'Ha ocurrido un problema al guardar la informacion al sistema. Pongase en contacto con el administrador'
                . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
            $fallo = str_replace("'", "*", $fallo);
            $fallo = str_replace('"', '*', $fallo);
            /* -------------- ACTUALIZR NOTIFICACION fallos_grabar_pedido --------------- */
            $not = Notificacion::All()
                ->where('estado', 1)
                ->where('nombre', 'fallos_grabar_pedido')
                ->first();
            foreach ($not->usuarios as $not_user) {
                $model = new UserNotification();
                $model->id_notificacion = $not->id_notificacion;
                $model->id_usuario = $not_user->id_usuario;
                $text_fecha = isset($fecha) ? $fecha : '...';
                $titulo = 'Fallo al grabar el pedido del cliente: ' . Cliente::find($request['cliente'])->detalle()->nombre . '; fecha: ' . $text_fecha . '; tipo: ' . $request['tipo'];
                $titulo = str_replace("'", "*", $titulo);
                $titulo = str_replace('"', '*', $titulo);
                $model->titulo = $titulo;
                $model->texto = $fallo;
                $model->url = 'pedidos';
                $model->save();
            }
        }
    }
}
