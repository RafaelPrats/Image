<?php

namespace yura\Jobs\Sincronizacion\Costos;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreCostoSemana implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $costoSemana;

    public function __construct($costoSemana)
    {
        $this->costoSemana = $costoSemana;
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

                $existeCostosSemana = Controller::objetoConsulta('costos_semana',[
                    ['id_costos_semana', $this->costoSemana->id_costos_semana]
                ])->exists();

                if(!$existeCostosSemana){

                    Controller::conexion()->table('costos_semana')->insert($this->costoSemana->toArray());

                }else{

                    Controller::conexion()->table('costos_semana')
                    ->where('id_costos_semana', $this->costoSemana->id_costos_semana)->update($this->costoSemana->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el costos_semana ".$this->costoSemana->id_costos_semana."\n".$this->costoSemana);

        }
    }
}
