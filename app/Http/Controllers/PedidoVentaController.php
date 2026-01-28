<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use yura\Jobs\ProyeccionVentaSemanalUpdate;
use yura\Modelos\Cliente;
use yura\Modelos\Color;
use yura\Modelos\Coloracion;
use yura\Modelos\DetalleEnvio;
use yura\Modelos\DetalleEspecificacionEmpaqueRamosCaja;
use yura\Modelos\DetallePedido;
use yura\Modelos\DetallePedidoDatoExportacion;
use yura\Modelos\Distribucion;
use yura\Modelos\DistribucionColoracion;
use yura\Modelos\Empaque;
use yura\Modelos\Marca;
use yura\Modelos\Marcacion;
use yura\Modelos\MarcacionColoracion;
use yura\Modelos\Pedido;
use DB;
use yura\Modelos\Submenu;
use yura\Modelos\DetalleCliente;
use yura\Modelos\ClientePedidoEspecificacion;
use yura\Modelos\ClienteAgenciaCarga;
use yura\Modelos\Envio;
use Carbon\Carbon;
use yura\Modelos\Especificacion;
use yura\Modelos\AgenciaCarga;
use yura\Modelos\DatosExportacion;
use Validator;
use yura\Modelos\ClienteConsignatario;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\Planta;
use yura\Modelos\Variedad;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use yura\Modelos\PedidoModificacion;

class PedidoVentaController extends Controller
{
    public function listar_pedidos(Request $request)
    {
        return view(
            'adminlte.gestion.postcocecha.pedidos_ventas.inicio',
            [
                'url' => $request->getRequestUri(),
                'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
                'text' => ['titulo' => 'Pedidos', 'subtitulo' => 'modulo de pedidos'],
                'clientes' => DB::table('cliente as c')
                    ->join('detalle_cliente as dc', 'c.id_cliente', '=', 'dc.id_cliente')
                    ->orderBy('nombre', 'asc')
                    ->where('dc.estado', 1)->get(),
                'annos' => DB::table('pedido as p')->select(DB::raw('YEAR(p.fecha_pedido) as anno'))
                    ->distinct()->get(),
                'empresas' => ConfiguracionEmpresa::where('estado', true)->get(),
                'plantas' => Planta::where('estado', true)->get()

            ]
        );
    }

    public function buscar_pedidos(Request $request)
    {
        $busquedaCliente = $request->has('id_cliente') ? $request->id_cliente : '';
        $busquedaAnno = $request->has('anno') ? $request->anno : '';
        $busquedaDesde = $request->has('desde') ? $request->desde : '';
        $busquedaHasta = $request->has('hasta') ? $request->hasta : '';

        $listado = DB::table('pedido as p')
            ->where('p.estado', $request->estado != '' ? $request->estado : 1)
            ->join('cliente_pedido_especificacion as cpe', 'p.id_cliente', '=', 'cpe.id_cliente')
            ->join('especificacion as esp', 'cpe.id_especificacion', '=', 'esp.id_especificacion')
            ->join('detalle_cliente as dc', 'p.id_cliente', '=', 'dc.id_cliente')
            ->join('detalle_pedido as dp', 'p.id_pedido', 'dp.id_pedido')
            ->select('p.*', 'dp.*', 'dc.nombre', 'p.fecha_pedido', 'p.id_cliente', 'dc.id_cliente')->where('dc.estado', 1);

        if ($request->anno != '')
            $listado = $listado->where(DB::raw('YEAR(p.fecha_pedido)'), $busquedaAnno);

        if ($busquedaDesde != '' && $request->hasta != '') {
            $listado = $listado->whereBetween('p.fecha_pedido', [$busquedaDesde, $busquedaHasta]);
            (Carbon::parse($busquedaHasta)->diffInDays($busquedaDesde) > 0)
                ? $a = true
                : $a = false;
        } else {
            $listado = $listado->where('p.fecha_pedido', Carbon::now()->toDateString());
            $a = false;
        }

        if ($request->id_cliente != '')
            $listado = $listado->where('p.id_cliente', $busquedaCliente);

        $listado = $listado->distinct()->orderBy('p.fecha_pedido', 'desc')->simplePaginate(20);

        $datos = [
            'listado' => $listado,
            'idCliente' => $request->id_cliente,
            'columnaFecha' => $a
        ];

        return view('adminlte.gestion.postcocecha.pedidos_ventas.partials.listado', $datos);
    }

    public function cargar_especificaciones(Request $request)
    {
        return [
            'especificaciones' => ClientePedidoEspecificacion::where('id_cliente', $request->id_cliente)
                ->join('especificacion as e', function ($j) {
                    $j->on('cliente_pedido_especificacion.id_especificacion', 'e.id_especificacion')
                        ->where('e.creada', 'PRE-ESTABLECIDA');
                })->select('cliente_pedido_especificacion.id_cliente_pedido_especificacion', 'e.nombre')->get(),
            'agencias_carga' => ClienteAgenciaCarga::where('id_cliente', $request->id_cliente)
                ->join('agencia_carga as ac', 'cliente_agenciacarga.id_agencia_carga', '=', 'ac.id_agencia_carga')
                ->select('ac.id_agencia_carga', 'ac.nombre')->get(),
            'iva_cliente' => getCliente($request->id_cliente)->detalle()->tipo_impuesto()['porcentaje']
        ];
    }

    public function add_orden_semanal(Request $request)
    {
        return view('adminlte.gestion.postcocecha.pedidos_ventas.partials.add_orden_semanal', [
            'clientes' => getClientes(),
            'empaques' => Empaque::All()->where('estado', '=', 1)->where('tipo', '=', 'C'),
            //'envolturas' => Empaque::All()->where('estado', '=', 1)->where('tipo', '=', 'E'),
            'presentaciones' => Empaque::All()->where('estado', '=', 1)->where('tipo', '=', 'P'),
            'colores' => Color::All()->where('estado', '=', 1),
        ]);
    }

    public function editar_pedido(Request $request)
    {
        $pedido = Pedido::where([
            ['pedido.id_pedido', $request->id_pedido],
            ['dc.estado', 1]
        ])->join('detalle_pedido as dp', 'pedido.id_pedido', 'dp.id_pedido')
            ->join('detalle_cliente as dc', 'pedido.id_cliente', 'dc.id_cliente')
            ->join('cliente_pedido_especificacion as cpe', 'dp.id_cliente_especificacion', 'cpe.id_cliente_pedido_especificacion')
            ->select('dp.cantidad as cantidad_especificacion', 'dp.precio', 'dp.id_agencia_carga', 'dc.id_cliente', 'pedido.fecha_pedido', 'pedido.descripcion', 'cpe.id_especificacion')->get();

        return [
            'pedido' => $pedido,
            'iva_cliente' => $pedido[0]->cliente->detalle()->tipo_impuesto()['porcentaje']

        ];
    }

    public function duplicar_especificacion(Request $request)
    {
        $empT = [];
        $empTallos = Empaque::where([
            ['f_empaque', 'T'],
            ['tipo', 'C']
        ])->get();
        foreach ($empTallos as $empRamo)
            $empT[] = $empRamo;

        $agenciasCarga = AgenciaCarga::where('c_ac.id_cliente', $request->id_cliente)
            ->join('cliente_agenciacarga as c_ac', 'agencia_carga.id_agencia_carga', 'c_ac.id_agencia_carga')->get();
        return view('adminlte.gestion.postcocecha.pedidos.forms.paritals.duplicar_especificacion', [
            'id_especificacion' => $request->id_especificacion,
            'agenciasCarga' => $agenciasCarga,
            'cant_esp' => $request->cant_esp,
            'id_cliente' => $request->id_cliente,
            'datos_exportacion' => DatosExportacion::join('cliente_datoexportacion as cde', 'dato_exportacion.id_dato_exportacion', 'cde.id_dato_exportacion')
                ->where('id_cliente', $request->id_cliente)->get(),
            'emp_tallos' => $empT,
            'tipo_especificacion' => getEspecificacion($request->id_especificacion)->tipo,
            'empaque' => $request->empaque
        ]);
    }

    public function form_duplicar_pedido(Request $request)
    {
        return view('adminlte.gestion.postcocecha.pedidos_ventas.forms.form_duplicar_pedido', [
            'id_pedido' => $request->id_pedido,
            'tipoPedido' => $request->tipo_pedido,
            'datos_exportacion' => DatosExportacion::join('cliente_datoexportacion as cde', 'dato_exportacion.id_dato_exportacion', 'cde.id_dato_exportacion')
                ->where('id_cliente', $request->id_cliente)->get(),
            'agenciasCarga' => AgenciaCarga::where('c_ac.id_cliente', $request->id_cliente)
                ->join('cliente_agenciacarga as c_ac', 'agencia_carga.id_agencia_carga', 'c_ac.id_agencia_carga')->get(),

        ]);
    }

    public function store_duplicar_pedido(Request $request)
    {
        //dd($request->all());
        $valida = Validator::make($request->all(), [
            //  'arrFechas' => 'required|Array',
            'id_pedido' => 'required',
        ]);

        if (!$valida->fails()) {

            ini_set('memory_limit', '-1');
            set_time_limit(0);

            DB::beginTransaction();

            try {

                $dataPedido = Pedido::find($request->id_pedido);

                if ($request->tipoPedido == 'STANDING ORDER') {
                    $fechas = [];
                    $desde = Carbon::parse($request->fecha_desde_pedido_fijo);
                    $hasta = Carbon::parse($request->fecha_hasta_pedido_fijo);

                    for ($x = $desde; $x <= $hasta; $x->addDay()) {
                        if ($x->dayOfWeek == $request->dia_semana)
                            $fechas[]['fecha'] = $x->toDateString();
                    }

                    $request->merge(['arrFechas' => $fechas]);
                }

                foreach ($request->arrFechas as $fecha) {

                    $empresa = ConfiguracionEmpresa::All()->where('estado', true)->first();

                    $objPedido = new Pedido;
                    $p = Pedido::orderBy('id_pedido', 'desc')->first();
                    $objPedido->id_pedido = isset($p->id_pedido) ? $p->id_pedido + 1 : 1;
                    $objPedido->id_cliente = $dataPedido->id_cliente;
                    $objPedido->fecha_pedido = $fecha['fecha'];
                    $objPedido->empaquetado = $dataPedido->empaquetado;
                    $objPedido->variedad = $dataPedido->variedad;
                    $objPedido->tipo_pedido = $request->tipoPedido;
                    $objPedido->tipo_especificacion = $dataPedido->tipo_especificacion;
                    $objPedido->id_configuracion_empresa = $dataPedido->id_configuracion_empresa;
                    $objPedido->eitqueta_impresa = $dataPedido->eitqueta_impresa;
                    $objPedido->packing = $empresa->numero_packing + 1;
                    if ($objPedido->save()) {
                        Artisan::call('update:pedido_confirmacion', [
                            'fecha' => $fecha['fecha']
                        ]);

                        $dataDetallePedido = $dataPedido->detalles;
                        $empresa->numero_packing = $objPedido->packing;
                        $empresa->save();
                        //bitacora('pedido', $modelPedido->id_pedido, 'I', 'Insercion satisfactoria de un duplicado de pedido');

                        $envio = $dataPedido->envios[0];

                        $objEnvio = new Envio;
                        $env = Envio::orderBy('id_envio', 'desc')->first();
                        $objEnvio->id_envio = isset($env->id_envio) ? $env->id_envio + 1 : 1;
                        $objEnvio->fecha_envio = $fecha['fecha'];
                        $objEnvio->id_pedido = $objPedido->id_pedido;
                        $objEnvio->id_consignatario = $envio->id_consignatario;

                        $objEnvio->save();

                        foreach ($dataDetallePedido as $detallePedido) {

                            $objDetallePedido = new DetallePedido;
                            $detPed = DetallePedido::orderBy('id_detalle_pedido', 'desc')->first();
                            $objDetallePedido->id_detalle_pedido = isset($detPed->id_detalle_pedido) ? $detPed->id_detalle_pedido + 1 : 1;
                            $objDetallePedido->id_cliente_especificacion = $detallePedido->id_cliente_especificacion;
                            $objDetallePedido->id_pedido = $objPedido->id_pedido;
                            $objDetallePedido->id_agencia_carga = $detallePedido->id_agencia_carga;
                            $objDetallePedido->cantidad = $detallePedido->cantidad;
                            $objDetallePedido->precio = $detallePedido->precio;
                            $objDetallePedido->orden = $detallePedido->orden;

                            $detalleEnvio = new DetalleEnvio;
                            $detEnv = DetalleEnvio::orderBy('id_detalle_envio', 'desc')->first();
                            $detalleEnvio->id_detalle_envio = isset($detEnv->id_detalle_envio) ? $detEnv->id_detalle_envio + 1 : 1;
                            $detalleEnvio->id_envio = $objEnvio->id_envio;
                            $objClienteEspecificacion = ClientePedidoEspecificacion::find($detallePedido->id_cliente_especificacion);
                            $detalleEnvio->id_especificacion = $objClienteEspecificacion->id_especificacion;
                            $detalleEnvio->cantidad = $detallePedido->cantidad;
                            $detalleEnvio->save();

                            if ($objDetallePedido->save()) {

                                foreach ($detallePedido->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                                    foreach ($esp_emp->detalles as $det_esp_emp) {
                                        $ramos_modificado = getRamosXCajaModificado($detallePedido->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);
                                        if (isset($ramos_modificado)) {
                                            $objDetEspEmpRamosCaja = new DetalleEspecificacionEmpaqueRamosCaja;
                                            $detEspEmpRxC = DetalleEspecificacionEmpaqueRamosCaja::orderBy('id_detalle_especificacionempaque_ramos_x_caja', 'desc')->first();
                                            $objDetEspEmpRamosCaja->id_detalle_especificacionempaque_ramos_x_caja = isset($detEspEmpRxC->id_detalle_especificacionempaque_ramos_x_caja) ? $detEspEmpRxC->id_detalle_especificacionempaque_ramos_x_caja + 1 : 1;
                                            $objDetEspEmpRamosCaja->id_detalle_pedido = $objDetallePedido->id_detalle_pedido;
                                            $objDetEspEmpRamosCaja->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                                            $objDetEspEmpRamosCaja->cantidad = $ramos_modificado->cantidad;
                                            $objDetEspEmpRamosCaja->fecha_registro = now()->format('Y-m-d H:i:s.v');
                                            $objDetEspEmpRamosCaja->save();
                                        }
                                    }
                                }


                                //bitacora('detalle_pedido', $model_detalle_pedido->id_detalle_pedido, 'I', 'Insercion satisfactoria del duplicado de un detalle pedio');

                                //foreach ($dataPedido->detalles as $dataDetPed) {
                                foreach ($detallePedido->detalle_pedido_dato_exportacion as $dePedDatExp) {
                                    $objDetallePedidoDatoExportacion = new DetallePedidoDatoExportacion;
                                    $dpe = DetallePedidoDatoExportacion::orderBy('id_detallepedido_datoexportacion', 'desc')->first();
                                    $objDetallePedidoDatoExportacion->id_detallepedido_datoexportacion = isset($dpe->id_detallepedido_datoexportacion) ? $dpe->id_detallepedido_datoexportacion + 1 : 1;
                                    $objDetallePedidoDatoExportacion->id_detalle_pedido = $objDetallePedido->id_detalle_pedido;
                                    $objDetallePedidoDatoExportacion->id_dato_exportacion = $dePedDatExp->id_dato_exportacion;
                                    $objDetallePedidoDatoExportacion->valor = $dePedDatExp->valor;
                                    $objDetallePedidoDatoExportacion->save();
                                }
                                //}

                                $success = true;
                                $msg = '<div class="alert alert-success text-center">' .
                                    '<p> Se ha duplicado el pedido exitosamente</p>'
                                    . '</div>';
                            }
                        }

                        // TABLA PEDIDO_MODIFICACION
                        if (hoy() == $objPedido->fecha_pedido || hoy() == opDiasFecha('-', 1, $objPedido->fecha_pedido)) {
                            foreach ($objPedido->detalles as $x => $det_ped) {
                                foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                                    foreach ($esp_emp->detalles as $det_esp_emp) {
                                        $pedidoModificacion = new PedidoModificacion();
                                        $pedidoModificacion->id_cliente = $objPedido->id_cliente;
                                        $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                                        $pedidoModificacion->fecha_nuevo_pedido = $objPedido->fecha_pedido;
                                        $pedidoModificacion->fecha_anterior_pedido = $objPedido->fecha_pedido;
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
                }

                DB::commit();
            } catch (\Exception $e) {

                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>
                            <p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                    . '</div>';
                DB::rollBack();
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

    public function empaquetar_pedido(Request $request)
    {

        $pedido = Pedido::find($request->id_pedido);
        $pedido->variedad = '';
        $pedido->empaquetado = true;

        if ($pedido->save()) {
            $success = true;
            $msg = '<div class="alert alert-success text-center">' .
                '<p> Se ha empaquetado el pedido exitosamente</p>'
                . '</div>';
            bitacora('pedido', $request->id_pedido, 'U', 'Pedido empaquetado con exito');
        } else {
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al empaquetar el pedido, intente nuevamente</p>'
                . '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function set_plantas_cajas_cliente(Request $request)
    {
        $plantas = ClientePedidoEspecificacion::join('especificacion as esp', 'cliente_pedido_especificacion.id_especificacion', 'esp.id_especificacion')
            ->join('especificacion_empaque as ee', 'esp.id_especificacion', 'ee.id_especificacion')
            ->join('detalle_especificacionempaque as det_esp_emp', 'ee.id_especificacion_empaque', 'det_esp_emp.id_especificacion_empaque')
            ->join('variedad as v', 'det_esp_emp.id_variedad', 'v.id_variedad')
            ->join('planta as p', 'v.id_planta', 'p.id_planta')
            ->where([
                ['cliente_pedido_especificacion.id_cliente', $request->id_cliente],
                ['cliente_pedido_especificacion.estado', true],
                ['esp.tipo', 'N'],
                ['esp.creada', 'PRE-ESTABLECIDA'],
                ['esp.estado', true],
                ['ee.estado', true],
                ['p.estado', true],
                ['v.estado', true]
            ])->select('p.id_planta', 'p.nombre as planta')->distinct()->get();

        $cajas = ClientePedidoEspecificacion::join('especificacion as esp', 'cliente_pedido_especificacion.id_especificacion', 'esp.id_especificacion')
            ->join('especificacion_empaque as ee', 'esp.id_especificacion', 'ee.id_especificacion')
            ->join('empaque as emp', 'ee.id_empaque', 'emp.id_empaque')
            ->where([
                ['cliente_pedido_especificacion.id_cliente', $request->id_cliente],
                ['cliente_pedido_especificacion.estado', true],
                ['esp.tipo', 'N'],
                ['esp.creada', 'PRE-ESTABLECIDA'],
                ['esp.estado', true],
                ['ee.estado', true],
                ['emp.estado', true],
                ['emp.tipo', 'C']
            ])->select('emp.id_empaque', 'emp.nombre as caja')->distinct()->get();

        $consignatario =  ClienteConsignatario::join('consignatario as c', 'cliente_consignatario.id_consignatario', 'c.id_consignatario')
            ->where([
                ['cliente_consignatario.id_cliente', $request->id_cliente],
                //  ['cliente_consignatario.estado',true]
            ])->select('c.id_consignatario', 'c.nombre', 'cliente_consignatario.default')->get();

        return [
            'plantas' => $plantas,
            'cajas' => $cajas,
            'consignatario' => $consignatario
        ];
    }

    public function get_variedad(Request $request)
    {
        return Variedad::where([
            ['id_planta', $request->id_planta],
            ['estado', true]
        ])->get();
    }

    public function restaurar_recetas(Request $request)
    {
        Artisan::call('distribucion:recetas', [
            'pedido' => $request->id_pedido
        ]);
        return [
            'success' => true,
            'mensaje' => 'Se han <b>RESTAURADO</b> las recetas',
        ];
    }
}
