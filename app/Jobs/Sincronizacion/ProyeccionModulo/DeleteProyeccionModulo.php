<?php

namespace yura\Jobs\Sincronizacion\ProyeccionModulo;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class DeleteProyeccionModulo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    private $proyeccionModulo;

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

                Controller::objetoConsulta('proyeccion_modulo',[
                    ['id_proyeccion_modulo', $this->proyeccionModulo->id_proyeccion_modulo]
                ])->delete();

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó eliminar la proyeccion_modulo".$this->proyeccionModulo->id_proyeccion_modulo."\n".$this->proyeccionModulo);

        }
    }
}
