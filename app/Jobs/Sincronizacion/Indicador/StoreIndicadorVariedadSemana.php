<?php

namespace yura\Jobs\Sincronizacion\Indicador;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreIndicadorVariedadSemana implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $indicadorVariedadSemana;

    public function __construct($indicadorVariedadSemana)
    {
        $this->indicadorVariedadSemana = $indicadorVariedadSemana;
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

                $existeIndicadorSemana = Controller::objetoConsulta('indicador_variedad_semana',[
                    ['indicador_variedad_semana', $this->indicadorVariedadSemana->indicador_variedad_semana]
                ])->exists();

                if(!$existeIndicadorSemana){

                    Controller::conexion()->table('indicador_variedad_semana')->insert($this->indicadorVariedadSemana->toArray());

                }else{

                    Controller::conexion()->table('indicador_variedad_semana')
                    ->where('indicador_variedad_semana', $this->indicadorVariedadSemana->indicador_variedad_semana)->update($this->indicadorVariedadSemana->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el indicador_variedad_semana ".$this->indicadorVariedadSemana->indicador_variedad_semana."\n".$this->indicadorVariedadSemana);

        }
    }
}
