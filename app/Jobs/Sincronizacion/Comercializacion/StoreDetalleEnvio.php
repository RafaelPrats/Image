<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;
use yura\Modelos\Envio;

class StoreDetalleEnvio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $detalleEnvio;

    public function __construct($detalleEnvio)
    {
        $this->detalleEnvio = $detalleEnvio;
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

                $existeEnvio = Envio::find($this->detalleEnvio->id_envio);

                if(isset($existeEnvio)){

                    $existeDetalleEnvio = Controller::objetoConsulta('detalle_envio',[
                        ['id_detalle_envio', $this->detalleEnvio->id_detalle_envio]
                    ])->exists();

                    if(!$existeDetalleEnvio){

                        Controller::conexion()->table('detalle_envio')->insert($this->detalleEnvio->toArray());

                    }else{

                        Controller::conexion()->table('detalle_envio')
                        ->where('id_detalle_envio', $this->detalleEnvio->id_detalle_envio)->update($this->detalleEnvio->toArray());

                    }
                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el detalle_envio ".$this->detalleEnvio->id_detalle_envio."\n".$this->detalleEnvio);

        }
    }
}
