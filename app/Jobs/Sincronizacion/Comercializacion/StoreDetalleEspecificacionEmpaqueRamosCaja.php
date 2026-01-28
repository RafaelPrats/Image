<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;
use yura\Modelos\DetallePedido;

class StoreDetalleEspecificacionEmpaqueRamosCaja implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $detalleEspecificacionEmpaqueRamosCaja;

    public function __construct($detalleEspecificacionEmpaqueRamosCaja)
    {
        $this->detalleEspecificacionEmpaqueRamosCaja = $detalleEspecificacionEmpaqueRamosCaja;
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

                $detPed = DetallePedido::where('id_detalle_pedido',$this->detalleEspecificacionEmpaqueRamosCaja->id_detalle_pedido)->exists();

                if($detPed){

                    $existeDetalleEspecificacionEmpaqueRamosCaja = Controller::objetoConsulta('detalle_especificacionempaque_ramos_x_caja',[
                        ['id_detalle_especificacionempaque_ramos_x_caja', $this->detalleEspecificacionEmpaqueRamosCaja->id_detalle_especificacionempaque_ramos_x_caja]
                    ])->exists();

                    if(!$existeDetalleEspecificacionEmpaqueRamosCaja){

                        Controller::conexion()->table('detalle_especificacionempaque_ramos_x_caja')->insert($this->detalleEspecificacionEmpaqueRamosCaja->toArray());

                    }else{

                        Controller::conexion()->table('detalle_especificacionempaque_ramos_x_caja')
                        ->where('id_detalle_especificacionempaque_ramos_x_caja', $this->detalleEspecificacionEmpaqueRamosCaja->id_detalle_especificacionempaque_ramos_x_caja)
                        ->update($this->detalleEspecificacionEmpaqueRamosCaja->toArray());

                    }

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el detalle_especificacionempaque_ramos_x_caja ".$this->detalleEspecificacionEmpaqueRamosCaja->id_detalle_especificacionempaque_ramos_x_caja."\n".$this->detalleEspecificacionEmpaqueRamosCaja);

        }
    }
}
