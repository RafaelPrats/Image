<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Actividad;
use yura\Modelos\PropagDisponibilidad;
use yura\Modelos\ResumenPropagacion;
use yura\Modelos\Semana;
use yura\Modelos\Variedad;

class UpdateResumenPropagacion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:resumen_propagacion {desde=0} {hasta=0} {variedad=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para actualizar la tabla resumen_propagacion';

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
        Log::info('<<<<< ! >>>>> Ejecutando comando "update:resumen_propagacion" <<<<< ! >>>>>');

        $desde_par = $this->argument('desde');
        $hasta_par = $this->argument('hasta');
        $variedad_par = $this->argument('variedad');

        if ($desde_par <= $hasta_par) {
            if ($desde_par == 0)
                $desde_par = getSemanaByDate(opDiasFecha('-', 42, date('Y-m-d')))->codigo;
            if ($hasta_par == 0)
                $hasta_par = getSemanaByDate(date('Y-m-d'));

            $semanas = DB::table('semana')
                ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
                ->where('codigo', '>=', $desde_par)
                ->where('codigo', '<=', $hasta_par)
                ->orderBy('codigo')
                ->get();

            $variedades = Variedad::All()->where('estado', 1);
            if ($variedad_par != 0)
                $variedades = $variedades->where('id_variedad', $variedad_par);
            foreach ($semanas as $pos_s => $sem) {
                $esquejes_cosechados_semanal = DB::table('cosecha_plantas_madres as cos')
                    ->select(DB::raw('sum(cantidad) as cantidad'))
                    ->where('cos.fecha', '>=', $sem->fecha_inicial)
                    ->where('cos.fecha', '<=', $sem->fecha_final)
                    ->get()[0]->cantidad;
                $esquejes_cosechados_semanal = $esquejes_cosechados_semanal != '' ? $esquejes_cosechados_semanal : 0;
                foreach ($variedades as $pos_v => $var) {
                    dump('sem: ' . ($pos_s + 1) . '/' . count($semanas) . ' - var: ' . ($pos_v + 1) . '/' . count($variedades));
                    $model = ResumenPropagacion::All()
                        ->where('id_variedad', $var->id_variedad)
                        ->where('semana', $sem->codigo)
                        ->first();
                    if ($model == '') {
                        $model = new ResumenPropagacion();
                        $model->id_variedad = $var->id_variedad;
                        $model->semana = $sem->codigo;
                    }
                    /* esquejes_cosechados */
                    $esquejes_cosechados = DB::table('cosecha_plantas_madres as cos')
                        ->select(DB::raw('sum(cantidad) as cantidad'))
                        ->where('id_variedad', $var->id_variedad)
                        ->where('cos.fecha', '>=', $sem->fecha_inicial)
                        ->where('cos.fecha', '<=', $sem->fecha_final)
                        ->get()[0]->cantidad;
                    $model->esquejes_cosechados = $esquejes_cosechados != '' ? $esquejes_cosechados : 0;
                    /* plantas_sembradas */
                    $desde = $sem->fecha_inicial;
                    $hasta = $sem->fecha_final;
                    $plantas_sembradas = DB::table('ciclo_cama_contenedor as ccc')
                        ->join('ciclo_cama as cc', 'cc.id_ciclo_cama', '=', 'ccc.id_ciclo_cama')
                        ->join('contenedor_propag as cp', 'cp.id_contenedor_propag', '=', 'ccc.id_contenedor_propag')
                        ->select(DB::raw('sum(ccc.cantidad * cp.cantidad) as cantidad'))
                        ->where('cc.id_variedad', $var->id_variedad)
                        ->Where(function ($q) use ($desde, $hasta) {
                            $q->where('cc.fecha_fin', '>=', $desde)
                                ->where('cc.fecha_fin', '<=', $hasta)
                                ->orWhere(function ($q) use ($desde, $hasta) {
                                    $q->where('cc.fecha_inicio', '>=', $desde)
                                        ->where('cc.fecha_inicio', '<=', $hasta);
                                })
                                ->orWhere(function ($q) use ($desde, $hasta) {
                                    $q->where('cc.fecha_inicio', '<', $desde)
                                        ->where('cc.fecha_fin', '>', $hasta);
                                });
                        })
                        ->get()[0]->cantidad;
                    $model->plantas_sembradas = $plantas_sembradas != '' ? $plantas_sembradas : 0;
                    /* esquejes_x_planta */
                    $model->esquejes_x_planta = $model->plantas_sembradas > 0 ? round($model->esquejes_cosechados / $model->plantas_sembradas, 2) : 0;
                    /* costo_x_esqueje */
                    $actividades = Actividad::where('nombre', 'like', '%PLANTAS MADRES%')->get();
                    $ids_actividades = [];
                    foreach ($actividades as $act)
                        array_push($ids_actividades, $act->id_actividad);
                    $costos_mo = DB::table('costos_semana_mano_obra as c')
                        ->join('actividad_mano_obra as a', 'a.id_actividad_mano_obra', '=', 'c.id_actividad_mano_obra')
                        ->select(DB::raw('sum(c.valor) as cantidad'))
                        ->where('c.codigo_semana', $sem->codigo)
                        ->whereIn('a.id_actividad', $ids_actividades)
                        ->get()[0]->cantidad;
                    $costos_ins = DB::table('costos_semana as c')
                        ->join('actividad_producto as a', 'a.id_actividad_producto', '=', 'c.id_actividad_producto')
                        ->select(DB::raw('sum(c.valor) as cantidad'))
                        ->where('c.codigo_semana', $sem->codigo)
                        ->whereIn('a.id_actividad', $ids_actividades)
                        ->get()[0]->cantidad;
                    $model->costo_x_esqueje = $esquejes_cosechados_semanal > 0 ? round(($costos_mo + $costos_ins) / $esquejes_cosechados_semanal, 3) : 0;
                    /* costo_x_planta */
                    $requerimientos = 0;
                    $query_all = PropagDisponibilidad::where('semana', $sem->codigo)->get();
                    $propag_disponibilidad = PropagDisponibilidad::where('id_variedad', $var->id_variedad)
                        ->where('semana', $sem->codigo)
                        ->first();
                    foreach ($query_all as $q) {
                        $requerimientos += $q->calcular_requerimientos();
                    }
                    $costos_propagacion = DB::table('resumen_semanal_total')
                        ->select(DB::raw('sum(propagacion) as cantidad'))
                        ->where('codigo_semana', $sem->codigo)
                        ->get()[0]->cantidad;
                    $model->costo_x_planta = $requerimientos > 0 ? round($costos_propagacion / $requerimientos, 3) : 0;
                    /* requerimientos */
                    $model->requerimientos = $propag_disponibilidad->calcular_requerimientos();
                    /* porcentaje_requerimiento */
                    $model->porcentaje_requerimiento = 100 - $propag_disponibilidad->desecho;
                    $model->save();
                }
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "update:resumen_propagacion" <<<<< * >>>>>');
    }
}
