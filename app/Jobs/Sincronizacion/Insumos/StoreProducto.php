<?php

namespace yura\Jobs\Sincronizacion\Insumos;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreProducto implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $producto;

    public function __construct($producto)
    {
        $this->producto = $producto;
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

                $existeProducto = Controller::objetoConsulta('producto',[
                    ['id_producto', $this->producto->id_producto]
                ])->exists();

                if(!$existeProducto){

                    Controller::conexion()->table('producto')->insert($this->producto->toArray());

                }else{

                    Controller::conexion()->table('producto')
                    ->where('id_producto', $this->producto->id_producto)->update($this->producto->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el producto ".$this->producto->id_producto."\n".$this->producto);

        }
    }
}
