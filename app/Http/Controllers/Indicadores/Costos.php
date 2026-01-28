<?php
/**
 * Created by PhpStorm.
 * User: Rafael Prats
 * Date: 2020-01-09
 * Time: 12:19
 */

namespace yura\Http\Controllers\Indicadores;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Cosecha;
use yura\Modelos\Area;
use yura\Modelos\IndicadorVariedad;
use yura\Modelos\ResumenCostosSemanal;
use yura\Modelos\Variedad;

class Costos
{
    public static function mano_de_obra_1_semana_atras()
    {
        $model = getIndicadorByName('C1');  // Costos Mano de Obra (-1 semana)
        if ($model != '') {
            $last_semana = DB::table('costos_semana_mano_obra')
                ->select(DB::raw('max(codigo_semana) as last_semana'))
                ->where('valor', '>', 0)
                ->get()[0]->last_semana;
            if ($last_semana != '') {
                $valor = DB::table('costos_semana_mano_obra')
                    ->select(DB::raw('sum(valor) as cant'))
                    ->where('codigo_semana', $last_semana)
                    ->get()[0]->cant;
                $model->valor = $last_semana . ':' . round($valor, 2);
            }
            $model->save();
        }
    }

    public static function costos_x_planta_4_semanas_atras()
    {
        $model = getIndicadorByName('C12');  // Costo x Planta (-4 semanas)
        if ($model != '') {
            $last_semana = $last_semana = DB::table('costos_semana_mano_obra')
                ->select(DB::raw('max(codigo_semana) as last_semana'))
                ->where('valor', '>', 0)
                ->get()[0]->last_semana;
            if ($last_semana != '') {
                if ($last_semana < 2138) {
                    $semana_desde = getObjSemana($last_semana);
                    $sem_desde = getSemanaByDate(opDiasFecha('-', 21, $semana_desde->fecha_inicial));   // 4 semana atras
                    $sem_hasta = $semana_desde;

                    $valor = DB::table('resumen_propagacion')
                        ->select(DB::raw('sum(costo_x_planta) as cantidad'), DB::raw('count(*) as positivos'))
                        ->where('semana', '>=', $sem_desde->codigo)
                        ->where('semana', '<=', $sem_hasta->codigo)
                        ->where('costo_x_planta', '>', 0)
                        ->get()[0];
                    $valor = $valor->positivos > 0 ? ($valor->cantidad / $valor->positivos) : 0;
                } else
                    $valor = 0.052;
                $model->valor = $valor;
            }
            $model->save();
        }
    }

    public static function costos_insumos_1_semana_atras()
    {
        $model = getIndicadorByName('C2');  // Costos Insumos (-1 semana)
        if ($model != '') {
            $last_semana = DB::table('costos_semana')
                ->select(DB::raw('max(codigo_semana) as last_semana'))
                ->where('valor', '>', 0)
                ->get()[0]->last_semana;
            if ($last_semana != '') {
                $valor = DB::table('costos_semana')
                    ->select(DB::raw('sum(valor) as cant'))
                    ->where('codigo_semana', $last_semana)
                    ->get()[0]->cant;
                $model->valor = $last_semana . ':' . round($valor, 2);
            }
            $model->save();
        }
    }

    public static function costos_propagacion_1_semana_atras()
    {
        $model = getIndicadorByName('C13');  // Costos Propagacion (-1 semana)
        if ($model != '') {
            $last_semana = DB::table('costos_semana')
                ->select(DB::raw('max(codigo_semana) as last_semana'))
                ->where('valor', '>', 0)
                ->get()[0]->last_semana;
            if ($last_semana != '') {
                $requerimientos = DB::table('resumen_propagacion as r')
                    ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                    ->select(DB::raw('sum(r.requerimientos) as requerimientos'))
                    ->where('v.estado', 1)
                    ->where('r.semana', $last_semana)
                    ->get()[0]->requerimientos;
                $valor = $requerimientos * 0.052;
                $model->valor = $last_semana . ':' . round($valor, 2);
                $model->save();

                /* ============================== INDICADOR x VARIEDAD ================================= */
                $variedades = Variedad::where('estado', 1)->get();
                foreach ($variedades as $pos_var => $var) {
                    dump('var: ' . ($pos_var + 1) . '/' . count($variedades));
                    $ind = IndicadorVariedad::All()
                        ->where('id_indicador', $model->id_indicador)
                        ->where('id_variedad', $var->id_variedad)
                        ->first();
                    if ($ind == '') {   // es nuevo
                        $ind = new IndicadorVariedad();
                        $iv = IndicadorVariedad::orderBy('id_indicador_variedad','desc')->first();
                        $ind->id_indicador_variedad = isset($iv->id_indicador_variedad) ? $iv->id_indicador_variedad + 1 : 1;
                        $ind->id_indicador = $model->id_indicador;
                        $ind->id_variedad = $var->id_variedad;
                    }

                    $requerimientos = DB::table('resumen_propagacion as r')
                        ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                        ->select(DB::raw('sum(r.requerimientos) as requerimientos'))
                        ->where('v.estado', 1)
                        ->where('r.id_variedad', $var->id_variedad)
                        ->where('r.semana', $last_semana)
                        ->get()[0]->requerimientos;
                    $valor = $requerimientos * 0.052;
                    $ind->valor = $last_semana . ':' . round($valor, 2);
                    $ind->save();
                }
            }
        }
    }

    public static function costos_fijos_1_semana_atras()
    {
        $model = getIndicadorByName('C7');  // Costos Insumos (-1 semana)
        if ($model != '') {
            $last_semana = $last_semana = DB::table('costos_semana')
                ->select(DB::raw('max(codigo_semana) as last_semana'))
                ->where('valor', '>', 0)
                ->get()[0]->last_semana;
            if ($last_semana != '') {
                $otros_gastos = DB::table('otros_gastos')
                    ->select(DB::raw('sum(gip) as cant_gip'), DB::raw('sum(ga) as cant_ga'))
                    ->where('codigo_semana', $last_semana)
                    ->get()[0];

                $valor = $otros_gastos->cant_gip + $otros_gastos->cant_ga;

                $model->valor = $last_semana . ':' . round($valor, 2);
                $model->save();
            }
        }
    }

    public static function costos_regalias_1_semana_atras()
    {
        $model = getIndicadorByName('C8');  // Costos Insumos (-1 semana)
        if ($model != '') {
            $last_semana = $last_semana = DB::table('costos_semana')
                ->select(DB::raw('max(codigo_semana) as last_semana'))
                ->where('valor', '>', 0)
                ->get()[0]->last_semana;

            if ($last_semana != '') {
                $resumen = ResumenCostosSemanal::All()
                    ->where('codigo_semana', $last_semana)
                    ->first();

                $model->valor = $last_semana . ':' . round($resumen->regalias, 2);
                $model->save();
            }
        }
    }

    public static function costos_campo_ha_4_semana_atras()
    {
        $model = getIndicadorByName('C3');  // Costos Campo/ha/semana (-4 semanas)
        if ($model != '') {
            $last_semana = $last_semana = DB::table('costos_semana')
                ->select(DB::raw('max(codigo_semana) as last_semana'))
                ->where('valor', '>', 0)
                ->get()[0]->last_semana;
            $last_semana = getObjSemana($last_semana);
            if ($last_semana != '') {
                $sem_desde = getSemanaByDate(opDiasFecha('-', 21, $last_semana->fecha_inicial));
                $sem_hasta = $last_semana;

                $areas = DB::table('resumen_area_semanal')
                    ->select(DB::raw('sum(area) as area'))
                    ->where('codigo_semana', '>=', $sem_desde->codigo)
                    ->where('codigo_semana', '<=', $sem_hasta->codigo)
                    ->get()[0]->area;
                $resumen_semanal_total = DB::table('resumen_semanal_total')
                    ->select(DB::raw('sum(campo) as campo'))
                    ->where('codigo_semana', '>=', $sem_desde->codigo)
                    ->where('codigo_semana', '<=', $sem_hasta->codigo)
                    ->get()[0];

                $campo_ha_semana = $areas > 0 ? ($resumen_semanal_total->campo / $areas) * 10000 : 0;
                $model->valor = $campo_ha_semana . '|' . $resumen_semanal_total->campo . '/' . ($areas / 10000);
                $model->save();
            }
        }
    }

    public static function costos_cosecha_tallo_4_semana_atras()
    {
        $model = getIndicadorByName('C4');  // Costos Cosecha x Tallo (-4 semanas)
        if ($model != '') {
            $last_semana = $last_semana = DB::table('costos_semana')
                ->select(DB::raw('max(codigo_semana) as last_semana'))
                ->where('valor', '>', 0)
                ->get()[0]->last_semana;
            $last_semana = getObjSemana($last_semana);
            if ($last_semana != '') {
                $sem_desde = getSemanaByDate(opDiasFecha('-', 21, $last_semana->fecha_inicial));
                $sem_hasta = $last_semana;

                $area_trabajo = Area::All()
                    ->where('estado', 1)
                    ->where('nombre', 'COSECHA')
                    ->first();
                $insumos = DB::table('costos_semana as c')
                    ->select(DB::raw('sum(c.valor) as cant'))
                    ->join('actividad_producto as ac', 'ac.id_actividad_producto', '=', 'c.id_actividad_producto')
                    ->join('actividad as a', 'a.id_actividad', '=', 'ac.id_actividad')
                    ->where('a.id_area', '=', $area_trabajo->id_area)
                    ->where('c.codigo_semana', '>=', $sem_desde->codigo)
                    ->where('c.codigo_semana', '<=', $sem_hasta->codigo)
                    ->get()[0]->cant;
                $mano_obra = DB::table('costos_semana_mano_obra as c')
                    ->select(DB::raw('sum(c.valor) as cant'))
                    ->join('actividad_mano_obra as am', 'am.id_actividad_mano_obra', '=', 'c.id_actividad_mano_obra')
                    ->join('actividad as a', 'a.id_actividad', '=', 'am.id_actividad')
                    ->where('a.id_area', '=', $area_trabajo->id_area)
                    ->where('c.codigo_semana', '>=', $sem_desde->codigo)
                    ->where('c.codigo_semana', '<=', $sem_hasta->codigo)
                    ->get()[0]->cant;
                $otros = DB::table('otros_gastos as o')
                    ->select(DB::raw('sum(o.gip + o.ga) as cant'))
                    ->where('o.id_area', '=', $area_trabajo->id_area)
                    ->where('o.codigo_semana', '>=', $sem_desde->codigo)
                    ->where('o.codigo_semana', '<=', $sem_hasta->codigo)
                    ->get()[0]->cant;

                $costos_total = $insumos + $mano_obra + $otros;

                $cosechas = Cosecha::All()->where('estado', 1)
                    ->where('fecha_ingreso', '>=', $sem_desde->fecha_inicial)
                    ->where('fecha_ingreso', '<=', $sem_hasta->fecha_final);
                $tallos = 0;
                foreach ($cosechas as $c) {
                    $tallos += $c->getTotalTallos();
                }
                //dump('costos totales = ' . $costos_total . '; insumos ' . $insumos . ' + mano_obra ' . $mano_obra . ' + otros ' . $otros . ' / tallos ' . $tallos);

                $model->valor = $tallos > 0 ? round(($costos_total / $tallos) * 100, 2) : 0;
                $model->save();
            }
        }
    }

    public static function costos_postcosecha_tallo_4_semana_atras()
    {
        $model = getIndicadorByName('C5');  // Costos Postcosecha x Tallo (-4 semanas)
        if ($model != '') {
            $last_semana = $last_semana = DB::table('costos_semana')
                ->select(DB::raw('max(codigo_semana) as last_semana'))
                ->where('valor', '>', 0)
                ->get()[0]->last_semana;
            $last_semana = getObjSemana($last_semana);
            if ($last_semana != '') {
                $sem_desde = getSemanaByDate(opDiasFecha('-', 21, $last_semana->fecha_inicial));
                $sem_hasta = $last_semana;

                $area_trabajo = Area::All()
                    ->where('estado', 1)
                    ->where('nombre', 'POSTCOSECHA')
                    ->first();
                $insumos = DB::table('costos_semana as c')
                    ->select(DB::raw('sum(c.valor) as cant'))
                    ->join('actividad_producto as ac', 'ac.id_actividad_producto', '=', 'c.id_actividad_producto')
                    ->join('actividad as a', 'a.id_actividad', '=', 'ac.id_actividad')
                    ->where('a.id_area', '=', $area_trabajo->id_area)
                    ->where('c.codigo_semana', '>=', $sem_desde->codigo)
                    ->where('c.codigo_semana', '<=', $sem_hasta->codigo)
                    ->get()[0]->cant;
                $mano_obra = DB::table('costos_semana_mano_obra as c')
                    ->select(DB::raw('sum(c.valor) as cant'))
                    ->join('actividad_mano_obra as am', 'am.id_actividad_mano_obra', '=', 'c.id_actividad_mano_obra')
                    ->join('actividad as a', 'a.id_actividad', '=', 'am.id_actividad')
                    ->where('a.id_area', '=', $area_trabajo->id_area)
                    ->where('c.codigo_semana', '>=', $sem_desde->codigo)
                    ->where('c.codigo_semana', '<=', $sem_hasta->codigo)
                    ->get()[0]->cant;
                $otros = DB::table('otros_gastos as o')
                    ->select(DB::raw('sum(o.gip + o.ga) as cant'))
                    ->where('o.id_area', '=', $area_trabajo->id_area)
                    ->where('o.codigo_semana', '>=', $sem_desde->codigo)
                    ->where('o.codigo_semana', '<=', $sem_hasta->codigo)
                    ->get()[0]->cant;

                $costos_total = $insumos + $mano_obra + $otros;

                $cosechas = Cosecha::All()->where('estado', 1)
                    ->where('fecha_ingreso', '>=', $sem_desde->fecha_inicial)
                    ->where('fecha_ingreso', '<=', $sem_hasta->fecha_final);
                $tallos = 0;
                foreach ($cosechas as $c) {
                    $tallos += $c->getTotalTallos();
                }

                dump('costos totales = ' . $costos_total . '; insumos ' . $insumos . ' + mano_obra ' . $mano_obra . ' + otros ' . $otros . ' / tallos ' . $tallos);
                $model->valor = $tallos > 0 ? round(($costos_total / $tallos) * 100, 2) : 0;
                $model->save();
            }
        }
    }

    public static function costos_total_tallo_4_semana_atras()
    {
        $model = getIndicadorByName('C6');  // Costos Total x Tallo (-4 semanas)
        if ($model != '') {
            $last_semana = $last_semana = DB::table('costos_semana')
                ->select(DB::raw('max(codigo_semana) as last_semana'))
                ->where('valor', '>', 0)
                ->get()[0]->last_semana;
            $last_semana = getObjSemana($last_semana);
            if ($last_semana != '') {
                $sem_desde = getSemanaByDate(opDiasFecha('-', 21, $last_semana->fecha_inicial));
                $sem_hasta = $last_semana;

                $resumen_semanal_total = DB::table('resumen_semanal_total')
                    ->select(DB::raw('sum(campo) as campo'),
                        DB::raw('sum(cosecha) as cosecha'),
                        DB::raw('sum(postcosecha) as postcosecha'),
                        DB::raw('sum(propagacion) as propagacion'),
                        DB::raw('sum(servicios_generales) as servicios_generales'),
                        DB::raw('sum(administrativos) as administrativos'),
                        DB::raw('sum(regalias) as regalias'))
                    ->where('codigo_semana', '>=', $sem_desde->codigo)
                    ->where('codigo_semana', '<=', $sem_hasta->codigo)
                    ->get()[0];
                $resumen_cosecha = DB::table('resumen_semana_cosecha')
                    ->select(DB::raw('sum(tallos_clasificados) as tallos_clasificados'))
                    ->where('codigo_semana', '>=', $sem_desde->codigo)
                    ->where('codigo_semana', '<=', $sem_hasta->codigo)
                    ->get()[0];

                dump($sem_desde->codigo, $sem_hasta->codigo, $resumen_semanal_total, $resumen_cosecha);
                $costo_total_x_tallo = $resumen_cosecha->tallos_clasificados > 0 ? (($resumen_semanal_total->propagacion + $resumen_semanal_total->campo + $resumen_semanal_total->cosecha + $resumen_semanal_total->postcosecha + $resumen_semanal_total->servicios_generales + $resumen_semanal_total->administrativos + $resumen_semanal_total->regalias) / $resumen_cosecha->tallos_clasificados) : 0;
                $model->valor = $costo_total_x_tallo * 100;
                $model->save();
            }
        }
    }

    public static function costos_m2_16_semanas_atras()
    {
        $model = getIndicadorByName('C9');  // Costos/m2 (-16 semanas)
        if ($model != '') {
            $last_semana = DB::table('costos_semana')
                ->select(DB::raw('max(codigo_semana) as semana'))
                ->where('valor', '>', 0)
                ->get()[0];
            $last_semana = getObjSemana($last_semana->semana);

            $sem_desde = getSemanaByDate(opDiasFecha('-', 84, $last_semana->fecha_inicial));   // 13 semana atras
            $sem_hasta = $last_semana;

            $costos = DB::table('resumen_costos_semanal')
                ->select(DB::raw('sum(mano_obra + insumos + fijos + regalias) as cant'))
                ->where('codigo_semana', '>=', $sem_desde->codigo)
                ->where('codigo_semana', '<=', $sem_hasta->codigo)
                ->get()[0]->cant;

            $requerimientos = DB::table('resumen_propagacion as r')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->select(DB::raw('sum(r.requerimientos) as requerimientos'), 'r.semana')
                ->where('v.estado', 1)
                ->where('r.semana', '>=', $sem_desde->codigo)
                ->where('r.semana', '<=', $sem_hasta->codigo)
                ->groupBy('r.semana')
                ->orderBy('r.semana')
                ->get();
            foreach ($requerimientos as $r)
                if ($r->semana >= 2138)
                    $costos += $r->requerimientos * 0.052;

            $area = DB::table('resumen_area_semanal')
                ->select(DB::raw('sum(area) as cant'))
                ->where('codigo_semana', '>=', $sem_desde->codigo)
                ->where('codigo_semana', '<=', $sem_hasta->codigo)
                ->get()[0]->cant;

            dump($last_semana->codigo, $sem_desde->codigo, $sem_hasta->codigo, $costos . '/' . $area);
            $valor = $area > 0 ? round(($costos / ($area / 13)) * 4, 2) : 0;
            $model->valor = $valor;
            $model->save();
        }
    }

    public static function costos_m2_4_semanas_atras()
    {
        $model = getIndicadorByName('C11');  // Costos/m2 (-4 semanas)
        if ($model != '') {
            $last_semana = DB::table('costos_semana')
                ->select(DB::raw('max(codigo_semana) as semana'))
                ->where('valor', '>', 0)
                ->get()[0];
            $last_semana = getObjSemana($last_semana->semana);

            $sem_desde = getSemanaByDate(opDiasFecha('-', 21, $last_semana->fecha_inicial));   // 4 semana atras
            $sem_hasta = $last_semana;

            $costos = DB::table('resumen_costos_semanal')
                ->select(DB::raw('sum(mano_obra + insumos + fijos + regalias) as cant'))
                ->where('codigo_semana', '>=', $sem_desde->codigo)
                ->where('codigo_semana', '<=', $sem_hasta->codigo)
                ->get()[0]->cant;

            $requerimientos = DB::table('resumen_propagacion as r')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->select(DB::raw('sum(r.requerimientos) as requerimientos'), 'r.semana')
                ->where('v.estado', 1)
                ->where('r.semana', '>=', $sem_desde->codigo)
                ->where('r.semana', '<=', $sem_hasta->codigo)
                ->groupBy('r.semana')
                ->orderBy('r.semana')
                ->get();
            foreach ($requerimientos as $r)
                if ($r->semana >= 2138)
                    $costos += $r->requerimientos * 0.052;

            $area = DB::table('resumen_area_semanal')
                ->select(DB::raw('sum(area) as area'))
                ->where('codigo_semana', '>=', $sem_desde->codigo)
                ->where('codigo_semana', '<=', $sem_hasta->codigo)
                ->get()[0]->area;

            dump($last_semana->codigo, $sem_desde->codigo, $sem_hasta->codigo, $costos . '/' . $area);
            $valor = $area > 0 ? round(($costos / ($area / 4)) * 13, 2) : 0;
            $model->valor = $valor;
            $model->save();
        }
    }

    public static function costos_m2_52_semanas_atras()
    {
        $model = getIndicadorByName('C10');  // Costos/m2 (-52 semanas)
        if ($model != '') {
            $last_semana = DB::table('costos_semana')
                ->select(DB::raw('max(codigo_semana) as semana'))
                ->where('valor', '>', 0)
                ->get()[0];
            $last_semana = getObjSemana($last_semana->semana);

            $sem_desde = getSemanaByDate(opDiasFecha('-', 364, $last_semana->fecha_inicial));   // 52 semana atras
            $sem_hasta = $last_semana;

            $costos = DB::table('resumen_costos_semanal')
                ->select(DB::raw('sum(mano_obra + insumos + fijos + regalias) as cant'))
                ->where('codigo_semana', '>=', $sem_desde->codigo)
                ->where('codigo_semana', '<=', $sem_hasta->codigo)
                ->get()[0]->cant;

            $requerimientos = DB::table('resumen_propagacion as r')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->select(DB::raw('sum(r.requerimientos) as requerimientos'), 'r.semana')
                ->where('v.estado', 1)
                ->where('r.semana', '>=', $sem_desde->codigo)
                ->where('r.semana', '<=', $sem_hasta->codigo)
                ->groupBy('r.semana')
                ->orderBy('r.semana')
                ->get();
            foreach ($requerimientos as $r)
                if ($r->semana >= 2138)
                    $costos += $r->requerimientos * 0.052;

            $area = DB::table('resumen_area_semanal')
                ->select(DB::raw('sum(area) as cant'))
                ->where('codigo_semana', '>=', $sem_desde->codigo)
                ->where('codigo_semana', '<=', $sem_hasta->codigo)
                ->get()[0]->cant;

            //dd($last_semana->codigo, $sem_desde->codigo, $sem_hasta->codigo, $costos . '/' . $area);
            $valor = $area > 0 ? round($costos / ($area / 52), 2) : 0;
            $model->valor = $valor;
            $model->save();
        }
    }

    public static function rentabilidad_4_meses()
    {
        $model = getIndicadorByName('R1');  // Rentabilidad (-4 meses)
        if ($model != '') {
            $valor = getIndicadorByName('D9')->valor - getIndicadorByName('C9')->valor;
            $model->valor = $valor;
            $model->save();

            /* -------------------------- X VARIEDAD ------------------------- */
            foreach (Variedad::All() as $var) {
                $ind = IndicadorVariedad::All()
                    ->where('id_indicador', $model->id_indicador)
                    ->where('id_variedad', $var->id_variedad)
                    ->first();
                if ($ind == '') {   // es nuevo
                    $ind = new IndicadorVariedad();
                    $iv = IndicadorVariedad::orderBy('id_indicador_variedad','desc')->first();
                    $ind->id_indicador_variedad = isset($iv->id_indicador_variedad) ? $iv->id_indicador_variedad + 1 : 1;
                    $ind->id_indicador = $model->id_indicador;
                    $ind->id_variedad = $var->id_variedad;
                }

                $valor = getIndicadorByName('D9')->getVariedad($var->id_variedad)->valor - getIndicadorByName('C9')->valor;
                $ind->valor = $valor;
                $ind->save();
            }
        }
    }

    public static function rentabilidad_1_mes()
    {
        $model = getIndicadorByName('R3');  // Rentabilidad (-1 mes)
        if ($model != '') {
            $valor = getIndicadorByName('D15')->valor - getIndicadorByName('C11')->valor;
            $model->valor = $valor;
            $model->save();

            /* -------------------------- X VARIEDAD ------------------------- */
            foreach (Variedad::All() as $var) {
                $ind = IndicadorVariedad::All()
                    ->where('id_indicador', $model->id_indicador)
                    ->where('id_variedad', $var->id_variedad)
                    ->first();
                if ($ind == '') {   // es nuevo
                    $ind = new IndicadorVariedad();
                    $iv = IndicadorVariedad::orderBy('id_indicador_variedad','desc')->first();
                    $ind->id_indicador_variedad = isset($iv->id_indicador_variedad) ? $iv->id_indicador_variedad + 1 : 1;
                    $ind->id_indicador = $model->id_indicador;
                    $ind->id_variedad = $var->id_variedad;
                }

                $valor = getIndicadorByName('D15')->getVariedad($var->id_variedad)->valor - getIndicadorByName('C11')->valor;
                $ind->valor = $valor;
                $ind->save();
            }
        }
    }

    public static function rentabilidad_1_anno()
    {
        $model = getIndicadorByName('R2');  // Rentabilidad (-1 año)
        if ($model != '') {
            $valor = getIndicadorByName('D10')->valor - getIndicadorByName('C10')->valor;
            $model->valor = $valor;
            $model->save();
        }
    }
}
