<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class DeleteCodigoVentureAgenciaCarga implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $codigoVentureClienteAgenciaCarga;

    public function __construct($codigoVentureClienteAgenciaCarga)
    {
        $this->codigoVentureClienteAgenciaCarga = $codigoVentureClienteAgenciaCarga;
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

                Controller::objetoConsulta('codigo_venture_agencia_carga',[
                    ['id_codigo_venture_agencia_carga', $this->codigoVentureClienteAgenciaCarga->id_codigo_venture_agencia_carga]
                ])->delete();

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó eliminar el codigo_venture_agencia_carga".$this->codigoVentureClienteAgenciaCarga->id_codigo_venture_agencia_carga."\n".$this->codigoVentureClienteAgenciaCarga);

        }
    }
}
