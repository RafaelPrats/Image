<?php

namespace yura\Jobs\Sincronizacion\Gastos;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreOtrosGastos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $otrosGastos;

    public function __construct($otrosGastos)
    {
        $this->otrosGastos = $otrosGastos;
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

                $existOtrosGastos = Controller::objetoConsulta('otros_gastos',[
                    ['id_otros_gastos', $this->otrosGastos->id_otros_gastos]
                ])->exists();

                if(!$existOtrosGastos){

                    Controller::conexion()->table('otros_gastos')->insert($this->otrosGastos->toArray());

                }else{

                    Controller::conexion()->table('otros_gastos')
                    ->where('id_otros_gastos', $this->otrosGastos->id_otros_gastos)->update($this->otrosGastos->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el otros_gastos ".$this->otrosGastos->id_otros_gastos."\n".$this->otrosGastos);

        }
    }
}
