<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use SoapClient;
use yura\Modelos\Aerolinea;
use yura\Modelos\ClasificacionRamo;
use yura\Modelos\Cliente;
use yura\Modelos\ClienteConsignatario;
use yura\Modelos\ClienteDatoExportacion;
use yura\Modelos\ClientePedidoEspecificacion;
use yura\Modelos\Comprobante;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\DataTallos;
use yura\Modelos\DatosExportacion;
use yura\Modelos\DetalleEnvio;
use yura\Modelos\DetalleEspecificacionEmpaque;
use yura\Modelos\DetalleEspecificacionEmpaqueRamosCaja;
use yura\Modelos\Especificacion;
use yura\Modelos\EspecificacionEmpaque;
use yura\Modelos\Pais;
use yura\Modelos\Pedido;
use yura\Modelos\DetallePedido;
use yura\Modelos\Coloracion;
use yura\Modelos\Envio;
use yura\Modelos\DetallePedidoDatoExportacion;
use yura\Modelos\Empaque;
use Validator;
use DB;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use yura\Modelos\PedidoCancelado;
use yura\Modelos\PedidoUnificado;
use yura\Modelos\ProductoYuraVenture;
use yura\Http\Controllers\ComprobanteController;
use yura\Jobs\CrearPedidos2doPlano;
use yura\Jobs\EdicionStandings;
use yura\Jobs\EditaStandingOrder;
use yura\Jobs\StorePedidoModificacion;
use yura\Modelos\Consignatario;
use yura\Modelos\DetallePedidoPerdido;
use yura\Modelos\Marcacion;
use yura\Modelos\MarcacionColoracion;
use yura\Modelos\PedidoModificacion;
use yura\Modelos\PedidoPerdido;
use yura\Modelos\Planta;
use yura\Modelos\Semana;
use yura\Modelos\UnidadMedida;
use yura\Modelos\Variedad;
use Illuminate\Support\Facades\Artisan;
use Session;
use yura\Modelos\DetalleEspecificacionEmpaqueRamosXCajaPerdido;
use yura\Modelos\DistribucionMixtos;
use yura\Jobs\DistribucionRecetas;
use yura\Jobs\jobCopiarPedido;
use yura\Jobs\jobCosechaEstimada;
use yura\Jobs\jobCrearOrdenFija;
use yura\Jobs\jobGrabarPedido;
use yura\Jobs\jobUpdateOrdenFija;
use yura\Modelos\DetalleDespacho;
use yura\Modelos\PedidoConfirmacion;

class PedidoController extends Controller
{
    public function listar_pedidos(Request $request)
    {
        return view(
            'adminlte.gestion.postcocecha.pedidos.inicio',
            [
                'idCliente' => $request->id_cliente,
                'annos' => DB::table('pedido as p')->select(DB::raw('YEAR(p.fecha_pedido) as anno'))->distinct()->get(),
                'especificaciones' => DB::table('pedido as p')
                    ->join('cliente_pedido_especificacion as cpe', 'p.id_cliente', '=', 'cpe.id_cliente')
                    ->join('especificacion as esp', 'cpe.id_especificacion', '=', 'esp.id_especificacion')
                    ->where('p.id_cliente', $request->id_cliente)
                    ->select('esp.id_especificacion', 'esp.nombre', 'cpe.id_cliente_pedido_especificacion')
                    ->distinct()->get()
            ]
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ver_pedidos(Request $request)
    {
        // dd($request->all());
        $busquedaAnno = $request->has('busquedaAnno') ? $request->busquedaAnno : '';
        $busquedaEspecificacion = $request->has('id_especificaciones') ? $request->id_especificaciones : '';
        $busquedaDesde = $request->has('desde') ? $request->desde : '';
        $busquedaHasta = $request->has('hasta') ? $request->hasta : '';

        $listado = DB::table('pedido as p')
            ->join('cliente_pedido_especificacion as cpe', 'p.id_cliente', '=', 'cpe.id_cliente')
            ->join('especificacion as esp', 'cpe.id_especificacion', '=', 'esp.id_especificacion')
            ->join('detalle_pedido as dp', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
            ->where('p.id_cliente', $request->id_cliente)
            ->select('p.*')->distinct();

        if ($request->busquedaAnno != '')
            $listado = $listado->where(DB::raw('YEAR(p.fecha_pedido)'), $busquedaAnno);
        if ($request->id_especificaciones != '')
            $listado = $listado->where('dp.id_cliente_especificacion', $busquedaEspecificacion);
        if ($request->desde != '' && $request->hasta != '')
            $listado = $listado->whereBetween('p.fecha_pedido', [$busquedaDesde, $busquedaHasta]);

        $listado = $listado->orderBy('p.fecha_pedido', 'desc')->simplePaginate(20);

        $datos = [
            'listado' => $listado,
            'idCliente' => $request->id_cliente,
        ];
        return view('adminlte.gestion.postcocecha.pedidos.partials.listado', $datos);
    }

    public function add_pedido(Request $request)
    {
        $detalle_pedido = DetallePedido::where('id_pedido', $request->id_pedido)->get();

        $idCliente = count($detalle_pedido) ?  $detalle_pedido[0]->pedido->id_cliente : '';

        $clientes = DB::table('cliente as c')
            ->join('detalle_cliente as dt', 'c.id_cliente', '=', 'dt.id_cliente')
            ->where('dt.estado', 1)->orderBy('dt.nombre', 'asc')->get();

        $variedad = Variedad::where('estado', true)->get();

        $cajas = ClientePedidoEspecificacion::join('especificacion as esp', 'cliente_pedido_especificacion.id_especificacion', 'esp.id_especificacion')
            ->join('especificacion_empaque as ee', 'esp.id_especificacion', 'ee.id_especificacion')
            ->join('empaque as emp', 'ee.id_empaque', 'emp.id_empaque')
            ->where([
                ['cliente_pedido_especificacion.id_cliente', $idCliente],
                ['cliente_pedido_especificacion.estado', true],
                ['esp.tipo', 'N'],
                ['esp.creada', 'PRE-ESTABLECIDA'],
                ['esp.estado', true],
                ['ee.estado', true],
                ['emp.estado', true],
                ['emp.tipo', 'C']
            ])->select('emp.id_empaque', 'emp.nombre as caja')->distinct()->get();

        $plantas = ClientePedidoEspecificacion::join('especificacion as esp', 'cliente_pedido_especificacion.id_especificacion', 'esp.id_especificacion')
            ->join('especificacion_empaque as ee', 'esp.id_especificacion', 'ee.id_especificacion')
            ->join('detalle_especificacionempaque as det_esp_emp', 'ee.id_especificacion_empaque', 'det_esp_emp.id_especificacion_empaque')
            ->join('variedad as v', 'det_esp_emp.id_variedad', 'v.id_variedad')
            ->join('planta as p', 'v.id_planta', 'p.id_planta')
            ->where([
                ['cliente_pedido_especificacion.id_cliente', $idCliente],
                ['cliente_pedido_especificacion.estado', true],
                ['esp.tipo', 'N'],
                ['esp.creada', 'PRE-ESTABLECIDA'],
                ['esp.estado', true],
                ['ee.estado', true],
                ['p.estado', true],
                ['v.estado', true]
            ])->select('p.id_planta', 'p.nombre as planta')->distinct()->get();

        $presentaciones = Empaque::where('tipo', 'P')->where('estado', true)
            ->select('id_empaque', 'nombre')->get();

        $datos_exportacion = ClienteDatoExportacion::where('id_cliente', $idCliente)->get();

        $agencia_cargas =  DB::table('agencia_carga as ac')
            ->where([
                ['ac.estado', 1]
            ])->get();

        $marcaciones = DatosExportacion::where('estado', 1)->get();

        $clasificacionRamos = ClasificacionRamo::where('estado', 1)->orderBy('nombre', 'asc')->get();

        return view(
            'adminlte.gestion.postcocecha.pedidos.forms.add_pedido',
            [
                'idCliente' => $idCliente,
                'pedido_fijo' => $request->pedido_fijo,
                'vista' => $request->vista,
                'marcaciones' => $marcaciones,
                'variedades' => $variedad,
                'cajas' => $cajas,
                'clientes' => $clientes,
                'id_pedido' => $request->id_pedido,
                'detalles' => $detalle_pedido,
                'datos_exportacion' => $datos_exportacion,
                'agenciasCarga' => $agencia_cargas,
                'presentaciones' => (string)$presentaciones,
                'comprobante' => isset($request->id_pedido) ? (isset(getPedido($request->id_pedido)->envios[0]->comprobante) ? getPedido($request->id_pedido)->envios[0]->comprobante : null) : null,
                'clasificacionRamos' => $clasificacionRamos,
                'plantas' => $plantas,
                'tipo' => $request->tipo
            ]
        );
    }

    public function store_pedidos(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $valida = Validator::make($request->all(), [
            'id_cliente' => 'required',
            'numero_ficticio' => 'required_if:factura_ficticia,true'
        ], [
            'numero_ficticio.required_if' => 'Debe colocar el numero manualmente para la factura'
        ]);

        if (!$valida->fails()) {

            DB::beginTransaction();

            try {
                if (empty($request->arrFechas))
                    $request->arrFechas = [$request->fecha_de_entrega];

                if ($request->tipo_pedido == 'STANDING ORDER') {
                    $numeroOrdenFija = DB::table('pedido')
                        ->select(DB::raw('max(orden_fija) as cantidad'))
                        ->get()[0]->cantidad;
                    $numeroOrdenFija = $numeroOrdenFija != '' ? ($numeroOrdenFija + 1) : 1;
                }

                if (isset($request->id_pedido)) {

                    $oldPedido = Pedido::find($request->id_pedido);
                    $numeroOrdenFija = $oldPedido->orden_fija;

                    /*if ($request->edita_standing == 'SI') {

                        $oldCpeClientePedido = $oldPedido->detalles->pluck('id_cliente_especificacion')->toArray();

                        $marcaciones = [];

                        foreach ($oldPedido->detalles as $detalle) {

                            foreach ($detalle->detalle_pedido_dato_exportacion as $dpde) {
                                $marcaciones[] = [
                                    'id_dato_exportacion' => $dpde->id_dato_exportacion,
                                    'valor' => $dpde->valor
                                ];
                            }
                        }

                        if (count($marcaciones)) {

                            $fechasNuevosPedidos = Pedido::where([
                                ['pedido.tipo_pedido', 'STANDING ORDER'],
                                ['pedido.fecha_pedido', '>=', $oldPedido->fecha_pedido],
                                [DB::raw("DAYOFWEEK(pedido.fecha_pedido)"), Carbon::parse($oldPedido->fecha_pedido)->dayOfWeek + 1]
                            ])->join('detalle_pedido as dp', function ($j) use ($oldCpeClientePedido) {
                                $j->on('pedido.id_pedido', 'dp.id_pedido')->whereIn('dp.id_cliente_especificacion', $oldCpeClientePedido);
                            })->join('detallepedido_datoexportacion as dpde', function ($j) use ($marcaciones) {
                                $j->on('dp.id_detalle_pedido', 'dpde.id_detalle_pedido')
                                    ->whereIn('dpde.id_dato_exportacion', array_column($marcaciones, 'id_dato_exportacion'))
                                    ->whereIn('dpde.valor', array_column($marcaciones, 'valor'));
                            })->join('cliente_pedido_especificacion as cpe', function ($j) use ($oldPedido) {
                                $j->on('cpe.id_cliente_pedido_especificacion', 'dp.id_cliente_especificacion')->where('cpe.id_cliente', $oldPedido->id_cliente);
                            })->select('pedido.fecha_pedido', 'pedido.id_pedido', 'pedido.packing')->distinct()->get();
                        } else {

                            $fechasNuevosPedidos = Pedido::where([
                                ['pedido.tipo_pedido', 'STANDING ORDER'],
                                ['pedido.fecha_pedido', '>=', $oldPedido->fecha_pedido],
                                [DB::raw("DAYOFWEEK(pedido.fecha_pedido)"), Carbon::parse($oldPedido->fecha_pedido)->dayOfWeek + 1]
                            ])->join('detalle_pedido as dp', function ($j) use ($oldCpeClientePedido) {
                                $j->on('pedido.id_pedido', 'dp.id_pedido')->whereIn('dp.id_cliente_especificacion', $oldCpeClientePedido);
                            })->join('cliente_pedido_especificacion as cpe', function ($j) use ($oldPedido) {
                                $j->on('cpe.id_cliente_pedido_especificacion', 'dp.id_cliente_especificacion')->where('cpe.id_cliente', $oldPedido->id_cliente);
                            })->where(DB::raw("(SELECT COUNT(*) FROM detallepedido_datoexportacion as dpde WHERE dpde.id_detalle_pedido = dp.id_detalle_pedido)"), 0)
                                ->select('pedido.fecha_pedido', 'pedido.id_pedido', 'pedido.packing')->distinct()->get();
                        }

                        $request->arrFechas = $fechasNuevosPedidos->pluck('fecha_pedido')->toArray();

                        //SI SE VA A MOVER EL STANDING A OTRA FECHA
                        if ($oldPedido->fecha_pedido != $request->fecha_de_entrega) {

                            $nuevasFechas = [];
                            $nuevaFecha = Carbon::parse($request->fecha_de_entrega);

                            for ($i = 0; $i < count($fechasNuevosPedidos); $i++) {

                                if ($i == 0) {
                                    $nuevasFechas[] = $request->fecha_de_entrega;
                                } else {
                                    $nuevasFechas[] = $nuevaFecha->addWeek()->toDateString();
                                }
                            }

                            $request->arrFechas = $nuevasFechas;
                        }

                        $idsEliminarPedido = $fechasNuevosPedidos->pluck('id_pedido')->toArray();

                        // dd($request->arrFechas, $idsEliminarPedido);

                    }*/
                }

                $arrDataDetallesPedidoDecode = json_decode($request->arrDataDetallesPedido, true);
                $arrDataDetallesPedido = (array)$arrDataDetallesPedidoDecode;

                foreach ($request->arrFechas as $key => $fechas) {

                    $formatoFecha = '';

                    if (isset($request->opcion) && $request->opcion != 3) {
                        $formato = explode("/", $fechas);
                        $formatoFecha = $formato[2] . '-' . $formato[0] . '-' . $formato[1];
                    }

                    $fechaFormateada = (isset($request->opcion) && $request->opcion != 3) ? $formatoFecha : $fechas;

                    if (!empty($request->id_pedido) && $key == 0) { //ACTUALIZAR LOS DATOS DEL ENVIO DEL PRIMER PEDIO

                        $dataEnvio = Envio::where('id_pedido', $request->id_pedido)->first();

                        if (isset($dataEnvio->id_envio)) {

                            $aerolinea = isset($dataEnvio->detalles[0]) ? $dataEnvio->detalles[0]->id_aerolinea : null;
                            $id_configuracion_empresa = $dataEnvio->pedido->id_configuracion_empresa;
                        }
                    }

                    $empresa = ConfiguracionEmpresa::All()->where('estado', true)->first();

                    $objPedido = new Pedido;
                    $p = Pedido::orderBy('id_pedido', 'desc')->first();
                    $objPedido->id_pedido = isset($p->id_pedido) ? $p->id_pedido + 1 : 1;
                    $objPedido->id_cliente = $request->id_cliente;
                    $objPedido->descripcion = $request->descripcion;
                    $objPedido->tipo_pedido = !isset($request->tipo_pedido) ? 'OPEN MARKET' : $request->tipo_pedido;
                    $objPedido->fecha_pedido = $fechaFormateada;
                    $objPedido->id_configuracion_empresa = isset($id_configuracion_empresa) ? $id_configuracion_empresa : $request->id_configuracion_empresa;
                    $objPedido->variedad = '';
                    $objPedido->packing = (isset($fechasNuevosPedidos) && isset($fechasNuevosPedidos[$key])) ? ($fechasNuevosPedidos[$key]->packing != '' ? $fechasNuevosPedidos[$key]->packing : $empresa->numero_packing) : $empresa->numero_packing;
                    if ($request->tipo_pedido == 'STANDING ORDER') {
                        $objPedido->orden_fija = $numeroOrdenFija;
                    }
                    if ($objPedido->save()) {
                        Artisan::call('update:pedido_confirmacion', [
                            'fecha' => $fechaFormateada
                        ]);

                        if ((!(isset($fechasNuevosPedidos) && isset($fechasNuevosPedidos[$key]))) || $fechasNuevosPedidos[$key]->packing != '') {
                            $empresa->numero_packing += 1;
                            $empresa->save();
                        }

                        $model = Pedido::orderBy('id_pedido', 'desc')->first();
                        //bitacora('pedido', $model->id_pedido, 'I', 'Insercion satisfactoria de un nuevo pedido');

                        foreach ($arrDataDetallesPedido as $key => $item) {

                            $objDetallePedido = new DetallePedido;
                            $detPed = DetallePedido::orderBy('id_detalle_pedido', 'desc')->first();
                            $objDetallePedido->id_detalle_pedido = isset($detPed->id_detalle_pedido) ? $detPed->id_detalle_pedido + 1 : 1;

                            //SI UN DETALLE PEDIDO (ESPECIFICACION) DEL PEDIDO ES CREADA MANUALMENTE SE CREA UNA NUEVA ESPECIFICACION DE TIPO 'O' Y SE ATA AL CLIENTE
                            if (!isset($item['id_cliente_pedido_especificacion'])) {

                                $id_cliente_pedido_especificacion = '';
                                $arr_det_esp_emp = [];
                                $objEspecificacion = new Especificacion;
                                $esp = Especificacion::orderBy('id_especificacion', 'desc')->first();
                                $objEspecificacion->id_especificacion = isset($esp->id_especificacion) ? $esp->id_especificacion + 1 : 1;
                                $objEspecificacion->estado = 1;
                                $objEspecificacion->tipo = 'N';
                                $objEspecificacion->creada = 'EJECUCION';
                                $objEspecificacion->save();

                                $modelEspecificacion = Especificacion::orderBy('id_especificacion', 'desc')->first();

                                $objEspecificacionEmpaque = new EspecificacionEmpaque;
                                $espEmpaque = EspecificacionEmpaque::orderBy('id_especificacion_empaque', 'desc')->first();
                                $objEspecificacionEmpaque->id_especificacion_empaque = isset($espEmpaque->id_especificacion_empaque) ? $espEmpaque->id_especificacion_empaque + 1 : 1;
                                $objEspecificacionEmpaque->id_especificacion = $modelEspecificacion->id_especificacion;
                                $objEspecificacionEmpaque->id_empaque = $item['especificacion_combo'][0]['caja'];
                                $objEspecificacionEmpaque->cantidad = 1;
                                $objEspecificacionEmpaque->save();

                                foreach ($item['especificacion_combo'] as $ad) {

                                    $modelEspecificacionEmpaque = EspecificacionEmpaque::orderBy('id_especificacion_empaque', 'desc')->first();

                                    $objDetalleEspecificacionEmpaque = new DetalleEspecificacionEmpaque;
                                    $detEspEmpaque = DetalleEspecificacionEmpaque::orderBy('id_detalle_especificacionempaque', 'desc')->first();
                                    $objDetalleEspecificacionEmpaque->id_detalle_especificacionempaque = isset($detEspEmpaque->id_detalle_especificacionempaque) ? $detEspEmpaque->id_detalle_especificacionempaque + 1 : 1;
                                    $objDetalleEspecificacionEmpaque->id_especificacion_empaque = $modelEspecificacionEmpaque->id_especificacion_empaque;
                                    $objDetalleEspecificacionEmpaque->id_variedad = $ad['variedad'];
                                    $objDetalleEspecificacionEmpaque->id_clasificacion_ramo = $ad['id_clasificacion_ramo'];
                                    $objDetalleEspecificacionEmpaque->cantidad = $ad['ramos_x_caja'];
                                    $objDetalleEspecificacionEmpaque->id_empaque_p = $ad['presentacion'];
                                    $objDetalleEspecificacionEmpaque->tallos_x_ramos = $ad['tallos_x_ramos'];
                                    $objDetalleEspecificacionEmpaque->longitud_ramo = $ad['longitud_ramo'];
                                    $objDetalleEspecificacionEmpaque->id_unidad_medida = $ad['unidad_medida'];

                                    $objDetalleEspecificacionEmpaque->save();

                                    $arr_det_esp_emp[] = $objDetalleEspecificacionEmpaque->id_detalle_especificacionempaque;

                                    $objClientePedidoEspecificacion = new ClientePedidoEspecificacion;
                                    $cpe = ClientePedidoEspecificacion::orderBy('id_cliente_pedido_especificacion', 'desc')->first();
                                    $objClientePedidoEspecificacion->id_cliente_pedido_especificacion = isset($cpe->id_cliente_pedido_especificacion) ? $cpe->id_cliente_pedido_especificacion + 1 : 1;
                                    $objClientePedidoEspecificacion->id_cliente = $request->id_cliente;
                                    $objClientePedidoEspecificacion->id_especificacion = $modelEspecificacion->id_especificacion;

                                    if ($objClientePedidoEspecificacion->save()) {
                                        $modelClientePedidoEspecificacion = ClientePedidoEspecificacion::orderBy('id_cliente_pedido_especificacion', 'desc')->first();
                                        $id_cliente_pedido_especificacion = $modelClientePedidoEspecificacion->id_cliente_pedido_especificacion;
                                    }
                                }

                                $objDetallePedido->id_cliente_especificacion = $id_cliente_pedido_especificacion;

                                $precio = substr($item['precio'], 0, -1);

                                $arr_precios = explode('|', $precio);
                                $nuevo_precio = '';

                                foreach ($arr_precios as $x => $ap)
                                    $nuevo_precio .= explode(';', $ap)[0] . ';' . $arr_det_esp_emp[$x] . '|';


                                $precio = substr($nuevo_precio, 0, -1);

                                $newRequest = $request->all();
                                $newRequest['arrDataDetallesPedido'] = $arrDataDetallesPedido;
                                $newRequest['arrDataDetallesPedido'][$key]['id_cliente_pedido_especificacion'] = $id_cliente_pedido_especificacion;
                                $newRequest['arrDataDetallesPedido'][$key]['precio'] = $precio;
                                $request = new Request($newRequest);
                            } else {

                                $objDetallePedido->id_cliente_especificacion = $item['id_cliente_pedido_especificacion'];
                                $precio = substr($item['precio'], 0, -1);
                            }

                            $objDetallePedido->id_pedido = $model->id_pedido;
                            $objDetallePedido->id_agencia_carga = $item['id_agencia_carga'];
                            $objDetallePedido->cantidad = $item['cantidad'];
                            $objDetallePedido->precio = $precio;
                            $objDetallePedido->orden = 1;/* $item['orden']; */

                            if ($objDetallePedido->save()) {
                                $modelDetallePedido = DetallePedido::orderBy('id_detalle_pedido', 'desc')->first();

                                //GUARDAR LOS RAMOS X CAJAS MODIFICADOS EN EL PEDIDO DE CADA DETALLE_SPECIFICACION_EMPAQUE
                                if (isset($item['arr_custom_ramos_x_caja']) && count($item['arr_custom_ramos_x_caja']) > 0) {
                                    foreach ($item['arr_custom_ramos_x_caja'] as $z => $customRamosXCaja) {
                                        $objDetEspEmpRxC = new DetalleEspecificacionEmpaqueRamosCaja;
                                        $detEspEmpRxC = DetalleEspecificacionEmpaqueRamosCaja::orderBy('id_detalle_especificacionempaque_ramos_x_caja', 'desc')->first();
                                        $objDetEspEmpRxC->id_detalle_especificacionempaque_ramos_x_caja = isset($detEspEmpRxC->id_detalle_especificacionempaque_ramos_x_caja) ? $detEspEmpRxC->id_detalle_especificacionempaque_ramos_x_caja + 1 : 1;
                                        $objDetEspEmpRxC->id_detalle_pedido = $modelDetallePedido->id_detalle_pedido;
                                        $objDetEspEmpRxC->id_detalle_especificacionempaque = $customRamosXCaja['id_det_esp_emp'];
                                        $objDetEspEmpRxC->cantidad = $customRamosXCaja['ramos_x_caja'];
                                        $objDetEspEmpRxC->fecha_registro = now()->format('Y-m-d H:i:s.v');
                                        $objDetEspEmpRxC->save();
                                    }
                                }

                                $decodeArrDatosExportacion = json_decode($request->arrDatosExportacion, true);
                                $arrDatosExportacion = (array)$decodeArrDatosExportacion;

                                if ($arrDatosExportacion != '' && isset($arrDatosExportacion[$key])) {

                                    foreach ($arrDatosExportacion[$key] as $de) {

                                        if (isset($de['valor']) && $de['valor'] != null) {
                                            $objDetallePedidoDatoExportacion = new DetallePedidoDatoExportacion;
                                            $dpe = DetallePedidoDatoExportacion::orderBy('id_detallepedido_datoexportacion', 'desc')->first();
                                            $objDetallePedidoDatoExportacion->id_detallepedido_datoexportacion = isset($dpe->id_detallepedido_datoexportacion) ? $dpe->id_detallepedido_datoexportacion + 1 : 1;
                                            $objDetallePedidoDatoExportacion->id_detalle_pedido = $modelDetallePedido->id_detalle_pedido;
                                            $objDetallePedidoDatoExportacion->id_dato_exportacion = $de['id_dato_exportacion'];
                                            $objDetallePedidoDatoExportacion->valor = $de['valor'];
                                            if ($objDetallePedidoDatoExportacion->save()) {
                                                $modelDetallePedidoDatoExportacion = DetallePedidoDatoExportacion::orderBy('id_detallepedido_datoexportacion', 'desc')->first();
                                                bitacora('detallepedido_datoexportacion', $modelDetallePedidoDatoExportacion->id_detallepedido_datoexportacion, 'I', 'Insercion satisfactoria de un nuevo detallepedido_datoexportacion');
                                            }
                                        }
                                    }
                                }

                                $success = true;
                                $msg = '<div class="alert alert-success text-center">' .
                                    '<p> Se ha guardado el pedido exitosamente</p>'
                                    . '</div>';
                            }
                        }

                        $objEnvio = new Envio;
                        $env = Envio::orderBy('id_envio', 'desc')->first();
                        $objEnvio->id_envio = isset($env->id_envio) ? $env->id_envio + 1 : 1;
                        $objEnvio->fecha_envio = $fechaFormateada;
                        $objEnvio->id_pedido = $model->id_pedido;

                        if (isset($request->id_consignatario))
                            $objEnvio->id_consignatario = $request->id_consignatario;

                        // if (isset($codigo_dae)) {
                        $objEnvio->codigo_dae = isset($dataEnvio) ? $dataEnvio->codigo_dae : '';
                        $objEnvio->dae = isset($dataEnvio) ? $dataEnvio->dae : '';
                        $objEnvio->guia_madre = isset($dataEnvio) ? $dataEnvio->guia_madre : '';
                        $objEnvio->guia_hija = isset($dataEnvio) ? $dataEnvio->guia_hija : '';
                        $objEnvio->email = isset($dataEnvio) ? $dataEnvio->email : '';
                        $objEnvio->telefono = isset($dataEnvio) ? $dataEnvio->telefono : '';
                        $objEnvio->direccion = isset($dataEnvio) ? $dataEnvio->direccion : '';
                        $objEnvio->codigo_pais = isset($dataEnvio) ? $dataEnvio->codigo_pais : '';
                        $objEnvio->almacen = isset($dataEnvio) ? $dataEnvio->almacen : '';
                        //}

                        if ($objEnvio->save()) {

                            $modelEnvio = Envio::orderBy('id_envio', 'desc')->first();

                            bitacora('envio', $modelEnvio->id_envio, 'I', 'Insercion satisfactoria de un nuevo envio');

                            $dataDetallePedido = DetallePedido::where('id_pedido', $model->id_pedido)
                                ->join('cliente_pedido_especificacion as cpe', 'detalle_pedido.id_cliente_especificacion', 'cpe.id_cliente_pedido_especificacion')
                                ->select('cpe.id_especificacion', 'detalle_pedido.cantidad')->get();

                            foreach ($dataDetallePedido as $detallePeido) {
                                $objDetalleEnvio = new DetalleEnvio;
                                $detEnv = DetalleEnvio::orderBy('id_detalle_envio', 'desc')->first();
                                $objDetalleEnvio->id_detalle_envio = isset($detEnv->id_detalle_envio) ? $detEnv->id_detalle_envio + 1 : 1;
                                $objDetalleEnvio->id_envio = $modelEnvio->id_envio;
                                $objDetalleEnvio->id_especificacion = $detallePeido->id_especificacion;
                                $objDetalleEnvio->cantidad = $detallePeido->cantidad;
                                isset($aerolinea) ? $objDetalleEnvio->id_aerolinea = $aerolinea : "";
                                if ($objDetalleEnvio->save()) {
                                    $modelDetalleEnvio = DetalleEnvio::orderBy('id_detalle_envio', 'desc')->first();
                                    bitacora('detalle_envio', $modelDetalleEnvio->id_detalle_envio, 'I', 'Insercion satisfactoria de un nuevo detalle envio');
                                }
                            }
                        }

                        /* ======= ACTUALIZAR LA TABLA COSECHA_DIARIA ========== */
                        DistribucionRecetas::dispatch($objPedido->id_pedido)->onQueue('buquets');
                    }
                }

                $oldPedido = !isset($request->id_pedido) ? Pedido::find($model->id_pedido) : Pedido::find($request->id_pedido);
                $newPedido = Pedido::find($model->id_pedido);

                //dd(now()->toDateString(), Carbon::parse($oldPedido->fecha_pedido)->subDay()->toDateString(), $oldPedido->fecha_pedido);
                if (hoy() == $oldPedido->fecha_pedido || hoy() == opDiasFecha('-', 1, $oldPedido->fecha_pedido)) {

                    foreach ($newPedido->detalles as $x => $det_ped) {

                        if (!isset($oldPedido->detalles[$x])) { // agregar un nuevo detalle

                            foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {

                                foreach ($esp_emp->detalles as $det_esp_emp) {

                                    $pedidoModificacion = new PedidoModificacion;
                                    $pedidoModificacion->id_cliente = $oldPedido->id_cliente;
                                    $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                                    $pedidoModificacion->fecha_nuevo_pedido = $oldPedido->fecha_pedido;
                                    $pedidoModificacion->fecha_anterior_pedido = $newPedido->fecha_pedido;
                                    $pedidoModificacion->cantidad = $det_ped->cantidad;
                                    $pedidoModificacion->operador = '+';
                                    $pedidoModificacion->id_usuario = Session::get('id_usuario');
                                    $getRamosXCajaModificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);
                                    $ramos_x_caja = isset($getRamosXCajaModificado) ? $getRamosXCajaModificado->cantidad : $det_esp_emp->cantidad;
                                    $pedidoModificacion->ramos_x_caja = $ramos_x_caja;
                                    $pedidoModificacion->save();
                                }
                            }
                        } else {

                            $oldDetPed = $oldPedido->detalles[$x];

                            if ($newPedido->fecha_pedido != $oldPedido->fecha_pedido) { // cambio de fecha del pedido VIEJO_PEDIDO

                                //dd('cambio de fecha del VIEJO pedido', $newPedido->fecha_pedido, $oldPedido->fecha_pedido);
                                foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {

                                    foreach ($esp_emp->detalles as $det_esp_emp) {

                                        $pedidoModificacion = new PedidoModificacion;
                                        $pedidoModificacion->id_cliente = $oldPedido->id_cliente;
                                        $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                                        $pedidoModificacion->fecha_nuevo_pedido = $newPedido->fecha_pedido;
                                        $pedidoModificacion->fecha_anterior_pedido = $oldPedido->fecha_pedido;
                                        $pedidoModificacion->cantidad = $det_ped->cantidad;
                                        $pedidoModificacion->operador = '-';
                                        $pedidoModificacion->id_usuario = Session::get('id_usuario');
                                        $getRamosXCajaModificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);
                                        $ramos_x_caja = isset($getRamosXCajaModificado) ? $getRamosXCajaModificado->cantidad : $det_esp_emp->cantidad;
                                        $pedidoModificacion->ramos_x_caja = $ramos_x_caja;
                                        $pedidoModificacion->save();

                                        if (hoy() == $newPedido->fecha_pedido || hoy() == opDiasFecha('-', 1, $newPedido->fecha_pedido)) {
                                            //dd('cambio de fecha del NUEVO pedido', $newPedido->fecha_pedido, $oldPedido->fecha_pedido);
                                            $pedidoModificacion = new PedidoModificacion;
                                            $pedidoModificacion->id_cliente = $oldPedido->id_cliente;
                                            $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                                            $pedidoModificacion->fecha_nuevo_pedido = $oldPedido->fecha_pedido;
                                            $pedidoModificacion->fecha_anterior_pedido = $newPedido->fecha_pedido;
                                            $pedidoModificacion->cantidad = $det_ped->cantidad;
                                            $pedidoModificacion->operador = '+';
                                            $pedidoModificacion->id_usuario = Session::get('id_usuario');
                                            $pedidoModificacion->cambio_fecha = 1;
                                            $pedidoModificacion->ramos_x_caja = $ramos_x_caja;
                                            $pedidoModificacion->save();
                                        }
                                    }
                                }
                            } else if ($oldDetPed->cantidad != $det_ped->cantidad) {    // cambio en las cantidades de piezas de un det_ped

                                foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {

                                    foreach ($esp_emp->detalles as $det_esp_emp) {

                                        $pedidoModificacion = new PedidoModificacion;
                                        $pedidoModificacion->id_cliente = $oldPedido->id_cliente;
                                        $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                                        $pedidoModificacion->fecha_nuevo_pedido = $oldPedido->fecha_pedido;
                                        $pedidoModificacion->fecha_anterior_pedido = $oldPedido->fecha_pedido;
                                        $pedidoModificacion->cantidad = abs($det_ped->cantidad - $oldDetPed->cantidad);
                                        $pedidoModificacion->operador = $det_ped->cantidad > $oldDetPed->cantidad ? '+' : '-';
                                        $pedidoModificacion->id_usuario = Session::get('id_usuario');
                                        $getRamosXCajaModificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);
                                        $ramos_x_caja = isset($getRamosXCajaModificado) ? $getRamosXCajaModificado->cantidad : $det_esp_emp->cantidad;
                                        $pedidoModificacion->ramos_x_caja = $ramos_x_caja;
                                        $pedidoModificacion->save();
                                    }
                                }
                            } else if (!isset($request->id_pedido)) {   // nuevo pedido

                                foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {

                                    foreach ($esp_emp->detalles as $det_esp_emp) {
                                        //dd('nuevo pedido');
                                        $pedidoModificacion = new PedidoModificacion;
                                        $pedidoModificacion->id_cliente = $oldPedido->id_cliente;
                                        $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                                        $pedidoModificacion->fecha_nuevo_pedido = $oldPedido->fecha_pedido;
                                        $pedidoModificacion->fecha_anterior_pedido = $newPedido->fecha_pedido;
                                        $pedidoModificacion->cantidad = $det_ped->cantidad;
                                        $pedidoModificacion->operador = '+';
                                        $pedidoModificacion->id_usuario = Session::get('id_usuario');
                                        $getRamosXCajaModificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);
                                        $ramos_x_caja = isset($getRamosXCajaModificado) ? $getRamosXCajaModificado->cantidad : $det_esp_emp->cantidad;
                                        $pedidoModificacion->ramos_x_caja = $ramos_x_caja;
                                        $pedidoModificacion->save();
                                    }
                                }
                            }
                        }
                    }
                } else if ($newPedido->fecha_pedido != $oldPedido->fecha_pedido) { // cambio de fecha del pedido NUEVO_PEDIDO
                    if (hoy() == $newPedido->fecha_pedido || hoy() == opDiasFecha('-', 1, $newPedido->fecha_pedido)) {
                        //dd('cambio de fecha del VIEJO pedido', $newPedido->fecha_pedido, $oldPedido->fecha_pedido);
                        foreach ($newPedido->detalles as $x => $det_ped) {
                            foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {

                                foreach ($esp_emp->detalles as $det_esp_emp) {

                                    //dd('cambio de fecha del NUEVO pedido', $newPedido->fecha_pedido, $oldPedido->fecha_pedido);
                                    $getRamosXCajaModificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);
                                    $ramos_x_caja = isset($getRamosXCajaModificado) ? $getRamosXCajaModificado->cantidad : $det_esp_emp->cantidad;
                                    $pedidoModificacion = new PedidoModificacion;
                                    $pedidoModificacion->id_cliente = $oldPedido->id_cliente;
                                    $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                                    $pedidoModificacion->fecha_nuevo_pedido = $oldPedido->fecha_pedido;
                                    $pedidoModificacion->fecha_anterior_pedido = $newPedido->fecha_pedido;
                                    $pedidoModificacion->cantidad = $det_ped->cantidad;
                                    $pedidoModificacion->operador = '+';
                                    $pedidoModificacion->id_usuario = Session::get('id_usuario');
                                    $pedidoModificacion->cambio_fecha = 1;
                                    $pedidoModificacion->ramos_x_caja = $ramos_x_caja;
                                    $pedidoModificacion->save();
                                }
                            }
                        }
                    }
                }

                if (isset($idsEliminarPedido) && count($idsEliminarPedido)) {

                    foreach ($idsEliminarPedido as $idPedido)
                        Pedido::destroy($idPedido);
                }

                Pedido::destroy($request->id_pedido);

                DB::commit();
            } catch (\Exception $e) {

                DB::rollBack();
                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                    '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
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

        // SE CREA EL RESTO DE PEDIOS EN 2do PLANO
        //COMENTAR CUANDO SE USA LA CARGA DE PEDIDOS DESDE EL EXCEL

        /* if(count($otrasFechas)){

            $data = $request->all();
            $data['arrFechas'] = $otrasFechas;
            CrearPedidos2doPlano::dispatch($data)->onQueue('crear_pedidos_2do_plano');

        } */

        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function inputs_pedidos(Request $request)
    {   //dd($request->all());
        $tipo_especificacion = DetallePedido::where('id_pedido', $request->id_pedido)
            ->join('cliente_pedido_especificacion as cpe', 'detalle_pedido.id_cliente_especificacion', 'cpe.id_cliente_pedido_especificacion')
            ->join('especificacion as esp', 'cpe.id_especificacion', 'esp.id_especificacion')->select('tipo')->first();

        if (!isset($request->editar) || !$request->editar) {

            $data_especificaciones = DB::table('cliente_pedido_especificacion as cpe')
                ->join('especificacion as esp', function ($j) {
                    $j->on('cpe.id_especificacion', 'esp.id_especificacion')
                        ->where('esp.creada', 'PRE-ESTABLECIDA');
                })->join('especificacion_empaque as ee', function ($join) use ($request) {

                    $join->on('cpe.id_especificacion', 'ee.id_especificacion');
                    isset($request->id_caja) && $join->where('ee.id_empaque', $request->id_caja);
                })->join('detalle_especificacionempaque as de', function ($join) use ($request) {

                    $join->on('ee.id_especificacion_empaque', 'de.id_especificacion_empaque');
                    isset($request->id_variedad) && $join->where('de.id_variedad', $request->id_variedad);
                })->join('variedad as v', function ($join) use ($request) {

                    $join->on('de.id_variedad', 'v.id_variedad');

                    if ($request->id_planta != '' && $request->id_variedad != '') {

                        $join->where('v.id_variedad', $request->id_variedad);
                    } else if ($request->id_planta != '' && $request->id_variedad == '') {

                        $idVariedades = Variedad::where([
                            ['id_planta', $request->id_planta],
                            ['estado', true]
                        ])->get()->pluck('id_variedad')->toArray();

                        $join->whereIn('v.id_variedad', $idVariedades);
                    }
                })->where([
                    ['cpe.id_cliente', $request->id_cliente],
                    ['esp.tipo', isset($tipo_especificacion->tipo) ? $tipo_especificacion->tipo : "N"],
                    ['esp.estado', 1],
                    ['cpe.estado', 1],
                    ['v.estado', 1],
                ])->where(function ($w) use ($request) {

                    if (isset($request->rxc) && $request->rxc > 0)
                        $w->where('de.cantidad', $request->rxc);
                })->select('v.orden', 'cpe.id_cliente_pedido_especificacion', 'cpe.id_especificacion', 'cpe.id_cliente', 'v.nombre')
                ->orderBy('v.orden', 'asc')->distinct();

            if (isset($tipo_especificacion->tipo) && $tipo_especificacion->tipo == "O") {
                $data_especificaciones = $data_especificaciones->join('detalle_pedido as dp', 'cpe.id_cliente_pedido_especificacion', 'dp.id_cliente_especificacion')
                    ->where('id_pedido', $request->id_pedido);
            }

            $data_especificaciones = $data_especificaciones->orderBy('v.nombre', 'asc')->get();
        } else {
            $data_especificaciones = [];
        }

        $empT = [];
        $empTallos = Empaque::where([
            ['f_empaque', 'T'],
            ['tipo', 'C']
        ])->get();

        foreach ($empTallos as $empRamo)
            $empT[] = $empRamo;

        $empaque = Empaque::where([
            ['tipo', 'C'],
            ['estado', true]
        ])->get();

        $clasificacion_ramo = ClasificacionRamo::where('estado', true)->get();

        $presentacion = Empaque::where([
            ['tipo', 'P'],
            ['estado', true]
        ])->get();

        return view(
            'adminlte.gestion.postcocecha.pedidos.forms.paritals.inputs_dinamicos',
            [
                'especificaciones' => $data_especificaciones,
                'agenciasCarga' => DB::table('agencia_carga')->where('estado', true)->get(),
                'agenciasCargaCliente' => DB::table('cliente_agenciacarga as cac')
                    ->join('agencia_carga as ac', 'cac.id_agencia_carga', 'ac.id_agencia_carga')
                    ->where([
                        ['cac.id_cliente', $request->id_cliente],
                        ['cac.estado', 1]
                    ])->first(),
                'datos_exportacion' => DatosExportacion::join('cliente_datoexportacion as cde', 'dato_exportacion.id_dato_exportacion', 'cde.id_dato_exportacion')
                    ->where('id_cliente', $request->id_cliente)->get(),
                'emp_tallos' => $empT,
                'clasificacion_ramo' => $clasificacion_ramo,
                'presentacion' => $presentacion,
                'empaque' => $empaque,
                'id_pedido' => $request->id_pedido,
                'th_datos_exportacion' => !isset($request->datos_exportacion) ? [] : $request->datos_exportacion
            ]
        );
    }

    public function inputs_pedidos_edit(Request $request)
    {
        $esp_creadas = [];
        $pedido = getPedido($request->id_pedido);
        foreach ($pedido->detalles as $det_ped)
            $esp_creadas[] = $det_ped->cliente_especificacion->especificacion->id_especificacion;

        $especificaciones_restantes = DB::table('cliente_pedido_especificacion as cpe')
            ->join('especificacion as esp', 'cpe.id_especificacion', 'esp.id_especificacion')
            ->where([
                ['cpe.id_cliente', $request->id_cliente],
                ['esp.tipo', "N"],
                ['esp.estado', 1]
            ])->whereNotIn('esp.id_especificacion', $esp_creadas)
            ->orderBy('id_cliente_pedido_especificacion', 'asc')->get();

        $empT = [];
        $empTallos = Empaque::where([
            ['f_empaque', 'T'],
            ['tipo', 'C']
        ])->get();
        foreach ($empTallos as $empRamo)
            $empT[] = $empRamo;

        return view(
            'adminlte.gestion.postcocecha.pedidos.forms.paritals.inputs_dinamicos_edit',
            [
                'id_pedido' => $request->id_pedido,
                'datos_exportacion' => DatosExportacion::join('cliente_datoexportacion as cde', 'dato_exportacion.id_dato_exportacion', 'cde.id_dato_exportacion')
                    ->where('id_cliente', $request->id_cliente)->get(),
                'agenciasCarga' => DB::table('cliente_agenciacarga as cac')
                    ->join('agencia_carga as ac', 'cac.id_agencia_carga', 'ac.id_agencia_carga')
                    ->where([
                        ['cac.id_cliente', $request->id_cliente],
                        ['cac.estado', 1]
                    ])->get(),
                'especificaciones_restante' => $especificaciones_restantes,
                'emp_tallos' => $empT,

            ]
        );
    }

    public function actualizar_estado_pedido_detalle(Request $request)
    {
        $objDetallePedido = DetallePedido::find($request->id_detalle_pedido);
        $objDetallePedido->estado = $request->estado == 1 ? 0 : 1;

        if ($objDetallePedido->save()) {
            $model = DetallePedido::orderBy('id_detalle_pedido', 'desc')->first();
            $success = true;
            $msg = '<div class="alert alert-success text-center">' .
                '<p> Se ha actualizado el estado del detalle del pedido exitosamente</p>'
                . '</div>';
            bitacora('detalle_pedido', $model->id_detalle_pedido, 'U', 'Actualizacion satisfactoria del estado del detalle del pedido');
        } else {
            $success = false;
            $msg = '<div class="alert alert-success text-center">' .
                '<p> Ha ocurrido un error al guardar la informacion intente nuevamente</p>'
                . '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function cancelar_pedido(Request $request)
    {
        DB::beginTransaction();

        try {

            $pedido = getPedido($request->id_pedido);
            $fecha = $pedido->fecha_pedido;
            $marcaciones = [];
            $resumen_variedades = [];

            foreach ($pedido->detalles as $detalle) {

                foreach ($detalle->detalle_pedido_dato_exportacion as $dpde) {
                    $marcaciones[] = [
                        'id_dato_exportacion' => $dpde->id_dato_exportacion,
                        'valor' => $dpde->valor
                    ];
                }

                foreach ($detalle->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                    foreach ($esp_emp->detalles as $det_esp_emp) {
                        if (!in_array([
                            'variedad' => $det_esp_emp->id_variedad,
                            'longitud' => $det_esp_emp->longitud_ramo
                        ], $resumen_variedades)) {
                            $resumen_variedades[] = [
                                'variedad' => $det_esp_emp->id_variedad,
                                'longitud' => $det_esp_emp->longitud_ramo
                            ];
                        }
                    }
                }
            }

            if ($request->registra_perdido != '0') {
                $pedidoPerdido = new PedidoPerdido();
                $pedidoPerdido->id_cliente = $pedido->id_cliente;
                $pedidoPerdido->fecha_pedido = $pedido->fecha_pedido;
                $pedidoPerdido->id_usuario = session('id_usuario');
                $pedidoPerdido->fecha_registro = now()->toDateTimeString();
                $pedidoPerdido->tipo = $request->registra_perdido;
                $pedidoPerdido->save();
                $pedidoPerdido->id_pedido_perdido = DB::table('pedido_perdido')
                    ->select(DB::raw('max(id_pedido_perdido) as id'))
                    ->get()[0]->id;

                foreach ($pedido->detalles as $detalle) {
                    $detallePedido = DetallePedido::find($detalle->id_detalle_pedido);
                    $detallePedidoPerdido = new DetallePedidoPerdido();
                    $detallePedidoPerdido->id_cliente_especificacion = $detallePedido->id_cliente_especificacion;
                    $detallePedidoPerdido->id_pedido_perdido = $pedidoPerdido->id_pedido_perdido;
                    $detallePedidoPerdido->id_agencia_carga = $detallePedido->id_agencia_carga;
                    $detallePedidoPerdido->cantidad = $detallePedido->cantidad;
                    $detallePedidoPerdido->precio = $detallePedido->precio;
                    $detallePedidoPerdido->fecha_registro = now()->toDateTimeString();
                    $detallePedidoPerdido->save();
                    $detallePedidoPerdido->id_detalle_pedido_perdido = DB::table('detalle_pedido_perdido')
                        ->select(DB::raw('max(id_detalle_pedido_perdido) as id'))
                        ->get()[0]->id;

                    foreach ($detallePedido->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                        foreach ($esp_emp->detalles as $det_esp_emp) {
                            $getRamosXCajaModificado = getRamosXCajaModificado($detallePedido->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);
                            $ramos_x_caja = isset($getRamosXCajaModificado) ? $getRamosXCajaModificado->cantidad : $det_esp_emp->cantidad;
                            $objDetEspEmpRxC = new DetalleEspecificacionEmpaqueRamosXCajaPerdido();
                            $objDetEspEmpRxC->id_detalle_pedido_perdido = $detallePedidoPerdido->id_detalle_pedido_perdido;
                            $objDetEspEmpRxC->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                            $objDetEspEmpRxC->cantidad = $ramos_x_caja;
                            $objDetEspEmpRxC->save();
                        }
                    }
                }
            }

            if (count($marcaciones)) {

                $pedidos = Pedido::where([
                    ['pedido.tipo_pedido', 'STANDING ORDER'],
                    ['pedido.fecha_pedido', '>', $pedido->fecha_pedido],
                    [DB::raw("DAYOFWEEK(pedido.fecha_pedido)"), Carbon::parse($pedido->fecha_pedido)->dayOfWeek + 1]
                ])->join('detalle_pedido as dp', function ($j) use ($pedido) {
                    $j->on('pedido.id_pedido', 'dp.id_pedido')->whereIn('dp.id_cliente_especificacion', $pedido->detalles->pluck('id_cliente_especificacion')->toArray());
                })->join('detallepedido_datoexportacion as dpde', function ($j) use ($marcaciones) {
                    $j->on('dp.id_detalle_pedido', 'dpde.id_detalle_pedido')
                        ->whereIn('dpde.id_dato_exportacion', array_column($marcaciones, 'id_dato_exportacion'))
                        ->whereIn('dpde.valor', array_column($marcaciones, 'valor'));
                })
                    ->join('cliente_pedido_especificacion as cpe', function ($j) use ($pedido) {
                        $j->on('cpe.id_cliente_pedido_especificacion', 'dp.id_cliente_especificacion')->where('cpe.id_cliente', $pedido->id_cliente);
                    })->select('pedido.id_pedido', 'pedido.fecha_pedido')->distinct()->get();
            } else {

                $pedidos = Pedido::where([
                    ['pedido.tipo_pedido', 'STANDING ORDER'],
                    ['pedido.fecha_pedido', '>', $pedido->fecha_pedido],
                    [DB::raw("DAYOFWEEK(pedido.fecha_pedido)"), Carbon::parse($pedido->fecha_pedido)->dayOfWeek + 1]
                ])->join('detalle_pedido as dp', function ($j) use ($pedido) {
                    $j->on('pedido.id_pedido', 'dp.id_pedido')->whereIn('dp.id_cliente_especificacion', $pedido->detalles->pluck('id_cliente_especificacion')->toArray());
                })->join('cliente_pedido_especificacion as cpe', function ($j) use ($pedido) {
                    $j->on('cpe.id_cliente_pedido_especificacion', 'dp.id_cliente_especificacion')->where('cpe.id_cliente', $pedido->id_cliente);
                })->where(DB::raw("(SELECT COUNT(*) FROM detallepedido_datoexportacion as dpde WHERE dpde.id_detalle_pedido = dp.id_detalle_pedido)"), 0)
                    ->select('pedido.id_pedido', 'pedido.fecha_pedido')->distinct()->get();
            }

            if (hoy() == $pedido->fecha_pedido || hoy() == opDiasFecha('-', 1, $pedido->fecha_pedido)) {

                foreach ($pedido->detalles as $det_ped) {

                    foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {

                        foreach ($esp_emp->detalles as $det_esp_emp) {
                            //dd('ok', $det_esp_emp->id_detalle_especificacionempaque, $pedido->id_cliente);
                            $pedidoModificacion = new PedidoModificacion;
                            $pedidoModificacion->id_cliente = $pedido->id_cliente;
                            $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                            $pedidoModificacion->fecha_nuevo_pedido = $pedido->fecha_pedido;
                            $pedidoModificacion->fecha_anterior_pedido = $pedido->fecha_pedido;
                            $pedidoModificacion->cantidad = $det_ped->cantidad;
                            $pedidoModificacion->operador = '-';
                            $pedidoModificacion->id_usuario = Session::get('id_usuario');
                            $getRamosXCajaModificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);
                            $ramos_x_caja = isset($getRamosXCajaModificado) ? $getRamosXCajaModificado->cantidad : $det_esp_emp->cantidad;
                            $pedidoModificacion->ramos_x_caja = $ramos_x_caja;
                            $pedidoModificacion->save();

                            $distribuciones = DB::table('distribucion_mixtos')
                                ->where('id_detalle_especificacionempaque', $det_esp_emp->id_detalle_especificacionempaque)
                                ->where('id_pedido', $pedido->id_pedido)
                                ->where('id_cliente', $pedido->id_cliente)
                                ->where('id_detalle_pedido', $det_ped->id_detalle_pedido)
                                ->get();
                            foreach ($distribuciones as $dist) {
                                $pedidoModificacion = new PedidoModificacion;
                                $pedidoModificacion->id_cliente = $pedido->id_cliente;
                                $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                                $pedidoModificacion->fecha_nuevo_pedido = $pedido->fecha_pedido;
                                $pedidoModificacion->fecha_anterior_pedido = $pedido->fecha_pedido;
                                $pedidoModificacion->cantidad = null;
                                $pedidoModificacion->operador = '-';
                                $pedidoModificacion->id_usuario = Session::get('id_usuario');
                                $pedidoModificacion->ramos = $dist->ramos * $dist->piezas;
                                $pedidoModificacion->tallos = $dist->tallos;
                                $pedidoModificacion->id_planta = $dist->id_planta;
                                $pedidoModificacion->siglas = $dist->siglas;
                                //$pedidoModificacion->ramos_x_caja = $ramos_x_caja;
                                $pedidoModificacion->save();
                            }
                        }
                    }
                }
            }

            bitacora('pedido', $pedido->id_pedido, 'E', 'ELIMINACION pedido con fecha ' . $pedido->fecha_pedido . ', del cliente: ' . $pedido->cliente->detalle()->nombre . ', tipo: ' . $pedido->tipo_pedido . ', #' . $pedido->orden_fija . ' desde la OPCION_CANCELAR_PEDIDO');

            if ($pedido->tipo_pedido == 'STANDING ORDER') {
                $pedido_cancelado = new PedidoCancelado();
                $pedido_cancelado->id_cliente = $pedido->id_cliente;
                $pedido_cancelado->fecha = $pedido->fecha_pedido;
                $pedido_cancelado->orden_fija = $pedido->orden_fija;
                $pedido_cancelado->id_usuario = session('id_usuario');
                $pedido_cancelado->save();
            }
            if ($request->eliminar_origen == 'SI') {
                $fecha_pedido = Pedido::find($request->id_pedido)->fecha_pedido;
                Pedido::destroy($request->id_pedido);

                Artisan::call('update:pedido_confirmacion', [
                    'fecha' => $fecha_pedido
                ]);
            }

            if (isset($request->elimina_standing) && $request->elimina_standing == 'SI')
                foreach ($pedidos as $p) {
                    $fecha_pedido = $p->fecha_pedido;
                    Pedido::destroy($p->id_pedido);

                    Artisan::call('update:pedido_confirmacion', [
                        'fecha' => $fecha_pedido
                    ]);
                }
            DB::commit();

            foreach ($resumen_variedades as $r) {
                jobCosechaEstimada::dispatch($r['variedad'], $r['longitud'], opDiasFecha('-', 1, $fecha))
                    ->onQueue('cosecha_estimada')
                    ->onConnection('database');
            }

            $success = true;

            $msg = '<div class="alert alert-success text-center">' .
                '<p> Se ha cancelado el pedido exitosamente</p>'
                . '</div>';
        } catch (\Exception $e) {

            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al cancelar el pedido.</p>'
                . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine()
                . '</div>';
        }


        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function opcion_pedido_fijo(Request $request)
    {
        return view(
            'adminlte.gestion.postcocecha.pedidos.forms.paritals.inputs_opciones_pedido_fijo',
            ['opcion' => $request->opcion]
        );
    }

    public function add_fechas_pedido_fijo_personalizado(Request $request)
    {
        return view(
            'adminlte.gestion.postcocecha.pedidos.forms.paritals.inputs_fechas_pedido_fijo_personalizado',
            ['cant_div' => $request->cant_div]
        );
    }

    public function crear_packing_list($id_pedido, $vista_despacho = false)
    {
        $pedido = getPedido($id_pedido);
        /*if ($pedido->packing == '') {
            $last_packing = Pedido::orderBy('packing', 'desc')->first();
            $pedido->packing = isset($last_packing->packing) ? $last_packing->packing + 1 : 1;
            $pedido->save();
        }*/

        $empresa = getConfiguracionEmpresa($pedido->id_configuracion_empresa);
        $despacho = isset(getDetalleDespacho($pedido->id_pedido)->despacho) ? getDetalleDespacho($pedido->id_pedido)->despacho : null;
        $facturaTercero = isset($pedido->envios) ? getFacturaClienteTercero($pedido->envios[0]->id_envio) : null;
        if ($facturaTercero !== null) {
            $cliente = [
                'nombre' => $facturaTercero->nombre_cliente_tercero,
                'identificacion' => $facturaTercero->identificacion,
                'tipo_identificacion' => getTipoIdentificacion($facturaTercero->codigo_identificacion)->nombre,
                'pais' => getPais($facturaTercero->codigo_pais)->nombre,
                'provincia' => $facturaTercero->provincia,
                'direccion' => $facturaTercero->direccion,
                'telefono' => $facturaTercero->telefono,
                'dae' => $facturaTercero->dae,
            ];
        } else {
            foreach ($pedido->cliente->detalles as $det_cli)
                if ($det_cli->estado == 1)
                    $cliente = $det_cli;
            $cliente = [
                'nombre' => $cliente->nombre,
                'identificacion' => $cliente->ruc,
                'tipo_identificacion' => '',    //getTipoIdentificacion($cliente->codigo_identificacion)->nombre,
                'pais' => getPais($cliente->codigo_pais)->nombre,
                'provincia' => $cliente->provincia,
                'direccion' => $cliente->direccion,
                'telefono' => $cliente->telefono,
                'dae' => $pedido->envios[0]->dae
            ];
        }
        return PDF::loadView('adminlte.gestion.postcocecha.despachos.partials.pdf_packing_list', compact(
            'pedido',
            'vista_despacho',
            'empresa',
            'despacho',
            'cliente'
        ))
            //->setPaper('a4', 'portrait')
            ->setPaper([0, 0, 650, 841.89], 'portrait')
            ->stream();
    }

    public function facturar_pedido(Request $request)
    {
        if (getPedido($request->id_pedido)->envios->count() === 0) {
            return '<div class="alert alert-danger text-center">' .
                '<p> El pedido no posee envio creado, edite el pedido y deje el Check "Envio autmatico" activado para realizar el envio</p>'
                . '</div>';
        }
        $pedido = getPedido($request->id_pedido);
        $listado = Envio::where([
            ['id_envio', $pedido->envios[0]->id_envio],
            ['dc.estado', 1],
            ['envio.estado', 0],
            ['p.estado', 1]
        ])->join('pedido as p', 'envio.id_pedido', '=', 'p.id_pedido')
            ->join('detalle_cliente as dc', 'p.id_cliente', '=', 'dc.id_cliente')
            ->join('impuesto as i', 'dc.codigo_impuesto', 'i.codigo')
            ->join('tipo_impuesto as ti', 'dc.codigo_porcentaje_impuesto', 'ti.codigo')
            ->orderBy('envio.id_envio', 'Desc')
            ->select('p.*', 'envio.*', 'i.nombre as nombre_impuesto', 'ti.porcentaje', 'dc.nombre', 'dc.direccion as direccion_cliente', 'dc.almacen as almacen_cliente', 'dc.provincia', 'dc.codigo_pais as pais_cliente', 'dc.telefono as telefono_cliente', 'dc.correo');

        return view('adminlte.gestion.postcocecha.envios.partials.listado', [
            'envios' => $listado->paginate(10),
            'paises' => Pais::all(),
            'aerolineas' => Aerolinea::where('estado', 1)->orderBy('nombre', 'asc')->get(),
            'vista' => $request->path(),
            'empresas' => ConfiguracionEmpresa::all(),
            'consignatarios' => ClienteConsignatario::where('id_cliente', $pedido->id_cliente)
                ->join('consignatario as c', 'cliente_consignatario.id_consignatario', 'c.id_consignatario')->get()
        ]);
    }

    public function ver_factura_pedido($id_pedido)
    {
        $clave_acceso = getPedido($id_pedido)->envios[0]->comprobante->clave_acceso;
        $tipo_documento = getDetallesClaveAcceso($clave_acceso, 'TIPO_COMPROBANTE');

        if ($tipo_documento == "01")
            $dataComprobante = Comprobante::where('clave_acceso', $clave_acceso)->select('numero_comprobante', 'id_envio')->first();

        if ($tipo_documento == "06")
            $dataComprobante = Comprobante::where('clave_acceso', $clave_acceso)
                ->join('detalle_guia_remision as dgr', 'comprobante.id_comprobante', 'dgr.id_comprobante')->select('id_comprobante_relacionado')->first();

        $cliente = new SoapClient(env('URL_WS_ATURIZACION'));
        $response = $cliente->autorizacionComprobante(["claveAccesoComprobante" => $clave_acceso]);
        $autorizacion = $response->RespuestaAutorizacionComprobante->autorizaciones->autorizacion;

        $data = [
            'autorizacion' => $autorizacion,
            'img_clave_acceso' => generateCodeBarGs1128((string)$autorizacion->numeroAutorizacion),
            'obj_xml' => simplexml_load_string($autorizacion->comprobante),
            'numeroComprobante' => $dataComprobante->numero_comprobante,
            'detalles_envio' => $tipo_documento == "01" ? getEnvio($dataComprobante->id_envio)->detalles : "",
            'pedido' => $tipo_documento == "06" ? getComprobante($dataComprobante->id_comprobante_relacionado)->envio->pedido : ""
        ];
        return PDF::loadView('adminlte.gestion.comprobante.partials.pdf.factura', compact('data'))->stream();
    }

    public function store_especificacion_pedido(Request $request)
    {
        $success = false;
        $msg = '<div class="alert alert-danger text-center">' .
            '<p> Ha ocurrido un problema al guardar la informacion al sistema, intente nuevamente</p>'
            . '</div>';
        $save = false;

        if ($request->arrDatosExportacion != null) {
            foreach ($request->arrDatosExportacion as $dato_exportacion) {
                foreach ($dato_exportacion as $de) {
                    $objDetallePedido = DetallePedido::find($de['id_detalle_pedido']);
                    $objDetallePedido->id_agencia_carga = $request->id_agencia_carga;
                    if ($objDetallePedido->save()) {
                        $objDetallePedidoDatoExportacion = DetallePedidoDatoExportacion::where([
                            ['id_detalle_pedido', $de['id_detalle_pedido']],
                            ['id_dato_exportacion', $de['id_dato_exportacion']]
                        ]);
                        if ($objDetallePedidoDatoExportacion->first() == null) {

                            $detallePedidoDatoExportacion = new DetallePedidoDatoExportacion;
                            $dpe = DetallePedidoDatoExportacion::orderBy('id_detallepedido_datoexportacion', 'desc')->first();
                            $detallePedidoDatoExportacion->id_detallepedido_datoexportacion = isset($dpe->id_detallepedido_datoexportacion) ? $dpe->id_detallepedido_datoexportacion + 1 : 1;
                            $detallePedidoDatoExportacion->id_detalle_pedido = $de['id_detalle_pedido'];
                            $detallePedidoDatoExportacion->id_dato_exportacion = $de['id_dato_exportacion'];
                            $detallePedidoDatoExportacion->valor = $de['valor'];
                            $save = !!$detallePedidoDatoExportacion->save();
                        } else {

                            $objDetallePedidoDatoExportacion = DetallePedidoDatoExportacion::find($objDetallePedidoDatoExportacion->first()->id_detallepedido_datoexportacion);
                            $objDetallePedidoDatoExportacion->valor = $de['valor'];
                            $save = !!$objDetallePedidoDatoExportacion->save();
                        }
                        if ($save) {
                            $success = true;
                            $msg = '<div class="alert alert-success text-center">' .
                                '<p> Se ha actualizado la informacion con exito</p>'
                                . '</div>';
                        }
                    }
                }
            }
        } else {
            $objDetallePedido = DetallePedido::where('id_pedido', $request->id_pedido)->get();
            foreach ($objDetallePedido as $dp) {
                $objDp = DetallePedido::find($dp->id_detalle_pedido);
                $objDp->id_agencia_carga = $request->id_agencia_carga;
                $objDp->save();
            }
            $success = true;
            $msg = '<div class="alert alert-success text-center">' .
                '<p> Se ha actualizado la informacion con exito</p>'
                . '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function buscar_codigo_venture(Request $request)
    {
        // dd($request->all());
        $idPlanta = getVariedad($request->id_variedad)->planta->id_planta;
        $presentacion_pedido_caja = $idPlanta . "|" . $request->id_variedad . "|" . $request->id_clasificacion_ramo . "|" . $request->id_u_m_clasificacion_ramo . "|" . $request->tallos_x_ramos . "|" . $request->longitud_ramo . "|" . $request->id_u_m_logitud_ramo;

        $productoVinculados = ProductoYuraVenture::where('id_configuracion_empresa', $request->id_configuracion_empresa)
            ->select('codigo_venture', 'presentacion_yura')->get();
        $arr = [];
        foreach ($productoVinculados as $productoVinculado) {
            $pieza = explode("|", $productoVinculado->presentacion_yura);
            $ids = $pieza[0] . "|" . $pieza[1] . "|" . $pieza[2] . "|" . $pieza[3] . "|" . $pieza[4] . "|" . $pieza[5] . "|" . $pieza[6];
            $arr[] = ["id" => $ids, "codigo_venture" => $productoVinculado->codigo_venture];
        }

        $presentacion_venture = "";
        $codigo_venture = "";
        //$clasificacionRamoEstandar= ClasificacionRamo::where('estandar',1)->first();
        foreach ($arr as $item) {
            if ($item['id'] === $presentacion_pedido_caja) {
                $presentacion_venture = $idPlanta . "|" . $request->id_variedad . "|" . $request->id_clasificacion_ramo . "|" . $request->id_u_m_clasificacion_ramo . "|" . $request->tallos_x_malla . "|" . $request->longitud_ramo . "|" . $request->id_u_m_logitud_ramo;
                $codigo_venture = $item['codigo_venture'];
            }
        }
        //dump($presentacion_venture,$codigo_venture);
        return response()->json([
            'presentacion_venture' => $presentacion_venture,
            'codigo_venture' => $codigo_venture
        ]);
    }

    public function asignaClienteEspecificacion($arr_datos, $id_cliente)
    {
        $estado = false;
        $id_cliente_pedido_especificacion = '';
        $arr_det_esp_emp = [];
        $objEspecificacion = new Especificacion;
        $esp = Especificacion::orderBy('id_especificacion', 'desc')->first();
        $objEspecificacion->id_especificacion = isset($esp->id_especificacion) ? $esp->id_especificacion + 1 : 1;
        $objEspecificacion->estado = 1;
        $objEspecificacion->tipo = 'N';
        $objEspecificacion->creada = 'EJECUCION';

        if ($objEspecificacion->save()) {
            $modelEspecificacion = Especificacion::orderBy('id_especificacion', 'desc')->first();

            $objEspecificacionEmpaque = new EspecificacionEmpaque;
            $espEmpaque = EspecificacionEmpaque::orderBy('id_especificacion_empaque', 'desc')->first();
            $objEspecificacionEmpaque->id_especificacion_empaque = isset($espEmpaque->id_especificacion_empaque) ? $espEmpaque->id_especificacion_empaque + 1 : 1;
            $objEspecificacionEmpaque->id_especificacion = $modelEspecificacion->id_especificacion;
            $objEspecificacionEmpaque->id_empaque = $arr_datos[0]['caja'];
            $objEspecificacionEmpaque->cantidad = 1;

            if ($objEspecificacionEmpaque->save()) {

                foreach ($arr_datos as $ad) {

                    $modelEspecificacionEmpaque = EspecificacionEmpaque::orderBy('id_especificacion_empaque', 'desc')->first();
                    $objDetalleEspecificacionEmpaque = new DetalleEspecificacionEmpaque;
                    $detEspEmpaque = DetalleEspecificacionEmpaque::orderBy('id_detalle_especificacionempaque', 'desc')->first();
                    $objDetalleEspecificacionEmpaque->id_detalle_especificacionempaque = isset($detEspEmpaque->id_detalle_especificacionempaque) ? $detEspEmpaque->id_detalle_especificacionempaque + 1 : 1;
                    $objDetalleEspecificacionEmpaque->id_especificacion_empaque = $modelEspecificacionEmpaque->id_especificacion_empaque;
                    $objDetalleEspecificacionEmpaque->id_variedad = $ad['variedad'];
                    $objDetalleEspecificacionEmpaque->id_clasificacion_ramo = $ad['id_clasificacion_ramo']; //ClasificacionRamo::where('estandar',1)->first()->id_clasificacion_ramo;
                    $objDetalleEspecificacionEmpaque->cantidad = $ad['ramos_x_caja'];
                    $objDetalleEspecificacionEmpaque->id_empaque_p = $ad['presentacion'];
                    $objDetalleEspecificacionEmpaque->tallos_x_ramos = $ad['tallos_x_ramos'];
                    $objDetalleEspecificacionEmpaque->longitud_ramo = $ad['longitud_ramo'];
                    $objDetalleEspecificacionEmpaque->id_unidad_medida = $ad['unidad_medida'];

                    if ($objDetalleEspecificacionEmpaque->save()) {

                        $arr_det_esp_emp[] = $objDetalleEspecificacionEmpaque->id_detalle_especificacionempaque;

                        $objClientePedidoEspecificacion = new ClientePedidoEspecificacion;
                        $cpe = ClientePedidoEspecificacion::orderBy('id_cliente_pedido_especificacion', 'desc')->first();
                        $objClientePedidoEspecificacion->id_cliente_pedido_especificacion = isset($cpe->id_cliente_pedido_especificacion) ? $cpe->id_cliente_pedido_especificacion + 1 : 1;
                        $objClientePedidoEspecificacion->id_cliente = $id_cliente;
                        $objClientePedidoEspecificacion->id_especificacion = $modelEspecificacion->id_especificacion;
                        if ($objClientePedidoEspecificacion->save()) {
                            $modelClientePedidoEspecificacion = ClientePedidoEspecificacion::where('id_cliente_pedido_especificacion', 'desc')->first();
                            $estado = true;
                            $id_cliente_pedido_especificacion = $modelClientePedidoEspecificacion->id_cliente_pedido_especificacion;
                        }
                    } else {
                        Especificacion::destroy($modelEspecificacion->id_especificacion);
                    }
                }
            }
        }
        return [
            'estado' => $estado,
            'id_cliente_pedido_especificacion' => $id_cliente_pedido_especificacion,
            'arr_det_esp_emp' => $arr_det_esp_emp
        ];
    }

    public function store_datos_tallos($datos, $id_detalle_pedido)
    {

        $success = false;
        //foreach ($arr_datos as $dataTallo){
        $objDataTallo = new DataTallos;
        $dt = DataTallos::orderBy('id_data_tallos', 'desc')->first();
        $objDataTallo->id_data_tallos = isset($dt->id_data_tallos) ? $dt->id_data_tallos + 1 : 1;
        $objDataTallo->id_detalle_pedido = $id_detalle_pedido;
        $objDataTallo->mallas = $datos['mallas'];
        $objDataTallo->tallos_x_caja = $datos['tallos_x_caja'];
        $objDataTallo->tallos_x_ramo = $datos['tallos_x_ramo'];
        $objDataTallo->tallos_x_malla = $datos['tallos_x_malla'];
        $objDataTallo->ramos_x_caja = $datos['ramos_x_caja'];
        if ($objDataTallo->save())
            $success = true;
        //}
        return $success;
    }

    public function desglose_pedido(Request $request)
    {
        $pedido = getPedido($request->id_pedido);

        return PDF::loadView('adminlte.gestion.postcocecha.pedidos.partials.desglose_pedido', compact('pedido'))->stream();
        /* return view('adminlte.gestion.postcocecha.pedidos.partials.desglose_pedido', [
            'pedido' => $pedido
        ]); */
    }

    public function pedidos_cliente(Request $request)
    {
        $mes = array_search($request->mes, getMeses(TP_COMPLETO, FR_ARREGLO)) + 1;
        $mes = $mes <= 9 ? str_pad('0', 2, $mes) : $mes;

        $pedidos = Pedido::where('estado', 1)
            ->where('id_cliente', $request->cliente)
            ->whereYear('fecha_pedido', $request->anno)
            ->whereMonth('fecha_pedido', $mes)
            ->orderBy('fecha_pedido')
            ->get();

        return view('adminlte.gestion.postcocecha.pedidos.partials.pedidos_cliente', [
            'pedidos' => $pedidos,
            'mes' => $request->mes,
            'anno' => $request->anno,
            'cliente' => Cliente::find($request->cliente),
        ]);
    }

    public function cambia_tipo_pedido(Request $request)
    {
        $pedido = getPedido($request->id_pedido);

        foreach ($pedido->detalles as $det_ped) {
            $dataMarcacionesColoraciones = [];
            $especificacionEmpaque = $det_ped->cliente_especificacion->especificacion->especificacionesEmpaque;

            foreach ($especificacionEmpaque as $x => $esp_emp) {
                $marcacion = new Marcacion;
                $marcacion->ramos = $esp_emp->ramos_x_caja($det_ped->id_detalle_pedido) * $det_ped->cantidad;
                $marcacion->id_detalle_pedido = $det_ped->id_detalle_pedido;
                $marcacion->id_especificacion_empaque = $esp_emp->id_especificacion_empaque;
                $marcacion->piezas = $det_ped->cantidad;
                if ($marcacion->save()) {
                    $modelMarcacion = Marcacion::all()->last();

                    $coloracion = new Coloracion;
                    $coloracion->id_color = 6;
                    $coloracion->id_especificacion_empaque = $esp_emp->id_especificacion_empaque;
                    $coloracion->id_detalle_pedido = $det_ped->id_detalle_pedido;

                    if ($coloracion->save()) {
                        $modelColoracion = Coloracion::all()->last();
                        foreach ($esp_emp->detalles as $det_esp_emp) {
                            $ramos_modificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);
                            $dataMarcacionesColoraciones[$modelMarcacion->id_marcacion][] = [
                                'id_marcacion' => $modelMarcacion->id_marcacion,
                                'id_coloracion' => $modelColoracion->id_coloracion,
                                'id_det_esp' => $det_esp_emp->id_detalle_especificacionempaque,
                                'cantidad' => (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad) * $det_ped->cantidad,
                                'r_x_c_modificados' => (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad)
                            ];
                        }
                    }
                }
            }

            if (($x + 1) == $especificacionEmpaque->count()) {
                $w = 0;
                $z = 0;
                foreach ($dataMarcacionesColoraciones as $marcacionColoracion) {
                    foreach ($marcacionColoracion as $marCol) {
                        $z++;
                        $marcacionColoracion = new MarcacionColoracion;
                        $marcacionColoracion->id_marcacion = $marCol['id_marcacion'];
                        $marcacionColoracion->id_coloracion = $marCol['id_coloracion'];
                        $marcacionColoracion->id_detalle_especificacionempaque = $marCol['id_det_esp'];
                        $marcacionColoracion->cantidad = $marCol['cantidad'];
                        if ($marcacionColoracion->save()) {
                            $ramos_modificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $marCol['id_det_esp']);
                            $detEspEmpRamocaja = new DetalleEspecificacionEmpaqueRamosCaja;
                            $detEspEmpRxC = DetalleEspecificacionEmpaqueRamosCaja::orderBy('id_detalle_especificacionempaque_ramos_x_caja', 'desc')->first();
                            $detEspEmpRamocaja->id_detalle_especificacionempaque_ramos_x_caja = isset($detEspEmpRxC->id_detalle_especificacionempaque_ramos_x_caja) ? $detEspEmpRxC->id_detalle_especificacionempaque_ramos_x_caja + 1 : 1;
                            $detEspEmpRamocaja->id_detalle_pedido = $det_ped->id_detalle_pedido;
                            $detEspEmpRamocaja->id_detalle_especificacionempaque = $marCol['id_det_esp'];
                            $detEspEmpRamocaja->cantidad = (isset($ramos_modificado) ? $ramos_modificado->cantidad : $marCol['r_x_c_modificados']);
                            if ($detEspEmpRamocaja->save()) {
                                $w++;
                            } else {
                                break 2;
                            };
                        }
                    }
                }

                if ($w == $z) {
                    $pedido->update([
                        "tipo_especificacion" => "T",
                        "descripcion" => "Flor tinturada"
                    ]);
                    $success = true;
                    $msg = '<div class="alert alert-success text-center">' .
                        '<p> Se ha cambiado el pedido normal a pedido tinturado con exito</p>'
                        . '</div>';
                } else {
                    $marcacion = Marcacion::where('id_detalle_pedido', $det_ped->id_detalle_pedido);
                    $marcacion->delete();
                    $coloracion = Coloracion::where('id_detalle_pedido', $det_ped->id_detalle_pedido);
                    $coloracion->delete();
                    $success = false;
                    $msg = '<div class="alert alert-danger text-center">' .
                        '<p> Hubo un error al cambiar el tipo del pedido, intente nuevamente, si el error persiste contacte al area de sistemas</p>'
                        . '</div>';
                }
            } else {
                $marcacion = Marcacion::where('id_detalle_pedido', $det_ped->id_detalle_pedido);
                $marcacion->delete();
                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<p> Hubo un error al cambiar el tipo del pedido, intente nuevamente, si el error persiste contacte al area de sistemas</p>'
                    . '</div>';
            }
        }

        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function modificar_comprobante(Request $request)
    {
        $pedido = Pedido::find($request->id_pedido);
        $envio = $pedido->envios[0];
        $comprobante = $envio->comprobante;
        return view('adminlte.gestion.postcocecha.pedidos_ventas.forms.modificar_comprobante', [
            'pedido' => $pedido,
            'envio' => $envio,
            'comprobante' => $comprobante,
        ]);
    }

    public function update_comprobante(Request $request)
    {
        $comprobante = Comprobante::find($request->id_comprobante);
        $comprobante->secuencial = $request->secuencial;
        if ($comprobante->save())
            return [
                'success' => true,
                'mensaje' => '<div class="alert alert-success text-center">Se ha modificado satisfactoriamente el comprobante</div>'
            ];
        else
            return [
                'success' => false,
                'mensaje' => '<div class="alert alert-danger text-center">' .
                    '<i class="fa fa-fw fa-exclamation-triangle"></i> Ha ocurrido un problema interno al modificar el comprobante</div>'
            ];
    }

    public function form_eliminar_detalle_pedido_masivo(Request $request)
    {
        $detalle_pedido = DetallePedido::where('id_pedido', $request->id_pedido)->get();

        $idCliente = count($detalle_pedido) ?  $detalle_pedido[0]->pedido->id_cliente : '';

        $clientes = DB::table('cliente as c')
            ->join('detalle_cliente as dt', 'c.id_cliente', '=', 'dt.id_cliente')
            ->where('dt.estado', 1)->orderBy('dt.nombre', 'asc')->get();

        $datos_exportacion = ClienteDatoExportacion::where('id_cliente', $idCliente)->get();

        $marcaciones = DatosExportacion::where('estado', 1)->get();

        $plantas = Planta::where('estado', true)->get();

        return view('adminlte.gestion.postcocecha.pedidos.forms.paritals.eliminar_detalle_pedido_masivo', [
            'idCliente' => $idCliente,
            'marcaciones' => $marcaciones,
            'clientes' => $clientes,
            'id_pedido' => $request->id_pedido,
            'detalles' => $detalle_pedido,
            'datos_exportacion' => $datos_exportacion,
            'plantas' => $plantas
        ]);
    }

    public function eliminar_detalle_pedido_masivo(Request $request)
    {
        try {

            DB::beginTransaction();

            $detalle_pedido = DetallePedido::find($request->id_detalle_pedido);

            $id_detalles_pedidos = Pedido::where('pedido.id_cliente', $detalle_pedido->pedido->id_cliente)
                ->join('detalle_pedido as dp', 'pedido.id_pedido', 'dp.id_pedido')
                ->where([
                    ['dp.id_cliente_especificacion', $detalle_pedido->id_cliente_especificacion],
                    ['pedido.fecha_pedido', '>=', $detalle_pedido->pedido->fecha_pedido],
                    ['pedido.tipo_pedido', 'STANDING ORDER'],
                    ['pedido.estado', 1],
                    [DB::raw("DAYOFWEEK(pedido.fecha_pedido)"), Carbon::parse($detalle_pedido->pedido->fecha_pedido)->dayOfWeek + 1]
                ])->pluck('id_detalle_pedido')->toArray();

            foreach ($id_detalles_pedidos as $id) {

                $det_ped = DetallePedido::find($id);

                if (now()->toDateString() >= Carbon::parse($det_ped->pedido->fecha_pedido)->subDay()->toDateString()) {

                    foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {

                        foreach ($esp_emp->detalles as $det_esp_emp) {

                            $pedidoModificacion = new PedidoModificacion;
                            $pedidoModificacion->fecha_nuevo_pedido = $det_ped->pedido->fecha_pedido;
                            $pedidoModificacion->id_cliente = $det_ped->pedido->id_cliente;
                            $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                            $pedidoModificacion->fecha_anterior_pedido = $det_ped->pedido->fecha_pedido;
                            $pedidoModificacion->cantidad = $det_ped->cantidad;
                            $pedidoModificacion->operador = '-';
                            $pedidoModificacion->id_usuario = Session::get('id_usuario');
                            $getRamosXCajaModificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);
                            $ramos_x_caja = isset($getRamosXCajaModificado) ? $getRamosXCajaModificado->cantidad : $det_esp_emp->cantidad;
                            $pedidoModificacion->ramos_x_caja = $ramos_x_caja;
                            $pedidoModificacion->save();
                        }
                    }
                }

                DetallePedido::destroy($id);
            }

            DB::commit();

            return [
                'success' => true,
                'mensaje' => '<div class="alert alert-success text-center">Se ha eliminado el detalle del pedido en Seleccione los Standings creados</div>'
            ];
        } catch (\Exception $e) {

            DB::rollBack();
            return [
                'success' => false,
                'mensaje' => '<div class="alert alert-danger text-center">' .
                    '<i class="fa fa-fw fa-exclamation-triangle"></i> Ha ocurrido un problema interno al modificar el pedido <br />' . $e->getMessage() . ' en la linea ' . $e->getLine() . ' del archivo ' . $e->getFile() .
                    ' </div>'
            ];
        }
    }

    public function store_especificacion_simple(Request $request)
    {
        # code...
    }

    public function editar_combo(Request $request)
    {
        $detalle = DetallePedido::find($request->detalle);
        $plantas = Planta::where('estado', 1)
            ->orderBy('orden')
            ->get();
        $clasificaciones_ramo = ClasificacionRamo::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $presentaciones = Empaque::where('estado', 1)
            ->where('tipo', 'P')
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postcocecha.pedidos.forms.editar_combo', [
            'detalle' => $detalle,
            'plantas' => $plantas,
            'clasificaciones_ramo' => $clasificaciones_ramo,
            'presentaciones' => $presentaciones,
        ]);
    }

    public function store_det_esp(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'id_variedad' => 'required',
            'empaque' => 'required',
            'ramos_x_caja' => 'required',
            'tallos_x_ramos' => 'required',
            'longitud' => 'required',
            'precio' => 'required',
        ], [
            'id_variedad.required' => 'El color es obligatorio',
            'empaque.required' => 'El empaque es obligatorio',
            'ramos_x_caja.required' => 'Los ramos x caja son obligatorios',
            'tallos_x_ramos.required' => 'Los tallos x ramos son obligatorios',
            'longitud.required' => 'La longitud es obligatoria',
            'precio.required' => 'El precio es obligatorio',
        ]);
        $id_det_esp = '';
        if (!$valida->fails()) {
            DB::beginTransaction();
            try {
                $model = new DetalleEspecificacionEmpaque();
                $det_esp = DetalleEspecificacionEmpaque::orderBy('id_detalle_especificacionempaque', 'desc')->first();
                $model->id_detalle_especificacionempaque = isset($det_esp->id_detalle_especificacionempaque) ? $det_esp->id_detalle_especificacionempaque + 1 : 1;
                $model->id_variedad = $request->id_variedad;
                $model->id_especificacion_empaque = $request->id_esp_emp;
                $model->id_clasificacion_ramo = $request->clasificacion;
                $model->id_empaque_p = $request->empaque;
                $model->tallos_x_ramos = $request->tallos_x_ramos;
                $model->cantidad = $request->ramos_x_caja;
                $model->longitud_ramo = $request->longitud;
                $model->id_unidad_medida = 1;

                if ($model->save()) {
                    $model = DetalleEspecificacionEmpaque::All()->last();
                    $id_det_esp = $model->id_detalle_especificacionempaque;
                    $success = true;
                    $msg = 'Se ha guardado un nuevo detalle satisfactoriamente';
                    bitacora('detalle_especificacionempaque', $model->id_detalle_especificacionempaque, 'I', 'Inserción satisfactoria de un nuevo det_esp');

                    $detalle = DetallePedido::find($request->id_det_ped);
                    $detalle->precio .= '|' . $request->precio . ';' . $model->id_detalle_especificacionempaque;
                    $detalle->save();
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                        . '</div>';
                }
                DB::commit();
            } catch (\Exception $e) {

                DB::rollBack();
                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                    '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
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
            'id_det_esp' => $id_det_esp,
        ];
    }

    public function update_det_esp(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'id_variedad' => 'required',
            'empaque' => 'required',
            'ramos_x_caja' => 'required',
            'tallos_x_ramos' => 'required',
            'longitud' => 'required',
            'precio' => 'required',
        ], [
            'id_variedad.required' => 'El color es obligatorio',
            'empaque.required' => 'El empaque es obligatorio',
            'ramos_x_caja.required' => 'Los ramos x caja son obligatorios',
            'tallos_x_ramos.required' => 'Los tallos x ramos son obligatorios',
            'longitud.required' => 'La longitud es obligatoria',
            'precio.required' => 'El precio es obligatorio',
        ]);
        if (!$valida->fails()) {
            DB::beginTransaction();
            try {
                $model = DetalleEspecificacionEmpaque::find($request->det_esp);
                $model->id_variedad = $request->id_variedad;
                $model->id_especificacion_empaque = $request->id_esp_emp;
                $model->id_clasificacion_ramo = $request->clasificacion;
                $model->id_empaque_p = $request->empaque;
                $model->tallos_x_ramos = $request->tallos_x_ramos;
                $model->cantidad = $request->ramos_x_caja;
                $model->longitud_ramo = $request->longitud;
                $model->id_unidad_medida = 1;

                if ($model->save()) {
                    $success = true;
                    $msg = 'Se ha guardado un nuevo detalle satisfactoriamente';
                    bitacora('detalle_especificacionempaque', $model->id_detalle_especificacionempaque, 'U', 'Modificacion satisfactoria de un nuevo det_esp');

                    $detalle = DetallePedido::find($request->id_det_ped);
                    $precio = '';
                    foreach ($detalle->cliente_especificacion->especificacion->especificacionesEmpaque[0]->detalles as $pos => $det_esp) {
                        $p = $model->id_detalle_especificacionempaque == $det_esp->id_detalle_especificacionempaque ? $request->precio : getPrecioByDetEsp($detalle->precio, $det_esp->id_detalle_especificacionempaque);
                        if ($pos == 0) {
                            $precio = $p . ';' . $det_esp->id_detalle_especificacionempaque;
                        } else {
                            $precio .= '|' . $p . ';' . $det_esp->id_detalle_especificacionempaque;
                        }
                    }
                    $detalle->precio = $precio;
                    $detalle->save();
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                        . '</div>';
                }
                DB::commit();
            } catch (\Exception $e) {

                DB::rollBack();
                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                    '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
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

    public function delete_det_esp(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = DetalleEspecificacionEmpaque::find($request->det_esp);
            $model->delete();
            $success = true;
            $msg = 'Se ha guardado un nuevo detalle satisfactoriamente';

            $detalle = DetallePedido::find($request->id_det_ped);
            $precio = '';
            foreach ($detalle->cliente_especificacion->especificacion->especificacionesEmpaque[0]->detalles as $pos => $det_esp) {
                $p = getPrecioByDetEsp($detalle->precio, $det_esp->id_detalle_especificacionempaque);
                if ($pos == 0) {
                    $precio = $p . ';' . $det_esp->id_detalle_especificacionempaque;
                } else {
                    $precio .= '|' . $p . ';' . $det_esp->id_detalle_especificacionempaque;
                }
            }
            $detalle->precio = $precio;
            $detalle->save();

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function update_orden_fija(Request $request)
    {
        DB::beginTransaction();
        try {
            $pedido = Pedido::find($request->id_ped);

            if ($request->has('fechas'))
                $fechas_futuras = json_decode($request->fechas);
            else
                $fechas_futuras = DB::table('pedido')
                    ->select('fecha_pedido')->distinct()
                    ->where('fecha_pedido', '>', $pedido->fecha_pedido)
                    ->where('orden_fija', $pedido->orden_fija)
                    ->orderBy('fecha_pedido')
                    ->get()->pluck('fecha_pedido')->toArray();

            $queue = getQueueForUpdateOrdenFija($request->id_ped);
            $pos_progreso = 1;
            foreach ($fechas_futuras as $pos_f => $f) {
                foreach ($pedido->detalles as $pos_d => $det) {
                    jobUpdateOrdenFija::dispatch(
                        $request->id_ped,
                        $det->id_detalle_pedido,
                        $f,
                        $pos_d == 0 ? true : false,
                        $pos_progreso,
                        count($fechas_futuras) * count($pedido->detalles),
                        session('id_usuario')
                    )->onQueue($queue);
                    $pos_progreso++;
                }
            }

            DB::commit();
            $success = true;
            $msg = 'Se esta procesando la información en un segundo plano';
        } catch (\Exception $e) {

            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function delete_orden_fija(Request $request)
    {
        DB::beginTransaction();
        try {
            Artisan::call('delete:orden_fija', [
                'pedido' => $request->id_ped
            ]);

            DB::commit();
            $success = true;
            $msg = 'Se esta procesando la información en un segundo plano';
        } catch (\Exception $e) {

            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function mover_fecha_orden_fija(Request $request)
    {
        $pedido = Pedido::find($request->pedido);
        $ordenes = Pedido::where('fecha_pedido', '>=', $pedido->fecha_pedido)
            ->where('orden_fija', $pedido->orden_fija)
            ->where('tipo_pedido', 'STANDING ORDER')
            ->where('id_cliente', $pedido->id_cliente)
            ->orderBy('fecha_pedido')
            ->get();
        return view('adminlte.gestion.postcocecha.despachos.form.mover_fecha_orden_fija', [
            'pedido' => $pedido,
            'ordenes' => $ordenes,
        ]);
    }

    public function store_mover_fechas(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach (json_decode($request->data) as $d) {
                $pedido = Pedido::find($d->id_ped);
                $pos_dia = transformDiaPhp(date('w', strtotime($pedido->fecha_pedido)));
                $dias = $d->dia - $pos_dia;
                $oldFecha = $pedido->fecha_pedido;
                $pedido->fecha_pedido = opDiasFecha($dias > 0 ? '+' : '-', $dias > 0 ? $dias : ($dias * (-1)), $pedido->fecha_pedido);
                $newFecha = $pedido->fecha_pedido;
                $pedido->save();

                if ($oldFecha != $newFecha) {  // cambio de fecha
                    if (hoy() == $newFecha || hoy() == opDiasFecha('-', 1, $newFecha)) { // fecha nueva
                        foreach ($pedido->detalles as $det_ped)
                            foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                                foreach ($esp_emp->detalles as $det_esp_emp) {
                                    $pedidoModificacion = new PedidoModificacion;
                                    $pedidoModificacion->id_cliente = $pedido->id_cliente;
                                    $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                                    $pedidoModificacion->fecha_nuevo_pedido = $oldFecha;
                                    $pedidoModificacion->fecha_anterior_pedido = $newFecha;
                                    $pedidoModificacion->cambio_fecha = 1;
                                    $pedidoModificacion->cantidad = $det_ped->cantidad; // piezas
                                    $pedidoModificacion->operador = '+';
                                    $pedidoModificacion->id_usuario = Session::get('id_usuario');
                                    $ramos_x_caja = $det_esp_emp->cantidad;
                                    $pedidoModificacion->ramos_x_caja = $ramos_x_caja;
                                    $pedidoModificacion->save();
                                }
                            }
                    }

                    if (hoy() == $oldFecha || hoy() == opDiasFecha('-', 1, $oldFecha)) { // fecha vieja
                        foreach ($pedido->detalles as $det_ped)
                            foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                                foreach ($esp_emp->detalles as $det_esp_emp) {
                                    $pedidoModificacion = new PedidoModificacion;
                                    $pedidoModificacion->id_cliente = $pedido->id_cliente;
                                    $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                                    $pedidoModificacion->fecha_nuevo_pedido = $newFecha;
                                    $pedidoModificacion->fecha_anterior_pedido = $oldFecha;
                                    $pedidoModificacion->cambio_fecha = 1;
                                    $pedidoModificacion->cantidad = $det_ped->cantidad; // piezas
                                    $pedidoModificacion->operador = '-';
                                    $pedidoModificacion->id_usuario = Session::get('id_usuario');
                                    $ramos_x_caja = $det_esp_emp->cantidad;
                                    $pedidoModificacion->ramos_x_caja = $ramos_x_caja;
                                    $pedidoModificacion->save();
                                }
                            }
                    }

                    foreach ($pedido->detalles as $det_ped)
                        foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp)
                            foreach ($esp_emp->detalles as $det_esp) {
                                jobCosechaEstimada::dispatch($det_esp->id_variedad, $det_esp->longitud_ramo, opDiasFecha('-', 1, $oldFecha))
                                    ->onQueue('cosecha_estimada')
                                    ->onConnection('database');
                                jobCosechaEstimada::dispatch($det_esp->id_variedad, $det_esp->longitud_ramo, opDiasFecha('-', 1, $newFecha))
                                    ->onQueue('cosecha_estimada')
                                    ->onConnection('database');
                            }
                }

                bitacora('pedido', $pedido->id_pedido, 'U', 'CAMBIO DE FECHA DE LA ORDEN FIJA #' . $pedido->orden_fija);
            }

            $success = true;
            $msg = 'Se ha guardado la informacion satisfactoriamente';

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function copiar_pedido(Request $request)
    {
        $pedido = Pedido::find($request->pedido);
        return view('adminlte.gestion.postcocecha.despachos.form.copiar_pedido', [
            'pedido' => $pedido,
        ]);
    }

    public function store_copiar_pedido(Request $request)
    {
        try {
            $pedOriginal = $request->id_ped;
            foreach (json_decode($request->data) as $d) {
                DB::beginTransaction();
                jobCopiarPedido::dispatch(
                    $pedOriginal,
                    $d,
                    count(json_decode($request->data)),
                    session('id_usuario'),
                    \Request::ip()
                )->onQueue('copiar_pedido');
                DB::commit();
            }

            $success = true;
            $msg = 'Se esta <b>COPIANDO</b> el pedido en un segundo plano';
        } catch (\Exception $e) {

            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function agregar_pedido(Request $request)
    {
        $clientes = DB::table('cliente as c')
            ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'c.id_cliente')
            ->select('dc.id_cliente', 'dc.nombre')->distinct()
            ->where('c.estado', 1)
            ->where('dc.estado', 1)
            ->orderBy('dc.nombre')
            ->get();
        $marcaciones = DB::table('dato_exportacion')
            ->where('estado', 1)
            ->get();
        return view('adminlte.gestion.postcocecha.pedidos.forms.agregar_pedido', [
            'clientes' => $clientes,
            'marcaciones' => $marcaciones
        ]);
    }

    public function form_seleccionar_cliente(Request $request)
    {
        $consignatarios = DB::table('cliente_consignatario as cc')
            ->join('consignatario as c', 'c.id_consignatario', '=', 'cc.id_consignatario')
            ->select('cc.id_consignatario', 'c.nombre')->distinct()
            ->where('cc.id_cliente', $request->cliente)
            ->orderBy('c.nombre')
            ->get();
        $option_consignatarios = '<option value="">Sin Consignatario</option>';
        foreach ($consignatarios as $pos => $c) {
            $selected = $pos == 0 ? 'selected' : '';
            $option_consignatarios .= '<option value="' . $c->id_consignatario . '" ' . $selected . '>' . $c->nombre . '</option>';
        }

        $plantas = DB::table('cliente_pedido_especificacion as cpe')
            ->join('especificacion as e', 'e.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('v.id_planta', 'p.nombre')->distinct()
            ->where('cpe.id_cliente', $request->cliente)
            ->where('e.creada', 'PRE-ESTABLECIDA')
            ->where('e.estado', 1)
            ->where('p.estado', 1)
            ->where('v.estado', 1)
            ->orderBy('p.orden')
            ->get();
        $option_plantas = '<option value="">Seleccione</option>';
        foreach ($plantas as $item) {
            $option_plantas .= '<option value="' . $item->id_planta . '">' . $item->nombre . '</option>';
        }

        $cajas = DB::table('cliente_pedido_especificacion as cpe')
            ->join('especificacion as e', 'e.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('empaque as emp', 'emp.id_empaque', '=', 'ee.id_empaque')
            ->select('ee.id_empaque', 'emp.nombre')->distinct()
            ->where('cpe.id_cliente', $request->cliente)
            ->where('e.creada', 'PRE-ESTABLECIDA')
            ->where('e.estado', 1)
            ->orderBy('emp.nombre')
            ->get();
        $option_cajas = '<option value="">Seleccione</option>';
        foreach ($cajas as $item) {
            $option_cajas .= '<option value="' . $item->id_empaque . '">' . explode('|', $item->nombre)[0] . '</option>';
        }

        $agencias = DB::table('cliente_agenciacarga as ca')
            ->join('agencia_carga as a', 'a.id_agencia_carga', '=', 'ca.id_agencia_carga')
            ->select('ca.id_agencia_carga', 'a.nombre')->distinct()
            ->orderBy('a.nombre')
            ->get();

        $agencias_cliente = DB::table('cliente_agenciacarga as ca')
            ->join('agencia_carga as a', 'a.id_agencia_carga', '=', 'ca.id_agencia_carga')
            ->select('ca.id_agencia_carga', 'a.nombre')->distinct()
            ->where('ca.id_cliente', $request->cliente)
            ->orderBy('a.nombre')
            ->get();
        $option_agencias = '';
        foreach ($agencias_cliente as $item) {
            $option_agencias .= '<option value="' . $item->id_agencia_carga . '">' . $item->nombre . '</option>';
        }
        foreach ($agencias as $item) {
            $option_agencias .= '<option value="' . $item->id_agencia_carga . '">' . $item->nombre . '</option>';
        }
        return [
            'consignatarios' => $option_consignatarios,
            'plantas' => $option_plantas,
            'cajas' => $option_cajas,
            'agencias' => $option_agencias,
        ];
    }

    public function form_seleccionar_planta(Request $request)
    {
        $variedades = DB::table('cliente_pedido_especificacion as cpe')
            ->join('especificacion as e', 'e.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->select('dee.id_variedad', 'v.nombre')->distinct()
            ->where('cpe.id_cliente', $request->cliente)
            ->where('e.creada', 'PRE-ESTABLECIDA')
            ->where('e.estado', 1)
            ->where('v.estado', 1)
            ->where('v.id_planta', $request->planta)
            ->orderBy('v.orden')
            ->get();
        $option_variedades = '<option value="">Seleccione</option>';
        foreach ($variedades as $item) {
            $option_variedades .= '<option value="' . $item->id_variedad . '">' . $item->nombre . '</option>';
        }

        $presentaciones = DB::table('cliente_pedido_especificacion as cpe')
            ->join('especificacion as e', 'e.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('empaque as emp', 'emp.id_empaque', '=', 'dee.id_empaque_p')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->select('dee.id_empaque_p', 'emp.nombre')->distinct()
            ->where('cpe.id_cliente', $request->cliente)
            ->where('e.creada', 'PRE-ESTABLECIDA')
            ->where('e.estado', 1)
            ->where('v.estado', 1)
            ->where('v.id_planta', $request->planta)
            ->orderBy('emp.nombre')
            ->get();
        $option_presentaciones = '';
        foreach ($presentaciones as $item) {
            $option_presentaciones .= '<option value="' . $item->id_empaque_p . '">' . $item->nombre . '</option>';
        }

        $dee = DB::table('cliente_pedido_especificacion as cpe')
            ->join('especificacion as e', 'e.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->select('dee.longitud_ramo', 'dee.cantidad as ramos_x_caja', 'dee.tallos_x_ramos')->distinct()
            ->where('cpe.id_cliente', $request->cliente)
            ->where('e.creada', 'PRE-ESTABLECIDA')
            ->where('e.estado', 1)
            ->where('v.estado', 1)
            ->where('v.id_planta', $request->planta)
            ->get()->first();
        $longitud = $dee->longitud_ramo;
        if ($request->planta == 2)
            $longitud = 70;
        $ramos_x_caja = $dee->ramos_x_caja;
        $tallos_x_ramos = $dee->tallos_x_ramos;
        return [
            'variedades' => $option_variedades,
            'presentaciones' => $option_presentaciones,
            'longitud' => $longitud,
            'ramos_x_caja' => $ramos_x_caja,
            'tallos_x_ramos' => $tallos_x_ramos,
        ];
    }

    public function buscar_form_especificaciones(Request $request)
    {
        $listado = Especificacion::join('cliente_pedido_especificacion as cpe', 'cpe.id_especificacion', '=', 'especificacion.id_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->select('especificacion.*')->distinct()
            ->where('cpe.id_cliente', $request->cliente)
            ->where('especificacion.creada', 'PRE-ESTABLECIDA')
            ->where('especificacion.estado', 1)
            ->where('v.estado', 1)
            ->where('v.id_planta', $request->planta);
        if ($request->variedad != null)
            $listado->where('dee.id_variedad', $request->variedad);
        if ($request->caja != null)
            $listado->where('ee.id_empaque', $request->caja);
        if ($request->ramos_x_caja != null)
            $listado->where('dee.cantidad', $request->ramos_x_caja);
        if ($request->longitud != null)
            $listado->where('dee.longitud_ramo', $request->longitud);
        $listado->orderBy('v.orden')
            ->orderBy('dee.cantidad', 'desc')
            ->orderBy('dee.id_detalle_especificacionempaque');

        $listado = $listado->get();

        $marcaciones = DB::table('dato_exportacion')
            ->where('estado', 1)
            ->get();
        return view('adminlte.gestion.postcocecha.pedidos.forms.buscar_form_especificaciones', [
            'listado' => $listado,
            'marcaciones' => $marcaciones
        ]);
    }

    public function agregar_combos_pedido(Request $request)
    {
        $detalles_combo = [];
        foreach (json_decode($request->data) as $d) {
            $detalles_combo[] = [
                'planta' => Planta::find($d->planta),
                'variedad' => Variedad::find($d->variedad),
                'presentacion' => Empaque::find($d->presentacion),
                'longitud' => $d->longitud,
                'ramos_x_caja' => $d->ramos_x_caja,
                'tallos_x_ramos' => $d->tallos_x_ramos,
                'precio' => $d->precio,
            ];
        }
        return view('adminlte.gestion.postcocecha.pedidos.forms.agregar_combos_pedido', [
            'piezas' => $request->piezas,
            'caja' => Empaque::find($request->caja),
            'celdas_marcaciones' => $request->celdas_marcaciones,
            'detalles_combo' => $detalles_combo,
            'form_cant_detalles' => $request->form_cant_detalles,
        ]);
    }

    public function cargar_opciones_orden_fija(Request $request)
    {
        return view(
            'adminlte.gestion.postcocecha.pedidos.forms.inputs_opciones_pedido_fijo',
            ['opcion' => $request->opcion]
        );
    }

    public function grabar_pedido(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $valida = Validator::make($request->all(), [
            'cliente' => 'required',
            //'consignatario' => 'required',
            'agencia' => 'required',
            'fecha' => 'required',
        ], [
            'cliente.required' => 'El cliente es obligatorio',
            //'consignatario.required' => 'El consignatario es obligatorio',
            'agencia.required' => 'La agencia es obligatoria',
            'fecha.required' => 'La fecha es obligatoria',
        ]);
        if (!$valida->fails()) {
            jobGrabarPedido::dispatch(
                $request->tipo,
                $request->fecha,
                $request->cliente,
                $request->consignatario,
                $request->agencia,
                $request->detalles_pedido,
                session('id_usuario'),
                \Request::ip()
            )->onQueue('grabar_pedido');

            $msg = 'Se esta <b>CREANDO</b> el pedido en un segundo plano';
            $success = true;
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

    public function modificar_pedido(Request $request)
    {
        $pedido = Pedido::find($request->pedido);
        $envio = Envio::All()->where('id_pedido', $request->pedido)->first();
        $consignatario = $envio->consignatario;
        $detalles_pedido = $pedido->detalles;
        $agencia = $detalles_pedido[0]->agencia_carga;
        $clientes = DB::table('cliente as c')
            ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'c.id_cliente')
            ->select('dc.id_cliente', 'dc.nombre')->distinct()
            ->where('c.estado', 1)
            ->where('dc.estado', 1)
            ->orderBy('dc.nombre')
            ->get();
        $marcaciones = DB::table('dato_exportacion')
            ->where('estado', 1)
            ->get();
        $consignatarios = DB::table('cliente_consignatario as cc')
            ->join('consignatario as c', 'c.id_consignatario', '=', 'cc.id_consignatario')
            ->select('cc.id_consignatario', 'c.nombre')->distinct()
            ->where('cc.id_cliente', $pedido->id_cliente)
            ->orderBy('c.nombre')
            ->get();
        $agencias = DB::table('cliente_agenciacarga as ca')
            ->join('agencia_carga as a', 'a.id_agencia_carga', '=', 'ca.id_agencia_carga')
            ->select('ca.id_agencia_carga', 'a.nombre')->distinct()
            ->where('a.estado', 1)
            //->where('ca.id_cliente', $pedido->id_cliente)
            ->orderBy('a.nombre')
            ->get();
        $plantas = DB::table('cliente_pedido_especificacion as cpe')
            ->join('especificacion as e', 'e.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('v.id_planta', 'p.nombre')->distinct()
            ->where('cpe.id_cliente', $pedido->id_cliente)
            ->where('e.creada', 'PRE-ESTABLECIDA')
            ->where('e.estado', 1)
            ->where('p.estado', 1)
            ->where('v.estado', 1)
            ->orderBy('p.orden')
            ->get();
        $option_plantas = '<option value="">Seleccione</option>';
        foreach ($plantas as $item) {
            $option_plantas .= '<option value="' . $item->id_planta . '">' . $item->nombre . '</option>';
        }

        $cajas = DB::table('cliente_pedido_especificacion as cpe')
            ->join('especificacion as e', 'e.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('empaque as emp', 'emp.id_empaque', '=', 'ee.id_empaque')
            ->select('ee.id_empaque', 'emp.nombre')->distinct()
            ->where('cpe.id_cliente', $pedido->id_cliente)
            ->where('e.creada', 'PRE-ESTABLECIDA')
            ->where('e.estado', 1)
            ->orderBy('emp.nombre')
            ->get();
        $option_cajas = '<option value="">Seleccione</option>';
        foreach ($cajas as $item) {
            $option_cajas .= '<option value="' . $item->id_empaque . '">' . explode('|', $item->nombre)[0] . '</option>';
        }

        $presentaciones = DB::table('cliente_pedido_especificacion as cpe')
            ->join('especificacion as e', 'e.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('empaque as emp', 'emp.id_empaque', '=', 'dee.id_empaque_p')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('dee.id_empaque_p', 'emp.nombre')->distinct()
            ->where('cpe.id_cliente', $pedido->id_cliente)
            ->where('e.creada', 'PRE-ESTABLECIDA')
            ->where('e.estado', 1)
            ->where('v.estado', 1)
            ->where('p.estado', 1)
            ->orderBy('emp.nombre')
            ->get();
        $option_presentaciones = '<option value="">Seleccione</option>';
        foreach ($presentaciones as $item) {
            $option_presentaciones .= '<option value="' . $item->id_empaque_p . '">' . $item->nombre . '</option>';
        }
        return view('adminlte.gestion.postcocecha.pedidos.forms.modificar_pedido', [
            'pedido' => $pedido,
            'detalles_pedido' => $detalles_pedido,
            'envio' => $envio,
            'consignatario' => $consignatario,
            'agencia' => $agencia,
            'clientes' => $clientes,
            'marcaciones' => $marcaciones,
            'consignatarios' => $consignatarios,
            'agencias' => $agencias,
            'plantas' => $plantas,
            'cajas' => $cajas,
            'presentaciones' => $presentaciones,
            'option_plantas' => $option_plantas,
            'option_cajas' => $option_cajas,
            'option_presentaciones' => $option_presentaciones,
        ]);
    }

    public function update_pedido(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        try {
            $valida = Validator::make($request->all(), [
                'cliente' => 'required',
                //'consignatario' => 'required',
                'agencia' => 'required',
                'fecha' => 'required',
            ], [
                'cliente.required' => 'El cliente es obligatorio',
                //'consignatario.required' => 'El consignatario es obligatorio',
                'agencia.required' => 'La agencia es obligatoria',
                'fecha.required' => 'La fecha es obligatoria',
            ]);
            if (!$valida->fails()) {
                DB::beginTransaction();
                $ini_timer = date('Y-m-d H:i:s');
                $fecha = $request->fecha;
                $ped = Pedido::find($request->id_pedido);
                $ped->tipo_pedido = $request->tipo;
                $oldFecha = $ped->fecha_pedido;
                $ped->fecha_pedido = $fecha;
                $ped->save();

                $envio = Envio::where('id_pedido', $ped->id_pedido)
                    ->get()
                    ->first();
                $envio->fecha_envio = $ped->fecha_pedido;
                $envio->id_consignatario = $request->consignatario;
                $envio->save();

                $ids_detalles_pedido = [];
                foreach (json_decode($request->detalles_pedido) as $pos_detalle => $d) {
                    if (isset($d->id_detalle_pedido)) {
                        $ids_detalles_pedido[] = $d->id_detalle_pedido;
                    }
                }

                $detalles_pedido_ausentes = DetallePedido::whereNotIn('id_detalle_pedido', $ids_detalles_pedido)
                    ->where('id_pedido', $ped->id_pedido)
                    ->delete();

                foreach (json_decode($request->detalles_pedido) as $pos_detalle => $d) {
                    if (isset($d->id_detalle_pedido)) { // editar detalle_pedido
                        //dump('existente');
                        //dump('Editando ESPECIFICACION_EMPAQUE');
                        $esp_emp = EspecificacionEmpaque::find($d->id_especificacion_empaque);
                        $esp_emp->id_empaque = $d->caja;
                        $esp_emp->save();

                        $precio = '';
                        foreach ($d->detalles_combo as $pos_det_esp => $det_combo) {
                            //dump('Editando DETALLE_ESPECIFICACION');
                            $det_esp = DetalleEspecificacionEmpaque::find($det_combo->id_detalle_especificacion);

                            if ($oldFecha != $fecha) {
                                jobCosechaEstimada::dispatch($det_esp->id_variedad, $det_esp->longitud_ramo, opDiasFecha('-', 1, $oldFecha))
                                    ->onQueue('cosecha_estimada')
                                    ->onConnection('database');
                            }
                            jobCosechaEstimada::dispatch($det_esp->id_variedad, $det_esp->longitud_ramo, opDiasFecha('-', 1, $fecha))
                                ->onQueue('cosecha_estimada')
                                ->onConnection('database');

                            if (
                                $det_esp->variedad->assorted == 1 &&
                                ($det_esp->cantidad != $det_combo->ramos_x_caja ||
                                    $det_esp->tallos_x_ramos != $det_combo->tallos_x_ramos ||
                                    $det_esp->longitud_ramo != $det_combo->longitud)
                            ) {
                                DB::select('delete from distribucion_mixtos where id_detalle_pedido = ' . $d->id_detalle_pedido . ' and id_detalle_especificacionempaque = ' . $det_esp->id_detalle_especificacionempaque);
                            }

                            $det_esp->id_variedad = $det_combo->variedad;
                            $det_esp->cantidad = $det_combo->ramos_x_caja;
                            $det_esp->id_empaque_p = $det_combo->presentacion;
                            $det_esp->tallos_x_ramos = $det_combo->tallos_x_ramos;
                            $det_esp->longitud_ramo = $det_combo->longitud;
                            $det_esp->save();

                            jobCosechaEstimada::dispatch($det_esp->id_variedad, $det_esp->longitud_ramo, opDiasFecha('-', 1, $fecha))
                                ->onQueue('cosecha_estimada')
                                ->onConnection('database');

                            if ($pos_det_esp == 0) {
                                $precio = $det_combo->precio_ped . ';' . $det_esp->id_detalle_especificacionempaque;
                            } else {
                                $precio .= '|' . $det_combo->precio_ped . ';' . $det_esp->id_detalle_especificacionempaque;
                            }

                            /* TABLA PEDIDO_CONFIRMACION */
                            $ped_conf = DB::table('pedido_confirmacion')
                                ->where('id_planta', $det_esp->variedad->id_planta)
                                ->where('fecha', $fecha)
                                ->get()
                                ->first();
                            if ($ped_conf == '') {
                                $ped_conf = new PedidoConfirmacion();
                                $ped_conf->id_planta = $det_esp->variedad->id_planta;
                                $ped_conf->fecha = $fecha;
                                $ped_conf->ejecutado = 0;
                                $ped_conf->save();
                            }
                        }

                        //dump('Editando DETALLE_PEDIDO');
                        $det_ped = DetallePedido::find($d->id_detalle_pedido);
                        $det_ped->id_agencia_carga = $request->agencia;
                        $oldPiezas = $det_ped->cantidad;
                        $det_ped->cantidad = $d->piezas;
                        $det_ped->orden = $pos_detalle + 1;
                        $det_ped->precio = $precio;
                        $det_ped->estado = 1;
                        $det_ped->save();

                        DB::select('delete from detallepedido_datoexportacion where id_detalle_pedido = ' . $d->id_detalle_pedido);

                        foreach ($d->valores_marcaciones as $pos_det_exp => $dato_exp) {
                            //dump('Editando DETALLE_PEDIDO_DATO_EXPORTACION');
                            if ($dato_exp->valor_marcacion != '') {
                                $det_ped_exp = new DetallePedidoDatoExportacion();
                                $det_ped_exp->id_detalle_pedido = $det_ped->id_detalle_pedido;
                                $det_ped_exp->id_dato_exportacion = $dato_exp->id_marcacion;
                                $det_ped_exp->valor = $dato_exp->valor_marcacion;
                                $det_ped_exp->save();
                            }
                        }

                        /* GUARDAR EN LA TABLA PEDIDO_MODIFICACION */
                        if ($oldFecha == $fecha && (hoy() == $fecha || hoy() == opDiasFecha('-', 1, $fecha))) { // misma fecha
                            if ($oldPiezas != $d->piezas) {
                                foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                                    foreach ($esp_emp->detalles as $det_esp_emp) {
                                        $diferencia = $d->piezas - $oldPiezas;
                                        if ($diferencia > 0)
                                            $signo = '+';
                                        else
                                            $signo = '-';

                                        $pedidoModificacion = new PedidoModificacion;
                                        $pedidoModificacion->id_cliente = $ped->id_cliente;
                                        $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                                        $pedidoModificacion->fecha_nuevo_pedido = $fecha;
                                        $pedidoModificacion->fecha_anterior_pedido = $fecha;
                                        $pedidoModificacion->cantidad = abs($diferencia); // piezas
                                        $pedidoModificacion->operador = $signo;
                                        $pedidoModificacion->id_usuario = Session::get('id_usuario');
                                        $ramos_x_caja = $det_esp_emp->cantidad;
                                        $pedidoModificacion->ramos_x_caja = $ramos_x_caja;
                                        $pedidoModificacion->save();

                                        if ($det_esp_emp->variedad->assorted == 1) {
                                            $distribuciones = DistribucionMixtos::where('id_detalle_pedido', $det_ped->id_detalle_pedido)
                                                ->where('id_detalle_especificacionempaque', $det_esp_emp->id_detalle_especificacionempaque)
                                                ->get();
                                            foreach ($distribuciones as $dist) {
                                                if ($dist->ramos > 0) {
                                                    $pedidoModificacion = new PedidoModificacion();
                                                    $pedidoModificacion->id_cliente = $ped->id_cliente;
                                                    $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                                                    $pedidoModificacion->fecha_nuevo_pedido =  $fecha;
                                                    $pedidoModificacion->fecha_anterior_pedido =  $fecha;
                                                    $pedidoModificacion->cantidad = null;
                                                    $pedidoModificacion->operador = $signo;
                                                    $pedidoModificacion->ramos = $dist->ramos * abs($diferencia);
                                                    $pedidoModificacion->tallos = $dist->ramos * abs($diferencia) * $det_esp_emp->tallos_x_ramos;
                                                    $pedidoModificacion->id_planta = $dist->id_planta;
                                                    $pedidoModificacion->siglas = $dist->siglas;
                                                    $pedidoModificacion->id_usuario = Session::get('id_usuario');
                                                    $pedidoModificacion->save();

                                                    $dist->piezas = $d->piezas;
                                                    $dist->tallos = $dist->ramos * $d->piezas * $det_esp_emp->tallos_x_ramos;
                                                    $dist->save();
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        if ($oldFecha != $fecha) {  // cambio de fecha
                            if (hoy() == $fecha || hoy() == opDiasFecha('-', 1, $fecha)) // fecha nueva
                                foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                                    foreach ($esp_emp->detalles as $det_esp_emp) {
                                        $pedidoModificacion = new PedidoModificacion;
                                        $pedidoModificacion->id_cliente = $ped->id_cliente;
                                        $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                                        $pedidoModificacion->fecha_nuevo_pedido = $oldFecha;
                                        $pedidoModificacion->fecha_anterior_pedido = $fecha;
                                        $pedidoModificacion->cambio_fecha = 1;
                                        $pedidoModificacion->cantidad = $d->piezas; // piezas
                                        $pedidoModificacion->operador = '+';
                                        $pedidoModificacion->id_usuario = Session::get('id_usuario');
                                        $ramos_x_caja = $det_esp_emp->cantidad;
                                        $pedidoModificacion->ramos_x_caja = $ramos_x_caja;
                                        $pedidoModificacion->save();

                                        if ($det_esp_emp->variedad->assorted == 1) {
                                            $distribuciones = DistribucionMixtos::where('id_detalle_pedido', $det_ped->id_detalle_pedido)
                                                ->where('id_detalle_especificacionempaque', $det_esp_emp->id_detalle_especificacionempaque)
                                                ->get();
                                            foreach ($distribuciones as $dist) {
                                                $pedidoModificacion = new PedidoModificacion();
                                                $pedidoModificacion->id_cliente = $ped->id_cliente;
                                                $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                                                $pedidoModificacion->fecha_nuevo_pedido = $oldFecha;
                                                $pedidoModificacion->fecha_anterior_pedido = $fecha;
                                                $pedidoModificacion->cantidad = null;
                                                $pedidoModificacion->operador = '+';
                                                $pedidoModificacion->ramos = $dist->ramos * $dist->piezas;
                                                $pedidoModificacion->tallos = $dist->ramos * $dist->piezas * $det_esp_emp->tallos_x_ramos;
                                                $pedidoModificacion->id_planta = $dist->id_planta;
                                                $pedidoModificacion->siglas = $dist->siglas;
                                                $pedidoModificacion->id_usuario = Session::get('id_usuario');
                                                $pedidoModificacion->save();
                                            }
                                        }
                                    }
                                }

                            if (hoy() == $oldFecha || hoy() == opDiasFecha('-', 1, $oldFecha)) // fecha vieja
                                foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                                    foreach ($esp_emp->detalles as $det_esp_emp) {
                                        $pedidoModificacion = new PedidoModificacion;
                                        $pedidoModificacion->id_cliente = $ped->id_cliente;
                                        $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                                        $pedidoModificacion->fecha_nuevo_pedido = $fecha;
                                        $pedidoModificacion->fecha_anterior_pedido = $oldFecha;
                                        $pedidoModificacion->cambio_fecha = 1;
                                        $pedidoModificacion->cantidad = $oldPiezas; // piezas
                                        $pedidoModificacion->operador = '-';
                                        $pedidoModificacion->id_usuario = Session::get('id_usuario');
                                        $ramos_x_caja = $det_esp_emp->cantidad;
                                        $pedidoModificacion->ramos_x_caja = $ramos_x_caja;
                                        $pedidoModificacion->save();

                                        if ($det_esp_emp->variedad->assorted == 1) {
                                            $distribuciones = DistribucionMixtos::where('id_detalle_pedido', $det_ped->id_detalle_pedido)
                                                ->where('id_detalle_especificacionempaque', $det_esp_emp->id_detalle_especificacionempaque)
                                                ->get();
                                            foreach ($distribuciones as $dist) {
                                                $pedidoModificacion = new PedidoModificacion();
                                                $pedidoModificacion->id_cliente = $ped->id_cliente;
                                                $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                                                $pedidoModificacion->fecha_nuevo_pedido = $fecha;
                                                $pedidoModificacion->fecha_anterior_pedido = $oldFecha;
                                                $pedidoModificacion->cantidad = null;
                                                $pedidoModificacion->operador = '-';
                                                $pedidoModificacion->ramos = $dist->ramos * $dist->piezas;
                                                $pedidoModificacion->tallos = $dist->ramos * $dist->piezas * $det_esp_emp->tallos_x_ramos;
                                                $pedidoModificacion->id_planta = $dist->id_planta;
                                                $pedidoModificacion->siglas = $dist->siglas;
                                                $pedidoModificacion->id_usuario = Session::get('id_usuario');
                                                $pedidoModificacion->save();
                                            }
                                        }
                                    }
                                }
                        }
                    } else {    // crear detalle_pedido
                        //dump('nuevo');
                        //dump('Creando ESPECIFICACION');
                        $esp = new Especificacion();
                        $esp->estado = 1;
                        $esp->tipo = 'N';
                        $esp->creada = 'EJECUCION';
                        $esp->save();
                        $esp->id_especificacion = DB::table('especificacion')
                            ->select(DB::raw('max(id_especificacion) as id'))
                            ->get()[0]->id;

                        //dump('Creando CLIENTE_PEDIDO_ESPECIFICACION');
                        $cli_ped = new ClientePedidoEspecificacion();
                        $cli_ped->id_especificacion = $esp->id_especificacion;
                        $cli_ped->id_cliente = $request->cliente;
                        $cli_ped->estado = 1;
                        $cli_ped->save();
                        $cli_ped->id_cliente_pedido_especificacion = DB::table('cliente_pedido_especificacion')
                            ->select(DB::raw('max(id_cliente_pedido_especificacion) as id'))
                            ->get()[0]->id;

                        //dump('Creando ESPECIFICACION_EMPAQUE');
                        $esp_emp = new EspecificacionEmpaque();
                        $esp_emp->id_especificacion = $esp->id_especificacion;
                        $esp_emp->id_empaque = $d->caja;
                        $esp_emp->cantidad = 1;
                        $esp_emp->save();
                        $esp_emp->id_especificacion_empaque = DB::table('especificacion_empaque')
                            ->select(DB::raw('max(id_especificacion_empaque) as id'))
                            ->get()[0]->id;

                        $precio = '';
                        foreach ($d->detalles_combo as $pos_det_esp => $det_combo) {
                            //dump('Creando DETALLE_ESPECIFICACION');
                            $det_esp = new DetalleEspecificacionEmpaque();
                            $det_esp->id_especificacion_empaque = $esp_emp->id_especificacion_empaque;
                            $det_esp->id_variedad = $det_combo->variedad;
                            $det_esp->id_clasificacion_ramo = 31;
                            $det_esp->cantidad = $det_combo->ramos_x_caja;
                            $det_esp->id_empaque_p = $det_combo->presentacion;
                            $det_esp->tallos_x_ramos = $det_combo->tallos_x_ramos;
                            $det_esp->longitud_ramo = $det_combo->longitud;
                            $det_esp->id_unidad_medida = 1;
                            $det_esp->save();
                            $det_esp->id_detalle_especificacionempaque = DB::table('detalle_especificacionempaque')
                                ->select(DB::raw('max(id_detalle_especificacionempaque) as id'))
                                ->get()[0]->id;

                            jobCosechaEstimada::dispatch($det_esp->id_variedad, $det_esp->longitud_ramo, opDiasFecha('-', 1, $fecha))
                                ->onQueue('cosecha_estimada')
                                ->onConnection('database');

                            if ($pos_det_esp == 0) {
                                $precio = $det_combo->precio_ped . ';' . $det_esp->id_detalle_especificacionempaque;
                            } else {
                                $precio .= '|' . $det_combo->precio_ped . ';' . $det_esp->id_detalle_especificacionempaque;
                            }
                        }

                        //dump('Creando DETALLE_PEDIDO');
                        $det_ped = new DetallePedido();
                        $det_ped->id_pedido = $ped->id_pedido;
                        $det_ped->id_cliente_especificacion = $cli_ped->id_cliente_pedido_especificacion;
                        $det_ped->id_agencia_carga = $request->agencia;
                        $det_ped->cantidad = $d->piezas;
                        $det_ped->orden = $pos_detalle + 1;
                        $det_ped->precio = $precio;
                        $det_ped->estado = 1;
                        $det_ped->save();
                        $det_ped->id_detalle_pedido = DB::table('detalle_pedido')
                            ->select(DB::raw('max(id_detalle_pedido) as id'))
                            ->get()[0]->id;

                        foreach ($d->valores_marcaciones as $pos_det_exp => $dato_exp) {
                            //dump('Creando DETALLE_PEDIDO_DATO_EXPORTACION');
                            if ($dato_exp->valor_marcacion != '') {
                                $det_ped_exp = new DetallePedidoDatoExportacion();
                                $det_ped_exp->id_detalle_pedido = $det_ped->id_detalle_pedido;
                                $det_ped_exp->id_dato_exportacion = $dato_exp->id_marcacion;
                                $det_ped_exp->valor = $dato_exp->valor_marcacion;
                                $det_ped_exp->save();
                            }
                        }

                        /* GUARDAR EN LA TABLA PEDIDO_MODIFICACION */
                        if (hoy() == $fecha || hoy() == opDiasFecha('-', 1, $fecha))    // fecha nueva (+)
                            foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                                foreach ($esp_emp->detalles as $det_esp_emp) {
                                    $pedidoModificacion = new PedidoModificacion;
                                    $pedidoModificacion->id_cliente = $ped->id_cliente;
                                    $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                                    $pedidoModificacion->fecha_nuevo_pedido = $fecha;
                                    $pedidoModificacion->fecha_anterior_pedido = $fecha;
                                    $pedidoModificacion->cantidad = $det_ped->cantidad; // piezas
                                    $pedidoModificacion->operador = '+';
                                    $pedidoModificacion->id_usuario = Session::get('id_usuario');
                                    $ramos_x_caja = $det_esp_emp->cantidad;
                                    $pedidoModificacion->ramos_x_caja = $ramos_x_caja;
                                    $pedidoModificacion->save();
                                }
                            }
                    }
                }

                if ($oldFecha != $fecha)
                    DB::select('update distribucion_mixtos set fecha = "' . opDiasFecha('-', 1, $fecha) . '" where id_pedido = ' . $ped->id_pedido);

                /* ======= ACTUALIZAR LA TABLA DISTRIBUCION_RECETAS ========== */
                DistribucionRecetas::dispatch($ped->id_pedido)->onQueue('buquets');

                bitacora('pedido', $ped->id_pedido, 'U', 'Modificar pedido con fecha ' . $ped->fecha_pedido . ', del cliente: ' . $ped->cliente->detalle()->nombre . ', tipo: ' . $ped->tipo_pedido . ' #' . $ped->orden_fija . ' desde el FORMULARIO_NUEVO_DE_EDICION');

                $success = true;
                $msg = 'Se ha <b>MODIFICADO</b> el pedido correctamente';
                DB::commit();

                $fin_timer = date('Y-m-d H:i:s');
                //dd('ok', difFechas($fin_timer, $ini_timer));
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
        } catch (\Exception $e) {

            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function borrar_detalle_pedido(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        try {
            $detalle_pedido = DetallePedido::find($request->id_detalle_pedido);
            $pedido = $detalle_pedido->pedido;

            if (count($pedido->detalles) > 1) {
                DB::beginTransaction();
                $fecha = $pedido->fecha_pedido;
                $resumen_variedades = [];

                /* GUARDAR EN LA TABLA PEDIDO_MODIFICACION */
                if (hoy() == $fecha || hoy() == opDiasFecha('-', 1, $fecha))    // fecha nueva (+)
                    foreach ($detalle_pedido->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                        foreach ($esp_emp->detalles as $det_esp_emp) {
                            $pedidoModificacion = new PedidoModificacion;
                            $pedidoModificacion->id_cliente = $pedido->id_cliente;
                            $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                            $pedidoModificacion->fecha_nuevo_pedido = $fecha;
                            $pedidoModificacion->fecha_anterior_pedido = $fecha;
                            $pedidoModificacion->cantidad = $detalle_pedido->cantidad; // piezas
                            $pedidoModificacion->operador = '-';
                            $pedidoModificacion->id_usuario = Session::get('id_usuario');
                            $ramos_x_caja = $det_esp_emp->cantidad;
                            $pedidoModificacion->ramos_x_caja = $ramos_x_caja;
                            $pedidoModificacion->save();

                            if (!in_array([
                                'variedad' => $det_esp_emp->id_variedad,
                                'longitud' => $det_esp_emp->longitud_ramo
                            ], $resumen_variedades)) {
                                $resumen_variedades[] = [
                                    'variedad' => $det_esp_emp->id_variedad,
                                    'longitud' => $det_esp_emp->longitud_ramo
                                ];
                            }

                            if ($det_esp_emp->variedad->assorted == 1) {
                                $distribuciones = DistribucionMixtos::where('id_detalle_pedido', $detalle_pedido->id_detalle_pedido)
                                    ->where('id_detalle_especificacionempaque', $det_esp_emp->id_detalle_especificacionempaque)
                                    ->get();
                                foreach ($distribuciones as $dist) {
                                    if ($dist->ramos > 0) {
                                        $pedidoModificacion = new PedidoModificacion();
                                        $pedidoModificacion->id_cliente = $pedido->id_cliente;
                                        $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                                        $pedidoModificacion->fecha_nuevo_pedido =  $fecha;
                                        $pedidoModificacion->fecha_anterior_pedido =  $fecha;
                                        $pedidoModificacion->cantidad = null;
                                        $pedidoModificacion->operador = '-';
                                        $pedidoModificacion->ramos = $dist->ramos * $dist->piezas;
                                        $pedidoModificacion->tallos = $dist->ramos * $dist->piezas * $det_esp_emp->tallos_x_ramos;
                                        $pedidoModificacion->id_planta = $dist->id_planta;
                                        $pedidoModificacion->siglas = $dist->siglas;
                                        $pedidoModificacion->id_usuario = Session::get('id_usuario');
                                        $pedidoModificacion->save();

                                        $dist_variedad = $dist->variedad();
                                        if (!in_array([
                                            'variedad' => $dist_variedad->id_variedad,
                                            'longitud' => $det_esp_emp->longitud_ramo
                                        ], $resumen_variedades)) {
                                            $resumen_variedades[] = [
                                                'variedad' => $dist_variedad->id_variedad,
                                                'longitud' => $det_esp_emp->longitud_ramo
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    }

                if ($request->perdida == 1) { // registrar como pérdida
                    $ped = new PedidoPerdido();
                    $ped->id_cliente = $pedido->id_cliente;
                    $ped->fecha_pedido = $fecha;
                    $ped->id_usuario = session('id_usuario');
                    $ped->save();
                    $ped->id_pedido = DB::table('pedido')
                        ->select(DB::raw('max(id_pedido) as id'))
                        ->get()[0]->id;

                    //dump('Creando ESPECIFICACION');
                    $esp = new Especificacion();
                    $esp->estado = 1;
                    $esp->tipo = 'N';
                    $esp->creada = 'EJECUCION';
                    $esp->save();
                    $esp->id_especificacion = DB::table('especificacion')
                        ->select(DB::raw('max(id_especificacion) as id'))
                        ->get()[0]->id;

                    //dump('Creando CLIENTE_PEDIDO_ESPECIFICACION');
                    $cli_ped = new ClientePedidoEspecificacion();
                    $cli_ped->id_especificacion = $esp->id_especificacion;
                    $cli_ped->id_cliente = $pedido->id_cliente;
                    $cli_ped->estado = 1;
                    $cli_ped->save();
                    $cli_ped->id_cliente_pedido_especificacion = DB::table('cliente_pedido_especificacion')
                        ->select(DB::raw('max(id_cliente_pedido_especificacion) as id'))
                        ->get()[0]->id;

                    //dump('Creando ESPECIFICACION_EMPAQUE');
                    $esp_emp = new EspecificacionEmpaque();
                    $esp_emp->id_especificacion = $esp->id_especificacion;
                    $esp_emp->id_empaque = $request->caja;
                    $esp_emp->cantidad = 1;
                    $esp_emp->save();
                    $esp_emp->id_especificacion_empaque = DB::table('especificacion_empaque')
                        ->select(DB::raw('max(id_especificacion_empaque) as id'))
                        ->get()[0]->id;

                    $precio = '';
                    foreach ($detalle_pedido->cliente_especificacion->especificacion->especificacionesEmpaque[0]->detalles as $pos_det_esp => $det_esp_emp) {
                        //dump('Creando DETALLE_ESPECIFICACION');
                        $det_esp = new DetalleEspecificacionEmpaque();
                        $det_esp->id_especificacion_empaque = $esp_emp->id_especificacion_empaque;
                        $det_esp->id_variedad = $det_esp_emp->id_variedad;
                        $det_esp->id_clasificacion_ramo = 31;
                        $det_esp->cantidad = $det_esp_emp->cantidad;
                        $det_esp->id_empaque_p = $det_esp_emp->id_empaque_p;
                        $det_esp->tallos_x_ramos = $det_esp_emp->tallos_x_ramos;
                        $det_esp->longitud_ramo = $det_esp_emp->longitud_ramo;
                        $det_esp->id_unidad_medida = 1;
                        $det_esp->save();
                        $det_esp->id_detalle_especificacionempaque = DB::table('detalle_especificacionempaque')
                            ->select(DB::raw('max(id_detalle_especificacionempaque) as id'))
                            ->get()[0]->id;

                        if ($pos_det_esp == 0) {
                            $precio = getPrecioByDetEsp($detalle_pedido->precio, $det_esp_emp->id_detalle_especificacionempaque) . ';' . $det_esp->id_detalle_especificacionempaque;
                        } else {
                            $precio .= '|' . getPrecioByDetEsp($detalle_pedido->precio, $det_esp_emp->id_detalle_especificacionempaque) . ';' . $det_esp->id_detalle_especificacionempaque;
                        }
                    }

                    //dump('Creando DETALLE_PEDIDO');
                    $det_ped = new DetallePedidoPerdido();
                    $det_ped->id_pedido_perdido = $ped->id_pedido_perdido;
                    $det_ped->id_cliente_especificacion = $cli_ped->id_cliente_pedido_especificacion;
                    $det_ped->id_agencia_carga = $detalle_pedido->id_agencia_carga;
                    $det_ped->cantidad = $detalle_pedido->cantidad;
                    $det_ped->precio = $precio;
                    $det_ped->save();
                    $det_ped->id_detalle_pedido = DB::table('detalle_pedido')
                        ->select(DB::raw('max(id_detalle_pedido) as id'))
                        ->get()[0]->id;
                }

                bitacora('detalle_pedido', $detalle_pedido->id_detalle_pedido, 'E', 'Eliminacion del detalle_pedido de ' . $detalle_pedido->cantidad . ' piezas con fecha ' . $pedido->fecha_pedido . ', tipo: ' . $pedido->tipo_pedido . ', #' . $pedido->orden_fija . ', del cliente: ' . $pedido->cliente->detalle()->nombre . ' desde el BOTON_ELIMINAR_DETALLE');
                $detalle_pedido->delete();

                foreach ($resumen_variedades as $r) {
                    jobCosechaEstimada::dispatch($r['variedad'], $r['longitud'], opDiasFecha('-', 1, $fecha))
                        ->onQueue('cosecha_estimada')
                        ->onConnection('database');
                }

                $success = true;
                $msg = 'Se ha <b>MODIFICADO</b> el pedido correctamente';
                DB::commit();
            } else {
                $success = false;
                $msg = '<div class="alert alert-danger text-center" style="font-size: 16px">No se puede eliminar, porque es el ultimo detalle del pedido</div>';
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function duplicar_contenido_pedido(Request $request)
    {
        $detalles_combo = [];
        foreach (json_decode($request->detalles_combo) as $d) {
            $variedad = Variedad::find($d->variedad);
            $detalles_combo[] = [
                'planta' =>  $variedad->planta,
                'variedad' =>  $variedad,
                'presentacion' => Empaque::find($d->presentacion),
                'longitud' => $d->longitud,
                'ramos_x_caja' => $d->ramos_x_caja,
                'tallos_x_ramos' => $d->tallos_x_ramos,
                'precio' => $d->precio_ped,
            ];
        }

        $plantas = DB::table('cliente_pedido_especificacion as cpe')
            ->join('especificacion as e', 'e.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('v.id_planta', 'p.nombre')->distinct()
            ->where('cpe.id_cliente', $request->cliente)
            ->where('e.creada', 'PRE-ESTABLECIDA')
            ->where('e.estado', 1)
            ->where('p.estado', 1)
            ->where('v.estado', 1)
            ->orderBy('p.orden')
            ->get();
        $option_plantas = '<option value="">Seleccione</option>';
        foreach ($plantas as $item) {
            $option_plantas .= '<option value="' . $item->id_planta . '">' . $item->nombre . '</option>';
        }

        $cajas = DB::table('cliente_pedido_especificacion as cpe')
            ->join('especificacion as e', 'e.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('empaque as emp', 'emp.id_empaque', '=', 'ee.id_empaque')
            ->select('ee.id_empaque', 'emp.nombre')->distinct()
            ->where('cpe.id_cliente', $request->cliente)
            ->where('e.creada', 'PRE-ESTABLECIDA')
            ->where('e.estado', 1)
            ->orderBy('emp.nombre')
            ->get();
        $option_cajas = '<option value="">Seleccione</option>';
        foreach ($cajas as $item) {
            $option_cajas .= '<option value="' . $item->id_empaque . '">' . explode('|', $item->nombre)[0] . '</option>';
        }

        $presentaciones = DB::table('cliente_pedido_especificacion as cpe')
            ->join('especificacion as e', 'e.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('empaque as emp', 'emp.id_empaque', '=', 'dee.id_empaque_p')
            ->select('dee.id_empaque_p', 'emp.nombre')->distinct()
            ->where('cpe.id_cliente', $request->cliente)
            ->where('e.creada', 'PRE-ESTABLECIDA')
            ->where('e.estado', 1)
            ->orderBy('emp.nombre')
            ->get();
        $option_presentaciones = '<option value="">Seleccione</option>';
        foreach ($presentaciones as $item) {
            $option_presentaciones .= '<option value="' . $item->id_empaque_p . '">' . $item->nombre . '</option>';
        }
        return view('adminlte.gestion.postcocecha.pedidos.forms.duplicar_contenido_pedido', [
            'piezas' => $request->piezas,
            'caja' => Empaque::find($request->caja),
            'valores_marcaciones' => json_decode($request->valores_marcaciones),
            'detalles_combo' => $detalles_combo,
            'num_pos' => $request->num_pos + 1,
            'plantas' => $plantas,
            'cajas' => $cajas,
            'presentaciones' => $presentaciones,
            'option_plantas' => $option_plantas,
            'option_cajas' => $option_cajas,
            'option_presentaciones' => $option_presentaciones,
            'cliente' => $request->cliente,
        ]);
    }

    public function generar_packings(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        try {
            DB::beginTransaction();

            $pedidos = Pedido::join('cliente as c', 'c.id_cliente', 'pedido.id_cliente')
                ->join('detalle_cliente as dc', function ($j) {
                    $j->on('dc.id_cliente', 'pedido.id_cliente')->where('dc.estado', true);
                })
                ->where('dc.estado', 1)
                ->where('c.estado', 1)
                ->where('pedido.estado', 1)
                ->whereBetween('pedido.fecha_pedido', [$request->desde, $request->hasta])
                ->select('pedido.*')
                ->orderBy('dc.nombre', 'asc')
                ->get();

            foreach ($pedidos as $pedido) {
                if ($pedido->packing == '') {
                    $last_packing = Pedido::orderBy('packing', 'desc')->first();
                    $pedido->packing = isset($last_packing->packing) ? $last_packing->packing + 1 : 1;
                    $pedido->save();
                    bitacora('pedido', $pedido->id_pedido, 'U', 'Generar numero de packing ' . $pedido->packing . ' desde el BOTON_GENERAR_PACKINGS');
                }
            }

            $success = true;
            $msg = 'Se han <b>GENERADO</b> los packings correctamente';
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function obtener_historial_orden_fija(Request $request)
    {
        $pedido = Pedido::find($request->id_ped);
        $fechas = DB::table('pedido')
            ->select('fecha_pedido')->distinct()
            ->where('tipo_pedido', 'STANDING ORDER')
            ->where('id_cliente', $pedido->id_cliente)
            ->where('orden_fija', $pedido->orden_fija)
            ->where('fecha_pedido', '>', $pedido->fecha_pedido)
            ->orderBy('fecha_pedido')
            ->get()->pluck('fecha_pedido')->toArray();
        return view(
            'adminlte.gestion.postcocecha.pedidos.forms.obtener_historial_orden_fija',
            [
                'pedido' => $pedido,
                'fechas' => $fechas,
            ]
        );
    }

    public function combinar_pedidos(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        try {
            DB::beginTransaction();

            if (count(json_decode($request->data)) > 1) {
                $clientes = DB::table('pedido')
                    ->select('id_cliente')->distinct()
                    ->whereIn('id_pedido', json_decode($request->data))
                    ->get()
                    ->pluck('id_cliente')
                    ->toArray();
                if (count($clientes) == 1) {
                    $fechas = DB::table('pedido')
                        ->select('fecha_pedido')->distinct()
                        ->whereIn('id_pedido', json_decode($request->data))
                        ->get()
                        ->pluck('fecha_pedido')
                        ->toArray();
                    if (count($fechas) == 1) {
                        $tipos_pedido = DB::table('pedido')
                            ->select('tipo_pedido')->distinct()
                            ->whereIn('id_pedido', json_decode($request->data))
                            ->get()
                            ->pluck('tipo_pedido')
                            ->toArray();
                        if (count($tipos_pedido) == 1) {
                            $tipo_pedido_resultante = $tipos_pedido[0];
                            $pedidos = Pedido::whereIn('id_pedido', json_decode($request->data))
                                ->get();

                            $fecha = $pedidos[0]->fecha_pedido;
                            $cliente = $pedidos[0]->id_cliente;
                            $envio_model = $pedidos[0]->envios[0];

                            $ped = new Pedido();
                            $ped->id_cliente = $cliente;
                            $ped->descripcion = '';
                            $ped->tipo_pedido = $tipo_pedido_resultante;
                            $ped->fecha_pedido = $fecha;
                            $ped->id_configuracion_empresa = 1;
                            $ped->variedad = '';
                            $ped->save();
                            $ped->id_pedido = DB::table('pedido')
                                ->select(DB::raw('max(id_pedido) as id'))
                                ->get()[0]->id;

                            $envio = new Envio();
                            $envio->fecha_envio = $fecha;
                            $envio->id_pedido = $ped->id_pedido;
                            $envio->id_consignatario = $envio_model->id_consignatario;
                            $envio->guia_madre = $envio_model->guia_madre;
                            $envio->guia_hija = $envio_model->guia_hija;
                            $envio->codigo_pais = $envio_model->codigo_pais;
                            $envio->codigo_dae = $envio_model->codigo_dae;
                            $envio->save();

                            foreach ($pedidos as $pedOriginal) {
                                foreach ($pedOriginal->detalles as $detOriginal) {
                                    $detOriginal->id_pedido = $ped->id_pedido;
                                    $detOriginal->save();
                                }
                                $distribuciones = DistribucionMixtos::where('id_pedido', $pedOriginal->id_pedido)
                                    ->get();
                                foreach ($distribuciones as $dist) {
                                    $dist->id_pedido = $ped->id_pedido;
                                    $dist->save();
                                }
                                $detalles_despacho = DetalleDespacho::where('id_pedido', $pedOriginal->id_pedido)
                                    ->get();
                                foreach ($detalles_despacho as $dd) {
                                    $dd->id_pedido = $ped->id_pedido;
                                    $dd->save();
                                }

                                /* GRABAR REGISTRO en tabla PEDIDO_UNIFICADO */
                                $pedido_unificado = new PedidoUnificado();
                                $pedido_unificado->orden_fija = $pedOriginal->orden_fija;
                                $pedido_unificado->fecha = $pedOriginal->fecha_pedido;
                                $pedido_unificado->id_usuario = session('id_usuario');
                                $pedido_unificado->id_cliente = $pedOriginal->id_cliente;
                                $pedido_unificado->save();

                                $pedOriginal->delete();
                            }
                        } else {
                            return [
                                'mensaje' => '<div class="alert alert-danger text-center" style="font-size: 16px">Debe seleccionar pedidos del <b>MISMO TIPO</b></div>',
                                'success' => false,
                            ];
                        }
                    } else {
                        return [
                            'mensaje' => '<div class="alert alert-danger text-center" style="font-size: 16px">Debe seleccionar pedidos de la <b>MISMA FECHA</b></div>',
                            'success' => false,
                        ];
                    }
                } else {
                    return [
                        'mensaje' => '<div class="alert alert-danger text-center" style="font-size: 16px">Debe seleccionar pedidos del <b>MISMO CLIENTE</b></div>',
                        'success' => false,
                    ];
                }
            } else {
                return [
                    'mensaje' => '<div class="alert alert-danger text-center" style="font-size: 16px">Debe seleccionar al menos <b>2 PEDIDOS</b></div>',
                    'success' => false,
                ];
            }

            $success = true;
            $msg = 'Se han <b>COMBINADOS</b> los pedidos correctamente';
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function separar_pedido(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        try {
            DB::beginTransaction();

            $pedOriginal = Pedido::find($request->id_ped);
            $data = json_decode($request->data);
            if (count($pedOriginal->detalles) > count($data)) {
                $fecha = $pedOriginal->fecha_pedido;
                $cliente = $pedOriginal->id_cliente;
                $envio_model = $pedOriginal->envios[0];

                $ped = new Pedido();
                $ped->id_cliente = $cliente;
                $ped->descripcion = '';
                $ped->tipo_pedido = 'OPEN MARKET';
                $ped->fecha_pedido = $fecha;
                $ped->id_configuracion_empresa = 1;
                $ped->variedad = '';
                $ped->save();
                $ped->id_pedido = DB::table('pedido')
                    ->select(DB::raw('max(id_pedido) as id'))
                    ->get()[0]->id;

                $envio = new Envio();
                $envio->fecha_envio = $fecha;
                $envio->id_pedido = $ped->id_pedido;
                $envio->id_consignatario = $envio_model->id_consignatario;
                $envio->guia_madre = $envio_model->guia_madre;
                $envio->guia_hija = $envio_model->guia_hija;
                $envio->codigo_pais = $envio_model->codigo_pais;
                $envio->codigo_dae = $envio_model->codigo_dae;
                $envio->save();

                $detalles_pedido = DetallePedido::whereIn('id_detalle_pedido', json_decode($request->data))->get();

                foreach ($detalles_pedido as $det_ped) {
                    $det_ped->id_pedido = $ped->id_pedido;
                    $det_ped->save();

                    $distribuciones = DistribucionMixtos::where('id_detalle_pedido', $det_ped->id_detalle_pedido)
                        ->get();
                    foreach ($distribuciones as $dist) {
                        $dist->id_pedido = $ped->id_pedido;
                        $dist->save();
                    }
                }
                DB::select('delete from detalle_despacho where id_pedido = ' . $pedOriginal->id_pedido);

                $success = true;
                $msg = 'Se ha <b>SEPARADO</b> el pedido correctamente';
            } else {
                $success = false;
                $msg = '<div class="alert alert-danger text-center" style="font-size: 16px">Este pedido <b>NO</b> se puede <b>SEPARAR</b> de esta forma<br><small>La cantidad de detalles a separar, debe ser <b>menor</b> que la cantidad <b>total</b> de detalles del pedido</small></div>';
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function edit_seleccionar_planta(Request $request)
    {
        $presentaciones = DB::table('cliente_pedido_especificacion as cpe')
            ->join('especificacion as e', 'e.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('empaque as emp', 'emp.id_empaque', '=', 'dee.id_empaque_p')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->select('dee.id_empaque_p', 'emp.nombre')->distinct()
            ->where('cpe.id_cliente', $request->cliente)
            ->where('e.creada', 'PRE-ESTABLECIDA')
            ->where('e.estado', 1)
            ->where('v.estado', 1)
            ->where('v.id_planta', $request->planta)
            ->orderBy('emp.nombre')
            ->get();
        $option_presentaciones = '';
        foreach ($presentaciones as $item) {
            $option_presentaciones .= '<option value="' . $item->id_empaque_p . '">' . $item->nombre . '</option>';
        }

        $cajas = DB::table('cliente_pedido_especificacion as cpe')
            ->join('especificacion as e', 'e.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('empaque as emp', 'emp.id_empaque', '=', 'ee.id_empaque')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->select('ee.id_empaque', 'emp.nombre')->distinct()
            ->where('cpe.id_cliente', $request->cliente)
            ->where('e.creada', 'PRE-ESTABLECIDA')
            ->where('e.estado', 1)
            ->where('emp.estado', 1)
            ->where('v.estado', 1)
            ->where('v.id_planta', $request->planta)
            ->orderBy('emp.nombre')
            ->get();
        $option_cajas = '';
        foreach ($cajas as $item) {
            $option_cajas .= '<option value="' . $item->id_empaque . '">' . explode('|', $item->nombre)[0] . '</option>';
        }

        $longitudes = DB::table('cliente_pedido_especificacion as cpe')
            ->join('especificacion as e', 'e.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->select('dee.longitud_ramo')->distinct()
            ->where('cpe.id_cliente', $request->cliente)
            ->where('e.creada', 'PRE-ESTABLECIDA')
            ->where('e.estado', 1)
            ->where('v.estado', 1)
            ->where('v.id_planta', $request->planta)
            ->orderBy('dee.longitud_ramo')
            ->get();
        $option_longitudes = '';
        foreach ($longitudes as $item) {
            $option_longitudes .= '<option value="' . $item->longitud_ramo . '">' . $item->longitud_ramo . 'cm</option>';
        }
        return [
            'presentaciones' => $option_presentaciones,
            'cajas' => $option_cajas,
            'longitudes' => $option_longitudes,
        ];
    }

    public function ver_resumen(Request $request)
    {
        $pedidos = Pedido::where('pedido.fecha_pedido', '>=', $request->desde)
            ->where('pedido.fecha_pedido', '<=', $request->hasta)
            ->where('pedido.estado', 1);
        if ($request->tipo_pedido != '')
            $pedidos = $pedidos->where('pedido.tipo_pedido', $request->tipo_pedido);
        if ($request->cliente != '')
            $pedidos = $pedidos->where('pedido.id_cliente', $request->cliente);
        if ($request->planta != '') {
            $pedidos = $pedidos->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'pedido.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->where('ee.estado', 1)
                ->where('dee.estado', 1)
                ->where('v.id_planta', $request->planta);
            if ($request->variedad != '')
                $pedidos = $pedidos->where('dee.id_variedad', $request->variedad);
        }
        $pedidos = $pedidos->select('pedido.*')->distinct()->get();

        $resumen_variedades = [];
        $resumen_presentaciones = [];
        $resumen_cajas = [];
        $list_esp_emp_contados = [];
        $caca = [];
        foreach ($pedidos as $ped) {
            foreach ($ped->detalles as $det_ped) {
                foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $pos_esp_emp => $esp_emp) {
                    foreach ($esp_emp->detalles as $pos_det_esp => $det_esp) {
                        $variedad = getVariedad($det_esp->id_variedad);
                        if ($request->planta == '' || $request->planta == $variedad->id_planta) {
                            if ($request->variedad == '' || $request->variedad == $variedad->id_variedad) {
                                if ($request->marcacion != '') {
                                    $ok_marcacion = false;
                                    foreach ($det_ped->detalle_pedido_dato_exportacion as $marcacion) {
                                        if ($marcacion->valor == $request->marcacion) {
                                            $ok_marcacion = true;
                                            break;
                                        }
                                    }
                                } else {
                                    $ok_marcacion = true;
                                }
                                if ($ok_marcacion) {    // cumple con el filtro marcacion
                                    //dd($det_ped->detalle_pedido_dato_exportacion);
                                    $ramos = $det_ped->cantidad * $esp_emp->cantidad * $det_esp->cantidad;
                                    $tallos = $det_ped->cantidad * $esp_emp->cantidad * $det_esp->cantidad * $det_esp->tallos_x_ramos;
                                    $dinero = $ramos * getPrecioByDetEsp($det_ped->precio, $det_esp->id_detalle_especificacionempaque);

                                    /* buscar la posicion de la variedad y longitud en el resumen */
                                    $pos_var = -1;
                                    foreach ($resumen_variedades as $pos_r => $r) {
                                        if ($r['variedad']->id_variedad == $det_esp->id_variedad && $r['longitud'] == $det_esp->longitud_ramo)
                                            $pos_var = $pos_r;
                                    }
                                    if ($pos_var == -1) { // la variedad y longitud es nueva en el resumen
                                        $resumen_variedades[] = [
                                            'variedad' => $variedad,
                                            'longitud' => $det_esp->longitud_ramo,
                                            'tallos' => $tallos,
                                            'ramos' => $ramos,
                                            'dinero' => $dinero,
                                        ];
                                    } else {    // la variedad y longitud ya existen en el resumen
                                        $resumen_variedades[$pos_var]['tallos'] += $tallos;
                                        $resumen_variedades[$pos_var]['ramos'] += $ramos;
                                        $resumen_variedades[$pos_var]['dinero'] += $dinero;
                                    }

                                    /* buscar la posicion de la presentacion en el resumen */
                                    $pos_var = -1;
                                    foreach ($resumen_presentaciones as $pos_r => $r) {
                                        if ($r['empaque']->id_empaque == $det_esp->id_empaque_p)
                                            $pos_var = $pos_r;
                                    }
                                    if ($pos_var == -1) { // la presentacion es nueva en el resumen
                                        $resumen_presentaciones[] = [
                                            'empaque' => getEmpaque($det_esp->id_empaque_p),
                                            'tallos' => $tallos,
                                            'ramos' => $ramos,
                                            'dinero' => $dinero,
                                        ];
                                    } else {    // la presentacion ya existe en el resumen
                                        $resumen_presentaciones[$pos_var]['tallos'] += $tallos;
                                        $resumen_presentaciones[$pos_var]['ramos'] += $ramos;
                                        $resumen_presentaciones[$pos_var]['dinero'] += $dinero;
                                    }

                                    /* buscar la posicion del tipo_caja en el resumen */
                                    $pos_var = -1;
                                    foreach ($resumen_cajas as $pos_r => $r) {
                                        if ($r['tipo_caja'] == explode('|', $esp_emp->empaque->nombre)[1])
                                            $pos_var = $pos_r;
                                    }
                                    if ($pos_var == -1) { // el tipo_caja es nueva en el resumen
                                        $resumen_cajas[] = [
                                            'tipo_caja' => explode('|', $esp_emp->empaque->nombre)[1],
                                            'cantidad' => $det_ped->cantidad,
                                        ];
                                        $list_esp_emp_contados[] = $esp_emp->id_especificacion_empaque . '|' . $det_ped->id_detalle_pedido;
                                    } else {    // el tipo_caja ya existe en el resumen
                                        if (!in_array($esp_emp->id_especificacion_empaque . '|' . $det_ped->id_detalle_pedido, $list_esp_emp_contados)) {
                                            $resumen_cajas[$pos_var]['cantidad'] += $det_ped->cantidad;
                                            $list_esp_emp_contados[] = $esp_emp->id_especificacion_empaque . '|' . $det_ped->id_detalle_pedido;
                                        }
                                    }
                                    if ($esp_emp->id_empaque == 150)
                                        $caca[] = $esp_emp->id_especificacion_empaque . ' => id_det_ped: ' . $det_ped->id_detalle_pedido . ' => cajas: ' . $det_ped->cantidad . ' => nombre: ' . $esp_emp->empaque->nombre;
                                }
                            }
                        }
                    }
                }
            }
        }
        return view(
            'adminlte.gestion.postcocecha.pedidos.partials.ver_resumen',
            [
                'resumen_variedades' => $resumen_variedades,
                'resumen_presentaciones' => $resumen_presentaciones,
                'resumen_cajas' => $resumen_cajas,
            ]
        );
    }
}
