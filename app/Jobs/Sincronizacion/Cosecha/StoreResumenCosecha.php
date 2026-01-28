<?php

namespace yura\Jobs\Sincronizacion\Cosecha;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreResumenCosecha implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $resumenSemanaCosecha;

    public function __construct($resumenSemanaCosecha)
    {
        $this->resumeSemanaCosecha = $resumenSemanaCosecha;
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

                $existeLote = Controller::objetoConsulta('resumen_semana_cosecha',[
                    ['id_resumen_semana_cosecha', $this->resumeSemanaCosecha->id_resumen_semana_cosecha]
                ])->exists();

                if(!$existeLote){

                    Controller::conexion()->table('resumen_semana_cosecha')->insert($this->resumeSemanaCosecha->toArray());

                }else{

                    Controller::conexion()->table('resumen_semana_cosecha')
                    ->where('id_resumen_semana_cosecha', $this->resumeSemanaCosecha->id_resumen_semana_cosecha)->update($this->resumeSemanaCosecha->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el resumen_semana_cosecha ".$this->resumeSemanaCosecha->id_resumen_semana_cosecha."\n".$this->resumeSemanaCosecha);

        }
    }
}
