<?php

namespace yura\Http\Controllers\Campo;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Ciclo;
use yura\Modelos\CicloLuz;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteLuzController extends Controller
{
    public function inicio(Request $request)
    {
        $semana_actual = getSemanaByDate(hoy());
        return view('adminlte.gestion.campo.reporte_luz.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semana_actual' => $semana_actual,
        ]);
    }

    public function listar_reporte_luz(Request $request)
    {
        $semana = getObjSemana($request->semana);
        $ciclos = Ciclo::where('estado', 1)
            ->where('activo', 1)
            ->orderBy('id_variedad')
            ->orderBy('fecha_inicio')
            ->get();
        $entradas = [];
        $salidas = [];
        $activos = [];

        foreach ($ciclos as $c) {
            if ($semana->codigo == getSemanaByDate(hoy())->codigo)  // semana actual
                $luz = $c->getLuzBySemana($semana);
            else
                $luz = CicloLuz::where('id_ciclo', $c->id_ciclo)
                    ->where('fecha', hoy())
                    ->first();
            if ($luz != '') {
                $dias_ciclo = difFechas(hoy(), $c->fecha_inicio)->days;
                $dias_luz = 0;
                if ($luz->inicio_luz <= $dias_ciclo)
                    if (($luz->dias_proy + $luz->dias_adicional) >= $dias_ciclo - $luz->inicio_luz)
                        $dias_luz = $dias_ciclo - $luz->inicio_luz;
                    else
                        $dias_luz = ($luz->dias_proy + $luz->dias_adicional);
                $inicio_luz = opDiasFecha('+', $luz->inicio_luz, $c->fecha_inicio);
                if (getSemanaByDate($inicio_luz)->codigo == $semana->codigo)
                    array_push($entradas, $luz);
                $fin_luz = opDiasFecha('+', $luz->inicio_luz + $luz->dias_proy + $luz->dias_adicional - 1, $c->fecha_inicio);
                if ($dias_luz > 0 && getSemanaByDate($fin_luz)->codigo == $semana->codigo)
                    array_push($salidas, $luz);
                if ($dias_luz > 0 && $fin_luz >= $semana->fecha_inicial && !in_array($luz, $salidas))
                    array_push($activos, $luz);
            }
        }

        /* order by fecha_inicio */
        if (count($entradas) > 0) {
            for ($i = 0; $i < count($entradas) - 1; $i++) {
                for ($y = $i + 1; $y < count($entradas); $y++) {
                    $ciclo_i = $entradas[$i]->ciclo;
                    $inicio_luz_i = opDiasFecha('+', $entradas[$i]->inicio_luz, $ciclo_i->fecha_inicio);
                    $ciclo_y = $entradas[$y]->ciclo;
                    $inicio_luz_y = opDiasFecha('+', $entradas[$y]->inicio_luz, $ciclo_y->fecha_inicio);
                    if ($inicio_luz_i > $inicio_luz_y) {
                        $temp = $entradas[$i];
                        $entradas[$i] = $entradas[$y];
                        $entradas[$y] = $temp;
                    }
                }
            }
        }
        if (count($activos) > 0) {
            for ($i = 0; $i < count($activos) - 1; $i++) {
                for ($y = $i + 1; $y < count($activos); $y++) {
                    $ciclo_i = $activos[$i]->ciclo;
                    $fin_luz_i = opDiasFecha('+', $activos[$i]->inicio_luz + $activos[$i]->dias_proy + $activos[$i]->dias_adicional - 1, $ciclo_i->fecha_inicio);
                    $ciclo_y = $activos[$y]->ciclo;
                    $fin_luz_y = opDiasFecha('+', $activos[$y]->inicio_luz + $activos[$y]->dias_proy + $activos[$y]->dias_adicional - 1, $ciclo_y->fecha_inicio);
                    if ($fin_luz_i > $fin_luz_y) {
                        $temp = $activos[$i];
                        $activos[$i] = $activos[$y];
                        $activos[$y] = $temp;
                    }
                }
            }
        }
        if (count($salidas) > 0) {
            for ($i = 0; $i < count($salidas) - 1; $i++) {
                for ($y = $i + 1; $y < count($salidas); $y++) {
                    $ciclo_i = $salidas[$i]->ciclo;
                    $fin_luz_i = opDiasFecha('+', $salidas[$i]->inicio_luz + $salidas[$i]->dias_proy + $salidas[$i]->dias_adicional - 1, $ciclo_i->fecha_inicio);
                    $ciclo_y = $salidas[$y]->ciclo;
                    $fin_luz_y = opDiasFecha('+', $salidas[$y]->inicio_luz + $salidas[$y]->dias_proy + $salidas[$y]->dias_adicional - 1, $ciclo_y->fecha_inicio);
                    if ($fin_luz_i > $fin_luz_y) {
                        $temp = $salidas[$i];
                        $salidas[$i] = $salidas[$y];
                        $salidas[$y] = $temp;
                    }
                }
            }
        }
        return view('adminlte.gestion.campo.reporte_luz.partials.listado', [
            'entradas' => $entradas,
            'salidas' => $salidas,
            'activos' => $activos,
            'semana' => $semana,
        ]);
    }

    public function listar_row_luz(Request $request)
    {
        $luz = CicloLuz::find($request->id);
        $ciclo = $luz->ciclo;
        $modulo = $ciclo->modulo;
        $dias_ciclo = difFechas(hoy(), $ciclo->fecha_inicio)->days;
        $inicio_luz = opDiasFecha('+', $luz->inicio_luz, $ciclo->fecha_inicio);
        $fin_luz = opDiasFecha('+', $luz->inicio_luz + $luz->dias_proy + $luz->dias_adicional - 1, $ciclo->fecha_inicio);
        $dias_luz = 0;
        if ($luz->inicio_luz <= $dias_ciclo)
            if (($luz->dias_proy + $luz->dias_adicional) >= $dias_ciclo - $luz->inicio_luz)
                $dias_luz = $dias_ciclo - $luz->inicio_luz;
            else
                $dias_luz = ($luz->dias_proy + $luz->dias_adicional);
        $horas_dia = isset($luz) ? $luz->getHorasDia() : 0;
        // calcular horas luz
        $horas_luz = $dias_luz * $horas_dia;
        $costo_luz = 0;
        $costo_x_tipo = $luz->tipo_luz / 1000;
        $costo_x_lampara = $costo_x_tipo * $luz->lamparas;
        $costo_x_lampara = $costo_x_lampara * $horas_luz;
        $costo_luz = $costo_x_lampara * 0.10;
        $costo_m2 = round($costo_luz / $ciclo->area, 4) * 100;

        return [
            'ini_luz' => convertDateToText($inicio_luz),
            'sem_ini_luz' => getSemanaByDate($inicio_luz)->codigo,
            'fin_luz' => convertDateToText($fin_luz),
            'sem_fin_luz' => getSemanaByDate($fin_luz)->codigo,
            'horas_luz' => $horas_luz,
            'costo_luz' => $costo_luz,
            'costo_m2' => $costo_m2,
        ];
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);
        $spread->getProperties()
            ->setTitle('Reporte_Luz');

        $fileName = "Reporte_Luz.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_reporte($spread, $request)
    {
        $semana = getObjSemana($request->semana);
        $ciclos = Ciclo::where('estado', 1)
            ->where('activo', 1)
            ->orderBy('id_variedad')
            ->orderBy('fecha_inicio')
            ->get();
        $entradas = [];
        $salidas = [];
        $activos = [];
        foreach ($ciclos as $c) {
            $luz = $c->getLuzBySemana($semana);
            if ($luz != '') {
                $inicio_luz = opDiasFecha('+', $luz->inicio_luz, $c->fecha_inicio);
                if (getSemanaByDate($inicio_luz)->codigo == $semana->codigo)
                    array_push($entradas, $luz);
                $fin_luz = opDiasFecha('+', $luz->inicio_luz + $luz->dias_proy + $luz->dias_adicional - 1, $c->fecha_inicio);
                if (getSemanaByDate($fin_luz)->codigo == $semana->codigo)
                    array_push($salidas, $luz);
                $dias_ciclo = difFechas(hoy(), $c->fecha_inicio)->days;
                $dias_luz = 0;
                if ($luz->inicio_luz <= $dias_ciclo)
                    if (($luz->dias_proy + $luz->dias_adicional) >= $dias_ciclo - $luz->inicio_luz)
                        $dias_luz = $dias_ciclo - $luz->inicio_luz;
                    else
                        $dias_luz = ($luz->dias_proy + $luz->dias_adicional);
                if ($dias_luz > 0 && $fin_luz >= $semana->fecha_inicial && !in_array($luz, $salidas))
                    array_push($activos, $luz);
            }
        }

        /* order by fecha_inicio */
        if (count($entradas) > 0) {
            for ($i = 0; $i < count($entradas) - 1; $i++) {
                for ($y = $i + 1; $y < count($entradas); $y++) {
                    $ciclo_i = $entradas[$i]->ciclo;
                    $inicio_luz_i = opDiasFecha('+', $entradas[$i]->inicio_luz, $ciclo_i->fecha_inicio);
                    $ciclo_y = $entradas[$y]->ciclo;
                    $inicio_luz_y = opDiasFecha('+', $entradas[$y]->inicio_luz, $ciclo_y->fecha_inicio);
                    if ($inicio_luz_i > $inicio_luz_y) {
                        $temp = $entradas[$i];
                        $entradas[$i] = $entradas[$y];
                        $entradas[$y] = $temp;
                    }
                }
            }
        }
        if (count($salidas) > 0) {
            for ($i = 0; $i < count($salidas) - 1; $i++) {
                for ($y = $i + 1; $y < count($salidas); $y++) {
                    $ciclo_i = $salidas[$i]->ciclo;
                    $fin_luz_i = opDiasFecha('+', $salidas[$i]->inicio_luz + $salidas[$i]->dias_proy + $salidas[$i]->dias_adicional - 1, $ciclo_i->fecha_inicio);
                    $ciclo_y = $salidas[$y]->ciclo;
                    $fin_luz_y = opDiasFecha('+', $salidas[$y]->inicio_luz + $salidas[$y]->dias_proy + $salidas[$y]->dias_adicional - 1, $ciclo_y->fecha_inicio);
                    if ($fin_luz_i > $fin_luz_y) {
                        $temp = $salidas[$i];
                        $salidas[$i] = $salidas[$y];
                        $salidas[$y] = $temp;
                    }
                }
            }
        }

        /* -------------------- CREAR HOJA EXCEL -------------------- */
        $objSheet = $spread->getActiveSheet()->setTitle('Reporte_Luz Semana ' . $request->semana);
        /* CICLOS ENTRANTES */

        setValueToCeldaExcel($objSheet, 'A1', 'Ciclos ENTRANTES');
        setBgToCeldaExcel($objSheet, 'A1', '5a7177');  // dark
        setColorTextToCeldaExcel($objSheet, 'A1', 'FFFFFF');  // blanco
        $objSheet->mergeCells('A1:O1');
        $row = 2;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'Variedad');
        setValueToCeldaExcel($objSheet, 'B' . $row, 'Módulo');
        setValueToCeldaExcel($objSheet, 'C' . $row, 'Poda');
        setValueToCeldaExcel($objSheet, 'D' . $row, 'Fecha Poda');
        setValueToCeldaExcel($objSheet, 'E' . $row, 'Días');
        setValueToCeldaExcel($objSheet, 'F' . $row, 'Tipo Luz');
        setValueToCeldaExcel($objSheet, 'G' . $row, '# Lamp.');
        setValueToCeldaExcel($objSheet, 'H' . $row, 'Día Ini. Luz');
        setValueToCeldaExcel($objSheet, 'I' . $row, 'Ini. Luz');
        setValueToCeldaExcel($objSheet, 'J' . $row, 'Días Proy.');
        setValueToCeldaExcel($objSheet, 'K' . $row, 'Días Adic. Luz');
        setValueToCeldaExcel($objSheet, 'L' . $row, 'Fin Luz');
        setValueToCeldaExcel($objSheet, 'M' . $row, 'Sem. Fin');
        setValueToCeldaExcel($objSheet, 'N' . $row, 'Hrs. Luz');
        setValueToCeldaExcel($objSheet, 'O' . $row, 'Horario');
        setBgToCeldaExcel($objSheet, 'A' . $row . ':' . 'O' . $row, '00b388');  // verde
        setColorTextToCeldaExcel($objSheet, 'A' . $row . ':' . 'O' . $row, 'FFFFFF');  // blanco

        foreach ($entradas as $luz) {
            $row++;
            $ciclo = $luz->ciclo;
            $modulo = $ciclo->modulo;
            $dias_ciclo = difFechas(hoy(), $ciclo->fecha_inicio)->days;
            $inicio_luz = opDiasFecha('+', $luz->inicio_luz, $ciclo->fecha_inicio);
            $fin_luz = opDiasFecha('+', $luz->inicio_luz + $luz->dias_proy + $luz->dias_adicional - 1, $ciclo->fecha_inicio);
            $dias_luz = 0;
            if (isset($luz) && $luz->inicio_luz <= $dias_ciclo)
                if (($luz->dias_proy + $luz->dias_adicional) >= $dias_ciclo - $luz->inicio_luz)
                    $dias_luz = $dias_ciclo - $luz->inicio_luz;
                else
                    $dias_luz = ($luz->dias_proy + $luz->dias_adicional);
            $horas_dia = isset($luz) ? $luz->getHorasDia() : 0;
            $horas_luz = $dias_luz * $horas_dia;

            setValueToCeldaExcel($objSheet, 'A' . $row, $ciclo->variedad->siglas);
            setValueToCeldaExcel($objSheet, 'B' . $row, $modulo->nombre);
            setValueToCeldaExcel($objSheet, 'C' . $row, $modulo->getPodaSiembraByCiclo($ciclo->id_ciclo));
            setValueToCeldaExcel($objSheet, 'D' . $row, convertDateToText($ciclo->fecha_inicio));
            setValueToCeldaExcel($objSheet, 'E' . $row, $dias_ciclo);
            setValueToCeldaExcel($objSheet, 'F' . $row, $luz->tipo_luz);
            setValueToCeldaExcel($objSheet, 'G' . $row, $luz->lamparas);
            setValueToCeldaExcel($objSheet, 'H' . $row, $luz->inicio_luz);
            setValueToCeldaExcel($objSheet, 'I' . $row, convertDateToText($inicio_luz));
            setValueToCeldaExcel($objSheet, 'J' . $row, $luz->dias_proy);
            setValueToCeldaExcel($objSheet, 'K' . $row, $luz->dias_adicional);
            setValueToCeldaExcel($objSheet, 'L' . $row, convertDateToText($fin_luz));
            setValueToCeldaExcel($objSheet, 'M' . $row, getSemanaByDate($fin_luz)->codigo);
            setValueToCeldaExcel($objSheet, 'N' . $row, $horas_luz);
            setValueToCeldaExcel($objSheet, 'O' . $row, $luz->hora_ini . ' - ' . $luz->hora_fin);
        }

        /* CICLOS SALIENTES */
        $row++;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'Ciclos SALIENTES');
        setBgToCeldaExcel($objSheet, 'A' . $row, '5a7177');  // dark
        setColorTextToCeldaExcel($objSheet, 'A' . $row, 'FFFFFF');  // blanco
        $objSheet->mergeCells('A' . $row . ':O' . $row);
        $row++;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'Variedad');
        setValueToCeldaExcel($objSheet, 'B' . $row, 'Módulo');
        setValueToCeldaExcel($objSheet, 'C' . $row, 'Poda');
        setValueToCeldaExcel($objSheet, 'D' . $row, 'Fecha Poda');
        setValueToCeldaExcel($objSheet, 'E' . $row, 'Días');
        setValueToCeldaExcel($objSheet, 'F' . $row, 'Tipo Luz');
        setValueToCeldaExcel($objSheet, 'G' . $row, '# Lamp.');
        setValueToCeldaExcel($objSheet, 'H' . $row, 'Día Ini. Luz');
        setValueToCeldaExcel($objSheet, 'I' . $row, 'Ini. Luz');
        setValueToCeldaExcel($objSheet, 'J' . $row, 'Sem. Ini.');
        setValueToCeldaExcel($objSheet, 'K' . $row, 'Días Luz');
        setValueToCeldaExcel($objSheet, 'L' . $row, 'Días Proy.');
        setValueToCeldaExcel($objSheet, 'M' . $row, 'Días Adic. Luz');
        setValueToCeldaExcel($objSheet, 'N' . $row, 'Fin Luz');
        setValueToCeldaExcel($objSheet, 'O' . $row, 'Hrs. Luz');
        setValueToCeldaExcel($objSheet, 'P' . $row, 'Horario');
        setBgToCeldaExcel($objSheet, 'A' . $row . ':' . 'P' . $row, '00b388');  // verde
        setColorTextToCeldaExcel($objSheet, 'A' . $row . ':' . 'P' . $row, 'FFFFFF');  // blanco

        foreach ($salidas as $luz) {
            $row++;
            $ciclo = $luz->ciclo;
            $modulo = $ciclo->modulo;
            $dias_ciclo = difFechas(hoy(), $ciclo->fecha_inicio)->days;
            $inicio_luz = opDiasFecha('+', $luz->inicio_luz, $ciclo->fecha_inicio);
            $fin_luz = opDiasFecha('+', $luz->inicio_luz + $luz->dias_proy + $luz->dias_adicional - 1, $ciclo->fecha_inicio);
            $dias_luz = 0;
            if (isset($luz) && $luz->inicio_luz <= $dias_ciclo)
                if (($luz->dias_proy + $luz->dias_adicional) >= $dias_ciclo - $luz->inicio_luz)
                    $dias_luz = $dias_ciclo - $luz->inicio_luz;
                else
                    $dias_luz = ($luz->dias_proy + $luz->dias_adicional);
            $horas_dia = isset($luz) ? $luz->getHorasDia() : 0;
            $horas_luz = $dias_luz * $horas_dia;

            setValueToCeldaExcel($objSheet, 'A' . $row, $ciclo->variedad->siglas);
            setValueToCeldaExcel($objSheet, 'B' . $row, $modulo->nombre);
            setValueToCeldaExcel($objSheet, 'C' . $row, $modulo->getPodaSiembraByCiclo($ciclo->id_ciclo));
            setValueToCeldaExcel($objSheet, 'D' . $row, convertDateToText($ciclo->fecha_inicio));
            setValueToCeldaExcel($objSheet, 'E' . $row, $dias_ciclo);
            setValueToCeldaExcel($objSheet, 'F' . $row, $luz->tipo_luz);
            setValueToCeldaExcel($objSheet, 'G' . $row, $luz->lamparas);
            setValueToCeldaExcel($objSheet, 'H' . $row, $luz->inicio_luz);
            setValueToCeldaExcel($objSheet, 'I' . $row, convertDateToText($inicio_luz));
            setValueToCeldaExcel($objSheet, 'J' . $row, getSemanaByDate($inicio_luz)->codigo);
            setValueToCeldaExcel($objSheet, 'K' . $row, $dias_luz);
            setValueToCeldaExcel($objSheet, 'L' . $row, $luz->dias_proy);
            setValueToCeldaExcel($objSheet, 'M' . $row, $luz->dias_adicional);
            setValueToCeldaExcel($objSheet, 'N' . $row, convertDateToText($fin_luz));
            setValueToCeldaExcel($objSheet, 'O' . $row, $horas_luz);
            setValueToCeldaExcel($objSheet, 'P' . $row, $luz->hora_ini . ' - ' . $luz->hora_fin);
        }

        /* CICLOS ACTIVOS */
        $row_act = $row + 1;
        setValueToCeldaExcel($objSheet, 'A' . $row_act, 'Ciclos ACTIVOS');
        setBgToCeldaExcel($objSheet, 'A' . $row_act, '5a7177');  // dark
        setColorTextToCeldaExcel($objSheet, 'A' . $row_act, 'FFFFFF');  // blanco
        $objSheet->mergeCells('A' . $row_act . ':O' . $row_act);
        setTextCenterToCeldaExcel($objSheet, 'A' . $row_act);
        $columnas = getColumnasExcel();
        $row_act++;
        $col = 0;
        $anterior_sum_row = 0;
        foreach ($activos as $pos => $luz) {
            $ciclo = $luz->ciclo;
            $modulo = $ciclo->modulo;
            $sum_row = intval($pos / 16);
            if ($anterior_sum_row != $sum_row) {
                $col = 0;
                $anterior_sum_row = $sum_row;
            }
            setValueToCeldaExcel($objSheet, $columnas[$col] . ($row_act + $sum_row), $ciclo->variedad->siglas . ' __ ' . $modulo->nombre);
            setTextCenterToCeldaExcel($objSheet, $columnas[$col] . ($row_act + $sum_row));
            setBorderToCeldaExcel($objSheet, $columnas[$col] . ($row_act + $sum_row));
            setBgToCeldaExcel($objSheet, $columnas[$col] . ($row_act + $sum_row), 'e9ecef');    // gris
            $col++;
        }

        setTextCenterToCeldaExcel($objSheet, 'A1:P' . $row);
        setBorderToCeldaExcel($objSheet, 'A1:P' . $row);
        $objSheet->getColumnDimension('A')->setAutoSize(true);
        $objSheet->getColumnDimension('B')->setAutoSize(true);
        $objSheet->getColumnDimension('C')->setAutoSize(true);
        $objSheet->getColumnDimension('D')->setAutoSize(true);
        $objSheet->getColumnDimension('E')->setAutoSize(true);
        $objSheet->getColumnDimension('F')->setAutoSize(true);
        $objSheet->getColumnDimension('G')->setAutoSize(true);
        $objSheet->getColumnDimension('H')->setAutoSize(true);
        $objSheet->getColumnDimension('I')->setAutoSize(true);
        $objSheet->getColumnDimension('J')->setAutoSize(true);
        $objSheet->getColumnDimension('K')->setAutoSize(true);
        $objSheet->getColumnDimension('L')->setAutoSize(true);
        $objSheet->getColumnDimension('M')->setAutoSize(true);
        $objSheet->getColumnDimension('N')->setAutoSize(true);
        $objSheet->getColumnDimension('O')->setAutoSize(true);
        $objSheet->getColumnDimension('P')->setAutoSize(true);
    }
}