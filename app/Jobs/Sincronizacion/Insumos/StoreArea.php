<?php

namespace yura\Jobs\Sincronizacion\Insumos;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreArea implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $area;

    public function __construct($area)
    {
        $this->area = $area;
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

                $existeArea = Controller::objetoConsulta('area',[
                    ['id_area', $this->area->id_area]
                ])->exists();

                if(!$existeArea){

                    Controller::conexion()->table('area')->insert($this->area->toArray());

                }else{

                    Controller::conexion()->table('area')
                    ->where('id_area', $this->area->id_area)->update($this->area->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el area ".$this->area->id_area."\n".$this->area);

        }
    }
}
