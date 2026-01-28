<?php

namespace yura\Jobs\Sincronizacion\Proyeccion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreProyeccionVentaSemanalReal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $proyVentaSemanalReal;

    public function __construct($proyVentaSemanalReal)
    {
        $this->proyVentaSemanalReal = $proyVentaSemanalReal;
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

                $existeProyVentaSemanalReal = Controller::objetoConsulta('proyeccion_venta_semanal_real',[
                    ['id_proyeccion_venta_semanal_real', $this->proyVentaSemanalReal->id_proyeccion_venta_semanal_real]
                ])->exists();

                if(!$existeProyVentaSemanalReal){

                    Controller::conexion()->table('proyeccion_venta_semanal_real')->insert($this->proyVentaSemanalReal->toArray());

                }else{

                    Controller::conexion()->table('proyeccion_venta_semanal_real')
                    ->where('id_proyeccion_venta_semanal_real', $this->proyVentaSemanalReal->id_proyeccion_venta_semanal_real)->update($this->proyVentaSemanalReal->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la proyeccion_venta_semanal_real ".$this->proyVentaSemanalReal->id_proyeccion_venta_semanal_real."\n".$this->proyVentaSemanalReal);

        }
    }
}
