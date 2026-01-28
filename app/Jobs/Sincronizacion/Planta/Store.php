<?php

namespace yura\Jobs\Sincronizacion\Planta;

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
    public $planta;

    public function __construct($planta)
    {
        $this->planta = $planta;
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

                $existePlanta = Controller::objetoConsulta('planta',[
                    ['id_planta', $this->planta->id_planta]
                ])->exists();

                if(!$existePlanta){

                    Controller::conexion()->table('planta')->insert($this->planta->toArray());

                }else{

                    Controller::conexion()->table('planta')
                    ->where('id_planta', $this->planta->id_planta)->update($this->planta->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la planta ".$this->planta->nombre."\n".$this->planta);

        }
    }
}
