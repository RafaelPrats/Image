<?php

namespace yura\Jobs\Sincronizacion\Ciclo;

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
    public $ciclo;

    public function __construct($ciclo)
    {
        $this->ciclo = $ciclo;
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

                $existeCiclo = Controller::objetoConsulta('ciclo',[
                    ['id_ciclo', $this->ciclo->id_ciclo]
                ])->exists();

                if(!$existeCiclo){

                    Controller::conexion()->table('ciclo')->insert($this->ciclo->toArray());

                }else{

                    Controller::conexion()->table('ciclo')
                    ->where('id_ciclo', $this->ciclo->id_ciclo)->update($this->ciclo->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el ciclo ".$this->ciclo->id_ciclo."\n".$this->ciclo);

        }
    }
}
