<?php

namespace yura\Http\Controllers\Indicadores;

use Illuminate\Support\Facades\DB;
use yura\Modelos\Indicador;
use yura\Modelos\Pedido;
use yura\Modelos\Variedad;
use yura\Modelos\IndicadorVariedad;

class Venta
{
    public static function ventas_7_dias_atras()
    {
        $model = getIndicadorByName('D4');  // Dinero ingresado (-7 días)
        if ($model != '') {
            $semana_anterior = getSemanaByDate(opDiasFecha('-', 7, hoy()));
            $valor = DB::table('detalle_clasificacion_verde as d')
                ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
                ->join('clasificacion_unitaria as u', 'u.id_clasificacion_unitaria', '=', 'd.id_clasificacion_unitaria')
                ->select(DB::raw('sum((cantidad_ramos * tallos_x_ramos) * u.precio_venta) as tallos'),
                    DB::raw('sum((descartes) * u.precio_venta) as descartes'))
                ->where('d.estado', 1)
                ->where('v.estado', 1)
                ->where('v.fecha_ingreso', '>=', $semana_anterior->fecha_inicial)
                ->where('v.fecha_ingreso', '<=', $semana_anterior->fecha_final)
                ->get()[0];
            dump($semana_anterior->codigo, $valor);
            $model->valor = round($valor->tallos - $valor->descartes, 2);
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

                $valor = DB::table('detalle_clasificacion_verde as d')
                    ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
                    ->join('clasificacion_unitaria as u', 'u.id_clasificacion_unitaria', '=', 'd.id_clasificacion_unitaria')
                    ->select(DB::raw('sum((cantidad_ramos * tallos_x_ramos) * u.precio_venta) as tallos'),
                        DB::raw('sum((descartes) * u.precio_venta) as descartes'))
                    ->where('d.estado', 1)
                    ->where('v.estado', 1)
                    ->where('d.id_variedad', $var->id_variedad)
                    ->where('v.fecha_ingreso', '>=', $semana_anterior->fecha_inicial)
                    ->where('v.fecha_ingreso', '<=', $semana_anterior->fecha_final)
                    ->get()[0];

                $ind->valor = round($valor->tallos - $valor->descartes, 2);
                $ind->save();
            }
        }
    }

    public static function dinero_m2_anno_4_meses_atras()
    {
        $model = getIndicadorByName('D9');  // Venta $/m2/año (-4 meses)
        if ($model != '') {
            $desde_sem = getSemanaByDate(opDiasFecha('-', 91, date('Y-m-d')));
            $hasta_sem = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));

            $venta_mensual = DB::table('resumen_semanal_total')
                ->select(DB::raw('sum(valor) as cant'))
                //->where('estado', 1)
                ->where('codigo_semana', '>=', $desde_sem->codigo)
                ->where('codigo_semana', '<=', $hasta_sem->codigo)
                ->get()[0]->cant;

            $areas = DB::table('resumen_area_semanal')
                ->select(DB::raw('sum(area) as area'))
                ->where('codigo_semana', '>=', $desde_sem->codigo)
                ->where('codigo_semana', '<=', $hasta_sem->codigo)
                ->get()[0]->area;

            $model->valor = $areas > 0 ? round(($venta_mensual / ($areas / 4)) * 13, 2) : 0;
            $model->save();
            dump($desde_sem->codigo, $hasta_sem->codigo, $venta_mensual, $areas, $model->valor);

            /* ============================== INDICADOR x VARIEDAD ================================= */
            $semanas = DB::table('semana')
                ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
                ->where('codigo', '>=', $desde_sem->codigo)
                ->where('codigo', '<=', $hasta_sem->codigo)
                ->get();
            $variedades = Variedad::where('estado', 1)->get();
            foreach ($variedades as $pos_var => $var) {
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
                $venta_mensual = 0;
                foreach ($semanas as $sem) {
                    dump($sem->codigo);
                    $valor = 0;
                    if ($sem->codigo < 2142) {   // calcular las ventas a traves de los pedidos
                        $pedidos = Pedido::where('estado', 1)
                            ->where('fecha_pedido', '>=', $sem->fecha_inicial)
                            ->where('fecha_pedido', '<=', $sem->fecha_final)
                            ->get();
                        foreach ($pedidos as $p) {
                            if (!getFacturaAnulada($p->id_pedido)) {
                                $precio = $p->getPrecioByPedidoVariedad($var->id_variedad);
                                $valor += $precio;
                            }
                        }
                    } else {    // calcular las ventas a traves de clasificacion verde
                        $c_unitarias = DB::table('detalle_clasificacion_verde as d')
                            ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
                            ->join('clasificacion_unitaria as u', 'u.id_clasificacion_unitaria', '=', 'd.id_clasificacion_unitaria')
                            ->select('d.id_clasificacion_unitaria', 'u.precio_venta')->distinct()
                            ->where('d.estado', 1)
                            //->where('v.estado', 1)
                            ->where('d.id_variedad', $var->id_variedad)
                            ->where('v.fecha_ingreso', '>=', $sem->fecha_inicial)
                            ->where('v.fecha_ingreso', '<=', $sem->fecha_final)
                            ->get();
                        foreach ($c_unitarias as $u) {
                            $tallos = DB::table('detalle_clasificacion_verde as d')
                                ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
                                ->select(DB::raw('sum(cantidad_ramos * tallos_x_ramos) as tallos'),
                                    DB::raw('sum(descartes) as descartes'))
                                ->where('d.estado', 1)
                                ->where('d.id_variedad', $var->id_variedad)
                                ->where('d.id_clasificacion_unitaria', $u->id_clasificacion_unitaria)
                                ->where('v.fecha_ingreso', '>=', $sem->fecha_inicial)
                                ->where('v.fecha_ingreso', '<=', $sem->fecha_final)
                                ->get()[0];
                            $valor += ($tallos->tallos * $u->precio_venta) - ($tallos->descartes * $u->precio_venta);
                        }
                    }
                    $venta_mensual += $valor;
                }

                $areas = DB::table('resumen_area_semanal')
                    ->select(DB::raw('sum(area) as area'))
                    ->where('id_variedad', $var->id_variedad)
                    ->where('codigo_semana', '>=', $desde_sem->codigo)
                    ->where('codigo_semana', '<=', $hasta_sem->codigo)
                    ->get()[0]->area;
                $ind->valor = $areas > 0 ? round(($venta_mensual / ($areas / 4)) * 13, 2) : 0;
                $ind->save();
                dump('var: ' . ($pos_var + 1) . '/' . count($variedades) . ' ; valor = ' . $ind->valor);
            }
        }
    }

    public static function dinero_m2_anno_1_mes_atras()
    {
        $model = getIndicadorByName('D15');  // Venta $/m2/año (-1 mes)
        if ($model != '') {
            $desde_sem = getSemanaByDate(opDiasFecha('-', 28, date('Y-m-d')));
            $hasta_sem = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));

            $venta_mensual = DB::table('resumen_semanal_total')
                ->select(DB::raw('sum(valor) as cant'))
                //->where('estado', 1)
                ->where('codigo_semana', '>=', $desde_sem->codigo)
                ->where('codigo_semana', '<=', $hasta_sem->codigo)
                ->get()[0]->cant;

            $areas = DB::table('resumen_area_semanal')
                ->select(DB::raw('sum(area) as area'))
                ->where('codigo_semana', '>=', $desde_sem->codigo)
                ->where('codigo_semana', '<=', $hasta_sem->codigo)
                ->get()[0]->area;

            $model->valor = $areas > 0 ? round(($venta_mensual / ($areas / 4)) * 13, 2) : 0;
            $model->save();
            dump($desde_sem->codigo, $hasta_sem->codigo, $venta_mensual, $areas, $model->valor);

            /* ============================== INDICADOR x VARIEDAD ================================= */
            $semanas = DB::table('semana')
                ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
                ->where('codigo', '>=', $desde_sem->codigo)
                ->where('codigo', '<=', $hasta_sem->codigo)
                ->get();
            $variedades = Variedad::where('estado', 1)->get();
            foreach ($variedades as $pos_var => $var) {
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
                $venta_mensual = 0;
                foreach ($semanas as $sem) {
                    $valor = 0;
                    if ($sem->codigo < 2142) {   // calcular las ventas a traves de los pedidos
                        $pedidos = Pedido::where('estado', 1)
                            ->where('fecha_pedido', '>=', $sem->fecha_inicial)
                            ->where('fecha_pedido', '<=', $sem->fecha_final)
                            ->get();
                        foreach ($pedidos as $p) {
                            if (!getFacturaAnulada($p->id_pedido)) {
                                $precio = $p->getPrecioByPedidoVariedad($var->id_variedad);
                                $valor += $precio;
                            }
                        }
                    } else {    // calcular las ventas a traves de clasificacion verde
                        $c_unitarias = DB::table('detalle_clasificacion_verde as d')
                            ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
                            ->join('clasificacion_unitaria as u', 'u.id_clasificacion_unitaria', '=', 'd.id_clasificacion_unitaria')
                            ->select('d.id_clasificacion_unitaria', 'u.precio_venta')->distinct()
                            ->where('d.estado', 1)
                            //->where('v.estado', 1)
                            ->where('d.id_variedad', $var->id_variedad)
                            ->where('v.fecha_ingreso', '>=', $sem->fecha_inicial)
                            ->where('v.fecha_ingreso', '<=', $sem->fecha_final)
                            ->get();
                        foreach ($c_unitarias as $u) {
                            $tallos = DB::table('detalle_clasificacion_verde as d')
                                ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
                                ->select(DB::raw('sum(cantidad_ramos * tallos_x_ramos) as tallos'))
                                ->where('d.estado', 1)
                                ->where('d.id_variedad', $var->id_variedad)
                                ->where('d.id_clasificacion_unitaria', $u->id_clasificacion_unitaria)
                                ->where('v.fecha_ingreso', '>=', $sem->fecha_inicial)
                                ->where('v.fecha_ingreso', '<=', $sem->fecha_final)
                                ->get()[0]->tallos;
                            $valor += $tallos * $u->precio_venta;
                        }
                    }
                    $venta_mensual += $valor;
                }

                $areas = DB::table('resumen_area_semanal')
                    ->select(DB::raw('sum(area) as area'))
                    ->where('id_variedad', $var->id_variedad)
                    ->where('codigo_semana', '>=', $desde_sem->codigo)
                    ->where('codigo_semana', '<=', $hasta_sem->codigo)
                    ->get()[0]->area;
                $ind->valor = $areas > 0 ? round(($venta_mensual / ($areas / 4)) * 13, 2) : 0;
                $ind->save();
                dump('var: ' . ($pos_var + 1) . '/' . count($variedades) . ' ; valor = ' . $ind->valor);
            }
        }
    }

    public static function dinero_m2_anno_1_anno_atras()
    {
        $model = getIndicadorByName('D10');  // Venta $/m2/año (-1 año)
        if ($model != '') {
            $desde_sem = getSemanaByDate(opDiasFecha('-', 364, date('Y-m-d')));   // 52 semanas atras
            $hasta_sem = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));

            $venta_mensual = DB::table('resumen_semanal_total')
                ->select(DB::raw('sum(valor) as cant'))
                //->where('estado', 1)
                ->where('codigo_semana', '>=', $desde_sem->codigo)
                ->where('codigo_semana', '<=', $hasta_sem->codigo)
                ->get()[0]->cant;

            $areas = DB::table('resumen_area_semanal')
                ->select(DB::raw('sum(area) as area'))
                ->where('codigo_semana', '>=', $desde_sem->codigo)
                ->where('codigo_semana', '<=', $hasta_sem->codigo)
                ->get()[0]->area;

            $model->valor = $areas > 0 ? round(($venta_mensual / ($areas / 4)) * 13, 2) : 0;
            $model->save();
            dump($desde_sem->codigo, $hasta_sem->codigo, $venta_mensual, $areas, $model->valor);

            /* ============================== INDICADOR x VARIEDAD ================================= */
            $semanas = DB::table('semana')
                ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
                ->where('codigo', '>=', $desde_sem->codigo)
                ->where('codigo', '<=', $hasta_sem->codigo)
                ->get();
            $variedades = Variedad::where('estado', 1)->get();
            foreach ($variedades as $pos_var => $var) {
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
                $venta_mensual = 0;
                foreach ($semanas as $sem) {
                    dump($sem->codigo);
                    $valor = 0;
                    if ($sem->codigo < 2142) {   // calcular las ventas a traves de los pedidos
                        $pedidos = Pedido::where('estado', 1)
                            ->where('fecha_pedido', '>=', $sem->fecha_inicial)
                            ->where('fecha_pedido', '<=', $sem->fecha_final)
                            ->get();
                        foreach ($pedidos as $p) {
                            if (!getFacturaAnulada($p->id_pedido)) {
                                $precio = $p->getPrecioByPedidoVariedad($var->id_variedad);
                                $valor += $precio;
                            }
                        }
                    } else {    // calcular las ventas a traves de clasificacion verde
                        $c_unitarias = DB::table('detalle_clasificacion_verde as d')
                            ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
                            ->join('clasificacion_unitaria as u', 'u.id_clasificacion_unitaria', '=', 'd.id_clasificacion_unitaria')
                            ->select('d.id_clasificacion_unitaria', 'u.precio_venta')->distinct()
                            ->where('d.estado', 1)
                            //->where('v.estado', 1)
                            ->where('d.id_variedad', $var->id_variedad)
                            ->where('v.fecha_ingreso', '>=', $sem->fecha_inicial)
                            ->where('v.fecha_ingreso', '<=', $sem->fecha_final)
                            ->get();
                        foreach ($c_unitarias as $u) {
                            $tallos = DB::table('detalle_clasificacion_verde as d')
                                ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
                                ->select(DB::raw('sum(cantidad_ramos * tallos_x_ramos) as tallos'))
                                ->where('d.estado', 1)
                                ->where('d.id_variedad', $var->id_variedad)
                                ->where('d.id_clasificacion_unitaria', $u->id_clasificacion_unitaria)
                                ->where('v.fecha_ingreso', '>=', $sem->fecha_inicial)
                                ->where('v.fecha_ingreso', '<=', $sem->fecha_final)
                                ->get()[0]->tallos;
                            $valor += $tallos * $u->precio_venta;
                        }
                    }
                    $venta_mensual += $valor;
                }

                $areas = DB::table('resumen_area_semanal')
                    ->select(DB::raw('sum(area) as area'))
                    ->where('id_variedad', $var->id_variedad)
                    ->where('codigo_semana', '>=', $desde_sem->codigo)
                    ->where('codigo_semana', '<=', $hasta_sem->codigo)
                    ->get()[0]->area;
                $ind->valor = $areas > 0 ? round(($venta_mensual / ($areas / 4)) * 13, 2) : 0;
                $ind->save();
                dump('var: ' . ($pos_var + 1) . '/' . count($variedades) . ' ; valor = ' . $ind->valor);
            }
        }
    }

    public static function cajas_equivalentes_vendidas_7_dias_atras()
    {
        $model = getIndicadorByName('D13'); // Cajas equivalentes vendidas (-7 días)
        $pedidos_semanal = Pedido::where('estado', 1)
            ->where('fecha_pedido', '>=', opDiasFecha('-', 7, date('Y-m-d')))
            ->where('fecha_pedido', '<=', opDiasFecha('-', 1, date('Y-m-d')))->get();
        $valor = 0;
        foreach ($pedidos_semanal as $pos_p => $pedido) {
            dump('D13 - pedido: ' . ($pos_p + 1) . '/' . count($pedidos_semanal));
            $valor += $pedido->getCajas();
        }

        $model->valor = $valor;
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
            $valor = 0;
            foreach ($pedidos_semanal as $pedido) {
                $valor += $pedido->getCajasByVariedad($var->id_variedad);
            }

            $ind->valor = $valor;
            $ind->save();
        }
    }

    public static function precio_por_tallo_7_dias_atras()
    {
        $model = getIndicadorByName('D14');  // Precio x tallo (-7 días)
        if ($model != '') {
            $desde = opDiasFecha('-', 7, hoy());
            $hasta = opDiasFecha('-', 1, hoy());
            $valor = DB::table('detalle_clasificacion_verde as d')
                ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
                ->join('clasificacion_unitaria as u', 'u.id_clasificacion_unitaria', '=', 'd.id_clasificacion_unitaria')
                ->select(DB::raw('sum((cantidad_ramos * tallos_x_ramos) * u.precio_venta) as venta'),
                    DB::raw('sum(cantidad_ramos * tallos_x_ramos) as tallos'))
                ->where('d.estado', 1)
                ->where('v.estado', 1)
                ->where('v.fecha_ingreso', '>=', $desde)
                ->where('v.fecha_ingreso', '<=', $hasta)
                ->get()[0];
            dump($desde, $hasta, $valor);
            $model->valor = round($valor->venta / $valor->tallos, 3);
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

                $valor = DB::table('detalle_clasificacion_verde as d')
                    ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
                    ->join('clasificacion_unitaria as u', 'u.id_clasificacion_unitaria', '=', 'd.id_clasificacion_unitaria')
                    ->select(DB::raw('sum((cantidad_ramos * tallos_x_ramos) * u.precio_venta) as venta'),
                        DB::raw('sum(cantidad_ramos * tallos_x_ramos) as tallos'))
                    ->where('d.estado', 1)
                    ->where('v.estado', 1)
                    ->where('d.id_variedad', $var->id_variedad)
                    ->where('v.fecha_ingreso', '>=', $desde)
                    ->where('v.fecha_ingreso', '<=', $hasta)
                    ->get()[0];

                $ind->valor = round($valor->venta / $valor->tallos, 3);
                $ind->save();
            }
        }
    }

    public static function variedades()
    {
        return Variedad::where('estado', 1)->select('id_variedad')->get();
    }
}
