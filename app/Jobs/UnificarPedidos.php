<?php

namespace yura\Jobs;

use Carbon\Carbon;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\DetalleEnvio;
use yura\Modelos\DetallePedido;
use yura\Modelos\DetallePedidoDatoExportacion;
use yura\Modelos\Envio;
use yura\Modelos\Pedido;

class UnificarPedidos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $fechasNuevosPedidos;
    public $request;
    public $timeout = 216000;
    public $idsPedidos;

    public function __construct($fechasNuevosPedidos,$idsPedidos,$request)
    {
        $this->fechasNuevosPedidos = $fechasNuevosPedidos;
        $this->request = $request;
        $this->idsPedidos = $idsPedidos;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('memory_limit', '-1');

        try{

            DB::beginTransaction();

            $empresa = ConfiguracionEmpresa::where('estado',1)->first();

            $oldsPedido = Pedido::whereIn('id_pedido',$this->request['id_pedidos'])->get();

            foreach($this->fechasNuevosPedidos as $newPedido){

                $pedido = Pedido::find($newPedido->id_pedido);

                $objPedido = new Pedido;
                $objPedido->id_cliente = $this->request['id_cliente'];
                $objPedido->tipo_pedido = $this->request['tipo_pedido'];
                $objPedido->fecha_pedido = $pedido->fecha_pedido;
                $objPedido->id_configuracion_empresa = $empresa->id_configuracion_empresa;
                //$objPedido->packing = $pedido->packing;
                $objPedido->variedad = '';
                $objPedido->save();

                foreach($oldsPedido as  $oldP){

                    $oldDp = DetallePedido::where('id_pedido',$oldP->id_pedido)->get();

                    foreach($oldDp as $dp){

                        $detallePedido = new DetallePedido;
                        $detallePedido->id_pedido = $objPedido->id_pedido;
                        $detallePedido->id_cliente_especificacion = $dp->id_cliente_especificacion;
                        $detallePedido->id_agencia_carga = $dp->id_agencia_carga;
                        $detallePedido->cantidad = $dp->cantidad;
                        $detallePedido->fecha_registro = $dp->fecha_registro;
                        $detallePedido->precio = $dp->precio;
                        $detallePedido->save();

                        foreach($dp->detalle_pedido_dato_exportacion as $dedp){

                            $detallePedidoDatoExportacion = new DetallePedidoDatoExportacion;
                            $detallePedidoDatoExportacion->id_detalle_pedido = $detallePedido->id_detalle_pedido;
                            $detallePedidoDatoExportacion->id_dato_exportacion = $dedp->id_dato_exportacion;
                            $detallePedidoDatoExportacion->valor = $dedp->valor;
                            $detallePedidoDatoExportacion->save();

                        }

                    }

                }

                $objEnvio = new Envio();
                $objEnvio->fecha_envio = Carbon::parse($pedido->fecha_pedido)->toDateTimeString();
                $objEnvio->id_pedido = $objPedido->id_pedido;
                $objEnvio->id_consignatario = $pedido->envios[0]->id_consignatario;
                $objEnvio->guia_madre= $pedido->envios[0]->guia_madre;
                $objEnvio->guia_hija= $pedido->envios[0]->guia_hija;
                $objEnvio->dae= $pedido->envios[0]->dae;
                $objEnvio->codigo_pais= $pedido->envios[0]->codigo_pais;
                $objEnvio->codigo_dae= $pedido->envios[0]->codigo_dae;
                $objEnvio->save();

                if(isset($pedido->envios[0]->detalles)){
                    $detalleEnvio = new DetalleEnvio;
                    $detalleEnvio->id_envio= $objEnvio->id_envio;
                    $detalleEnvio->id_especificacion= $pedido->envios[0]->detalles[0]->id_especificacion;
                    $detalleEnvio->id_aerolinea= $pedido->envios[0]->detalles[0]->id_aerolinea;
                    $detalleEnvio->cantidad= $pedido->envios[0]->detalles[0]->cantidad;
                    $detalleEnvio->save();
                }

            }

            //DESTRUIR PEDIDOS VIEJOS
            info($this->idsPedidos);
            foreach($this->idsPedidos as $idEliminarPedido)
                Pedido::destroy($idEliminarPedido);

            DB::commit();

        }catch(\Exception $e){

            info('ERROR EN LA UNIFICACION DE PEDIDOS '.$e->getMessage().' '.$e->getFile().' '.$e->getLine());
            DB::rollBack();

        }

    }
}
