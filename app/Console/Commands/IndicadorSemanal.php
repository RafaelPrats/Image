<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Area;
use yura\Modelos\IndicadorSemana;
use yura\Modelos\IndicadorVariedad;
use yura\Modelos\IndicadorVariedadSemana;
use yura\Modelos\Semana;
use yura\Modelos\Variedad;

class IndicadorSemanal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indicador_semana:update {desde=0} {hasta=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para actualizar los indicadores por semana';

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
        Log::info('<<<<< ! >>>>> Ejecutando comando "indicador_semana:update" <<<<< ! >>>>>');

        $desde_par = $this->argument('desde');
        $hasta_par = $this->argument('hasta');

        if ($desde_par <= $hasta_par) {
            if ($desde_par != 0)
                $semana_desde = Semana::where('estado', 1)->where('codigo', $desde_par)->first();
            else
                $semana_desde = getSemanaByDate(date('Y-m-d'));
            if ($hasta_par != 0)
                $semana_hasta = Semana::where('estado', 1)->where('codigo', $hasta_par)->first();
            else
                $semana_hasta = getSemanaByDate(date('Y-m-d'));

            Log::info('SEMANA PARAMETRO DESDE: ' . $desde_par . ' => ' . $semana_desde->codigo);
            Log::info('SEMANA PARAMETRO HASTA: ' . $hasta_par . ' => ' . $semana_hasta->codigo);

            $array_semanas = DB::table('semana')
                ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
                ->where('codigo', '>=', $semana_desde->codigo)
                ->where('codigo', '<=', $semana_hasta->codigo)
                ->where('estado', 1)
                ->orderBy('codigo')
                ->get();
            $variedades = Variedad::where('estado', 1)->get();

            dump('========================== C9 Costos/m2 (-16 semanas) ===========================');
            /* ========================== C9 Costos/m2 (-16 semanas) =========================== */
            $indicador = getIndicadorByName('C9');  // Costos/m2 (-16 semanas)
            if ($indicador != '') {
                foreach ($array_semanas as $pos_s => $sem) {
                    dump('C9 - sem:' . ($pos_s + 1) . '/' . count($array_semanas));
                    $model = $indicador->getSemana($sem->codigo);
                    $model = IndicadorSemana::find(isset($model) ? $model->id_indicador_semana : 0);
                    if ($model == '') {
                        $model = new IndicadorSemana();
                        $indicadorSemana = IndicadorSemana::orderBy('id_indicador_semana', 'desc')->first();
                        $model->id_indicador_semana = isset($indicadorSemana) ? $indicadorSemana->id_indicador_semana + 1 : 1;
                        $model->id_indicador = $indicador->id_indicador;
                        $model->codigo_semana = $sem->codigo;
                    }

                    $sem_desde = getSemanaByDate(opDiasFecha('-', 84, $sem->fecha_inicial));   // 13 semana atras
                    $sem_hasta = $sem;

                    $costos = DB::table('resumen_costos_semanal')
                        ->select(DB::raw('sum(mano_obra + insumos + fijos + regalias) as cant'))
                        ->where('codigo_semana', '>=', $sem_desde->codigo)
                        ->where('codigo_semana', '<=', $sem_hasta->codigo)
                        ->get()[0]->cant;
                    $area = DB::table('resumen_area_semanal')
                        ->select(DB::raw('sum(area) as cant'))
                        ->where('codigo_semana', '>=', $sem_desde->codigo)
                        ->where('codigo_semana', '<=', $sem_hasta->codigo)
                        ->get()[0]->cant;

                    $valor = $area > 0 ? round(($costos / ($area / 13)) * 4, 2) : 0;

                    $model->valor = $valor;
                    $model->save();
                }
            }

            dump('========================== D9 Venta $/m2/año (-4 meses) ===========================');
            /* ========================== D9 Venta $/m2/año (-4 meses) =========================== */
            $indicador = getIndicadorByName('D9');  // Venta $/m2/año (-4 meses)
            if ($indicador != '') {
                foreach ($array_semanas as $pos_s => $sem) {
                    dump('D9 - sem:' . ($pos_s + 1) . '/' . count($array_semanas));
                    $model = $indicador->getSemana($sem->codigo);
                    $model = IndicadorSemana::find(isset($model) ? $model->id_indicador_semana : 0);
                    if ($model == '') {
                        $model = new IndicadorSemana();
                        $indicadorSemana = IndicadorSemana::orderBy('id_indicador_semana', 'desc')->first();
                        $model->id_indicador_semana = isset($indicadorSemana) ? $indicadorSemana->id_indicador_semana + 1 : 1;
                        $model->id_indicador = $indicador->id_indicador;
                        $model->codigo_semana = $sem->codigo;
                    }

                    $hasta_sem = getSemanaByDate(opDiasFecha('-', 7, $sem->fecha_inicial));
                    $desde_sem = getSemanaByDate(opDiasFecha('-', 91, $sem->fecha_inicial));

                    $venta_mensual = DB::table('resumen_semanal_total')
                        ->select(DB::raw('sum(valor) as cant'))
                        //->where('estado', 1)
                        ->where('codigo_semana', '>=', $desde_sem->codigo)
                        ->where('codigo_semana', '<=', $hasta_sem->codigo)
                        ->get()[0]->cant;

                    //$semana_desde = getSemanaByDate(opDiasFecha('-', 91, $desde_sem->fecha_inicial));   // 16 semanas atras
                    //$semana_hasta = $desde_sem;

                    //$data = getAreaCiclosByRango($semana_desde->codigo, $semana_hasta->codigo, 'T');
                    $data = getAreaCiclosByRango($desde_sem->codigo, $hasta_sem->codigo, 'T');
                    $area_anual = getAreaActivaFromData($data['variedades'], $data['semanas']) * 10000;

                    /*if ($sem == 2005)
                        dd($desde_sem->codigo, $hasta_sem->codigo, $venta_mensual, $semana_desde->codigo, $semana_hasta->codigo, $area_anual);*/

                    $model->valor = $area_anual > 0 ? round(($venta_mensual / $area_anual) * 3, 2) : 0;
                    $model->save();

                    /* ============================== INDICADOR x VARIEDAD ================================= */
                    foreach ($variedades as $pos_v => $var) {
                        dump('D9 - sem:' . ($pos_s + 1) . '/' . count($array_semanas) . ' - var:' . ($pos_v + 1) . '/' . count($variedades));
                        $ind_var = IndicadorVariedad::All()
                            ->where('id_indicador', $indicador->id_indicador)
                            ->where('id_variedad', $var->id_variedad)
                            ->first();
                        if ($ind_var != '') {
                            $model = $ind_var->getSemana($sem->codigo);
                            $model = IndicadorVariedadSemana::find(isset($model) ? $model->id_indicador_variedad_semana : 0);
                            if ($model == '') {
                                $model = new IndicadorVariedadSemana();
                                $indicadorVariedadSemana = IndicadorVariedadSemana::orderBy('id_indicador_variedad_semana', 'desc')->first();
                                $model->id_indicador_variedad_semana= isset($indicadorVariedadSemana) ? $indicadorVariedadSemana->id_indicador_variedad_semana + 1 : 1;
                                $model->id_indicador_variedad = $ind_var->id_indicador_variedad;
                                $model->codigo_semana = $sem->codigo;
                            }
                            /*$hasta_sem = $semana;
                            $desde_sem = getSemanaByDate(opDiasFecha('-', 112, $hasta_sem->fecha_inicial));*/

                            $venta_mensual = DB::table('proyeccion_venta_semanal_real')
                                ->select(DB::raw('sum(valor) as cant'))
                                ->where('id_variedad', $ind_var->id_variedad)
                                ->where('codigo_semana', '>=', $desde_sem->codigo)
                                ->where('codigo_semana', '<=', $hasta_sem->codigo)
                                ->get()[0]->cant;

                            //$semana_desde = getSemanaByDate(opDiasFecha('-', 112, $desde_sem->fecha_inicial));   // 16 semanas atras
                            //$semana_hasta = $desde_sem;

                            //$data = getAreaCiclosByRango($semana_desde->codigo, $semana_hasta->codigo, $ind_var->id_variedad);
                            $data = getAreaCiclosByRango($desde_sem->codigo, $hasta_sem->codigo, $ind_var->id_variedad);
                            $area_anual = getAreaActivaFromData($data['variedades'], $data['semanas']) * 10000;

                            $model->valor = $area_anual > 0 ? round(($venta_mensual / $area_anual) * 3, 2) : 0;
                            $model->save();
                        }
                    }
                }
            }

            dump('========================== R1 Rentabilidad (-4 meses) ===========================');
            /* ========================== R1 Rentabilidad (-4 meses) =========================== */
            $indicador = getIndicadorByName('R1');  // Rentabilidad (-4 meses)
            if ($indicador != '') {
                foreach ($array_semanas as $pos_s => $sem) {
                    dump('R1 - sem:' . ($pos_s + 1) . '/' . count($array_semanas));
                    $model = $indicador->getSemana($sem->codigo);
                    $model = IndicadorSemana::find(isset($model) ? $model->id_indicador_semana : 0);
                    if ($model == '') {
                        $model = new IndicadorSemana();
                        $indicadorSemana = IndicadorSemana::orderBy('id_indicador_semana', 'desc')->first();
                        $model->id_indicador_semana= isset($indicadorSemana) ? $indicadorSemana->id_indicador_semana + 1 : 1;
                        $model->id_indicador = $indicador->id_indicador;
                        $model->codigo_semana = $sem->codigo;
                    }

                    $valor = getIndicadorByName('D9')->getSemana($sem->codigo)->valor - getIndicadorByName('C9')->getSemana($sem->codigo)->valor;
                    $model->valor = $valor;
                    $model->save();

                    /* ============================== INDICADOR x VARIEDAD ================================= */
                    foreach ($variedades as $pos_v => $var) {
                        dump('R1 - sem:' . ($pos_s + 1) . '/' . count($array_semanas) . ' - var:' . ($pos_v + 1) . '/' . count($variedades));
                        $ind_var = IndicadorVariedad::All()
                            ->where('id_indicador', $indicador->id_indicador)
                            ->where('id_variedad', $var->id_variedad)
                            ->first();
                        if ($ind_var != '') {
                            $model = $ind_var->getSemana($sem->codigo);
                            if ($model == '') {
                                $model = new IndicadorVariedadSemana();
                                $model->id_indicador_variedad = $ind_var->id_indicador_variedad;
                                $model->codigo_semana = $sem;
                            }
                            $valor = getIndicadorByName('D9')->getVariedad($var->id_variedad)->getSemana($sem->codigo)->valor - getIndicadorByName('C9')->getSemana($sem->codigo)->valor;
                            $model->valor = $valor;
                            $model->save();
                        }
                    }
                }
            }

            dump('========================== C12 Costo x Planta (-4 semanas) ===========================');
            /* ========================== C12 Costo x Planta (-4 semanas) =========================== */
            $indicador = getIndicadorByName('C12');  // Costo x Planta (-4 semanas)
            if ($indicador != '') {
                foreach ($array_semanas as $pos_s => $sem) {
                    dump('C12 - sem:' . ($pos_s + 1) . '/' . count($array_semanas));
                    $model = $indicador->getSemana($sem->codigo);
                    $model = IndicadorSemana::find(isset($model) ? $model->id_indicador_semana : 0);
                    if ($model == '') {
                        $model = new IndicadorSemana();
                        $indicadorSemana = IndicadorSemana::orderBy('id_indicador_semana', 'desc')->first();
                        $model->id_indicador_semana= isset($indicadorSemana) ? $indicadorSemana->id_indicador_semana + 1 : 1;
                        $model->id_indicador = $indicador->id_indicador;
                        $model->codigo_semana = $sem->codigo;
                    }
                    $sem_desde = getSemanaByDate(opDiasFecha('-', 21, $sem->fecha_inicial));   // 4 semana atras
                    $sem_hasta = $sem;

                    $valor = DB::table('resumen_propagacion')
                        ->select(DB::raw('sum(costo_x_planta) as cantidad'), DB::raw('count(*) as positivos'))
                        ->where('semana', '>=', $sem_desde->codigo)
                        ->where('semana', '<=', $sem_hasta->codigo)
                        ->where('costo_x_planta', '>', 0)
                        ->get()[0];
                    $valor = $valor->positivos > 0 ? round($valor->cantidad / $valor->positivos, 4) : 0;

                    $model->valor = $valor;
                    $model->save();
                }
            }

            dump('========================== D7 Area en produccion (-4 semanas) ===========================');
            /* ========================== D7 Area en produccion (-4 semanas) =========================== */
            $indicador = getIndicadorByName('D7');  // Area en produccion (-4 semanas)
            if ($indicador != '') {
                foreach ($array_semanas as $pos_s => $sem) {
                    dump('D7 - sem:' . ($pos_s + 1) . '/' . count($array_semanas));
                    $model = $indicador->getSemana($sem->codigo);
                    $model = IndicadorSemana::find(isset($model) ? $model->id_indicador_semana : 0);
                    if ($model == '') {
                        $model = new IndicadorSemana();
                        $indicadorSemana = IndicadorSemana::orderBy('id_indicador_semana', 'desc')->first();
                        $model->id_indicador_semana= isset($indicadorSemana) ? $indicadorSemana->id_indicador_semana + 1 : 1;
                        $model->id_indicador = $indicador->id_indicador;
                        $model->codigo_semana = $sem->codigo;
                    }
                    $desde = getSemanaByDate(opDiasFecha('-', 28, $sem->fecha_inicial));
                    $hasta = getSemanaByDate(opDiasFecha('-', 7, $sem->fecha_inicial));

                    $area = 0;
                    $data_4semanas = getAreaCiclosByRango($desde->codigo, $hasta->codigo, 'T');

                    foreach ($data_4semanas['variedades'] as $var) {
                        foreach ($var['ciclos'] as $c) {
                            foreach ($c['areas'] as $a) {
                                $area += $a;
                            }
                        }
                    }

                    $model->valor = round($area / 4, 2);
                    $model->save();
                }
            }

            dump('========================== C3 Costos Campo/ha/semana (-4 semanas) ===========================');
            /* ========================== C3 Costos Campo/ha/semana (-4 semanas) =========================== */
            $indicador = getIndicadorByName('C3');  // Costo x Planta (-4 semanas)
            if ($indicador != '') {
                $ids_areas = [];
                $areas = Area::where('estado', 1)->where('nombre', 'like', 'CAMPO%')->get();
                foreach ($areas as $a)
                    array_push($ids_areas, $a->id_area);
                foreach ($array_semanas as $pos_s => $sem) {
                    dump('C3 - sem:' . ($pos_s + 1) . '/' . count($array_semanas));
                    $model = $indicador->getSemana($sem->codigo);
                    $model = IndicadorSemana::find(isset($model) ? $model->id_indicador_semana : 0);
                    if ($model == '') {
                        $model = new IndicadorSemana();
                        $indicadorSemana = IndicadorSemana::orderBy('id_indicador_semana', 'desc')->first();
                        $model->id_indicador_semana= isset($indicadorSemana) ? $indicadorSemana->id_indicador_semana + 1 : 1;
                        $model->id_indicador = $indicador->id_indicador;
                        $model->codigo_semana = $sem->codigo;
                    }
                    $sem_desde = getSemanaByDate(opDiasFecha('-', 21, $sem->fecha_inicial));
                    $sem_hasta = $sem;

                    $insumos = DB::table('costos_semana as c')
                        ->select(DB::raw('sum(c.valor) as cant'))
                        ->join('actividad_producto as ac', 'ac.id_actividad_producto', '=', 'c.id_actividad_producto')
                        ->join('actividad as a', 'a.id_actividad', '=', 'ac.id_actividad')
                        ->whereIn('a.id_area', $ids_areas)
                        ->where('c.codigo_semana', '>=', $sem_desde->codigo)
                        ->where('c.codigo_semana', '<=', $sem_hasta->codigo)
                        ->get()[0]->cant;
                    $mano_obra = DB::table('costos_semana_mano_obra as c')
                        ->select(DB::raw('sum(c.valor) as cant'))
                        ->join('actividad_mano_obra as am', 'am.id_actividad_mano_obra', '=', 'c.id_actividad_mano_obra')
                        ->join('actividad as a', 'a.id_actividad', '=', 'am.id_actividad')
                        ->whereIn('a.id_area', $ids_areas)
                        ->where('c.codigo_semana', '>=', $sem_desde->codigo)
                        ->where('c.codigo_semana', '<=', $sem_hasta->codigo)
                        ->get()[0]->cant;
                    $otros = DB::table('otros_gastos as o')
                        ->select(DB::raw('sum(o.gip + o.ga) as cant'))
                        ->whereIn('o.id_area', $ids_areas)
                        ->where('o.codigo_semana', '>=', $sem_desde->codigo)
                        ->where('o.codigo_semana', '<=', $sem_hasta->codigo)
                        ->get()[0]->cant;

                    $costos_total = $insumos + $mano_obra + $otros;
                    $area = getIndicadorByName('D7')->getSemana($sem->codigo);   // Área en producción (-4 semanas)

                    $valor = ($area != '' && $area->valor > 0) ? round(($costos_total / 4) / ($area->valor / 10000), 2) : 0;
                    $model->valor = $valor . '|' . $insumos . '+' . $mano_obra . '+' . $otros . '/' . ($area->valor / 10000);
                    $model->save();
                }
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "indicador_semana:update" <<<<< * >>>>>');
    }
}
