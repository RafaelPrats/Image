<?php

namespace yura\Jobs\Sincronizacion\Cliente;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreClienteAgenciaCarga implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $clienteAgenciaCarga;

    public function __construct($clienteAgenciaCarga)
    {
        $this->clienteAgenciaCarga = $clienteAgenciaCarga;
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

                $existeClienteAgenciaCarga = Controller::objetoConsulta('cliente_agenciacarga',[
                    ['id_cliente_agencia_carga', $this->clienteAgenciaCarga->id_cliente_agencia_carga]
                ])->exists();

                if(!$existeClienteAgenciaCarga){

                    Controller::conexion()->table('cliente_agenciacarga')->insert($this->clienteAgenciaCarga->toArray());

                }else{

                    Controller::conexion()->table('cliente_agenciacarga')
                    ->where('id_cliente_agencia_carga', $this->clienteAgenciaCarga->id_cliente_agencia_carga)->update($this->clienteAgenciaCarga->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el cliente_agenciacarga ".$this->clienteAgenciaCarga->id_cliente_agencia_carga."\n".$this->clienteAgenciaCarga);

        }
    }
}
