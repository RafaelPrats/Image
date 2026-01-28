<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreMarca implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $marca;

    public function __construct($marca)
    {
        $this->marca = $marca;
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

                $existeMarca = Controller::objetoConsulta('marcas',[
                    ['id_marca', $this->marca->id_marca]
                ])->exists();

                if(!$existeMarca){

                    Controller::conexion()->table('marcas')->insert($this->marca->toArray());

                }else{

                    Controller::conexion()->table('marcas')
                    ->where('id_marca', $this->marca->id_marca)->update($this->marca->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la marca ".$this->marca->id_marca."\n".$this->marca);

        }
    }
}
