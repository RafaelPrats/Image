<?php

namespace yura\Jobs\Sincronizacion\Cliente;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class DeleteClientePedidoEspecificacion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $clientePedidoEspecificacion;

    public function __construct($clientePedidoEspecificacion)
    {
        $this->clientePedidoEspecificacion = $clientePedidoEspecificacion;
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

                Controller::objetoConsulta('cliente_pedido_especificacion',[
                    ['id_cliente_pedido_especificacion', $this->clientePedidoEspecificacion->id_cliente_pedido_especificacion]
                ])->delete();

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó eliminar el cliente_pedido_especificacion".$this->clientePedidoEspecificacion->id_cliente_pedido_especificacion."\n".$this->clientePedidoEspecificacion);

        }
    }
}
