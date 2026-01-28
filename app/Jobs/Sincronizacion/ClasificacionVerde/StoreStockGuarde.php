<?php

namespace yura\Jobs\Sincronizacion\ClasificacionVerde;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreStockGuarde implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $stockGuarde;

    public function __construct($stockGuarde)
    {
        $this->stockGuarde = $stockGuarde;
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

                $existeStockGuarde = Controller::objetoConsulta('stock_guarde',[
                    ['id_stock_guarde', $this->stockGuarde->id_stock_guarde]
                ])->exists();

                if(!$existeStockGuarde){

                    Controller::conexion()->table('stock_guarde')->insert($this->stockGuarde->toArray());

                }else{

                    Controller::conexion()->table('stock_guarde')
                    ->where('id_stock_guarde', $this->stockGuarde->id_stock_guarde)->update($this->stockGuarde->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el stock_guarde ".$this->stockGuarde->id_stock_guarde."\n".$this->stockGuarde);

        }
    }
}
