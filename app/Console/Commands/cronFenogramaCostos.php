<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\AplicacionCampo;
use yura\Modelos\AplicacionMatriz;
use yura\Modelos\Ciclo;
use yura\Modelos\CostoHoras;
use yura\Modelos\FenogramaCostos;
use yura\Modelos\CicloLuz;

class cronFenogramaCostos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fenograma:costos {fecha=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para llenar la tabla fenograma_costos';

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
        dump('<<<<< ! >>>>> Ejecutando comando "fenograma:costos" <<<<< ! >>>>>');
        Log::info('<<<<< ! >>>>> Ejecutando comando "fenograma:costos" <<<<< ! >>>>>');

        $fecha = $this->argument('fecha');
        if ($fecha == 0)
            $fecha = hoy();

        $ciclos = Ciclo::where('estado', 1)
            ->where('fecha_inicio', '<=', $fecha)
            ->where('fecha_fin', '>=', $fecha)
            ->orderBy('fecha_inicio')
            ->get();

        foreach ($ciclos as $pos => $c) {
            dump('ciclos: ' . ($pos + 1) . '/' . count($ciclos));
            $model = $c->fenograma_costos;
            if ($model == '') {
                $model = new FenogramaCostos();
                $model->id_ciclo = $c->id_ciclo;
            }

            /* PLANTAS */
            $semanas = DB::table('semana')
                ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
                ->where('id_variedad', $c->id_variedad)
                ->where('fecha_final', '>=', $c->fecha_inicio)
                ->orderBy('codigo')
                ->limit(10)
                ->get();
            $array_codigos = [];
            foreach ($semanas as $sem)
                array_push($array_codigos, $sem->codigo);
            $disponibilidades = DB::table('propag_disponibilidad')
                ->where('id_variedad', $c->id_variedad)
                ->whereIn('semana', $array_codigos)
                ->orderBy('semana')
                ->get();
            $plantas = 0;
            foreach ($disponibilidades as $d) {
                if ($d->requerimientos != '')
                    foreach (explode('|', $d->requerimientos) as $req) {
                        $explode = explode('+', $req);
                        if ($explode[0] == $c->id_modulo)
                            $plantas += count($explode) >= 2 && $explode[2] > 0 ? $explode[2] : 0;
                    }
                if ($d->requerimientos_adicionales != '')
                    foreach (explode('|', $d->requerimientos_adicionales) as $req) {
                        $explode = explode('+', $req);
                        if ($explode[0] == $c->id_modulo)
                            $plantas += count($explode) >= 2 && $explode[2] > 0 ? $explode[2] : 0;
                    }
            }
            $model->plantas = round($plantas * 0.052, 2);

            /* LUZ */
            $query_luz = CicloLuz::where('id_ciclo', $c->id_ciclo)
                ->orderBy('fecha')
                ->get();

            $costo_luz = 0;
            foreach ($query_luz as $luz) {
                $inicio_luz = opDiasFecha('+', $luz->inicio_luz, $c->fecha_inicio);
                $fin_luz = opDiasFecha('+', $luz->inicio_luz + $luz->dias_proy + $luz->dias_adicional - 1, $c->fecha_inicio);
                if ($luz->fecha >= $inicio_luz && $luz->fecha <= $fin_luz) {
                    $costo_x_tipo = $luz->tipo_luz / 1000;
                    $costo_x_lampara = $costo_x_tipo * $luz->lamparas;
                    $costo_x_lampara = $costo_x_lampara * $luz->getHorasDia();
                    $costo_luz += $costo_x_lampara * 0.10;
                }
            }
            $model->luz = round($costo_luz, 2);

            /* GIBERELICO */
            $app_matriz = AplicacionMatriz::All()
                ->where('nombre', 'ACIDO GIBERELICO')
                ->first();
            $ids_app = [];
            foreach ($app_matriz->aplicaciones as $app)
                array_push($ids_app, $app->id_aplicacion);
            $app_campo = AplicacionCampo::whereIn('id_aplicacion', $ids_app)
                ->where('id_ciclo', $c->id_ciclo)
                ->orderBy('fecha')
                ->get();
            $costo_giberelico = 0;
            foreach ($app_campo as $labor) {
                $camas = $labor->camas;
                $litro_x_cama = $labor->litro_x_cama;
                $volumen = round($camas * $litro_x_cama);
                foreach ($labor->detalles as $det) {
                    if ($det->id_producto != '') {
                        $producto = $det->producto;
                        $dosis = $det->factor_conversion != '' ? round($det->dosis * $det->factor_conversion, 3) : $det->dosis;
                        $dosis = $dosis * $volumen;
                        if ($producto->nombre == 'ACIDO GIBERELICO ROBUST 90%') {
                            $dosis_acido_giberelico = $dosis;
                        }
                        if ($producto->nombre == 'ALCOHOL POTABLE') {
                            $dosis = $det->factor_conversion != '' ? round($det->dosis * $det->factor_conversion, 2) : $det->dosis;
                            $dosis = $dosis * $dosis_acido_giberelico;
                        }
                        $costo_giberelico += $dosis * $producto->precio;
                    }
                }
            }
            $model->giberelico = round($costo_giberelico, 2);

            /* DESBROTE */
            $app_matriz = AplicacionMatriz::All()
                ->where('nombre', 'DESBROTE')
                ->first();
            $ids_app = [];
            foreach ($app_matriz->aplicaciones as $app)
                array_push($ids_app, $app->id_aplicacion);
            $app_campo = AplicacionCampo::whereIn('id_aplicacion', $ids_app)
                ->where('id_ciclo', $c->id_ciclo)
                ->orderBy('fecha')
                ->get();
            $hr_ordinaria = CostoHoras::All()
                ->where('nombre', 'ORDINARIA')
                ->first();
            $hr_50 = CostoHoras::All()
                ->where('nombre', '50%')
                ->first();
            $hr_100 = CostoHoras::All()
                ->where('nombre', '100%')
                ->first();
            $costo_desbrote = 0;
            foreach ($app_campo as $labor) {
                $dia_semana = date('w', strtotime($labor->fecha));
                if ($dia_semana >= 1 && $dia_semana <= 5) { // lunes a viernes
                    $horas_50 = $labor->horas_dia - 8;
                    $horas_ordinarias = $labor->horas_dia - $horas_50;
                    $costo_desbrote += $horas_ordinarias * $labor->hombres_dia * $hr_ordinaria->valor_hora_provisiones;
                    $costo_desbrote += $horas_50 * $labor->hombres_dia * $hr_50->valor_hora_provisiones;
                } else {    // sabado y domingo
                    $costo_desbrote += $labor->horas_dia * $labor->hombres_dia * $hr_100->valor_hora_provisiones;
                }
            }
            $model->desbrote = round($costo_desbrote, 2);

            $model->save();
        }


        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "fenograma:costos" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "fenograma:costos" <<<<< * >>>>>');
    }
}
