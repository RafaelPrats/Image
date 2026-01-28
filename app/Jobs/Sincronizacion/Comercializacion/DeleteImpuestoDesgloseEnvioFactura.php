<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class DeleteImpuestoDesgloseEnvioFactura implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $impuestoDesgloseEnvioFactura;

    public function __construct($impuestoDesgloseEnvioFactura)
    {
        $this->impuestoDesgloseEnvioFactura = $impuestoDesgloseEnvioFactura;
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

                Controller::objetoConsulta('impuesto_desglose_envio_factura',[
                    ['id_impuesto_desglose_envio_factura', $this->impuestoDesgloseEnvioFactura->id_impuesto_desglose_envio_factura]
                ])->delete();

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó eliminar el impuesto_desglose_envio_factura".$this->impuestoDesgloseEnvioFactura->id_impuesto_desglose_envio_factura."\n".$this->impuestoDesgloseEnvioFactura);

        }
    }
}
