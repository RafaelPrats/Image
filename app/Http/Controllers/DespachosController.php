<?php

namespace yura\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Color;
use PHPExcel_Style_Fill;
use PHPExcel_Worksheet;
use PHPExcel_Style_Border;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Modelos\Camion;
use yura\Modelos\Conductor;
use yura\Modelos\Despacho;
use yura\Modelos\DetalleDespacho;
use yura\Modelos\Pedido;
use yura\Modelos\Submenu;
use yura\Modelos\Transportista;
use yura\Modelos\Variedad;
use Validator;
use Barryvdh\DomPDF\Facade as PDF;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\DetallePedido;
use yura\Modelos\Envio;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;
use yura\Jobs\DividirMarcacionesStandigs;
use yura\Jobs\UnificarPedidos;
use yura\Modelos\AgenciaCarga;
use yura\Modelos\DatosExportacion;
use yura\Modelos\DetalleEnvio;
use yura\Modelos\DetalleEspecificacionEmpaque;
use yura\Modelos\DetallePedidoDatoExportacion;
use yura\Modelos\DistribucionMixtos;
use yura\Modelos\Planta;

class DespachosController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.postcocecha.despachos.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'annos' => DB::table('semana as s')
                ->select('s.anno')->distinct()
                ->where('s.estado', '=', 1)->orderBy('s.anno')->get(),
            'variedades' => Variedad::All()->where('estado', '=', 1),
            'clientes' => \DB::table('cliente as c')
                ->join('detalle_cliente as dc', 'c.id_cliente', '=', 'dc.id_cliente')
                ->orderBy('nombre', 'asc')
                ->where('dc.estado', 1)->get(),
            'unitarias' => getUnitarias(),
            'empresas' => getConfiguracionEmpresa(null, true),
            'agenciasCarga' => AgenciaCarga::get()
        ]);
    }

    public function update_despacho_detalle(Request $request)
    {
        if (!empty($request->id_despacho)) {
            $detalleDespacho = DetalleDespacho::find($request->id_detalle_despacho);
            $detalleDespacho->id_despacho = $request->id_despacho;
            $updated = $detalleDespacho->save();
            $response = [
                'success' => $updated
            ];
            return response()->json($response);
        }
    }
    public function listar_resumen_pedidos(Request $request)
    {
        $listado = [];
        $ramos_x_variedad = [];
        $listadoVariedades = [];

        $ini_timer = date('Y-m-d H:i:s');
        if ($request->desde != '') {
            $listado = DB::table('pedido as p')
                ->join('cliente as c', 'c.id_cliente', 'p.id_cliente')
                ->join('detalle_cliente as dc', 'dc.id_cliente', 'p.id_cliente')
                ->join('detalle_pedido as dp', 'p.id_pedido', 'dp.id_pedido')
                ->leftJoin('detalle_despacho as dd', function ($join) {
                    $join->on('dd.id_pedido', '=', 'dp.id_pedido')
                        ->whereRaw('dd.id_detalle_despacho = (SELECT MAX(id_detalle_despacho) FROM detalle_despacho WHERE id_pedido = dp.id_pedido)');
                })
                ->leftJoin('agencia_carga as ac', 'ac.id_agencia_carga', 'dp.id_agencia_carga')
                ->leftJoin('despacho as d', 'd.id_despacho', 'dd.id_despacho')
                ->select(
                    'p.*',
                    'dc.nombre',
                    'dp.id_agencia_carga',
                    'd.id_despacho',
                    'd.n_despacho',
                    'dd.id_detalle_despacho'
                )->distinct()
                ->where('dc.estado', 1)
                ->where('c.estado', 1)
                ->where('p.estado', 1)
                ->where(function ($w) use ($request) {

                    if (isset($request->hasta)) {
                        $w->whereBetween('p.fecha_pedido', [$request->desde, $request->hasta]);
                    } else {
                        $w->where('p.fecha_pedido', $request->desde);
                    }

                    if (isset($request->tipo_pedido) && $request->tipo_pedido != '') {
                        $w->where('tipo_pedido', $request->tipo_pedido);
                    }

                    if (isset($request->id_marcacion) && $request->id_marcacion != '') {

                        $w->whereRaw("(SELECT COUNT(*) > 0 FROM detalle_pedido AS dp2
                    INNER JOIN detallepedido_datoexportacion AS dpde ON dpde.id_detalle_pedido = dp2.id_detalle_pedido
                    WHERE dp2.id_detalle_pedido = dp.id_detalle_pedido AND dpde.valor = '" . $request->id_marcacion . "')", []);
                    }
                })->where(function ($query) use ($request) {
                    if (isset($request->id_cliente))
                        $query->where('p.id_cliente', $request->id_cliente);

                    if ($request->id_configuracion_empresa != "")
                        $query->where('p.id_configuracion_empresa', $request->id_configuracion_empresa);

                    if (isset($request->id_agencia_carga))
                        $query->where('dp.id_agencia_carga', $request->id_agencia_carga);
                });

            if (isset($request->id_planta) && !isset($request->variedad)) {
                $p = Planta::find($request->id_planta);
                $listadoVariedades = $p->variedades->pluck('id_variedad')->toArray();
            }

            if (isset($request->id_planta) && isset($request->id_variedad)) {
                $v = Variedad::where([
                    ['id_variedad', $request->id_variedad],
                    ['id_planta', $request->id_planta]
                ])->first();
                $listadoVariedades = [$v->id_variedad];
            }

            if (isset($listadoVariedades) && count($listadoVariedades)) {

                $listado->whereRaw("(SELECT COUNT(*) > 0 FROM cliente_pedido_especificacion AS cpe
                INNER JOIN especificacion AS esp ON cpe.id_especificacion = esp.id_especificacion
                INNER JOIN especificacion_empaque as esp_emp ON esp.id_especificacion = esp_emp.id_especificacion
                INNER JOIN detalle_especificacionempaque as det_esp_emp ON esp_emp.id_especificacion_empaque = det_esp_emp.id_especificacion_empaque
                WHERE cpe.id_cliente_pedido_especificacion = dp.id_cliente_especificacion AND det_esp_emp.id_variedad IN (" . implode(',', $listadoVariedades) . "))", []);
            }

            // if ($request->opciones) {
            //     $listado = $listado->orderBy('fecha_pedido', 'asc')->orderBy('dc.nombre', 'asc')->orderBy('packing', 'desc')->get();
            // } else {
            //     $listado = $listado->orderBy('fecha_pedido', 'asc')->orderBy('dp.id_agencia_carga', 'asc')->get();
            // }
            $listado = $listado->orderBy('dd.id_despacho', 'asc')->orderBy('ac.orden', 'asc')->get();

            $ids_pedidos = $listado->pluck('id_pedido')->toArray();

            $detalleEspecificaciones = DetalleEspecificacionEmpaque::join('variedad as v', 'detalle_especificacionempaque.id_variedad', 'v.id_variedad')
                ->join('especificacion_empaque as ee', 'detalle_especificacionempaque.id_especificacion_empaque', 'ee.id_especificacion_empaque')
                ->join('cliente_pedido_especificacion as cpe', 'ee.id_especificacion', 'cpe.id_especificacion')
                ->join('detalle_pedido as dp', 'cpe.id_cliente_pedido_especificacion', 'dp.id_cliente_especificacion')
                ->whereIn('dp.id_pedido', $ids_pedidos)
                ->where(function ($w) use ($request) {

                    if (isset($request->id_variedad) && $request->id_variedad != '') {

                        $w->where('detalle_especificacionempaque.id_variedad', $request->id_variedad)
                            ->where('v.id_planta', $request->id_planta);
                    } else if (isset($request->id_planta) && $request->id_planta != '') {

                        $planta = Planta::find($request->id_planta);
                        $w->whereIn('detalle_especificacionempaque.id_variedad', $planta->variedades->pluck('id_variedad')->toArray())
                            ->where('v.id_planta', $request->id_planta);
                    }
                    if (isset($request->id_cliente) && $request->id_cliente != '') {

                        $w->where('cpe.id_cliente', $request->id_cliente);
                    }
                })->groupBy(
                    'detalle_especificacionempaque.id_detalle_especificacionempaque',
                    'dp.id_pedido',
                    'dp.id_detalle_pedido',
                    'dp.cantidad',
                    'detalle_especificacionempaque.id_variedad',
                    'detalle_especificacionempaque.id_clasificacion_ramo',
                    'detalle_especificacionempaque.tallos_x_ramos',
                    'detalle_especificacionempaque.longitud_ramo',
                    'detalle_especificacionempaque.id_unidad_medida'
                )
                ->select(
                    'detalle_especificacionempaque.id_detalle_especificacionempaque',
                    'dp.id_detalle_pedido',
                    'dp.cantidad as piezas',
                    'detalle_especificacionempaque.id_variedad',
                    'detalle_especificacionempaque.id_clasificacion_ramo',
                    'detalle_especificacionempaque.tallos_x_ramos',
                    'detalle_especificacionempaque.longitud_ramo',
                    'detalle_especificacionempaque.id_unidad_medida',
                    DB::raw('sum(detalle_especificacionempaque.tallos_x_ramos * detalle_especificacionempaque.cantidad * ee.cantidad * dp.cantidad) as tallos'),
                    DB::raw('sum(detalle_especificacionempaque.cantidad * ee.cantidad * dp.cantidad) as ramos'),
                )->get();


            foreach ($detalleEspecificaciones as $det_esp_emp) {

                $ramosDistribucion = 0;
                $tallosDistribucion = 0;

                if (isset($request->id_cliente) && $request->id_cliente != '' && isset($request->id_planta) && $request->id_planta != '' && isset($request->id_variedad) && $request->id_variedad != '') {

                    $variedad = Variedad::find($request->id_variedad);

                    $distribucionAssorted = DistribucionMixtos::where('ramos', '>', 0)
                        ->whereBetween('fecha', [opDiasFecha('-', 1, $request->desde), opDiasFecha('-', 1, $request->hasta)])
                        ->where('id_cliente', $request->id_cliente)
                        ->where('id_planta', $request->id_planta)
                        ->where('siglas', $variedad->siglas)->get();

                    $ramosDistribucion = $distribucionAssorted->sum('ramos');
                    $tallosDistribucion = $distribucionAssorted->sum('tallos');
                }

                $getRamosXCajaModificado = getRamosXCajaModificado($det_esp_emp->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);
                $ramos = isset($getRamosXCajaModificado) ? ($getRamosXCajaModificado->cantidad * $det_esp_emp->piezas) : $det_esp_emp->ramos;
                $tallos = isset($getRamosXCajaModificado) ? ($getRamosXCajaModificado->cantidad * $det_esp_emp->tallos_x_ramos * $det_esp_emp->piezas) : $det_esp_emp->tallos;

                $ramos_x_variedad[$det_esp_emp->variedad->planta->nombre . ' ' . $det_esp_emp->variedad->nombre . ' ' . $det_esp_emp->longitud_ramo . ' ' . $det_esp_emp->unidad_medida->siglas][] = [
                    'tallos' =>  $tallos + $ramosDistribucion,
                    'ramos' => $ramos + $tallosDistribucion
                ];
            }

            $variedades = DB::table('detalle_especificacionempaque as dee')
                ->join('especificacion_empaque as ee', 'dee.id_especificacion_empaque', 'ee.id_especificacion_empaque')
                ->join('cliente_pedido_especificacion as cpe', 'ee.id_especificacion', 'cpe.id_especificacion')
                ->join('detalle_pedido as dp', 'cpe.id_cliente_pedido_especificacion', 'dp.id_cliente_especificacion')
                ->select('dee.id_variedad')->distinct()
                ->whereIn('dp.id_pedido', $ids_pedidos)->get();
        }
        $fin_timer = date('Y-m-d H:i:s');
        //dd('ok', difFechas($fin_timer, $ini_timer));

        $datos = [
            'listado' => $listado,
            'fecha' => $request->desde,
            'ramos_x_variedad' => $ramos_x_variedad,
            'variedades' => $variedades,
            'opciones' => $request->opciones,
            'id_configuracion_empresa' => $request->id_configuracion_empresa,
            'estado' =>  $request->estado,
            'id_planta' => $request->id_planta,
            'id_variedad' => $request->id_variedad,
            'id_marcacion' => $request->id_marcacion,
            'listadoVariedades' => $listadoVariedades,
            'envio' => Envio::class,
            'carbon' => Carbon::class,
            'desde' => $request->desde,
            'hasta' => $request->hasta,
        ];
        return view('adminlte.gestion.postcocecha.despachos.partials.listado', $datos);
    }

    public function crear_despacho(Request $request)
    {
        $arr_data_pedido = [];
        foreach ($request->pedidos as $id_pedido) {
            $arr_data_pedido[] = Pedido::where('id_pedido', $id_pedido)
                ->with(['detalles.agencia_carga'])
                ->first();
        }

        // Ordenar los resultados por agencia_carga.orden
        usort($arr_data_pedido, function ($a, $b) {
            $orden_a = $a->detalles[0]->agencia_carga->orden;
            $orden_b = $b->detalles[0]->agencia_carga->orden;

            return $orden_a <=> $orden_b;
        });

        if (!empty($request->pedidos)) {
            return view('adminlte.gestion.postcocecha.despachos.form.despacho_listado', [
                'pedidos' => $arr_data_pedido,
                'datos_responsables' => Despacho::where('id_despacho', 'desc')->first(),
                'id_pedidos' => $request->pedidos
            ]);
        } else {
        }
    }

    public function list_camiones_conductores(Request $request)
    {
        return response()->json([
            'camiones' => Camion::where([
                ['id_transportista', $request->id_transportista],
                ['estado', 1]
            ])->get(),
            'conductores' => Conductor::where([
                ['id_transportista', $request->id_transportista],
                ['estado', 1]
            ])->get()
        ]);
    }

    public function list_placa_camion(Request $request)
    {
        return Camion::where([
            ['id_camion', $request->id_camion],
            ['estado', 1]
        ])->select('placa')->first();
    }

    public function store_despacho(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'data_despacho.*.fecha_despacho' => 'required',
            //'data_despacho.*.firma_id_transportista' => 'required',
            'data_despacho.*.id_camion' => 'required',
            'data_despacho.*.id_conductor' => 'required',
            //'data_despacho.*.id_cuarto_frio' => 'required',
            //'data_despacho.*.id_guardia_turno' => 'required',
            //'data_despacho.*.id_oficina_despacho' => 'required',
            'data_despacho.*.id_transportista' => 'required',
            'data_despacho.*.n_placa' => 'required',
            'data_despacho.*.n_viaje' => 'required',
            //'data_despacho.*.nombre_cuarto_frio' => 'required',
            //'data_despacho.*.nombre_guardia_turno' => 'required',
            'data_despacho.*.nombre_oficina_despacho' => 'required',
            'data_despacho.*.nombre_transportista' => 'required',
            //'data_despacho.*.arr_sellos' => 'required|Array',
            'data_despacho.*.semana' => 'required',
            //'data_despacho.*.correo_oficina_despacho'  => 'required',
        ], [
            'data_despacho.*.fecha_despacho.required' => 'Debe colocar la fecha de despacho para el camion',
            'data_despacho.*.id_camion.required' => 'Debe seleccionar el camion',
            'data_despacho.*.n_placa.required' =>  'Debe escribir la placa del camion',
            'data_despacho.*.semana.required' => 'Debe escribir la semana',
            //'data_despacho.*.correo_oficina_despacho.required' => 'Debe escribir el correo de la persona de la oficina de despacho',
            'data_despacho.*.nombre_transportista.required' => 'Debe escribir el nombre del transportista',
            'data_despacho.*.nombre_oficina_despacho.required' => 'Debe escribir el nombre de la persona de la oficina de despacho',
            //'data_despacho.*.nombre_guardia_turno.required' => 'Debe escribir el nombre del guardia de turno',
            //'data_despacho.*.id_guardia_turno.required' => 'Debe escribir la identificacion del guardia de turno',
            //'data_despacho.*.nombre_cuarto_frio.required' => 'Debe escribir el nombre de la persona del cuarto frio',
            //'data_despacho.*.id_cuarto_frio.required' => 'Debe escribir la identificacion de la persona del cuarto frio',
            'data_despacho.*.id_conductor.required' => 'Debe seleccionar el conductor del camion',
            'data_despacho.*.id_transportista.required' => 'Debe seleccionar una agencia de transporte'
        ]);

        if (!$valida->fails()) {

            DB::beginTransaction();

            try {

                $msg = '';
                foreach ($request->data_despacho as $despacho) {

                    $s = '';
                    $id_camion_insertado = '';
                    $camion_existe = Camion::where('placa', $despacho['n_placa'])->exists();
                    $camion_modelo = Camion::select('modelo')->distinct()
                        ->where('id_camion', $despacho['id_camion'])
                        ->first();

                    if (!$camion_existe) {
                        $nuevoCamion = new Camion();
                        $nuevoCamion->id_transportista = $despacho['id_transportista'];
                        $nuevoCamion->placa = $despacho['n_placa'];
                        $nuevoCamion->modelo = $camion_modelo->modelo;
                        $nuevoCamion->save();

                        $id_camion_insertado = $nuevoCamion->id;
                        // Utiliza $id_camion_insertado según tus necesidades
                    }

                    if (isset($despacho['arr_sellos'])) {

                        foreach ($despacho['arr_sellos'] as $sellos)
                            $s .= implode(',', $sellos) . '/';
                    }

                    $distribucion = substr($despacho['distribucion'], 0, -1);
                    $objDespacho = new Despacho;

                    $idDespacho = Despacho::orderBy('id_despacho', 'desc')->first();

                    $objDespacho->id_despacho = isset($idDespacho) ? $idDespacho->id_despacho + 1 : 1;
                    $objDespacho->id_transportista = $despacho['id_transportista'];
                    $objDespacho->id_camion = $id_camion_insertado != '' ? $id_camion_insertado : $despacho['id_camion'];
                    $objDespacho->id_conductor = $despacho['id_conductor'];
                    $objDespacho->fecha_despacho = $despacho['fecha_despacho'];
                    $objDespacho->sello_salida = $despacho['sello_salida'];
                    $objDespacho->semana = $despacho['semana'];
                    $objDespacho->rango_temp = $despacho['rango_temp'];
                    $objDespacho->n_viaje = $despacho['n_viaje'];
                    $objDespacho->hora_salida = $despacho['horas_salida'];
                    $objDespacho->temp = $despacho['temperatura'];
                    $objDespacho->kilometraje = $despacho['kilometraje'];
                    $objDespacho->sellos = substr($s, 0, -1);
                    //$objDespacho->sello_adicional =$despacho['sello_adicional'];
                    $objDespacho->horario = $despacho['horario'];
                    $objDespacho->resp_ofi_despacho = $despacho['nombre_oficina_despacho'];
                    $objDespacho->id_resp_ofi_despacho = $despacho['id_oficina_despacho'];
                    //$objDespacho->aux_cuarto_fri = $despacho['nombre_cuarto_frio'];
                    //$objDespacho->id_aux_cuarto_fri = $despacho['id_cuarto_frio'];
                    // $objDespacho->guardia_turno = $despacho['nombre_guardia_turno'];
                    // $objDespacho->id_guardia_turno = $despacho['id_guardia_turno'];
                    $objDespacho->asist_comercial_ext = $despacho['nombre_asist_comercial'];
                    $objDespacho->id_asist_comrecial_ext = $despacho['id_asist_comercial'];
                    $objDespacho->resp_transporte = $despacho['nombre_transportista'];
                    // $objDespacho->mail_resp_ofi_despacho = $despacho['correo_oficina_despacho'];
                    $distribucion = explode(";", $distribucion);
                    $idPedido = explode("|", $distribucion[0])[0];
                    $empresa = getPedido($idPedido)->empresa;
                    $objDespacho->id_configuracion_empresa = $empresa->id_configuracion_empresa;
                    $objDespacho->n_despacho = getSecuenciaDespacho($empresa);

                    if ($objDespacho->save()) {

                        /* bitacora('despacho', $modelDespacho->id_despacho, 'I', 'Insercion satisfactoria de un nuevo despacho'); */

                        foreach ($distribucion as $d) {
                            $idDetalleDespacho = DetalleDespacho::orderBy('id_detalle_despacho', 'desc')->first();

                            $objDetalleDespacho = new DetalleDespacho;

                            $objDetalleDespacho->id_detalle_despacho = isset($idDetalleDespacho) ? $idDetalleDespacho->id_detalle_despacho + 1 : 1;
                            $objDetalleDespacho->id_despacho = $objDespacho->id_despacho;
                            $objDetalleDespacho->id_pedido = explode("|", $d)[0];
                            $objDetalleDespacho->cantidad = explode("|", $d)[1];

                            if ($objDetalleDespacho->save()) {
                                /* $modelDetalleDespacho = DetalleDespacho::all()->last();
                                bitacora('detalle_despacho', $modelDetalleDespacho->id_detalle_despacho, 'I', 'Insercion satisfactoria de un nuevo detalle de despacho');
                                $success = true; */
                            } else {
                                DetalleDespacho::where('id_despacho', $objDespacho->id_despacho)->delete();
                                Despacho::destroy($objDespacho->id_despacho);
                                $msg = '<div class="alert alert-warning text-center">' .
                                    '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>'
                                    . '</div>';
                                $success = false;
                                return [
                                    'mensaje' => $msg,
                                    'success' => $success
                                ];
                            }
                        }
                        $msg .= '<div class="alert alert-success text-center">' .
                            '<p> Se ha guardado el despacho ' . str_pad($objDespacho->n_despacho, 9, "0", STR_PAD_LEFT) . '  exitosamente,
                                <a target="_blank" href="' . url('despachos/descargar_despacho/' . $objDespacho->id_despacho . '') . '">  clic aqui para ver y descargar</a></p>'
                            . '</div>';
                        $success = true;
                        /*$data = [
                            'empresa' => $empresa,
                            'despacho' => Despacho::where('n_despacho',(getSecuenciaDespacho($empresa)-1))
                                ->join('detalle_despacho as dd','despacho.id_despacho','dd.id_despacho')->get()
                        ];

                         PDF::loadView('adminlte.gestion.postcocecha.despachos.partials.pdf_despacho', compact('data'))->setPaper('a4', 'landscape')
                            ->save(env('PATH_PDF_DESPACHOS') . str_pad((getSecuenciaDespacho($empresa)-1), 9, "0", STR_PAD_LEFT) . ".pdf"); */
                    } else {
                        $success = false;
                        $msg = '<div class="alert alert-warning text-center">' .
                            '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>'
                            . '</div>';
                    }
                }

                DB::commit();
            } catch (\Exception $e) {
                $success = false;
                DB::rollBack();
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> Ha ocurrido un problema al guardar la informacion al sistema <br />' . $e->getMessage() . '</p>'
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
            'success' => $success,
        ];
    }

    public function descargar_despacho($id_despacho)
    {
        $despacho = Despacho::where('id_despacho', $id_despacho)->first();
        $detallesOrdenados = $despacho->detalles->sortBy(function ($pedido) {
            $nombreCliente = $pedido->pedido->cliente->detalle()->nombre;
            $ordenAgenciaCarga = $pedido->pedido->detalles[0]->agencia_carga->orden;
            return [$ordenAgenciaCarga, $nombreCliente];
        });
        $despachoOrdenado = $despacho; // Copia el objeto $despacho en $despachoOrdenado
        $despachoOrdenado->detalles = $detallesOrdenados; // Reemplaza los detalles ordenados en $despachoOrdenado
        $data = [
            'empresa' => $despacho->detalles[0]->pedido->empresa,
            'despacho' => $despachoOrdenado
        ];
        PDF::loadView('adminlte.gestion.postcocecha.despachos.partials.pdf_despacho', compact('data'))
            ->setPaper('a4', 'landscape')->save(public_path('pdf/pdf1.pdf'));

        PDF::loadView('adminlte.gestion.postcocecha.despachos.partials.pdf_despacho_firmas', compact('data'))
            ->setPaper('a4', 'portrait')->save(public_path('pdf/pdf2.pdf'));


        $oMerger = PDFMerger::init();

        $oMerger->addPDF(public_path('pdf/pdf1.pdf'), 'all');
        $oMerger->addPDF(public_path('pdf/pdf2.pdf'), 'all');


        $oMerger->merge();
        $oMerger->stream();
    }

    public function ver_despachos(Request $request)
    {
        $listado = Despacho::where('estado', 1)
            ->whereBetween('fecha_despacho', [$request->desde, $request->hasta])
            ->get();
        return view('adminlte.gestion.postcocecha.despachos.partials.despachos', [
            'listado' => $listado
        ]);
    }

    public function update_estado_despachos(Request $request)
    {
        $despacho = Despacho::find($request->id_despacho);
        $despacho->estado = $request->estado == 1 ? 0 : 1;
        if ($despacho->save()) {
            $success = true;
            $msg = '<div class="alert alert-success text-center">' .
                '<p> Se ha actualizado el estado del despacho exitosamente</p>'
                . '</div>';
        } else {
            $success = false;
            $msg = '<div class="alert danger text-center">' .
                '<p> Hubo un error al intentar actualizar el estado del despacho exitosamente</p>'
                . '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function distribuir_despacho(Request $request)
    {

        return view('adminlte.gestion.postcocecha.despachos.partials.distribucion', [
            'transportistas' => Transportista::where('estado', 1)->get(),
            'cant_form' => $request->cant_form,
            'resp_transporte' => Despacho::select('resp_transporte')->get()->last(),
            'pedidos' => Pedido::whereIn('id_pedido', explode(',', $request->pedidos))->get()
        ]);
    }

    public function add_pedido_piezas(Request $request)
    {
        //dump($request->secuencial);
        return view('adminlte.gestion.postcocecha.despachos.partials.add_pedido_piezas', [
            'sec' => $request->secuencial,
            'arr_pedidos' => $request->arr_pedidos,
            'cant_form' => $request->cant_form
        ]);
    }

    public function exportar_pedidos_despacho(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_pedidos_despacho($spread, $request);

        $fileName = "PEDIDOS.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
    }

    public function excel_pedidos_despacho($spread, $request)
    {
        $pedidos = Pedido::join('cliente as c', 'pedido.id_cliente', 'c.id_cliente')
            ->join('detalle_cliente as dc', 'c.id_cliente', 'dc.id_cliente')
            ->where([
                ['pedido.estado', 1],
                ['dc.estado', 1]
            ])->whereBetWeen('fecha_pedido', [$request->fecha_desde, $request->fecha_hasta])
            ->where(function ($w) use ($request) {

                if (isset($request->id_configuracion_empresa))
                    $w->where('id_configuracion_empresa', $request->id_configuracion_empresa);
            })->orderBy('dc.nombre', 'asc')->get();

        setlocale(LC_TIME, "es_ES.UTF-8");

        $totalTallos = 0;
        $montoTotal = 0;
        $totalCajas = 0;

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('PEDIDOS');

        $sheet->getCell('A1')->setValue('FECHA');
        $sheet->getCell('B1')->setValue('ANO');
        $sheet->getCell('C1')->setValue('DIA');
        $sheet->getCell('D1')->setValue('DIA TEXTO');
        $sheet->getCell('E1')->setValue('CLIENTE');
        $sheet->getCell('F1')->setValue('CONSIGNATARIO');
        $sheet->getCell('G1')->setValue('MARCACION');
        $sheet->getCell('H1')->setValue('TALLOS X RAMO');
        $sheet->getCell('I1')->setValue('RAMOS X CAJA');
        $sheet->getCell('J1')->setValue('FLOR');
        $sheet->getCell('K1')->setValue('COLOR');
        $sheet->getCell('L1')->setValue('LONGITUD');
        $sheet->getCell('M1')->setValue('CAJAS');
        $sheet->getCell('N1')->setValue('TIPO DE CAJA');
        $sheet->getCell('O1')->setValue('PRECIO X BUNCHE');
        $sheet->getCell('P1')->setValue('PRESENTACION');
        $sheet->getCell('Q1')->setValue('TOTAL TALLOS');
        $sheet->getCell('R1')->setValue('TOTAL RAMOS');
        $sheet->getCell('S1')->setValue('MES');
        $sheet->getCell('T1')->setValue('SEMANA');
        $sheet->getCell('U1')->setValue('CARGUERA');
        $sheet->getCell('V1')->setValue('TIPO DE PEDIDO');
        $sheet->getCell('W1')->setValue('PRECIO UNITARIO');
        $sheet->getCell('X1')->setValue('PRECIO TOTAL');
        setTextCenterToCeldaExcel($sheet, 'A1:X1');
        setBgToCeldaExcel($sheet, 'A1:X1', '357ca5');
        setColorTextToCeldaExcel($sheet, 'A1:X1', 'ffffff');

        $x = 1;

        foreach ($pedidos as $a => $pedido) {

            if ($pedido->tipo_especificacion === "N") {

                foreach ($pedido->detalles as $det_ped) {

                    foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $y => $esp_emp) {

                        switch (explode('|', $esp_emp->empaque->nombre)[1]) {

                            case '0.5':
                                $caja = 'HB';
                                break;
                            case '0.25':
                                $caja = 'QB';
                                break;
                            default:
                                $caja = 'EB';
                        }

                        foreach ($esp_emp->detalles as $z => $det_esp_emp) {

                            $precio = getPrecioByDetEsp($det_ped->precio, $det_esp_emp->id_detalle_especificacionempaque);
                            $ramos_modificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);
                            $datos_exportacion = [];

                            if (getDatosExportacionByDetPed($det_ped->id_detalle_pedido)->count() > 0)
                                foreach (getDatosExportacionByDetPed($det_ped->id_detalle_pedido) as $dE)
                                    $datos_exportacion[] =  $dE->dato_exportacion->nombre . ': ' . $dE->valor;

                            $sheet->getCell('A' . ($x + 1))->setValue(Carbon::parse($pedido->fecha_pedido)->format('d/m/Y'));
                            $sheet->getCell('B' . ($x + 1))->setValue(Carbon::parse($pedido->fecha_pedido)->format('Y'));
                            $sheet->getCell('C' . ($x + 1))->setValue(Carbon::parse($pedido->fecha_pedido)->format('d'));
                            $sheet->getCell('D' . ($x + 1))->setValue(strtoupper(strftime("%A", gmmktime(12, 0, 0, Carbon::parse($pedido->fecha_pedido)->format('m'), Carbon::parse($pedido->fecha_pedido)->format('d'), Carbon::parse($pedido->fecha_pedido)->format('Y')))));
                            $sheet->getCell('E' . ($x + 1))->setValue($pedido->cliente->detalle()->nombre);
                            $sheet->getCell('F' . ($x + 1))->setValue(isset($pedido->envios[0]) && isset($pedido->envios[0]->consignatario) ? $pedido->envios[0]->consignatario->nombre : "");
                            $sheet->getCell('G' . ($x + 1))->setValue(implode(' ', $datos_exportacion));
                            $sheet->getCell('H' . ($x + 1))->setValue($det_esp_emp->tallos_x_ramos);
                            $sheet->getCell('I' . ($x + 1))->setValue((isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad));
                            $sheet->getCell('J' . ($x + 1))->setValue($det_esp_emp->variedad->planta->nombre);
                            $sheet->getCell('K' . ($x + 1))->setValue($det_esp_emp->variedad->nombre);
                            $sheet->getCell('L' . ($x + 1))->setValue($det_esp_emp->longitud_ramo);
                            $sheet->getCell('M' . ($x + 1))->setValue($y == 0 && $z == 0 ? $det_ped->cantidad : '');
                            if ($y == 0 && $z == 0) $totalCajas += $det_ped->cantidad;
                            $sheet->getCell('N' . ($x + 1))->setValue($y == 0 && $z == 0 ? explode('|', $esp_emp->empaque->nombre)[0] : '');
                            $sheet->getCell('O' . ($x + 1))->setValue('$' . getPrecioByDetEsp($det_ped->precio, $det_esp_emp->id_detalle_especificacionempaque));
                            $sheet->getCell('P' . ($x + 1))->setValue($det_esp_emp->empaque_p->nombre);
                            $sheet->getCell('Q' . ($x + 1))->setValue(number_format($det_esp_emp->tallos_x_ramos * $det_ped->cantidad * (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad), 2, '.', ''));
                            $sheet->getCell('R' . ($x + 1))->setValue((isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad));
                            $totalTallos += $det_esp_emp->tallos_x_ramos * $det_ped->cantidad * (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad);
                            $sheet->getCell('S' . ($x + 1))->setValue(strtoupper(strftime("%B", gmmktime(12, 0, 0, Carbon::parse($pedido->fecha_pedido)->format('m'), Carbon::parse($pedido->fecha_pedido)->format('d'), Carbon::parse($pedido->fecha_pedido)->format('Y')))));
                            $sheet->getCell('T' . ($x + 1))->setValue(Carbon::parse($pedido->fecha_pedido)->weekOfYear);
                            $sheet->getCell('U' . ($x + 1))->setValue($det_ped->agencia_carga->nombre);
                            $sheet->getCell('V' . ($x + 1))->setValue($pedido->tipo_pedido);
                            $sheet->getCell('W' . ($x + 1))->setValue(number_format($det_esp_emp->tallos_x_ramos > 0 ? ($precio / $det_esp_emp->tallos_x_ramos) : 0, 2));
                            $precioTotal = ($precio * (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad) * $det_ped->cantidad);
                            $sheet->getCell('X' . ($x + 1))->setValue($precioTotal);
                            $montoTotal += $precioTotal;

                            $x++;
                        }
                    }
                }
            }
        }

        $sheet->getCell('L' . ($x + 1))->setValue('TOTALES');
        $sheet->getCell('M' . ($x + 1))->setValue(number_format($totalCajas, 2));
        $sheet->getCell('Q' . ($x + 1))->setValue(number_format($totalTallos, 2));
        $sheet->getCell('X' . ($x + 1))->setValue('$' . number_format($montoTotal, 2));

        setBgToCeldaExcel($sheet, 'L' . ($x + 1) . ':X' . ($x + 1), '357ca5');
        setColorTextToCeldaExcel($sheet, 'L' . ($x + 1) . ':X' . ($x + 1), 'ffffff');

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(30);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(18);
        $sheet->getColumnDimension('I')->setWidth(28);
        $sheet->getColumnDimension('J')->setWidth(20);
        $sheet->getColumnDimension('K')->setWidth(27);
        $sheet->getColumnDimension('L')->setWidth(22);
        $sheet->getColumnDimension('M')->setWidth(22);
        $sheet->getColumnDimension('N')->setWidth(22);
        $sheet->getColumnDimension('O')->setWidth(22);
        $sheet->getColumnDimension('P')->setWidth(22);
        $sheet->getColumnDimension('Q')->setWidth(22);
        $sheet->getColumnDimension('R')->setWidth(22);
        $sheet->getColumnDimension('S')->setWidth(22);
        $sheet->getColumnDimension('T')->setWidth(22);
        $sheet->getColumnDimension('U')->setWidth(22);
        $sheet->getColumnDimension('V')->setWidth(22);
        $sheet->getColumnDimension('W')->setWidth(22);
        $sheet->getColumnDimension('X')->setWidth(22);
    }

    public function exportar_excel_listado_despacho(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_listado_pedidos_despacho($spread, $request);

        $fileName = "Disponibilidad_Diaria.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_listado_pedidos_despacho($spread, $request)
    {
        $pedidos = Pedido::join('cliente as c', 'pedido.id_cliente', 'c.id_cliente')
            ->join('detalle_cliente as dc', 'c.id_cliente', 'dc.id_cliente')
            ->select('pedido.*')->distinct()
            ->where('pedido.fecha_pedido', '>=', $request->fecha_pedido)
            ->where('pedido.fecha_pedido', '<=', $request->fecha_pedido_hasta)
            ->where('pedido.id_configuracion_empresa', $request->id_configuracion_empresa)
            ->where('pedido.estado', 1)
            ->where('dc.estado', 1)
            ->orderBy('pedido.fecha_pedido')
            ->orderBy('dc.nombre')
            ->get();

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Despacho de pedidos');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Fecha');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Cliente');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Consignatario');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Factura');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Marcaciones');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Piezas');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Cajas full');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Half');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Cuartos');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Octavos');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'SB');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Agencia de carga');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Facturado por');

        $total_full = 0;
        $total_half = 0;
        $total_cuarto = 0;
        $total_octavo = 0;
        $total_sb = 0;
        $total_piezas_despacho =  0;
        foreach ($pedidos as $p => $pedido) {
            if (!getFacturaAnulada($pedido->id_pedido)) {
                $full = 0;
                $half = 0;
                $cuarto = 0;
                $sexto = 0;
                $octavo = 0;
                $sb = 0;
                if (count($pedido->detalles) > 0) {
                    foreach ($pedido->detalles as $det_tinturado => $det_ped) {
                        foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $m => $esp_emp) {
                            $full += explode("|", $esp_emp->empaque->nombre)[1] * $det_ped->cantidad;
                            switch (explode("|", $esp_emp->empaque->nombre)[1]) {
                                case '0.5':
                                    $half += $det_ped->cantidad;
                                    break;
                                case '0.25':
                                    $cuarto += $det_ped->cantidad;
                                    break;
                                case '0.17':
                                    $sexto += $det_ped->cantidad;
                                    break;
                                case '0.125':
                                    $octavo += $det_ped->cantidad;
                                    break;
                                case '0.0625':
                                    $sb += $det_ped->cantidad;
                                    $total_sb += $det_ped->cantidad;
                                    break;
                            }
                            $piezas_despacho = $half + $cuarto + $sexto + $octavo + $sb;
                        }
                        $datosExportacion = "";
                        if (count(getDatosExportacionCliente($det_ped->id_detalle_pedido)) > 0)
                            foreach (getDatosExportacionCliente($det_ped->id_detalle_pedido) as $de)
                                $datosExportacion .= " " . $de->valor;
                    }
                    //dd($pedido);$row = 1;
                    $row++;
                    $col = 0;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pedido->fecha_pedido);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pedido->cliente->detalle()->nombre);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, isset($pedido->envios[0]) && isset($pedido->envios[0]->consignatario) ? $pedido->envios[0]->consignatario->nombre : '');
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, isset($pedido->envios[0]) && isset($pedido->envios[0]->comprobante->secuencial) ? $pedido->envios[0]->comprobante->secuencial : "");
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $datosExportacion);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $piezas_despacho);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $full);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $half > 0 ? $half : '');
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $cuarto > 0 ? $cuarto : '');
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $octavo > 0 ? $octavo : '');
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $sb > 0 ? $sb : '');
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pedido->detalles[0]->agencia_carga->nombre);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pedido->empresa->nombre);

                    $total_full += $full;
                    $total_half += $half;
                    $total_cuarto += $cuarto;
                    $total_octavo += $octavo;
                    $total_sb += $sb;
                    $total_piezas_despacho += $piezas_despacho;
                }
            }
        }

        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col + 4] . $row);
        $col += 5;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_piezas_despacho);
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_full);
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_half);
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_cuarto);
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_octavo);
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_sb);
        $col += 3;

        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function exportar_pedidos_despacho_cuarto_frio(Request $request)
    {
        //---------------------- EXCEL --------------------------------------
        $objPHPExcel = new PHPExcel;
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(12);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");

        $this->excel_pedidos_despacho_cuarto_frio($objPHPExcel, $request);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="Etiquestas Cajas.xlsx"');
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

    public function excel_pedidos_despacho_cuarto_frio($objPHPExcel, $request)
    {

        $pedidos = Pedido::where([['fecha_pedido', $request->fecha_pedido], ['pedido.estado', 1], ['dc.estado', 1]])
            ->join('cliente as c', 'pedido.id_cliente', 'c.id_cliente')->join('detalle_cliente as dc', 'c.id_cliente', 'dc.id_cliente')
            ->orderBy('dc.nombre', 'asc')->select('id_pedido')->get();

        $objSheet1 = new PHPExcel_Worksheet($objPHPExcel, 'Despacho finca ' . now()->toDateString());
        $objPHPExcel->addSheet($objSheet1, 1);
        $objPHPExcel->setActiveSheetIndex(1);

        $objSheet1->getCell('A1')->setValue(strtoupper(getConfiguracionEmpresa()->razon_social));

        $objSheet1->getCell('B1')->setValue('SEMANA: ' . getSemanaByDate($request->fecha_pedido)->codigo);
        $objSheet1->getCell('B2')->setValue('DIA: ' . getDias(TP_COMPLETO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime(substr($request->fecha_pedido, 0, 10))))]);
        $objSheet1->getCell('B3')->setValue('FECHA: ' . Carbon::parse($request->fecha_pedido)->format('d-m-Y'));

        $objSheet1->getCell('C1')->setValue('DESPACHO DIARIOS DE CAJAS');
        $objSheet1->mergeCells('C1:J3');

        $objSheet1->getCell('A4')->setValue('CLIENTE / CODIGO');
        $objSheet1->getCell('B4')->setValue('FLOR');
        $objSheet1->getCell('C4')->setValue('COLOR');
        $objSheet1->getCell('D4')->setValue('EMPAQUE');
        $objSheet1->getCell('E4')->setValue('PRESENTACION');
        $objSheet1->getCell('F4')->setValue('TOTAL RAMOS');
        $objSheet1->getCell('G4')->setValue('RAMOS POR CAJA');
        $objSheet1->getCell('H4')->setValue('PIEZAS');
        $objSheet1->getCell('I4')->setValue('CAJAS FULL');
        $objSheet1->getCell('J4')->setValue('CUARTO FRIO');
        $objSheet1->getColumnDimension('A')->setWidth(40);
        $objSheet1->getColumnDimension('B')->setWidth(40);
        $objSheet1->getColumnDimension('C')->setWidth(30);
        $objSheet1->getColumnDimension('D')->setWidth(30);
        $objSheet1->getColumnDimension('E')->setWidth(10);
        $objSheet1->getColumnDimension('F')->setWidth(20);
        $objSheet1->getColumnDimension('G')->setWidth(30);
        $objSheet1->getColumnDimension('H')->setWidth(30);
        $objSheet1->getColumnDimension('I')->setWidth(30);
        $objSheet1->getColumnDimension('J')->setWidth(30);

        $BStyle = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        $style = array(
            'alignment' => array(
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        );

        $x = 4;
        $cajas_full_totales_no_tinturados = 0;

        foreach ($pedidos as $a => $pedido) {
            $p = getPedido($pedido->id_pedido);

            foreach ($p->detalles as $det => $det_ped) {

                $datos_exportacion = '';

                if (getDatosExportacionByDetPed($det_ped->id_detalle_pedido)->count() > 0)
                    foreach (getDatosExportacionByDetPed($det_ped->id_detalle_pedido) as $dE)
                        $datos_exportacion .= $dE->valor . "-";

                if ($det == 0)
                    $inicio_a = $x + 1;

                $final_a = getCantidadDetallesEspecificacionByPedido($pedido->id_pedido) + $inicio_a - 1;

                foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $sp => $esp_emp) {

                    foreach ($esp_emp->detalles as $det_sp => $det_esp_emp) {

                        $distribucionAssorted = DistribucionMixtos::where('ramos', '>', 0)
                            ->where('fecha', opDiasFecha('-', 1, $p->fecha_pedido))
                            ->where('id_cliente', $p->id_cliente)
                            ->where('id_pedido', $p->id_pedido)
                            ->where('id_detalle_pedido', $det_ped->id_detalle_pedido)
                            ->where('id_detalle_especificacionempaque', $det_esp_emp->id_detalle_especificacionempaque)->get();

                        $ramos_modificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);

                        if ($sp == 0 && $det_sp == 0) {
                            $inicio_b = $x + 1;
                        }

                        $final_b = getCantidadDetallesByEspecificacion($det_ped->cliente_especificacion->id_especificacion) + $inicio_b - 1;

                        if ($det_sp == 0) {
                            $inicio_d = $x + 1;
                            $cajas_full_totales_no_tinturados += ($esp_emp->cantidad * $det_ped->cantidad) * explode('|', $esp_emp->empaque->nombre)[1];
                        }

                        $final_d = count($esp_emp->detalles) + $inicio_d - 1;

                        if ($distribucionAssorted->count()) {

                            foreach ($distribucionAssorted as $disAssorted) {

                                /* $objSheet1->mergeCells('H' . $inicio_a . ':H' . $final_a);
                                $objSheet1->mergeCells('G' . $inicio_a . ':G' . $final_a);
                                $objSheet1->mergeCells('H' . $inicio_a . ':H' . $final_a); */

                                $objSheet1->getCell('A' . ($x + 1))->setValue($p->cliente->detalle()->nombre . ((!$datos_exportacion) ? "" : " / " . substr($datos_exportacion, 0, -1)));
                                $objSheet1->getCell('B' . ($x + 1))->setValue($disAssorted->planta->nombre);
                                $objSheet1->getCell('C' . ($x + 1))->setValue(($disAssorted->variedad()->nombre . " - ASSORTED") . " " . $disAssorted->longitud_ramo . " " . $disAssorted->unidad_medida->siglas);
                                $objSheet1->getCell('D' . ($x + 1))->setValue(explode("|", $esp_emp->empaque->nombre)[0]);
                                $objSheet1->getCell('E' . ($x + 1))->setValue(explode('|', $det_esp_emp->empaque_p->nombre)[0]);
                                $objSheet1->getCell('F' . ($x + 1))->setValue($disAssorted->ramos * $esp_emp->cantidad * $det_ped->cantidad);
                                $objSheet1->getCell('G' . ($x + 1))->setValue($disAssorted->ramos);
                                $objSheet1->getCell('H' . ($x + 1))->setValue($disAssorted->piezas);
                                $objSheet1->getCell('I' . ($x + 1))->setValue($disAssorted->piezas * explode('|', $esp_emp->empaque->nombre)[1]);
                                $objSheet1->getCell('J' . ($x + 1))->setValue($det_ped->agencia_carga->nombre);
                                $objSheet1->getStyle('A' . ($x - 3) . ':S' . ($x + 1))->applyFromArray($style);
                                $objSheet1->getStyle('A' . ($x - 3) . ':S' . ($x + 1))->applyFromArray($BStyle);
                                $x++;
                            }
                        } else {


                            /* $objSheet1->mergeCells('E' . $inicio_d . ':E' . $final_d);
                            $objSheet1->mergeCells('F' . $inicio_b . ':F' . $final_b);
                            $objSheet1->mergeCells('G' . $inicio_d . ':G' . $final_d);
                            $objSheet1->mergeCells('H' . $inicio_d . ':H' . $final_d);
                            $objSheet1->mergeCells('I' . $inicio_a . ':I' . $final_a); */

                            $objSheet1->getCell('A' . ($x + 1))->setValue($p->cliente->detalle()->nombre . ((!$datos_exportacion) ? "" : " / " . substr($datos_exportacion, 0, -1)));
                            $objSheet1->getCell('B' . ($x + 1))->setValue($det_esp_emp->variedad->planta->nombre);
                            $objSheet1->getCell('C' . ($x + 1))->setValue($det_esp_emp->variedad->nombre . " " . $det_esp_emp->longitud_ramo . " " . $det_esp_emp->unidad_medida->siglas);
                            $objSheet1->getCell('D' . ($x + 1))->setValue(explode("|", $esp_emp->empaque->nombre)[0]);
                            $objSheet1->getCell('E' . ($x + 1))->setValue(explode('|', $det_esp_emp->empaque_p->nombre)[0]);
                            $objSheet1->getCell('F' . ($x + 1))->setValue((isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad) * $esp_emp->cantidad * $det_ped->cantidad);
                            $objSheet1->getCell('G' . ($x + 1))->setValue(isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad);
                            $objSheet1->getCell('H' . ($x + 1))->setValue($esp_emp->cantidad * $det_ped->cantidad);
                            $objSheet1->getCell('I' . ($x + 1))->setValue(($esp_emp->cantidad * $det_ped->cantidad) * explode('|', $esp_emp->empaque->nombre)[1]);
                            $objSheet1->getCell('J' . ($x + 1))->setValue($det_ped->agencia_carga->nombre);
                            $objSheet1->getStyle('A' . ($x - 3) . ':S' . ($x + 1))->applyFromArray($style);
                            $objSheet1->getStyle('A' . ($x - 3) . ':S' . ($x + 1))->applyFromArray($BStyle);
                            $x++;
                        }
                    }
                }
            }
        }
    }

    public function unificar_pedidos(Request $request)
    {   //dd($request->all());
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        DB::beginTransaction();

        try {

            if ($request->tipo_pedido == 'STANDING ORDER' && $request->unir_futuro == 'SI') {

                //SE OBTIENEN LAS FECHAS DE LOS PEDIDOS NUEVOS
                $pedidoStanding = Pedido::whereIn('id_pedido', $request->id_pedidos)->first();

                $oldCpeClientePedido = $pedidoStanding->detalles->pluck('id_cliente_especificacion')->toArray();

                $marcaciones = [];

                foreach ($pedidoStanding->detalles as $detalle) {

                    foreach ($detalle->detalle_pedido_dato_exportacion as $dpde) {

                        $marcaciones[] = [
                            'id_dato_exportacion' => $dpde->id_dato_exportacion,
                            'valor' => $dpde->valor
                        ];
                    }
                }

                if (count($marcaciones)) {

                    $fechasNuevosPedidos = Pedido::where([
                        ['pedido.tipo_pedido', $pedidoStanding->tipo_pedido],
                        ['pedido.fecha_pedido', '>=', $pedidoStanding->fecha_pedido],
                        [DB::raw("DAYOFWEEK(pedido.fecha_pedido)"), Carbon::parse($pedidoStanding->fecha_pedido)->dayOfWeek + 1]
                    ])->join('detalle_pedido as dp', function ($j) use ($oldCpeClientePedido) {
                        $j->on('pedido.id_pedido', 'dp.id_pedido')->whereIn('dp.id_cliente_especificacion', $oldCpeClientePedido);
                    })->join('detallepedido_datoexportacion as dpde', function ($j) use ($marcaciones) {
                        $j->on('dp.id_detalle_pedido', 'dpde.id_detalle_pedido')
                            ->whereIn('dpde.id_dato_exportacion', array_column($marcaciones, 'id_dato_exportacion'))
                            ->whereIn('dpde.valor', array_column($marcaciones, 'valor'));
                    })->join('cliente_pedido_especificacion as cpe', function ($j) use ($pedidoStanding) {
                        $j->on('cpe.id_cliente_pedido_especificacion', 'dp.id_cliente_especificacion')->where('cpe.id_cliente', $pedidoStanding->id_cliente);
                    })->select('pedido.fecha_pedido', 'pedido.id_pedido', 'pedido.packing')->distinct()->get();
                } else {

                    $fechasNuevosPedidos = Pedido::where([
                        ['pedido.tipo_pedido', $pedidoStanding->tipo_pedido],
                        ['pedido.fecha_pedido', '>=', $pedidoStanding->fecha_pedido],
                        [DB::raw("DAYOFWEEK(pedido.fecha_pedido)"), Carbon::parse($pedidoStanding->fecha_pedido)->dayOfWeek + 1]
                    ])->join('detalle_pedido as dp', function ($j) use ($oldCpeClientePedido) {
                        $j->on('pedido.id_pedido', 'dp.id_pedido')->whereIn('dp.id_cliente_especificacion', $oldCpeClientePedido);
                    })->join('cliente_pedido_especificacion as cpe', function ($j) use ($pedidoStanding) {
                        $j->on('cpe.id_cliente_pedido_especificacion', 'dp.id_cliente_especificacion')->where('cpe.id_cliente', $pedidoStanding->id_cliente);
                    })->where(DB::raw("(SELECT COUNT(*) FROM detallepedido_datoexportacion as dpde WHERE dpde.id_detalle_pedido = dp.id_detalle_pedido)"), 0)
                        ->select('pedido.fecha_pedido', 'pedido.id_pedido', 'pedido.packing')->distinct()->get();
                }
                //FIN FECHA PEDIDOS NUEVOS



                //SE OBTIENEN LOS ID A ELIMINAR
                $oldsPedido = Pedido::whereIn('id_pedido', $request->id_pedidos)->get();
                $idsPedidos = [];

                foreach ($oldsPedido as  $oldP) {

                    $oldCpeClientePedido = $oldP->detalles->pluck('id_cliente_especificacion')->toArray();

                    $marcaciones = [];

                    foreach ($oldP->detalles as $detalle) {

                        foreach ($detalle->detalle_pedido_dato_exportacion as $dpde) {

                            $marcaciones[] = [
                                'id_dato_exportacion' => $dpde->id_dato_exportacion,
                                'valor' => $dpde->valor
                            ];
                        }
                    }

                    if (count($marcaciones)) {

                        $idsEliminarPedido = Pedido::where([
                            ['pedido.tipo_pedido', $oldP->tipo_pedido],
                            ['pedido.fecha_pedido', '>=', $oldP->fecha_pedido],
                            [DB::raw("DAYOFWEEK(pedido.fecha_pedido)"), Carbon::parse($oldP->fecha_pedido)->dayOfWeek + 1]
                        ])->join('detalle_pedido as dp', function ($j) use ($oldCpeClientePedido) {
                            $j->on('pedido.id_pedido', 'dp.id_pedido')->whereIn('dp.id_cliente_especificacion', $oldCpeClientePedido);
                        })->join('detallepedido_datoexportacion as dpde', function ($j) use ($marcaciones) {
                            $j->on('dp.id_detalle_pedido', 'dpde.id_detalle_pedido')
                                ->whereIn('dpde.id_dato_exportacion', array_column($marcaciones, 'id_dato_exportacion'))
                                ->whereIn('dpde.valor', array_column($marcaciones, 'valor'));
                        })->join('cliente_pedido_especificacion as cpe', function ($j) use ($oldP) {
                            $j->on('cpe.id_cliente_pedido_especificacion', 'dp.id_cliente_especificacion')->where('cpe.id_cliente', $oldP->id_cliente);
                        })->select('pedido.id_pedido')->distinct()->get();

                        foreach ($idsEliminarPedido as $id)
                            $idsPedidos[] = $id->id_pedido;
                    } else {

                        $idsEliminarPedido = Pedido::where([
                            ['pedido.tipo_pedido', $oldP->tipo_pedido],
                            ['pedido.fecha_pedido', '>=', $oldP->fecha_pedido],
                            [DB::raw("DAYOFWEEK(pedido.fecha_pedido)"), Carbon::parse($oldP->fecha_pedido)->dayOfWeek + 1]
                        ])->join('detalle_pedido as dp', function ($j) use ($oldCpeClientePedido) {
                            $j->on('pedido.id_pedido', 'dp.id_pedido')->whereIn('dp.id_cliente_especificacion', $oldCpeClientePedido);
                        })->join('cliente_pedido_especificacion as cpe', function ($j) use ($oldP) {
                            $j->on('cpe.id_cliente_pedido_especificacion', 'dp.id_cliente_especificacion')->where('cpe.id_cliente', $oldP->id_cliente);
                        })->where(DB::raw("(SELECT COUNT(*) FROM detallepedido_datoexportacion as dpde WHERE dpde.id_detalle_pedido = dp.id_detalle_pedido)"), 0)
                            ->select('pedido.id_pedido')->distinct()->get();

                        foreach ($idsEliminarPedido as $id)
                            $idsPedidos[] = $id->id_pedido;
                    }
                }
                //FIN ID A ELIMINAR

                UnificarPedidos::dispatchNow($fechasNuevosPedidos, $idsPedidos, $request->all());
            }
            if (($request->tipo_pedido == 'OPEN MARKET' && $request->unir_futuro == 'NO') || $request->tipo_pedido == 'OPEN MARKET') {

                $newPedido = Pedido::whereIn('id_pedido', $request->id_pedidos)->first();

                $objPedido = new Pedido;
                $p = Pedido::orderBy('id_pedido', 'desc')->first();
                $objPedido->id_pedido = isset($p->id_pedido) ? $p->id_pedido + 1 : 1;
                $objPedido->id_cliente = $request->id_cliente;
                $objPedido->tipo_pedido = $request->tipo_pedido;
                $objPedido->fecha_pedido = $newPedido->fecha_pedido;
                $objPedido->id_configuracion_empresa = ConfiguracionEmpresa::where('estado', 1)->first()->id_configuracion_empresa;
                $objPedido->packing = $newPedido->packing;
                $objPedido->variedad = '';
                $objPedido->save();

                $objEnvio = new Envio;
                $env = Envio::orderBy('id_envio', 'desc')->first();
                $objEnvio->id_envio = isset($env->id_envio) ? $env->id_envio + 1 : 1;
                $objEnvio->fecha_envio = Carbon::parse($newPedido->fecha_pedido)->toDateTimeString();
                $objEnvio->id_pedido = $objPedido->id_pedido;
                $objEnvio->id_consignatario = $newPedido->envios[0]->id_consignatario;
                $objEnvio->guia_madre = $newPedido->envios[0]->guia_madre;
                $objEnvio->guia_hija = $newPedido->envios[0]->guia_hija;
                $objEnvio->dae = $newPedido->envios[0]->dae;
                $objEnvio->codigo_pais = $newPedido->envios[0]->codigo_pais;
                $objEnvio->codigo_dae = $newPedido->envios[0]->codigo_dae;
                $objEnvio->save();

                $oldsPedido = Pedido::whereIn('id_pedido', $request->id_pedidos)->get();

                foreach ($oldsPedido as $oldPedido) {

                    $detallePedidos = DetallePedido::where('id_pedido', $oldPedido->id_pedido)->get();

                    // SE CAMBIA LA RELACION DEL DETALLE PEDIDO AL NUEVO PEDIDO
                    foreach ($detallePedidos as $dp) {
                        $detallePedido = DetallePedido::find($dp->id_detalle_pedido);
                        $detallePedido->id_pedido = $objPedido->id_pedido;
                        $detallePedido->save();
                    }

                    if (isset($oldPedido->envios[0])) {

                        $detalleEnvios = DetalleEnvio::where('id_envio', $oldPedido->envios[0]->id_envio)->get();

                        // SE CAMBIA LA RELACION DEL DETALLE ENVIO AL NUEVO ENVIO
                        foreach ($detalleEnvios as $de) {
                            $detalleEnvio = DetalleEnvio::find($de->id_detalle_envio);
                            $detalleEnvio->id_envio = $objEnvio->id_envio;
                            $detalleEnvio->save();
                        }
                    }
                }

                //DESTRUIR CABECERA DE PEDIDOS VIEJOS QUE YA NO TIENEN DETALLES
                foreach ($oldsPedido as $oldPedido)
                    Pedido::destroy($oldPedido);
            }

            DB::commit();

            $success = true;
            $msg =  '<div class="alert alert-success text-center">' .
                '<p> Se han unificado exitosamente los pedidos </p>'
                . '</div>';
        } catch (\Exception $e) {

            $success = false;
            $msg =  '<div class="alert alert-danger text-center">' .
                '<p> Hubo un error al intentar unificar el pedido </p> <p><strong>Error: </strong>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
            DB::rollBack();
        }

        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function dividir_marcaciones(Request $request)
    { //dd($request->all());
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        DB::beginTransaction();

        try {

            $p = Pedido::orderBy('id_pedido', 'desc')->first();
            $pedido = Pedido::find($request->detalles_pedido[0]['id_pedido']);

            $cpeOldPedido = $pedido->detalles->pluck('id_cliente_especificacion')->toArray();

            $empresa = ConfiguracionEmpresa::All()->where('estado', true)->first();

            $newPedido = $pedido->replicate();
            $newPedido->id_pedido = $p->id_pedido + 1;
            $newPedido->fecha_registro = now()->toDateTimeString();
            $newPedido->packing = $empresa->numero_packing + 1;
            $newPedido->save();

            $empresa->numero_packing = $newPedido->packing;
            $empresa->save();

            $objEnvio = new Envio;
            $env = Envio::orderBy('id_envio', 'desc')->first();
            $objEnvio->id_envio = isset($env->id_envio) ? $env->id_envio + 1 : 1;
            $objEnvio->fecha_envio = $newPedido->fecha_registro;
            $objEnvio->id_pedido = $newPedido->id_pedido;
            $objEnvio->id_consignatario = $pedido->envios[0]->id_consignatario;
            $objEnvio->save();

            $detEnvio = DetalleEnvio::find($pedido->envios[0]->detalles[0]->id_detalle_envio);

            $objDetEnvio = $detEnvio->replicate();
            $objDetEnvio->id_envio = $objEnvio->id_envio;
            $objDetEnvio->save();

            $variedades = [];
            $marcaciones = [];
            foreach ($request->detalles_pedido as $det_ped) {

                $detallePedido = DetallePedido::find($det_ped['id_det_ped']);

                foreach ($detallePedido->detalle_pedido_dato_exportacion as $marcacion) {
                    $marcaciones[] = [
                        'id_dato_exportacion' => $marcacion->id_dato_exportacion,
                        'valor' => $marcacion->valor
                    ];
                }

                $detallePedido->id_pedido = $newPedido->id_pedido;
                $detallePedido->save();

                foreach ($detallePedido->cliente_especificacion->especificacion->especificacionesEmpaque as $espEmp) {

                    foreach ($espEmp->detalles as $detEspEmp) {

                        $variedades[] = $detEspEmp->id_variedad;
                    }
                }
            }

            $nuevasVariedadesPedidoViejo = [];

            foreach (explode("|", $pedido->variedad) as $v) {
                if (!in_array($v, $variedades)) {
                    $nuevasVariedadesPedidoViejo[] = $v;
                }
            }

            //VARIEDADES DEL PEDIDO QUE SE CREAR AL DIVIDIR LA MARCACIONES
            $updateNewPedido = Pedido::find($newPedido->id_pedido);
            $updateNewPedido->variedad = implode("|", $variedades);
            $updateNewPedido->save();

            // VARIEDADES DEL PEDIDO QUE SE MODIFICO
            $pedido->variedad = implode('|', $nuevasVariedadesPedidoViejo);
            $pedido->save();

            DB::commit();

            if ($request->edicion_futura == 'SI')
                DividirMarcacionesStandigs::dispatchNow($cpeOldPedido, $request->detalles_pedido, $variedades, $marcaciones);

            $success = true;
            $msg =  '<div class="alert alert-success text-center">' .
                '<p> Se han dividido exitosamente las marcaciones </p>'
                . '</div>';
        } catch (\Exception $e) {

            $success = false;
            $msg =  '<div class="alert alert-danger text-center">' .
                '<p> Hubo un error al intentar unificar el pedido </p> <p><strong>Error: </strong>' . $e->getMessage() . '</p>' .
                '<p>' . $e->getFile() . '</p>' .
                '<p>' . $e->getLine() . '</p>'
                . '</div>';
            DB::rollBack();
        }

        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function exportar_jire_cabecera(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_cabecera_jire($spread, $request);

        $fileName = "JIRE.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
    }

    public function excel_cabecera_jire($objPHPExcel, $request)
    {
        $pedidos = Pedido::join('cliente as c', 'pedido.id_cliente', 'c.id_cliente')
            ->join('detalle_cliente as dc', 'c.id_cliente', 'dc.id_cliente')
            ->where([
                ['pedido.estado', 1],
                ['dc.estado', 1]
            ])->whereBetWeen('fecha_pedido', [$request->fecha_desde, $request->fecha_hasta])
            ->where(function ($w) use ($request) {

                if (isset($request->id_configuracion_empresa))
                    $w->where('id_configuracion_empresa', $request->id_configuracion_empresa);
            })->orderBy('dc.nombre', 'asc')->get();

        $objSheet1 = $objPHPExcel->getActiveSheet()->setTitle('CABECERA PACKING');
        $w = 0;

        foreach ($pedidos as $x => $pedido) {
            $cajaFin = 0;
            $contador = 1;
            $cliente = $pedido->cliente->detalle();
            //$contacto = $pedido->cliente->contacto_principal();
            $envio = isset($pedido->envios) ? $pedido->envios[0] : null;
            $upc = [];

            foreach ($pedido->detalles as $det_ped) {
                $marcacion = [];
                foreach ($det_ped->detalle_pedido_dato_exportacion as $de) {
                    if (strtoupper($de->dato_exportacion->nombre) === 'UPC' && !in_array($de->valor, $upc)) {
                        $upc[] = $de->valor;
                    } else if ((strtoupper($de->dato_exportacion->nombre) === 'MARCACION' || strtoupper($de->dato_exportacion->nombre) === 'PO') && !in_array($de->valor, $marcacion)) {
                        $marcacion[] = $de->valor;
                    }
                }

                $cajaInicio = $cajaFin + 1;
                $cajaFin += $det_ped->cantidad;
                $precio = explode("|", $det_ped->precio);
                $i = 0;

                foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $y => $esp_emp) {

                    foreach ($esp_emp->detalles as $z => $det_esp_emp) {

                        $ramos_modificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);

                        $L50 = 0;
                        $L60 = 0;
                        $L70 = 0;
                        $L80 = 0;
                        $L90 = 0;

                        if ($det_esp_emp->longitud_ramo == 50)
                            $L50 = (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad);

                        if ($det_esp_emp->longitud_ramo == 60)
                            $L60 = (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad);

                        if ($det_esp_emp->longitud_ramo == 70 || $det_esp_emp->longitud_ramo == 0)
                            $L70 = (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad);

                        if ($det_esp_emp->longitud_ramo == 80)
                            $L80 = (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad);

                        if ($det_esp_emp->longitud_ramo == 90)
                            $L90 = (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad);

                        $tipo_caja = '';

                        switch (explode('|', $esp_emp->empaque->nombre)[1]) {
                            case '1':
                                $tipo_caja = 5;
                                break;
                            case '0.5':
                                $tipo_caja = 2;
                                break;
                            case '0.25':
                                $tipo_caja = 1;
                                break;
                            case '0.125':
                                $tipo_caja = 3;
                                break;
                            case '0.0625':
                                $tipo_caja = 0.0625;
                                break;
                        }

                        $objSheet1->getCell('A' . ($w + 1))->setValue($pedido->packing);
                        $objSheet1->getCell('B' . ($w + 1))->setValue($pedido->cliente->detalle()->ruc);
                        $objSheet1->getCell('C' . ($w + 1))->setValue($pedido->detalles[0]->agencia_carga->identificacion);
                        $objSheet1->getCell('D' . ($w + 1))->setValue(isset($envio) && isset($envio->consignatario) ? $envio->consignatario->telefono : ''); // TELEFONO CONSIGNATARIO
                        $objSheet1->getCell('E' . ($w + 1))->setValue(isset($envio) && isset($envio->consignatario) ? $envio->consignatario->identificacion : ''); // ID CONSIGNATARIO
                        $objSheet1->getCell('F' . ($w + 1))->setValue($cliente->direccion);
                        $objSheet1->getCell('G' . ($w + 1))->setValue(isset($envio) && isset($envio->consignatario) ? $envio->consignatario->nombre : '');
                        $objSheet1->getCell('H' . ($w + 1))->setValue($cliente->telefono);
                        $objSheet1->getCell('I' . ($w + 1))->setValue($cliente->provincia . ', ' . $cliente->pais->nombre);
                        $objSheet1->getCell('J' . ($w + 1))->setValue($pedido->fecha_pedido);
                        $objSheet1->getCell('K' . ($w + 1))->setValue(isset($envio) ?  $envio->guia_madre : '');
                        $objSheet1->getCell('L' . ($w + 1))->setValue(isset($envio) ?  $envio->guia_hija : '');
                        if (isset($envio)) {
                            $objSheet1->getCell('M' . ($w + 1))->setValue(substr($envio->guia_madre, 0, 3));
                        }
                        $objSheet1->getCell('N' . ($w + 1))->setValue(isset($envio) ?  $envio->dae : '');
                        $objSheet1->getCell('O' . ($w + 1))->setValue($pedido->getCajasFull());
                        $objSheet1->getCell('P' . ($w + 1))->setValue($pedido->getCajasFisicas());
                        $objSheet1->getCell('Q' . ($w + 1))->setValue('P');
                        $objSheet1->getCell('R' . ($w + 1))->setValue($cliente->codigo_pais == 'EC' ? 'N' : 'E');
                        $objSheet1->getCell('S' . ($w + 1))->setValue(implode(', ', $upc));
                        $objSheet1->getCell('T' . ($w + 1))->setValue(implode(', ', $marcacion));
                        $objSheet1->getCell('U' . ($w + 1))->setValue('');
                        $objSheet1->getCell('V' . ($w + 1))->setValue(implode(', ', $marcacion));

                        ///////////// DETALLE PACKING ///////////////
                        $objSheet1->getCell('W' . ($w + 1))->setValue($cajaInicio);
                        $objSheet1->getCell('X' . ($w + 1))->setValue($cajaFin);
                        $objSheet1->getCell('Y' . ($w + 1))->setValue($pedido->packing);
                        $objSheet1->getCell('Z' . ($w + 1))->setValue('');
                        $objSheet1->getCell('AA' . ($w + 1))->setValue($det_esp_emp->variedad->planta->siglas);
                        $objSheet1->getCell('AB' . ($w + 1))->setValue($det_esp_emp->variedad->siglas);
                        $objSheet1->getCell('AC' . ($w + 1))->setValue($det_esp_emp->tallos_x_ramos);
                        $objSheet1->getCell('AD' . ($w + 1))->setValue($L50 * $det_ped->cantidad);
                        $objSheet1->getCell('AE' . ($w + 1))->setValue($L60 * $det_ped->cantidad);
                        $objSheet1->getCell('AF' . ($w + 1))->setValue($L70 * $det_ped->cantidad);
                        $objSheet1->getCell('AG' . ($w + 1))->setValue($L80 * $det_ped->cantidad);
                        $objSheet1->getCell('AH' . ($w + 1))->setValue($L90 * $det_ped->cantidad);
                        $objSheet1->getCell('AI' . ($w + 1))->setValue(($L50 + $L60 + $L70 + $L80 + $L90) * $det_ped->cantidad);
                        $objSheet1->getCell('AJ' . ($w + 1))->setValue(5);
                        $objSheet1->getCell('AK' . ($w + 1))->setValue($det_esp_emp->variedad->planta->nombre . ' ' . $det_esp_emp->variedad->nombre . ' / ' . $det_esp_emp->tallos_x_ramos . ' STEMS');
                        $objSheet1->getCell('AL' . ($w + 1))->setValue($tipo_caja == 3 ? 0 : $contador);
                        $objSheet1->getCell('AM' . ($w + 1))->setValue($pedido->tipo_pedido == 'STANDING ORDER' ? 'SO' : 'OM');
                        $objSheet1->getCell('AN' . ($w + 1))->setValue(number_format(explode(";", $precio[$i])[0], 2, ".", ""));
                        $objSheet1->getCell('AO' . ($w + 1))->setValue($y == 0 && $z == 0 ? $tipo_caja : '');

                        if ($tipo_caja == 3)
                            $contador = 0;

                        $w++;
                        $i++;
                    }

                    $contador++;
                }
            }
        }
    }

    public function exportar_jire_detalle_cabecera(Request $request)
    {
        //---------------------- EXCEL --------------------------------------
        $objPHPExcel = new PHPExcel;
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(12);
        $objPHPExcel->removeSheetByIndex(0);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");

        $this->excel_detalle_cabecera_jire($objPHPExcel, $request);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="DETALLE CABECERA PACKING.xlsx"');
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

    public function excel_detalle_cabecera_jire($objPHPExcel, $request)
    {
        $pedidos = Pedido::join('cliente as c', 'pedido.id_cliente', 'c.id_cliente')
            ->join('detalle_cliente as dc', 'c.id_cliente', 'dc.id_cliente')
            ->where([
                ['pedido.estado', 1],
                ['dc.estado', 1]
            ])->whereBetWeen('fecha_pedido', [$request->fecha_desde, $request->fecha_hasta])
            ->where(function ($w) use ($request) {

                if (isset($request->id_configuracion_empresa))
                    $w->where('id_configuracion_empresa', $request->id_configuracion_empresa);
            })->orderBy('dc.nombre', 'asc')->get();

        $objSheet1 = new PHPExcel_Worksheet($objPHPExcel, 'DETALLE PACKING');
        $objPHPExcel->addSheet($objSheet1, 0);
        $objPHPExcel->setActiveSheetIndex(0);
        $x = 0;

        foreach ($pedidos as $a => $pedido) {

            $cajaFin = 0;
            $contador = 1;

            foreach ($pedido->detalles as $det_ped) {

                $cajaInicio = $cajaFin + 1;
                $cajaFin += $det_ped->cantidad;
                $precio = explode("|", $det_ped->precio);
                $i = 0;

                foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $y => $esp_emp) {

                    foreach ($esp_emp->detalles as $z => $det_esp_emp) {

                        $ramos_modificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);

                        $L50 = 0;
                        $L60 = 0;
                        $L70 = 0;
                        $L80 = 0;
                        $L90 = 0;

                        if ($det_esp_emp->longitud_ramo == 50)
                            $L50 = (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad);

                        if ($det_esp_emp->longitud_ramo == 60)
                            $L60 = (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad);

                        if ($det_esp_emp->longitud_ramo == 70)
                            $L70 = (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad);

                        if ($det_esp_emp->longitud_ramo == 80)
                            $L80 = (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad);

                        if ($det_esp_emp->longitud_ramo == 90)
                            $L90 = (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad);

                        $tipo_caja = '';

                        switch (explode('|', $esp_emp->empaque->nombre)[1]) {
                            case '0.5':
                                $tipo_caja = 2;
                                break;
                            case '0.25':
                                $tipo_caja = 1;
                                break;
                            case '0.125':
                                $tipo_caja = 3;
                                break;
                            case '0.0625':
                                $tipo_caja = 0.0625;
                                break;
                        }

                        $objSheet1->getCell('A' . ($x + 1))->setValue($cajaInicio);
                        $objSheet1->getCell('B' . ($x + 1))->setValue($cajaFin);
                        $objSheet1->getCell('C' . ($x + 1))->setValue(300008 + ($a + 1));
                        $objSheet1->getCell('D' . ($x + 1))->setValue('');
                        $objSheet1->getCell('E' . ($x + 1))->setValue($det_esp_emp->variedad->planta->siglas);
                        $objSheet1->getCell('F' . ($x + 1))->setValue($det_esp_emp->variedad->siglas);
                        $objSheet1->getCell('G' . ($x + 1))->setValue($det_esp_emp->tallos_x_ramos);
                        $objSheet1->getCell('H' . ($x + 1))->setValue($L50);
                        $objSheet1->getCell('I' . ($x + 1))->setValue($L60);
                        $objSheet1->getCell('J' . ($x + 1))->setValue($L70);
                        $objSheet1->getCell('K' . ($x + 1))->setValue($L80);
                        $objSheet1->getCell('L' . ($x + 1))->setValue($L90);
                        $objSheet1->getCell('M' . ($x + 1))->setValue($L50 = (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad));
                        $objSheet1->getCell('N' . ($x + 1))->setValue(5);
                        $objSheet1->getCell('O' . ($x + 1))->setValue($det_esp_emp->variedad->planta->nombre . ' ' . $det_esp_emp->variedad->nombre);
                        $objSheet1->getCell('P' . ($x + 1))->setValue($tipo_caja == 3 ? 0 : $contador);
                        $objSheet1->getCell('Q' . ($x + 1))->setValue($pedido->tipo_pedido == 'STANDING ORDER' ? 'SO' : 'OM');
                        $objSheet1->getCell('R' . ($x + 1))->setValue(number_format(explode(";", $precio[$i])[0], 2, ".", ""));
                        $objSheet1->getCell('S' . ($x + 1))->setValue($y == 0 && $z == 0 ? $tipo_caja : '');

                        if ($tipo_caja == 3)
                            $contador = 0;

                        $x++;
                        $i++;
                    }

                    $contador++;
                }
            }
        }
    }

    public function descargar_packings_unificados(Request $request)
    {
        $pedidos = Pedido::join('cliente as c', 'c.id_cliente', 'pedido.id_cliente')
            ->join('detalle_cliente as dc', function ($j) {
                $j->on('dc.id_cliente', 'pedido.id_cliente')->where('dc.estado', true);
            });
        if ($request->ids_pedidos != '')
            $pedidos = $pedidos->whereIn('pedido.id_pedido', $request->ids_pedidos);
        $pedidos = $pedidos->where('dc.estado', 1)
            ->where('c.estado', 1)
            ->where('pedido.estado', 1)
            ->where(function ($w) use ($request) {

                if (isset($request->hasta)) {
                    $w->whereBetween('pedido.fecha_pedido', [$request->desde, $request->hasta]);
                } else {
                    $w->where('pedido.fecha_pedido', $request->desde);
                }

                if (isset($request->tipo_pedido) && $request->tipo_pedido != '')
                    $w->where('tipo_pedido', $request->tipo_pedido);
            })->where(function ($query) use ($request) {
                if (isset($request->id_cliente))
                    $query->where('pedido.id_cliente', $request->id_cliente);
            })->select('pedido.*', 'dc.nombre')->orderBy('dc.nombre', 'asc')->get();

        $oMerger = PDFMerger::init();

        /*foreach ($pedidos as $p) {
            if (count($p->detalles) == 0 || count($p->envios) == 0)
                dump($p->id_pedido . ' - ' . $p->packing, $p->cliente->detalle()->nombre, 'detalles: ' . count($p->detalles), 'envios: ' . count($p->envios));
        }
        dd('fin');*/

        foreach ($pedidos as $pedido) {
            if (count($pedido->detalles) > 0 && count($pedido->envios) > 0) {

                /*if ($pedido->packing == '') {
                    $last_packing = Pedido::orderBy('packing', 'desc')->first();
                    $pedido->packing = isset($last_packing->packing) ? $last_packing->packing + 1 : 1;
                    $pedido->save();
                }*/

                PDF::loadView('adminlte.gestion.postcocecha.despachos.partials.pdf_packing_list', compact('pedido'))
                    ->setPaper([0, 0, 650, 841.89], 'portrait')
                    ->save(public_path('pdf/pdf' . $pedido->id_pedido . '.pdf'));

                $oMerger->addPDF(public_path('pdf/pdf' . $pedido->id_pedido . '.pdf'), 'all');
            }
        }

        $oMerger->merge();

        foreach ($pedidos as $pedido)
            if (count($pedido->detalles) > 0 && count($pedido->envios) > 0)
                unlink(public_path('pdf/pdf' . $pedido->id_pedido . '.pdf'));

        return $oMerger->download();
    }

    public function exportar_excel_flor_posco(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte_flor_posco($spread, $request);

        $fileName = "PREPARACION_DE_FLOR.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_reporte_flor_posco($spread, $request)
    {
        $pedidos = Pedido::join('cliente as c', 'pedido.id_cliente', 'c.id_cliente')
            ->join('detalle_cliente as dc', 'c.id_cliente', 'dc.id_cliente')
            ->where([
                ['pedido.estado', 1],
                ['dc.estado', 1]
            ])->whereBetWeen('fecha_pedido', [$request->fecha_desde, $request->fecha_hasta])
            ->orderBy('dc.nombre', 'asc')->get();

        setlocale(LC_TIME, "es_ES.UTF-8");

        $totalTallos = 0;
        $montoTotal = 0;
        $totalCajas = 0;

        $datosExportacion = DatosExportacion::where('estado', true)->get();

        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('PEDIDOS ' . $request->fecha_desde . ' ' . $request->fecha_hasta);
        $sheet->getCell('A1')->setValue('FECHA');
        $sheet->getCell('B1')->setValue('ANO');
        $sheet->getCell('C1')->setValue('DIA');
        $sheet->getCell('D1')->setValue('DIA TEXTO');
        $sheet->getCell('E1')->setValue('CLIENTE');
        $sheet->getCell('F1')->setValue('CONSIGNATARIO');
        $sheet->getCell('G1')->setValue('PO');
        $sheet->getCell('H1')->setValue('UPC');
        $sheet->getCell('I1')->setValue('MARCACION');
        $sheet->getCell('J1')->setValue('TALLOS X RAMO');
        $sheet->getCell('K1')->setValue('RAMOS X CAJA');
        $sheet->getCell('L1')->setValue('FLOR');
        $sheet->getCell('M1')->setValue('COLOR');
        $sheet->getCell('N1')->setValue('MEDIDA');
        $sheet->getCell('O1')->setValue('CAJAS');
        $sheet->getCell('P1')->setValue('TIPO DE CAJA');
        $sheet->getCell('Q1')->setValue('PRECIO X BUNCHE');
        $sheet->getCell('R1')->setValue('PRESENTACION');
        $sheet->getCell('S1')->setValue('TOTAL TALLOS');
        $sheet->getCell('T1')->setValue('TOTAL RAMOS');
        $sheet->getCell('U1')->setValue('MES');
        $sheet->getCell('V1')->setValue('SEMANA');
        $sheet->getCell('W1')->setValue('CARGUERA');
        $sheet->getCell('X1')->setValue('TIPO DE PEDIDO');
        /*$sheet->getDefaultStyle()->applyFromArray(array(
            'alignment' => array(
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        ));*/
        $sheet->getStyle('A1:V1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('357ca5');
        $sheet->getStyle('A1:V1')->getFont()->getColor()->applyFromArray(array('rgb' => 'ffffff'));

        $x = 1;

        foreach ($pedidos as $a => $pedido) {

            $datosExportacion = [];

            if ($pedido->tipo_especificacion === "N") {

                foreach ($pedido->detalles as $det_ped) {

                    /*foreach ($det_ped->detalle_pedido_dato_exportacion as $de) {
                        if (!in_array($de->dato_exportacion->nombre, array_column($datosExportacion, 'nombre'))) {
                            $datosExportacion[] = [
                                'id_dato_exportacion' => $de->id_dato_exportacion,
                                'nombre' => $de->dato_exportacion->nombre
                            ];
                        }
                    }*/

                    foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $y => $esp_emp) {

                        foreach ($esp_emp->detalles as $z => $det_esp_emp) {

                            $ramos_modificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);
                            //$datos_exportacion=[];

                            /*if (getDatosExportacionByDetPed($det_ped->id_detalle_pedido)->count() > 0)
                                foreach (getDatosExportacionByDetPed($det_ped->id_detalle_pedido) as $dE)
                                    $datos_exportacion[] =  $dE->dato_exportacion->nombre . ': ' . $dE->valor;*/

                            $distribucionAssorted = DistribucionMixtos::where('ramos', '>', 0)
                                ->where('fecha', opDiasFecha('-', 1, $pedido->fecha_pedido))
                                ->where('id_cliente', $pedido->id_cliente)
                                ->where('id_pedido', $pedido->id_pedido)
                                ->where('id_detalle_pedido', $det_ped->id_detalle_pedido)
                                ->where('id_detalle_especificacionempaque', $det_esp_emp->id_detalle_especificacionempaque)->get();

                            if ($distribucionAssorted->count()) {

                                foreach ($distribucionAssorted as $pos_dist => $da) {

                                    $sheet->getCell('A' . ($x + 1))->setValue(Carbon::parse($pedido->fecha_pedido)->format('d/m/Y'));
                                    $sheet->getCell('B' . ($x + 1))->setValue(Carbon::parse($pedido->fecha_pedido)->format('Y'));
                                    $sheet->getCell('C' . ($x + 1))->setValue(Carbon::parse($pedido->fecha_pedido)->format('d'));
                                    $sheet->getCell('D' . ($x + 1))->setValue(strtoupper(strftime("%A", gmmktime(12, 0, 0, Carbon::parse($pedido->fecha_pedido)->format('m'), Carbon::parse($pedido->fecha_pedido)->format('d'), Carbon::parse($pedido->fecha_pedido)->format('Y')))));
                                    $sheet->getCell('E' . ($x + 1))->setValue($pedido->cliente->detalle()->nombre);
                                    $sheet->getCell('F' . ($x + 1))->setValue(isset($pedido->envios[0]->consignatario) ? $pedido->envios[0]->consignatario->nombre : "");
                                    $po = $det_ped->detalle_pedido_dato_exportacion->where('id_dato_exportacion', 2)->first();
                                    $sheet->getCell('G' . ($x + 1))->setValue(isset($po) ? $po->valor : '');
                                    $upc = $det_ped->detalle_pedido_dato_exportacion->where('id_dato_exportacion', 1)->first();
                                    $sheet->getCell('H' . ($x + 1))->setValue(isset($upc) ? $upc->valor : '');
                                    $marcacion = $det_ped->detalle_pedido_dato_exportacion->where('id_dato_exportacion', 3)->first();
                                    $sheet->getCell('I' . ($x + 1))->setValue(isset($marcacion) ? $marcacion->valor : '');
                                    $sheet->getCell('J' . ($x + 1))->setValue($det_esp_emp->tallos_x_ramos);
                                    $sheet->getCell('K' . ($x + 1))->setValue($da->ramos);
                                    $sheet->getCell('L' . ($x + 1))->setValue($da->planta->nombre);
                                    $sheet->getCell('M' . ($x + 1))->setValue($da->variedad()->nombre);
                                    $sheet->getCell('N' . ($x + 1))->setValue($det_esp_emp->longitud_ramo . ' ' . $det_esp_emp->unidad_medida->siglas);
                                    $sheet->getCell('O' . ($x + 1))->setValue($pos_dist == 0 ? $det_ped->cantidad : '');
                                    $sheet->getCell('P' . ($x + 1))->setValue($pos_dist == 0 ? explode('|', $esp_emp->empaque->nombre)[0] : '');
                                    $sheet->getCell('Q' . ($x + 1))->setValue('$' . getPrecioByDetEsp($det_ped->precio, $det_esp_emp->id_detalle_especificacionempaque));
                                    $sheet->getCell('R' . ($x + 1))->setValue($det_esp_emp->empaque_p->nombre);
                                    $sheet->getCell('S' . ($x + 1))->setValue(number_format($det_esp_emp->tallos_x_ramos * $da->ramos * $da->piezas, 2, '.', ''));
                                    $sheet->getCell('T' . ($x + 1))->setValue($da->ramos * $det_ped->cantidad);
                                    $sheet->getCell('U' . ($x + 1))->setValue(strtoupper(strftime("%B", gmmktime(12, 0, 0, Carbon::parse($pedido->fecha_pedido)->format('m'), Carbon::parse($pedido->fecha_pedido)->format('d'), Carbon::parse($pedido->fecha_pedido)->format('Y')))));
                                    $sheet->getCell('V' . ($x + 1))->setValue(Carbon::parse($pedido->fecha_pedido)->weekOfYear);
                                    $sheet->getCell('W' . ($x + 1))->setValue($det_ped->agencia_carga->nombre);
                                    $sheet->getCell('X' . ($x + 1))->setValue($pedido->tipo_pedido);

                                    $x++;
                                }
                            } else {

                                $sheet->getCell('A' . ($x + 1))->setValue(Carbon::parse($pedido->fecha_pedido)->format('d/m/Y'));
                                $sheet->getCell('B' . ($x + 1))->setValue(Carbon::parse($pedido->fecha_pedido)->format('Y'));
                                $sheet->getCell('C' . ($x + 1))->setValue(Carbon::parse($pedido->fecha_pedido)->format('d'));
                                $sheet->getCell('D' . ($x + 1))->setValue(strtoupper(strftime("%A", gmmktime(12, 0, 0, Carbon::parse($pedido->fecha_pedido)->format('m'), Carbon::parse($pedido->fecha_pedido)->format('d'), Carbon::parse($pedido->fecha_pedido)->format('Y')))));
                                $sheet->getCell('E' . ($x + 1))->setValue($pedido->cliente->detalle()->nombre);
                                $sheet->getCell('F' . ($x + 1))->setValue(isset($pedido->envios[0]->consignatario) ? $pedido->envios[0]->consignatario->nombre : "");
                                $po = $det_ped->detalle_pedido_dato_exportacion->where('id_dato_exportacion', 2)->first();
                                $sheet->getCell('G' . ($x + 1))->setValue(isset($po) ? $po->valor : '');
                                $upc = $det_ped->detalle_pedido_dato_exportacion->where('id_dato_exportacion', 1)->first();
                                $sheet->getCell('H' . ($x + 1))->setValue(isset($upc) ? $upc->valor : '');
                                $marcacion = $det_ped->detalle_pedido_dato_exportacion->where('id_dato_exportacion', 3)->first();
                                $sheet->getCell('I' . ($x + 1))->setValue(isset($marcacion) ? $marcacion->valor : '');
                                $sheet->getCell('J' . ($x + 1))->setValue($det_esp_emp->tallos_x_ramos);
                                $sheet->getCell('K' . ($x + 1))->setValue((isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad));
                                $sheet->getCell('L' . ($x + 1))->setValue($det_esp_emp->variedad->planta->nombre);
                                $sheet->getCell('M' . ($x + 1))->setValue($det_esp_emp->variedad->nombre);
                                $sheet->getCell('N' . ($x + 1))->setValue($det_esp_emp->longitud_ramo . ' ' . $det_esp_emp->unidad_medida->siglas);
                                $sheet->getCell('O' . ($x + 1))->setValue($y == 0 && $z == 0 ? $det_ped->cantidad : '');
                                if ($y == 0 && $z == 0) $totalCajas += $det_ped->cantidad;
                                $sheet->getCell('P' . ($x + 1))->setValue($y == 0 && $z == 0 ? explode('|', $esp_emp->empaque->nombre)[0] : '');
                                $sheet->getCell('Q' . ($x + 1))->setValue('$' . getPrecioByDetEsp($det_ped->precio, $det_esp_emp->id_detalle_especificacionempaque));
                                $sheet->getCell('R' . ($x + 1))->setValue($det_esp_emp->empaque_p->nombre);
                                $sheet->getCell('S' . ($x + 1))->setValue(number_format($det_esp_emp->tallos_x_ramos * $det_ped->cantidad * (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad), 2, '.', ''));
                                $sheet->getCell('T' . ($x + 1))->setValue((isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad) * $det_ped->cantidad);
                                $totalTallos += $det_esp_emp->tallos_x_ramos * $det_ped->cantidad * (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad);
                                $sheet->getCell('U' . ($x + 1))->setValue(strtoupper(strftime("%B", gmmktime(12, 0, 0, Carbon::parse($pedido->fecha_pedido)->format('m'), Carbon::parse($pedido->fecha_pedido)->format('d'), Carbon::parse($pedido->fecha_pedido)->format('Y')))));
                                $sheet->getCell('V' . ($x + 1))->setValue(Carbon::parse($pedido->fecha_pedido)->weekOfYear);
                                $sheet->getCell('W' . ($x + 1))->setValue($det_ped->agencia_carga->nombre);
                                $sheet->getCell('X' . ($x + 1))->setValue($pedido->tipo_pedido);

                                $x++;
                            }
                        }
                    }
                }
            }
        }

        $sheet->getStyle('K' . ($x + 1) . ':W' . ($x + 1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('357ca5');
        $sheet->getStyle('K' . ($x + 1) . ':W' . ($x + 1))->getFont()->getColor()->applyFromArray(array('rgb' => 'ffffff'));


        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(30);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(18);
        $sheet->getColumnDimension('I')->setWidth(28);
        $sheet->getColumnDimension('J')->setWidth(20);
        $sheet->getColumnDimension('K')->setWidth(27);
        $sheet->getColumnDimension('L')->setWidth(22);
        $sheet->getColumnDimension('M')->setWidth(22);
        $sheet->getColumnDimension('N')->setWidth(22);
        $sheet->getColumnDimension('O')->setWidth(22);
        $sheet->getColumnDimension('P')->setWidth(22);
        $sheet->getColumnDimension('Q')->setWidth(22);
        $sheet->getColumnDimension('R')->setWidth(22);
        $sheet->getColumnDimension('S')->setWidth(22);
        $sheet->getColumnDimension('T')->setWidth(22);
        $sheet->getColumnDimension('U')->setWidth(22);
        $sheet->getColumnDimension('V')->setWidth(22);
        $sheet->getColumnDimension('W')->setWidth(22);
    }
    public function get_pedido_proceso(Request $request)
    {
        $arrIdsPedido = $request->input('arrIdsPedido', []);
        $arrPedidoProceso = DB::table('pedido_proceso as pp')
            ->join('usuario as u', 'pp.id_usuario', '=', 'u.id_usuario')
            ->whereIn('pp.id_pedido', $arrIdsPedido)
            ->select('pp.id_pedido', 'pp.estado', 'pp.descripcion', 'pp.id_usuario', 'u.username', 'pp.fecha_registro', 'pp.tipo_proceso', 'pp.progreso', 'pp.total_procesar', 'pp.cant_procesado', 'pp.last_update')
            ->get();

        return $arrPedidoProceso;
    }
}
