<?php

namespace yura\Jobs\Sincronizacion\Menu;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreGrupoMenu implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $grupoMenu;

    public function __construct($grupoMenu)
    {
        $this->grupoMenu = $grupoMenu;
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

                $existeUsuario = Controller::objetoConsulta('grupo_menu',[
                    ['id_grupo_menu', $this->grupoMenu->id_grupo_menu]
                ])->first();

                if(!isset($existeUsuario)){

                    Controller::conexion()->table('grupo_menu')->insert($this->grupoMenu->toArray());

                }else{

                    Controller::conexion()->table('grupo_menu')
                    ->where('id_grupo_menu', $this->grupoMenu->id_grupo_menu)->update($this->grupoMenu->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el grupo menu ".$this->grupoMenu->id_grupo_menu."\n".$this->grupoMenu);

        }
    }
}
