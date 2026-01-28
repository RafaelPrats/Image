<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreContactoConsignatario implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $contactoConsignatario;

    public function __construct($contactoConsignatario)
    {
        $this->contactoConsignatario = $contactoConsignatario;
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

                $existeContantoConsignatario = Controller::objetoConsulta('contacto_consignatario',[
                    ['id_contacto_consignatario', $this->contactoConsignatario->id_contacto_consignatario]
                ])->exists();

                if(!$existeContantoConsignatario){

                    Controller::conexion()->table('contacto_consignatario')->insert($this->contactoConsignatario->toArray());

                }else{

                    Controller::conexion()->table('contacto_consignatario')
                    ->where('id_contacto_consignatario', $this->contactoConsignatario->id_contacto_consignatario)->update($this->contactoConsignatario->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el contacto_consignatario ".$this->contactoConsignatario->id_contacto_consignatario."\n".$this->contactoConsignatario);

        }
    }
}
