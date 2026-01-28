<?php

namespace yura\Jobs\Sincronizacion\Costos;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreCostosHoras implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $costoHoras;

    public function __construct($costoHoras)
    {
        $this->costoHoras = $costoHoras;
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

                $existeCostoHoras = Controller::objetoConsulta('costo_horas',[
                    ['id_costo_horas', $this->costoHoras->id_costo_horas]
                ])->exists();

                if(!$existeCostoHoras){

                    Controller::conexion()->table('costo_horas')->insert($this->costoHoras->toArray());

                }else{

                    Controller::conexion()->table('costo_horas')
                    ->where('id_costo_horas', $this->costoHoras->id_costo_horas)->update($this->costoHoras->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el costo_horas ".$this->costoHoras->id_costo_horas."\n".$this->costoHoras);

        }
    }
}
