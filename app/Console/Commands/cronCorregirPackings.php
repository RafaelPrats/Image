<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class cronCorregirPackings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'corregir:packings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para corregir los numero de packings repetidos en la tabla pedido';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ini = date('Y-m-d H:i:s');
        dump('<<<<< ! >>>>> Ejecutando comando "corregir:packings" <<<<< ! >>>>>');
        Log::info('<<<<< ! >>>>> Ejecutando comando "corregir:packings" <<<<< ! >>>>>');

        /*Artisan::call('comando:dev', [
            'comando' => 'corregir_packings',
        ]);*/

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "corregir:packings" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "corregir:packings" <<<<< * >>>>>');
    }
}
