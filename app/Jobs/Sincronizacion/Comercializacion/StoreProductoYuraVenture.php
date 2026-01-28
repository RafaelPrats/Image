<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreProductoYuraVenture implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $productoYuraVenture;

    public function __construct($productoYuraVenture)
    {
        $this->productoYuraVenture = $productoYuraVenture;
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

                $existeProductoYuraVenture = Controller::objetoConsulta('productos_yura_venture',[
                    ['id_producto_yura_venture', $this->productoYuraVenture->id_producto_yura_venture]
                ])->exists();

                if(!$existeProductoYuraVenture){

                    Controller::conexion()->table('productos_yura_venture')->insert($this->productoYuraVenture->toArray());

                }else{

                    Controller::conexion()->table('productos_yura_venture')
                    ->where('id_producto_yura_venture', $this->productoYuraVenture->id_producto_yura_venture)->update($this->productoYuraVenture->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el productos_yura_venture ".$this->productoYuraVenture->id_producto_yura_venture."\n".$this->productoYuraVenture);

        }
    }
}
