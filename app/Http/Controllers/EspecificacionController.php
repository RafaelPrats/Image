<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use yura\Modelos\ClasificacionRamo;
use yura\Modelos\ClientePedidoEspecificacion;
use yura\Modelos\DetalleEspecificacionEmpaque;
use yura\Modelos\DetallePedido;
use yura\Modelos\Empaque;
use yura\Modelos\Especificacion;
use yura\Modelos\Cliente;
use DB;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Color;
use PHPExcel_Style_Fill;
use PHPExcel_Worksheet;
use yura\Modelos\EspecificacionEmpaque;
use yura\Modelos\Submenu;
use yura\Modelos\UnidadMedida;
use yura\Modelos\Variedad;
use Validator;
use yura\Modelos\Planta;

class EspecificacionController extends Controller
{
    public function inicio(Request $request)
    {
        $clientes = Cliente::join('detalle_cliente as dc', 'cliente.id_cliente', 'dc.id_cliente')
            ->where('dc.estado', 1)
            ->orderBy('dc.nombre')
            ->get();

        $presentaciones =  Empaque::where([
            ['tipo', 'P'],
            ['estado', 1]
        ])->orderBy('nombre')->get();

        $plantas = Planta::where('estado', 1)->orderBy('nombre', 'asc')->get();

        $tipos_caja = DB::table('empaque')
            ->select('siglas')->distinct()
            ->where('estado', 1)
            ->where('tipo', 'C')
            ->whereNotNull('siglas')
            ->orderBy('siglas')
            ->get();

        return view(
            'adminlte.gestion.postcocecha.especificacion.incio',
            [
                'url' => $request->getRequestUri(),
                'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
                'text' => ['titulo' => 'Especificaciones', 'subtitulo' => 'módulo de especificaciones'],
                'clientes' => $clientes,
                'presentaciones' => $presentaciones,
                'plantas' => $plantas,
                'tipos_caja' => $tipos_caja,
            ]
        );
    }

    public function listado_especificaciones(Request $request)
    {
        $id_cliente = $request->has('id_cliente') ? $request->id_cliente : '';
        $tipo = $request->has('tipo') ? $request->tipo : '';

        $listado = Especificacion::join('especificacion_empaque as ee', 'especificacion.id_especificacion', 'ee.id_especificacion')
            ->join('detalle_especificacionempaque as de', 'de.id_especificacion_empaque', 'ee.id_especificacion_empaque')
            ->join('empaque as e', 'e.id_empaque', 'ee.id_empaque')
            ->join('variedad as v', function ($join) use ($request) {

                $join->on('v.id_variedad', 'de.id_variedad');

                if (isset($request->id_variedad)) {
                    $join->where('v.id_variedad', $request->id_variedad);
                } else if (isset($request->id_planta)) {
                    $join->where('v.id_planta', $request->id_planta);
                }
            })->where([
                ['especificacion.tipo', 'N'],
                ['especificacion.creada', 'PRE-ESTABLECIDA'],
                ['especificacion.estado', $request->estado],
                ['v.estado', 1],
            ]);

        if ($tipo == 'NINTANGA') {
            $listado->where(DB::raw("UPPER(e.nombre)"), 'like', '%' . strtoupper($tipo) . '%');
        } else if ($tipo == 'ESPECIALES') {
            $listado->where(DB::raw("UPPER(e.nombre)"), 'not like', '%NINTANGA%');
        }

        if ($id_cliente != '' && $id_cliente != 'undefined')
            $listado->join('cliente_pedido_especificacion as cpe', 'especificacion.id_especificacion', 'cpe.id_especificacion')
                ->join('detalle_cliente as dc', 'cpe.id_cliente', 'dc.id_cliente')
                ->where([
                    ['cpe.id_cliente', $id_cliente],
                    ['dc.estado', 1],
                ]);

        if ($request->longitud != '')
            $listado->where('de.longitud_ramo', $request->longitud);

        if ($request->tipo_caja != '')
            $listado->where('e.siglas', $request->tipo_caja);


        $listado = $listado->select(
            'especificacion.id_especificacion',
            'especificacion.tipo',
            'especificacion.estado',
            DB::raw("(SELECT GROUP_CONCAT(dc.nombre SEPARATOR ' | ') FROM cliente_pedido_especificacion AS cpe INNER JOIN detalle_cliente AS dc ON dc.id_cliente = cpe.id_cliente AND dc.estado= true WHERE cpe.id_especificacion = especificacion.id_especificacion AND cpe.estado = true) AS clientes"),
        )->distinct()
            ->orderBy('especificacion.id_especificacion', 'desc')->get();

        $datos = [
            'listado' => $listado,
            'plantas' => Planta::where('estado', true)->orderBy('nombre', 'asc')->get(),
            'clasificacion_ramo' => ClasificacionRamo::where('estado', true)
                ->select('nombre', 'id_clasificacion_ramo')->orderBy('nombre', 'asc')->get(),
            'empaque' => Empaque::where([
                ['tipo', 'C'],
                ['estado', 1]
            ])->select('nombre', 'id_empaque')->get(),
            'presentacion' => Empaque::where([
                ['tipo', 'P'],
                ['estado', 1]
            ])->select('nombre', 'id_empaque')->get(),
            'unidad_medida' => UnidadMedida::where([
                ['tipo', 'L'],
                ['estado', 1]
            ])->get()
        ];
        return view('adminlte.gestion.postcocecha.especificacion.partials.listado', $datos);
    }

    public function form_asignacion_especificacion(Request $request)
    {
        return view('adminlte.gestion.postcocecha.especificacion.form.form_asignar_especificacion', [
            'listado' => Cliente::join('detalle_cliente as dc', 'cliente.id_cliente', 'dc.id_cliente')->where([
                ['dc.estado', 1],
                ['cliente.estado', 1]
            ])->orderBy('dc.nombre', 'asc')->get(),
            'id_especificacion' => $request->id_especificacion,
            'data_especificacion' => Especificacion::where('id_especificacion', $request->id_especificacion)->get(),
            'asginacion' => ClientePedidoEspecificacion::where('id_especificacion', $request->id_especificacion)->get()
        ]);
    }

    public function sotre_asignacion_especificacion(Request $request)
    {
        $objClientePedidoEspecificacion = new ClientePedidoEspecificacion;
        $cpe = ClientePedidoEspecificacion::orderBy('id_cliente_pedido_especificacion', 'desc')->first();
        $objClientePedidoEspecificacion->id_cliente_pedido_especificacion = isset($cpe->id_cliente_pedido_especificacion) ? $cpe->id_cliente_pedido_especificacion + 1 : 1;
        $objClientePedidoEspecificacion->id_cliente = $request->id_cliente;
        $objClientePedidoEspecificacion->id_especificacion = $request->id_especificacion;
        $detalle_cliente = getDatosCliente($request->id_cliente)->select('nombre')->first();
        if ($objClientePedidoEspecificacion->save()) {
            $msg = '<div class="alert alert-success text-center">' .
                '<p> Se ha agregado exitosamente la especificación al cliente ' . $detalle_cliente->nombre . '</p>'
                . '</div>';
        } else {
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> hubo un error asignando la especificación al cliente ' . $detalle_cliente->nombre . ', intente nuevamente</p>'
                . '</div>';
        }
        return [
            'msg' => $msg,
            'id_cliente_pedido_especificacion' => $objClientePedidoEspecificacion->id_cliente_pedido_especificacion
        ];
    }

    public function verificar_pedido_especificacion(Request $request)
    {
        $cliente_especificacion = ClientePedidoEspecificacion::where([
            ['id_cliente', $request->id_cliente],
            ['id_especificacion', $request->id_especificacion]
        ])->select('id_cliente_pedido_especificacion')->first();
        $existDetallePedido = 0;
        if ($cliente_especificacion != null)
            $existDetallePedido = DetallePedido::where('id_cliente_especificacion', $cliente_especificacion->id_cliente_pedido_especificacion)->count();
        return $existDetallePedido;
    }

    public function delete_asignacion_especificacion(Request $request)
    {
        $clienteEspecificacion = DB::table('cliente_pedido_especificacion')->where([
            ['id_especificacion', $request->id_especificacion],
            ['id_cliente', $request->id_cliente]
        ])->first();

        ClientePedidoEspecificacion::destroy($clienteEspecificacion->id_cliente_pedido_especificacion);
        $detalle_cliente = getDatosCliente($request->id_cliente)->select('nombre')->first();
        return '<div class="alert alert-success text-center">' .
            '<p> Se ha eliminado la especificación al cliente ' . $detalle_cliente->nombre . ', con éxito</p>'
            . '</div>';
    }

    public function nueva_especificacion(Request $request)
    {
        return view('adminlte.gestion.postcocecha.especificacion.form.form_row_especificacion', [
            'cant_row' => $request->cant_rows,
            'plantas' => Planta::where('estado', true)->orderBy('nombre', 'asc')->get(),
            'clasificacion_ramo' => ClasificacionRamo::select('nombre', 'id_clasificacion_ramo')->get(),
            'empaque' => Empaque::where([
                ['tipo', 'C'],
                ['estado', 1]
            ])->select('nombre', 'id_empaque')->get(),
            'presentacion' => Empaque::where([
                ['tipo', 'P'],
                ['estado', 1]
            ])->select('nombre', 'id_empaque')->get(),
            'unidad_medida' => UnidadMedida::where([
                ['tipo', 'L'],
                ['estado', 1]
            ])->get()
        ]);
    }

    public function store_row_especificacion(Request $request)
    {
        $valida = Validator::make(
            $request->all(),
            [
                'arrData' => 'required|Array',
                'modo' => 'required',
            ],
            [
                'modo.required' => 'Debe seleccionar el modo de como quiere que se cree la especificación'
            ]
        );

        if (!$valida->fails()) {

            foreach ($request->arrData as $key => $data) {

                if ($data['id_variedad'] == '') {
                    $variedades = Variedad::where('id_planta', $data['id_planta'])->where('estado', 1)->select('id_variedad')->get()->pluck('id_variedad');
                } else {
                    $variedades = [$data['id_variedad']];
                }

                if ($data['id_empaque'] == '') {
                    $cajas = Empaque::where([
                        ['tipo', 'C'],
                        ['estado', true]
                    ])->select('id_empaque')->get()->pluck('id_empaque');
                } else {
                    $cajas = [$data['id_empaque']];
                }

                foreach ($variedades as $v) {

                    foreach ($cajas as $c) {

                        $objEspecificacion = new Especificacion;
                        $objEspecificacion->estado = 1;

                        $x = 0;
                        if ($request->modo == 1 && $x == $key)
                            $objEspecificacion->save() ? $y = true : $y = false;

                        if ($request->modo == 0)
                            $objEspecificacion->save() ? $y = true : $y = false;

                        $success = false;
                        $msg = '<div class="alert alert-danger text-center">' .
                            '<p> Ha ocurrido un error al tratar de crear la especificación, intente nuevamente </p>'
                            . '</div>';

                        if ($y) {
                            $modelEspecificacion = Especificacion::orderBy('id_especificacion', 'desc')->first();
                            $objEspecificacionEmpaque = new EspecificacionEmpaque;
                            $objEspecificacionEmpaque->id_especificacion = $modelEspecificacion->id_especificacion;
                            $objEspecificacionEmpaque->id_empaque = $c;
                            $objEspecificacionEmpaque->cantidad = 1;

                            /*if($request->modo == 1 && $x == $key)
                                $objEspecificacionEmpaque->save() ? $z = true : $z = false;

                            if($request->modo == 0)
                                $objEspecificacionEmpaque->save() ? $z = true : $z = false;*/

                            if ($objEspecificacionEmpaque->save()) {
                                $modelEspecificacionEmpaque = EspecificacionEmpaque::orderBy('id_especificacion_empaque', 'desc')->first();
                                $objDetalleEspecificacionEmpaque = new DetalleEspecificacionEmpaque;
                                $objDetalleEspecificacionEmpaque->id_especificacion_empaque = $modelEspecificacionEmpaque->id_especificacion_empaque;
                                $objDetalleEspecificacionEmpaque->id_variedad = $v;
                                $objDetalleEspecificacionEmpaque->id_clasificacion_ramo = $data['id_clasificacion_ramo_'];
                                $objDetalleEspecificacionEmpaque->cantidad = $data['ramos_x_caja'];
                                $objDetalleEspecificacionEmpaque->id_empaque_p = $data['id_presentacion'];
                                $objDetalleEspecificacionEmpaque->tallos_x_ramos = $data['tallos_x_ramo'];
                                $objDetalleEspecificacionEmpaque->longitud_ramo = $data['longitud'];
                                $objDetalleEspecificacionEmpaque->id_unidad_medida = $data['id_unidad_medida'];

                                if ($objDetalleEspecificacionEmpaque->save()) {
                                    //$modelDetalleEspecificacionEmpaque = DetalleEspecificacionEmpaque::where('id_detalle_especificacionempaque','desc')->first();
                                    $success = true;
                                    $msg = '<div class="alert alert-success text-center">' .
                                        '<p> Se ha agregado la especificación </p>'
                                        . '</div>';
                                    //bitacora('detalle_especificacion_empaque', $modelDetalleEspecificacionEmpaque->id_detalle_especificacionempaque, 'I', 'Inserción satisfactoria de un nuevo detalle de especificación de empaque');

                                    if (isset($request->id_cliente)) {
                                        $resAsignacionEspecificacion = $this->sotre_asignacion_especificacion(new Request([
                                            'id_cliente' => $request->id_cliente,
                                            'id_especificacion' => $modelEspecificacion->id_especificacion
                                        ]));
                                    }
                                } else {
                                    EspecificacionEmpaque::destroy($modelEspecificacionEmpaque->id_especificacion_empaque);
                                    Especificacion::destroy($modelEspecificacion->id_especificacion);
                                }
                            } else {
                                Especificacion::destroy($modelEspecificacion->id_especificacion);
                            }
                        }
                        $x++;
                    }
                }
            }
        } else {
            $success = false;
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }

        return [
            'mensaje' => $msg,
            'success' => $success,
            'resAsignacionEspecificacion' => isset($resAsignacionEspecificacion) ? $resAsignacionEspecificacion : null,
            'idDetEspEmp' => isset($objDetalleEspecificacionEmpaque) ? $objDetalleEspecificacionEmpaque->id_detalle_especificacionempaque : null
        ];
    }

    public function delete_row_especificacion(Request $request)
    {
        DB::beginTransaction();

        try {

            $cpe = ClientePedidoEspecificacion::find($request->id_cliente_pedido_especificacion);

            $esp = $cpe->id_especificacion;

            ClientePedidoEspecificacion::destroy($cpe->id_cliente_pedido_especificacion);
            Especificacion::destroy($esp);

            $msg = '<div class="alert alert-success text-center">' .
                '<p> Se ha eliminado la especificación </p>'
                . '</div>';
            $success = true;

            DB::commit();
        } catch (\Exception $e) {

            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> hubo un error la especificación, intente nuevamente ' . $e->getMessage() . ' </p>'
                . '</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg
        ];
    }

    public function actualizar_row_especificacion(Request $request)
    {
        $valida = Validator::make(
            $request->all(),
            [
                'id_presentacion' => 'required|exists:empaque,id_empaque',
                'ramos_x_caja' => 'required|numeric|min:1',
                'tallos_x_ramo' => 'required|numeric|min:1',
                'id_cliente_pedido_especificacion' => 'required|exists:cliente_pedido_especificacion,id_cliente_pedido_especificacion'
            ],
            [
                'id_presentacion.required' => 'Debe seleccionar una presentación',
                'id_presentacion.exists' => 'No exite el empaque',
                'ramos_x_caja.required' => 'Debe ingresar la cantidad de ramos por caja',
                'ramos_x_caja.numeric' => 'Los ramos por cajas deben ser un número',
                'ramos_x_caja.min' => 'Los ramos por caja deben ser mínimo 1',
                'tallos_x_ramo.required' => 'Debe ingresar la cantidad de tallos por ramo',
                'tallos_x_ramo.numeric' => 'Los tallos por ramo deben ser un número',
                'tallos_x_ramo.min' => 'Los tallos por ramo deben ser mínimo 1',
                'id_cliente_pedido_especificacion.required' => 'No se obtuvo la relación de la especificación',
            ]
        );

        if (!$valida->fails()) {

            DB::beginTransaction();

            try {

                $idDetEspEMp = ClientePedidoEspecificacion::join('especificacion as esp', 'cliente_pedido_especificacion.id_especificacion', 'esp.id_especificacion')
                    ->join('especificacion_empaque as esp_emp', 'esp.id_especificacion', 'esp_emp.id_especificacion')
                    ->join('detalle_especificacionempaque as det_esp_emp', 'esp_emp.id_especificacion_empaque', 'det_esp_emp.id_especificacion_empaque')
                    ->where('id_cliente_pedido_especificacion', $request->id_cliente_pedido_especificacion)
                    ->select('det_esp_emp.id_detalle_especificacionempaque')->first();

                $detEspEmp = DetalleEspecificacionEmpaque::find($idDetEspEMp->id_detalle_especificacionempaque);
                $detEspEmp->id_empaque_p = $request->id_presentacion;
                $detEspEmp->tallos_x_ramos = $request->tallos_x_ramo;
                $detEspEmp->cantidad = $request->ramos_x_caja;
                $detEspEmp->save();

                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p>Especificacion modificada</p>'
                    . '</div>';

                DB::commit();
            } catch (\Exception $e) {

                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<p> Hubo un error actualizando la especificación ' . $e->getMessage() . '</p>'
                    . '</div>';
            }
        } else {
            $success = false;
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }

        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function cambiar_estado(Request $request)
    {
        $model = ClientePedidoEspecificacion::All()
            ->where('id_cliente', $request->cliente)
            ->where('id_especificacion', $request->esp)
            ->first();
        $model->estado = $model->estado == 1 ? 0 : 1;
        $model->save();
        return [
            'estado' => $model->estado
        ];
    }

    public function seleccionar_variedad_especificacion(Request $request)
    {
        return Variedad::where('id_planta', $request->id_planta)->where('estado', 1)->orderBy('nombre', 'asc')->get();
    }

    public function descargar_especificaciones(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        //---------------------- EXCEL --------------------------------------//
        $objPHPExcel = new PHPExcel;

        //--------------------------- GUARDAR EL EXCEL -----------------------

        $objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(12);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");

        $objPHPExcel->removeSheetByIndex(0); //Eliminar la hoja inicial por defecto

        $this->excel_especificaciones($objPHPExcel, $request);

        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="Especificaciones.xlsx"');
        header("Content-Transfer-Encoding: binary");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
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

    public function excel_especificaciones($objPHPExcel, $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $tipo = $request->has('tipo') ? $request->tipo : '';

        $listado = DB::table('especificacion as esp')
            ->join('especificacion_empaque as ee', 'esp.id_especificacion', 'ee.id_especificacion')
            ->join('detalle_especificacionempaque as deesp', function ($j) {

                $j->on('ee.id_especificacion_empaque', 'deesp.id_especificacion_empaque')->where('deesp.estado', true);
            })->join('variedad as v', function ($j) use ($request) {

                $j->on('deesp.id_variedad', 'v.id_variedad');

                if (isset($request->variedad)) {

                    $j->where('v.id_variedad', $request->variedad);
                } else if (isset($request->planta)) {

                    $j->where('v.id_planta', $request->planta);
                }
            })->join('planta as p', 'v.id_planta', 'p.id_planta')
            ->join('clasificacion_ramo as cr', 'deesp.id_clasificacion_ramo', 'cr.id_clasificacion_ramo')
            ->join('empaque as presentacion', 'presentacion.id_empaque', 'deesp.id_empaque_p')
            ->join('unidad_medida as um', 'deesp.id_unidad_medida', 'um.id_unidad_medida')
            ->where([
                ['p.estado', true],
                ['v.estado', true],
                ['esp.estado', true],
                ['esp.creada', 'PRE-ESTABLECIDA'],
                ['esp.tipo', 'N'],

            ])->select(
                'esp.id_especificacion',
                'p.nombre as variedad',
                'v.nombre as color',
                'cr.nombre as calibre',
                'deesp.cantidad as ramos_x_caja',
                'presentacion.nombre as presentacion',
                'deesp.tallos_x_ramos',
                'deesp.longitud_ramo as longitud',
                'um.siglas as unidad',
                DB::raw("(
                SELECT GROUP_CONCAT(dc.nombre SEPARATOR ' - ') FROM detalle_cliente AS dc
                INNER JOIN cliente_pedido_especificacion as cpe2 ON dc.id_cliente = cpe2.id_cliente AND cpe2.estado=true AND cpe2.id_especificacion = esp.id_especificacion
                INNER JOIN cliente as c2 ON c2.id_cliente = dc.id_cliente AND c2.estado=true
                WHERE dc.estado= true " . (isset($request->cliente) ? ' AND cpe2.id_cliente = ' . $request->cliente . '' : '') . "
            ) as clientes")
            );

        if ($tipo == 'NINTANGA') {

            $listado->join('empaque as e', 'e.id_empaque', 'ee.id_empaque')
                ->where(DB::raw("UPPER(e.nombre)"), 'like', '%' . strtoupper($tipo) . '%');
        } else if ($tipo == 'ESPECIALES') {

            $listado->join('empaque as e', 'e.id_empaque', 'ee.id_empaque')
                ->where(DB::raw("UPPER(e.nombre)"), 'not like', '%NINTANGA%');
        }

        if (isset($request->cliente)) {

            $listado->join('cliente_pedido_especificacion as cpe', function ($j) use ($request) {

                $j->on('cpe.id_especificacion', 'esp.id_especificacion')->where([
                    ['cpe.estado', true],
                    ['cpe.id_cliente', $request->cliente]
                ]);
            });
        }

        $listado = $listado->orderBy('esp.id_especificacion', 'desc')->get();

        if (count($listado) > 0) {
            $objSheet = new PHPExcel_Worksheet($objPHPExcel, 'Especificaciones');
            $objPHPExcel->addSheet($objSheet, 0);

            $objSheet->mergeCells('A1:I1');
            $objSheet->getStyle('A1:I1')->getFont()->setBold(true)->setSize(12);
            $objSheet->getStyle('A1:I1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objSheet->getStyle('A1:I1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('CCFFCC');
            $objSheet->getCell('A1')->setValue('LISTADO DE ESPECIFICACIONES');

            $objSheet->getCell('A3')->setValue('VARIEDAD');
            $objSheet->getCell('B3')->setValue('COLOR');
            $objSheet->getCell('C3')->setValue('CALIBRE');
            $objSheet->getCell('D3')->setValue('RAMOS POR CAJA');
            $objSheet->getCell('E3')->setValue('PRESENTACIÓN');
            $objSheet->getCell('F3')->setValue('TALLOS POR RAMO ');
            $objSheet->getCell('G3')->setValue('LONGITUD');
            $objSheet->getCell('H3')->setValue('UNIDAD');
            $objSheet->getCell('I3')->setValue('CLIENTES');

            $objSheet->getStyle('A3:I3')->getFont()->setBold(true)->setSize(12);

            $objSheet->getStyle('A3:I3')
                ->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)
                ->getColor()->setRGB(PHPExcel_Style_Color::COLOR_BLACK);

            $objSheet->getStyle('A3:I3')
                ->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()->setRGB('CCFFCC');

            //--------------------------- LLENAR LA TABLA ---------------------------------------------
            for ($i = 0; $i < sizeof($listado); $i++) {

                $objSheet->getStyle('A' . ($i + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objSheet->getStyle('B' . ($i + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objSheet->getStyle('C' . ($i + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objSheet->getStyle('D' . ($i + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objSheet->getStyle('E' . ($i + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objSheet->getStyle('F' . ($i + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objSheet->getStyle('G' . ($i + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objSheet->getStyle('H' . ($i + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objSheet->getStyle('I' . ($i + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                $objSheet->getCell('A' . ($i + 4))->setValue($listado[$i]->variedad);
                $objSheet->getCell('B' . ($i + 4))->setValue($listado[$i]->color);
                $objSheet->getCell('C' . ($i + 4))->setValue($listado[$i]->calibre);
                $objSheet->getCell('D' . ($i + 4))->setValue($listado[$i]->ramos_x_caja);
                $objSheet->getCell('E' . ($i + 4))->setValue($listado[$i]->presentacion);
                $objSheet->getCell('F' . ($i + 4))->setValue($listado[$i]->tallos_x_ramos);
                $objSheet->getCell('G' . ($i + 4))->setValue($listado[$i]->longitud);
                $objSheet->getCell('H' . ($i + 4))->setValue($listado[$i]->unidad);
                $objSheet->getCell('I' . ($i + 4))->setValue($listado[$i]->clientes == '' ? 'Sin asignación' : $listado[$i]->clientes);
            }

            $objSheet->getColumnDimension('A')->setAutoSize(true);
            $objSheet->getColumnDimension('B')->setAutoSize(true);
            $objSheet->getColumnDimension('C')->setAutoSize(true);
            $objSheet->getColumnDimension('D')->setAutoSize(true);
            $objSheet->getColumnDimension('E')->setAutoSize(true);
            $objSheet->getColumnDimension('F')->setAutoSize(true);
            $objSheet->getColumnDimension('G')->setAutoSize(true);
            $objSheet->getColumnDimension('H')->setAutoSize(true);
            $objSheet->getColumnDimension('i')->setAutoSize(true);
        } else {
            return '<div>No se han encontrado coincidencias para exportar</div>';
        }
    }
}
