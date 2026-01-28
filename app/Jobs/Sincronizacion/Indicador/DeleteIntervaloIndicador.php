<?php

namespace yura\Jobs\Sincronizacion\Indicador;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class DeleteIntervaloIndicador implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    private $intervaloIdicador;

    public function __construct($intervaloIdicador)
    {
        $this->intervaloIdicador = $intervaloIdicador;
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

                Controller::objetoConsulta('intervalo_indicador',[
                    ['id_intervalo_indicador', $this->intervaloIdicador->id_intervalo_indicador]
                ])->delete();

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó eliminar la intervalo_indicador".$this->intervaloIdicador->id_intervalo_indicador."\n".$this->intervaloIdicador);

        }
    }
}
