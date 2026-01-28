<?php

namespace yura\Jobs\Sincronizacion\ProyNintanga;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreDistribucionMixtosSemana implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $distribucionMixtosSemana;

    public function __construct($distribucionMixtosSemana)
    {
        $this->distribucionMixtosSemana = $distribucionMixtosSemana;
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

                $existeDistribucionMixto = Controller::objetoConsulta('distribucion_mixtos_semana',[
                    ['id_distribucion_mixtos_semana', $this->distribucionMixtosSemana->id_distribucion_mixtos_semana]
                ])->exists();

                if(!$existeDistribucionMixto){

                    Controller::conexion()->table('distribucion_mixtos_semana')->insert($this->distribucionMixtosSemana->toArray());

                }else{

                    Controller::conexion()->table('distribucion_mixtos_semana')
                    ->where('id_distribucion_mixtos_semana', $this->distribucionMixtosSemana->id_distribucion_mixtos_semana)->update($this->distribucionMixtosSemana->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la distribucion_mixtos_semana ".$this->distribucionMixtosSemana->id_distribucion_mixtos_semana."\n".$this->distribucionMixtosSemana);

        }
    }
}
