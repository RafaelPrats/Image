<?php

namespace yura\Jobs\Sincronizacion\Indicador;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreIndicadorVariedad implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $indicadorVariedad;

    public function __construct($indicadorVariedad)
    {
        $this->indicadorVariedad = $indicadorVariedad;
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

                $existeIndicadorVariedad = Controller::objetoConsulta('indicador_variedad',[
                    ['id_indicador_variedad', $this->indicadorVariedad->id_indicador_variedad]
                ])->exists();

                if(!$existeIndicadorVariedad){

                    Controller::conexion()->table('indicador_variedad')->insert($this->indicadorVariedad->toArray());

                }else{

                    Controller::conexion()->table('indicador_variedad')
                    ->where('id_indicador_variedad', $this->indicadorVariedad->id_indicador_variedad)->update($this->indicadorVariedad->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el indicador_variedad ".$this->indicadorVariedad->id_indicador_variedad."\n".$this->indicadorVariedad);

        }
    }
}
