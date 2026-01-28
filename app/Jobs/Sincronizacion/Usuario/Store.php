<?php

namespace yura\Jobs\Sincronizacion\Usuario;

use Illuminate\Bus\Queueable;
//use Illuminate\Queue\SerializesModels;
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
    public $usuario;

    public function __construct($usuario)
    {
        $this->usuario = $usuario;
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

                $existeUsuario = Controller::objetoConsulta('usuario',[
                    ['id_usuario', $this->usuario->id_usuario]
                ])->first();

                if(!isset($existeUsuario)){

                    Controller::conexion()->table('usuario')->insert($this->usuario->toArray());

                }else{

                    Controller::conexion()->table('usuario')
                    ->where('id_usuario', $this->usuario->id_usuario)->update($this->usuario->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el usuario ".$this->usuario->nombre_completo."\n".$this->usuario);

        }
    }
}
