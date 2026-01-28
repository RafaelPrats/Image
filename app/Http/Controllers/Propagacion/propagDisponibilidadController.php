<?php

namespace yura\Http\Controllers\Propagacion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\EnraizamientoSemanal;
use yura\Modelos\Planta;
use yura\Modelos\PropagDisponibilidad;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Color;
use PHPExcel_Style_Fill;
use PHPExcel_Worksheet;

class propagDisponibilidadController extends Controller
{
    public function inicio(Request $request)
    {
        $semana_hasta = getSemanaByDate(opDiasFecha('+', 42, date('Y-m-d')));
        $plantas = Planta::where('estado', 1)->where('tipo', 'N')->orderBy('nombre')->get();
        return view('adminlte.gestion.propagacion.disponibilidad.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semana_hasta' => $semana_hasta,
            'plantas' => $plantas,
        ]);
    }

    public function listar_disponibilidades(Request $request)
    {
        $listado = PropagDisponibilidad::where('id_variedad', $request->variedad)
            ->where('semana', '>=', $request->desde)
            ->where('semana', '<=', $request->hasta)
            ->orderBy('semana')
            ->get();
        $modulos = DB::table('ciclo as c')
            ->join('modulo as m', 'm.id_modulo', '=', 'c.id_modulo')
            ->select('c.id_modulo', 'm.nombre', 'c.poda_siembra')->distinct()
            ->where('c.activo', 1)
            ->where('c.id_variedad', $request->variedad)
            ->where('c.fecha_inicio', '>=', opDiasFecha('-', 50, date('Y-m-d')))
            ->get();
        return view('adminlte.gestion.propagacion.disponibilidad.partials.listado', [
            'listado' => $listado,
            'modulos' => $modulos,
        ]);
    }

    public function modal_cambiar_ptas_sembradas(Request $request)
    {
        $propag_disp = PropagDisponibilidad::find($request->id_disp);
        return view('adminlte.gestion.propagacion.disponibilidad.forms.modal_cambiar_ptas_sembradas', [
            'propag_disp' => $propag_disp,
            'valor' => $request->valor,
            'variedad' => Variedad::find($request->variedad),
        ]);
    }

    public function cambiar_ptas_sembradas(Request $request)
    {
        $propag_disp = PropagDisponibilidad::find($request->id_disp);
        foreach (explode('|', $propag_disp->destino_plantas_sembradas) as $item) {
            $semana_ini = explode('+', $item)[0];
            if ($semana_ini == $request->semana_ini) {
                $enr_sem = EnraizamientoSemanal::All()
                    ->where('semana_ini', $semana_ini)
                    ->where('id_variedad', $request->variedad)
                    ->first();
                $enr_sem->semana_fin = $enr_sem->semana_fin + ($request->valor);
                $enr_sem->save();
                Artisan::call('update:propag_disponibilidad', [
                    'semana_desde' => $enr_sem->semana_ini,
                    'semana_hasta' => getLastSemanaByVariedad($enr_sem->id_variedad)->codigo,
                    'variedad' => $enr_sem->id_variedad,
                ]);
            }
        }
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">Se han modificado las semanas satisfactoriamente</div>',
        ];
    }

    public function update_disponibilidad(Request $request)
    {
        $model = PropagDisponibilidad::find($request->id);
        $model->desecho = $request->desecho;
        $model->plantas_disponibles = ($model->saldo_inicial + $model->plantas_sembradas) - $model->desecho();
        $model->requerimientos_adicionales = $request->req_adicionales;
        $model->mantener_cambios = $request->mantener_cambios;

        $model->requerimientos = $request->requerimientos;

        $model->saldo = $model->plantas_disponibles - $model->calcular_requerimientos();
        if ($model->save()) {
            Artisan::call('update:propag_disponibilidad', [
                'semana_desde' => $request->semana_desde,
                'semana_hasta' => $request->semana_hasta,
                'variedad' => $request->semana_hasta,
            ]);
            $success = true;
            $msg = '<div class="alert alert-success text-center">Se ha actualizado la información satisfactoriamente</div>';
        } else {
            $success = false;
            $msg = '<div class="alert alert-danger text-center">Ha ocurrido un problema al guardar la información</div>';
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function update_semana(Request $request)
    {
        Artisan::call('update:propag_disponibilidad', [
            'semana_desde' => $request->semana,
            'semana_hasta' => $request->semana,
            'variedad' => $request->variedad,
            'obligatorio' => 1,
        ]);
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">Se ha actualizado la información satisfactoriamente</div>',
        ];
    }

    public function exportar_disponibilidades(Request $request)
    {
        //---------------------- EXCEL --------------------------------------
        $objPHPExcel = new PHPExcel;
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(12);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");

        $objPHPExcel->removeSheetByIndex(0); //Eliminar la hoja inicial por defecto

        $this->excel_reporte_disponibilidades($objPHPExcel, $request);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="Reporte_Disponibilidad.xlsx"');
        header("Content-Transfer-Encoding: binary");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        ob_start();
        $objWriter->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        $opResult = array(
            'status' => 1,
            'data' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
        );
        echo json_encode($opResult);
    }

    function excel_reporte_disponibilidades($objPHPExcel, $request)
    {
        $listado = PropagDisponibilidad::where('id_variedad', $request->variedad)
            ->where('semana', '>=', $request->desde)
            ->where('semana', '<=', $request->hasta)
            ->orderBy('semana')
            ->get();
        $objSheet = new PHPExcel_Worksheet($objPHPExcel, 'Reporte Disponibilidad');
        $objPHPExcel->addSheet($objSheet, 0);
        $columnas = getColumnasExcel();

        $objSheet->getCell('A1')->setValue('Semanas');
        $objSheet->getStyle('A1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00B388');
        /* --------------- SALDO INICIAL ------------------ */
        $objSheet->getCell('A2')->setValue('Saldo Inicial');
        $objSheet->getStyle('A2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00B388');
        $objSheet->getStyle('A2')->getFont()->getColor()->setRGB('FFFFFF');
        /* --------------- Plantas Sembradas ------------------ */
        $objSheet->getCell('A3')->setValue('Plantas Sembradas');
        $objSheet->getStyle('A3')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00B388');
        $objSheet->getStyle('A3')->getFont()->getColor()->setRGB('FFFFFF');
        /* --------------- Desecho % ------------------ */
        $objSheet->getCell('A4')->setValue('Desecho %');
        $objSheet->getStyle('A4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00B388');
        $objSheet->getStyle('A4')->getFont()->getColor()->setRGB('FFFFFF');
        /* --------------- Plantas Disponibles ------------------ */
        $objSheet->getCell('A5')->setValue('Plantas Disponibles');
        $objSheet->getStyle('A5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00B388');
        $objSheet->getStyle('A5')->getFont()->getColor()->setRGB('FFFFFF');
        /* --------------- Requerimientos ------------------ */
        $objSheet->getCell('A6')->setValue('Requerimientos');
        $objSheet->getStyle('A6')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00B388');
        $objSheet->getStyle('A6')->getFont()->getColor()->setRGB('FFFFFF');
        /* --------------- Saldo ------------------ */
        $objSheet->getCell('A7')->setValue('Saldo');
        $objSheet->getStyle('A7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00B388');
        $col = 1;
        // --------------------------- LLENAR LA TABLA (encabezado) ---------------------------------------------
        foreach ($listado as $x => $item) {
            $objSheet->getCell($columnas[$col] . 1)->setValue($item->semana);
            $objSheet->mergeCells($columnas[$col] . '1:' . $columnas[$col + 1] . '1');
            /* estilos */
            $objSheet->getStyle($columnas[$col] . 1)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('5A7177');

            /* --------------- SALDO INICIAL ------------------ */
            $objSheet->getCell($columnas[$col] . 2)->setValue(number_format($item->saldo_inicial));
            $objSheet->mergeCells($columnas[$col] . '2:' . $columnas[$col + 1] . '2');
            /* estilos */
            $objSheet->getStyle($columnas[$col] . 2)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f7f8f8');

            /* --------------- Plantas Sembradas ------------------ */
            $objSheet->getCell($columnas[$col] . 3)->setValue(number_format($item->plantas_sembradas));
            $objSheet->mergeCells($columnas[$col] . '3:' . $columnas[$col + 1] . '3');
            /* estilos */
            $objSheet->getStyle($columnas[$col] . 3)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f7f8f8');

            /* --------------- Desecho % ------------------ */
            $objSheet->getCell($columnas[$col] . 4)->setValue($item->desecho());
            $objSheet->mergeCells($columnas[$col] . '4:' . $columnas[$col + 1] . '4');
            /* estilos */
            $objSheet->getStyle($columnas[$col] . 4)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f7f8f8');

            /* --------------- Plantas Disponibles ------------------ */
            $objSheet->getCell($columnas[$col] . 5)->setValue(number_format($item->plantas_disponibles));
            $objSheet->mergeCells($columnas[$col] . '5:' . $columnas[$col + 1] . '5');
            /* estilos */
            $objSheet->getStyle($columnas[$col] . 5)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f7f8f8');

            /* --------------- Requerimientos ------------------ */
            $objSheet->getCell($columnas[$col] . 6)->setValue(number_format($item->calcular_requerimientos()));
            $objSheet->mergeCells($columnas[$col] . '6:' . $columnas[$col + 1] . '6');
            /* estilos */
            $objSheet->getStyle($columnas[$col] . 6)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f7f8f8');

            /* --------------- Saldo ------------------ */
            $objSheet->getCell($columnas[$col] . 7)->setValue(number_format($item->saldo));
            $objSheet->mergeCells($columnas[$col] . '7:' . $columnas[$col + 1] . '7');
            /* estilos */
            $objSheet->getStyle($columnas[$col] . 7)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('5A7177');

            $col += 2;
        }
        $objSheet->getStyle('A1:' . $columnas[$col] . '7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objSheet->getStyle('A1:' . $columnas[$col] . '1')->getFont()->getColor()->setRGB('FFFFFF');
        $objSheet->getStyle('A7:' . $columnas[$col] . '7')->getFont()->getColor()->setRGB('FFFFFF');
        $objSheet->getStyle('A1:' . $columnas[$col] . '7')
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)
            ->getColor()
            ->setRGB(PHPExcel_Style_Color::COLOR_BLACK);

        $objSheet->getColumnDimension('A')->setAutoSize(true);
    }
}