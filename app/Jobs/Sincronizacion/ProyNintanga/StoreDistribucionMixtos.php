<?php

namespace yura\Jobs\Sincronizacion\ProyNintanga;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreDistribucionMixtos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $distribucionMixtos;

    public function __construct($distribucionMixtos)
    {
        $this->distribucionMixtos = $distribucionMixtos;
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

                $existeDistribucionMixto = Controller::objetoConsulta('distribucion_mixtos',[
                    ['id_distribucion_mixtos', $this->distribucionMixtos->id_distribucion_mixtos]
                ])->exists();

                if(!$existeDistribucionMixto){

                    Controller::conexion()->table('distribucion_mixtos')->insert($this->distribucionMixtos->toArray());

                }else{

                    Controller::conexion()->table('distribucion_mixtos')
                    ->where('id_distribucion_mixtos', $this->distribucionMixtos->id_distribucion_mixtos)->update($this->distribucionMixtos->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la distribucion_mixtos ".$this->distribucionMixtos->id_distribucion_mixtos."\n".$this->distribucionMixtos);

        }
    }
}
