<?php

namespace yura\Jobs\Sincronizacion\Cosecha;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreRecepcion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $recepcion;

    public function __construct($recepcion)
    {
        $this->recepcion = $recepcion;
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

                $existeRecepcion = Controller::objetoConsulta('recepcion',[
                    ['id_recepcion', $this->recepcion->id_recepcion]
                ])->exists();

                if(!$existeRecepcion){

                    Controller::conexion()->table('recepcion')->insert($this->recepcion->toArray());

                }else{

                    Controller::conexion()->table('recepcion')
                    ->where('id_recepcion', $this->recepcion->id_recepcion)->update($this->recepcion->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la recepcion ".$this->recepcion->id_recepcion."\n".$this->recepcion);

        }
    }
}
