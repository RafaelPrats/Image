<?php

namespace yura\Jobs\Sincronizacion\Cosecha;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreCosecha implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $consecha;

    public function __construct($consecha)
    {
        $this->cosecha = $consecha;
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

                $existeCosecha = Controller::objetoConsulta('cosecha',[
                    ['id_cosecha', $this->cosecha->id_cosecha]
                ])->exists();

                if(!$existeCosecha){

                    Controller::conexion()->table('cosecha')->insert($this->cosecha->toArray());

                }else{

                    Controller::conexion()->table('cosecha')
                    ->where('id_cosecha', $this->cosecha->id_cosecha)->update($this->cosecha->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la cosecha ".$this->cosecha->id_cosecha."\n".$this->cosecha);

        }
    }
}
