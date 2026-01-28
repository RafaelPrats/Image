<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Aplicacion;
use yura\Modelos\DetalleProyeccionCampoSemanalAplicacion;
use yura\Modelos\ProyeccionCampoSemanal;
use yura\Modelos\ProyeccionCampoSemanalAplicacion;

class UpdateProyeccionCampoSemanal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proyeccion:campo_semanal {modulo=0} {variedad=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para guardar en la tabla proyeccion_campo_semanal el resumen semanal de labores por modulo';

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
        Log::info('<<<<< ! >>>>> Ejecutando comando "proyeccion:campo_semanal" <<<<< ! >>>>>');
        dump('<<<<< ! >>>>> Ejecutando comando "proyeccion:campo_semanal" <<<<< ! >>>>>');
        $modulo = $this->argument('modulo');
        $variedad = $this->argument('variedad');

        $aplicaciones = Aplicacion::where('estado', 1)
            ->orderBy('nombre')->get();
        $semana_actual = getSemanaByDate(hoy());
        $ciclos = DB::table('ciclo as c')
            ->join('modulo as mod', 'mod.id_modulo', '=', 'c.id_modulo')
            ->select('c.id_modulo', 'mod.nombre', 'c.area', 'c.fecha_inicio', 'c.id_variedad', 'c.poda_siembra', 'c.curva', 'c.semana_poda_siembra')
            ->distinct()
            ->where('c.estado', 1)
            ->where('c.activo', 1);
        if ($modulo != 0)
            $ciclos = $ciclos->where('c.id_modulo', $modulo);
        if ($variedad != 0)
            $ciclos = $ciclos->where('c.id_variedad', $variedad);
        $ciclos = $ciclos->orderBy('c.fecha_inicio')
            ->get();

        $ids_modulos = [];
        foreach ($ciclos as $c)
            $ids_modulos[] = $c->id_modulo;

        $proys = DB::table('proyeccion_modulo as p')
            ->join('modulo as mod', 'mod.id_modulo', '=', 'p.id_modulo')
            ->select('p.id_modulo', 'mod.nombre', 'mod.area', 'p.fecha_inicio', 'p.id_variedad', 'p.poda_siembra', 'p.curva', 'p.semana_poda_siembra')
            ->distinct()
            ->where('p.estado', 1)
            ->where('mod.estado', 1)
            ->whereIn('p.id_modulo', $ids_modulos);
        if ($modulo != 0)
            $proys = $proys->where('p.id_modulo', $modulo);
        if ($variedad != 0)
            $proys = $proys->where('p.id_variedad', $variedad);
        $proys = $proys->orderBy('p.fecha_inicio')
            ->get();

        $num_proc = 0;

        /* APLICAR aplicaciones que comienzan en la semana del ciclo */
        dump('/* APLICAR aplicaciones que comienzan en la semana del ciclo */');
        foreach ($ciclos as $pos_c => $c) {
            $semana_ini = getSemanaByDate($c->fecha_inicio);
            $cant_dias = ((count(explode('-', $c->curva)) - 1) * 7) + (($c->semana_poda_siembra - 1) * 7);
            $semana_fin = getSemanaByDate(opDiasFecha('+', $cant_dias, $c->fecha_inicio));
            $semanas = DB::table('semana')
                ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
                ->where('estado', 1)
                ->where('codigo', '>=', $semana_ini->codigo)
                ->where('codigo', '<=', $semana_fin->codigo)
                ->orderBy('codigo')
                ->get();
            $num_proc++;
            dump(porcentaje($num_proc, count($ciclos) + count($proys), 1) . '%');

            foreach ($semanas as $pos_s => $sem) {
                dump('ciclo: ' . ($pos_c + 1) . '/' . count($ciclos) . ' - sem: ' . ($pos_s + 1) . '/' . count($semanas));
                $model = ProyeccionCampoSemanal::All()
                    ->where('id_modulo', $c->id_modulo)
                    ->where('id_variedad', $c->id_variedad)
                    ->where('semana', $sem->codigo)
                    //->where('poda_siembra', $c->poda_siembra)
                    ->first();
                if ($model == '') {
                    $model = new ProyeccionCampoSemanal();
                    $model->id_modulo = $c->id_modulo;
                    $model->id_variedad = $c->id_variedad;
                    $model->semana = $sem->codigo;
                    $model->poda_siembra = $c->poda_siembra;
                    $model->num_sem = $pos_s + 1;
                    $model->save();
                    $model = ProyeccionCampoSemanal::All()
                        ->where('id_modulo', $c->id_modulo)
                        ->where('id_variedad', $c->id_variedad)
                        ->where('semana', $sem->codigo)
                        ->where('poda_siembra', $c->poda_siembra)
                        ->first();
                } else {
                    $model->poda_siembra = $c->poda_siembra;
                    $model->num_sem = $pos_s + 1;
                    $model->save();
                }

                /* programar labores */
                if ($model->semana >= $semana_actual->codigo) {
                    DetalleProyeccionCampoSemanalAplicacion::join('proyeccion_campo_semanal_aplicacion as p', 'p.id_proyeccion_campo_semanal_aplicacion', '=', 'detalle_proyeccion_campo_semanal_aplicacion.id_proyeccion_campo_semanal_aplicacion')
                        ->select('detalle_proyeccion_campo_semanal_aplicacion.*')
                        ->where('p.id_proyeccion_campo_semanal', $model->id_proyeccion_campo_semanal)
                        ->where('p.estado', 'P')
                        ->delete();
                    ProyeccionCampoSemanalAplicacion::where('id_proyeccion_campo_semanal', $model->id_proyeccion_campo_semanal)
                        ->where('estado', 'P')
                        ->delete();
                    foreach ($aplicaciones as $pos_a => $app) {
                        $existe_variedad = false;
                        foreach ($app->variedades as $var)
                            if ($var->id_variedad == $c->id_variedad)
                                $existe_variedad = true;
                        if ($existe_variedad && ($app->poda_siembra == $model->poda_siembra || $app->poda_siembra == 'T')) {
                            // es la misma variedad del ciclo y la misma poda_siembra del ciclo
                            if (count($app->parametros) > 0) {   // buscar por parametros
                                dump('*********************** buscar por parametros: ' . $app->nombre);
                            } else {    // buscar en la misma tabla de aplicaciones
                                if ($model->num_sem >= $app->semana_ini && $model->num_sem <= $app->semana_ini + $app->repeticiones - 1) {
                                    // es una semana del ciclo dentro del rango de la aplicacion
                                    $repeticion = ($model->num_sem - $app->semana_ini) * $app->veces_x_semana;
                                    dump('mod: ' . $c->nombre . ' num_sem: ' . $model->num_sem . ' - hay que aplicar: ' . $app->nombre . ' - x' . $repeticion);

                                    for ($i = 1; $i <= $app->veces_x_semana; $i++) {
                                        $existe = ProyeccionCampoSemanalAplicacion::All()
                                            ->where('id_proyeccion_campo_semanal', $model->id_proyeccion_campo_semanal)
                                            ->where('app_nombre', $app->nombre)
                                            ->where('app_repeticion', ($repeticion + $i))
                                            ->where('app_litro_x_cama', $app->litro_x_cama)
                                            ->whereIn('estado', ['C', 'E', 'M'])
                                            ->first();
                                        if ($existe == '') {
                                            $proy_app = new ProyeccionCampoSemanalAplicacion();
                                            $proy_app->id_proyeccion_campo_semanal = $model->id_proyeccion_campo_semanal;
                                            $proy_app->app_nombre = $app->nombre;
                                            $proy_app->app_repeticion = $repeticion + $i;
                                            $proy_app->app_litro_x_cama = $app->litro_x_cama;
                                            $proy_app->app_continua = $app->continua;
                                            $proy_app->app_uso = $app->tipo;
                                            $proy_app->estado = 'P';
                                            $proy_app->camas = calcularCamas($c->area);
                                            $proy_app->save();
                                            $proy_app = ProyeccionCampoSemanalAplicacion::All()->last();

                                            /* aplicar detalles de labores */
                                            foreach ($app->detalles as $det) {
                                                $det_proy_app = new DetalleProyeccionCampoSemanalAplicacion();
                                                $det_proy_app->id_proyeccion_campo_semanal_aplicacion = $proy_app->id_proyeccion_campo_semanal_aplicacion;
                                                $det_proy_app->id_mano_obra = $det->id_mano_obra;
                                                $det_proy_app->id_producto = $det->id_producto;

                                                $parametro = '';
                                                $tipo_det = $det->getTipoParametros();
                                                dump('1*************************************************************' . $tipo_det);
                                                if ($tipo_det == -1) {  // tiene mas de un tipo de parametro
                                                    dump('tiene mas de un tipo de parametro');
                                                } elseif ($tipo_det == '') { // no tiene parametros
                                                    dump('no tiene parametros');
                                                } else {    // tiene parametros
                                                    if ($tipo_det == 'E') { // parametro estandar
                                                        dump('tiene parametros - parametro estandar');
                                                        $parametro = $det->parametros[0];
                                                        dump($parametro);
                                                    }
                                                    if ($tipo_det == 'D') { // parametro Delta Acum. 10 días
                                                        dump('tiene parametros - parametro Delta Acum. 10 días');
                                                        $last_fecha = DB::table('temperatura')
                                                            ->select(DB::raw('max(fecha) as fecha'))
                                                            ->where('estado', 1)
                                                            ->get()[0]->fecha;
                                                        $delta_acum_10_dias = DB::table('temperatura')
                                                            ->select(DB::raw('sum(maxima - minima) as cantidad'))
                                                            ->where('fecha', '>=', opDiasFecha('-', 9, $last_fecha))
                                                            ->where('fecha', '<=', $last_fecha)
                                                            ->get()[0]->cantidad;
                                                        $delta_acum_10_dias = round($delta_acum_10_dias);
                                                        foreach ($det->parametros as $par)
                                                            if ($delta_acum_10_dias >= $par->desde && $delta_acum_10_dias <= $par->hasta)
                                                                $parametro = $par;
                                                    }
                                                }

                                                $det_proy_app->dosis = $parametro != '' ? $parametro->dosis : null;
                                                $det_proy_app->rendimiento = $parametro != '' ? $parametro->cantidad_mo : null;
                                                $det_proy_app->id_unidad_medida = $parametro != '' ? $parametro->id_unidad_medida : null;
                                                $det_proy_app->factor_conversion = $parametro != '' ? $parametro->factor_conversion : null;
                                                $det_proy_app->id_unidad_conversion = $parametro != '' ? $parametro->id_unidad_conversion : null;
                                                $det_proy_app->save();
                                            }
                                        }
                                    }
                                }
                                if ($model->num_sem == 1 && $app->semana_ini == 0 && $app->dia_ini >= 0) {    // es en la primera semana del ciclo
                                    $repeticion = 0;
                                    dump('**** mod: ' . $c->nombre . ' num_sem: ' . $model->num_sem . ' - hay que aplicar: ' . $app->nombre);

                                    for ($i = 1; $i <= $app->veces_x_semana; $i++) {
                                        $existe = ProyeccionCampoSemanalAplicacion::All()
                                            ->where('id_proyeccion_campo_semanal', $model->id_proyeccion_campo_semanal)
                                            ->where('app_nombre', $app->nombre)
                                            ->where('app_repeticion', ($repeticion + $i))
                                            ->where('app_litro_x_cama', $app->litro_x_cama)
                                            ->whereIn('estado', ['C', 'E', 'M'])
                                            ->first();
                                        if ($existe == '') {
                                            $proy_app = new ProyeccionCampoSemanalAplicacion();
                                            $proy_app->id_proyeccion_campo_semanal = $model->id_proyeccion_campo_semanal;
                                            $proy_app->app_nombre = $app->nombre;
                                            $proy_app->app_repeticion = $repeticion + $i;
                                            $proy_app->app_litro_x_cama = $app->litro_x_cama;
                                            $proy_app->app_continua = $app->continua;
                                            $proy_app->app_uso = $app->tipo;
                                            $proy_app->estado = 'P';
                                            $proy_app->camas = calcularCamas($c->area);
                                            $proy_app->save();
                                            $proy_app = ProyeccionCampoSemanalAplicacion::All()->last();

                                            /* aplicar detalles de labores */
                                            foreach ($app->detalles as $det) {
                                                $det_proy_app = new DetalleProyeccionCampoSemanalAplicacion();
                                                $det_proy_app->id_proyeccion_campo_semanal_aplicacion = $proy_app->id_proyeccion_campo_semanal_aplicacion;
                                                $det_proy_app->id_mano_obra = $det->id_mano_obra;
                                                $det_proy_app->id_producto = $det->id_producto;

                                                $parametro = '';
                                                $tipo_det = $det->getTipoParametros();
                                                dump('2*************************************************************' . $tipo_det);
                                                if ($tipo_det == -1) {  // tiene mas de un tipo de parametro
                                                    dump('tiene mas de un tipo de parametro');
                                                } elseif ($tipo_det == '') { // no tiene parametros
                                                    dump('no tiene parametros');
                                                } else {    // tiene parametros
                                                    if ($tipo_det == 'E') { // parametro estandar
                                                        dump('tiene parametros - parametro estandar');
                                                        $parametro = $det->parametros[0];
                                                        dump($parametro);
                                                    }
                                                    if ($tipo_det == 'D') { // parametro Delta Acum. 10 días
                                                        dump('tiene parametros - parametro Delta Acum. 10 días');
                                                        $last_fecha = DB::table('temperatura')
                                                            ->select(DB::raw('max(fecha) as fecha'))
                                                            ->where('estado', 1)
                                                            ->get()[0]->fecha;
                                                        $delta_acum_10_dias = DB::table('temperatura')
                                                            ->select(DB::raw('sum(maxima - minima) as cantidad'))
                                                            ->where('fecha', '>=', opDiasFecha('-', 9, $last_fecha))
                                                            ->where('fecha', '<=', $last_fecha)
                                                            ->get()[0]->cantidad;
                                                        $delta_acum_10_dias = round($delta_acum_10_dias);
                                                        foreach ($det->parametros as $par)
                                                            if ($delta_acum_10_dias >= $par->desde && $delta_acum_10_dias <= $par->hasta)
                                                                $parametro = $par;
                                                    }
                                                }

                                                $det_proy_app->dosis = $parametro != '' ? $parametro->dosis : null;
                                                $det_proy_app->rendimiento = $parametro != '' ? $parametro->cantidad_mo : null;
                                                $det_proy_app->id_unidad_medida = $parametro != '' ? $parametro->id_unidad_medida : null;
                                                $det_proy_app->factor_conversion = $parametro != '' ? $parametro->factor_conversion : null;
                                                $det_proy_app->id_unidad_conversion = $parametro != '' ? $parametro->id_unidad_conversion : null;
                                                $det_proy_app->save();
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        /* APLICAR en PROYECCIONES las aplicaciones */
        dump('/* APLICAR en PROYECCIONES las aplicaciones */');
        foreach ($proys as $pos_p => $proy) {
            $num_proc++;
            dump(porcentaje($num_proc, count($ciclos) + count($proys), 1) . '% - proy: ' . $pos_p . '/' . count($proys));
            foreach ($aplicaciones as $pos_a => $app) {
                if ($app->semana_ini < 0 || $app->dia_ini < 0) {  // es una aplicacion que comienza antes del ciclo
                    $existe_variedad = false;
                    foreach ($app->variedades as $var)
                        if ($var->id_variedad == $proy->id_variedad)
                            $existe_variedad = true;
                    $proy_poda_siembra = $proy->poda_siembra > 0 ? 'P' : 'S';
                    if ($existe_variedad && ($app->poda_siembra == $proy_poda_siembra || $app->poda_siembra == 'T')) {
                        // es la misma variedad del ciclo y la misma poda_siembra del ciclo
                        $fecha_app = opDiasFecha('-', $app->dia_ini * (-1), $proy->fecha_inicio);
                        $semana_app = getSemanaByDate($fecha_app);
                        if ($semana_app->codigo >= $semana_actual->codigo) {
                            dump('mod: ' . $proy->nombre . '; var: ' . $proy->id_variedad . '; ' . $app->nombre . '; dia_ini: ' . $app->dia_ini .
                                '; num_sem: ' . intval($app->dia_ini / 7) . '; ' . $fecha_app . '; ' . $semana_app->codigo);
                            $model = ProyeccionCampoSemanal::All()
                                ->where('id_modulo', $proy->id_modulo)
                                ->where('id_variedad', $proy->id_variedad)
                                ->where('semana', $semana_app->codigo)
                                //->where('poda_siembra', $c->poda_siembra)
                                ->first();
                            if ($model == '') {
                                $model = new ProyeccionCampoSemanal();
                                $model->id_modulo = $proy->id_modulo;
                                $model->id_variedad = $proy->id_variedad;
                                $model->semana = $semana_app->codigo;
                                $model->poda_siembra = $proy_poda_siembra;
                                //$model->num_sem = intval($app->dia_ini / 7);
                                $model->save();
                                $model = ProyeccionCampoSemanal::All()
                                    ->where('id_modulo', $proy->id_modulo)
                                    ->where('id_variedad', $proy->id_variedad)
                                    ->where('semana', $semana_app->codigo)
                                    ->where('poda_siembra', $proy_poda_siembra)
                                    ->first();
                            } else {
                                $model->poda_siembra = $proy_poda_siembra;
                                //$model->num_sem = intval($app->dia_ini / 7);
                                $model->save();
                            }

                            DetalleProyeccionCampoSemanalAplicacion::join('proyeccion_campo_semanal_aplicacion as p', 'p.id_proyeccion_campo_semanal_aplicacion', '=', 'detalle_proyeccion_campo_semanal_aplicacion.id_proyeccion_campo_semanal_aplicacion')
                                ->select('detalle_proyeccion_campo_semanal_aplicacion.*')
                                ->where('p.id_proyeccion_campo_semanal', $model->id_proyeccion_campo_semanal)
                                ->where('p.estado', 'X')
                                ->delete();
                            ProyeccionCampoSemanalAplicacion::where('id_proyeccion_campo_semanal', $model->id_proyeccion_campo_semanal)
                                ->where('estado', 'X')
                                ->delete();
                            if (count($app->parametros) > 0) { // buscar por parametros
                                dump('*********************** buscar por parametros: ' . $app->nombre);
                            } else {    // buscar en la misma tabla de aplicaciones
                                for ($i = 1; $i <= $app->veces_x_semana; $i++) {
                                    $existe = ProyeccionCampoSemanalAplicacion::All()
                                        ->where('id_proyeccion_campo_semanal', $model->id_proyeccion_campo_semanal)
                                        ->where('app_nombre', $app->nombre)
                                        ->where('app_repeticion', $i)
                                        ->where('app_litro_x_cama', $app->litro_x_cama)
                                        ->whereIn('estado', ['C', 'E', 'M'])
                                        ->first();
                                    if ($existe == '') {
                                        $proy_app = new ProyeccionCampoSemanalAplicacion();
                                        $proy_app->id_proyeccion_campo_semanal = $model->id_proyeccion_campo_semanal;
                                        $proy_app->app_nombre = $app->nombre;
                                        $proy_app->app_repeticion = $i;
                                        $proy_app->app_litro_x_cama = $app->litro_x_cama;
                                        $proy_app->app_continua = $app->continua;
                                        $proy_app->app_uso = $app->tipo;
                                        $proy_app->estado = 'X';
                                        $proy_app->camas = calcularCamas($proy->area);
                                        $proy_app->save();
                                        $proy_app = ProyeccionCampoSemanalAplicacion::All()->last();

                                        /* aplicar detalles de labores */
                                        foreach ($app->detalles as $det) {
                                            $det_proy_app = new DetalleProyeccionCampoSemanalAplicacion();
                                            $det_proy_app->id_proyeccion_campo_semanal_aplicacion = $proy_app->id_proyeccion_campo_semanal_aplicacion;
                                            $det_proy_app->id_mano_obra = $det->id_mano_obra;
                                            $det_proy_app->id_producto = $det->id_producto;

                                            $parametro = '';
                                            $tipo_det = $det->getTipoParametros();
                                            dump('3*************************************************************' . $tipo_det);
                                            if ($tipo_det == -1) {  // tiene mas de un tipo de parametro
                                                dump('tiene mas de un tipo de parametro');
                                            } elseif ($tipo_det == '') { // no tiene parametros
                                                dump('no tiene parametros');
                                            } else {    // tiene parametros
                                                if ($tipo_det == 'E') { // parametro estandar
                                                    dump('tiene parametros - parametro estandar');
                                                    $parametro = $det->parametros[0];
                                                    dump($parametro);
                                                }
                                                if ($tipo_det == 'D') { // parametro Delta Acum. 10 días
                                                    dump('tiene parametros - parametro Delta Acum. 10 días');
                                                    $last_fecha = DB::table('temperatura')
                                                        ->select(DB::raw('max(fecha) as fecha'))
                                                        ->where('estado', 1)
                                                        ->get()[0]->fecha;
                                                    $delta_acum_10_dias = DB::table('temperatura')
                                                        ->select(DB::raw('sum(maxima - minima) as cantidad'))
                                                        ->where('fecha', '>=', opDiasFecha('-', 9, $last_fecha))
                                                        ->where('fecha', '<=', $last_fecha)
                                                        ->get()[0]->cantidad;
                                                    $delta_acum_10_dias = round($delta_acum_10_dias);
                                                    foreach ($det->parametros as $par)
                                                        if ($delta_acum_10_dias >= $par->desde && $delta_acum_10_dias <= $par->hasta)
                                                            $parametro = $par;
                                                }
                                            }

                                            $det_proy_app->dosis = $parametro != '' ? $parametro->dosis : null;
                                            $det_proy_app->rendimiento = $parametro != '' ? $parametro->cantidad_mo : null;
                                            $det_proy_app->id_unidad_medida = $parametro != '' ? $parametro->id_unidad_medida : null;
                                            $det_proy_app->factor_conversion = $parametro != '' ? $parametro->factor_conversion : null;
                                            $det_proy_app->id_unidad_conversion = $parametro != '' ? $parametro->id_unidad_conversion : null;
                                            $det_proy_app->save();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "proyeccion:campo_semanal" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "proyeccion:campo_semanal" <<<<< * >>>>>');
    }
}