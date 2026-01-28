<?php

namespace yura\Jobs\Sincronizacion\Indicador;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreIndicadorSemana implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $indicadorSemana;

    public function __construct($indicadorSemana)
    {
        $this->indicadorSemana = $indicadorSemana;
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

                $existeIndicadorSemana = Controller::objetoConsulta('indicador_semana',[
                    ['id_indicador_semana', $this->indicadorSemana->id_indicador_semana]
                ])->exists();

                if(!$existeIndicadorSemana){

                    Controller::conexion()->table('indicador_semana')->insert($this->indicadorSemana->toArray());

                }else{

                    Controller::conexion()->table('indicador_semana')
                    ->where('id_indicador_semana', $this->indicadorSemana->id_indicador_semana)->update($this->indicadorSemana->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el indicador_semana ".$this->indicadorSemana->id_indicador_semana."\n".$this->indicadorSemana);

        }
    }
}
