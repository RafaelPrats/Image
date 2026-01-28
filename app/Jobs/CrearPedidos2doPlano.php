<?php

namespace yura\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\DetalleEnvio;
use yura\Modelos\DetalleEspecificacionEmpaqueRamosCaja;
use yura\Modelos\DetallePedido;
use yura\Modelos\DetallePedidoDatoExportacion;
use yura\Modelos\Envio;
use yura\Modelos\Pedido;

class CrearPedidos2doPlano implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $request;
    public $timeout = 216000;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{

            ini_set('memory_limit', '-1');
            $request = new Request($this->request);

            foreach ($request->arrFechas as $key => $fechas) {

                $formatoFecha = '';

                if (isset($request->opcion) && $request->opcion != 3) {
                    $formato = explode("/", $fechas);
                    $formatoFecha = $formato[2] . '-' . $formato[0] . '-' . $formato[1];
                }

                $fechaFormateada = (isset($request->opcion) && $request->opcion != 3) ? $formatoFecha : $fechas;

                if (!empty($request->id_pedido)) { //ACTUALIZAR

                    $dataEnvio = Envio::where('id_pedido', $request->id_pedido)->first();

                    if (isset($dataEnvio->id_envio)) {

                        $codigo_dae = $dataEnvio->codigo_dae;
                        $dae = $dataEnvio->dae;
                        $guia_madre = $dataEnvio->guia_madre;
                        $guia_hija = $dataEnvio->guia_hija;
                        $email = $dataEnvio->email;
                        $telefono = $dataEnvio->telefono;
                        $direccion = $dataEnvio->direccion;
                        $codigo_pais = $dataEnvio->codigo_pais;
                        $almacen = $dataEnvio->almacen;
                        $aerolinea = $dataEnvio->detalles[0]->id_aerolinea;
                        $id_configuracion_empresa = $dataEnvio->pedido->id_configuracion_empresa;
                    }
                }

                $empresa = ConfiguracionEmpresa::find($request->id_configuracion_empresa);

                $objPedido = new Pedido;
                $p = Pedido::orderBy('id_pedido', 'desc')->first();
                $objPedido->id_pedido = isset($p->id_pedido) ? $p->id_pedido+1 : 1;
                $objPedido->id_cliente = $request->id_cliente;
                $objPedido->descripcion = $request->descripcion;
                $objPedido->tipo_pedido = !isset($request->tipo_pedido) ? 'OPEN MARKET' : $request->tipo_pedido;
                $objPedido->fecha_pedido = $fechaFormateada;
                $objPedido->id_configuracion_empresa = isset($id_configuracion_empresa) ? $id_configuracion_empresa : $request->id_configuracion_empresa;
                $objPedido->variedad = substr(implode("|", array_unique($request->variedades)), 0, -1);
                $objPedido->packing = $empresa->numero_packing+1;

                if ($objPedido->save()) {
                    info('2do plano '.$objPedido->packing);
                    $empresa->numero_packing = $objPedido->packing;
                    $empresa->save();

                    $model = Pedido::orderBy('id_pedido','desc')->first();

                    foreach ($request->arrDataDetallesPedido as $key => $item) {

                        $objDetallePedido = new DetallePedido();
                        $detPed = DetallePedido::orderBy('id_detalle_pedido', 'desc')->first();
                        $objDetallePedido->id_detalle_pedido = isset($detPed->id_detalle_pedido) ? $detPed->id_detalle_pedido + 1 : 1;

                        $objDetallePedido->id_cliente_especificacion = $item['id_cliente_pedido_especificacion'];
                        $precio = substr($item['precio'], 0, -1);

                        $objDetallePedido->id_pedido = $model->id_pedido;
                        $objDetallePedido->id_agencia_carga = $item['id_agencia_carga'];
                        $objDetallePedido->cantidad = $item['cantidad'];
                        $objDetallePedido->precio = $precio;
                        $objDetallePedido->orden = 1;/* $item['orden']; */

                        if ($objDetallePedido->save()) {
                            $modelDetallePedido = DetallePedido::orderBy('id_detalle_pedido','desc')->first();

                            //GUARDAR LOS RAMOS X CAJAS MODIFICADOS EN EL PEDIDO DE CADA DETALLE_SPECIFICACION_EMPAQUE
                            if (isset($item['arr_custom_ramos_x_caja']) && count($item['arr_custom_ramos_x_caja']) > 0) {
                                foreach ($item['arr_custom_ramos_x_caja'] as $z => $customRamosXCaja) {
                                    $objDetEspEmpRxC = new DetalleEspecificacionEmpaqueRamosCaja();
                                    $detEspEmpRxC = DetalleEspecificacionEmpaqueRamosCaja::orderBy('id_detalle_especificacionempaque_ramos_x_caja', 'desc')->first();
                                    $objDetEspEmpRxC->id_detalle_especificacionempaque_ramos_x_caja = isset($detEspEmpRxC->id_detalle_especificacionempaque_ramos_x_caja) ? $detEspEmpRxC->id_detalle_especificacionempaque_ramos_x_caja + 1 : 1;
                                    $objDetEspEmpRxC->id_detalle_pedido = $modelDetallePedido->id_detalle_pedido;
                                    $objDetEspEmpRxC->id_detalle_especificacionempaque = $customRamosXCaja['id_det_esp_emp'];
                                    $objDetEspEmpRxC->cantidad = $customRamosXCaja['ramos_x_caja'];
                                    $objDetEspEmpRxC->fecha_registro = now()->format('Y-m-d H:i:s.v');
                                    $objDetEspEmpRxC->save();
                                }

                                if (($z + 1) < count($item['arr_custom_ramos_x_caja']))
                                    Pedido::destroy($model->id_pedido);

                            }

                            if ($request->arrDatosExportacion != '' && isset($request->arrDatosExportacion[$key])) {

                                foreach ($request->arrDatosExportacion[$key] as $de) {

                                    if (isset($de['valor']) && $de['valor'] != null) {
                                        $objDetallePedidoDatoExportacion = new DetallePedidoDatoExportacion();
                                        $dpe = DetallePedidoDatoExportacion::orderBy('id_detallepedido_datoexportacion', 'desc')->first();
                                        $objDetallePedidoDatoExportacion->id_detallepedido_datoexportacion = isset($dpe->id_detallepedido_datoexportacion) ? $dpe->id_detallepedido_datoexportacion + 1 : 1;
                                        $objDetallePedidoDatoExportacion->id_detalle_pedido = $modelDetallePedido->id_detalle_pedido;
                                        $objDetallePedidoDatoExportacion->id_dato_exportacion = $de['id_dato_exportacion'];
                                        $objDetallePedidoDatoExportacion->valor = $de['valor'];
                                        $objDetallePedidoDatoExportacion->save();
                                    }
                                }
                            }

                            Pedido::destroy($request->id_pedido);

                        } else {

                        }
                    }

                    $objEnvio = new Envio;
                    $env = Envio::orderBy('id_envio','desc')->first();
                    $objEnvio->id_envio = isset($env->id_envio) ? $env->id_envio+1 : 1;
                    $objEnvio->fecha_envio = $fechaFormateada;
                    $objEnvio->id_pedido = $model->id_pedido;
                    $objEnvio->id_consignatario = $request->id_consignatario;

                    if (isset($codigo_dae)) {
                        $objEnvio->codigo_dae = $codigo_dae;
                        $objEnvio->dae = $dae;
                        $objEnvio->guia_madre = $guia_madre;
                        $objEnvio->guia_hija = $guia_hija;
                        $objEnvio->email = $email;
                        $objEnvio->telefono = $telefono;
                        $objEnvio->direccion = $direccion;
                        $objEnvio->codigo_pais = $codigo_pais;
                        $objEnvio->almacen = $almacen;
                    }

                    if ($objEnvio->save()) {
                        $modelEnvio = Envio::orderBy('id_envio','desc')->first();

                        $dataDetallePedido = DetallePedido::where('id_pedido', $model->id_pedido)
                            ->join('cliente_pedido_especificacion as cpe', 'detalle_pedido.id_cliente_especificacion', 'cpe.id_cliente_pedido_especificacion')
                            ->select('cpe.id_especificacion', 'detalle_pedido.cantidad')->get();

                        foreach ($dataDetallePedido as $detallePeido) {
                            $objDetalleEnvio = new DetalleEnvio();
                            $detEnv = DetalleEnvio::orderBy('id_detalle_envio','desc')->first();
                            $objDetalleEnvio->id_detalle_envio = isset($detEnv->id_detalle_envio) ? $detEnv->id_detalle_envio+1 : 1;
                            $objDetalleEnvio->id_envio = $modelEnvio->id_envio;
                            $objDetalleEnvio->id_especificacion = $detallePeido->id_especificacion;
                            $objDetalleEnvio->cantidad = $detallePeido->cantidad;
                            isset($aerolinea) ? $objDetalleEnvio->id_aerolinea = $aerolinea : "";
                            $objDetalleEnvio->save();
                        }

                    }

                }
            }

        }catch(\Exception $e){

            info("ERROR CREANDO PEDIDO SEGUN PLANO");
            info($e->getMessage()." Linea ".$e->getLine()." archivo ".$e->getFile());
            $this->fail($e->getMessage()." Linea ".$e->getLine()." archivo ".$e->getFile());
        }

    }
}
