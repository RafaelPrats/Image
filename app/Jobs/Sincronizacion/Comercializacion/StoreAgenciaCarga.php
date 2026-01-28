<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreAgenciaCarga implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $agenciaCarga;

    public function __construct($agenciaCarga)
    {
        $this->agenciaCarga = $agenciaCarga;
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

                $existeAgenciaCarga = Controller::objetoConsulta('agencia_carga',[
                    ['id_agencia_carga', $this->agenciaCarga->id_agencia_carga]
                ])->exists();

                if(!$existeAgenciaCarga){

                    Controller::conexion()->table('agencia_carga')->insert($this->agenciaCarga->toArray());

                }else{

                    Controller::conexion()->table('agencia_carga')
                    ->where('id_agencia_carga', $this->agenciaCarga->id_agencia_carga)->update($this->agenciaCarga->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la agencia_carga ".$this->agenciaCarga->id_agencia_carga."\n".$this->agenciaCarga);

        }
    }
}
