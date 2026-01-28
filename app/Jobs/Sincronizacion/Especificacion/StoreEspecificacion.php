<?php

namespace yura\Jobs\Sincronizacion\Especificacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreEspecificacion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $especificacion;

    public function __construct($especificacion)
    {
        $this->especificacion = $especificacion;
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

                $existeEspecificacion = Controller::objetoConsulta('especificacion',[
                    ['id_especificacion', $this->especificacion->id_especificacion]
                ])->exists();

                if(!$existeEspecificacion){

                    Controller::conexion()->table('especificacion')->insert($this->especificacion->toArray());

                }else{

                    Controller::conexion()->table('especificacion')
                    ->where('id_especificacion', $this->especificacion->id_especificacion)->update($this->especificacion->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la especificacion ".$this->especificacion->id_especificacion."\n".$this->especificacion);

        }
    }
}
