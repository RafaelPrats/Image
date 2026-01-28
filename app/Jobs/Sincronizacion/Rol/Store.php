<?php

namespace yura\Jobs\Sincronizacion\Rol;

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
    public $rol;

    public function __construct($rol)
    {
        $this->rol = $rol;
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

                $existeRol = Controller::objetoConsulta('rol',[
                    ['id_rol', $this->rol->id_rol]
                ])->exists();

                if(!$existeRol){

                    Controller::conexion()->table('rol')->insert($this->rol->toArray());

                }else{

                    Controller::conexion()->table('rol')
                    ->where('id_rol', $this->rol->id_rol)->update($this->rol->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el rol ".$this->rol->id_rol."\n".$this->rol);

        }
    }
}
