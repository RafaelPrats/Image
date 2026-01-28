<?php

namespace yura\Jobs\Sincronizacion\UsuarioSector;

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
    public $usuarioSector;

    public function __construct($usuarioSector)
    {
        $this->usuarioSector = $usuarioSector;
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

                $existeUsuario = Controller::objetoConsulta('usuario_sector',[
                    ['id_usuario_sector', $this->usuarioSector->id_usuario_sector]
                ])->first();

                if(!isset($existeUsuario)){

                    Controller::conexion()->table('usuario_sector')->insert($this->usuarioSector->toArray());

                }else{

                    Controller::conexion()->table('usuario_sector')
                    ->where('id_usuario_sector', $this->usuarioSector->id_usuario_sector)->update($this->usuarioSector->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el usuario sector".$this->usuarioSector->id_usuario_sector."\n".$this->usuarioSector);

        }
    }
}
