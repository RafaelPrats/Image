<?php

namespace yura\Jobs\Sincronizacion\ProyNintanga;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreProyCortes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $proyCortes;

    public function __construct($proyCortes)
    {
        $this->proyCortes = $proyCortes;
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

                $existeProyCortes = Controller::objetoConsulta('proy_cortes',[
                    ['id_proy_cortes', $this->proyCortes->id_proy_cortes]
                ])->exists();

                if(!$existeProyCortes){

                    Controller::conexion()->table('proy_cortes')->insert($this->proyCortes->toArray());

                }else{

                    Controller::conexion()->table('proy_cortes')
                    ->where('id_proy_cortes', $this->proyCortes->id_proy_cortes)->update($this->proyCortes->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la proy_cortes ".$this->proyCortes->id_proy_cortes."\n".$this->proyCortes);

        }
    }
}
