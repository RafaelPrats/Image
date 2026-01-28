<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Ciclo;
use yura\Modelos\EnraizamientoSemanal;
use yura\Modelos\PropagDisponibilidad;
use yura\Modelos\ProyeccionModulo;
use yura\Modelos\Semana;
use yura\Modelos\Variedad;

class PropagUpdateDisponibilidad extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:propag_disponibilidad {semana_desde=0} {semana_hasta=0} {variedad=0} {obligatorio=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para actualizar el cuadro de disponibilidades semana por semana (por variedad)';

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
        Log::info('<<<<< ! >>>>> Ejecutando comando "update:propag_disponibilidad" <<<<< ! >>>>>');

        $sem_parametro_desde = $this->argument('semana_desde');
        $sem_parametro_hasta = $this->argument('semana_hasta');
        $variedad_parametro = $this->argument('variedad');
        $obligatorio_parametro = $this->argument('obligatorio');

        if ($sem_parametro_desde <= $sem_parametro_hasta) {
            if ($sem_parametro_desde != 0)
                $semana_desde = Semana::All()->where('estado', 1)->where('codigo', $sem_parametro_desde)->first();
            else
                $semana_desde = getSemanaByDate(hoy());
            if ($sem_parametro_hasta != 0)
                $semana_hasta = Semana::All()->where('estado', 1)->where('codigo', $sem_parametro_hasta)->first();
            else
                $semana_hasta = getSemanaByDate(opDiasFecha('+', 119, date('Y-m-d')));

            if ($variedad_parametro != 0)
                $variedades = Variedad::where('id_variedad', $variedad_parametro)->get();
            else
                $variedades = Variedad::where('estado', 1)->get();

            $semanas = DB::table('semana')
                ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
                ->where('codigo', '>=', $semana_desde->codigo)
                ->where('codigo', '<=', $semana_hasta->codigo)
                ->get();
            foreach ($variedades as $pos_var => $var) {
                foreach ($semanas as $pos_sem => $sem) {
                    dump('var: ' . ($pos_var + 1) . '/' . count($variedades) . ' - sem: ' . ($pos_sem + 1) . '/' . count($semanas));
                    $model = PropagDisponibilidad::All()
                        ->where('id_variedad', $var->id_variedad)
                        ->where('semana', $sem->codigo)
                        ->first();
                    if ($model == '') {
                        $model = new PropagDisponibilidad();
                        $model->id_variedad = $var->id_variedad;
                        $model->semana = $sem->codigo;
                        $model->desecho = $var->desecho_propag_disponibilidad;
                    }
                    $semana_pasada = getSemanaByDate(opDiasFecha('-', 7, $sem->fecha_inicial));
                    $semana_pasada_ini = getSemanaByDate(opDiasFecha('-', 21, $sem->fecha_inicial));
                    $anterior = PropagDisponibilidad::All()
                        ->where('id_variedad', $var->id_variedad)
                        ->where('semana', $semana_pasada->codigo)
                        ->first();
                    /* saldo_inicial */
                    $model->saldo_inicial = $anterior != '' ? $anterior->saldo : 0;
                    /* plantas_sembradas */
                    $plantas_sembradas = DB::table('enraizamiento_semanal')
                        ->select(DB::raw('sum(cantidad_siembra) as cantidad'))
                        ->where('id_variedad', $var->id_variedad)
                        ->where('semana_fin', $sem->codigo)
                        ->get()[0]->cantidad;
                    $model->plantas_sembradas = $plantas_sembradas > 0 ? $plantas_sembradas : 0;
                    /* semana_disponible */
                    $enrz = EnraizamientoSemanal::All()
                        ->where('id_variedad', $var->id_variedad)
                        ->where('semana_ini', $sem->codigo)
                        ->first();
                    $model->semana_disponible = $enrz != '' ? $enrz->semana_fin : 0;
                    /* plantas_disponibles */
                    $model->plantas_disponibles = $model->saldo_inicial + $model->plantas_sembradas - $model->desecho();
                    /* requerimientos */
                    if ($obligatorio_parametro == 1 || $model->mantener_cambios == 0) {
                        $podas = Ciclo::where('estado', 1)
                            ->where('id_variedad', $var->id_variedad)
                            ->where('poda_siembra', 'P')
                            ->where('fecha_inicio', '>=', $semana_pasada_ini->fecha_inicial)
                            ->where('fecha_inicio', '<=', $semana_pasada->fecha_final)
                            ->get();
                        $siembras = Ciclo::where('estado', 1)
                            ->where('id_variedad', $var->id_variedad)
                            ->where('poda_siembra', 'S')
                            ->where('fecha_inicio', '>=', $sem->fecha_inicial)
                            ->where('fecha_inicio', '<=', $sem->fecha_final)
                            ->get();
                        $proys_S = ProyeccionModulo::where('estado', 1)
                            ->where('id_variedad', $var->id_variedad)
                            ->where('tipo', 'S')
                            ->where('fecha_inicio', '>=', $sem->fecha_inicial)
                            ->where('fecha_inicio', '<=', $sem->fecha_final)
                            ->get();
                        $proys_P = ProyeccionModulo::where('estado', 1)
                            ->where('id_variedad', $var->id_variedad)
                            ->where('tipo', 'P')
                            ->where('fecha_inicio', '>=', $semana_pasada_ini->fecha_inicial)
                            ->where('fecha_inicio', '<=', $semana_pasada->fecha_final)
                            ->get();
                        $requerimientos = '';
                        foreach ($podas as $c) {
                            $num_sem = intval(difFechas($sem->fecha_inicial, $c->fecha_inicio)->days / 7) + 1;
                            if ($num_sem > 1)
                                $plantas = porcentaje(10, $c->plantas_iniciales, 2);
                            else
                                $plantas = $c->plantas_muertas;

                            $plantas = round($plantas);

                            if ($requerimientos != '')
                                $requerimientos .= '|' . $c->id_modulo . '+' . $c->modulo->nombre . '+' . $plantas . '+P';
                            else
                                $requerimientos = $c->id_modulo . '+' . $c->modulo->nombre . '+' . $plantas . '+P';
                        }
                        foreach ($siembras as $c) {
                            if ($requerimientos != '')
                                $requerimientos .= '|' . $c->id_modulo . '+' . $c->modulo->nombre . '+' . $c->plantas_iniciales . '+S';
                            else
                                $requerimientos = $c->id_modulo . '+' . $c->modulo->nombre . '+' . $c->plantas_iniciales . '+S';
                        }
                        foreach ($proys_S as $c) {
                            if ($requerimientos != '')
                                $requerimientos .= '|' . $c->id_modulo . '+' . $c->modulo->nombre . '+' . $c->plantas_iniciales . '+S';
                            else
                                $requerimientos = $c->id_modulo . '+' . $c->modulo->nombre . '+' . $c->plantas_iniciales . '+S';
                        }
                        foreach ($proys_P as $c) {
                            $last_ciclo = $c->last_ciclo();

                            if ($last_ciclo != '') { // tiene un ciclo actual
                                if ($last_ciclo->plantas_muertas > 0) { // tiene plantas muertas el ciclo actual
                                    $plantas = $last_ciclo->plantas_muertas + porcentaje(20, $last_ciclo->plantas_muertas, 2);
                                } else {    // NO tiene plantas muertas el ciclo actual
                                    $plantas = porcentaje(20, $c->plantas_iniciales, 2);
                                }
                            }

                            $num_sem = intval(difFechas($sem->fecha_inicial, $c->fecha_inicio)->days / 7) + 1;
                            if ($num_sem > 1)
                                $plantas = porcentaje(10, $c->plantas_iniciales, 2);

                            $plantas = round($plantas);

                            if ($requerimientos != '')
                                $requerimientos .= '|' . $c->id_modulo . '+' . $c->modulo->nombre . '+' . $plantas . '+P';
                            else
                                $requerimientos = $c->id_modulo . '+' . $c->modulo->nombre . '+' . $plantas . '+P';
                        }
                        $model->requerimientos = $requerimientos;
                    }
                    /* saldo */
                    $model->saldo = $model->plantas_disponibles - $model->calcular_requerimientos();
                    /* destino_plantas_sembradas */
                    $destino_plantas_sembradas = DB::table('enraizamiento_semanal')
                        ->select(DB::raw('sum(cantidad_siembra) as cantidad'), 'semana_ini')
                        ->where('id_variedad', $var->id_variedad)
                        ->where('semana_fin', $sem->codigo)
                        ->groupBy('semana_ini')
                        ->get();
                    $value = '';
                    foreach ($destino_plantas_sembradas as $c) {
                        if ($value != '')
                            $value .= '|' . $c->semana_ini . '+' . $c->cantidad;
                        else
                            $value = $c->semana_ini . '+' . $c->cantidad;
                    }
                    $model->destino_plantas_sembradas = $value;
                    $model->save();
                }
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "update:propag_disponibilidad" <<<<< * >>>>>');
    }
}
