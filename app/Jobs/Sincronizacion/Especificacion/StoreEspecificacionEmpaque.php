<?php

namespace yura\Jobs\Sincronizacion\Especificacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreEspecificacionEmpaque implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $especificacionEmpaque;

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

                $existeEspecificacionEmpaque = Controller::objetoConsulta('especificacion_empaque',[
                    ['id_especificacion_empaque', $this->especificacionEmpaque->id_especificacion_empaque]
                ])->exists();

                if(!$existeEspecificacionEmpaque){

                    Controller::conexion()->table('especificacion_empaque')->insert($this->especificacionEmpaque->toArray());

                }else{

                    Controller::conexion()->table('especificacion_empaque')
                    ->where('id_especificacion_empaque', $this->especificacionEmpaque->id_especificacion_empaque)->update($this->especificacionEmpaque->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la especificacion_empaque ".$this->especificacionEmpaque->id_especificacion_empaque."\n".$this->especificacionEmpaque);

        }
    }
}
