<?php

namespace yura\Jobs\Sincronizacion\Regalias;

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
    public $regalias;

    public function __construct($regalias)
    {
        $this->regalias = $regalias;
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

                $existeRegalia = Controller::objetoConsulta('regalias',[
                    ['id_regalias', $this->regalias->id_regalias]
                ])->exists();

                if(!$existeRegalia){

                    Controller::conexion()->table('regalias')->insert($this->regalias->toArray());

                }else{

                    Controller::conexion()->table('regalias')
                    ->where('id_regalias', $this->regalias->id_regalias)->update($this->regalias->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la regalia ".$this->regalias->id_regalias."\n".$this->regalias);

        }
    }
}
