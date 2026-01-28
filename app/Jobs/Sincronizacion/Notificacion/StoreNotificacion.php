<?php

namespace yura\Jobs\Sincronizacion\Notificacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreNotificacion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $notificacion;

    public function __construct($notificacion)
    {
        $this->notificacion = $notificacion;
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

                $existeNotificacion = Controller::objetoConsulta('notificacion',[
                    ['id_notificacion', $this->notificacion->id_notificacion]
                ])->exists();

                if(!$existeNotificacion){

                    Controller::conexion()->table('notificacion')->insert($this->notificacion->toArray());

                }else{

                    Controller::conexion()->table('notificacion')
                    ->where('id_notificacion', $this->notificacion->id_notificacion)->update($this->notificacion->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar la notificacion ".$this->notificacion->nombre."\n".$this->notificacion);

        }
    }
}
