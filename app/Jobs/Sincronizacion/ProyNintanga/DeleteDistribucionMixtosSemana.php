<?php

namespace yura\Jobs\Sincronizacion\ProyNintanga;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class DeleteDistribucionMixtosSemana implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $distribucionMixtoSemana;

    public function __construct($distribucionMixtoSemana)
    {
        $this->distribucionMixtoSemana = $distribucionMixtoSemana;
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

                Controller::objetoConsulta('distribucion_mixtos_semana',[
                    ['id_distribucion_mixtos_semana', $this->distribucionMixtoSemana->id_distribucion_mixtos_semana]
                ])->delete();

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó eliminar la distribucion_mixtos_semana".$this->distribucionMixtoSemana->id_distribucion_mixtos_semana."\n".$this->distribucionMixtoSemana);

        }
    }
}
