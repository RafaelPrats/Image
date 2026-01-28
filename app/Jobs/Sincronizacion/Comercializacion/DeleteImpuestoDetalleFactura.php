<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class DeleteImpuestoDetalleFactura implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $impuestoDetalleFactura;

    public function __construct($impuestoDetalleFactura)
    {
        $this->impuestoDetalleFactura = $impuestoDetalleFactura;
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

                Controller::objetoConsulta('impuesto_detalle_factura',[
                    ['id_impuesto_detalle_factura', $this->impuestoDetalleFactura->id_impuesto_detalle_factura]
                ])->delete();

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó eliminar el impuesto_detalle_factura".$this->impuestoDetalleFactura->id_impuesto_detalle_factura."\n".$this->impuestoDetalleFactura);

        }
    }
}
