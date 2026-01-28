<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StorePedidoModificacion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */


    public $tries = 2;
    public $pedidoModificacion;

    public function __construct($pedidoModificacion)
    {
        $this->pedidoModificacion = $pedidoModificacion;
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

                $existePedidoModificacion = Controller::objetoConsulta('pedido_modificacion',[
                    ['id_pedido_modificacion', $this->pedidoModificacion->id_pedido_modificacion]
                ])->exists();

                if(!$existePedidoModificacion){

                    Controller::conexion()->table('pedido_modificacion')->insert($this->pedidoModificacion->toArray());

                }else{

                    Controller::conexion()->table('pedido_modificacion')
                    ->where('id_pedido_modificacion', $this->pedidoModificacion->id_pedido_modificacion)->update($this->pedidoModificacion->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el pedido_modificacion ".$this->pedidoModificacion->id_pedido_modificacion."\n".$this->pedidoModificacion);

        }
    }
}
