<?php

namespace yura\Jobs\Sincronizacion\Cliente;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreDocumento implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $documento;

    public function __construct($documento)
    {
        $this->documento = $documento;
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

                $existeCliente = Controller::objetoConsulta('documento',[
                    ['id_documento', $this->documento->id_documento]
                ])->exists();

                if(!$existeCliente){

                    Controller::conexion()->table('documento')->insert($this->documento->toArray());

                }else{

                    Controller::conexion()->table('documento')
                    ->where('id_documento', $this->documento->id_documento)->update($this->documento->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el documento ".$this->documento->id_documento."\n".$this->documento);

        }
    }
}
