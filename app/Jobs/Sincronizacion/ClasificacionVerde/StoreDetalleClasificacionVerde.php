<?php

namespace yura\Jobs\Sincronizacion\ClasificacionVerde;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreDetalleClasificacionVerde implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $detalleClasificacionVerde;

    public function __construct($detalleClasificacionVerde)
    {
        $this->detalleClasificacionVerde= $detalleClasificacionVerde;
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

                $existeClasificacionVerde = Controller::objetoConsulta('detalle_clasificacion_verde',[
                    ['id_detalle_clasificacion_verde', $this->detalleClasificacionVerde->id_detalle_clasificacion_verde]
                ])->exists();

                if(!$existeClasificacionVerde){

                    Controller::conexion()->table('detalle_clasificacion_verde')->insert($this->detalleClasificacionVerde->toArray());

                }else{

                    Controller::conexion()->table('detalle_clasificacion_verde')
                    ->where('id_detalle_clasificacion_verde', $this->detalleClasificacionVerde->id_detalle_clasificacion_verde)->update($this->detalleClasificacionVerde->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el detalle_clasificacion_verde ".$this->detalleClasificacionVerde->id_detalle_clasificacion_verde."\n".$this->detalleClasificacionVerde);

        }
    }
}
