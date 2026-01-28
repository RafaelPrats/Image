<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreDetalleFactura implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $detalleFactura;

    public function __construct($detalleFactura)
    {
        $this->detalleFactura = $detalleFactura;
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

                $existeDetalleFactura = Controller::objetoConsulta('detalle_factura',[
                    ['id_detalle_factura', $this->detalleFactura->id_detalle_factura]
                ])->exists();

                if(!$existeDetalleFactura){

                    Controller::conexion()->table('detalle_factura')->insert($this->detalleFactura->toArray());

                }else{

                    Controller::conexion()->table('detalle_factura')
                    ->where('id_detalle_factura', $this->detalleFactura->id_detalle_factura)->update($this->detalleFactura->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el detalle_factura ".$this->detalleFactura->id_detalle_factura."\n".$this->detalleFactura);

        }
    }
}
