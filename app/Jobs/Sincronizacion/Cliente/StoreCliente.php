<?php

namespace yura\Jobs\Sincronizacion\Cliente;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreCliente implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $cliente;

    public function __construct($cliente)
    {
        $this->cliente = $cliente;
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

                $existeCliente = Controller::objetoConsulta('cliente',[
                    ['id_cliente', $this->cliente->id_cliente]
                ])->exists();

                if(!$existeCliente){

                    Controller::conexion()->table('cliente')->insert($this->cliente->toArray());

                }else{

                    Controller::conexion()->table('cliente')
                    ->where('id_cliente', $this->cliente->id_cliente)->update($this->cliente->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el cliente ".$this->cliente->id_cliente."\n".$this->cliente);

        }
    }
}
