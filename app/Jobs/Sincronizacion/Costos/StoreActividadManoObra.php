<?php

namespace yura\Jobs\Sincronizacion\Costos;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreActividadManoObra implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $actividadManoObra;

    public function __construct($actividadManoObra)
    {
        $this->actividadManoObra = $actividadManoObra;
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

                $existeActividadManoObra = Controller::objetoConsulta('actividad_mano_obra',[
                    ['id_actividad_mano_obra', $this->actividadManoObra->id_actividad_mano_obra]
                ])->exists();

                if(!$existeActividadManoObra){

                    Controller::conexion()->table('actividad_mano_obra')->insert($this->actividadManoObra->toArray());

                }else{

                    Controller::conexion()->table('actividad_mano_obra')
                    ->where('id_actividad_mano_obra', $this->actividadManoObra->id_actividad_mano_obra)->update($this->actividadManoObra->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la actividad_mano_obra ".$this->actividadManoObra->id_actividad_mano_obra."\n".$this->actividadManoObra);

        }
    }
}
