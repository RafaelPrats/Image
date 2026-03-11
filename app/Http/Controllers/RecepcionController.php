<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Modelos\Cosecha;
use yura\Modelos\DesgloseRecepcion;
use yura\Modelos\Recepcion;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use yura\Modelos\Planta;
use yura\Modelos\ProyLongitudes;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Modelos\InventarioRecepcion;
use yura\Modelos\SobranteRecepcion;

class RecepcionController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = Planta::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postcocecha.recepciones.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $listado = DesgloseRecepcion::join('recepcion as r', 'r.id_recepcion', '=', 'desglose_recepcion.id_recepcion');
        if ($request->planta != '')
            $listado = $listado->join('variedad as v', 'v.id_variedad', '=', 'desglose_recepcion.id_variedad')
                ->where('v.id_planta', $request->planta);
        if ($request->longitud != '')
            $listado = $listado->where('desglose_recepcion.longitud_ramo', ProyLongitudes::find($request->longitud)->nombre);
        $listado = $listado->select('desglose_recepcion.*')->distinct()
            ->where('r.fecha_ingreso', $request->fecha)
            ->get();

        $plantas = Planta::where('estado', 1)
            ->get();
        return view('adminlte.gestion.postcocecha.recepciones.partials.listado', [
            'listado' => $listado,
            'plantas' => $plantas,
        ]);
    }

    public function add_recepcion(Request $request)
    {
        $variedades = Variedad::where('estado', 1)
            ->orderBy('orden')
            ->get();
        return view('adminlte.gestion.postcocecha.recepciones.forms.add_desglose', [
            'variedades' => $variedades,
            'fecha' => $request->fecha,
        ]);
    }

    public function ver_sobrantes(Request $request)
    {
        $listado = SobranteRecepcion::join('variedad as v', 'v.id_variedad', '=', 'sobrante_recepcion.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('sobrante_recepcion.*')->distinct()
            ->where('sobrante_recepcion.fecha', $request->fecha)
            ->orderBy('p.orden')
            ->orderBy('v.orden')
            ->get();
        return view('adminlte.gestion.postcocecha.recepciones.partials.ver_sobrantes', [
            'listado' => $listado,
        ]);
    }

    public function select_variedad_recepcion(Request $request)
    {
        return [
            'variedad' => Variedad::find($request->variedad),
        ];
    }

    public function store_recepcion(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach (json_decode($request->data) as $d) {
                $model = new InventarioRecepcion();
                dd($d);
                bitacora('inventario_recepcion', $model->id_inventario_recepcion, 'I', 'INGRESO RECEPCION');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'mensaje' => $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine()
            ];
        }
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>GUARDADO</strong> la cosecha correctamente'
        ];
    }

    public function update_desglose(Request $request)
    {
        $desglose = DesgloseRecepcion::find($request->id);
        $desglose->id_variedad = $request->variedad;
        $desglose->longitud_ramo = $request->longitud;
        $desglose->tallos_x_malla = $request->tallos_x_malla;
        $desglose->cantidad_mallas = $request->mallas;
        $desglose->save();
        bitacora('desglose_recepcion', $desglose->id_desglose_recepcion, 'U', 'UPDATE_DESGLOSE: var:' . $desglose->id_variedad . '; cm: ' . $desglose->longitud_ramo . '; mallas: ' . $desglose->cantidad_mallas . '; tallos_x_malla: ' . $desglose->tallos_x_malla);

        /* ======= ACTUALIZAR LA TABLA COSECHA_DIARIA ========== */
        /*jobActualizarCosecha::dispatch($request->variedad, substr($desglose->recepcion->fecha_ingreso, 0, 10), getFincaActiva())
            ->onQueue('proy_cosecha');*/

        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>GUARDADO</strong> la cosecha correctamente'
        ];
    }

    public function store_sobrantes(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach (json_decode($request->data) as $d) {
                $sobrante = new SobranteRecepcion();
                $sobrante->fecha = $request->fecha;
                $sobrante->id_variedad = $d->variedad;
                $sobrante->longitud = $d->longitud;
                $sobrante->cantidad = $d->sobrante;
                $sobrante->save();
                $sobrante = SobranteRecepcion::All()->last();
                bitacora('sobrante_recepcion', $sobrante->id_sobrante_recepcion, 'i', 'Ingreso Sobrante: ' . $d->sobrante);

                /* GRABAR SIGUIENTE DIA */
                $fecha = opDiasFecha('+', 1, $request->fecha);
                $cosecha = Cosecha::All()
                    ->where('fecha_ingreso', $fecha)
                    ->first();
                if ($cosecha == '') {
                    $cosecha = new Cosecha();
                    $cosecha->fecha_ingreso = $fecha;
                    $cosecha->personal = 1;
                    $cosecha->hora_inicio = '08:00';
                    $cosecha->fecha_registro = date('Y-m-d H:i:s');
                    $cosecha->save();
                    $cosecha = Cosecha::All()->last();
                    bitacora('cosecha', $cosecha->id_cosecha, 'I', 'STORE_RECEPCION por Sobrante');
                }
                $recepcion = new Recepcion();
                $recepcion->id_semana = getSemanaByDate($fecha)->id_semana;
                $recepcion->id_cosecha = $cosecha->id_cosecha;
                $recepcion->fecha_ingreso = $fecha;
                $recepcion->fecha_registro = date('Y-m-d H:i:s');
                $recepcion->save();
                $recepcion = Recepcion::All()->last();
                bitacora('recepcion', $recepcion->id_recepcion, 'I', 'STORE_RECEPCION por Sobrante');

                $new_desglose = new DesgloseRecepcion();
                $new_desglose->id_variedad = $d->variedad;
                $new_desglose->longitud_ramo = $d->longitud;
                $new_desglose->id_modulo = -1;
                $new_desglose->tallos_x_malla = $d->sobrante;
                $new_desglose->cantidad_mallas = 1;
                $new_desglose->id_recepcion = $recepcion->id_recepcion;
                $new_desglose->fecha_registro = date('Y-m-d H:i:s');
                $new_desglose->save();
                $new_desglose = DesgloseRecepcion::All()->last();
                bitacora('desglose_recepcion', $new_desglose->id_desglose_recepcion, 'I', 'STORE_RECEPCION: var:' . $new_desglose->id_variedad . '; cm: ' . $new_desglose->longitud_ramo . '; mallas: ' . $new_desglose->cantidad_mallas . '; tallos_x_malla: ' . $new_desglose->tallos_x_malla . ' po Sobrante');
            }
            $success = true;
            $mensaje = 'Se han <strong>GUARDADO</strong> los sobrantes correctamente';
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $mensaje = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
        }
        return [
            'success' => $success,
            'mensaje' => $mensaje
        ];
    }

    public function delete_sobrante(Request $request)
    {
        $model = SobranteRecepcion::find($request->id);
        bitacora('sobrante_recepcion', $model->id_sobrante_recepcion, 'D', 'DELETE_SOBRANTE: var:' . $model->id_variedad . '; cm: ' . $model->longitud . '; tallos: ' . $model->cantidad);
        $model->delete();

        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>ELIMINADO</strong> el registro de sobrante correctamente'
        ];
    }

    public function delete_desglose(Request $request)
    {
        $desglose = DesgloseRecepcion::find($request->id);
        /*$variedad = $desglose->id_variedad;
        $fecha = substr($desglose->recepcion->fecha_ingreso, 0, 10);*/
        bitacora('desglose_recepcion', $desglose->id_desglose_recepcion, 'D', 'DELETE_DESGLOSE: var:' . $desglose->id_variedad . '; cm: ' . $desglose->longitud_ramo . '; mallas: ' . $desglose->cantidad_mallas . '; tallos_x_malla: ' . $desglose->tallos_x_malla);
        $desglose->delete();

        /* ======= ACTUALIZAR LA TABLA COSECHA_DIARIA ========== */
        /*jobActualizarCosecha::dispatch($variedad, $fecha, getFincaActiva())
            ->onQueue('proy_cosecha');*/
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>ELIMINADO</strong> la cosecha correctamente'
        ];
    }

    public function exportar_recepcion(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_recepcion($spread, $request);

        $fileName = "Cosecha.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_recepcion($spread, $request)
    {
        $listado = DesgloseRecepcion::join('recepcion as r', 'r.id_recepcion', '=', 'desglose_recepcion.id_recepcion');
        if ($request->planta != '')
            $listado = $listado->join('variedad as v', 'v.id_variedad', '=', 'desglose_recepcion.id_variedad')
                ->where('v.id_planta', $request->planta);
        if ($request->longitud != '')
            $listado = $listado->where('desglose_recepcion.longitud_ramo', ProyLongitudes::find($request->longitud)->nombre);
        $listado = $listado->select('desglose_recepcion.*')->distinct()
            ->where('r.fecha_ingreso', $request->fecha)
            ->get();

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Cuarto Frio');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Color');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Longitud');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Mallas');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos x Malla');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Total Tallos');
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        $total_tallos = 0;
        foreach ($listado as $item) {
            $variedad = $item->variedad;
            $total_tallos += $item->cantidad_mallas * $item->tallos_x_malla;
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $variedad->planta->nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $variedad->nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->longitud_ramo);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->cantidad_mallas);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->tallos_x_malla);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->cantidad_mallas * $item->tallos_x_malla);
        }

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
