<?php

namespace yura\Http\Controllers\ProyNintanga;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Planta;
use yura\Modelos\ProyVariedadSemana;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Modelos\ProyLongitudes;

class ProyeccionesSemanaController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = Planta::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $semana_desde = getSemanaByDate(opDiasFecha('+', 0, hoy()));
        $semana_hasta = getSemanaByDate(opDiasFecha('+', 35, hoy()));
        return view('adminlte.gestion.proyeccion_nintanga.proyeccion_semana.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
            'semana_desde' => $semana_desde,
            'semana_hasta' => $semana_hasta,
        ]);
    }

    public function listar_formulario(Request $request)
    {
        $variedades = DB::table('variedad')
            ->select('nombre', 'siglas')->distinct()
            ->where('id_planta', $request->planta)
            ->where('assorted', 0)
            ->where('estado', 1);
        if ($request->variedad != '')
            $variedades = $variedades->where('id_variedad', $request->variedad);
        $variedades = $variedades->orderBy('orden')
            ->get();

        $longitudes = DB::table('proy_longitudes')
            ->where('id_planta', $request->planta)
            ->orderBy('orden')
            ->get();
        $semanas = DB::table('proy_variedad_semana')
            ->select('semana')->distinct()
            ->where('id_planta', $request->planta)
            ->where('semana', '>=', $request->desde)
            ->where('semana', '<=', $request->hasta)
            ->orderBy('semana', 'asc')
            ->get();

        $listado = [];
        foreach ($variedades as $var) {
            $valores = [];
            foreach ($semanas as $sem) {
                $val_longitudes = [];
                foreach ($longitudes as $c) {
                    $query = ProyVariedadSemana::where('id_planta', $request->planta)
                        ->where('semana', $sem->semana)
                        ->where('siglas', $var->siglas)
                        ->where('id_longitudes', $c->id_proy_longitudes)
                        ->get()
                        ->first();
                    array_push($val_longitudes, $query != '' ? $query->cantidad : '');
                }
                array_push($valores, $val_longitudes);
            }
            array_push($listado, [
                'var' => $var,
                'valores' => $valores,
            ]);
        }

        return view('adminlte.gestion.proyeccion_nintanga.proyeccion_semana.forms.ingresos', [
            'listado' => $listado,
            'variedades' => $variedades,
            'semanas' => $semanas,
            'longitudes' => $longitudes,
            'planta' => $request->planta,
        ]);
    }

    public function grabar_proy(Request $request)
    {
        foreach ($request->data as $d) {
            foreach ($d['valores'] as $v) {
                $delete = ProyVariedadSemana::where('id_planta', $request->planta)
                    ->where('siglas', $d['var'])
                    ->where('semana', $v['semana'])
                    ->where('id_longitudes', $v['long'])
                    ->delete();

                $semana = getObjSemana($v['semana']);
                if ($v['cant'] != '' && $v['semana'] != '' && $semana != '') {
                    $model = new ProyVariedadSemana();
                    $model->id_planta = $request->planta;
                    $model->siglas = $d['var'];
                    $model->semana = $v['semana'];
                    $model->id_longitudes = $v['long'];
                    $model->cantidad = $v['cant'];
                    $model->save();
                }
            }
        }
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>GUARDADO</strong> la información correctamente',
        ];
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);

        $fileName = "Proyeccion_Semana.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_reporte($spread, $request)
    {
        $columnas = getColumnasExcel();
        $plantas = Planta::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        foreach ($plantas as $pos_pta => $pta) {
            $variedades = DB::table('variedad')
                ->select('nombre', 'siglas')->distinct()
                ->where('id_planta', $pta->id_planta)
                ->where('assorted', 0)
                ->where('estado', 1)
                ->orderBy('orden')
                ->get();

            $longitudes = DB::table('proy_longitudes')
                ->where('id_planta', $pta->id_planta)
                ->orderBy('orden')
                ->get();
            $semanas = DB::table('proy_variedad_semana')
                ->select('semana')->distinct()
                ->where('id_planta', $pta->id_planta)
                ->where('semana', '>=', $request->desde)
                ->where('semana', '<=', $request->hasta)
                ->orderBy('semana', 'asc')
                ->get();

            $listado = [];
            foreach ($variedades as $var) {
                $valores = [];
                foreach ($semanas as $sem) {
                    $val_longitudes = [];
                    foreach ($longitudes as $c) {
                        $query = ProyVariedadSemana::where('id_planta', $pta->id_planta)
                            ->where('semana', $sem->semana)
                            ->where('siglas', $var->siglas)
                            ->where('id_longitudes', $c->id_proy_longitudes)
                            ->get()
                            ->first();
                        array_push($val_longitudes, $query != '' ? $query->cantidad : '');
                    }
                    array_push($valores, $val_longitudes);
                }
                array_push($listado, [
                    'var' => $var,
                    'valores' => $valores,
                ]);
            }

            if ($pos_pta == 0)
                $sheet = $spread->getActiveSheet();
            else {
                $sheet = $spread->createSheet($pos_pta);
                $sheet = $spread->setActiveSheetIndex($pos_pta);
                $sheet = $spread->getActiveSheet();
            }
            $sheet->setTitle($pta->nombre);

            $sheet = $spread->getActiveSheet();
            $sheet->mergeCells('A1:A2');
            setBgToCeldaExcel($sheet, 'A1:A2', '00b388');
            setColorTextToCeldaExcel($sheet, 'A1:A2', 'ffffff');
            $col = 1;
            $row = 1;
            foreach ($semanas as $pos_sem => $sem) {
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $sem->semana);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
                setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                setTextCenterToCeldaExcel($sheet, $columnas[$col] . $row);
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col + count($longitudes) - 1] . $row);
                $col += count($longitudes);
            }
            $row = 2;
            $col = 1;
            $totales = [];
            foreach ($semanas as $pos_sem => $sem) {
                $valores_sem = [];
                foreach ($longitudes as $pos_c => $c) {
                    $valores_sem[] = 0;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $c->nombre);
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, 'cecbcb');
                    setTextCenterToCeldaExcel($sheet, $columnas[$col] . $row);
                    $col++;
                }
                $totales[] = $valores_sem;
            }

            $row = 3;
            foreach ($listado as $item) {
                $col = 0;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['var']->nombre);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
                setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                $col++;
                foreach ($item['valores'] as $pos_sem => $v) {
                    $bg_color = ($pos_sem + 1) % 2 == 0 ? 'ffffff' : 'b8efaa';
                    foreach ($v as $pos_c => $val) {
                        $totales[$pos_sem][$pos_c] += $val != '' ? $val : 0;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val);
                        setBgToCeldaExcel($sheet, $columnas[$col] . $row, $bg_color);
                        $col++;
                    }
                }
                $row++;
            }

            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            foreach ($totales as $pos_sem => $sem) {
                foreach ($sem as $pos_c => $c) {
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $c);
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
                    setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                }
            }


            setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

            for ($i = 0; $i <= $col; $i++)
                $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
        }
    }

    public function add_semana(Request $request)
    {
        $semana = getObjSemana($request->semana);
        $r = $semana != '' ? getSemanaByDate(opDiasFecha('+', 7, $semana->fecha_inicial))->codigo : '';
        return [
            'semana' => $r,
        ];
    }

    public function add_longitudes(Request $request)
    {
        $listado = ProyLongitudes::where('id_planta', $request->planta)
            ->orderBy('orden')
            ->get();
        return view('adminlte.gestion.proyeccion_nintanga.proyeccion_semana.forms.add_longitudes', [
            'planta' => $request->planta,
            'listado' => $listado,
        ]);
    }

    public function store_longitud(Request $request)
    {
        $model = new ProyLongitudes();
        $model->id_planta = $request->planta;
        $model->nombre = $request->nombre;
        $model->orden = $request->orden;
        $model->save();
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>CREADO</strong> una nueva longitud',
        ];
    }

    public function update_longitud(Request $request)
    {
        $model = ProyLongitudes::find($request->id);
        $model->nombre = $request->nombre;
        $model->orden = $request->orden;
        $model->save();
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>MODIFICADO</strong> la longitud',
        ];
    }

    public function delete_longitud(Request $request)
    {
        $model = ProyLongitudes::find($request->id);
        $model->delete();
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>ELIMINADO</strong> la longitud',
        ];
    }
}
