<?php

namespace yura\Jobs\Sincronizacion\Cliente;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreDetalleCliente implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $detalleCliente;

    public function __construct($detalleCliente)
    {
        $this->detalleCliente = $detalleCliente;
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

                $existeCliente = Controller::objetoConsulta('detalle_cliente',[
                    ['id_detalle_cliente', $this->detalleCliente->id_detalle_cliente]
                ])->exists();

                if(!$existeCliente){

                    Controller::conexion()->table('detalle_cliente')->insert($this->detalleCliente->toArray());

                }else{

                    Controller::conexion()->table('detalle_cliente')
                    ->where('id_detalle_cliente', $this->detalleCliente->id_detalle_cliente)->update($this->detalleCliente->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el detalle_cliente ".$this->detalleCliente->id_detalle_cliente."\n".$this->detalleCliente);

        }
    }
}
