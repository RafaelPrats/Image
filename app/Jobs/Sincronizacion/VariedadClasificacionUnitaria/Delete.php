<?php

namespace yura\Jobs\Sincronizacion\VariedadClasificacionUnitaria;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class Delete implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    private $variedadClasificacionUnitaria;

    public function __construct($variedadClasificacionUnitaria)
    {
        $this->variedadClasificacionUnitaria = $variedadClasificacionUnitaria;
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

                Controller::objetoConsulta('variedad_clasificacion_unitaria',[
                    ['id_variedad_clasificacion_unitaria', $this->variedadClasificacionUnitaria->id_variedad_clasificacion_unitaria]
                ])->delete();

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó eliminar la variedad_clasificacion_unitaria".$this->variedadClasificacionUnitaria->id_variedad_clasificacion_unitaria."\n".$this->variedadClasificacionUnitaria);

        }
    }
}
