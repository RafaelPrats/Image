<?php

namespace yura\Jobs\Sincronizacion\Indicador;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreIndicador implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $indicador;

    public function __construct($indicador)
    {
        $this->indicador = $indicador;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        info('se activo el jobs indicador');
        if(Controller::checkPing()){

            Controller::conexion()->transaction(function() {

                $existeIndicador = Controller::objetoConsulta('indicador',[
                    ['id_indicador', $this->indicador->id_indicador]
                ])->exists();

                if(!$existeIndicador){

                    Controller::conexion()->table('indicador')->insert($this->indicador->toArray());

                }else{

                    Controller::conexion()->table('indicador')
                    ->where('id_indicador', $this->indicador->id_indicador)->update($this->indicador->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el indicador ".$this->indicador->nombre."\n".$this->indicador);

        }
    }
}
