<?php

namespace yura\Jobs\Sincronizacion\UnidadMedida;

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
    public $unidadMedida;

    public function __construct($unidadMedida)
    {
        $this->unidadMedida = $unidadMedida;
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

                $existeUnidadMedida = Controller::objetoConsulta('unidad_medida',[
                    ['id_unidad_medida', $this->unidadMedida->id_unidad_medida]
                ])->exists();

                if(!$existeUnidadMedida){

                    Controller::conexion()->table('unidad_medida')->insert($this->unidadMedida->toArray());

                }else{

                    Controller::conexion()->table('unidad_medida')
                    ->where('id_unidad_medida', $this->unidadMedida->id_unidad_medida)->update($this->unidadMedida->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la unidad_medida ".$this->unidadMedida->nombre."\n".$this->unidadMedida);

        }
    }
}
