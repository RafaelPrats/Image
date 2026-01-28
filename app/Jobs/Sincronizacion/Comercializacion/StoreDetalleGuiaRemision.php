<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreDetalleGuiaRemision implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $detalleGuiaRemision;

    public function __construct($detalleGuiaRemision)
    {
        $this->detalleGuiaRemision = $detalleGuiaRemision;
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

                $existeDetalleGuiaRemision = Controller::objetoConsulta('detalle_guia_remision',[
                    ['id_detalle_guia_remision', $this->detalleGuiaRemision->id_detalle_guia_remision]
                ])->exists();

                if(!$existeDetalleGuiaRemision){

                    Controller::conexion()->table('detalle_guia_remision')->insert($this->detalleGuiaRemision->toArray());

                }else{

                    Controller::conexion()->table('detalle_guia_remision')
                    ->where('id_detalle_guia_remision', $this->detalleGuiaRemision->id_detalle_guia_remision)->update($this->detalleGuiaRemision->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el detalle_guia_remision ".$this->detalleGuiaRemision->id_detalle_guia_remision."\n".$this->detalleGuiaRemision);

        }
    }
}
