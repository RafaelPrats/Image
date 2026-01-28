<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\PedidoConfirmacion;

class cronCorregirPedidoConfirmacion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'corregir:pedido_confirmacion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para corregir la tabla pedido_confirmacion';

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
        dump('<<<<< ! >>>>> Ejecutando comando "corregir:pedido_confirmacion" <<<<< ! >>>>>');
        Log::info('<<<<< ! >>>>> Ejecutando comando "corregir:pedido_confirmacion" <<<<< ! >>>>>');

        $plantas = DB::table('planta')
            ->where('estado', 1)
            ->orderBy('orden')
            ->get();
        foreach ($plantas as $pos => $p) {
            dump('planta: ' . ($pos + 1) . '/' . count($plantas));
            $fechas = DB::table('pedido_confirmacion')
                ->select('fecha')->distinct()
                ->where('id_planta', $p->id_planta)
                ->orderBy('fecha')
                ->get();
            foreach ($fechas as $pos_f => $f) {
                $query = PedidoConfirmacion::where('id_planta', $p->id_planta)
                    ->where('fecha', $f->fecha)
                    ->orderBy('ejecutado', 'desc')
                    ->get();
                foreach ($query as $pos_q => $q) {
                    if ($pos_q > 0) {
                        $q->delete();
                        dump('CORREGIR planta: ' . $p->nombre . '; fecha: ' . $f->fecha);
                    }
                }
            }

            /* BORRAR FECHAS */
            $fechas = DB::table('pedido')
                ->select('fecha_pedido')->distinct()
                ->where('fecha_pedido', '>=', opDiasFecha('-', 2, hoy()))
                ->where('fecha_pedido', '<=', opDiasFecha('+', 21, hoy()))
                ->orderBy('fecha_pedido')
                ->get()->pluck('fecha_pedido')->toArray();
            foreach ($fechas as $f) {
                $pedidos = DB::table('pedido as p')
                    ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                    ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                    ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                    ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                    ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                    ->select('p.id_pedido')->distinct()
                    ->where('p.estado', '=', 1)
                    ->where('v.id_planta', '=', $p->id_planta)
                    ->where('p.fecha_pedido', $f)
                    ->get();
                if (count($pedidos) == 0) {  // no hay pedidos de la planta en la fecha $f
                    $delete = PedidoConfirmacion::where('id_planta', $p->id_planta)
                        ->where('fecha', $f)
                        ->first();
                    if ($delete != '') {
                        $delete->delete();
                        dump('ELIMINAR planta: ' . $p->nombre . '; fecha: ' . $f);
                    }
                } else {
                    $ped_conf = PedidoConfirmacion::where('id_planta', $p->id_planta)
                        ->where('fecha', $f)
                        ->first();
                    if ($ped_conf == '') {
                        $ped_conf = new PedidoConfirmacion();
                        $ped_conf->id_planta = $p->id_planta;
                        $ped_conf->fecha = $f;
                        $ped_conf->ejecutado = 0;
                        $ped_conf->save();
                        dump('CREANDO planta: ' . $p->nombre . '; fecha: ' . $f);
                    }
                }
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "corregir:pedido_confirmacion" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "corregir:pedido_confirmacion" <<<<< * >>>>>');
    }
}
