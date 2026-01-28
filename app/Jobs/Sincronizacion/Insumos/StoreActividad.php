<?php

namespace yura\Jobs\Sincronizacion\Insumos;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreActividad implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $actividad;

    public function __construct($actividad)
    {
        $this->actividad = $actividad;
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

                $existeActividad = Controller::objetoConsulta('actividad',[
                    ['id_actividad', $this->actividad->id_actividad]
                ])->exists();

                if(!$existeActividad){

                    Controller::conexion()->table('actividad')->insert($this->actividad->toArray());

                }else{

                    Controller::conexion()->table('actividad')
                    ->where('id_actividad', $this->actividad->id_actividad)->update($this->actividad->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la actividad ".$this->actividad->id_actividad."\n".$this->actividad);

        }
    }
}
