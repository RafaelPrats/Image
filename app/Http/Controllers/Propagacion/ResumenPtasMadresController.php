<?php

namespace yura\Http\Controllers\Propagacion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Planta;
use yura\Modelos\ResumenPropagacion;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Color;
use PHPExcel_Style_Fill;
use PHPExcel_Worksheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ResumenPtasMadresController extends Controller
{
    public function inicio(Request $request)
    {
        $semana_desde = getSemanaByDate(opDiasFecha('-', 21, date('Y-m-d')));
        $semana_hasta = getSemanaByDate(opDiasFecha('+', 42, date('Y-m-d')));
        $plantas = Planta::where('estado', 1)->where('tipo', 'N')->orderBy('nombre')->get();
        return view('adminlte.gestion.propagacion.resumen_ptas_madres.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semana_desde' => $semana_desde,
            'semana_hasta' => $semana_hasta,
            'plantas' => $plantas,
        ]);
    }

    public function listar_resumen(Request $request)
    {
        //dd($request->all());
        $semanas = DB::table('resumen_propagacion')
            ->select('semana')->distinct()
            ->where('semana', '>=', $request->desde)
            ->where('semana', '<=', $request->hasta)
            ->orderBy('semana')
            ->get();
        $variedades = DB::table('variedad as v')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('v.id_variedad as id_variedad', 'p.nombre as planta', 'v.nombre as variedad')->distinct()
            ->where('p.estado', 1)
            ->where('v.estado', 1);
        if ($request->variedad != 'T')
            $variedades = $variedades->where('v.id_variedad', $request->variedad);
        elseif ($request->planta != '')
            $variedades = $variedades->where('v.id_planta', $request->planta);
        $variedades = $variedades->orderBy('p.nombre', 'asc')->orderBy('v.nombre', 'asc')->get();

        $listado = [];
        foreach ($variedades as $var) {
            $valores = ResumenPropagacion::where('id_variedad', $var->id_variedad)
                ->where('semana', '>=', $request->desde)
                ->where('semana', '<=', $request->hasta)
                ->orderBy('semana')
                ->get();
            array_push($listado, [
                'variedad' => $var,
                'valores' => $valores,
            ]);
        }
        return view('adminlte.gestion.propagacion.resumen_ptas_madres.partials.listado_' . $request->tipo_reporte, [
            'listado' => $listado,
            'semanas' => $semanas,
        ]);
    }

    public function exportar_resumen(Request $request)
    {
        //---------------------- EXCEL --------------------------------------
        $spread = new Spreadsheet();
        $spread->getProperties()
            //->setCreator("Benchflow")
            //->setLastModifiedBy('BaulPHP')
            ->setTitle('Resumen Propagación')
            ->setSubject('Resumen Propagación');
            //->setDescription('Excel generado como demostración')
            //->setKeywords('PHPSpreadsheet')
            //->setCategory('Categoría Excel');

        $fileName = "Resumen_propagacion.xlsx";
        $writer = new Xlsx($spread);

        /*-------------------------------------------------------------------------*/
        $semanas = DB::table('resumen_propagacion')
            ->select('semana')->distinct()
            ->where('semana', '>=', $request->desde)
            ->where('semana', '<=', $request->hasta)
            ->orderBy('semana')
            ->get();
        $variedades = DB::table('variedad as v')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('v.id_variedad as id_variedad', 'p.nombre as planta', 'v.nombre as variedad')->distinct()
            ->where('p.estado', 1)
            ->where('v.estado', 1);
        if ($request->variedad != 'T')
            $variedades = $variedades->where('v.id_variedad', $request->variedad);
        elseif ($request->planta != '')
            $variedades = $variedades->where('v.id_planta', $request->planta);
        $variedades = $variedades->orderBy('p.nombre', 'asc')->orderBy('v.nombre', 'asc')->get();

        $listado = [];
        foreach ($variedades as $var) {
            $valores = ResumenPropagacion::where('id_variedad', $var->id_variedad)
                ->where('semana', '>=', $request->desde)
                ->where('semana', '<=', $request->hasta)
                ->orderBy('semana')
                ->get();
            array_push($listado, [
                'variedad' => $var,
                'valores' => $valores,
            ]);
        }
        /*-------------------------------------------------------------------------*/
        if ($request->tipo_reporte == 'enraizamiento')
            $this->excel_reporte_resumen_enraizamiento($spread, $listado, $semanas);
        if ($request->tipo_reporte == 'plantas_madres')
            $this->excel_reporte_resumen_plantas_madres($spread, $listado, $semanas);

        //--------------------------- GUARDAR EL EXCEL -----------------------
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
    }

    function excel_reporte_resumen_enraizamiento($spread, $listado, $semanas)
    {
        $objSheet = $spread->getActiveSheet();
        $columnas = getColumnasExcel();

        /* --------------- SEMANAS ------------------ */
        $objSheet->getCell('A1')->setValue('Semanas');

        $totales_plantas_sembradas = [];
        $totales_requerimientos = [];
        $totales_porcentaje_requerimientos = [];
        $totales_costo_x_planta = [];
        $col = 1;
        foreach ($semanas as $por_s => $sem) {
            $objSheet->getCell($columnas[$col] . '1')->setValue($sem->semana);

            array_push($totales_plantas_sembradas, 0);
            array_push($totales_requerimientos, 0);
            array_push($totales_porcentaje_requerimientos, [
                'cant' => 0,
                'positivos' => 0
            ]);
            array_push($totales_costo_x_planta, 0);

            $col++;
        }

        $row = 2;
        foreach ($listado as $item) {
            $objSheet->getCell('A' . $row)->setValue($item['variedad']->planta . ': ' . $item['variedad']->variedad);
            $objSheet->mergeCells('A' . $row . ':' . $columnas[$col - 1] . $row);
            $objSheet->getStyle('A' . $row)->getFont()->getColor()->setRGB('FFFFFF');   // fila 1
            $objSheet->getStyle('A' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('5a7177');
            $row++;
            $objSheet->getCell('A' . $row)->setValue('Requerimientos');
            $objSheet->getStyle('A' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('e9ecef');
            foreach ($item['valores'] as $pos => $val) {
                $objSheet->getCell($columnas[$pos + 1] . $row)->setValue($val->requerimientos);
                $totales_requerimientos[$pos] += $val->requerimientos;
            }
            $row++;
            $objSheet->getCell('A' . $row)->setValue('% Enraizamiento');
            $objSheet->getStyle('A' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('e9ecef');
            foreach ($item['valores'] as $pos => $val) {
                $objSheet->getCell($columnas[$pos + 1] . $row)->setValue($val->porcentaje_requerimiento . '%');
                $totales_porcentaje_requerimientos[$pos]['cant'] += $val->porcentaje_requerimiento;
                if ($val->porcentaje_requerimiento > 0)
                    $totales_porcentaje_requerimientos[$pos]['positivos']++;
                $totales_costo_x_planta[$pos] = $val->costo_x_planta;
            }
            $row++;
        }

        $objSheet->getCell('A' . $row)->setValue('TOTALES');
        $objSheet->mergeCells('A' . $row . ':' . $columnas[$col - 1] . $row);
        $objSheet->getStyle('A' . $row)->getFont()->getColor()->setRGB('FFFFFF');   // fila 1
        $objSheet->getStyle('A' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('5a7177');
        $row++;
        $objSheet->getCell('A' . $row)->setValue('Requerimientos');
        $objSheet->getStyle('A' . $row)->getFont()->getColor()->setRGB('FFFFFF');   // fila 1
        $objSheet->getStyle('A' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00B388');
        foreach ($totales_requerimientos as $pos => $val)
            $objSheet->getCell($columnas[$pos + 1] . $row)->setValue($val);
        $row++;
        $objSheet->getCell('A' . $row)->setValue('% Enraizamiento');
        $objSheet->getStyle('A' . $row)->getFont()->getColor()->setRGB('FFFFFF');   // fila 1
        $objSheet->getStyle('A' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00B388');
        foreach ($totales_porcentaje_requerimientos as $pos => $val)
            $objSheet->getCell($columnas[$pos + 1] . $row)->setValue(($val['positivos'] > 0 ? round($val['cant'] / $val['positivos'], 2) : 0) . '%');
        $row++;
        $objSheet->getCell('A' . $row)->setValue('Costo x planta');
        $objSheet->getStyle('A' . $row)->getFont()->getColor()->setRGB('FFFFFF');   // fila 1
        $objSheet->getStyle('A' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00B388');
        foreach ($totales_costo_x_planta as $pos => $val)
            $objSheet->getCell($columnas[$pos + 1] . $row)->setValue('¢' . round($val * 100, 3));
        $row++;

        $objSheet->getStyle('A1:' . $columnas[$col - 1] . '1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00B388');
        $objSheet->getStyle('A1:' . $columnas[$col - 1] . '1')->getFont()->getColor()->setRGB('FFFFFF');   // fila 1
        $objSheet->getStyle('A1:' . $columnas[$col - 1] . ($row - 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objSheet->getStyle('A1:' . $columnas[$col - 1] . ($row - 1))
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)
            ->getColor()
            ->setRGB(PHPExcel_Style_Color::COLOR_BLACK);

        for ($i = 0; $i <= $col; $i++)
            $objSheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    function excel_reporte_resumen_plantas_madres($spread, $listado, $semanas)
    {
        $objSheet = $spread->getActiveSheet();
        $columnas = getColumnasExcel();

        /* --------------- SEMANAS ------------------ */
        $objSheet->getCell('A1')->setValue('Semanas');

        $totales_esquejes_cosechados = [];
        $totales_plantas_sembradas = [];
        $totales_costo_x_esqueje = [];
        $col = 1;
        foreach ($semanas as $por_s => $sem) {
            $objSheet->getCell($columnas[$col] . '1')->setValue($sem->semana);

            array_push($totales_esquejes_cosechados, 0);
            array_push($totales_plantas_sembradas, 0);
            array_push($totales_costo_x_esqueje, 0);

            $col++;
        }

        $row = 2;
        foreach ($listado as $item) {
            $objSheet->getCell('A' . $row)->setValue($item['variedad']->planta . ': ' . $item['variedad']->variedad);
            $objSheet->mergeCells('A' . $row . ':' . $columnas[$col - 1] . $row);
            $objSheet->getStyle('A' . $row)->getFont()->getColor()->setRGB('FFFFFF');   // fila 1
            $objSheet->getStyle('A' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('5a7177');
            $row++;
            $objSheet->getCell('A' . $row)->setValue('Esquejes Cosechados');
            $objSheet->getStyle('A' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('e9ecef');
            foreach ($item['valores'] as $pos => $val) {
                $objSheet->getCell($columnas[$pos + 1] . $row)->setValue($val->esquejes_cosechados);
                $totales_esquejes_cosechados[$pos] += $val->esquejes_cosechados;
            }
            $row++;
            $objSheet->getCell('A' . $row)->setValue('Ptas Sembradas');
            $objSheet->getStyle('A' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('e9ecef');
            foreach ($item['valores'] as $pos => $val) {
                $objSheet->getCell($columnas[$pos + 1] . $row)->setValue($val->plantas_sembradas);
                $totales_plantas_sembradas[$pos] += $val->plantas_sembradas;
            }
            $row++;
            $objSheet->getCell('A' . $row)->setValue('Esquejes x planta');
            $objSheet->getStyle('A' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('e9ecef');
            foreach ($item['valores'] as $pos => $val) {
                $objSheet->getCell($columnas[$pos + 1] . $row)->setValue($val->esquejes_x_planta);
                $totales_costo_x_esqueje[$pos] = $val->costo_x_esqueje;
            }
            $row++;
        }

        $objSheet->getCell('A' . $row)->setValue('TOTALES');
        $objSheet->mergeCells('A' . $row . ':' . $columnas[$col - 1] . $row);
        $objSheet->getStyle('A' . $row)->getFont()->getColor()->setRGB('FFFFFF');   // fila 1
        $objSheet->getStyle('A' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('5a7177');
        $row++;
        $objSheet->getCell('A' . $row)->setValue('Esquejes Cosechados');
        $objSheet->getStyle('A' . $row)->getFont()->getColor()->setRGB('FFFFFF');   // fila 1
        $objSheet->getStyle('A' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00B388');
        foreach ($totales_esquejes_cosechados as $pos => $val)
            $objSheet->getCell($columnas[$pos + 1] . $row)->setValue($val);
        $row++;
        $objSheet->getCell('A' . $row)->setValue('Ptas Sembradas');
        $objSheet->getStyle('A' . $row)->getFont()->getColor()->setRGB('FFFFFF');   // fila 1
        $objSheet->getStyle('A' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00B388');
        foreach ($totales_plantas_sembradas as $pos => $val)
            $objSheet->getCell($columnas[$pos + 1] . $row)->setValue($val);
        $row++;
        $objSheet->getCell('A' . $row)->setValue('Esqueje x planta');
        $objSheet->getStyle('A' . $row)->getFont()->getColor()->setRGB('FFFFFF');   // fila 1
        $objSheet->getStyle('A' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00B388');
        foreach ($totales_plantas_sembradas as $pos => $val)
            $objSheet->getCell($columnas[$pos + 1] . $row)->setValue($val > 0 ? round($totales_esquejes_cosechados[$pos] / $val, 2) : 0);
        $row++;
        $objSheet->getCell('A' . $row)->setValue('Costo x esqueje');
        $objSheet->getStyle('A' . $row)->getFont()->getColor()->setRGB('FFFFFF');   // fila 1
        $objSheet->getStyle('A' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00B388');
        foreach ($totales_costo_x_esqueje as $pos => $val)
            if ($semanas[$pos]->semana >= 2138)
                $objSheet->getCell($columnas[$pos + 1] . $row)->setValue('¢' . round(5.2, 3));
            else
                $objSheet->getCell($columnas[$pos + 1] . $row)->setValue('¢' . round($val * 100, 3));
        $row++;

        $objSheet->getStyle('A1:' . $columnas[$col - 1] . '1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00B388');
        $objSheet->getStyle('A1:' . $columnas[$col - 1] . '1')->getFont()->getColor()->setRGB('FFFFFF');   // fila 1
        $objSheet->getStyle('A1:' . $columnas[$col - 1] . ($row - 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objSheet->getStyle('A1:' . $columnas[$col - 1] . ($row - 1))
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)
            ->getColor()
            ->setRGB(PHPExcel_Style_Color::COLOR_BLACK);

        for ($i = 0; $i <= $col; $i++)
            $objSheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}