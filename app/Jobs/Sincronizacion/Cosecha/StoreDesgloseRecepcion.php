<?php

namespace yura\Jobs\Sincronizacion\Cosecha;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreDesgloseRecepcion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $desgloseRecepcion;

    public function __construct($desgloseRecepcion)
    {
        $this->desgloseRecepcion = $desgloseRecepcion;
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

                $existeDesgloseRecepcion = Controller::objetoConsulta('desglose_recepcion',[
                    ['id_desglose_recepcion', $this->desgloseRecepcion->id_desglose_recepcion]
                ])->exists();

                if(!$existeDesgloseRecepcion){

                    Controller::conexion()->table('desglose_recepcion')->insert($this->desgloseRecepcion->toArray());

                }else{

                    Controller::conexion()->table('desglose_recepcion')
                    ->where('id_desglose_recepcion', $this->desgloseRecepcion->id_desglose_recepcion)->update($this->desgloseRecepcion->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el desglose_recepcion ".$this->desgloseRecepcion->id_desglose_recepcion."\n".$this->desgloseRecepcion);

        }
    }
}
