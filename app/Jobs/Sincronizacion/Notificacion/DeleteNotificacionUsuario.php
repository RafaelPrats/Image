<?php

namespace yura\Jobs\Sincronizacion\Notificacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class DeleteNotificacionUsuario implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    private $notificacionUSuario;

    public function __construct($notificacionUSuario)
    {
        $this->notificacionUSuario = $notificacionUSuario;
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

                Controller::objetoConsulta('notificacion_usuario',[
                    ['id_notificacion_usuario', $this->notificacionUSuario->id_notificacion_usuario]
                ])->delete();

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó eliminar la notificacion_usuario".$this->notificacionUSuario->id_notificacion_usuario."\n".$this->notificacionUSuario);

        }
    }
}
