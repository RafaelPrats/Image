<?php

namespace yura\Jobs;

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

class DividirMarcacionesStandigs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $cpeOldPedido;
    public $detallePedido;
    public $idNewPedido;
    public $variedades;
    public $marcaciones;
    public $timeout = 216000;

    public function __construct($cpeOldPedido,$detallePedido,$variedades,$marcaciones)
    {
        $this->cpeOldPedido = $cpeOldPedido;
        $this->detallePedido = $detallePedido;
        $this->variedades = $variedades;
        $this->marcaciones = $marcaciones;
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

            $pedido = Pedido::find($this->detallePedido[0]['id_pedido']);

            $pedidos = Pedido::where([
                ['pedido.tipo_pedido', 'STANDING ORDER'],
                ['pedido.fecha_pedido','>',$pedido->fecha_pedido]
            ])->join('detalle_pedido as dp',function($j) {
                $j->on('pedido.id_pedido','dp.id_pedido')->whereIn('dp.id_cliente_especificacion',$this->cpeOldPedido);
            })->leftJoin('detallepedido_datoexportacion as dpde',function($j){
                $j->on('dp.id_detalle_pedido','dpde.id_detalle_pedido');
                if(count($this->marcaciones)){
                    $j->whereIn('dpde.id_dato_exportacion',array_column($this->marcaciones,'id_dato_exportacion'))
                    ->whereIn('dpde.valor',array_column($this->marcaciones,'valor'));
                }
            })->join('cliente_pedido_especificacion as cpe',function($j) use($pedido){
                $j->on('cpe.id_cliente_pedido_especificacion','dp.id_cliente_especificacion')->where('cpe.id_cliente',$pedido->id_cliente);
            })->select('pedido.fecha_pedido','pedido.id_pedido')->distinct()->get();

            foreach($pedidos as $p){

                $idPedido = Pedido::orderBy('id_pedido','desc')->first();
                $empresa = ConfiguracionEmpresa::All()->where('estado',true)->first();

                $newPedido = new Pedido;
                $newPedido->id_pedido= $idPedido->id_pedido+1;
                $newPedido->fecha_pedido = $p->fecha_pedido;
                $newPedido->id_cliente = $pedido->id_cliente;
                $newPedido->estado = $pedido->estado;
                $newPedido->empaquetado = $pedido->empaquetado;
                $newPedido->variedad = implode('|',$this->variedades);
                $newPedido->tipo_especificacion = $pedido->tipo_especificacion;
                $newPedido->confirmado = $pedido->confirmado;
                $newPedido->tipo_pedido = $pedido->tipo_pedido;
                $newPedido->id_configuracion_empresa = $pedido->id_configuracion_empresa;
                //$newPedido->etiqueta_impresa = $p->etiqueta_impresa;
                $newPedido->fecha_registro = now()->toDateTimeString();
                //$newPedido->packing = $empresa->numero_packing+1;
                $newPedido->save();

                //$empresa->numero_packing = $newPedido->packing;
                $empresa->save();

                $envio = $pedido->envios[0];

                $idEnvio = Envio::orderBy('id_envio','desc')->first();

                $newEnvio = new Envio;
                $newEnvio->id_envio= $idEnvio->id_envio+1;
                $newEnvio->id_pedido= $newPedido->id_pedido;
                $newEnvio->fecha_envio= $newPedido->fecha_pedido;
                $newEnvio->fecha_registro= now()->toDateTimeString();
                $newEnvio->estado= $envio->estado;
                $newEnvio->guia_madre= $envio->guia_madre;
                $newEnvio->guia_hija= $envio->guia_hija;
                $newEnvio->dae= $envio->dae;
                $newEnvio->email= $envio->email;
                $newEnvio->telefono= $envio->telefono;
                $newEnvio->direccion= $envio->direccion;
                $newEnvio->codigo_pais= $envio->codigo_pais;
                $newEnvio->almacen= $envio->almacen;
                $newEnvio->codigo_dae= $envio->codigo_dae;
                $newEnvio->id_consignatario= $envio->id_consignatario;
                $newEnvio->save();

                $detEnvio = $envio->detalles[0];

                $newDetEnv = new DetalleEnvio;

                $idDetEnv = DetalleEnvio::orderBy('id_detalle_envio','desc')->first();
                $newDetEnv->id_detalle_envio = $idDetEnv->id_detalle_envio+1;
                $newDetEnv->id_envio = $newEnvio->id_envio;
                $newDetEnv->id_especificacion = $detEnvio->id_especificacion;
                $newDetEnv->id_aerolinea = $detEnvio->id_aerolinea;
                $newDetEnv->cantidad = $detEnvio->cantidad;
                $newDetEnv->envio = $detEnvio->envio;
                $newDetEnv->form = $detEnvio->form;
                $newDetEnv->save();

                foreach($this->detallePedido as $detPed){

                    $oldDetPed = DetallePedido::find($detPed['id_det_ped']);

                    if(in_array($oldDetPed->id_cliente_especificacion,$p->detalles->pluck('id_cliente_especificacion')->toArray())){

                        foreach($p->detalles as $detallePedido){

                            if($oldDetPed->id_cliente_especificacion == $detallePedido->id_cliente_especificacion){

                                info('coincide el cliente_especificacion');
                                info('packing '.$newPedido->packing);

                                $newDetallePedido = DetallePedido::find($detallePedido->id_detalle_pedido);
                                $newDetallePedido->id_pedido = $newPedido->id_pedido;
                                $newDetallePedido->fecha_registro = now()->toDateTimeString();
                                $newDetallePedido->save();
                                break;

                            }

                        }

                    }

                }

            }
        }catch(\Exception $e){

            info("ERROR DIVIDIENDO LAS MARCACIONES");
            info($e->getMessage()." Linea ".$e->getLine()." archivo ".$e->getFile());
            $this->fail($e->getMessage()." Linea ".$e->getLine()." archivo ".$e->getFile());

        }

    }
}
