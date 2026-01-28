<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreImpuestoDetalleFactura implements ShouldQueue
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

                $existeImpuestoDetalleFactura = Controller::objetoConsulta('impuesto_detalle_factura',[
                    ['id_impuesto_detalle_factura', $this->impuestoDetalleFactura->id_impuesto_detalle_factura]
                ])->exists();

                if(!$existeImpuestoDetalleFactura){

                    Controller::conexion()->table('impuesto_detalle_factura')->insert($this->impuestoDetalleFactura->toArray());

                }else{

                    Controller::conexion()->table('impuesto_detalle_factura')
                    ->where('id_impuesto_detalle_factura', $this->impuestoDetalleFactura->id_impuesto_detalle_factura)->update($this->impuestoDetalleFactura->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el impuesto_detalle_factura ".$this->impuestoDetalleFactura->id_impuesto_detalle_factura."\n".$this->impuestoDetalleFactura);

        }
    }
}
