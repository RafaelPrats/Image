<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreTransportista implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $transportista;

    public function __construct($transportista)
    {
        $this->transportista = $transportista;
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

                $existeTransportista = Controller::objetoConsulta('transportista',[
                    ['id_transportista', $this->transportista->id_transportista]
                ])->exists();

                if(!$existeTransportista){

                    Controller::conexion()->table('transportista')->insert($this->transportista->toArray());

                }else{

                    Controller::conexion()->table('transportista')
                    ->where('id_transportista', $this->transportista->id_transportista)->update($this->transportista->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el transportista ".$this->transportista->id_transportista."\n".$this->transportista);

        }
    }
}
