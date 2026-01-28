<?php

namespace yura\Jobs\Sincronizacion\loteRe;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Http\Controllers\Controller;

class StoreLoteRe implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public $loteRe;

    public function __construct($loteRe)
    {
        $this->loteRe = $loteRe;
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

                $existeLoteRe = Controller::objetoConsulta('lote_re',[
                    ['id_lote_re', $this->loteRe->id_lote_re]
                ])->exists();

                if(!$existeLoteRe){

                    Controller::conexion()->table('lote_re')->insert($this->loteRe->toArray());

                }else{

                    Controller::conexion()->table('lote_re')
                    ->where('id_lote_re', $this->loteRe->id_lote_re)->update($this->loteRe->toArray());

                }

            });

        }else{

            $this->fail("No hubo ping en la conexión con el servidor local cuando se intentó guardar el lote_re ".$this->loteRe->id_lote_re."\n".$this->loteRe);

        }
    }
}
