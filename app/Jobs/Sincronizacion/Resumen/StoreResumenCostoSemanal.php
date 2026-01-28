<?php

namespace yura\Jobs\Sincronizacion\Resumen;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreResumenCostoSemanal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $resumenCostoSemanal;

    public function __construct($resumenCostoSemanal)
    {
        $this->resumenCostoSemanal = $resumenCostoSemanal;
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

                $existeResumenCostoSemanal = Controller::objetoConsulta('resumen_costos_semanal',[
                    ['id_resumen_costos_semanal', $this->resumenCostoSemanal->id_resumen_costos_semanal]
                ])->exists();

                if(!$existeResumenCostoSemanal){

                    Controller::conexion()->table('resumen_costos_semanal')->insert($this->resumenCostoSemanal->toArray());

                }else{

                    Controller::conexion()->table('resumen_costos_semanal')
                    ->where('id_resumen_costos_semanal', $this->resumenCostoSemanal->id_resumen_costos_semanal)->update($this->resumenCostoSemanal->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el resumen_costos_semanal ".$this->resumenCostoSemanal->id_resumen_costos_semanal."\n".$this->resumenCostoSemanal);

        }
    }
}
