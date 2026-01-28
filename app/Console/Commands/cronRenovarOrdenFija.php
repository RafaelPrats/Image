<?php

namespace yura\Console\Commands;

use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use yura\Modelos\ClientePedidoEspecificacion;
use yura\Modelos\DetalleEspecificacionEmpaque;
use yura\Modelos\DetallePedido;
use yura\Modelos\DetallePedidoDatoExportacion;
use yura\Modelos\Envio;
use yura\Modelos\Especificacion;
use yura\Modelos\EspecificacionEmpaque;
use yura\Modelos\Pedido;
use yura\Modelos\RenovarOrdenFija;

class cronRenovarOrdenFija extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'renovar:orden {orden=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para renovar las ordenes fijas';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ini_set('max_execution_time', 36000);
        set_time_limit(3600);
        $ini = date('Y-m-d H:i:s');
        Log::info('<<<<< ! >>>>> Ejecutando comando "renovar:orden" <<<<< ! >>>>>');

        $orden = $this->argument('orden');
        $renovaciones = RenovarOrdenFija::where('renovacion', '>', 0);
        if ($orden != 0)
            $renovaciones = $renovaciones->where('orden_fija', $orden);
        $renovaciones = $renovaciones->get();
        foreach ($renovaciones as $r) {
            $pedidos = Pedido::where('orden_fija', $r->orden_fija)
                ->where('fecha_pedido', '>=', hoy())
                ->orderBy('fecha_pedido', 'desc')
                ->get();
            $faltantes = 10 - count($pedidos);
            if ($faltantes > 0) {
                $pedOriginal = $pedidos->first();
                if ($pedOriginal != '')
                    for ($i = 1; $i <= $faltantes; $i++) {
                        $fecha = opDiasFecha('+', ($r->renovacion * $i), $pedOriginal->fecha_pedido);
                        dump('*****************************Creando Pedido de la orden: ' . $r->orden_fija . ' para la fecha: ' . $fecha . '*********************************');
                        $envios = Envio::where('id_pedido', $pedOriginal->id_pedido)->get();
                        if (count($envios) == 0) {
                            dump('Creando ENVIO a pedOriginal');
                            $consignatario = DB::table('cliente_consignatario')
                                ->where('id_cliente', $pedOriginal->id_cliente)
                                ->get()
                                ->first();
                            $consignatario = $consignatario != '' ? $consignatario->id_consignatario : '';
                            $envio = new Envio();
                            $envio->fecha_envio = $pedOriginal->fecha_pedido;
                            $envio->id_pedido = $pedOriginal->id_pedido;
                            $envio->id_consignatario = $consignatario;
                            $envio->save();
                        } else
                            $consignatario = $envios[0]->id_consignatario;

                        dump('Creando Pedido');
                        $ped = new Pedido();
                        $ped->id_cliente = $pedOriginal->id_cliente;
                        $ped->descripcion = $pedOriginal->descripcion;
                        $ped->tipo_pedido = $pedOriginal->tipo_pedido;
                        $ped->orden_fija = $pedOriginal->orden_fija;
                        $ped->fecha_pedido = $fecha;
                        $ped->tipo_pedido = 'STANDING ORDER';
                        $ped->id_configuracion_empresa = $pedOriginal->id_configuracion_empresa;
                        $ped->variedad = '';
                        $ped->save();
                        $ped->id_pedido = DB::table('pedido')
                            ->select(DB::raw('max(id_pedido) as id'))
                            ->get()[0]->id;

                        dump('Creando Envio');
                        $envio = new Envio();
                        $envio->fecha_envio = $ped->fecha_pedido;
                        $envio->id_pedido = $ped->id_pedido;
                        $envio->id_consignatario = $consignatario;
                        $envio->save();

                        foreach ($pedOriginal->detalles as $pos_det => $detOriginal) {
                            $det_expOriginales = $detOriginal->detalle_pedido_dato_exportacion;
                            $cli_ped_espOriginal = $detOriginal->cliente_especificacion;
                            $espOriginal = $cli_ped_espOriginal->especificacion;
                            $esp_empOriginal = $espOriginal->especificacionesEmpaque[0];

                            dump('Procesando DETALLE ' . ($pos_det + 1) . '/' . count($pedOriginal->detalles));
                            dump('Creando ESPECIFICACION');
                            $esp = new Especificacion();
                            $esp->estado = 1;
                            $esp->tipo = $espOriginal->tipo;
                            $esp->creada = 'EJECUCION';
                            $esp->save();
                            $esp->id_especificacion = DB::table('especificacion')
                                ->select(DB::raw('max(id_especificacion) as id'))
                                ->get()[0]->id;

                            dump('Creando ESPECIFICACION_EMPAQUE');
                            $esp_emp = new EspecificacionEmpaque();
                            $esp_emp->id_especificacion = $esp->id_especificacion;
                            $esp_emp->id_empaque = $esp_empOriginal->id_empaque;
                            $esp_emp->cantidad = 1;
                            $esp_emp->save();
                            $esp_emp->id_especificacion_empaque = DB::table('especificacion_empaque')
                                ->select(DB::raw('max(id_especificacion_empaque) as id'))
                                ->get()[0]->id;

                            dump('Creando CLIENTE_PEDIDO_ESPECIFICACION');
                            $cli_ped = new ClientePedidoEspecificacion();
                            $cli_ped->id_especificacion = $esp->id_especificacion;
                            $cli_ped->id_cliente = $cli_ped_espOriginal->id_cliente;
                            $cli_ped->estado = 1;
                            $cli_ped->save();
                            $cli_ped->id_cliente_pedido_especificacion = DB::table('cliente_pedido_especificacion')
                                ->select(DB::raw('max(id_cliente_pedido_especificacion) as id'))
                                ->get()[0]->id;

                            $precio = '';
                            foreach ($esp_empOriginal->detalles as $pos_det_esp => $det_espOriginal) {
                                dump('Procesando DETALLE_ESPECIFICACION ' . ($pos_det_esp + 1) . '/' . count($esp_empOriginal->detalles));
                                dump('Creando DETALLE_ESPECIFICACION');
                                $det_esp = new DetalleEspecificacionEmpaque();
                                $det_esp->id_especificacion_empaque = $esp_emp->id_especificacion_empaque;
                                $det_esp->id_variedad = $det_espOriginal->id_variedad;
                                $det_esp->id_clasificacion_ramo = $det_espOriginal->id_clasificacion_ramo;
                                $det_esp->cantidad = $det_espOriginal->cantidad;
                                $det_esp->id_empaque_p = $det_espOriginal->id_empaque_p;
                                $det_esp->tallos_x_ramos = $det_espOriginal->tallos_x_ramos;
                                $det_esp->longitud_ramo = $det_espOriginal->longitud_ramo;
                                $det_esp->id_unidad_medida = $det_espOriginal->id_unidad_medida;
                                $det_esp->save();
                                $det_esp->id_detalle_especificacionempaque = DB::table('detalle_especificacionempaque')
                                    ->select(DB::raw('max(id_detalle_especificacionempaque) as id'))
                                    ->get()[0]->id;

                                $p = getPrecioByDetEsp($detOriginal->precio, $det_espOriginal->id_detalle_especificacionempaque);
                                if ($pos_det_esp == 0) {
                                    $precio = $p . ';' . $det_esp->id_detalle_especificacionempaque;
                                } else {
                                    $precio .= '|' . $p . ';' . $det_esp->id_detalle_especificacionempaque;
                                }
                            }

                            dump('Creando DETALLE_PEDIDO');
                            $det_ped = new DetallePedido();
                            $det_ped->id_pedido = $ped->id_pedido;
                            $det_ped->id_cliente_especificacion = $cli_ped->id_cliente_pedido_especificacion;
                            $det_ped->id_agencia_carga = $detOriginal->id_agencia_carga;
                            $det_ped->cantidad = $detOriginal->cantidad;
                            $det_ped->orden = $detOriginal->orden;
                            $det_ped->precio = $precio;
                            $det_ped->estado = 1;
                            $det_ped->save();
                            $det_ped->id_detalle_pedido = DB::table('detalle_pedido')
                                ->select(DB::raw('max(id_detalle_pedido) as id'))
                                ->get()[0]->id;

                            foreach ($det_expOriginales as $pos_det_exp => $det_expOriginal) {
                                dump('Procesando DETALLE_PEDIDO_DATO_EXPORTACION ' . ($pos_det_exp + 1) . '/' . count($det_expOriginales));
                                dump('Creando DETALLE_PEDIDO_DATO_EXPORTACION');
                                $det_ped_exp = new DetallePedidoDatoExportacion();
                                $det_ped_exp->id_detalle_pedido = $det_ped->id_detalle_pedido;
                                $det_ped_exp->id_dato_exportacion = $det_expOriginal->id_dato_exportacion;
                                $det_ped_exp->valor = $det_expOriginal->valor;
                                $det_ped_exp->save();
                            }
                        }
                    }
            }
        }


        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "renovar:orden" <<<<< * >>>>>');
    }
}
