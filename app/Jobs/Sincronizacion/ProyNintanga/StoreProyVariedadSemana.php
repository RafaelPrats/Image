<?php

namespace yura\Jobs\Sincronizacion\ProyNintanga;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreProyVariedadSemana implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $proyVariedadSemana;

    public function __construct($proyVariedadSemana)
    {
        $this->proyVariedadSemana = $proyVariedadSemana;
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

                $existeProyVariedadSemana = Controller::objetoConsulta('proy_variedad_semana',[
                    ['id_proy_variedad_semana', $this->proyVariedadSemana->id_proy_variedad_semana]
                ])->exists();

                if(!$existeProyVariedadSemana){

                    Controller::conexion()->table('proy_variedad_semana')->insert($this->proyVariedadSemana->toArray());

                }else{

                    Controller::conexion()->table('proy_variedad_semana')
                    ->where('id_proy_variedad_semana', $this->proyVariedadSemana->id_proy_variedad_semana)->update($this->proyVariedadSemana->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la proy_variedad_semana ".$this->proyVariedadSemana->id_proy_variedad_semana."\n".$this->proyVariedadSemana);

        }
    }
}
