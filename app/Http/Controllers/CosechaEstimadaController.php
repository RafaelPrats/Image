<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Modelos\PedidoModificacion;
use yura\Modelos\ResumenCosechaEstimada;

class CosechaEstimadaController extends Controller
{
    public function inicio(Request $request)
    {
        $fecha_min = hoy();
        $fecha_fin = opDiasFecha('+', 1, $fecha_min);

        return view('adminlte.crm.cosecha_estimada.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => Planta::where('estado', '=', 1)->orderBy('nombre')->get(),
            'desde' => $fecha_min,
            'hasta' => $fecha_fin,
        ]);
    }

    public function buscar_cosecha_estimada(Request $request)
    {
        $plantas = Planta::where('estado', 1);
        if ($request->planta != 'T')
            $plantas = $plantas->where('id_planta', $request->planta);
        $plantas = $plantas->orderBy('orden')->get();

        /*$ids_variedades = [];
        foreach ($plantas as $p)
            foreach ($p->variedades->where('estado', 1) as $var)
                if ($var->assorted == 0)
                    array_push($ids_variedades, $var->id_variedad);*/

        $fechas = [];
        $f = $request->desde;
        while ($f <= $request->hasta) {
            array_push($fechas, $f);
            $f = opDiasFecha('+', 1, $f);
        }


        $listado = [];
        foreach ($plantas as $p) {
            $longitudes = DB::table('proy_longitudes')
                ->where('id_planta', $p->id_planta)
                ->orderBy('orden')
                ->get();
            foreach ($longitudes as $long) {
                foreach ($p->variedades->where('assorted', 0)->where('estado', 1)->sortBy('orden') as $v) {
                    $valores = [];
                    $cant_total = 0;
                    $tiene_mod = false;
                    foreach ($fechas as $fecha) {
                        $query = DB::table('pedido as p')
                            ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                            ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                            ->join('detalle_cliente as cli', 'cli.id_cliente', '=', 'cpe.id_cliente')
                            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                            ->select(
                                'dp.id_detalle_pedido',
                                'dp.cantidad as piezas',
                                'dee.id_detalle_especificacionempaque',
                                'dee.tallos_x_ramos',
                                DB::raw('sum(dee.cantidad * dee.tallos_x_ramos * dp.cantidad) as tallos'),
                                DB::raw('sum(dee.cantidad * dp.cantidad) as ramos')
                            )->distinct()
                            ->where('p.estado', 1)
                            ->where('cli.estado', 1)
                            ->where('p.fecha_pedido', opDiasFecha('+', 1, $fecha))
                            ->where('dee.id_variedad', $v->id_variedad)
                            ->where('dee.longitud_ramo', $long->nombre)
                            ->whereNotNull('dee.tallos_x_ramos')
                            ->groupBy(
                                'dp.id_detalle_pedido',
                                'dp.cantidad',
                                'dee.id_detalle_especificacionempaque',
                                'dee.tallos_x_ramos',
                            )
                            ->get();
                        $tallos = 0;
                        $ramos = 0;
                        foreach ($query as $q) {
                            $getRamosXCajaModificado = getRamosXCajaModificado($q->id_detalle_pedido, $q->id_detalle_especificacionempaque);
                            $ramos += isset($getRamosXCajaModificado) ? ($getRamosXCajaModificado->cantidad * $q->piezas) : $q->ramos;
                            $tallos += isset($getRamosXCajaModificado) ? ($getRamosXCajaModificado->cantidad * $q->tallos_x_ramos * $q->piezas) : $q->tallos;
                        }

                        $cant_mixtos = DB::table('distribucion_mixtos as d')
                            ->join('detalle_especificacionempaque as dee', 'dee.id_detalle_especificacionempaque', '=', 'd.id_detalle_especificacionempaque')
                            ->select(DB::raw('sum(d.ramos * d.piezas * dee.tallos_x_ramos) as cantidad'))
                            ->where('d.id_planta', $p->id_planta)
                            ->where('d.siglas', $v->siglas)
                            ->where('d.fecha', $fecha)
                            ->where('dee.longitud_ramo', $long->nombre)
                            ->get()[0]->cantidad;
                        $actual = $tallos + $cant_mixtos;

                        $modificaciones_solidos = PedidoModificacion::join('detalle_especificacionempaque as dee', 'dee.id_detalle_especificacionempaque', '=', 'pedido_modificacion.id_detalle_especificacionempaque')
                            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                            ->select(
                                'pedido_modificacion.*',
                                'dee.cantidad as dee_cantidad',
                                'dee.tallos_x_ramos as dee_tallos_x_ramos'
                            )->distinct()
                            ->where('v.id_planta', $p->id_planta)
                            ->where('v.siglas', $v->siglas)
                            ->whereNotNull('pedido_modificacion.cantidad')
                            ->where('pedido_modificacion.fecha_anterior_pedido', opDiasFecha('+', 1, $fecha))
                            ->where('pedido_modificacion.usar', 1)
                            ->where('dee.longitud_ramo', $long->nombre)
                            ->get();

                        $modificaciones_mixtas = PedidoModificacion::join('detalle_especificacionempaque as dee', 'dee.id_detalle_especificacionempaque', '=', 'pedido_modificacion.id_detalle_especificacionempaque')
                            ->select('pedido_modificacion.*')->distinct()
                            ->where('pedido_modificacion.id_planta', $p->id_planta)
                            ->where('pedido_modificacion.siglas', $v->siglas)
                            ->whereNull('pedido_modificacion.cantidad')
                            ->where('pedido_modificacion.fecha_anterior_pedido', opDiasFecha('+', 1, $fecha))
                            ->where('pedido_modificacion.usar', 1)
                            ->where('dee.longitud_ramo', $long->nombre)
                            ->get();

                        $val_mod = 0;
                        foreach ($modificaciones_solidos as $mod) {
                            $ramos_x_caja = $mod->ramos_x_caja != '' ? $mod->ramos_x_caja : $mod->dee_cantidad;
                            if ($mod->operador == '+') {
                                $val_mod += $mod->cantidad * $ramos_x_caja * $mod->dee_tallos_x_ramos;
                                $actual -= $mod->cantidad * $ramos_x_caja * $mod->dee_tallos_x_ramos;
                            } else {
                                $val_mod -= $mod->cantidad * $ramos_x_caja * $mod->dee_tallos_x_ramos;
                                $actual += $mod->cantidad * $ramos_x_caja * $mod->dee_tallos_x_ramos;
                            }
                        }
                        foreach ($modificaciones_mixtas as $mod) {
                            if ($mod->operador == '+') {
                                $val_mod += $mod->tallos;
                                $actual -= $mod->tallos;
                            } else {
                                $val_mod -= $mod->tallos;
                                $actual += $mod->tallos;
                            }
                        }

                        $tallos_bqt = DB::table('distribucion_recetas as dr')
                            ->join('detalle_pedido as dp', 'dp.id_detalle_pedido', '=', 'dr.id_detalle_pedido')
                            ->join('pedido as p', 'p.id_pedido', '=', 'dp.id_pedido')
                            ->select(DB::raw('sum(dr.tallos) as cantidad'))
                            ->where('dr.id_planta', $p->id_planta)
                            ->where('dr.siglas', $v->siglas)
                            ->where('dr.longitud_ramo', $long->nombre)
                            ->where('p.fecha_pedido', opDiasFecha('+', 1, $fecha))
                            ->get()[0]->cantidad;
                        array_push($valores, [
                            'fijos' => $actual,
                            'mod' => $val_mod,
                            'tallos_bqt' => $tallos_bqt,
                        ]);

                        $cant_total += $actual + $tallos_bqt;
                        if ($val_mod != 0)
                            $tiene_mod = true;
                    }

                    if ($cant_total > 0 || $tiene_mod) {
                        $agregar = false;
                        if ($request->cambios == 'C') {
                            $agregar = $tiene_mod;
                        } else
                            $agregar = true;
                        if ($agregar)
                            array_push($listado, [
                                'planta' => $p,
                                'long' => $long->nombre,
                                'variedad' => $v,
                                'valores' => $valores,
                            ]);
                    }
                }
            }
        }
        return view('adminlte.crm.cosecha_estimada.partials.listado', [
            'listado' => $listado,
            'fechas' => $fechas,
        ]);
    }

    public function buscar_cosecha_estimada_new(Request $request)
    {
        $plantas = Planta::where('estado', 1);
        if ($request->planta != 'T')
            $plantas = $plantas->where('id_planta', $request->planta);
        $plantas = $plantas->orderBy('orden')->get();

        $fechas = DB::table('proyecto')
            ->select('fecha')
            ->distinct()
            ->where('fecha', '>=', $request->desde)
            ->where('fecha', '<=', $request->hasta)
            ->orderBy('fecha')
            ->get()->pluck('fecha')->toArray();

        $listado = [];
        foreach ($plantas as $p) {
            $longitudes = DB::table('proy_longitudes')
                ->where('id_planta', $p->id_planta)
                ->orderBy('orden')
                ->get();
            foreach ($longitudes as $long) {
                foreach ($p->variedades->where('assorted', 0)->where('estado', 1)->sortBy('orden') as $v) {
                    $valores = [];
                    $cant_total = 0;
                    $tiene_mod = false;
                    foreach ($fechas as $fecha) {
                        $resumen = ResumenCosechaEstimada::where('id_variedad', $v->id_variedad)
                            ->where('longitud', $long->nombre)
                            ->where('fecha', $fecha)
                            ->get()
                            ->first();
                        if ($resumen != '') {
                            array_push($valores, [
                                'fijos' => $resumen->actual,
                                'mod' => $resumen->cambios,
                                'tallos_bqt' => $resumen->tallos_bqt,
                            ]);

                            $cant_total += $resumen->actual + $resumen->tallos_bqt;
                            if ($resumen->cambios != 0)
                                $tiene_mod = true;
                        }
                    }

                    if ($cant_total > 0 || $tiene_mod) {
                        $agregar = false;
                        if ($request->cambios == 'C') {
                            $agregar = $tiene_mod;
                        } else
                            $agregar = true;
                        if ($agregar)
                            array_push($listado, [
                                'planta' => $p,
                                'long' => $long->nombre,
                                'variedad' => $v,
                                'valores' => $valores,
                            ]);
                    }
                }
            }
        }
        return view('adminlte.crm.cosecha_estimada.partials.listado', [
            'listado' => $listado,
            'fechas' => $fechas,
        ]);
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);

        $fileName = "Cosecha_Estimada.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_reporte($spread, $request)
    {
        $plantas = Planta::where('estado', 1);
        if ($request->planta != 'T')
            $plantas = $plantas->where('id_planta', $request->planta);
        $plantas = $plantas->orderBy('orden')->get();

        $fechas = DB::table('proyecto')
            ->select('fecha')
            ->distinct()
            ->where('fecha', '>=', $request->desde)
            ->where('fecha', '<=', $request->hasta)
            ->orderBy('fecha')
            ->get()->pluck('fecha')->toArray();

        $listado = [];
        foreach ($plantas as $p) {
            $longitudes = DB::table('proy_longitudes')
                ->where('id_planta', $p->id_planta)
                ->orderBy('orden')
                ->get();
            foreach ($longitudes as $long) {
                foreach ($p->variedades->where('assorted', 0)->where('estado', 1)->sortBy('orden') as $v) {
                    $valores = [];
                    $cant_total = 0;
                    $tiene_mod = false;
                    foreach ($fechas as $fecha) {
                        $resumen = ResumenCosechaEstimada::where('id_variedad', $v->id_variedad)
                            ->where('longitud', $long->nombre)
                            ->where('fecha', $fecha)
                            ->get()
                            ->first();
                        if ($resumen != '') {
                            array_push($valores, [
                                'fijos' => $resumen->actual,
                                'mod' => $resumen->cambios,
                                'tallos_bqt' => $resumen->tallos_bqt,
                            ]);

                            $cant_total += $resumen->actual + $resumen->tallos_bqt;
                            if ($resumen->cambios != 0)
                                $tiene_mod = true;
                        }
                    }

                    if ($cant_total > 0 || $tiene_mod) {
                        $agregar = false;
                        if ($request->cambios == 'C') {
                            $agregar = $tiene_mod;
                        } else
                            $agregar = true;
                        if ($agregar)
                            array_push($listado, [
                                'planta' => $p,
                                'long' => $long->nombre,
                                'variedad' => $v,
                                'valores' => $valores,
                            ]);
                    }
                }
            }
        }
        //dd('EN desarrollo');

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Cosecha Estimada');

        //$sheet->setCellValue('A1', 'En desarrollo!');
        setValueToCeldaExcel($sheet, 'A1', 'Variedad');
        setValueToCeldaExcel($sheet, 'B1', 'Color');
        setBoldToCeldaExcel($sheet, 'A1:B1');
        setBgToCeldaExcel($sheet, 'A1:B1', '00b388');
        setColorTextToCeldaExcel($sheet, 'A1:B1', 'ffffff');
        $totales = [];
        $totales_cambio = [];
        $totales_bqt = [];
        $array_total_grupo_fechas = [];
        $array_total_grupo_cambios = [];
        $array_total_grupo_bqt = [];
        $col = 2;
        $row = 1;
        foreach ($fechas as $f) {
            $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col + 2] . $row);
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $f);
            setBoldToCeldaExcel($sheet, $columnas[$col] . $row);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            $totales[] = 0;
            $totales_cambio[] = 0;
            $totales_bqt[] = 0;
            $array_total_grupo_fechas[] = 0;
            $array_total_grupo_cambios[] = 0;
            $array_total_grupo_bqt[] = 0;
            $col += 3;
        }
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col + 2] . $row);
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTAL');
        setBoldToCeldaExcel($sheet, $columnas[$col] . $row);
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');

        $actual = $listado[0]['planta']->nombre . ' ' . $listado[0]['long'] . 'cm';
        $total_grupo_fechas = $array_total_grupo_fechas;
        $count_listado = count($listado);
        foreach ($listado as $pos_i => $item) {
            $col = 0;
            $row++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['planta']->nombre);
            setBoldToCeldaExcel($sheet, $columnas[$col] . $row);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, 'cecbcb');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['variedad']->nombre . ' ' . $item['long'] . 'cm');
            setBoldToCeldaExcel($sheet, $columnas[$col] . $row);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, 'cecbcb');
            $total_var = 0;
            $total_var_cambios = 0;
            $total_var_bqt = 0;
            foreach ($item['valores'] as $pos => $v) {
                $total_var += $v['fijos'];
                $total_var_cambios += $v['mod'];
                $total_var_bqt += $v['tallos_bqt'];
                $totales[$pos] += $v['fijos'];
                $totales_cambio[$pos] += $v['mod'];
                $totales_bqt[$pos] += $v['tallos_bqt'];
                $total_grupo_fechas[$pos] += $v['fijos'];
                $array_total_grupo_cambios[$pos] += $v['mod'];
                $array_total_grupo_bqt[$pos] += $v['tallos_bqt'];
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $v['fijos'] + $v['tallos_bqt']);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, $pos_i % 2 == 0 ? 'e5e5e5' : 'ffffff');
                $col++;
                if ($v['mod'] != 0) {
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $v['fijos'] + $v['mod'] + $v['tallos_bqt']);
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, $pos_i % 2 == 0 ? 'e5e5e5' : 'ffffff');
                }
                $col++;
                if ($v['mod'] != 0) {
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $v['mod']);
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, $pos_i % 2 == 0 ? 'e5e5e5' : 'ffffff');
                }
            }
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_var + $total_var_bqt);
            setBoldToCeldaExcel($sheet, $columnas[$col] . $row);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            $col++;
            if ($total_var_cambios != 0) {
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_var + $total_var_cambios + $total_var_bqt);
                setBoldToCeldaExcel($sheet, $columnas[$col] . $row);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
                setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            }
            $col++;
            if ($total_var_cambios != 0) {
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_var_cambios);
                setBoldToCeldaExcel($sheet, $columnas[$col] . $row);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
                setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            }

            $next = $pos_i == $count_listado - 1 ? null : $listado[$pos_i + 1]['planta']->nombre . ' ' . $listado[$pos_i + 1]['long'] . 'cm';
            if ($actual != $next) {
                $col = 0;
                $row++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $actual);
                setBoldToCeldaExcel($sheet, $columnas[$col] . $row);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
                setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col + 1] . $row);
                $total_grupo = 0;
                $total_grupo_cambio = 0;
                $total_grupo_bqt = 0;
                $col = 2;
                foreach ($total_grupo_fechas as $posi => $v) {
                    $total_grupo += $v;
                    $total_grupo_cambio += $array_total_grupo_cambios[$posi];
                    $total_grupo_bqt += $array_total_grupo_bqt[$posi];
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $v + $array_total_grupo_bqt[$posi]);
                    setBoldToCeldaExcel($sheet, $columnas[$col] . $row);
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
                    setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                    $col++;
                    if ($array_total_grupo_cambios[$posi] != 0) {
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $v + $array_total_grupo_cambios[$posi] + $array_total_grupo_bqt[$posi]);
                        setBoldToCeldaExcel($sheet, $columnas[$col] . $row);
                        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
                        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                    }
                    $col++;
                    if ($array_total_grupo_cambios[$posi] != 0) {
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $array_total_grupo_cambios[$posi]);
                        setBoldToCeldaExcel($sheet, $columnas[$col] . $row);
                        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
                        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                    }
                    $col++;
                    $array_total_grupo_cambios[$posi] = 0;
                    $array_total_grupo_bqt[$posi] = 0;
                }
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_grupo + $total_grupo_bqt);
                setBoldToCeldaExcel($sheet, $columnas[$col] . $row);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
                setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                $col++;
                if ($total_grupo_cambio != 0) {
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_grupo + $total_grupo_cambio + $total_grupo_bqt);
                    setBoldToCeldaExcel($sheet, $columnas[$col] . $row);
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
                    setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                }
                $col++;
                if ($total_grupo_cambio != 0) {
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_grupo_cambio);
                    setBoldToCeldaExcel($sheet, $columnas[$col] . $row);
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
                    setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                }
                $actual = $next;
                $total_grupo_fechas = $array_total_grupo_fechas;
            }
        }
        $col = 0;
        $row++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        setBoldToCeldaExcel($sheet, $columnas[$col] . $row);
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col + 1] . $row);
        $total = 0;
        $total_cambio = 0;
        $total_bqt = 0;
        $col = 2;
        foreach ($totales as $posy => $v) {
            $total += $v;
            $total_cambio += $totales_cambio[$posy];
            $total_bqt += $totales_bqt[$posy];
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $v + $totales_bqt[$posy]);
            setBoldToCeldaExcel($sheet, $columnas[$col] . $row);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            $col++;
            if ($totales_cambio[$posy] != 0) {
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $v + $totales_cambio[$posy] + $totales_bqt[$posy]);
                setBoldToCeldaExcel($sheet, $columnas[$col] . $row);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
                setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            }
            $col++;
            if ($totales_cambio[$posy] != 0) {
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $totales_cambio[$posy]);
                setBoldToCeldaExcel($sheet, $columnas[$col] . $row);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
                setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            }
            $col++;
        }
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total);
        setBoldToCeldaExcel($sheet, $columnas[$col] . $row);
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        $col++;
        if ($total_cambio != 0) {
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total + $total_cambio);
            setBoldToCeldaExcel($sheet, $columnas[$col] . $row);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        }
        $col++;
        if ($total_cambio != 0) {
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_cambio);
            setBoldToCeldaExcel($sheet, $columnas[$col] . $row);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        }

        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
