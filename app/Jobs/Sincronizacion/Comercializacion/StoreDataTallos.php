<?php

namespace yura\Jobs\Sincronizacion\Comercializacion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreDataTallos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $dataTallos;

    public function __construct($dataTallos)
    {
        $this->dataTallos = $dataTallos;
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

                $existeDatoExportacion = Controller::objetoConsulta('data_tallos',[
                    ['id_data_tallos', $this->dataTallos->id_data_tallos]
                ])->exists();

                if(!$existeDatoExportacion){

                    Controller::conexion()->table('data_tallos')->insert($this->dataTallos->toArray());

                }else{

                    Controller::conexion()->table('data_tallos')
                    ->where('id_data_tallos', $this->dataTallos->id_data_tallos)->update($this->dataTallos->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el data_tallos ".$this->dataTallos->id_data_tallos."\n".$this->dataTallos);

        }
    }
}
