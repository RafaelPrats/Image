<?php

namespace yura\Jobs\Sincronizacion\ClasificacionVerde;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreStockApertura implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $stockApertura;

    public function __construct($stockApertura)
    {
        $this->stockApertura = $stockApertura;
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

                $existeStockApertura = Controller::objetoConsulta('stock_apertura',[
                    ['id_stock_apertura', $this->stockApertura->id_stock_apertura]
                ])->exists();

                if(!$existeStockApertura){

                    Controller::conexion()->table('stock_apertura')->insert($this->stockApertura->toArray());

                }else{

                    Controller::conexion()->table('stock_apertura')
                    ->where('id_stock_apertura', $this->stockApertura->id_stock_apertura)->update($this->stockApertura->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el stock_apertura ".$this->stockApertura->id_stock_apertura."\n".$this->stockApertura);

        }
    }
}
