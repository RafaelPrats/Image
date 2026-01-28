<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Modelos\PedidoModificacion;
use yura\Modelos\Submenu;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Modelos\CambiosPedido;

class ModificacionesPedidosController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = DB::table('cambios_pedido as m')
            ->join('variedad as v', 'v.id_variedad', '=', 'm.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('v.id_planta', 'p.nombre')->distinct()
            ->orderBy('p.orden')
            ->get();
        $clientes = DB::table('cambios_pedido as m')
            ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'm.id_cliente')
            ->select('m.id_cliente', 'dc.nombre')->distinct()
            ->where('dc.estado', 1)
            ->orderBy('dc.nombre')
            ->get();
        return view('adminlte.gestion.modificaciones_pedidos.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
            'clientes' => $clientes,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $listado = CambiosPedido::join('usuario as u', 'u.id_usuario', '=', 'cambios_pedido.id_usuario')
            ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'cambios_pedido.id_cliente')
            ->join('empaque as e', 'e.id_empaque', '=', 'cambios_pedido.id_empaque_p')
            ->join('empaque as c', 'c.id_empaque', '=', 'cambios_pedido.id_empaque_c')
            ->join('variedad as v', 'v.id_variedad', '=', 'cambios_pedido.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'cambios_pedido.*',
                'v.nombre as nombre_var',
                'p.nombre as nombre_pta',
                'dc.nombre as nombre_cliente',
                //'dee.cantidad as ramos_x_caja',
                'u.nombre_completo as nombre_usuario',
                'e.nombre as presentacion',
                'c.siglas as caja',
                'c.nombre as nombre_caja',
            )->distinct()
            ->where('dc.estado', 1)
            ->where('cambios_pedido.fecha_anterior', $request->fecha);
        if ($request->cliente != '') {
            $listado = $listado->where('cambios_pedido.id_cliente', $request->cliente);
        }
        if ($request->planta != '') {
            $listado = $listado->where('v.id_planta', $request->planta);
        }
        $listado = $listado->orderBy('cambios_pedido.fecha_registro', 'desc')
            //->orderBy('p.orden')
            //->orderBy('v.orden')
            ->get();
        return view('adminlte.gestion.modificaciones_pedidos.partials.listado', [
            'listado' => $listado,
        ]);
    }

    public function cambiar_uso(Request $request)
    {
        $model = PedidoModificacion::find($request->id);
        $model->usar = $model->usar == 1 ? 0 : 1;
        $model->save();
        bitacora('pedido_modificacion', $request->id, 'U', 'CAMBIAR USO A: ' . $model->usar);
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>CAMBIADO el USO<strong> correctamente',
        ];
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);

        $fileName = "Modificaciones_pedidos.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_reporte($spread, $request)
    {
        $listado = PedidoModificacion::join('detalle_especificacionempaque as dee', 'dee.id_detalle_especificacionempaque', '=', 'pedido_modificacion.id_detalle_especificacionempaque')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion_empaque', '=', 'dee.id_especificacion_empaque')
            ->join('usuario as u', 'u.id_usuario', '=', 'pedido_modificacion.id_usuario')
            ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'pedido_modificacion.id_cliente')
            ->join('empaque as e', 'e.id_empaque', '=', 'dee.id_empaque_p')
            ->join('empaque as c', 'c.id_empaque', '=', 'ee.id_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'pedido_modificacion.*',
                'v.nombre as nombre_var',
                'p.nombre as nombre_pta',
                'dc.nombre as nombre_cliente',
                //'dee.cantidad as ramos_x_caja',
                'dee.tallos_x_ramos as tallos_x_ramos',
                'dee.longitud_ramo as longitud_ramo',
                'u.nombre_completo as nombre_usuario',
                'e.nombre as presentacion',
                'c.siglas as caja',
                'c.nombre as nombre_caja',
            )->distinct()
            ->where('dc.estado', 1)
            ->where('pedido_modificacion.fecha_anterior_pedido', $request->fecha);
        if ($request->cliente != '') {
            $listado = $listado->where('pedido_modificacion.id_cliente', $request->cliente);
        }
        if ($request->planta != '') {
            $listado = $listado->where('v.id_planta', $request->planta);
        }
        $listado = $listado->orderBy('pedido_modificacion.fecha_registro', 'desc')
            //->orderBy('p.orden')
            //->orderBy('v.orden')
            ->get();

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Modificaciones de pedidos');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Cliente');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Planta');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Color');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Presentacion');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Longitud');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Caja');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tipo Caja');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Piezas');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Ramos');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Usuario');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Fecha y Hora');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Cambio Fecha');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $item) {
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->nombre_cliente);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->id_planta != null ? $item->planta->nombre : $item->nombre_pta);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->id_planta != null ? $item->getVariedad()->nombre : $item->nombre_var);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->presentacion);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->longitud_ramo);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, explode('|', $item->nombre_caja)[0]);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->caja);
            $col++;
            if ($item->cantidad != null)
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, '(' . $item->operador . ')' . $item->cantidad);
            else
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'MIXTOS');
            $col++;
            $ramos = $item->cantidad != null ? ($item->cantidad * $item->ramos_x_caja) : $item->ramos;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, '(' . $item->operador . ')' . $ramos);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->cantidad != null ? ($item->cantidad * $item->ramos_x_caja * $item->tallos_x_ramos) : $item->tallos);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->nombre_usuario);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->fecha_registro);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->cambio_fecha ? $item->fecha_nuevo_pedido : 'NO');
        }

        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
