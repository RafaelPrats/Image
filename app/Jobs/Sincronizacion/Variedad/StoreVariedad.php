<?php

namespace yura\Jobs\Sincronizacion\Variedad;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreVariedad implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $variedad;

    public function __construct($variedad)
    {
        $this->variedad = $variedad;
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

                $existeVariedad = Controller::objetoConsulta('variedad',[
                    ['id_variedad', $this->variedad->id_variedad]
                ])->exists();

                if(!$existeVariedad){

                    Controller::conexion()->table('variedad')->insert($this->variedad->toArray());

                }else{

                    Controller::conexion()->table('variedad')
                    ->where('id_variedad', $this->variedad->id_variedad)->update($this->variedad->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la variedad ".$this->variedad->nombre."\n".$this->variedad);

        }
    }
}
