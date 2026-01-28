<?php

namespace yura\Jobs\Sincronizacion\Menu;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreMenu implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $menu;

    public function __construct($menu)
    {
        $this->menu = $menu;
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

                $existeUsuario = Controller::objetoConsulta('menu',[
                    ['id_menu', $this->menu->id_menu]
                ])->first();

                if(!isset($existeUsuario)){

                    Controller::conexion()->table('menu')->insert($this->menu->toArray());

                }else{

                    Controller::conexion()->table('menu')
                    ->where('id_menu', $this->menu->id_menu)->update($this->menu->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el menu ".$this->menu->id_menu."\n".$this->menu);

        }
    }
}
