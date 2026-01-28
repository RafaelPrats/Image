<?php

namespace yura\Jobs\Sincronizacion\ProyeccionModulo;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreProyeccionModulo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $proyeccionModulo;

    public function __construct($proyeccionModulo)
    {
        $this->proyeccionModulo = $proyeccionModulo;
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

                $existeProyeccionModulo = Controller::objetoConsulta('proyeccion_modulo',[
                    ['id_proyeccion_modulo', $this->proyeccionModulo->id_proyeccion_modulo]
                ])->exists();

                if(!$existeProyeccionModulo){

                    Controller::conexion()->table('proyeccion_modulo')->insert($this->proyeccionModulo->toArray());

                }else{

                    Controller::conexion()->table('proyeccion_modulo')
                    ->where('id_proyeccion_modulo', $this->proyeccionModulo->id_proyeccion_modulo)->update($this->proyeccionModulo->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la proyeccion_modulo ".$this->proyeccionModulo->id_proyeccion_modulo."\n".$this->proyeccionModulo);

        }
    }
}
