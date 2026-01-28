<?php

namespace yura\Jobs\Sincronizacion\ProyNintanga;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreDistribucionVariedad implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $distribucionVariedad;

    public function __construct($distribucionVariedad)
    {
        $this->distribucionVariedad = $distribucionVariedad;
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

                $existeDistribucionMixto = Controller::objetoConsulta('distribucion_variedad',[
                    ['id_distribucion_variedad', $this->distribucionVariedad->id_distribucion_variedad]
                ])->exists();

                if(!$existeDistribucionMixto){

                    Controller::conexion()->table('distribucion_variedad')->insert($this->distribucionVariedad->toArray());

                }else{

                    Controller::conexion()->table('distribucion_variedad')
                    ->where('id_distribucion_variedad', $this->distribucionVariedad->id_distribucion_variedad)->update($this->distribucionVariedad->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la distribucion_variedad ".$this->distribucionVariedad->id_distribucion_variedad."\n".$this->distribucionVariedad);

        }
    }
}
