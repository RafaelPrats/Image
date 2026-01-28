<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreDatosExportacion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $datosExportacion;

    public function __construct($datosExportacion)
    {
        $this->datosExportacion = $datosExportacion;
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

                $existeDatoExportacion = Controller::objetoConsulta('dato_exportacion',[
                    ['id_dato_exportacion', $this->datosExportacion->id_dato_exportacion]
                ])->exists();

                if(!$existeDatoExportacion){

                    Controller::conexion()->table('dato_exportacion')->insert($this->datosExportacion->toArray());

                }else{

                    Controller::conexion()->table('dato_exportacion')
                    ->where('id_dato_exportacion', $this->datosExportacion->id_dato_exportacion)->update($this->datosExportacion->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el dato_exportacion ".$this->datosExportacion->id_dato_exportacion."\n".$this->datosExportacion);

        }
    }
}
