<?php

namespace yura\Jobs\Sincronizacion\ProyNintanga;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreProyVariedadCortes implements ShouldQueue
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

                $existeProyVariedadCortes = Controller::objetoConsulta('proy_variedad_cortes',[
                    ['id_proy_variedad_cortes', $this->proyVariedadCortes->id_proy_variedad_cortes]
                ])->exists();

                if(!$existeProyVariedadCortes){

                    Controller::conexion()->table('proy_variedad_cortes')->insert($this->proyVariedadCortes->toArray());

                }else{

                    Controller::conexion()->table('proy_variedad_cortes')
                    ->where('id_proy_variedad_cortes', $this->proyVariedadCortes->id_proy_variedad_cortes)->update($this->proyVariedadCortes->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la proy_variedad_cortes ".$this->proyVariedadCortes->id_proy_variedad_cortes."\n".$this->proyVariedadCortes);

        }
    }
}
