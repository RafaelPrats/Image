<?php

namespace yura\Jobs\Sincronizacion\Proyeccion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreResumenSaldoProyVentaSemanal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $resumenSaldoProyVentaSemanal;

    public function __construct($resumenSaldoProyVentaSemanal)
    {
        $this->resumenSaldoProyVentaSemanal = $resumenSaldoProyVentaSemanal;
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

                $existeResumenSaldoProyVentaSemanal = Controller::objetoConsulta('resumen_saldo_proy_venta_semanal',[
                    ['id_resumen_saldo_proy_venta_semanal', $this->resumenSaldoProyVentaSemanal->id_resumen_saldo_proy_venta_semanal]
                ])->exists();

                if(!$existeResumenSaldoProyVentaSemanal){

                    Controller::conexion()->table('resumen_saldo_proy_venta_semanal')->insert($this->resumenSaldoProyVentaSemanal->toArray());

                }else{

                    Controller::conexion()->table('resumen_saldo_proy_venta_semanal')
                    ->where('id_resumen_saldo_proy_venta_semanal', $this->resumenSaldoProyVentaSemanal->id_resumen_saldo_proy_venta_semanal)->update($this->resumenSaldoProyVentaSemanal->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la resumen_saldo_proy_venta_semanal ".$this->resumenSaldoProyVentaSemanal->id_resumen_saldo_proy_venta_semanal."\n".$this->resumenSaldoProyVentaSemanal);

        }
    }

}
