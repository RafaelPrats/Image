<?php

namespace yura\Jobs\Sincronizacion\ClasificacionVerde;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreMonitoreoCalibre implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $monitoreoCalibre;

    public function __construct($monitoreoCalibre)
    {
        $this->monitoreoCalibre= $monitoreoCalibre;
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

                $existeClasificacionVerde = Controller::objetoConsulta('monitoreo_calibre',[
                    ['id_monitoreo_calibre', $this->monitoreoCalibre->id_monitoreo_calibre]
                ])->exists();

                if(!$existeClasificacionVerde){

                    Controller::conexion()->table('monitoreo_calibre')->insert($this->monitoreoCalibre->toArray());

                }else{

                    Controller::conexion()->table('monitoreo_calibre')
                    ->where('id_monitoreo_calibre', $this->monitoreoCalibre->id_monitoreo_calibre)->update($this->monitoreoCalibre->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el monitoreo_calibre ".$this->monitoreoCalibre->id_monitoreo_calibre."\n".$this->monitoreoCalibre);

        }
    }
}
