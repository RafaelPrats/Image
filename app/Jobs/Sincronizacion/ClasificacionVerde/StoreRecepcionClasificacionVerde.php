<?php

namespace yura\Jobs\Sincronizacion\ClasificacionVerde;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreRecepcionClasificacionVerde implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $recepcionClasificacionVerde;

    public function __construct($recepcionClasificacionVerde)
    {
        $this->recepcionClasificacionVerde = $recepcionClasificacionVerde;
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

                $existeRecepcionClasificacionVerde = Controller::objetoConsulta('recepcion_clasificacion_verde',[
                    ['id_recepcion_clasificacion_verde', $this->recepcionClasificacionVerde->id_recepcion_clasificacion_verde]
                ])->exists();

                if(!$existeRecepcionClasificacionVerde){

                    Controller::conexion()->table('recepcion_clasificacion_verde')->insert($this->recepcionClasificacionVerde->toArray());

                }else{

                    Controller::conexion()->table('recepcion_clasificacion_verde')
                    ->where('id_recepcion_clasificacion_verde', $this->recepcionClasificacionVerde->id_recepcion_clasificacion_verde)->update($this->recepcionClasificacionVerde->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la recepcion_clasificacion_verde ".$this->recepcionClasificacionVerde->id_recepcion_clasificacion_verde."\n".$this->recepcionClasificacionVerde);

        }
    }
}
