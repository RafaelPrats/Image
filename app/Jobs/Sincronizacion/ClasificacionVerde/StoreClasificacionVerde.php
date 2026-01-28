<?php

namespace yura\Jobs\Sincronizacion\ClasificacionVerde;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreClasificacionVerde implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $clasificacionVerde;

    public function __construct($clasificacionVerde)
    {
        $this->clasificacionVerde = $clasificacionVerde;
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

                $existeClasificacionVerde = Controller::objetoConsulta('clasificacion_verde',[
                    ['id_clasificacion_verde', $this->clasificacionVerde->id_clasificacion_verde]
                ])->exists();

                if(!$existeClasificacionVerde){

                    Controller::conexion()->table('clasificacion_verde')->insert($this->clasificacionVerde->toArray());

                }else{

                    Controller::conexion()->table('clasificacion_verde')
                    ->where('id_clasificacion_verde', $this->clasificacionVerde->id_clasificacion_verde)->update($this->clasificacionVerde->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el clasificacion_verde ".$this->clasificacionVerde->id_clasificacion_verde."\n".$this->clasificacionVerde);

        }
    }
}
