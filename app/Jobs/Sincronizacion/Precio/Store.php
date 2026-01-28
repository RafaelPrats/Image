<?php

namespace yura\Jobs\Sincronizacion\Precio;

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
    public $precio;

    public function __construct($precio)
    {
        $this->precio = $precio;
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

                $existePrecio = Controller::objetoConsulta('precio',[
                    ['id_precio', $this->precio->id_precio]
                ])->exists();

                if(!$existePrecio){

                    Controller::conexion()->table('precio')->insert($this->precio->toArray());

                }else{

                    Controller::conexion()->table('precio')
                    ->where('id_precio', $this->precio->id_precio)->update($this->precio->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el precio ".$this->precio->id_precio."\n".$this->precio);

        }
    }
}
