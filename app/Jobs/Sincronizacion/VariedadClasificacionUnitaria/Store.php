<?php

namespace yura\Jobs\Sincronizacion\VariedadClasificacionUnitaria;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class Store implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $variedadClasificacionUnitaria;

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

                $existeVariedadClasificacionUnitaria = Controller::objetoConsulta('variedad_clasificacion_unitaria',[
                    ['id_variedad_clasificacion_unitaria', $this->variedadClasificacionUnitaria->id_variedad_clasificacion_unitaria]
                ])->exists();

                if(!$existeVariedadClasificacionUnitaria){

                    Controller::conexion()->table('variedad_clasificacion_unitaria')->insert($this->variedadClasificacionUnitaria->toArray());

                }else{

                    Controller::conexion()->table('variedad_clasificacion_unitaria')
                    ->where('id_variedad_clasificacion_unitaria', $this->variedadClasificacionUnitaria->id_variedad_clasificacion_unitaria)->update($this->variedadClasificacionUnitaria->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la variedad_clasificacion_unitaria ".$this->variedadClasificacionUnitaria->id_variedad_clasificacion_unitaria."\n".$this->variedadClasificacionUnitaria);

        }
    }
}
