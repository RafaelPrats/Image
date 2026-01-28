<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class DeletePedido implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $pedido;

    public function __construct($pedido)
    {
        $this->pedido = $pedido;
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

                Controller::objetoConsulta('pedido',[
                    ['id_pedido', $this->pedido->id_pedido]
                ])->delete();

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó eliminar el pedido".$this->pedido->id_pedido."\n".$this->pedido);

        }
    }
}
