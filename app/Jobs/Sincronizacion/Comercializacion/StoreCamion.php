<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreCamion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $camion;

    public function __construct($camion)
    {
        $this->camion = $camion;
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

                $existeCamion = Controller::objetoConsulta('camion',[
                    ['id_camion', $this->camion->id_camion]
                ])->exists();

                if(!$existeCamion){

                    Controller::conexion()->table('camion')->insert($this->camion->toArray());

                }else{

                    Controller::conexion()->table('camion')
                    ->where('id_camion', $this->camion->id_camion)->update($this->camion->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el camion ".$this->camion->id_camion."\n".$this->camion);

        }
    }
}
