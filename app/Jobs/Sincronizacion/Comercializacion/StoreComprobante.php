<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreComprobante implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $comprobante;

    public function __construct($comprobante)
    {
        $this->comprobante = $comprobante;
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

                $existeComprobante = Controller::objetoConsulta('comprobante',[
                    ['id_comprobante', $this->comprobante->id_comprobante]
                ])->exists();

                if(!$existeComprobante){

                    Controller::conexion()->table('comprobante')->insert($this->comprobante->toArray());

                }else{

                    Controller::conexion()->table('comprobante')
                    ->where('id_comprobante', $this->comprobante->id_comprobante)->update($this->comprobante->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el comprobante ".$this->comprobante->id_comprobante."\n".$this->comprobante);

        }
    }
}
