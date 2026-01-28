<?php

namespace yura\Jobs\Sincronizacion\Temperatura;

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
    public $temperatura;

    public function __construct($temperatura)
    {
        $this->temperatura = $temperatura;
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

                $existetemperatura = Controller::objetoConsulta('temperatura',[
                    ['id_temperatura', $this->temperatura->id_temperatura]
                ])->exists();

                if(!$existetemperatura){

                    Controller::conexion()->table('temperatura')->insert($this->temperatura->toArray());

                }else{

                    Controller::conexion()->table('temperatura')
                    ->where('id_temperatura', $this->temperatura->id_temperatura)->update($this->temperatura->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la temperatura ".$this->temperatura->id_temperatura."\n".$this->temperatura);

        }
    }
}
