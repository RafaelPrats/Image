<?php

namespace yura\Jobs\Sincronizacion\ProyNintanga;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreDiasCosechaSemana implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $diasCosechaSemana;

    public function __construct($diasCosechaSemana)
    {
        $this->diasCosechaSemana = $diasCosechaSemana;
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

                $existeDistribucionMixto = Controller::objetoConsulta('dias_cosecha_semana',[
                    ['id_dias_cosecha_semana', $this->diasCosechaSemana->id_dias_cosecha_semana]
                ])->exists();

                if(!$existeDistribucionMixto){

                    Controller::conexion()->table('dias_cosecha_semana')->insert($this->diasCosechaSemana->toArray());

                }else{

                    Controller::conexion()->table('dias_cosecha_semana')
                    ->where('id_dias_cosecha_semana', $this->diasCosechaSemana->id_dias_cosecha_semana)->update($this->diasCosechaSemana->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la dias_cosecha_semana ".$this->diasCosechaSemana->id_dias_cosecha_semana."\n".$this->diasCosechaSemana);

        }
    }
}
