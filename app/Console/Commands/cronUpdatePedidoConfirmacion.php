<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\PedidoConfirmacion;

class cronUpdatePedidoConfirmacion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:pedido_confirmacion {fecha=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para actualizar la tabla pedido_confirmacion';

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
        //dump('<<<<< ! >>>>> Ejecutando comando "update:pedido_confirmacion" <<<<< ! >>>>>');
        Log::info('<<<<< ! >>>>> Ejecutando comando "update:pedido_confirmacion" <<<<< ! >>>>>');

        $fecha = $this->argument('fecha');
        //$fecha = $fecha == 0 ? hoy() : $fecha;

        $plantas = DB::table('pedido as p')
            ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
            ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->select(
                'v.id_planta',
            )->distinct()
            ->where('p.estado', '=', 1)
            ->where('p.fecha_pedido', $fecha)
            ->get();
        $ids_plantas = [];
        foreach ($plantas as $pos => $p) {
            //dump('planta: ' . ($pos + 1) . '/' . count($plantas));
            $existe = DB::table('pedido_confirmacion')
                ->where('id_planta', $p->id_planta)
                ->where('fecha', $fecha)
                ->get()
                ->first();
            if ($existe == '') {
                $model = new PedidoConfirmacion();
                $model->fecha = $fecha;
                $model->id_planta = $p->id_planta;
                $model->ejecutado = 0;
                $model->save();
            } else {
                $model = PedidoConfirmacion::find($existe->id_pedido_confirmacion);
            }
            $ids_plantas[] = $p->id_planta;
        }

        $delete_query = PedidoConfirmacion::where('fecha', $fecha)
            ->whereNotIn('id_planta', $ids_plantas)
            ->delete();

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        //dump('<*> DURACION: ' . $time_duration . '  <*>');
        //dump('<<<<< * >>>>> Fin satisfactorio del comando "update:pedido_confirmacion" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "update:pedido_confirmacion" <<<<< * >>>>>');
    }
}
