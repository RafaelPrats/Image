<?php

namespace yura\Http\Controllers\Proyecciones;

use Carbon\Carbon;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\ResumenSemanaCosecha;
use yura\Modelos\Semana;
use yura\Modelos\Submenu;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class proyResumenTotalController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.proyecciones.resumen_total.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'desde' => getSemanaByDate(hoy()),
            'hasta' => getSemanaByDate(opDiasFecha('+', 98, hoy()))
        ]);
    }

    public function listarProyecionResumenTotal(Request $request)
    {
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('estado', 1)
            ->where('codigo', '>=', $request->desde)
            ->where('codigo', '<=', $request->hasta)
            ->orderBy('codigo')
            ->get();
        $desde = $semanas[0];
        $hasta = $semanas[count($semanas) - 1];

        $variedad_activa = DB::table('ciclo')
            ->select('id_variedad')->distinct()
            ->where('estado', '=', 1)
            ->where('fecha_fin', '>=', $desde->fecha_inicial)
            ->orderBy('activo', 'desc')
            ->orderBy('fecha_inicio')
            ->get();

        $variedad_inactiva = DB::table('proyeccion_modulo')
            ->select('id_variedad')->distinct()
            ->where('estado', '=', 1)
            ->where('fecha_inicio', '>=', $desde->fecha_inicial)
            ->orderBy('fecha_inicio', 'asc')
            ->get();

        $ids_variedad = [];
        foreach ($variedad_activa as $v)
            array_push($ids_variedad, $v->id_variedad);
        foreach ($variedad_inactiva as $v)
            if (!in_array($v->id_variedad, $ids_variedad))
                array_push($ids_variedad, $v->id_variedad);

        $variedades = DB::table('variedad')
            ->select('id_variedad', 'nombre')
            ->where('estado', 1)
            ->whereIn('id_variedad', $ids_variedad)
            ->get();

        $data = [];
        foreach ($variedades as $pos => $var) {
            $query_modulos = DB::table('ciclo')
                ->select('id_modulo')->distinct()
                ->where('estado', '=', 1)
                ->where('id_variedad', '=', $var->id_variedad)
                ->where('fecha_fin', '>=', $desde->fecha_inicial)
                ->orderBy('activo', 'desc')
                ->orderBy('fecha_inicio')
                ->get();
            $ids_modulos = [];
            foreach ($query_modulos as $item)
                array_push($ids_modulos, $item->id_modulo);
            $modulos_inactivos = DB::table('proyeccion_modulo')
                ->select('id_modulo')->distinct()
                ->where('estado', '=', 1)
                ->where('id_variedad', '=', $var->id_variedad)
                ->where('fecha_inicio', '>=', $desde->fecha_inicial)
                ->whereNotIn('id_modulo', $ids_modulos)
                ->orderBy('fecha_inicio', 'asc')
                ->get();
            $query_modulos = $query_modulos->merge($modulos_inactivos);
            $ids_modulos = [];
            foreach ($query_modulos as $item)
                array_push($ids_modulos, $item->id_modulo);

            $valores = [];
            foreach ($semanas as $sem) {
                $proy = DB::table('proyeccion_modulo_semana')
                    ->select(DB::raw('sum(proyectados) as proyectados'))
                    ->where('id_variedad', $var->id_variedad)
                    ->where('semana', $sem->codigo)
                    ->whereIn('id_modulo', $ids_modulos)
                    ->get()[0]->proyectados;
                $area = DB::table('proyeccion_modulo_semana')
                    ->select(DB::raw('sum(area) as area'))
                    ->where('id_variedad', $var->id_variedad)
                    ->where('semana', $sem->codigo)
                    ->whereIn('id_modulo', $ids_modulos)
                    ->whereIn('info', ['S', 'P'])
                    ->get()[0]->area;

                array_push($valores, [
                    'proy' => $proy,
                    'area' => $area,
                ]);
            }
            array_push($data, [
                'variedad' => $var,
                'valores' => $valores,
            ]);
        }

        return view('adminlte.gestion.proyecciones.resumen_total.partials.listado', [
            'semanas' => $semanas,
            'data' => $data,
        ]);
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);
        $spread->getProperties()
            ->setTitle('Resumen_Proyeccion');

        $fileName = "Resumen_Proyeccion.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_reporte($spread, $request)
    {
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('estado', 1)
            ->where('codigo', '>=', $request->desde)
            ->where('codigo', '<=', $request->hasta)
            ->orderBy('codigo')
            ->get();
        $desde = $semanas[0];
        $hasta = $semanas[count($semanas) - 1];

        $variedad_activa = DB::table('ciclo')
            ->select('id_variedad')->distinct()
            ->where('estado', '=', 1)
            ->where('fecha_fin', '>=', $desde->fecha_inicial)
            ->orderBy('activo', 'desc')
            ->orderBy('fecha_inicio')
            ->get();

        $variedad_inactiva = DB::table('proyeccion_modulo')
            ->select('id_variedad')->distinct()
            ->where('estado', '=', 1)
            ->where('fecha_inicio', '>=', $desde->fecha_inicial)
            ->orderBy('fecha_inicio', 'asc')
            ->get();

        $ids_variedad = [];
        foreach ($variedad_activa as $v)
            array_push($ids_variedad, $v->id_variedad);
        foreach ($variedad_inactiva as $v)
            if (!in_array($v->id_variedad, $ids_variedad))
                array_push($ids_variedad, $v->id_variedad);

        $variedades = DB::table('variedad')
            ->select('id_variedad', 'nombre')
            ->where('estado', 1)
            ->whereIn('id_variedad', $ids_variedad)
            ->get();

        $data = [];
        foreach ($variedades as $pos => $var) {
            $query_modulos = DB::table('ciclo')
                ->select('id_modulo')->distinct()
                ->where('estado', '=', 1)
                ->where('id_variedad', '=', $var->id_variedad)
                ->where('fecha_fin', '>=', $desde->fecha_inicial)
                ->orderBy('activo', 'desc')
                ->orderBy('fecha_inicio')
                ->get();
            $ids_modulos = [];
            foreach ($query_modulos as $item)
                array_push($ids_modulos, $item->id_modulo);
            $modulos_inactivos = DB::table('proyeccion_modulo')
                ->select('id_modulo')->distinct()
                ->where('estado', '=', 1)
                ->where('id_variedad', '=', $var->id_variedad)
                ->where('fecha_inicio', '>=', $desde->fecha_inicial)
                ->whereNotIn('id_modulo', $ids_modulos)
                ->orderBy('fecha_inicio', 'asc')
                ->get();
            $query_modulos = $query_modulos->merge($modulos_inactivos);
            $ids_modulos = [];
            foreach ($query_modulos as $item)
                array_push($ids_modulos, $item->id_modulo);

            $valores = [];
            foreach ($semanas as $sem) {
                $proy = DB::table('proyeccion_modulo_semana')
                    ->select(DB::raw('sum(proyectados) as proyectados'))
                    ->where('id_variedad', $var->id_variedad)
                    ->where('semana', $sem->codigo)
                    ->whereIn('id_modulo', $ids_modulos)
                    ->get()[0]->proyectados;
                $area = DB::table('proyeccion_modulo_semana')
                    ->select(DB::raw('sum(area) as area'))
                    ->where('id_variedad', $var->id_variedad)
                    ->where('semana', $sem->codigo)
                    ->whereIn('id_modulo', $ids_modulos)
                    ->whereIn('info', ['S', 'P'])
                    ->get()[0]->area;

                array_push($valores, [
                    'proy' => $proy,
                    'area' => $area,
                ]);
            }
            array_push($data, [
                'variedad' => $var,
                'valores' => $valores,
            ]);
        }

        /* -------------------- CREAR HOJA EXCEL -------------------- */
        $objSheet = $spread->getActiveSheet()->setTitle('Resumen_Proyeccion');
        $columnas = getColumnasExcel();
        setValueToCeldaExcel($objSheet, 'A1', 'SEMANAS');
        $totales = [];
        foreach ($semanas as $col => $sem) {
            setValueToCeldaExcel($objSheet, $columnas[$col + 1] . '1', $sem->codigo);
            $totales[] = [
                'proy' => 0,
                'area' => 0,
            ];
        }
        setBgToCeldaExcel($objSheet, 'A1:' . $columnas[$col + 1] . '1', '00b388');   // verde
        setColorTextToCeldaExcel($objSheet, 'A1:' . $columnas[$col + 1] . '2', 'FFFFFF');   // blanco
        $row = 1;
        foreach ($data as $d) {
            $row++;
            $objSheet->mergeCells('A' . $row . ':' . $columnas[$col + 1] . $row);
            setValueToCeldaExcel($objSheet, 'A' . $row, $d['variedad']->nombre);
            setBgToCeldaExcel($objSheet, 'A' . $row, '5a7177');   // dark
            setColorTextToCeldaExcel($objSheet, 'A' . $row, 'FFFFFF');   // blanco
            $row++;
            setValueToCeldaExcel($objSheet, 'A' . $row, 'Proyectados');
            setBgToCeldaExcel($objSheet, 'A' . $row, 'e9ecef');   // gris
            foreach ($d['valores'] as $pos => $item) {
                setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($item['proy'], 2));
                $totales[$pos]['proy'] += $item['proy'];
            }
            $row++;
            setValueToCeldaExcel($objSheet, 'A' . $row, 'Área m2');
            setBgToCeldaExcel($objSheet, 'A' . $row, 'e9ecef');   // gris
            foreach ($d['valores'] as $pos => $item) {
                setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($item['area'], 2));
                $totales[$pos]['area'] += $item['area'];
            }
        }
        $row++;
        $objSheet->mergeCells('A' . $row . ':' . $columnas[$col + 1] . $row);
        setValueToCeldaExcel($objSheet, 'A' . $row, 'TOTALES');
        setBgToCeldaExcel($objSheet, 'A' . $row, '5a7177');   // dark
        setColorTextToCeldaExcel($objSheet, 'A' . $row, 'FFFFFF');   // blanco
        $row++;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'Proyectados');
        setBgToCeldaExcel($objSheet, 'A' . $row, '00b388');   // verde
        setColorTextToCeldaExcel($objSheet, 'A' . $row, 'FFFFFF');   // blanco
        foreach ($totales as $pos => $item) {
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($item['proy'], 2));
            setBgToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, 'e9ecef');   // gris
        }
        $row++;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'Área m2');
        setBgToCeldaExcel($objSheet, 'A' . $row, '00b388');   // verde
        setColorTextToCeldaExcel($objSheet, 'A' . $row, 'FFFFFF');   // blanco
        foreach ($totales as $pos => $item) {
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($item['area'], 2));
            setBgToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, 'e9ecef');   // gris
        }

        setBorderToCeldaExcel($objSheet, 'A1:' . $columnas[$col + 1] . $row);
        for ($i = 0; $i <= $col + 1; $i++)
            $objSheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}