<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreDesgloseEnvioFactura implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $desgloseEnvioFactura;

    public function __construct($desgloseEnvioFactura)
    {
        $this->desgloseEnvioFactura = $desgloseEnvioFactura;
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

                $existeDesgloseEnvioFactura = Controller::objetoConsulta('desglose_envio_factura',[
                    ['id_desglose_envio_factura', $this->desgloseEnvioFactura->id_desglose_envio_factura]
                ])->exists();

                if(!$existeDesgloseEnvioFactura){

                    Controller::conexion()->table('desglose_envio_factura')->insert($this->desgloseEnvioFactura->toArray());

                }else{

                    Controller::conexion()->table('desglose_envio_factura')
                    ->where('id_desglose_envio_factura', $this->desgloseEnvioFactura->id_desglose_envio_factura)->update($this->desgloseEnvioFactura->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el desglose_envio_factura ".$this->desgloseEnvioFactura->id_desglose_envio_factura."\n".$this->desgloseEnvioFactura);

        }
    }
}
