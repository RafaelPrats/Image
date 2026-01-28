<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreAerolinea implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $aerolinea;

    public function __construct($aerolinea)
    {
        $this->aerolinea = $aerolinea;
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

                $existeAerolinea = Controller::objetoConsulta('aerolinea',[
                    ['id_aerolinea', $this->aerolinea->id_aerolinea]
                ])->exists();

                if(!$existeAerolinea){

                    Controller::conexion()->table('aerolinea')->insert($this->aerolinea->toArray());

                }else{

                    Controller::conexion()->table('aerolinea')
                    ->where('id_aerolinea', $this->aerolinea->id_aerolinea)->update($this->aerolinea->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la aerolinea ".$this->aerolinea->id_aerolinea."\n".$this->aerolinea);

        }
    }
}
