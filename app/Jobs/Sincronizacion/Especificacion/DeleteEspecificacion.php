<?php

namespace yura\Jobs\Sincronizacion\Especificacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class DeleteEspecificacion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    private $especificacionEmpaque;

    public function __construct($especificacionEmpaque)
    {
        $this->especificacionEmpaque = $especificacionEmpaque;
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

                Controller::objetoConsulta('especificacion_empaque',[
                    ['id_especificacion_empaque', $this->especificacionEmpaque->id_especificacion_empaque]
                ])->delete();

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó eliminar la especificacion".$this->especificacionEmpaque->id_especificacion_empaque."\n".$this->especificacionEmpaque);

        }
    }
}
