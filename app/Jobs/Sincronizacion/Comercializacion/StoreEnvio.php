<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;
use yura\Modelos\Pedido;

class StoreEnvio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $envio;

    public function __construct($envio)
    {
        $this->envio = $envio;
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

                $existePedido =  Pedido::find($this->envio->id_pedido);

                if(isset($existePedido)){

                    $existeenvio = Controller::objetoConsulta('envio',[
                        ['id_envio', $this->envio->id_envio]
                    ])->exists();

                    if(!$existeenvio){

                        Controller::conexion()->table('envio')->insert($this->envio->toArray());

                    }else{

                        Controller::conexion()->table('envio')
                        ->where('id_envio', $this->envio->id_envio)->update($this->envio->toArray());

                    }

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el envio ".$this->envio->id_envio."\n".$this->envio);

        }
    }
}
