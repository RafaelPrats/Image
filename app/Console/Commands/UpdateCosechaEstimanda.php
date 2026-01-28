<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\ResumenCosechaEstimada;

class UpdateCosechaEstimanda extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:cosecha_estimada {variedad=0} {longitud=0} {fecha=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        //dump('<<<<< ! >>>>> Ejecutando comando "cosecha:estimada" <<<<< ! >>>>>');
        Log::info('<<<<< ! >>>>> Ejecutando comando "cosecha:estimada" <<<<< ! >>>>>');
        $variedad_par = $this->argument('variedad');
        $longitud_par = $this->argument('longitud');
        $fecha_par = $this->argument('fecha');

        $fecha = $fecha_par == 0 ? hoy() : $fecha_par;

        $variedades = DB::table('variedad')
            ->select('*')
            ->where('assorted', 0)
            ->where('estado', 1);
        if ($variedad_par != 0)
            $variedades = $variedades->where('id_variedad', $variedad_par);
        $variedades = $variedades->get();

        foreach ($variedades as $pos_v => $var) {
            $longitudes = DB::table('proy_longitudes')
                ->select('*')
                ->where('id_planta', $var->id_planta);
            if ($longitud_par != 0)
                $longitudes = $longitudes->where('nombre', $longitud_par);
            $longitudes = $longitudes->orderBy('orden')
                ->get();
            foreach ($longitudes as $pos_l => $long) {
                dump('fecha: ' . $fecha . '; var: ' . $pos_v . '/' . count($variedades) . '; long: ' . $pos_l . '/' . count($longitudes));
                $tallos_solidos = DB::table('proyecto as p')
                    ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                    ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                    ->select(
                        DB::raw('sum(cp.cantidad * dc.ramos_x_caja * dc.tallos_x_ramo) as cantidad')
                    )
                    ->where('p.estado', 1)
                    ->where('dc.id_variedad', $var->id_variedad)
                    ->where('dc.longitud_ramo', $long->nombre)
                    ->where('p.fecha', $fecha)
                    ->get()[0]->cantidad;

                $tallos_mixtos = DB::table('proyecto as p')
                    ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                    ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                    ->join('mixtos as m', 'm.id_detalle_caja_proyecto', '=', 'dc.id_detalle_caja_proyecto')
                    ->select(
                        DB::raw('sum(m.tallos) as cantidad')
                    )
                    ->where('p.estado', 1)
                    ->where('m.id_variedad', $var->id_variedad)
                    ->where('m.longitud_ramo', $long->nombre)
                    ->where('p.fecha', $fecha)
                    ->get()[0]->cantidad;

                $tallos_cambios = DB::table('cambios_pedido')
                    ->select(
                        DB::raw('sum(tallos) as tallos')
                    )
                    ->where('id_variedad', $var->id_variedad)
                    ->where('longitud_ramo', $long->nombre)
                    ->where('fecha_actual', $fecha)
                    ->get()[0]->tallos;
                $actual = $tallos_solidos + $tallos_mixtos - $tallos_cambios;

                $tallos_bqt = 0;    // desarrollar la distribucion de tallos en bouquets

                $model = ResumenCosechaEstimada::where('id_variedad', $var->id_variedad)
                    ->where('longitud', $long->nombre)
                    ->where('fecha', $fecha)
                    ->get()
                    ->first();
                if ($model == '') {
                    $model = new ResumenCosechaEstimada();
                    $model->id_variedad = $var->id_variedad;
                    $model->longitud = $long->nombre;
                    $model->fecha = $fecha;
                    $model->actual = $actual != '' ? $actual : 0;
                    $model->solidos = $tallos_solidos != '' ? $tallos_solidos : 0;
                    $model->mixtos = $tallos_mixtos != '' ? $tallos_mixtos : 0;
                    $model->cambios = $tallos_cambios != '' ? $tallos_cambios : 0;
                    $model->tallos_bqt = $tallos_bqt != '' ? $tallos_bqt : 0;
                    $model->save();
                } else {
                    $model->actual = $actual != '' ? $actual : 0;
                    $model->solidos = $tallos_solidos != '' ? $tallos_solidos : 0;
                    $model->mixtos = $tallos_mixtos != '' ? $tallos_mixtos : 0;
                    $model->cambios = $tallos_cambios != '' ? $tallos_cambios : 0;
                    $model->tallos_bqt = $tallos_bqt != '' ? $tallos_bqt : 0;
                    $model->save();
                }
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        //dump('<*> DURACION: ' . $time_duration . '  <*>');
        //dump('<<<<< * >>>>> Fin satisfactorio del comando "cosecha:estimada" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "cosecha:estimada" <<<<< * >>>>>');
    }
}
