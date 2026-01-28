<?php

namespace yura\Jobs\Sincronizacion\Costos;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreActividadProducto implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $actividadProducto;

    public function __construct($actividadProducto)
    {
        $this->actividadProducto = $actividadProducto;
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

                $existeActividadProducto = Controller::objetoConsulta('actividad_producto',[
                    ['id_actividad_producto', $this->actividadProducto->id_actividad_producto]
                ])->exists();

                if(!$existeActividadProducto){

                    Controller::conexion()->table('actividad_producto')->insert($this->actividadProducto->toArray());

                }else{

                    Controller::conexion()->table('actividad_producto')
                    ->where('id_actividad_producto', $this->actividadProducto->id_actividad_producto)->update($this->actividadProducto->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la actividad_producto ".$this->actividadProducto->id_actividad_producto."\n".$this->actividadProducto);

        }
    }
}
