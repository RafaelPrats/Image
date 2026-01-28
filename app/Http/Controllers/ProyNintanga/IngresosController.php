<?php

namespace yura\Http\Controllers\ProyNintanga;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Planta;
use yura\Modelos\ProyCortes;
use yura\Modelos\ProyVariedadCortes;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class IngresosController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = Planta::where('estado', 1)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.proyeccion_nintanga.ingresos_diarios.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_formulario(Request $request)
    {
        $variedades = Variedad::where('id_planta', $request->planta)
            ->where('assorted', 0)
            ->where('estado', 1);
        if ($request->variedad != '')
            $variedades = $variedades->where('id_variedad', $request->variedad);
        $variedades = $variedades->orderBy('orden')
            ->get();

        $ids_variedades = $variedades->pluck('id_variedad')->toArray();

        $cortes = DB::table('proy_cortes')
            ->where('id_planta', $request->planta)
            ->orderBy('nombre', 'asc')
            ->get();

        $listado = [];
        foreach ($variedades as $var) {
            if ($request->planta != 8 || ($request->planta == 8 && $var->tipo == 'P')) {    // caso de la planta STATICE
                $valores = [];
                foreach ($cortes as $c) {
                    $query = ProyVariedadCortes::where('id_variedad', $var->id_variedad)
                        ->where('id_cortes', $c->id_proy_cortes)
                        ->where('fecha', $request->fecha)
                        ->get()
                        ->first();
                    array_push($valores, $query != '' ? $query->cantidad : '');
                }
                array_push($listado, [
                    'var' => $var,
                    'valores' => $valores,
                ]);
            }
        }

        return view('adminlte.gestion.proyeccion_nintanga.ingresos_diarios.forms.ingresos', [
            'listado' => $listado,
            'planta' => Planta::find($request->planta),
            'variedades' => $variedades,
            'cortes' => $cortes,
        ]);
    }

    public function grabar_proy(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach ($request->data as $d) {
                $delete = ProyVariedadCortes::where('id_variedad', $d['var'])
                    ->where('fecha', $request->fecha)
                    ->delete();

                if (isset($d['valores']))
                    foreach ($d['valores'] as $v) {
                        if (isset($v['id_corte'])) {
                            $corte = ProyCortes::find($v['id_corte']);
                            if ($corte->nombre != mb_strtoupper($v['corte'])) {
                                $corte->nombre = mb_strtoupper($v['corte']);
                                $corte->save();
                            }
                        } else
                            $corte = ProyCortes::where('nombre', mb_strtoupper($v['corte']))
                                ->where('id_planta', $request->planta)
                                ->get()
                                ->first();
                        if ($corte == '') {
                            $corte = new ProyCortes();
                            $corte->nombre = mb_strtoupper($v['corte']);
                            $corte->id_planta = $request->planta;
                            $corte->save();
                            $corte->id_proy_cortes = DB::table('proy_cortes')
                                ->select(DB::raw('max(id_proy_cortes) as id'))
                                ->get()[0]->id;
                        }
                        $model = new ProyVariedadCortes();
                        $model->id_variedad = $d['var'];
                        $model->id_cortes = $corte->id_proy_cortes;
                        $model->fecha = $request->fecha;
                        $model->cantidad = $v['cant'] * $request->factor_conversion;
                        $model->save();
                    }
            }

            DB::commit();

            $success = true;
            $msg = 'Se ha <strong>GUARDADO</strong> la información correctamente';
        } catch (\Exception $e) {

            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }
        //dd('TODO OK');

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);

        $fileName = "Proyeccion_Diaria.xlsx";
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
            ->orderBy('nombre')
            ->get();

        $columnas = getColumnasExcel();
        foreach ($plantas as $pos => $p) {
            $variedades = Variedad::where('id_planta', $p->id_planta)
                ->where('assorted', 0)
                ->where('estado', 1)
                ->orderBy('orden')
                ->get();

            $ids_variedades = $variedades->pluck('id_variedad')->toArray();

            $cortes = DB::table('proy_cortes')
                ->where('id_planta', $p->id_planta)
                ->orderBy('nombre', 'asc')
                ->get();

            $listado = [];
            foreach ($variedades as $var) {
                if ($p->id_planta != 8 || ($p->id_planta == 8 && $var->tipo == 'P')) {    // caso de la planta STATICE
                    $valores = [];
                    foreach ($cortes as $c) {
                        $query = ProyVariedadCortes::where('id_variedad', $var->id_variedad)
                            ->where('id_cortes', $c->id_proy_cortes)
                            ->where('fecha', $request->fecha)
                            ->get()
                            ->first();
                        array_push($valores, $query != '' ? $query->cantidad : '');
                    }
                    array_push($listado, [
                        'var' => $var,
                        'valores' => $valores,
                    ]);
                }
            }

            if ($pos == 0)
                $sheet = $spread->getActiveSheet();
            else {
                $sheet = $spread->createSheet($pos);
                $sheet = $spread->setActiveSheetIndex($pos);
                $sheet = $spread->getActiveSheet();
            }
            $sheet->setTitle($p->nombre);

            //$sheet->setCellValue('A1', 'En desarrollo!');
            setValueToCeldaExcel($sheet, 'A1', $p->nombre);
            setBoldToCeldaExcel($sheet, 'A1');
            $col = 1;
            $row = 1;
            foreach ($cortes as $pos_c => $c) {
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, substr($c->nombre, 2));
                $col++;
            }

            foreach ($listado as $item) {
                $col = 0;
                $row++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['var']->nombre);
                setBoldToCeldaExcel($sheet, $columnas[$col] . $row);
                foreach ($item['valores'] as $pos_c => $v) {
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $v);
                }
            }

            setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

            for ($i = 0; $i <= $col; $i++)
                $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
        }
    }

    public function cambiar_uso_corte(Request $request)
    {
        $model = ProyCortes::find($request->corte);
        $model->usar = $model->usar == 1 ? 0 : 1;
        $model->save();
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>MODIFICADO</strong> el corte satisfactoriamente',
        ];
    }

    public function update_factor_conversion(Request $request)
    {
        DB::beginTransaction();
        try {
            $planta = Planta::find($request->planta);
            $planta->factor_conversion_proy = $request->valor != '' ? $request->valor : 1;
            $planta->save();

            DB::commit();

            $success = true;
            $msg = 'Se ha <strong>GUARDADO</strong> la información correctamente';
        } catch (\Exception $e) {

            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }
        //dd('TODO OK');

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }
}
