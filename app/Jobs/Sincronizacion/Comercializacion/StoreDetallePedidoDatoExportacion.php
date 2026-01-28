<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreDetallePedidoDatoExportacion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $detallePedidoDatoExportacion;

    public function __construct($detallePedidoDatoExportacion)
    {
        $this->detallePedidoDatoExportacion = $detallePedidoDatoExportacion;
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

                $existeDetallePedidoDatoExportacion = Controller::objetoConsulta('detallepedido_datoexportacion',[
                    ['id_detallepedido_datoexportacion', $this->detallePedidoDatoExportacion->id_detallepedido_datoexportacion]
                ])->exists();

                $existeDetallePedido = Controller::objetoConsulta('detalle_pedido',[
                    ['id_detalle_pedido', $this->detallePedidoDatoExportacion->id_detalle_pedido]
                ])->exists();

                if($existeDetallePedido){

                    if(!$existeDetallePedidoDatoExportacion){

                        Controller::conexion()->table('detallepedido_datoexportacion')->insert($this->detallePedidoDatoExportacion->toArray());

                    }else{

                        Controller::conexion()->table('detallepedido_datoexportacion')
                        ->where('id_detallepedido_datoexportacion', $this->detallePedidoDatoExportacion->id_detallepedido_datoexportacion)->update($this->detallePedidoDatoExportacion->toArray());

                    }

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el detallepedido_datoexportacion ".$this->detallePedidoDatoExportacion->id_detallepedido_datoexportacion."\n".$this->detallePedidoDatoExportacion);

        }
    }
}
