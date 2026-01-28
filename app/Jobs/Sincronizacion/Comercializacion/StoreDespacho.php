<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreDespacho implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $despacho;

    public function __construct($despacho)
    {
        $this->despacho = $despacho;
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

                $existeDatoExportacion = Controller::objetoConsulta('despacho',[
                    ['id_despacho', $this->despacho->id_despacho]
                ])->exists();

                if(!$existeDatoExportacion){

                    Controller::conexion()->table('despacho')->insert($this->despacho->toArray());

                }else{

                    Controller::conexion()->table('despacho')
                    ->where('id_despacho', $this->despacho->id_despacho)->update($this->despacho->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el despacho ".$this->despacho->id_despacho."\n".$this->despacho);

        }
    }
}
