<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreConsignatario implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $consignatario;

    public function __construct($consignatario)
    {
        $this->consignatario = $consignatario;
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

                $existeConsignatario = Controller::objetoConsulta('consignatario',[
                    ['id_consignatario', $this->consignatario->id_consignatario]
                ])->exists();

                if(!$existeConsignatario){

                    Controller::conexion()->table('consignatario')->insert($this->consignatario->toArray());

                }else{

                    Controller::conexion()->table('consignatario')
                    ->where('id_consignatario', $this->consignatario->id_consignatario)->update($this->consignatario->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el consignatario ".$this->consignatario->id_consignatario."\n".$this->consignatario);

        }
    }
}
