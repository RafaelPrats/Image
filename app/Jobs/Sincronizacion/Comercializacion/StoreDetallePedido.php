<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;
use yura\Modelos\Pedido;

class StoreDetallePedido implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $detallePedido;

    public function __construct($detallePedido)
    {
        $this->detallePedido = $detallePedido;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(Controller::checkPing()){

            Controller::conexion()->transaction(function() {

                $existePedido =  Pedido::find($this->detallePedido->id_pedido);

                if(isset($existePedido)){

                    $existeDetallePedido = Controller::objetoConsulta('detalle_pedido',[
                        ['id_detalle_pedido', $this->detallePedido->id_detalle_pedido]
                    ])->exists();

                    $data = $this->detallePedido->toArray();
                    unset($data['cliente_especificacion']);
                    unset($data['agencia_carga']);
                    unset($data['pedido']);
                    unset($data['marcaciones']);
                    unset($data['detalle_pedido_dato_exportacion']);

                    if(!$existeDetallePedido){

                        Controller::conexion()->table('detalle_pedido')->insert($data);

                    }else{

                        Controller::conexion()->table('detalle_pedido')
                        ->where('id_detalle_pedido', $this->detallePedido->id_detalle_pedido)->update($data);

                    }

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el detalle_pedido ".$this->detallePedido->id_detalle_pedido."\n".$this->detallePedido);

        }
    }
}
