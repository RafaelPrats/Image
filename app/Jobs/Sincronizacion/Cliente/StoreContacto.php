<?php

namespace yura\Jobs\Sincronizacion\Cliente;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreContacto implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $contacto;

    public function __construct($contacto)
    {
        $this->contacto = $contacto;
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

                $existeCliente = Controller::objetoConsulta('contacto',[
                    ['id_contacto', $this->contacto->id_contacto]
                ])->exists();

                if(!$existeCliente){

                    Controller::conexion()->table('contacto')->insert($this->contacto->toArray());

                }else{

                    Controller::conexion()->table('contacto')
                    ->where('id_contacto', $this->contacto->id_contacto)->update($this->contacto->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el contacto ".$this->contacto->id_contacto."\n".$this->contacto);

        }
    }
}
