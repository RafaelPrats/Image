<?php

namespace yura\Jobs\Sincronizacion\Especificacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreDetalleEspecificacionEmpaque implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $detalleEspecificacionEmpaque;

    public function __construct($detalleEspecificacionEmpaque)
    {
        $this->detalleEspecificacionEmpaque = $detalleEspecificacionEmpaque;
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

                $existeEspecificacion = Controller::objetoConsulta('detalle_especificacionempaque',[
                    ['id_detalle_especificacionempaque', $this->detalleEspecificacionEmpaque->id_detalle_especificacionempaque]
                ])->exists();

                if(!$existeEspecificacion){

                    Controller::conexion()->table('detalle_especificacionempaque')->insert($this->detalleEspecificacionEmpaque->toArray());

                }else{

                    Controller::conexion()->table('detalle_especificacionempaque')
                    ->where('id_detalle_especificacionempaque', $this->detalleEspecificacionEmpaque->id_detalle_especificacionempaque)
                    ->update($this->detalleEspecificacionEmpaque->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el detalle_especificacionempaque ".$this->detalleEspecificacionEmpaque->id_detalle_especificacionempaque."\n".$this->detalleEspecificacionEmpaque);

        }
    }
}
