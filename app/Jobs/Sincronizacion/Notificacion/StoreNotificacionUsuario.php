<?php

namespace yura\Jobs\Sincronizacion\Notificacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreNotificacionUsuario implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $notificacionUsuario;

    public function __construct($notificacionUsuario)
    {
        $this->notificacionUsuario = $notificacionUsuario;
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

                $existeNotificacionUsuario = Controller::objetoConsulta('notificacion_usuario',[
                    ['id_notificacion_usuario', $this->notificacionUsuario->id_notificacion_usuario]
                ])->exists();

                if(!$existeNotificacionUsuario){

                    Controller::conexion()->table('notificacion_usuario')->insert($this->notificacionUsuario->toArray());

                }else{

                    Controller::conexion()->table('notificacion_usuario')
                    ->where('id_notificacion_usuario', $this->notificacionUsuario->id_notificacion_usuario)->update($this->notificacionUsuario->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la notificacion_usuario ".$this->notificacionUsuario->id_notificacion_usuario."\n".$this->notificacionUsuario);

        }
    }
}
