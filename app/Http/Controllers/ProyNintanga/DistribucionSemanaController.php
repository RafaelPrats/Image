<?php

namespace yura\Http\Controllers\ProyNintanga;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\DiasCosechaSemana;
use yura\Modelos\DistribucionMixtosSemana;
use yura\Modelos\DistribucionVariedad;
use yura\Modelos\Planta;
use yura\Modelos\ProyLongitudes;
use yura\Modelos\ProyVariedadSemana;
use yura\Modelos\Submenu;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DistribucionSemanaController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = Planta::join('variedad as v', 'v.id_planta', '=', 'planta.id_planta')
            ->select('planta.*')->distinct()
            ->where('v.receta', 0)
            ->where('v.estado', 1)
            ->where('planta.estado', 1)
            ->orderBy('planta.nombre')
            ->get();
        $semana_actual = getSemanaByDate(opDiasFecha('+', 0, hoy()));
        return view('adminlte.gestion.proyeccion_nintanga.distribucion_semana.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
            'semana_actual' => $semana_actual,
        ]);
    }

    public function seleccionar_planta(Request $request)
    {
        return getLongitudesByPlanta($request->planta, 'option', $request->select);
    }

    public function listar_formulario(Request $request)
    {
        $variedades = DB::table('variedad')
            ->select('nombre', 'siglas')->distinct()
            ->where('id_planta', $request->planta)
            ->where('assorted', 0)
            ->where('estado', 1)
            ->orderBy('orden')
            ->get();

        $semana = getObjSemana($request->semana);
        $semana_anterior = getSemanaByDate(opDiasFecha('-', 7, $semana->fecha_inicial));

        $dias_cosecha_semana = DiasCosechaSemana::where('semana', $semana_anterior->codigo)
            ->get()
            ->first();

        if ($dias_cosecha_semana == '') {
            $dias_cosecha_semana = new DiasCosechaSemana();
            $dias_cosecha_semana->semana = $semana_anterior->codigo;
            $dias_cosecha_semana->cantidad = 5;
            $dias_cosecha_semana->save();
        }
        $longitud = ProyLongitudes::find($request->longitud);
        $tallos_mixtos = DB::table('pedido as p')
            ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
            ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->select(
                DB::raw('sum(dee.cantidad * dee.tallos_x_ramos * dp.cantidad) as cantidad')
            )
            ->where('p.estado', 1)
            ->where('p.fecha_pedido', '>=', $semana->fecha_inicial)
            ->where('p.fecha_pedido', '<=', $semana->fecha_final)
            ->where('v.assorted', 1)
            ->where('v.id_planta', $request->planta)
            ->whereNotNull('dee.tallos_x_ramos')
            ->where('dee.longitud_ramo', $longitud->nombre)
            ->get()[0]->cantidad;

        $fechas = [];
        for ($i = 0; $i < 7; $i++) {
            $fecha = opDiasFecha('+', $i, $semana->fecha_inicial);
            $mixtos = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->select(
                    DB::raw('sum(dee.cantidad * dee.tallos_x_ramos * dp.cantidad) as cantidad')
                )
                ->where('p.estado', 1)
                ->where('p.fecha_pedido', $fecha)
                ->where('v.assorted', 1)
                ->where('v.id_planta', $request->planta)
                ->where('dee.longitud_ramo', $longitud->nombre)
                ->whereNotNull('dee.tallos_x_ramos')
                ->get()[0]->cantidad;
            array_push($fechas, [
                'fecha' => $fecha,
                'mixtos' => $mixtos,
            ]);
        }

        $listado = [];
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
                ->where('p.fecha_pedido', '>=', $semana->fecha_inicial)
                ->where('p.fecha_pedido', '<=', $semana->fecha_final)
                ->where('v.siglas', '=', $var->siglas)
                ->where('v.id_planta', $request->planta)
                ->whereNotNull('dee.tallos_x_ramos')
                ->where('dee.longitud_ramo', $longitud->nombre)
                ->get()[0]->cantidad;
            $mixtos = DistribucionMixtosSemana::where('id_planta', $request->planta)
                ->where('siglas', $var->siglas)
                ->where('semana', $request->semana)
                ->where('longitud', $longitud->nombre)
                ->get()
                ->first();
            $distribucion = '';
            if ($mixtos == '') {
                $distribucion = DistribucionVariedad::where('id_planta', $request->planta)
                    ->where('siglas', $var->siglas)
                    ->where('longitud', $longitud->nombre)
                    ->get()
                    ->first();
            }
            $proy_sem = DB::table('proy_variedad_semana')
                ->select(DB::raw('sum(cantidad) as cantidad'))
                ->where('id_planta', $request->planta)
                ->where('semana', $semana->codigo)
                ->where('siglas', $var->siglas)
                ->where('id_longitudes', $request->longitud)
                ->get()[0];

            $valores = [];
            foreach ($fechas as $f) {
                $solidos = DB::table('pedido as p')
                    ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                    ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                    ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                    ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                    ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                    ->select(
                        DB::raw('sum(dee.cantidad * dee.tallos_x_ramos * dp.cantidad) as cantidad')
                    )
                    ->where('p.estado', 1)
                    ->where('p.fecha_pedido', $f['fecha'])
                    ->where('v.siglas', '=', $var->siglas)
                    ->where('v.id_planta', $request->planta)
                    ->whereNotNull('dee.tallos_x_ramos')
                    ->get()[0]->cantidad;

                array_push($valores, [
                    'solidos' => $solidos != '' ? $solidos : 0,
                ]);
            }
            array_push($listado, [
                'var' => $var,
                'pedidos_solidos' => $pedidos_solidos,
                'mixtos' => $mixtos,
                'distribucion' => $distribucion != '' ? $distribucion->valor : 0,
                'proy_sem' => $proy_sem != '' ? $proy_sem->cantidad : 0,
                'valores' => $valores,
            ]);
        }
        return view('adminlte.gestion.proyeccion_nintanga.distribucion_semana.forms.ingresos', [
            'planta' => Planta::find($request->planta),
            'semana' => $semana,
            'semana_anterior' => $semana_anterior,
            'dias_cosecha_semana' => $dias_cosecha_semana->cantidad,
            'tallos_mixtos' => $tallos_mixtos,
            'listado' => $listado,
            'variedades' => $variedades,
            'fechas' => $fechas,
        ]);
    }

    public function update_dias_cosecha_semana(Request $request)
    {
        $model = DiasCosechaSemana::where('semana', $request->semana)
            ->get()
            ->first();

        if ($request->cantidad >= 5 && $request->cantidad <= 7)
            $model->cantidad = $request->cantidad;
        else
            $model->cantidad = 7;

        $model->save();

        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>MODIFICADO</strong> la cantidad correctamente'
        ];
    }

    public function store_distribucion(Request $request)
    {
        $longitud = ProyLongitudes::find($request->longitud);
        $delete = DistribucionMixtosSemana::where('id_planta', $request->planta)
            ->where('semana', $request->semana)
            ->where('longitud', $longitud->nombre)
            ->delete();

        foreach ($request->data as $d) {
            $model = new DistribucionMixtosSemana();
            $model->id_planta = $request->planta;
            $model->semana = $request->semana;
            $model->longitud = $longitud->nombre;
            $model->siglas = $d['siglas'];
            $model->cantidad = $d['cantidad'];
            $model->save();
        }

        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>MODIFICADO</strong> la cantidad correctamente',
        ];
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);

        $fileName = "Distribución_Semanal.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_reporte($spread, $request)
    {
        $plantas = Planta::join('variedad as v', 'v.id_planta', '=', 'planta.id_planta')
            ->select('planta.*')->distinct()
            ->where('v.receta', 0)
            ->where('v.estado', 1)
            ->where('planta.estado', 1)
            ->orderBy('planta.nombre')
            ->get();
        $semana = getObjSemana($request->semana);
        $semana_anterior = getSemanaByDate(opDiasFecha('-', 7, $semana->fecha_inicial));
        $dias_cosecha_semana = DiasCosechaSemana::where('semana', $semana_anterior->codigo)->first();

        if ($dias_cosecha_semana == '') {
            $dias_cosecha_semana = new DiasCosechaSemana();
            $dias_cosecha_semana->semana = $semana_anterior->codigo;
            $dias_cosecha_semana->cantidad = 5;
            $dias_cosecha_semana->save();
        }
        $columnas = getColumnasExcel();

        foreach ($plantas as $pos_pta => $planta) {
            $longitudes = ProyLongitudes::where('id_planta', $planta->id_planta)->get();

            $variedades = DB::table('variedad')
                ->select('nombre', 'siglas')->distinct()
                ->where('id_planta', $planta->id_planta)
                ->where('assorted', 0)
                ->where('estado', 1)
                ->orderBy('orden')
                ->get();

            foreach ($longitudes as $longitud) {
                $tallos_mixtos = DB::table('pedido as p')
                    ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                    ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                    ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                    ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                    ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                    ->select(
                        DB::raw('sum(dee.cantidad * dee.tallos_x_ramos * dp.cantidad) as cantidad')
                    )
                    ->where('p.estado', 1)
                    ->where('p.fecha_pedido', '>=', $semana->fecha_inicial)
                    ->where('p.fecha_pedido', '<=', $semana->fecha_final)
                    ->where('v.assorted', 1)
                    ->where('v.id_planta', $planta->id_planta)
                    ->whereNotNull('dee.tallos_x_ramos')
                    ->where('dee.longitud_ramo', $longitud->nombre)
                    ->get()[0]->cantidad;

                $fechas = [];
                for ($i = 0; $i < 7; $i++) {
                    $fecha = opDiasFecha('+', $i, $semana->fecha_inicial);
                    $mixtos = DB::table('pedido as p')
                        ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                        ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                        ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                        ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                        ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                        ->select(
                            DB::raw('sum(dee.cantidad * dee.tallos_x_ramos * dp.cantidad) as cantidad')
                        )
                        ->where('p.estado', 1)
                        ->where('p.fecha_pedido', $fecha)
                        ->where('v.assorted', 1)
                        ->where('v.id_planta', $planta->id_planta)
                        ->where('dee.longitud_ramo', $longitud->nombre)
                        ->whereNotNull('dee.tallos_x_ramos')
                        ->get()[0]->cantidad;
                    array_push($fechas, [
                        'fecha' => $fecha,
                        'mixtos' => $mixtos,
                    ]);
                }

                $listado = [];
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
                        ->where('p.fecha_pedido', '>=', $semana->fecha_inicial)
                        ->where('p.fecha_pedido', '<=', $semana->fecha_final)
                        ->where('v.siglas', '=', $var->siglas)
                        ->where('v.id_planta', $planta->id_planta)
                        ->whereNotNull('dee.tallos_x_ramos')
                        ->where('dee.longitud_ramo', $longitud->nombre)
                        ->get()[0]->cantidad;
                    $mixtos = DistribucionMixtosSemana::where('id_planta', $planta->id_planta)
                        ->where('siglas', $var->siglas)
                        ->where('semana', $request->semana)
                        ->where('longitud', $longitud->nombre)
                        ->get()
                        ->first();
                    $distribucion = '';
                    if ($mixtos == '') {
                        $distribucion = DistribucionVariedad::where('id_planta', $planta->id_planta)
                            ->where('siglas', $var->siglas)
                            ->where('longitud', $longitud->nombre)
                            ->get()
                            ->first();
                    }
                    $proy_sem = DB::table('proy_variedad_semana')
                        ->select(DB::raw('sum(cantidad) as cantidad'))
                        ->where('id_planta', $planta->id_planta)
                        ->where('semana', $semana->codigo)
                        ->where('siglas', $var->siglas)
                        ->where('id_longitudes', $request->longitud)
                        ->get()[0];

                    $valores = [];
                    foreach ($fechas as $f) {
                        $solidos = DB::table('pedido as p')
                            ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                            ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                            ->select(
                                DB::raw('sum(dee.cantidad * dee.tallos_x_ramos * dp.cantidad) as cantidad')
                            )
                            ->where('p.estado', 1)
                            ->where('p.fecha_pedido', $f['fecha'])
                            ->where('v.siglas', '=', $var->siglas)
                            ->where('v.id_planta', $planta->id_planta)
                            ->whereNotNull('dee.tallos_x_ramos')
                            ->get()[0]->cantidad;

                        array_push($valores, [
                            'solidos' => $solidos != '' ? $solidos : 0,
                        ]);
                    }
                    array_push($listado, [
                        'var' => $var,
                        'pedidos_solidos' => $pedidos_solidos,
                        'mixtos' => $mixtos,
                        'distribucion' => $distribucion != '' ? $distribucion->valor : 0,
                        'proy_sem' => $proy_sem != '' ? $proy_sem->cantidad : 0,
                        'valores' => $valores,
                    ]);
                }

                if ($pos_pta == 0)
                    $sheet = $spread->getActiveSheet()->setTitle($planta->nombre . ' ' . $longitud->nombre . 'cm');
                else
                    $sheet = $spread->createSheet()->setTitle($planta->nombre . ' ' . $longitud->nombre . 'cm');

                $row = 1;
                $col = 0;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Dias Cosecha: ' . $dias_cosecha_semana->cantidad);
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'SOLIDOS');
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'MIXTOS (' . $tallos_mixtos . ')');
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'PROY SEM');
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));
                $col++;
                foreach ($fechas as $pos => $f) {
                    setValueToCeldaExcel($sheet, $columnas[$col + ($pos * 5)] . $row, getDiaSemanaByFecha($f['fecha']));
                    $sheet->mergeCells($columnas[$col + ($pos * 5)] . $row . ':' . $columnas[$col + (($pos + 1) * 5) - 1] . $row);
                }
                $col += (($pos + 1) * 5) - 1;
                setBgToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row, '00b388');
                setColorTextToCeldaExcel($sheet, 'A1:' . $columnas[$col] . 2, 'ffffff');
                setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

                $total_solidos = 0;
                $total_mixtos = 0;
                $total_proy = 0;
                $totales = [];

                $row++;
                $col = 3;
                foreach ($fechas as $pos => $f) {
                    $totales[] = [
                        'solidos' => 0,
                        'mixtos' => 0,
                        'proy' => 0,
                        'saldo' => 0,
                    ];
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'SOLIDOS');
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5A7177');
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'MIXTOS (' . $f['mixtos'] . ')');
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5A7177');
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTAL');
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5A7177');
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'PROY');
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5A7177');
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'SALDO');
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5A7177');
                }

                $row++;
                foreach ($listado as $item) {
                    $col = 0;

                    $mixtos = $item['mixtos'] != '' ? $item['mixtos']->cantidad : porcentaje($item['distribucion'], $tallos_mixtos, 2);
                    $total_mixtos += $mixtos;
                    $total_solidos += $item['pedidos_solidos'];
                    $total_proy += $item['proy_sem'];
                    $saldo = 0;

                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['var']->nombre);
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5A7177');
                    setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['pedidos_solidos']);
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5A7177');
                    setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, round($mixtos));
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5A7177');
                    setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['proy_sem']);
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5A7177');
                    setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                    foreach ($item['valores'] as $pos => $val) {
                        $proy = round($item['proy_sem'] / 7);
                        // es LUNES
                        if ($dias_cosecha_semana->cantidad == 5) {
                            if (date('N', strtotime($fechas[$pos]['fecha'])) == 1) {
                                $proy = $proy * 3;
                            } elseif (in_array(date('N', strtotime($fechas[$pos]['fecha'])), [6, 7])) {
                                $proy = 0;
                            }
                        } elseif ($dias_cosecha_semana->cantidad == 6) {
                            if (date('N', strtotime($fechas[$pos]['fecha'])) == 1) {
                                $proy = $proy * 2;
                            } elseif (in_array(date('N', strtotime($fechas[$pos]['fecha'])), [7])) {
                                $proy = 0;
                            }
                        }
                        $porcentaje = $tallos_mixtos >  0 ? round(($mixtos / $tallos_mixtos) * 100, 2) : 0;
                        $mixto_fecha = $fechas[$pos]['mixtos'];
                        $mixtos_f = $mixto_fecha > 0 ? round(($porcentaje * $mixto_fecha) / 100) : 0;
                        //dd($mixtos_f);
                        //$mixtos_f = porcentaje($item['distribucion'], $fechas[$pos]['mixtos'], 2);
                        $saldo += $proy - $val['solidos'] - $mixtos_f;
                        $totales[$pos]['solidos'] += $val['solidos'];
                        $totales[$pos]['mixtos'] += $mixtos_f;
                        $totales[$pos]['proy'] += $proy;

                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['solidos']);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $mixtos_f);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['solidos'] + $mixtos_f);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $proy);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $saldo);
                        setBgToCeldaExcel($sheet, $columnas[$col] . $row, 'f6f6f6');
                    }
                    $row++;
                }

                $col = 0;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
                setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_solidos);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
                setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_mixtos);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
                setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_proy);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
                setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                $saldo_total = 0;
                foreach ($totales as $pos => $val) {
                    $saldo_total += $val['proy'] - $val['solidos'] - $val['mixtos'];

                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['solidos']);
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5A7177');
                    setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['mixtos']);
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5A7177');
                    setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['solidos'] + $val['mixtos']);
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5A7177');
                    setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['proy']);
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5A7177');
                    setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $saldo_total);
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5A7177');
                    setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                }

                setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

                for ($i = 0; $i <= $col; $i++)
                    $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
            }
        }
    }
}
