<?php

namespace yura\Jobs\Sincronizacion\ClasificacionVerde;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreStockEmpaquetado implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $stockEmpaquetado;

    public function __construct($stockEmpaquetado)
    {
        $this->stockEmpaquetado = $stockEmpaquetado;
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

                $existeStockGuarde = Controller::objetoConsulta('stock_empaquetado',[
                    ['id_stock_empaquetado', $this->stockEmpaquetado->id_stock_empaquetado]
                ])->exists();

                if(!$existeStockGuarde){

                    Controller::conexion()->table('stock_empaquetado')->insert($this->stockEmpaquetado->toArray());

                }else{

                    Controller::conexion()->table('stock_empaquetado')
                    ->where('id_stock_empaquetado', $this->stockEmpaquetado->id_stock_empaquetado)->update($this->stockEmpaquetado->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el stock_empaquetado ".$this->stockEmpaquetado->id_stock_empaquetado."\n".$this->stockEmpaquetado);

        }
    }
}
