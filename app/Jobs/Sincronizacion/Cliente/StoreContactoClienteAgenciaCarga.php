<?php

namespace yura\Jobs\Sincronizacion\Cliente;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreContactoClienteAgenciaCarga implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $contactoClienteAgenciaCarga;

    public function __construct($contactoClienteAgenciaCarga)
    {
        $this->contactoClienteAgenciaCarga = $contactoClienteAgenciaCarga;
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

                $existeContactoClienteAgenciaCarga = Controller::objetoConsulta('contactos_cliente_agenciacarga',[
                    ['id_contactos_cliente_agenciacarga', $this->contactoClienteAgenciaCarga->id_contactos_cliente_agenciacarga]
                ])->exists();

                if(!$existeContactoClienteAgenciaCarga){

                    Controller::conexion()->table('contactos_cliente_agenciacarga')->insert($this->contactoClienteAgenciaCarga->toArray());

                }else{

                    Controller::conexion()->table('contactos_cliente_agenciacarga')
                    ->where('id_contactos_cliente_agenciacarga', $this->contactoClienteAgenciaCarga->id_contactos_cliente_agenciacarga)->update($this->contactoClienteAgenciaCarga->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el contactos_cliente_agenciacarga ".$this->contactoClienteAgenciaCarga->id_contactos_cliente_agenciacarga."\n".$this->contactoClienteAgenciaCarga);

        }
    }
}
