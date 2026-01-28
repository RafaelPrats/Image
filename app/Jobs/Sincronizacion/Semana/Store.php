<?php

namespace yura\Jobs\Sincronizacion\Semana;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class Store implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $semana;

    public function __construct($semana)
    {
        $this->semana = $semana;
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

                $existeSemana = Controller::objetoConsulta('semana',[
                    ['id_semana', $this->semana->id_semana]
                ])->exists();

                if(!$existeSemana){

                    Controller::conexion()->table('semana')->insert($this->semana->toArray());

                }else{

                    Controller::conexion()->table('semana')
                    ->where('id_semana', $this->semana->id_semana)->update($this->semana->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la semana ".$this->semana->id_semana."\n".$this->semana);

        }
    }
}
