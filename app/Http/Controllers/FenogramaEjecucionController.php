<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Modelos\Aplicacion;
use yura\Modelos\AplicacionMatriz;
use yura\Modelos\Ciclo;
use yura\Modelos\Submenu;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Color;
use PHPExcel_Style_Fill;
use PHPExcel_Worksheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FenogramaEjecucionController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.crm.fenograma_ejecucion.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function filtrar_ciclos(Request $request)
    {
        $app_matriz_giberelico = AplicacionMatriz::All()->where('nombre', 'ACIDO GIBERELICO')->first();
        $app_gib_siembra = Aplicacion::All()
            ->where('id_aplicacion_matriz', $app_matriz_giberelico->id_aplicacion_matriz)
            ->where('poda_siembra', 'S')
            ->where('estado', 1)
            ->first();
        $app_gib_poda = Aplicacion::All()
            ->where('id_aplicacion_matriz', $app_matriz_giberelico->id_aplicacion_matriz)
            ->where('poda_siembra', 'P')
            ->where('estado', 1)
            ->first();

        $ciclos = Ciclo::where('estado', 1)
            ->where('fecha_inicio', '<=', $request->fecha)
            ->where('fecha_fin', '>=', $request->fecha);
        if ($request->ps != 'T')
            $ciclos = $ciclos->where('poda_siembra', $request->ps);
        if ($request->estado != 'T')
            $ciclos = $ciclos->where('activo', $request->estado);
        if ($request->variedad != 'T')
            $ciclos = $ciclos->where('id_variedad', $request->variedad);

        $ciclos = $ciclos->orderBy('fecha_inicio')->get();

        return view('adminlte.crm.fenograma_ejecucion.partials.filtrar_ciclos', [
            'ciclos' => $ciclos,
            'app_gib_siembra' => $app_gib_siembra,
            'app_gib_poda' => $app_gib_poda,
        ]);
    }

    public function mostrar_resumen_modulo(Request $request)
    {
        $ciclo = Ciclo::find($request->ciclo);
        $fechas = DB::table('recepcion as r')
            ->join('desglose_recepcion as dr', 'dr.id_recepcion', '=', 'r.id_recepcion')
            ->select('r.fecha_ingreso')->distinct()
            ->where('r.estado', 1)
            ->where('dr.estado', 1)
            ->where('dr.id_modulo', $ciclo->id_modulo)
            ->where('r.fecha_ingreso', '>=', opDiasFecha('+', 7, $ciclo->fecha_inicio))
            ->where('r.fecha_ingreso', '<=', $ciclo->fecha_fin)
            ->get();
        $array_fechas = [];
        foreach ($fechas as $f)
            if (!in_array(substr($f->fecha_ingreso, 0, 10), $array_fechas))
                array_push($array_fechas, substr($f->fecha_ingreso, 0, 10));
        $monitoreos = DB::table('monitoreo_calibre as mc')
            ->join('clasificacion_unitaria as u', 'u.id_clasificacion_unitaria', '=', 'mc.id_clasificacion_unitaria')
            ->join('unidad_medida as um', 'um.id_unidad_medida', '=', 'u.id_unidad_medida')
            ->select('mc.id_clasificacion_unitaria', 'u.nombre as unitaria_nombre', 'um.siglas as um_siglas',
                'u.color')->distinct()
            ->where('mc.id_ciclo', $ciclo->id_ciclo)
            ->where('mc.ramos', '>', 0)
            ->groupBy('mc.id_clasificacion_unitaria', 'u.nombre', 'um.siglas', 'u.color')
            ->orderBy('u.nombre')
            ->get();
        $data = [];
        foreach ($array_fechas as $f) {
            $tallos_cosechados = DB::table('recepcion as r')
                ->join('desglose_recepcion as dr', 'dr.id_recepcion', '=', 'r.id_recepcion')
                ->select(DB::raw('sum(cantidad_mallas * tallos_x_malla) as cant'))
                ->where('r.estado', 1)
                ->where('dr.estado', 1)
                ->where('dr.id_modulo', $ciclo->id_modulo)
                ->where('r.fecha_ingreso', 'like', $f . '%')
                ->get()[0]->cant;
            $tallos_monitoreo = DB::table('monitoreo_calibre')
                ->select(DB::raw('sum(ramos * tallos_x_ramo) as cant'))
                ->where('id_ciclo', $ciclo->id_ciclo)
                ->where('fecha', $f)
                ->get()[0]->cant;

            array_push($data, [
                'fecha' => $f,
                'tallos_cosechados' => $tallos_cosechados,
                'tallos_monitoreo' => $tallos_monitoreo,
            ]);
        }
        return view('adminlte.crm.fenograma_ejecucion.partials._mostrar_resumen_modulo', [
            'data' => $data,
            'monitoreos' => $monitoreos,
            'ciclo' => $ciclo,
        ]);
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);
        $spread->getProperties()
            ->setCreator("Nestor Tapia")
            ->setLastModifiedBy('BaulPHP')
            ->setTitle('Excel creado con PhpSpreadSheet')
            ->setSubject('Excel de prueba')
            ->setDescription('Excel generado como demostración')
            ->setKeywords('PHPSpreadsheet')
            ->setCategory('Categoría Excel');

        $fileName = "Descarga_excel.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_reporte($spread, $request)
    {
        $ciclos = Ciclo::where('estado', 1)
            ->where('fecha_inicio', '<=', $request->fecha)
            ->where('fecha_fin', '>=', $request->fecha);
        if ($request->ps != 'T')
            $ciclos = $ciclos->where('poda_siembra', $request->ps);
        if ($request->estado != 'T')
            $ciclos = $ciclos->where('activo', $request->estado);
        if ($request->variedad != 'T')
            $ciclos = $ciclos->where('id_variedad', $request->variedad);

        $ciclos = $ciclos->orderBy('fecha_inicio')->get();

        if (count($ciclos) > 0) {
            $sheet = $spread->getActiveSheet();
            //$sheet->setCellValue('A1', 'En desarrollo!');

            $sheet->mergeCells('A1:R1');
            $sheet->getStyle('A1:R1')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A1:R1')->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('CCFFCC');

            $sheet->getCell('A1')->setValue('Reporte Fenograma de Ejecucion');

            $sheet->getCell('A2')->setValue('MÓDULO');
            $sheet->getCell('B2')->setValue('INICIO');
            $sheet->getCell('C2')->setValue('SEMANA');
            $sheet->getCell('D2')->setValue('P/S');
            $sheet->getCell('E2')->setValue('DÍAS');
            $sheet->getCell('F2')->setValue('ÁREA m2');
            $sheet->getCell('G2')->setValue('TOTAL x SEMANA m2');
            $sheet->getCell('H2')->setValue('1ra FLOR');
            $sheet->getCell('I2')->setValue('%M');
            $sheet->getCell('J2')->setValue('CALIBRE');
            $sheet->getCell('K2')->setValue('TALLOS COSECHADOS');
            $sheet->getCell('L2')->setValue('REAL TALLOS/m2');
            $sheet->getCell('M2')->setValue('COSECHADO %');
            $sheet->getCell('N2')->setValue('PROY TALLOS/m2');
            $sheet->getCell('O2')->setValue('Ptas INICIALES');
            $sheet->getCell('P2')->setValue('Ptas ACTUALES');
            $sheet->getCell('Q2')->setValue('DEND. P.INI/m2');
            $sheet->getCell('R2')->setValue('CONTEO T/P');

            $sheet->getStyle('A2:R2')->getFont()->setBold(true)->setSize(12);

            $sheet->getStyle('A2:R2')
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)
                ->getColor()
                ->setRGB('FFFFFF');

            $sheet->getStyle('A2:R2')
                ->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('00B388');

            $sheet->getStyle('A2:R2')
                ->getFont()
                ->getColor()
                ->setRGB('FFFFFF');

            //--------------------------- LLENAR LA TABLA ---------------------------------------------
            $total_area = 0;
            $ciclo = 0;
            $total_tallos = 0;
            $total_tallos_m2 = 0;
            $positivos_tallos_m2 = 0;
            $total_iniciales = 0;
            $total_actuales = 0;
            $total_mortalidad = [
                'valor' => 0,
                'positivos' => 0,
            ];
            $total_densidad = [
                'valor' => 0,
                'positivos' => 0,
            ];
            $total_tallos_m2_proy = [
                'valor' => 0,
                'positivos' => 0,
            ];

            $codigo_semana = $ciclos[0]->semana()->codigo;
            $area = 0;
            foreach ($ciclos as $pos_item => $item) {
                $semana = $item->semana();
                $poda_siembra = $item->modulo->getPodaSiembraByCiclo($item->id_ciclo);
                $tallos_cosechados = $item->getTallosCosechados();

                $desecho = $item->desecho > 0 ? $item->desecho : $semana->desecho;
                $desecho = $desecho > 0 ? $desecho : 20;

                $conteo = $item->conteo;
                if ($item->conteo <= 0)
                    if ($poda_siembra > 0)
                        $conteo = $semana->tallos_planta_poda;
                    else
                        $conteo = $semana->tallos_planta_siembra;

                $tallos_m2_cos = round($tallos_cosechados / $item->area, 2);
                $tallos_m2_proy = round((($item->plantas_actuales() * $conteo) * ((100 - $desecho) / 100)) / $item->area, 2);

                $sheet->getStyle('A' . ($pos_item + 3) . ':R' . ($pos_item + 3))
                    ->getFill()
                    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB($pos_item % 2 == 0 ? '72FFE0' : 'FFFFFF');

                $sheet->getStyle('A' . ($pos_item + 3) . ':R' . ($pos_item + 3))
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)
                    ->getColor()
                    ->setRGB('9D9D9D');

                $sheet->getCell('A' . ($pos_item + 3))->setValue($item->modulo->nombre);
                $sheet->getCell('B' . ($pos_item + 3))->setValue($item->fecha_inicio);
                $sheet->getCell('C' . ($pos_item + 3))->setValue($semana->codigo);
                $sheet->getCell('D' . ($pos_item + 3))->setValue($poda_siembra);
                if ($item->fecha_fin != '')
                    $sheet->getCell('E' . ($pos_item + 3))->setValue(difFechas($item->fecha_fin, $item->fecha_inicio)->days);
                else
                    $sheet->getCell('E' . ($pos_item + 3))->setValue(difFechas(date('Y-m-d'), $item->fecha_inicio)->days);
                $sheet->getCell('F' . ($pos_item + 3))->setValue(round($item->area, 2));
                if ($codigo_semana == $semana->codigo) {
                    $area += $item->area;
                } else {
                    $area = $item->area;
                    $codigo_semana = $semana->codigo;
                }

                if ($pos_item + 1 < count($ciclos)) {
                    if ($ciclos[$pos_item + 1]->semana()->codigo != $codigo_semana) {
                        $sheet->getCell('G' . ($pos_item + 3))->setValue(round($area, 2));
                    }
                } else {
                    $sheet->getCell('G' . ($pos_item + 3))->setValue(round($area, 2));
                }

                $sheet->getCell('H' . ($pos_item + 3))->setValue($item->fecha_cosecha != '' ? difFechas($item->fecha_cosecha, $item->fecha_inicio)->days : '');
                $mortalidad = $item->getMortalidad();

                $color = 'EF6E11';
                if ($mortalidad < 10)
                    $color = '00B388';
                if ($mortalidad > 20)
                    $color = 'D01C62';
                $sheet->getCell('I' . ($pos_item + 3))->setValue($mortalidad);
                $sheet->getStyle('I' . ($pos_item + 3))
                    ->getFont()
                    ->getColor()
                    ->setRGB($color);
                $sheet->getCell('J' . ($pos_item + 3))->setValue($item->getCalibreAcumulado());
                $sheet->getCell('K' . ($pos_item + 3))->setValue($tallos_cosechados);
                $sheet->getCell('L' . ($pos_item + 3))->setValue($tallos_m2_cos);
                $sheet->getCell('M' . ($pos_item + 3))->setValue($tallos_m2_proy > 0 ? round(($tallos_m2_cos / $tallos_m2_proy) * 100, 2) : 0 . '%');
                $color = 'EF6E11';
                if ($tallos_m2_proy < 35)
                    $color = 'D01C62';
                if ($tallos_m2_proy > 45)
                    $color = '00B388';
                $sheet->getCell('N' . ($pos_item + 3))->setValue($tallos_m2_proy);
                $sheet->getStyle('N' . ($pos_item + 3))
                    ->getFont()
                    ->getColor()
                    ->setRGB($color);
                $sheet->getCell('O' . ($pos_item + 3))->setValue($item->plantas_iniciales);
                $sheet->getCell('P' . ($pos_item + 3))->setValue($item->plantas_actuales());
                $sheet->getCell('Q' . ($pos_item + 3))->setValue($item->getDensidadIniciales());
                $sheet->getCell('R' . ($pos_item + 3))->setValue($conteo);

                $total_area += $item->area;
                $total_iniciales += $item->plantas_iniciales;
                $total_actuales += $item->plantas_actuales();
                if ($item->plantas_iniciales > 0 && $item->plantas_actuales() > 0) {
                    $total_mortalidad['valor'] += $item->getMortalidad();
                    $total_mortalidad['positivos']++;
                }
                if ($item->plantas_iniciales > 0 && $item->area > 0) {
                    $total_densidad['valor'] += $item->getDensidadIniciales();
                    $total_densidad['positivos']++;
                }
                if ($item->area > 0 && $tallos_m2_proy > 0) {
                    $total_tallos_m2_proy['valor'] += $tallos_m2_proy;
                    $total_tallos_m2_proy['positivos']++;
                }
                $ciclo += $item->fecha_fin != '' ? difFechas($item->fecha_fin, $item->fecha_inicio)->days : difFechas(date('Y-m-d'), $item->fecha_inicio)->days;
                $total_tallos += $tallos_cosechados;
                $total_tallos_m2 += $tallos_m2_cos;
                if ($tallos_cosechados > 0) {
                    $positivos_tallos_m2++;
                }
            }
            $sheet->getCell('A' . ($pos_item + 4))->setValue('TOTALES');
            $sheet->mergeCells('A' . ($pos_item + 4) . ':D' . ($pos_item + 4));
            $sheet->getCell('E' . ($pos_item + 4))->setValue(count($ciclos) > 0 ? round($ciclo / count($ciclos), 2) : 0);
            $sheet->getCell('F' . ($pos_item + 4))->setValue(round($total_area / 10000, 2));
            $sheet->mergeCells('G' . ($pos_item + 4) . ':H' . ($pos_item + 4));
            $sheet->getCell('I' . ($pos_item + 4))->setValue($total_mortalidad['positivos'] > 0 ? round($total_mortalidad['valor'] / $total_mortalidad['positivos'], 2) : 0);
            $sheet->getCell('K' . ($pos_item + 4))->setValue($total_tallos);
            if ($positivos_tallos_m2 > 0)
                $real_tallos_total = count($ciclos) > 0 ? round($total_tallos_m2 / $positivos_tallos_m2, 2) : 0;
            else
                $real_tallos_total = 0;
            $sheet->getCell('L' . ($pos_item + 4))->setValue($real_tallos_total);
            $sheet->getCell('N' . ($pos_item + 4))->setValue($total_tallos_m2_proy['positivos'] > 0 ? round($total_tallos_m2_proy['valor'] / $total_tallos_m2_proy['positivos'], 2) : 0);
            $sheet->getCell('O' . ($pos_item + 4))->setValue($total_iniciales);
            $sheet->getCell('P' . ($pos_item + 4))->setValue($total_actuales);
            $sheet->getCell('Q' . ($pos_item + 4))->setValue($total_densidad['positivos'] > 0 ? round($total_densidad['valor'] / $total_densidad['positivos'], 2) : 0);

            $sheet->getStyle('A' . ($pos_item + 4) . ':R' . ($pos_item + 4))
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)
                ->getColor()
                ->setRGB(PHPExcel_Style_Color::COLOR_BLACK);

            $sheet->getStyle('A' . ($pos_item + 4) . ':R' . ($pos_item + 4))
                ->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('00B388');

            $sheet->getStyle('A' . ($pos_item + 4) . ':R' . ($pos_item + 4))
                ->getFont()
                ->getColor()
                ->setRGB('FFFFFF');

            $sheet->getStyle('A1:R' . ($pos_item + 4))
                ->getAlignment()
                ->applyFromArray(
                    array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    )
                );

            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);
            $sheet->getColumnDimension('G')->setAutoSize(true);
            $sheet->getColumnDimension('H')->setAutoSize(true);
            $sheet->getColumnDimension('I')->setAutoSize(true);
            $sheet->getColumnDimension('J')->setAutoSize(true);
            $sheet->getColumnDimension('K')->setAutoSize(true);
            $sheet->getColumnDimension('L')->setAutoSize(true);
            $sheet->getColumnDimension('M')->setAutoSize(true);
            $sheet->getColumnDimension('N')->setAutoSize(true);
            $sheet->getColumnDimension('O')->setAutoSize(true);
            $sheet->getColumnDimension('P')->setAutoSize(true);
            $sheet->getColumnDimension('Q')->setAutoSize(true);
            $sheet->getColumnDimension('R')->setAutoSize(true);
        } else {
            dd('No se han encontrado coincidencias');
        }
    }
}