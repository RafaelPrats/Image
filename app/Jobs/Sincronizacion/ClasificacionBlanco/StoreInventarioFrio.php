<?php

namespace yura\Jobs\Sincronizacion\ClasificacionBlanco;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreInventarioFrio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $inventarioFrio;

    public function __construct($inventarioFrio)
    {
        $this->inventarioFrio = $inventarioFrio;
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

                $existeInventarioFrio = Controller::objetoConsulta('inventario_frio',[
                    ['id_inventario_frio', $this->inventarioFrio->id_inventario_frio]
                ])->exists();

                if(!$existeInventarioFrio){

                    Controller::conexion()->table('inventario_frio')->insert($this->inventarioFrio->toArray());

                }else{

                    Controller::conexion()->table('inventario_frio')
                    ->where('id_inventario_frio', $this->inventarioFrio->id_inventario_frio)->update($this->inventarioFrio->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el inventario_frio ".$this->inventarioFrio->id_inventario_frio."\n".$this->inventarioFrio);

        }
    }
}
