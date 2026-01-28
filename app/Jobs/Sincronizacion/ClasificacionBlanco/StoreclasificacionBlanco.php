<?php

namespace yura\Jobs\Sincronizacion\ClasificacionBlanco;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreclasificacionBlanco implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $clasificacionBlanco;

    public function __construct($clasificacionBlanco)
    {
        $this->clasificacionBlanco = $clasificacionBlanco;
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

                $existeClasificacionBlanco = Controller::objetoConsulta('clasificacion_blanco',[
                    ['id_clasificacion_blanco', $this->clasificacionBlanco->id_clasificacion_blanco]
                ])->exists();

                if(!$existeClasificacionBlanco){

                    Controller::conexion()->table('clasificacion_blanco')->insert($this->clasificacionBlanco->toArray());

                }else{

                    Controller::conexion()->table('clasificacion_blanco')
                    ->where('id_clasificacion_blanco', $this->clasificacionBlanco->id_clasificacion_blanco)->update($this->clasificacionBlanco->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la clasificacion_blanco ".$this->clasificacionBlanco->id_clasificacion_blanco."\n".$this->clasificacionBlanco);

        }
    }
}
