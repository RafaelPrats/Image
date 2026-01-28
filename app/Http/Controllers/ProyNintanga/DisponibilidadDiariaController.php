<?php

namespace yura\Http\Controllers\ProyNintanga;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Http\Controllers\Controller;
use yura\Modelos\DisponibilidadDiaria;
use yura\Modelos\DistribucionMixtosDiaria;
use yura\Modelos\Planta;
use yura\Modelos\ProyVariedadCortes;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class DisponibilidadDiariaController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = Planta::where('estado', 1)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.proyeccion_nintanga.disponibilidad_diaria.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $plantas = Planta::where('estado', 1);
        if ($request->planta != '')
            $plantas = $plantas->where('id_planta', $request->planta);
        $plantas = $plantas->orderBy('orden')
            ->get();
        $listado = [];
        $new_dispo = false;
        foreach ($plantas as $planta) {
            $variedades = Variedad::where('id_planta', $planta->id_planta)
                ->where('assorted', 0)
                ->where('estado', 1)
                ->orderBy('orden')
                ->get();
            $query_longitudes = DB::table('proy_longitudes')
                ->where('id_planta', $planta->id_planta)
                ->orderBy('orden')
                ->get();
            $longitudes = [];
            foreach ($query_longitudes as $long) {
                $query_cortes = DB::table('proy_cortes')
                    ->where('id_planta', $planta->id_planta)
                    ->where('nombre', 'like', '%' . $long->nombre . '%')
                    ->orderBy('nombre', 'asc')
                    ->get();
                $cortes = $query_cortes;
                if (count($cortes) == 0)
                    $cortes = DB::table('proy_cortes')
                        ->where('id_planta', $planta->id_planta)
                        ->orderBy('nombre', 'asc')
                        ->get();
                $valores_longitud = [];
                foreach ($variedades as $var) {
                    $valores_cortes = [];
                    foreach ($cortes as $c) {
                        $query = ProyVariedadCortes::where('id_variedad', $var->id_variedad)
                            ->where('id_cortes', $c->id_proy_cortes)
                            ->where('fecha', opDiasFecha('-', 1, $request->fecha))
                            ->get()
                            ->first();
                        array_push($valores_cortes, $query != '' ? $query->cantidad : '');
                    }
                    $pedidos_solidos = DB::table('pedido as p')
                        ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                        ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                        ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                        ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                        ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                        ->select(
                            DB::raw('sum(dee.cantidad * dee.tallos_x_ramos * dp.cantidad) as cantidad')
                        )
                        ->where('p.estado', 1)
                        ->where('p.fecha_pedido', $request->fecha)
                        ->where('v.siglas', '=', $var->siglas)
                        ->where('v.id_planta', $planta->id_planta)
                        ->where('dee.longitud_ramo', $long->nombre)
                        ->whereNotNull('dee.tallos_x_ramos')
                        ->get()[0]->cantidad;
                    $mixtos = DB::table('distribucion_mixtos')
                        ->select(
                            DB::raw('sum(tallos) as cantidad')
                        )
                        ->where('fecha', opDiasFecha('-', 1, $request->fecha))
                        ->where('siglas', $var->siglas)
                        ->where('id_planta', $planta->id_planta)
                        ->where('longitud_ramo', $long->nombre)
                        ->get()[0]->cantidad;
                    $cuarto_frio = DB::table('inventario_frio')
                        ->select(DB::raw('sum(disponibles * tallos_x_ramo) as cantidad'))
                        ->where('id_variedad', $var->id_variedad)
                        ->where('longitud_ramo', $long->nombre)
                        ->where('estado', 1)
                        ->where('disponibilidad', 1)
                        ->where('basura', 0)
                        ->get()[0]->cantidad;

                    //dd($pedidos_solidos, $mixtos, $cuarto_frio, $valores_cortes);

                    // CALCULAR SALDOS
                    $saldo_var_cs = 0;
                    $solidos = $pedidos_solidos != null ? $pedidos_solidos : 0;
                    $valor = $cuarto_frio != '' ? $cuarto_frio : 0;
                    if ($solidos >= $valor) {
                        $solidos -= $valor;
                        $saldo_sd = 0;
                    } else {
                        $saldo_sd = $valor - $solidos;
                        $solidos = 0;
                    }
                    $saldo_cs = $saldo_sd;

                    if ($solidos == 0) {
                        if ($mixtos >= $saldo_cs) {
                            $mixtos -= $saldo_cs;
                            $saldo_cs = 0;
                        } else {
                            $saldo_cs -= $mixtos;
                            $mixtos = 0;
                        }
                    }
                    $saldo_var_cs += $saldo_cs;

                    foreach ($valores_cortes as $pos_c => $v) {
                        if ($cortes[$pos_c]->usar == 1) {
                            $valor = $v != '' ? $v : 0;
                            if ($solidos >= $valor) {
                                $solidos -= $valor;
                                $saldo_sd = 0;
                            } else {
                                $saldo_sd = $valor - $solidos;
                                $solidos = 0;
                            }
                            $saldo_cs = $saldo_sd;

                            if ($solidos == 0) {
                                if ($mixtos >= $saldo_cs) {
                                    $mixtos -= $saldo_cs;
                                    $saldo_cs = 0;
                                } else {
                                    $saldo_cs -= $mixtos;
                                    $mixtos = 0;
                                }
                            }
                            $saldo_var_cs += $saldo_cs;
                        }
                    }

                    $pedidos_solidos_next = DB::table('pedido as p')
                        ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                        ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                        ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                        ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                        ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                        ->select(
                            DB::raw('sum(dee.cantidad * dee.tallos_x_ramos * dp.cantidad) as cantidad')
                        )
                        ->where('p.estado', 1)
                        ->where('p.fecha_pedido', opDiasFecha('+', 1, $request->fecha))
                        ->where('v.siglas', '=', $var->siglas)
                        ->where('v.id_planta', $planta->id_planta)
                        ->where('dee.longitud_ramo', $long->nombre)
                        ->whereNotNull('dee.tallos_x_ramos')
                        ->get()[0]->cantidad;
                    $mixtos_next = DB::table('distribucion_mixtos')
                        ->select(
                            DB::raw('sum(tallos) as cantidad')
                        )
                        ->where('fecha', $request->fecha)
                        ->where('siglas', $var->siglas)
                        ->where('id_planta', $planta->id_planta)
                        ->where('longitud_ramo', $long->nombre)
                        ->get()[0]->cantidad;
                    $saldo_var_cs -= ($pedidos_solidos_next + $mixtos_next);
                    if ($saldo_var_cs > 0) {
                        $dispo = DisponibilidadDiaria::where('id_variedad', $var->id_variedad)
                            ->where('longitud_ramo', $long->nombre)
                            ->where('fecha', $request->fecha)
                            ->get()
                            ->first();
                        if ($dispo == '') {
                            $dispo = DisponibilidadDiaria::where('id_variedad', $var->id_variedad)
                                ->where('longitud_ramo', $long->nombre)
                                ->orderBy('fecha', 'desc')
                                ->get()
                                ->first();
                            $new_dispo = true;
                        }
                        $valores_longitud[] = [
                            'variedad' => $var,
                            'cuarto_frio' => $cuarto_frio,
                            'saldo' => $saldo_var_cs,
                            'dispo' => $dispo,
                        ];
                    }
                }
                if (count($valores_longitud) > 0)
                    $longitudes[] = [
                        'longitud' => $long,
                        'valores' => $valores_longitud,
                    ];
            }
            if (count($longitudes) > 0)
                $listado[] = [
                    'planta' => $planta,
                    'longitudes' => $longitudes,
                ];
        }

        return view('adminlte.gestion.proyeccion_nintanga.disponibilidad_diaria.partials.listado', [
            'listado' => $listado,
            'new_dispo' => $new_dispo,
            'fecha' => $request->fecha,
        ]);
    }

    public function store_disponibilidad_diaria(Request $request)
    {
        foreach (json_decode($request->data) as $d) {
            $variedad = explode('_', $d->id)[0];
            $longitud = explode('_', $d->id)[1];
            $model = DisponibilidadDiaria::where('id_variedad', $variedad)
                ->where('longitud_ramo', $longitud)
                ->where('fecha', $request->fecha)
                ->get()
                ->first();
            if ($model == '') {
                $model = new DisponibilidadDiaria();
                $model->id_variedad = $variedad;
                $model->longitud_ramo = $longitud;
                $model->fecha = $request->fecha;
            }
            $model->tallos_x_ramo = $d->tallos_x_ramo;
            $model->ramos_x_caja = $d->ramos_x_caja;
            $model->tipo_caja = mb_strtoupper($d->tipo_caja);
            $model->precio = $d->precio;
            $model->save();
        }
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>GUARDADO</strong> la disponiblidad'
        ];
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);

        $fileName = "Disponibilidad_Diaria.xlsx";
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
        if ($request->planta != '')
            $plantas = $plantas->where('id_planta', $request->planta);
        $plantas = $plantas->orderBy('orden')
            ->get();
        $listado = [];
        foreach ($plantas as $planta) {
            $variedades = Variedad::where('id_planta', $planta->id_planta)
                ->where('assorted', 0)
                ->where('estado', 1)
                ->orderBy('orden')
                ->get();
            $query_longitudes = DB::table('proy_longitudes')
                ->where('id_planta', $planta->id_planta)
                ->orderBy('orden')
                ->get();
            $longitudes = [];
            foreach ($query_longitudes as $long) {
                $query_cortes = DB::table('proy_cortes')
                    ->where('id_planta', $planta->id_planta)
                    ->where('nombre', 'like', '%' . $long->nombre . '%')
                    ->orderBy('nombre', 'asc')
                    ->get();
                $cortes = $query_cortes;
                if (count($cortes) == 0)
                    $cortes = DB::table('proy_cortes')
                        ->where('id_planta', $planta->id_planta)
                        ->orderBy('nombre', 'asc')
                        ->get();
                $valores_longitud = [];
                foreach ($variedades as $var) {
                    $valores_cortes = [];
                    foreach ($cortes as $c) {
                        $query = ProyVariedadCortes::where('id_variedad', $var->id_variedad)
                            ->where('id_cortes', $c->id_proy_cortes)
                            ->where('fecha', $request->fecha)
                            ->get()
                            ->first();
                        array_push($valores_cortes, $query != '' ? $query->cantidad : '');
                    }
                    $pedidos_solidos = DB::table('pedido as p')
                        ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                        ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                        ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                        ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                        ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                        ->select(
                            DB::raw('sum(dee.cantidad * dee.tallos_x_ramos * dp.cantidad) as cantidad')
                        )
                        ->where('p.estado', 1)
                        ->where('p.fecha_pedido', opDiasFecha('+', 1, $request->fecha))
                        ->where('v.siglas', '=', $var->siglas)
                        ->where('v.id_planta', $planta->id_planta)
                        ->where('dee.longitud_ramo', $long->nombre)
                        ->whereNotNull('dee.tallos_x_ramos')
                        ->get()[0]->cantidad;

                    $cuarto_frio = DB::table('inventario_frio')
                        ->select(DB::raw('sum(cantidad * tallos_x_ramo) as cantidad'))
                        ->where('id_variedad', $var->id_variedad)
                        ->where('longitud_ramo', $long->nombre)
                        ->where('estado', 1)
                        ->where('disponibilidad', 1)
                        ->where('basura', 0)
                        ->get()[0]->cantidad;

                    // CALCULAR SALDOS
                    $solidos = $pedidos_solidos != null ? $pedidos_solidos : 0;
                    $valor = $cuarto_frio != '' ? $cuarto_frio : 0;
                    if ($solidos >= $valor) {
                        $solidos -= $valor;
                        $saldo_sd = 0;
                    } else {
                        $saldo_sd = $valor - $solidos;
                        $solidos = 0;
                    }

                    foreach ($valores_cortes as $pos_c => $v) {
                        if ($cortes[$pos_c]->usar == 1 && $solidos > 0) {
                            $valor = $v != '' ? $v : 0;
                            if ($solidos >= $valor) {
                                $solidos -= $valor;
                                $saldo_sd = 0;
                            } else {
                                $saldo_sd = $valor - $solidos;
                                $solidos = 0;
                            }
                        }
                    }

                    if ($saldo_sd > 0) {
                        $dispo = DisponibilidadDiaria::where('id_variedad', $var->id_variedad)
                            ->where('longitud_ramo', $long->nombre)
                            ->where('fecha', $request->fecha)
                            ->get()
                            ->first();
                        $valores_longitud[] = [
                            'variedad' => $var,
                            'cuarto_frio' => $cuarto_frio,
                            'saldo' => $saldo_sd,
                            'dispo' => $dispo,
                        ];
                    }
                }
                if (count($valores_longitud) > 0)
                    $longitudes[] = [
                        'longitud' => $long,
                        'valores' => $valores_longitud,
                    ];
            }
            if (count($longitudes) > 0)
                $listado[] = [
                    'planta' => $planta,
                    'longitudes' => $longitudes,
                ];
        }

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Disponibilidad Diaria');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'PLANTA');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'COLOR');
        /*$col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'DISPONIBILIDAD');*/
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TIPO CAJA');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TALLOS x RAMO');
        /*$col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'RAMOS');*/
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'RAMOS x CAJA');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTAL CAJAS');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'PRECIO');
        setBgToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row, 'ffffff');

        $total_saldo = 0;
        foreach ($listado as $pos_pta => $pta) {
            foreach ($pta['longitudes'] as $pos_long => $long) {
                $total_saldo_planta_longitud = 0;
                foreach ($long['valores'] as $pos_val => $val) {
                    $total_saldo_planta_longitud += $val['saldo'];
                    $total_saldo += $val['saldo'];
                    $row++;
                    $col = 0;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pta['planta']->nombre);
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, 'dddddd');
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['variedad']->nombre . ' ' . $long['longitud']->nombre . 'cm');
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, 'dddddd');
                    /*$col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['saldo']);*/
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['dispo'] != '' ? $val['dispo']->tipo_caja : '');
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['dispo'] != '' ? $val['dispo']->tallos_x_ramo : '');
                    //$col++;
                    $ramos = ($val['dispo'] != '' && $val['dispo']->tallos_x_ramo > 0) ? round($val['saldo'] / $val['dispo']->tallos_x_ramo) : 0;
                    //setValueToCeldaExcel($sheet, $columnas[$col] . $row, $ramos);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['dispo'] != '' ? $val['dispo']->ramos_x_caja : '');
                    $col++;
                    $cajas = ($val['dispo'] != '' && $val['dispo']->ramos_x_caja > 0) ? round($ramos / $val['dispo']->ramos_x_caja) : '';
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $cajas);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['dispo'] != '' ? $val['dispo']->precio : '');
                }
                $row++;
                $col = 0;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pta['planta']->nombre . ' ' . $long['longitud']->nombre . 'cm');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, '');
                /*$col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_saldo_planta_longitud);*/
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, '');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, '');
                /*$col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, '');*/
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, '');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, '');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, '');
                setBgToCeldaExcel($sheet, 'A' . $row . ':' . $columnas[$col] . $row, '5A7177');
                setColorTextToCeldaExcel($sheet, 'A' . $row . ':' . $columnas[$col] . $row, 'ffffff');
            }
        }
        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, '');
        /*$col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_saldo);*/
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, '');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, '');
        /*$col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, '');*/
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, '');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, '');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, '');
        setBgToCeldaExcel($sheet, 'A' . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, 'A' . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
