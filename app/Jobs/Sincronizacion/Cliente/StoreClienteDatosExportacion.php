<?php

namespace yura\Jobs\Sincronizacion\Cliente;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreClienteDatosExportacion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $clienteDatoExportacion;

    public function __construct($clienteDatoExportacion)
    {
        $this->clienteDatoExportacion = $clienteDatoExportacion;
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

                $existeCliente = Controller::objetoConsulta('cliente_datoexportacion',[
                    ['id_cliente_datoexportacion', $this->clienteDatoExportacion->id_cliente_datoexportacion]
                ])->exists();

                if(!$existeCliente){

                    Controller::conexion()->table('cliente_datoexportacion')->insert($this->clienteDatoExportacion->toArray());

                }else{

                    Controller::conexion()->table('cliente_datoexportacion')
                    ->where('id_cliente_datoexportacion', $this->clienteDatoExportacion->id_cliente_datoexportacion)->update($this->clienteDatoExportacion->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el cliente_datoexportacion ".$this->clienteDatoExportacion->id_cliente_datoexportacion."\n".$this->clienteDatoExportacion);

        }
    }
}
