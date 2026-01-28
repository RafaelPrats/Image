<?php

namespace yura\Jobs\Sincronizacion\Cliente;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class DeleteClienteConsignatario implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $clienteConsignatarios;

    public function __construct($clienteConsignatarios)
    {
        $this->clienteConsignatarios = $clienteConsignatarios;
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

                Controller::objetoConsulta('cliente_consignatario',[
                    ['id_cliente_consignatario', $this->clienteConsignatarios->id_cliente_consignatario]
                ])->delete();

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó eliminar el cliente_consignatario".$this->clienteConsignatarios->id_cliente_consignatario."\n".$this->clienteConsignatarios);

        }
    }
}
