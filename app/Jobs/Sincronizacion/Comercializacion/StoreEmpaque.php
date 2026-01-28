<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreEmpaque implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $empaque;

    public function __construct($empaque)
    {
        $this->empaque = $empaque;
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

                $existeEmpaque = Controller::objetoConsulta('empaque',[
                    ['id_empaque', $this->empaque->id_empaque]
                ])->exists();

                if(!$existeEmpaque){

                    Controller::conexion()->table('empaque')->insert($this->empaque->toArray());

                }else{

                    Controller::conexion()->table('empaque')
                    ->where('id_empaque', $this->empaque->id_empaque)->update($this->empaque->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el empaque ".$this->empaque->id_empaque."\n".$this->empaque);

        }
    }
}
