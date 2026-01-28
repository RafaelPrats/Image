<?php

namespace yura\Jobs\Sincronizacion\Lote;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class Store implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $lote;

    public function __construct($lote)
    {
        $this->lote = $lote;
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

                $existeLote = Controller::objetoConsulta('lote',[
                    ['id_lote', $this->lote->id_lote]
                ])->exists();

                if(!$existeLote){

                    Controller::conexion()->table('lote')->insert($this->lote->toArray());

                }else{

                    Controller::conexion()->table('lote')
                    ->where('id_lote', $this->lote->id_lote)->update($this->lote->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el lote ".$this->lote->nombre."\n".$this->lote);

        }
    }

}
