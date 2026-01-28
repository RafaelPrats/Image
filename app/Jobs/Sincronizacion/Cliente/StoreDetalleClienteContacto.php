<?php

namespace yura\Jobs\Sincronizacion\Cliente;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreDetalleClienteContacto implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $detalleClienteContacto;

    public function __construct($detalleClienteContacto)
    {
        $this->detalleClienteContacto = $detalleClienteContacto;
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

                $existeCliente = Controller::objetoConsulta('detalle_cliente_contacto',[
                    ['id_detalle_cliente_contacto', $this->detalleClienteContacto->id_detalle_cliente_contacto]
                ])->exists();

                if(!$existeCliente){

                    Controller::conexion()->table('detalle_cliente_contacto')->insert($this->detalleClienteContacto->toArray());

                }else{

                    Controller::conexion()->table('detalle_cliente_contacto')
                    ->where('id_detalle_cliente_contacto', $this->detalleClienteContacto->id_detalle_cliente_contacto)->update($this->detalleClienteContacto->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el detalle_cliente_contacto ".$this->detalleClienteContacto->id_detalle_cliente_contacto."\n".$this->detalleClienteContacto);

        }
    }
}
