<?php

namespace yura\Jobs\Sincronizacion\Indicador;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreIntervaloIndicador implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $intervaloIndicador;

    public function __construct($intervaloIndicador)
    {
        $this->intervaloIndicador = $intervaloIndicador;
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

                $existeIntervaloIndicador = Controller::objetoConsulta('intervalo_indicador',[
                    ['id_intervalo_indicador', $this->intervaloIndicador->id_intervalo_indicador]
                ])->exists();

                if(!$existeIntervaloIndicador){

                    Controller::conexion()->table('intervalo_indicador')->insert($this->intervaloIndicador->toArray());

                }else{

                    Controller::conexion()->table('intervalo_indicador')
                    ->where('id_intervalo_indicador', $this->intervaloIndicador->id_intervalo_indicador)->update($this->intervaloIndicador->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el intervalo_indicador ".$this->intervaloIndicador->id_intervalo_indicador."\n".$this->intervaloIndicador);

        }
    }
}
