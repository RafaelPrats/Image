<?php

namespace yura\Jobs\Sincronizacion\ProyNintanga;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class DeleteProyVariedadCortes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $proyVariedadCortes;

    public function __construct($proyVariedadCortes)
    {
        $this->proyVariedadCortes = $proyVariedadCortes;
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

                Controller::objetoConsulta('proy_variedad_cortes',[
                    ['id_proy_variedad_cortes', $this->proyVariedadCortes->id_proy_variedad_cortes]
                ])->delete();

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó eliminar la proy_variedad_cortes".$this->proyVariedadCortes->id_proy_variedad_cortes."\n".$this->proyVariedadCortes);

        }
    }
}
