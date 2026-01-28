<?php

namespace yura\Http\Controllers\Comercializacion;

use DB;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Http\Controllers\Controller;
use yura\Modelos\CuartoFrio;
use yura\Modelos\Empaque;
use yura\Modelos\InventarioBasura;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class InventarioCuartoFrioController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = Planta::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $presentaciones = Empaque::where('estado', 1)
            ->where('tipo', 'P')
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.comercializacion.inventario_cuarto_frio.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
            'presentaciones' => $presentaciones,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $items = DB::table('cuarto_frio as cf')
            ->join('empaque as e', 'e.id_empaque', '=', 'cf.id_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'cf.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'cf.id_variedad',
                'v.nombre as var_nombre',
                'cf.id_empaque',
                'e.nombre as pres_nombre',
                'v.id_planta',
                'p.nombre as pta_nombre',
                'cf.tallos_x_ramo',
                'cf.longitud_ramo',
            )->distinct()
            ->where('cf.disponibles', '>', 0);
        if ($request->planta != '')
            $items = $items->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $items = $items->where('cf.id_variedad', $request->variedad);
        if ($request->presentacion != '')
            $items = $items->where('cf.id_empaque', $request->presentacion);
        $items = $items->orderBy('p.orden')
            ->orderBy('v.orden')
            ->orderBy('e.nombre')
            ->get();
        $listado = [];
        foreach ($items as $item) {
            $valores = DB::table('cuarto_frio')
                ->select(
                    DB::raw('sum(disponibles) as cantidad'),
                    'fecha'
                )
                ->where('id_variedad', $item->id_variedad)
                ->where('id_empaque', $item->id_empaque)
                ->where('tallos_x_ramo', $item->tallos_x_ramo)
                ->where('longitud_ramo', $item->longitud_ramo)
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get();
            $listado[] = [
                'item' => $item,
                'valores' => $valores,
            ];
        }
        return view('adminlte.gestion.comercializacion.inventario_cuarto_frio.partials.listado', [
            'listado' => $listado,
        ]);
    }

    public function modal_inventario(Request $request)
    {
        $listado = CuartoFrio::join('variedad as v', 'v.id_variedad', '=', 'cuarto_frio.id_variedad')
            ->select('cuarto_frio.*')->distinct()
            ->where('cuarto_frio.disponibles', '>', 0);
        if ($request->pos != 'T') {
            $listado = $listado->where('cuarto_frio.id_variedad', $request->variedad)
                ->where('cuarto_frio.id_empaque', $request->empaque)
                ->where('cuarto_frio.tallos_x_ramo', $request->tallos_x_ramo)
                ->where('cuarto_frio.longitud_ramo', $request->longitud);
        } else {
            if ($request->planta != '')
                $listado = $listado->where('v.id_planta', $request->planta);
            if ($request->variedad != '')
                $listado = $listado->where('cuarto_frio.id_variedad', $request->variedad);
            if ($request->empaque != '')
                $listado = $listado->where('cuarto_frio.id_empaque', $request->empaque);
        }
        if (difFechas(hoy(), $request->fecha)->days < 5)
            $listado = $listado->where('cuarto_frio.fecha', $request->fecha);
        else
            $listado = $listado->where('cuarto_frio.fecha', '<=', $request->fecha);
        $listado = $listado->orderBy('cuarto_frio.fecha_registro')
            ->get();
        return view('adminlte.gestion.comercializacion.inventario_cuarto_frio.partials.modal_inventario', [
            'listado' => $listado,
            'variedad' => Variedad::find($request->variedad),
            'empaque' => Empaque::find($request->empaque),
            'tallos_x_ramo' => $request->tallos_x_ramo,
            'longitud' => $request->longitud,
            'fecha' => $request->fecha,
            'pos' => $request->pos,
        ]);
    }

    public function botar_inventario(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = CuartoFrio::find($request->id);
            if ($request->cantidad <= $model->disponibles) {
                // QUITAR del inventario original
                $model->disponibles -= $request->cantidad;
                $model->save();
                bitacora('cuarto_frio', $request->id, 'U', 'BOTAR_INVENTARIO ' . $request->cantidad);

                // GRABR basura
                $basura = new InventarioBasura();
                $basura->id_variedad = $model->id_variedad;
                $basura->id_empaque = $model->id_empaque;
                $basura->tallos_x_ramo = $model->tallos_x_ramo;
                $basura->longitud_ramo = $model->longitud_ramo;
                $basura->fecha = hoy();
                $basura->cantidad = $request->cantidad;
                $basura->id_dato_exportacion = $model->id_dato_exportacion;
                $basura->valor_marcacion = $model->valor_marcacion;
                $basura->save();
                $id = DB::table('inventario_basura')
                    ->select(DB::raw('max(id_inventario_basura) as id'))
                    ->get()[0]->id;
                bitacora('inventario_basura', $id, 'I', 'BOTAR_INVENTARIO con id:' . $model->id_cuarto_frio);
                DB::commit();
                $success = true;
                $msg = 'Se han <strong>BOTADO</strong> los ramos correctamente';
            } else {
                DB::rollBack();
                $success = false;
                $msg = '<div class="alert alert-danger text-center">Debe seleccionar una cantidad <strong>IGUAL o MENOR</strong> a los ramos disponibles</div>';
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'mensaje' => $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine()
            ];
        }
        return [
            'success' => $success,
            'mensaje' => $msg
        ];
    }

    public function delete_inventario(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = CuartoFrio::find($request->id);
            if ($request->cantidad <= $model->disponibles) {
                // QUITAR del inventario original
                $model->disponibles -= $request->cantidad;
                $model->save();
                bitacora('cuarto_frio', $request->id, 'U', 'DELETE_INVENTARIO ' . $request->cantidad);

                DB::commit();
                $success = true;
                $msg = 'Se han <strong>ELIMINADO</strong> los ramos correctamente';
            } else {
                DB::rollBack();
                $success = false;
                $msg = '<div class="alert alert-danger text-center">Debe seleccionar una cantidad <strong>IGUAL o MENOR</strong> a los ramos disponibles</div>';
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'mensaje' => $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine()
            ];
        }
        return [
            'success' => $success,
            'mensaje' => $msg
        ];
    }

    public function agregar_inventario(Request $request)
    {
        $plantas = Planta::where('estado', 1)
            ->orderBy('orden')
            ->get();
        $presentaciones = Empaque::where('estado', 1)
            ->where('tipo', 'P')
            ->orderBy('nombre')
            ->get();
        $datos_exportacion = DB::table('dato_exportacion')
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.comercializacion.inventario_cuarto_frio.forms.agregar_inventario', [
            'plantas' => $plantas,
            'presentaciones' => $presentaciones,
            'datos_exportacion' => $datos_exportacion,
        ]);
    }

    public function store_grabar_inventario(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = json_decode($request->data);
            foreach ($data as $d) {
                $cf = new CuartoFrio();
                $cf->id_variedad = $d->variedad;
                $cf->id_empaque = $d->presentacion;
                $cf->tallos_x_ramo = $d->tallos_x_ramo;
                $cf->longitud_ramo = $d->longitud;
                $cf->fecha = $d->fecha;
                $cf->cantidad = $d->cantidad;
                $cf->disponibles = $d->cantidad;
                $cf->id_dato_exportacion = $d->dato_exportacion != '' ? $d->dato_exportacion : null;
                $cf->valor_marcacion = $d->valor_marcacion;
                $cf->save();
                bitacora('cuarto_frio', $cf->id_cuarto_frio, 'I', 'STORE_GRABAR_INVENTARIO');
            }
            DB::commit();
            return [
                'success' => true,
                'mensaje' => 'Inventario grabado con éxito'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'mensaje' => $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine()
            ];
        }
    }

    public function descargar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);

        $fileName = "Inventario.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_reporte($spread, $request)
    {
        $items = DB::table('cuarto_frio as cf')
            ->join('empaque as e', 'e.id_empaque', '=', 'cf.id_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'cf.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'cf.id_variedad',
                'v.nombre as var_nombre',
                'cf.id_empaque',
                'e.nombre as pres_nombre',
                'v.id_planta',
                'p.nombre as pta_nombre',
                'cf.tallos_x_ramo',
                'cf.longitud_ramo',
            )->distinct()
            ->where('cf.disponibles', '>', 0);
        if ($request->variedad != '')
            $items = $items->where('cf.id_variedad', $request->variedad);
        if ($request->presentacion != '')
            $items = $items->where('cf.id_empaque', $request->presentacion);
        $items = $items->orderBy('p.orden')
            ->orderBy('v.orden')
            ->orderBy('e.nombre')
            ->get();
        $listado = [];
        foreach ($items as $item) {
            $valores = DB::table('cuarto_frio')
                ->select(
                    DB::raw('sum(disponibles) as ramos'),
                    DB::raw('sum(disponibles * tallos_x_ramo) as tallos'),
                    'fecha'
                )
                ->where('id_variedad', $item->id_variedad)
                ->where('id_empaque', $item->id_empaque)
                ->where('tallos_x_ramo', $item->tallos_x_ramo)
                ->where('longitud_ramo', $item->longitud_ramo)
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get();
            $listado[] = [
                'item' => $item,
                'valores' => $valores,
            ];
        }

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('PEDIDOS ' . $request->desde . ' ' . $request->hasta);

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Color');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Presentacion');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos x Ramo');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Longitud');
        $totales_fecha = [];
        for ($i = 0; $i <= 4; $i++) {
            $totales_fecha[] = 0;

            $col++;
            $txt = $i;
            $txt .= $i == 4 ? '...' : '';
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $txt);
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Total Ramos');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Total Tallos');
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        $total_ramos = 0;
        foreach ($listado as $pos => $item) {
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['item']->pta_nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['item']->var_nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['item']->pres_nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['item']->tallos_x_ramo);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['item']->longitud_ramo . 'cm');
            $total_ramos_item = 0;
            $total_tallos_item = 0;
            for ($i = 0; $i <= 4; $i++) {
                $fecha = opDiasFecha('-', $i, hoy());
                $ramos = 0;
                $tallos = 0;
                foreach ($item['valores'] as $val) {
                    if ($i < 4) {
                        // desde hoy hasta 3 dias de antiguedad
                        if ($val->fecha == $fecha) {
                            $tallos += $val->tallos;
                            $ramos += $val->ramos;
                        }
                    } else {
                        // todo lo que tiene 4 o mas dias de antiguedad
                        if ($val->fecha <= $fecha) {
                            $tallos += $val->tallos;
                            $ramos += $val->ramos;
                        }
                    }
                }
                $total_ramos_item += $ramos;
                $total_tallos_item += $tallos;
                $total_ramos += $ramos;
                $totales_fecha[$i] += $tallos;

                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $tallos > 0 ? $tallos : '-');
            }
            // total por item
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_ramos_item);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_tallos_item);
        }

        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[($col + 4)] . $row);
        $col = 4;
        $total_tallos = 0;
        foreach ($totales_fecha as $i => $val) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val);
            $total_tallos += $val;
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_ramos);
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_tallos);
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setBorderToCeldaExcel($sheet, $columnas[0] . 1 . ':' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
