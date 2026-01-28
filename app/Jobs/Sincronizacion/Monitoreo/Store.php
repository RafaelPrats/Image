<?php

namespace yura\Jobs\Sincronizacion\Monitoreo;

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
    public $monitoreo;

    public function __construct($monitoreo)
    {
        $this->monitoreo = $monitoreo;
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

                $existeMonitoreo = Controller::objetoConsulta('monitoreo',[
                    ['id_monitoreo', $this->monitoreo->id_monitoreo]
                ])->exists();

                if(!$existeMonitoreo){

                    Controller::conexion()->table('monitoreo')->insert($this->monitoreo->toArray());

                }else{

                    Controller::conexion()->table('monitoreo')
                    ->where('id_monitoreo', $this->monitoreo->id_monitoreo)->update($this->monitoreo->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el monitoreo ".$this->monitoreo->id_monitoreo."\n".$this->monitoreo);

        }
    }
}
