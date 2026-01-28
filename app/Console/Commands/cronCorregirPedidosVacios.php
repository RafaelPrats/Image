<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\DistribucionMixtos;
use yura\Modelos\Pedido;

class cronCorregirPedidosVacios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'corregir:pedidos_vacios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para eliminar los pedidos que no tienen detalle_pedido';

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
        dump('<<<<< ! >>>>> Ejecutando comando "corregir:pedidos_vacios" <<<<< ! >>>>>');
        Log::info('<<<<< ! >>>>> Ejecutando comando "corregir:pedidos_vacios" <<<<< ! >>>>>');

        $ids_pedidos = DB::table('detalle_pedido')
            ->select('id_pedido')->distinct()
            ->get()->pluck('id_pedido')->toArray();
        $pedidos_vacios = Pedido::whereNotIn('id_pedido', $ids_pedidos)
            ->get();
        foreach ($pedidos_vacios as $pos => $p) {
            dump('pedido: ' . ($pos + 1) . '/' . count($pedidos_vacios));
            $p->delete();
        }
        $distribuciones_vacias = DistribucionMixtos::whereNotIn('id_pedido', $ids_pedidos)
            ->get();
        foreach ($distribuciones_vacias as $pos => $p) {
            dump('distribucion: ' . ($pos + 1) . '/' . count($distribuciones_vacias));
            //$p->delete();
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "corregir:pedidos_vacios" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "corregir:pedidos_vacios" <<<<< * >>>>>');
    }
}
