<?php

namespace yura\Jobs\Sincronizacion\Cliente;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreClienteConsignatario implements ShouldQueue
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

                $existeCliente = Controller::objetoConsulta('cliente_consignatario',[
                    ['id_cliente_consignatario', $this->clienteConsignatarios->id_cliente_consignatario]
                ])->exists();

                if(!$existeCliente){

                    Controller::conexion()->table('cliente_consignatario')->insert($this->clienteConsignatarios->toArray());

                }else{

                    Controller::conexion()->table('cliente_consignatario')
                    ->where('id_cliente_consignatario', $this->clienteConsignatarios->id_cliente_consignatario)->update($this->clienteConsignatarios->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el cliente_consignatario ".$this->clienteConsignatarios->id_cliente_consignatario."\n".$this->clienteConsignatarios);

        }
    }
}
