<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Modelos\DistribucionMixtosDiaria;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use yura\Modelos\ProyLongitudes;
use yura\Modelos\ProyVariedadCortes;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CuadreFlorController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = Planta::where('estado', 1)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.postcocecha.cuadre_flor.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $variedades = Variedad::where('id_planta', $request->planta)
            ->where('assorted', 0)
            ->where('estado', 1)
            ->orderBy('orden')
            ->get();
        $longitud = ProyLongitudes::find($request->longitud);

        $listado = [];
        foreach ($variedades as $pos_var => $var) {
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
                ->where('v.id_planta', $request->planta)
                ->where('dee.longitud_ramo', $longitud->nombre)
                ->whereNotNull('dee.tallos_x_ramos')
                ->get()[0]->cantidad;
            $mixtos = DB::table('distribucion_mixtos')
                ->select(
                    DB::raw('sum(tallos) as cantidad')
                )
                ->where('fecha', opDiasFecha('-', 1, $request->fecha))
                ->where('siglas', $var->siglas)
                ->where('id_planta', $request->planta)
                ->where('longitud_ramo', $longitud->nombre)
                ->get()[0]->cantidad;
            $pedidos_solidos_anterior = 0;
            $mixtos_anterior = 0;
            if ($request->fecha == opDiasFecha('+', 2, hoy())) {
                $pedidos_solidos_anterior = DB::table('pedido as p')
                    ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                    ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                    ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                    ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                    ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                    ->select(
                        DB::raw('sum(dee.cantidad * dee.tallos_x_ramos * dp.cantidad) as cantidad')
                    )
                    ->where('p.estado', 1)
                    ->where('p.fecha_pedido', opDiasFecha('-', 1, $request->fecha))
                    ->where('v.siglas', '=', $var->siglas)
                    ->where('v.id_planta', $request->planta)
                    ->where('dee.longitud_ramo', $longitud->nombre)
                    ->whereNotNull('dee.tallos_x_ramos')
                    ->get()[0]->cantidad;
                $mixtos_anterior = DB::table('distribucion_mixtos')
                    ->select(
                        DB::raw('sum(tallos) as cantidad')
                    )
                    ->where('fecha', opDiasFecha('-', 2, $request->fecha))
                    ->where('siglas', $var->siglas)
                    ->where('id_planta', $request->planta)
                    ->where('longitud_ramo', $longitud->nombre)
                    ->get()[0]->cantidad;
            }
            $cuarto_frio = DB::table('inventario_frio')
                ->select(DB::raw('sum(disponibles * tallos_x_ramo) as cantidad'))
                ->where('id_variedad', $var->id_variedad)
                ->where('longitud_ramo', $longitud->nombre)
                ->where('estado', 1)
                ->where('disponibilidad', 1)
                ->where('basura', 0);
            if ($request->fecha == hoy())
                $cuarto_frio = $cuarto_frio->where('fecha_ingreso', '<=', opDiasFecha('-', 1, hoy()));
            $cuarto_frio = $cuarto_frio->get()[0]->cantidad;
            $cosecha = DB::table('desglose_recepcion as dr')
                ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                ->select(DB::raw('sum(cantidad_mallas * tallos_x_malla) as cantidad'))
                ->where('r.fecha_ingreso', $request->fecha)
                ->where('dr.id_variedad', $var->id_variedad)
                ->where('dr.longitud_ramo', $longitud->nombre)
                ->get()[0]->cantidad;
            $sobrante = DB::table('sobrante_recepcion as sr')
                ->select(DB::raw('sum(cantidad) as cantidad'))
                ->where('sr.fecha', opDiasFecha('-', 1, $request->fecha))
                ->where('sr.id_variedad', $var->id_variedad)
                ->where('sr.longitud', $longitud->nombre)
                ->get()[0]->cantidad;
            $saldo = $cuarto_frio + $cosecha - ($pedidos_solidos + $mixtos);
            $corte_objetivo = '';
            if ($saldo < 0 && $request->fecha < opDiasFecha('+', 2, hoy())) {
                $cortes = DB::table('proy_variedad_cortes as p')
                    ->join('proy_cortes as c', 'c.id_proy_cortes', '=', 'p.id_cortes')
                    ->select('c.nombre', 'p.cantidad')
                    ->where('p.id_variedad', $var->id_variedad)
                    ->where('p.fecha', opDiasFecha('-', 1, $request->fecha))
                    ->orderBy('c.nombre')
                    ->get();
                $meta = $saldo;
                foreach ($cortes as $cort) {
                    $meta += $cort->cantidad;
                    if ($meta >= 0) {
                        $corte_objetivo = $cort->nombre;
                        break;
                    }
                }
            }
            array_push($listado, [
                'var' => $var,
                'pedidos_solidos' => $pedidos_solidos,
                'pedidos_solidos_anterior' => $pedidos_solidos_anterior,
                'pedidos_mixtos' => $mixtos,
                'pedidos_mixtos_anterior' => $mixtos_anterior,
                'cuarto_frio' => $cuarto_frio,
                'cosecha' => $cosecha,
                'sobrante' => $sobrante,
                'saldo' => $saldo,
                'corte_objetivo' => $corte_objetivo,
            ]);
        }

        return view('adminlte.gestion.postcocecha.cuadre_flor.partials.listado', [
            'listado' => $listado,
            'variedades' => $variedades,
            'fecha' => $request->fecha,
            'planta' => $request->planta,
        ]);
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);

        $fileName = "Cuadre de Flor.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_reporte($spread, $request)
    {
        $plantas = Planta::where('estado', 1)
            ->orderBy('orden')
            ->get();
        $listado = [];
        foreach ($plantas as $p) {
            $longitudes = DB::table('proy_longitudes')
                ->where('id_planta', $p->id_planta)
                ->orderBy('orden')
                ->get();
            $variedades = Variedad::where('id_planta', $p->id_planta)
                ->where('assorted', 0)
                ->where('estado', 1)
                ->orderBy('orden')
                ->get();
            $valores_longitudes = [];
            foreach ($longitudes as $longitud) {
                $valores_variedad = [];
                foreach ($variedades as $var) {
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
                        ->where('v.id_planta', $p->id_planta)
                        ->where('dee.longitud_ramo', $longitud->nombre)
                        ->whereNotNull('dee.tallos_x_ramos')
                        ->get()[0]->cantidad;
                    $mixtos = DB::table('distribucion_mixtos')
                        ->select(
                            DB::raw('sum(tallos) as cantidad')
                        )
                        ->where('fecha', opDiasFecha('-', 1, $request->fecha))
                        ->where('siglas', $var->siglas)
                        ->where('id_planta', $p->id_planta)
                        ->where('longitud_ramo', $longitud->nombre)
                        ->get()[0]->cantidad;
                    $pedidos_solidos_anterior = 0;
                    $mixtos_anterior = 0;
                    if ($request->fecha == opDiasFecha('+', 2, hoy())) {
                        $pedidos_solidos_anterior = DB::table('pedido as p')
                            ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                            ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                            ->select(
                                DB::raw('sum(dee.cantidad * dee.tallos_x_ramos * dp.cantidad) as cantidad')
                            )
                            ->where('p.estado', 1)
                            ->where('p.fecha_pedido', opDiasFecha('-', 1, $request->fecha))
                            ->where('v.siglas', '=', $var->siglas)
                            ->where('v.id_planta', $request->planta)
                            ->where('dee.longitud_ramo', $longitud->nombre)
                            ->whereNotNull('dee.tallos_x_ramos')
                            ->get()[0]->cantidad;
                        $mixtos_anterior = DB::table('distribucion_mixtos')
                            ->select(
                                DB::raw('sum(tallos) as cantidad')
                            )
                            ->where('fecha', opDiasFecha('-', 2, $request->fecha))
                            ->where('siglas', $var->siglas)
                            ->where('id_planta', $request->planta)
                            ->where('longitud_ramo', $longitud->nombre)
                            ->get()[0]->cantidad;
                    }
                    $cuarto_frio = DB::table('inventario_frio')
                        ->select(DB::raw('sum(disponibles * tallos_x_ramo) as cantidad'))
                        ->where('id_variedad', $var->id_variedad)
                        ->where('longitud_ramo', $longitud->nombre)
                        ->where('estado', 1)
                        ->where('disponibilidad', 1)
                        ->where('basura', 0);
                    if ($request->fecha == hoy())
                        $cuarto_frio = $cuarto_frio->where('fecha_ingreso', '<=', opDiasFecha('-', 1, hoy()));
                    $cuarto_frio = $cuarto_frio->get()[0]->cantidad;
                    $cosecha = DB::table('desglose_recepcion as dr')
                        ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                        ->select(DB::raw('sum(cantidad_mallas * tallos_x_malla) as cantidad'))
                        ->where('r.fecha_ingreso', $request->fecha)
                        ->where('dr.id_variedad', $var->id_variedad)
                        ->where('dr.longitud_ramo', $longitud->nombre)
                        ->get()[0]->cantidad;
                    $sobrante = DB::table('sobrante_recepcion as sr')
                        ->select(DB::raw('sum(cantidad) as cantidad'))
                        ->where('sr.fecha', opDiasFecha('-', 1, $request->fecha))
                        ->where('sr.id_variedad', $var->id_variedad)
                        ->where('sr.longitud', $longitud->nombre)
                        ->get()[0]->cantidad;
                    if ($request->fecha == opDiasFecha('+', 2, hoy())) {
                        $cuarto_frio -= ($pedidos_solidos_anterior + $mixtos_anterior);
                    }
                    $saldo = $cuarto_frio + $cosecha - ($pedidos_solidos + $mixtos);
                    $corte_objetivo = '';
                    if ($saldo < 0) {
                        $cortes = DB::table('proy_variedad_cortes as p')
                            ->join('proy_cortes as c', 'c.id_proy_cortes', '=', 'p.id_cortes')
                            ->select('c.nombre', 'p.cantidad')
                            ->where('p.id_variedad', $var->id_variedad)
                            ->where('p.fecha', opDiasFecha('-', 1, $request->fecha))
                            ->orderBy('c.nombre')
                            ->get();
                        $meta = $saldo;
                        foreach ($cortes as $cort) {
                            $meta += $cort->cantidad;
                            if ($meta >= 0) {
                                $corte_objetivo = $cort->nombre;
                                break;
                            }
                        }
                    }
                    array_push($valores_variedad, [
                        'var' => $var,
                        'pedidos_solidos' => $pedidos_solidos,
                        'pedidos_solidos_anterior' => $pedidos_solidos_anterior,
                        'pedidos_mixtos' => $mixtos,
                        'pedidos_mixtos_anterior' => $mixtos_anterior,
                        'cuarto_frio' => $cuarto_frio,
                        'cosecha' => $cosecha,
                        'sobrante' => $sobrante,
                        'saldo' => $saldo,
                        'corte_objetivo' => $corte_objetivo,
                    ]);
                }
                if (count($valores_variedad) > 0)
                    $valores_longitudes[] = [
                        'longitud' => $longitud,
                        'valores_variedad' => $valores_variedad,
                    ];
            }
            if (count($valores_longitudes) > 0)
                $listado[] = [
                    'planta' => $p,
                    'valores_longitudes' => $valores_longitudes,
                ];
        }

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Cuadre de Flor');

        $row = 0;
        foreach ($listado as $pos => $item) {
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['planta']->nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'SOLIDOS');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'MIXTOS');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'CUARTO FRIO');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'SOBRANTE');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'COSECHA');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'SALDO');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'CORTE');

            setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
            setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

            foreach ($item['valores_longitudes'] as $long) {
                $total_pedidos_solidos_long = 0;
                $total_armados_mixtos_long = 0;
                $total_inventario_long = 0;
                $total_sobrante_long = 0;
                $total_cosecha_long = 0;
                foreach ($long['valores_variedad'] as $var) {
                    $solidos = $var['pedidos_solidos'] != null ? $var['pedidos_solidos'] : 0;
                    $mixtos = $var['pedidos_mixtos'];
                    $total_pedidos_solidos_long += $solidos;
                    $total_armados_mixtos_long += $mixtos;
                    $total_inventario_long += $var['cuarto_frio'];
                    $total_sobrante_long += $var['sobrante'];
                    $total_cosecha_long += $var['cosecha'];
                    $row++;
                    $col = 0;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $var['var']->nombre . ' ' . $long['longitud']->nombre . 'cm');
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $solidos);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $mixtos);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $solidos + $mixtos);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $var['cuarto_frio'] != '' ? $var['cuarto_frio'] : '');
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $var['sobrante']);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $var['cosecha']);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $var['saldo']);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, count(explode('-', $var['corte_objetivo'])) > 1 ? explode('-', $var['corte_objetivo'])[1] : '');
                }
                $row++;
                $col = 0;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES ' . $long['longitud']->nombre . 'cm');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_pedidos_solidos_long);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_armados_mixtos_long);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_pedidos_solidos_long + $total_armados_mixtos_long);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_inventario_long);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_sobrante_long);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_cosecha_long);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_inventario_long + $total_cosecha_long - ($total_pedidos_solidos_long + $total_armados_mixtos_long));
                $col++;

                setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '5a7177');
                setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');
                $row++;
            }
        }

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
