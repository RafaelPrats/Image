<?php

namespace yura\Jobs;

use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\PedidoController;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\DetalleEnvio;
use yura\Modelos\DetallePedido;
use yura\Modelos\DetallePedidoDatoExportacion;
use yura\Modelos\Envio;
use yura\Modelos\Pedido;
use Illuminate\Http\Request;

class EditaStandingOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $timeout = 216000;
    //public $tries = 2;
    public $cpeOldPedido;
    public $pedido;
    public $newIdPedido;

    public function __construct($cpeOldPedido,$pedido,$newIdPedido)
    {
        $this->cpeOldPedido = $cpeOldPedido;
        $this->pedido = $pedido;
        $this->newIdPedido = $newIdPedido;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            ini_set('memory_limit', '-1');

            $pedidos = Pedido::where([
                ['pedido.tipo_pedido', 'STANDING ORDER'],
                ['pedido.fecha_pedido', '>',$this->pedido['fecha_pedido']]
            ])->join('detalle_pedido as dp',function($j) {
                $j->on('pedido.id_pedido','dp.id_pedido')->whereIn('dp.id_cliente_especificacion',$this->cpeOldPedido);
            })->join('cliente_pedido_especificacion as cpe',function($j) {
                $j->on('cpe.id_cliente_pedido_especificacion','dp.id_cliente_especificacion')->where('cpe.id_cliente',$this->pedido['id_cliente']);
            })->select('pedido.id_pedido','pedido.fecha_pedido','pedido.packing')->distinct()->get();

            /* $newPedido = Pedido::find($this->newIdPedido);

            if(isset($newPedido)){

                $pedidos = Pedido::where([
                    ['pedido.tipo_pedido', 'STANDING ORDER'],
                    ['pedido.fecha_pedido', '>',$this->pedido['fecha_pedido']]
                ])->join('detalle_pedido as dp',function($j) {
                    $j->on('pedido.id_pedido','dp.id_pedido')->whereIn('dp.id_cliente_especificacion',$this->cpeOldPedido);
                })->join('cliente_pedido_especificacion as cpe',function($j) {
                    $j->on('cpe.id_cliente_pedido_especificacion','dp.id_cliente_especificacion')->where('cpe.id_cliente',$this->pedido['id_cliente']);
                })->select('pedido.id_pedido','pedido.fecha_pedido','pedido.packing')->distinct()->get();

                info('cantidad de pedidos a editar y crear '.count($pedidos));

                foreach($pedidos as $p){

                    $idPedido = Pedido::orderBy('id_pedido','desc')->first();

                    $replicatePedido = new Pedido;

                    $replicatePedido->id_pedido = isset($idPedido) ? $idPedido->id_pedido+1 : 1;
                    $replicatePedido->id_cliente = $newPedido->id_cliente;
                    $replicatePedido->estado = $newPedido->estado;
                    $replicatePedido->empaquetado = $newPedido->empaquetado;
                    $replicatePedido->tipo_especificacion = $newPedido->tipo_especificacion;
                    $replicatePedido->confirmado = $newPedido->confirmado;
                    $replicatePedido->tipo_pedido = $newPedido->tipo_pedido;
                    $replicatePedido->id_configuracion_empresa = $newPedido->id_configuracion_empresa;
                    //$replicatePedido->etiqueta_impresa = $newPedido->etiqueta_impresa;
                    $replicatePedido->tipo_pedido = $newPedido->tipo_pedido;
                    $replicatePedido->fecha_pedido = $p->fecha_pedido;
                    $replicatePedido->fecha_registro= now()->toDateTimeString();
                    $replicatePedido->variedad = $newPedido->variedad;
                    $replicatePedido->packing = $p->packing;
                    $replicatePedido->save();

                    $envio =  $newPedido->envios[0];

                    $env = Envio::orderBy('id_envio','desc')->first();

                    $newEnvio = new Envio;
                    $newEnvio->id_envio = isset($env->id_envio) ? $env->id_envio + 1 : 1;
                    $newEnvio->id_pedido= $replicatePedido->id_pedido;
                    $newEnvio->fecha_envio= $p->fecha_pedido;
                    $newEnvio->fecha_registro= now()->toDateTimeString();
                    $newEnvio->estado=$envio->estado;
                    $newEnvio->guia_madre= $envio->guia_madre;
                    $newEnvio->guia_hija=$envio->guia_hija;
                    $newEnvio->dae=$envio->dae;
                    $newEnvio->email=$envio->email;
                    $newEnvio->telefono=$envio->telefono;
                    $newEnvio->direccion=$envio->direccion;
                    $newEnvio->codigo_pais=$envio->codigo_pais;
                    $newEnvio->almacen=$envio->almacen;
                    $newEnvio->codigo_dae=$envio->codigo_dae;
                    $newEnvio->id_consignatario=$envio->id_consignatario;
                    $newEnvio->save();

                    foreach($newPedido->detalles as $det_ped){

                        $newDetPed = new DetallePedido;

                        $idDetPed = DetallePedido::orderBy('id_detalle_pedido','desc')->first();
                        $newDetPed->id_detalle_pedido = isset($idDetPed) ? $idDetPed->id_detalle_pedido+1 : 1;
                        $newDetPed->id_cliente_especificacion= $det_ped->id_cliente_especificacion;
                        $newDetPed->id_pedido= $replicatePedido->id_pedido;
                        $newDetPed->id_agencia_carga= $det_ped->id_agencia_carga;
                        $newDetPed->cantidad= $det_ped->cantidad;
                        $newDetPed->precio= $det_ped->precio;
                        $newDetPed->save();

                        $detEnvios = $envio->detalles;

                        foreach($detEnvios as $detEnvio){

                            $idDetEnv = DetalleEnvio::orderBy('id_detalle_envio','desc')->first();
                            $newDetEnv = new DetalleEnvio;
                            $newDetEnv->id_detalle_envio = isset($idDetEnv->id_detalle_envio) ? $idDetEnv->id_detalle_envio + 1 : 1;
                            $newDetEnv->id_envio = $newEnvio->id_envio;
                            $newDetEnv->id_especificacion = $detEnvio->id_especificacion;
                            $newDetEnv->id_aerolinea = $detEnvio->id_aerolinea;
                            $newDetEnv->cantidad = $detEnvio->cantidad;
                            $newDetEnv->save();

                        }

                        foreach($det_ped->detalle_pedido_dato_exportacion as $dato_export){

                            $idDetPedDatoExp = DetallePedidoDatoExportacion::orderBy('id_detallepedido_datoexportacion','desc')->first();
                            $newPedDatoExp = new DetallePedidoDatoExportacion;
                            $newPedDatoExp->id_detallepedido_datoexportacion =  isset($idDetPedDatoExp) ? $idDetPedDatoExp->id_detallepedido_datoexportacion+1 :1;
                            $newPedDatoExp->id_detalle_pedido = $newDetPed->id_detalle_pedido;
                            $newPedDatoExp->id_dato_exportacion = $dato_export->id_dato_exportacion;
                            $newPedDatoExp->valor = $dato_export->valor;
                            $newPedDatoExp->save();

                        }

                    }

                    Pedido::destroy($p->id_pedido);

                }

            } */

        } catch (\Exception $e) {

            $this->fail($e->getMessage().' '.$e->getFile().' '.$e->getLine());

        }

    }
}
