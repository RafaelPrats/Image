<?php

namespace yura\Jobs\Sincronizacion\Costos;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreCostoSemanaManoObra implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $costoSemanaManoObra;

    public function __construct($costoSemanaManoObra)
    {
        $this->costoSemanaManoObra = $costoSemanaManoObra;
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

                $existeCostoSemanaManoObra = Controller::objetoConsulta('costos_semana_mano_obra',[
                    ['id_costos_semana_mano_obra', $this->costoSemanaManoObra->id_costos_semana_mano_obra]
                ])->exists();

                if(!$existeCostoSemanaManoObra){

                    Controller::conexion()->table('costos_semana_mano_obra')->insert($this->costoSemanaManoObra->toArray());

                }else{

                    Controller::conexion()->table('costos_semana_mano_obra')
                    ->where('id_costos_semana_mano_obra', $this->costoSemanaManoObra->id_costos_semana_mano_obra)->update($this->costoSemanaManoObra->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el costos_semana_mano_obra ".$this->costoSemanaManoObra->id_costos_semana_mano_obra."\n".$this->costoSemanaManoObra);

        }
    }
}
