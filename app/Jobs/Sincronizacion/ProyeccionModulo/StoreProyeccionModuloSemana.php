<?php

namespace yura\Jobs\Sincronizacion\ProyeccionModulo;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreProyeccionModuloSemana implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $proyeccionModuloSemana;

    public function __construct($proyeccionModuloSemana)
    {
        $this->proyeccionModuloSemana = $proyeccionModuloSemana;
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

                $existeProyeccionModuloSemana = Controller::objetoConsulta('proyeccion_modulo_semana',[
                    ['id_proyeccion_modulo_semana', $this->proyeccionModuloSemana->id_proyeccion_modulo_semana]
                ])->exists();

                if(!$existeProyeccionModuloSemana){

                    Controller::conexion()->table('proyeccion_modulo_semana')->insert($this->proyeccionModuloSemana->toArray());

                }else{

                    Controller::conexion()->table('proyeccion_modulo_semana')
                    ->where('id_proyeccion_modulo_semana', $this->proyeccionModuloSemana->id_proyeccion_modulo_semana)->update($this->proyeccionModuloSemana->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la proyeccion_modulo_semana ".$this->proyeccionModuloSemana->id_proyeccion_modulo_semana."\n".$this->proyeccionModuloSemana);

        }
    }
}
