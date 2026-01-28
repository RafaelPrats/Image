<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use yura\Modelos\InventarioFrio;
use yura\Modelos\Submenu;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DesechosCuartoFrioController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.postcocecha.desechos_frio.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $listado = InventarioFrio::join('variedad as v', 'v.id_variedad', '=', 'inventario_frio.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('inventario_frio.*')->distinct()
            ->where('inventario_frio.fecha_ingreso', '>=', $request->desde)
            ->where('inventario_frio.fecha_ingreso', '<=', $request->hasta)
            ->where('inventario_frio.estado', 1)
            ->where('inventario_frio.basura', 1)
            ->orderBy('p.orden')
            ->orderBy('v.orden')
            ->get();
        return view('adminlte.gestion.postcocecha.desechos_frio.partials.listado', [
            'listado' => $listado
        ]);
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);

        $fileName = "Desechos Cuarto Frio.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_reporte($spread, $request)
    {
        $listado = InventarioFrio::join('variedad as v', 'v.id_variedad', '=', 'inventario_frio.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('inventario_frio.*')->distinct()
            ->where('inventario_frio.fecha_ingreso', '>=', $request->desde)
            ->where('inventario_frio.fecha_ingreso', '<=', $request->hasta)
            ->where('inventario_frio.estado', 1)
            ->where('inventario_frio.basura', 1)
            ->orderBy('p.orden')
            ->orderBy('v.orden')
            ->get();

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Desechos Cuarto Frio');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'VARIEDAD');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'COLOR');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'PRESENTACION');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'MEDIDA');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TxR');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'RAMOS');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'T. TALLOS');
        
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        $total_tallos = 0;
        $total_ramos = 0;
        foreach ($listado as $pos => $item) {
            $variedad = $item->variedad;
            $total_tallos += $item->tallos_x_ramo * $item->cantidad;
            $total_ramos += $item->cantidad;
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $variedad->planta->nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $variedad->nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->empaque_p->nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->longitud_ramo.'cm');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->tallos_x_ramo);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->cantidad);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->tallos_x_ramo * $item->cantidad);
        }
        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        $col++;
        $col++;
        $col++;
        $col++;
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_ramos);
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_tallos);

        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
