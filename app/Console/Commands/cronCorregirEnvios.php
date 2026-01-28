<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Envio;

class cronCorregirEnvios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'corregir:envios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para eliminar los envios duplicados de los pedidos';

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
        dump('<<<<< ! >>>>> Ejecutando comando "corregir:envios" <<<<< ! >>>>>');
        Log::info('<<<<< ! >>>>> Ejecutando comando "corregir:envios" <<<<< ! >>>>>');

        $cant_deletes = 0;
        $pedidos = DB::select('select count(*) as cantidad, id_pedido from envio where fecha_envio >= "' . hoy() . ' 00:00:00" group by id_pedido having count(*) > 1 order by cantidad desc');
        foreach ($pedidos as $pos_p => $p) {
            $envios = Envio::where('id_pedido', $p->id_pedido)->orderBy('id_envio')->get();
            $cant_deletes++;
            foreach ($envios as $pos_env => $env) {
                if ($pos_env > 0) {
                    $env->delete();
                }
            }
        }
        if ($cant_deletes > 0)
            dump('SE CORRIGIERON: ' . $cant_deletes . ' PEDIDO(s)');
        else
            dump('NO HAY ENVIOS DUPLICADOS');

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "corregir:envios" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "corregir:envios" <<<<< * >>>>>');
    }
}
