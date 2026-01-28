<?php

namespace yura\Jobs\Sincronizacion\Rol;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreRolMenu implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $rolSubMenu;
    public $tries = 2;

    public function __construct($rolSubMenu)
    {
        $this->rolSubMenu = $rolSubMenu;
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

                $existeRolSubMenu = Controller::objetoConsulta('rol_submenu',[
                    ['id_rol_submenu', $this->rolSubMenu->id_rol_submenu]
                ])->exists();

                if(!$existeRolSubMenu){

                    Controller::conexion()->table('rol_submenu')->insert($this->rolSubMenu->toArray());

                }else{

                    Controller::conexion()->table('rol_submenu')
                    ->where('id_rol_submenu', $this->rolSubMenu->id_rol_submenu)->update($this->rolSubMenu->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el rol submenu ".$this->rolSubMenu->id_rol_submenu."\n".$this->rolSubMenu);

        }
    }
}
