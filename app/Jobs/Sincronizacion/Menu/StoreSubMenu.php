<?php

namespace yura\Jobs\Sincronizacion\Menu;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreSubMenu implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $subMenu;

    public function __construct($subMenu)
    {
        $this->subMenu = $subMenu;
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

                $existeUsuario = Controller::objetoConsulta('submenu',[
                    ['id_submenu', $this->subMenu->id_submenu]
                ])->first();

                if(!isset($existeUsuario)){

                    Controller::conexion()->table('submenu')->insert($this->subMenu->toArray());

                }else{

                    Controller::conexion()->table('submenu')
                    ->where('id_submenu', $this->subMenu->id_submenu)->update($this->subMenu->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el sub menu ".$this->subMenu->id_submenu."\n".$this->subMenu);

        }
    }
}
