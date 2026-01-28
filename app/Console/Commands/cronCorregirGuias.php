<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Envio;

class cronCorregirGuias extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'corregir:guias';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para eliminar los campos guia_madre; guia_hija y dae en los pedidos futuros';

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
        dump('<<<<< ! >>>>> Ejecutando comando "corregir:guias" <<<<< ! >>>>>');
        Log::info('<<<<< ! >>>>> Ejecutando comando "corregir:guias" <<<<< ! >>>>>');

        /*$query = Envio::where('fecha_envio', '>=', opDiasFecha('+', 2, hoy()))
            ->get();
        foreach ($query as $pos => $q) {
            if ($q->guia_madre != '' || $q->guia_hija != '' || $q->dae != '') {
                dump(($pos + 1) . '/' . count($query) . ' corregir');
                $q->guia_madre = '';
                $q->guia_hija = '';
                $q->dae = '';
                $q->save();
            }
        }*/

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "corregir:guias" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "corregir:guias" <<<<< * >>>>>');
    }
}
