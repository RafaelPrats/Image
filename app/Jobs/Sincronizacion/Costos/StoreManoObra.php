<?php

namespace yura\Jobs\Sincronizacion\Costos;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreManoObra implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $manoObra;

    public function __construct($manoObra)
    {
        $this->manoObra = $manoObra;
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

                $existemanoObra = Controller::objetoConsulta('mano_obra',[
                    ['id_mano_obra', $this->manoObra->id_mano_obra]
                ])->exists();

                if(!$existemanoObra){

                    Controller::conexion()->table('mano_obra')->insert($this->manoObra->toArray());

                }else{

                    Controller::conexion()->table('mano_obra')
                    ->where('id_mano_obra', $this->manoObra->id_mano_obra)->update($this->manoObra->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la mano_obra ".$this->manoObra->id_mano_obra."\n".$this->manoObra);

        }
    }
}
