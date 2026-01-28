<?php

namespace yura\Jobs\Sincronizacion\Notificacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreUserNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $userNotificacion;

    public function __construct($userNotificacion)
    {
        $this->userNotificacion = $userNotificacion;
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

                $existeNotificacion = Controller::objetoConsulta('user_notificacion',[
                    ['id_user_notification', $this->userNotificacion->id_user_notification]
                ])->exists();

                if(!$existeNotificacion){

                    Controller::conexion()->table('user_notificacion')->insert($this->userNotificacion->toArray());

                }else{

                    Controller::conexion()->table('user_notificacion')
                    ->where('id_user_notification', $this->userNotificacion->id_user_notification)->update($this->userNotificacion->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la user_notification ".$this->userNotificacion->id_user_notification."\n".$this->userNotificacion);

        }
    }
}
