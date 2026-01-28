<?php

namespace yura\Jobs\Sincronizacion\Modulo;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class Store implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $modulo;

    public function __construct($modulo)
    {
        $this->modulo = $modulo;
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

                $existeModulo = Controller::objetoConsulta('modulo',[
                    ['id_modulo', $this->modulo->id_modulo]
                ])->exists();

                if(!$existeModulo){

                    Controller::conexion()->table('modulo')->insert($this->modulo->toArray());

                }else{

                    Controller::conexion()->table('modulo')
                    ->where('id_modulo', $this->modulo->id_modulo)->update($this->modulo->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el modulo ".$this->modulo->nombre."\n".$this->modulo);

        }
    }
}
