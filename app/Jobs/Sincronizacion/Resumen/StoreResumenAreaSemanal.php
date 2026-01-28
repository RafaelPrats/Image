<?php

namespace yura\Jobs\Sincronizacion\Resumen;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreResumenAreaSemanal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $resumenAreaSemanal;

    public function __construct($resumenAreaSemanal)
    {
        $this->resumenAreaSemanal = $resumenAreaSemanal;
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

                $existeResumenAreaSemanal = Controller::objetoConsulta('resumen_area_semanal',[
                    ['id_resumen_area_semanal', $this->resumenAreaSemanal->id_resumen_area_semanal]
                ])->exists();

                if(!$existeResumenAreaSemanal){

                    Controller::conexion()->table('resumen_area_semanal')->insert($this->resumenAreaSemanal->toArray());

                }else{

                    Controller::conexion()->table('resumen_area_semanal')
                    ->where('id_resumen_area_semanal', $this->resumenAreaSemanal->id_resumen_area_semanal)->update($this->resumenAreaSemanal->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el resumen_area_semanal ".$this->resumenAreaSemanal->id_resumen_area_semanal."\n".$this->resumenAreaSemanal);

        }
    }
}
