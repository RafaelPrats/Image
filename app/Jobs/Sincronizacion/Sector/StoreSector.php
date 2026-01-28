<?php

namespace yura\Jobs\Sincronizacion\Sector;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreSector implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $sector;

    public function __construct($sector)
    {
        $this->sector = $sector;
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

                $existeSector = Controller::objetoConsulta('sector',[
                    ['id_sector', $this->sector->id_sector]
                ])->exists();

                if(!$existeSector){

                    Controller::conexion()->table('sector')->insert($this->sector->toArray());

                }else{

                    Controller::conexion()->table('sector')
                    ->where('id_sector', $this->sector->id_sector)->update($this->sector->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el sector ".$this->sector->id_sector."\n".$this->sector);

        }
    }
}
