<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreCodigoVentureAgenciaCarga implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $codigoVentureAgenciaCarga;

    public function __construct($codigoVentureAgenciaCarga)
    {
        $this->codigoVentureAgenciaCarga = $codigoVentureAgenciaCarga;
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

                $existeCodigoVentureAgenciaCarga = Controller::objetoConsulta('codigo_venture_agencia_carga',[
                    ['id_codigo_venture_agencia_carga', $this->codigoVentureAgenciaCarga->id_codigo_venture_agencia_carga]
                ])->exists();

                if(!$existeCodigoVentureAgenciaCarga){

                    Controller::conexion()->table('codigo_venture_agencia_carga')->insert($this->codigoVentureAgenciaCarga->toArray());

                }else{

                    Controller::conexion()->table('codigo_venture_agencia_carga')
                    ->where('id_codigo_venture_agencia_carga', $this->codigoVentureAgenciaCarga->id_codigo_venture_agencia_carga)->update($this->codigoVentureAgenciaCarga->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el codigo_venture_agencia_carga ".$this->codigoVentureAgenciaCarga->id_codigo_venture_agencia_carga."\n".$this->codigoVentureAgenciaCarga);

        }
    }
}
