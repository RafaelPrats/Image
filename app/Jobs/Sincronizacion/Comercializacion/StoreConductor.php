<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreConductor implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $conductor;

    public function __construct($conductor)
    {
        $this->conductor = $conductor;
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

                $existeConductor = Controller::objetoConsulta('conductor',[
                    ['id_conductor', $this->conductor->id_conductor]
                ])->exists();

                if(!$existeConductor){

                    Controller::conexion()->table('conductor')->insert($this->conductor->toArray());

                }else{

                    Controller::conexion()->table('conductor')
                    ->where('id_conductor', $this->conductor->id_conductor)->update($this->conductor->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el conductor ".$this->conductor->id_conductor."\n".$this->conductor);

        }
    }
}
