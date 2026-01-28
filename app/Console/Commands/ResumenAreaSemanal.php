<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Semana;
use yura\Modelos\ResumenAreaSemanal as ResumenArea;
use yura\Modelos\Variedad;

class ResumenAreaSemanal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'area:update_semanal {semana_desde=0} {semana_hasta=0} {variedad=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para actualizar semanalmente la info sobre el área';

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
        dump('<<<<< ! >>>>> Ejecutando comando "area:update_semanal" <<<<< ! >>>>>');
        Log::info('<<<<< ! >>>>> Ejecutando comando "area:update_semanal" <<<<< ! >>>>>');

        $desde_par = $this->argument('semana_desde') != 0 ? $this->argument('semana_desde') : getSemanaByDate(opDiasFecha('-', 49, hoy()))->codigo;
        $hasta_par = $this->argument('semana_hasta') != 0 ? $this->argument('semana_hasta') : getSemanaByDate(hoy())->codigo;
        $variedad_par = $this->argument('variedad');

        $variedades = $variedad_par == 0 ? Variedad::where('estado', 1)->get() : [getVariedad($variedad_par)];

        $array_semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('codigo', '>=', $desde_par)
            ->where('codigo', '<=', $hasta_par)
            ->where('estado', 1)
            ->orderBy('codigo')
            ->get();

        $pos = 0;
        foreach ($array_semanas as $pos_s => $semana) {
            foreach ($variedades as $pos_v => $var) {
                $pos++;
                dump(porcentaje($pos, count($array_semanas) * count($variedades), 1) .
                    '% - sem: ' . ($pos_s + 1) . '/' . count($array_semanas) .
                    ' - var: ' . ($pos_v + 1) . '/' . count($variedades));
                $model = ResumenArea::All()
                    ->where('estado', 1)
                    ->where('id_variedad', $var->id_variedad)
                    ->where('codigo_semana', $semana->codigo)
                    ->first();
                if ($model == '') {
                    $model = new ResumenArea();
                    $resumenAreaSemanal = ResumenArea::orderBy('id_resumen_area_semanal','desc')->first();
                    $model->id_resumen_area_semanal = isset($resumenAreaSemanal->id_resumen_area_semanal) ? $resumenAreaSemanal->id_resumen_area_semanal+1 : 1;
                    $model->id_variedad = $var->id_variedad;
                    $model->codigo_semana = $semana->codigo;
                }

                $area = 0;
                $data = getAreaCiclosByRango($semana->codigo, $semana->codigo, $var->id_variedad);
                foreach ($data['variedades'] as $v) {
                    foreach ($v['ciclos'] as $c) {
                        foreach ($c['areas'] as $a) {
                            $area += $a;
                        }
                    }
                }

                $data_ciclos = getCiclosCerradosByRango($semana->codigo, $semana->codigo, $var->id_variedad);
                $ciclo = $data_ciclos['ciclo'];
                $tallos_m2 = $data_ciclos['area_cerrada'] > 0 ? round($data_ciclos['tallos_cosechados'] / $data_ciclos['area_cerrada'], 2) : 0;
                $area_cerrada = $data_ciclos['area_cerrada'];
                $tallos = $data_ciclos['tallos_cosechados'];
                $data_cosecha = getCosechaByRango($semana->codigo, $semana->codigo, $var->id_variedad);
                $calibre = $data_cosecha['calibre'];
                $ramos = $calibre > 0 ? round($tallos / $calibre, 2) : 0;
                $ramos_m2 = $area_cerrada > 0 ? round($ramos / $area_cerrada, 2) : 0;

                $ciclo_ano = $area_cerrada > 0 ? round(365 / $ciclo, 2) : 0;
                $ramos_m2_anno = $area_cerrada > 0 ? round($ciclo_ano * round($ramos / $area_cerrada, 2), 2) : 0;


                $model->area = $area;
                $model->ciclo = $ciclo;
                $model->tallos_m2 = $tallos_m2;
                $model->ramos_m2 = $ramos_m2;
                $model->ramos_m2_anno = $ramos_m2_anno;
                $model->save();
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "area:update_semanal" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "area:update_semanal" <<<<< * >>>>>');
    }
}
