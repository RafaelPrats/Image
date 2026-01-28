<?php

namespace yura\Jobs\Sincronizacion\ClasificacionVerde;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class DeleteDetalleClasificacionVerde implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    private $detalleClasificacionVerde;

    public function __construct($detalleClasificacionVerde)
    {
        $this->detalleClasificacionVerde = $detalleClasificacionVerde;
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

                Controller::objetoConsulta('clasificacion_verde',[
                    ['id_detalle_clasificacion_verde', $this->detalleClasificacionVerde->id_detalle_clasificacion_verde]
                ])->delete();

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó eliminar el detalle_clasificacion_verde".$this->detalleClasificacionVerde->id_detalle_clasificacion_verde."\n".$this->detalleClasificacionVerde);

        }
    }

}
