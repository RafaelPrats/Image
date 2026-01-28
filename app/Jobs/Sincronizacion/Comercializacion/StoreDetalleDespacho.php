<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreDetalleDespacho implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $detalleDespacho;

    public function __construct($detalleDespacho)
    {
        $this->detalleDespacho = $detalleDespacho;
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

                $existeDetalleDespacho = Controller::objetoConsulta('detalle_despacho',[
                    ['id_detalle_despacho', $this->detalleDespacho->id_detalle_despacho]
                ])->exists();

                if(!$existeDetalleDespacho){

                    Controller::conexion()->table('detalle_despacho')->insert($this->detalleDespacho->toArray());

                }else{

                    Controller::conexion()->table('detalle_despacho')
                    ->where('id_detalle_despacho', $this->detalleDespacho->id_detalle_despacho)->update($this->detalleDespacho->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el detalle_despacho ".$this->detalleDespacho->id_detalle_despacho."\n".$this->detalleDespacho);

        }
    }
}
