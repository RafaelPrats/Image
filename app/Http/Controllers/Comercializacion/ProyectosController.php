<?php

namespace yura\Http\Controllers\Comercializacion;

use Artisan;
use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Jobs\jobStoreProyecto;
use yura\Modelos\CajaProyecto;
use yura\Modelos\CajaProyectoMarcacion;
use yura\Modelos\DatosExportacion;
use yura\Modelos\DetalleCajaProyecto;
use yura\Modelos\Empaque;
use yura\Modelos\Planta;
use yura\Modelos\Proyecto;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use Barryvdh\DomPDF\Facade as PDF;
use yura\Modelos\CambiosPedido;
use yura\Modelos\Mixtos;
use yura\Modelos\PedidoConfirmacion;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Jobs\jobCosechaEstimada;
use yura\Modelos\DetalleHojaRuta;

class ProyectosController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = Planta::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $clientes = DB::table('cliente as c')
            ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'c.id_cliente')
            ->select('dc.nombre', 'dc.id_cliente')
            ->where('c.estado', 1)
            ->where('dc.estado', 1)
            ->orderBy('dc.nombre')
            ->get();
        $valores_marcaciones = DB::table('caja_proyecto_marcacion')
            ->select('valor')->distinct()
            ->orderBy('valor')
            ->get();

        return view('adminlte.gestion.comercializacion.proyectos.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
            'clientes' => $clientes,
            'valores_marcaciones' => $valores_marcaciones,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $proyectos = DB::table('proyecto as p')
            ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
            ->join('detalle_caja_proyecto as det', 'det.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
            ->leftJoin('consignatario as cons', 'cons.id_consignatario', '=', 'p.id_consignatario')
            ->leftJoin('agencia_carga as age', 'age.id_agencia_carga', '=', 'p.id_agencia_carga')
            ->leftJoin('caja_proyecto_marcacion as cm', 'cm.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
            ->join('detalle_cliente as det_c', 'det_c.id_cliente', '=', 'p.id_cliente')
            ->join('variedad as v', 'v.id_variedad', '=', 'det.id_variedad')
            ->select(
                'p.id_proyecto',
                'p.id_cliente',
                'p.id_consignatario',
                'p.fecha',
                'p.tipo',
                'p.packing',
                'p.orden_fija',
                'cons.nombre as nombre_consignatario',
                'age.nombre as nombre_agencia',
                'det_c.nombre as nombre_cliente',
            )->distinct()
            ->where('p.fecha', '>=', $request->desde)
            ->where('p.fecha', '<=', $request->hasta)
            ->where('det_c.estado', 1);
        if ($request->tipo != '')
            $proyectos = $proyectos->where('p.tipo', $request->tipo);
        if ($request->cliente != '')
            $proyectos = $proyectos->where('p.id_cliente', $request->cliente);
        if ($request->planta != '')
            $proyectos = $proyectos->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $proyectos = $proyectos->where('det.id_variedad', $request->variedad);
        if ($request->marcacion != '')
            $proyectos = $proyectos->where('cm.valor', $request->marcacion);
        $proyectos = $proyectos->orderBy('p.fecha')
            ->orderBy('det_c.nombre')
            ->get();
        $listado = [];
        foreach ($proyectos as $proy) {
            $cajas = DB::table('caja_proyecto as cp')
                ->join('empaque as c', 'c.id_empaque', '=', 'cp.id_empaque')
                ->join('detalle_caja_proyecto as det', 'det.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                ->join('variedad as v', 'v.id_variedad', '=', 'det.id_variedad')
                ->select(
                    'cp.id_caja_proyecto',
                    'cp.cantidad',
                    'c.nombre',
                    'c.siglas',
                )->distinct()
                ->where('cp.id_proyecto', $proy->id_proyecto);
            if ($request->planta != '')
                $cajas = $cajas->where('v.id_planta', $request->planta);
            if ($request->variedad != '')
                $cajas = $cajas->where('det.id_variedad', $request->variedad);
            $cajas = $cajas->get();
            $valores_cajas = [];
            foreach ($cajas as $caja) {
                $detalles = DB::table('detalle_caja_proyecto as det')
                    ->join('empaque as pres', 'pres.id_empaque', '=', 'det.id_empaque')
                    ->join('variedad as v', 'v.id_variedad', '=', 'det.id_variedad')
                    ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                    ->select(
                        'det.id_empaque',
                        'det.id_variedad',
                        'det.ramos_x_caja',
                        'det.tallos_x_ramo',
                        'det.precio',
                        'det.longitud_ramo',
                        'pres.nombre as nombre_presentacion',
                        'v.nombre as nombre_variedad',
                        'p.nombre as nombre_planta',
                    )
                    ->where('det.id_caja_proyecto', $caja->id_caja_proyecto);
                if ($request->planta != '')
                    $detalles = $detalles->where('v.id_planta', $request->planta);
                if ($request->variedad != '')
                    $detalles = $detalles->where('det.id_variedad', $request->variedad);
                $detalles = $detalles->get();

                $marcaciones = DB::table('caja_proyecto_marcacion as cm')
                    ->join('dato_exportacion as m', 'm.id_dato_exportacion', '=', 'cm.id_dato_exportacion')
                    ->select(
                        'cm.id_dato_exportacion',
                        'm.nombre',
                        'cm.valor',
                    )->distinct()
                    ->where('cm.id_caja_proyecto', $caja->id_caja_proyecto)
                    ->get();

                $valores_cajas[] = [
                    'caja' => $caja,
                    'detalles' => $detalles,
                    'marcaciones' => $marcaciones,
                ];
            }
            $total_cajas = DB::table('caja_proyecto as cp')
                ->select(DB::raw('sum(cp.cantidad) as cantidad'))
                ->where('cp.id_proyecto', $proy->id_proyecto)
                ->get()[0]->cantidad;
            $listado[] = [
                'proyecto' => $proy,
                'valores_cajas' => $valores_cajas,
                'total_cajas' => $total_cajas,
            ];
        }
        return view('adminlte.gestion.comercializacion.proyectos.partials.listado', [
            'listado' => $listado,
        ]);
    }

    public function add_proyecto(Request $request)
    {
        $clientes = DB::table('cliente as c')
            ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'c.id_cliente')
            ->select('dc.nombre', 'dc.id_cliente')
            ->where('c.estado', 1)
            ->where('dc.estado', 1)
            ->orderBy('dc.nombre')
            ->get();

        $datos_exportacion = DatosExportacion::where('estado', 1)->get();

        return view('adminlte.gestion.comercializacion.proyectos.forms.add_proyecto', [
            'clientes' => $clientes,
            'datos_exportacion' => $datos_exportacion,
        ]);
    }

    public function cargar_opciones_orden_fija(Request $request)
    {
        return view(
            'adminlte.gestion.comercializacion.proyectos.forms.inputs_opciones_pedido_fijo',
            [
                'opcion' => $request->opcion
            ]
        );
    }

    public function seleccionar_cliente(Request $request)
    {
        $consignatarios = DB::table('cliente_consignatario as cc')
            ->join('consignatario as c', 'c.id_consignatario', '=', 'cc.id_consignatario')
            ->select('c.nombre', 'cc.id_consignatario')->distinct()
            ->where('c.estado', 1)
            ->where('cc.id_cliente', $request->cliente)
            ->orderBy('c.nombre')
            ->get();
        $options_consignatario = '';
        foreach ($consignatarios as $con) {
            $options_consignatario .= '<option value="' . $con->id_consignatario . '">' . $con->nombre . '</option>';
        }

        $options_agencia = '';
        $agencias_cliente = DB::table('cliente_agenciacarga as ca')
            ->join('agencia_carga as a', 'a.id_agencia_carga', '=', 'ca.id_agencia_carga')
            ->select('a.nombre', 'ca.id_agencia_carga')->distinct()
            ->where('a.estado', 1)
            ->where('ca.id_cliente', $request->cliente)
            ->orderBy('a.nombre')
            ->get();
        foreach ($agencias_cliente as $age) {
            $options_agencia .= '<option value="' . $age->id_agencia_carga . '">' . $age->nombre . '</option>';
        }
        $agencias = DB::table('agencia_carga as a')
            ->select('a.nombre', 'a.id_agencia_carga')->distinct()
            ->where('a.estado', 1)
            ->whereNotIn('a.id_agencia_carga', $agencias_cliente->pluck('id_agencia_carga')->toArray())
            ->orderBy('a.nombre')
            ->get();
        foreach ($agencias as $age) {
            $options_agencia .= '<option value="' . $age->id_agencia_carga . '">' . $age->nombre . '</option>';
        }

        $plantas = DB::table('especificaciones as e')
            ->join('planta as p', 'p.id_planta', '=', 'e.id_planta')
            ->select('p.nombre', 'e.id_planta')->distinct()
            ->where('p.estado', 1)
            ->where('e.id_cliente', $request->cliente)
            ->orderBy('p.nombre')
            ->get();
        $options_plantas = '<option value="">Seleccione</option>';
        foreach ($plantas as $pta) {
            $options_plantas .= '<option value="' . $pta->id_planta . '">' . $pta->nombre . '</option>';
        }

        $cajas = DB::table('especificaciones as e')
            ->join('empaque as p', 'p.id_empaque', '=', 'e.id_empaque_c')
            ->select('p.nombre', 'e.id_empaque_c')->distinct()
            ->where('p.estado', 1)
            ->where('e.id_cliente', $request->cliente)
            ->orderBy('p.nombre')
            ->get();
        $options_cajas = '<option value="">Seleccione</option>';
        foreach ($cajas as $caj) {
            $options_cajas .= '<option value="' . $caj->id_empaque_c . '">' . explode('|', $caj->nombre)[0] . '</option>';
        }

        return [
            'options_consignatario' => $options_consignatario,
            'options_agencia' => $options_agencia,
            'options_plantas' => $options_plantas,
            'options_cajas' => $options_cajas,
        ];
    }

    public function buscar_form_especificaciones(Request $request)
    {
        $listado = DB::table('especificaciones as e')
            ->join('planta as p', 'p.id_planta', '=', 'e.id_planta')
            ->join('variedad as v', 'v.id_variedad', '=', 'e.id_variedad')
            ->join('empaque as c', 'c.id_empaque', '=', 'e.id_empaque_c')
            ->join('empaque as pres', 'pres.id_empaque', '=', 'e.id_empaque_p')
            ->select(
                'p.nombre as pta_nombre',
                'v.nombre as var_nombre',
                'c.nombre as caj_nombre',
                'pres.nombre as pres_nombre',
                'e.*'
            )->distinct()
            ->where('e.id_cliente', $request->cliente)
            ->where('e.id_planta', $request->planta)
            ->where('p.estado', 1)
            ->where('v.estado', 1)
            ->where('c.estado', 1)
            ->where('pres.estado', 1);
        if ($request->variedad != '')
            $listado = $listado->where('e.id_variedad', $request->variedad);
        if ($request->caja != '')
            $listado = $listado->where('e.id_empaque_c', $request->caja);
        if ($request->ramos_x_caja != '')
            $listado = $listado->where('e.ramos_x_caja', $request->ramos_x_caja);
        if ($request->longitud != '')
            $listado = $listado->where('e.longitud_ramo', $request->longitud);
        $listado = $listado->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->orderBy('c.nombre')
            ->orderBy('pres.nombre')
            ->get();
        $datos_exportacion = DatosExportacion::where('estado', 1)->get();

        return view('adminlte.gestion.comercializacion.proyectos.forms._buscar_form_especificaciones', [
            'listado' => $listado,
            'datos_exportacion' => $datos_exportacion,
        ]);
    }

    public function form_combos_seleccionar_planta(Request $request)
    {
        $variedades = DB::table('especificaciones as e')
            ->join('variedad as v', 'v.id_variedad', '=', 'e.id_variedad')
            ->select('v.nombre', 'e.id_variedad')->distinct()
            ->where('v.estado', 1)
            ->where('e.id_cliente', $request->cliente)
            ->where('e.id_planta', $request->planta)
            ->orderBy('v.orden')
            ->get();
        $options_variedades = '<option value="">Seleccione</option>';
        foreach ($variedades as $item) {
            $options_variedades .= '<option value="' . $item->id_variedad . '">' . $item->nombre . '</option>';
        }

        $presentaciones = DB::table('especificaciones as e')
            ->join('empaque as p', 'p.id_empaque', '=', 'e.id_empaque_p')
            ->select('p.nombre', 'e.id_empaque_p')->distinct()
            ->where('p.estado', 1)
            ->where('e.id_cliente', $request->cliente)
            ->where('e.id_planta', $request->planta)
            ->orderBy('p.nombre')
            ->get();
        $options_presentaciones = '';
        foreach ($presentaciones as $item) {
            $options_presentaciones .= '<option value="' . $item->id_empaque_p . '">' . $item->nombre . '</option>';
        }

        $especificacion = DB::table('especificaciones')
            ->where('id_cliente', $request->cliente)
            ->where('estado', 1)
            ->where('id_planta', $request->planta)
            ->get()->first();
        $longitud = $especificacion->longitud_ramo;
        if ($request->planta == 2)
            $longitud = 70;
        $ramos_x_caja = $especificacion->ramos_x_caja;
        $tallos_x_ramos = $especificacion->tallos_x_ramos;
        return [
            'variedades' => $options_variedades,
            'presentaciones' => $options_presentaciones,
            'longitud' => $longitud,
            'ramos_x_caja' => $ramos_x_caja,
            'tallos_x_ramos' => $tallos_x_ramos,
        ];
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
        return view('adminlte.gestion.comercializacion.proyectos.forms._agregar_combos_pedido', [
            'piezas' => $request->piezas,
            'caja' => Empaque::find($request->caja),
            'celdas_marcaciones' => $request->celdas_marcaciones,
            'detalles_combo' => $detalles_combo,
            'form_cant_detalles' => $request->form_cant_detalles,
        ]);
    }

    public function store_proyecto(Request $request)
    {
        jobStoreProyecto::dispatch(
            $request->all(),
            session('id_usuario'),
            \Request::ip()
        )->onQueue('store_proyecto')->onConnection('database');

        $msg = 'Se esta <b>CREANDO</b> el pedido en un segundo plano';
        $success = true;
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function editar_proyecto(Request $request)
    {
        $proyecto = Proyecto::find($request->id);
        $cliente = $proyecto->cliente;
        $consignatarios = DB::table('cliente_consignatario as cc')
            ->join('consignatario as c', 'c.id_consignatario', '=', 'cc.id_consignatario')
            ->select('c.nombre', 'cc.id_consignatario')->distinct()
            ->where('c.estado', 1)
            ->where('cc.id_cliente', $proyecto->id_cliente)
            ->orderBy('c.nombre')
            ->get();
        $agencias = DB::table('agencia_carga as a')
            ->select('a.nombre', 'a.id_agencia_carga')->distinct()
            ->where('a.estado', 1)
            ->orderBy('a.nombre')
            ->get();
        $plantas = DB::table('especificaciones as e')
            ->join('planta as p', 'p.id_planta', '=', 'e.id_planta')
            ->select('p.nombre', 'e.id_planta')->distinct()
            ->where('p.estado', 1)
            ->where('e.id_cliente', $proyecto->id_cliente)
            ->orderBy('p.nombre')
            ->get();
        $options_plantas = '<option value="">Seleccione</option>';
        foreach ($plantas as $pta) {
            $options_plantas .= '<option value="' . $pta->id_planta . '">' . $pta->nombre . '</option>';
        }

        $cajas = DB::table('especificaciones as e')
            ->join('empaque as p', 'p.id_empaque', '=', 'e.id_empaque_c')
            ->select('p.nombre', 'e.id_empaque_c')->distinct()
            ->where('p.estado', 1)
            ->where('e.id_cliente', $proyecto->id_cliente)
            ->orderBy('p.nombre')
            ->get();
        $options_cajas = '<option value="">Seleccione</option>';
        foreach ($cajas as $caj) {
            $options_cajas .= '<option value="' . $caj->id_empaque_c . '">' . explode('|', $caj->nombre)[0] . '</option>';
        }

        $datos_exportacion = DatosExportacion::where('estado', 1)->get();
        $presentaciones = DB::table('especificaciones as e')
            ->join('empaque as p', 'p.id_empaque', '=', 'e.id_empaque_p')
            ->select('p.nombre', 'e.id_empaque_p')->distinct()
            ->where('p.estado', 1)
            ->where('e.id_cliente', $proyecto->id_cliente)
            ->orderBy('p.nombre')
            ->get();

        return view('adminlte.gestion.comercializacion.proyectos.forms.editar_proyecto', [
            'proyecto' => $proyecto,
            'cliente' => $cliente,
            'consignatarios' => $consignatarios,
            'agencias' => $agencias,
            'plantas' => $plantas,
            'options_plantas' => $options_plantas,
            'cajas' => $cajas,
            'options_cajas' => $options_cajas,
            'datos_exportacion' => $datos_exportacion,
            'presentaciones' => $presentaciones,
        ]);
    }

    public function update_proyecto(Request $request)
    {
        try {
            DB::beginTransaction();
            $proyecto = Proyecto::find($request->id_proyecto);
            $fecha_anterior = $proyecto->fecha;
            $fecha_actual = $request->fecha;
            $proyecto->fecha = $request->fecha;
            $proyecto->tipo = $request->tipo;
            $proyecto->id_consignatario = $request->consignatario;
            $proyecto->id_agencia_carga = $request->agencia;
            $proyecto->save();

            $cajas_actuales = '';
            foreach (json_decode($request->detalles_pedido) as $pos_det => $det_ped) {
                if (isset($det_ped->id_caja_proyecto)) {
                    // CAJA PROYECTO EXISTENTE
                    $caja = CajaProyecto::find($det_ped->id_caja_proyecto);
                    $cajaOriginal = $caja;
                    $oldCajas = $caja->cantidad;
                } else {
                    // NUEVA CAJA PROYECTO
                    $caja = new CajaProyecto();
                    $caja->id_proyecto = $proyecto->id_proyecto;
                    $cajaOriginal = '';
                    $oldCajas = 0;
                }
                $newCajas = $det_ped->piezas;
                $caja->cantidad = $det_ped->piezas;
                $caja->id_empaque = $det_ped->caja;
                $caja->save();
                if (!isset($det_ped->id_caja_proyecto)) {
                    $caja->id_caja_proyecto = DB::table('caja_proyecto')
                        ->select(DB::raw('max(id_caja_proyecto) as id'))
                        ->get()[0]->id;
                }
                foreach ($det_ped->detalles_combo as $det_caj) {
                    $tiene_cambios = false;
                    if (isset($det_caj->id_detalle_caja_proyecto)) {
                        // DETALLE CAJA PROYECTO EXISTENTE
                        $detalle = DetalleCajaProyecto::find($det_caj->id_detalle_caja_proyecto);
                        $detalleOriginal = [
                            'id_detalle_caja_proyecto' => $detalle->id_detalle_caja_proyecto,
                            'id_variedad' => $detalle->id_variedad,
                            'id_planta' => $detalle->variedad->id_planta,
                            'id_empaque' => $detalle->id_empaque,
                            'longitud_ramo' => $detalle->longitud_ramo,
                            'ramos_x_caja' => $detalle->ramos_x_caja,
                            'tallos_x_ramo' => $detalle->tallos_x_ramo,
                            'precio' => $detalle->precio,
                            'assorted' => $detalle->variedad->assorted,
                        ];
                        $oldRamos = $oldCajas * $detalle->ramos_x_caja;

                        if (
                            $detalle->id_variedad != $det_caj->variedad ||
                            $detalle->id_empaque != $det_caj->presentacion ||
                            $detalle->longitud_ramo != $det_caj->longitud
                        ) {
                            $tiene_cambios = true;
                        }
                    } else {
                        // NUEVO DETALLE CAJA PROYECTO
                        $detalleOriginal = '';
                        $detalle = new DetalleCajaProyecto();
                        $detalle->id_caja_proyecto = $caja->id_caja_proyecto;
                        $oldRamos = 0;
                    }
                    $newRamos = $newCajas * $det_caj->ramos_x_caja;
                    $detalle->id_variedad = $det_caj->variedad;
                    $detalle->id_empaque = $det_caj->presentacion;
                    $detalle->ramos_x_caja = $det_caj->ramos_x_caja;
                    $detalle->tallos_x_ramo = $det_caj->tallos_x_ramos;
                    $detalle->precio = $det_caj->precio_ped;
                    $detalle->longitud_ramo = $det_caj->longitud;
                    $detalle->save();

                    // GRABAR CAMBIOS
                    if (!$tiene_cambios) { // no hay cambios en variedad, presentacion o longitud
                        if ($fecha_anterior == $fecha_actual) {   // misma fecha
                            if (hoy() == $fecha_actual || hoy() == opDiasFecha('-', 1, $fecha_actual)) {
                                if ($oldRamos != $newRamos) {
                                    $difRamos = $newRamos - $oldRamos;
                                    $difCajas = $newCajas - $oldCajas;
                                    $factor = $difRamos > 0 ? 1 : -1;
                                    $pedidoModificacion = new CambiosPedido();
                                    $pedidoModificacion->id_cliente = $proyecto->id_cliente;
                                    $pedidoModificacion->id_planta = $detalle->variedad->id_planta;
                                    $pedidoModificacion->id_variedad = $detalle->id_variedad;
                                    $pedidoModificacion->fecha_actual = $fecha_actual;
                                    $pedidoModificacion->fecha_anterior = $fecha_actual;
                                    $pedidoModificacion->piezas = $difCajas; // piezas
                                    $pedidoModificacion->id_usuario = session('id_usuario');
                                    $pedidoModificacion->ramos = $difRamos;
                                    $pedidoModificacion->tallos = $difRamos * $detalle->tallos_x_ramo;
                                    $pedidoModificacion->ramos_x_caja = $detalle->ramos_x_caja;
                                    $pedidoModificacion->tallos_x_ramo = $detalle->tallos_x_ramo;
                                    $pedidoModificacion->longitud_ramo = $detalle->longitud_ramo;
                                    $pedidoModificacion->id_empaque_p = $detalle->id_empaque;
                                    $pedidoModificacion->id_empaque_c = $caja->id_empaque;
                                    $pedidoModificacion->save();

                                    // GRABAR CAMBIOS de MIXTOS
                                    if ($detalle->variedad->assorted == 1 && isset($det_caj->id_detalle_caja_proyecto)) {
                                        $distribuciones = Mixtos::where('id_detalle_caja_proyecto', $det_caj->id_detalle_caja_proyecto)
                                            ->get();
                                        foreach ($distribuciones as $dist) {
                                            if ($dist->ramos > 0) {
                                                $pedidoModificacion = new CambiosPedido();
                                                $pedidoModificacion->id_cliente = $proyecto->id_cliente;
                                                $pedidoModificacion->id_planta = $dist->id_planta;
                                                $pedidoModificacion->id_variedad = $dist->id_variedad;
                                                $pedidoModificacion->fecha_actual = $fecha_actual;
                                                $pedidoModificacion->fecha_anterior = $fecha_actual;
                                                $pedidoModificacion->piezas = null;
                                                $pedidoModificacion->id_usuario = session('id_usuario');
                                                $pedidoModificacion->ramos = $dist->ramos * $difCajas;
                                                $pedidoModificacion->tallos = $dist->ramos * $difCajas * $detalle->tallos_x_ramo;
                                                $pedidoModificacion->ramos_x_caja = $detalle->ramos_x_caja;
                                                $pedidoModificacion->tallos_x_ramo = $detalle->tallos_x_ramo;
                                                $pedidoModificacion->longitud_ramo = $detalle->longitud_ramo;
                                                $pedidoModificacion->id_empaque_p = $detalle->id_empaque;
                                                $pedidoModificacion->id_empaque_c = $caja->id_empaque;
                                                $pedidoModificacion->save();

                                                $dist->piezas = $caja->cantidad;
                                                $dist->tallos = $dist->ramos * $caja->cantidad * $detalle->tallos_x_ramo;
                                                $dist->save();
                                            }
                                        }
                                    }
                                }
                            }
                            /* TABLA PEDIDO_CONFIRMACION */
                            $ped_conf = PedidoConfirmacion::where('id_planta', $detalle->variedad->id_planta)
                                ->where('fecha', $fecha_actual)
                                ->first();
                            if ($ped_conf == '') {
                                $ped_conf = new PedidoConfirmacion();
                                $ped_conf->id_planta = $detalle->variedad->id_planta;
                                $ped_conf->fecha = $fecha_actual;
                                $ped_conf->ejecutado = 0;
                                $ped_conf->save();
                            }
                        }
                        // cambio de fecha
                        if ($fecha_anterior != $fecha_actual) {
                            if (hoy() == $fecha_anterior || hoy() == opDiasFecha('-', 1, $fecha_anterior)) {    // fecha anterior
                                $pedidoModificacion = new CambiosPedido();
                                $pedidoModificacion->id_cliente = $proyecto->id_cliente;
                                $pedidoModificacion->id_planta = $detalle->variedad->id_planta;
                                $pedidoModificacion->id_variedad = $detalle->id_variedad;
                                $pedidoModificacion->fecha_actual = $fecha_anterior;
                                $pedidoModificacion->fecha_anterior = $fecha_actual;
                                $pedidoModificacion->piezas = $oldCajas * -1; // piezas
                                $pedidoModificacion->id_usuario = session('id_usuario');
                                $pedidoModificacion->ramos = $oldRamos * -1;
                                $pedidoModificacion->tallos = $oldRamos * $detalle->tallos_x_ramo * -1;
                                $pedidoModificacion->ramos_x_caja = $detalle->ramos_x_caja;
                                $pedidoModificacion->tallos_x_ramo = $detalle->tallos_x_ramo;
                                $pedidoModificacion->longitud_ramo = $detalle->longitud_ramo;
                                $pedidoModificacion->id_empaque_p = $detalle->id_empaque;
                                $pedidoModificacion->id_empaque_c = $caja->id_empaque;
                                $pedidoModificacion->cambio_fecha = 1;
                                $pedidoModificacion->save();

                                // GRABAR CAMBIOS de MIXTOS
                                if ($detalle->variedad->assorted == 1) {
                                    $distribuciones = Mixtos::where('id_detalle_caja_proyecto', $detalle->id_detalle_caja_proyecto)
                                        ->get();
                                    foreach ($distribuciones as $dist) {
                                        if ($dist->ramos > 0) {
                                            $pedidoModificacion = new CambiosPedido();
                                            $pedidoModificacion->id_cliente = $proyecto->id_cliente;
                                            $pedidoModificacion->id_planta = $dist->id_planta;
                                            $pedidoModificacion->id_variedad = $dist->id_variedad;
                                            $pedidoModificacion->fecha_actual = $fecha_anterior;
                                            $pedidoModificacion->fecha_anterior = $fecha_actual;
                                            $pedidoModificacion->piezas = null;
                                            $pedidoModificacion->id_usuario = session('id_usuario');
                                            $pedidoModificacion->ramos = $dist->ramos * -1;
                                            $pedidoModificacion->tallos = $dist->tallos * -1;
                                            $pedidoModificacion->ramos_x_caja = $detalle->ramos_x_caja;
                                            $pedidoModificacion->tallos_x_ramo = $detalle->tallos_x_ramo;
                                            $pedidoModificacion->longitud_ramo = $detalle->longitud_ramo;
                                            $pedidoModificacion->id_empaque_p = $detalle->id_empaque;
                                            $pedidoModificacion->id_empaque_c = $caja->id_empaque;
                                            $pedidoModificacion->cambio_fecha = 1;
                                            $pedidoModificacion->save();
                                        }
                                    }
                                }
                            }
                            if (hoy() == $fecha_actual || hoy() == opDiasFecha('-', 1, $fecha_actual)) {    // fecha_actual
                                $pedidoModificacion = new CambiosPedido();
                                $pedidoModificacion->id_cliente = $proyecto->id_cliente;
                                $pedidoModificacion->id_planta = $detalle->variedad->id_planta;
                                $pedidoModificacion->id_variedad = $detalle->id_variedad;
                                $pedidoModificacion->fecha_actual = $fecha_actual;
                                $pedidoModificacion->fecha_anterior = $fecha_anterior;
                                $pedidoModificacion->piezas = $newCajas; // piezas
                                $pedidoModificacion->id_usuario = session('id_usuario');
                                $pedidoModificacion->ramos = $newRamos;
                                $pedidoModificacion->tallos = $newRamos * $detalle->tallos_x_ramo;
                                $pedidoModificacion->ramos_x_caja = $detalle->ramos_x_caja;
                                $pedidoModificacion->tallos_x_ramo = $detalle->tallos_x_ramo;
                                $pedidoModificacion->longitud_ramo = $detalle->longitud_ramo;
                                $pedidoModificacion->id_empaque_p = $detalle->id_empaque;
                                $pedidoModificacion->id_empaque_c = $caja->id_empaque;
                                $pedidoModificacion->cambio_fecha = 1;
                                $pedidoModificacion->save();

                                // GRABAR CAMBIOS de MIXTOS
                                if ($detalle->variedad->assorted == 1) {
                                    $distribuciones = Mixtos::where('id_detalle_caja_proyecto', $detalle->id_detalle_caja_proyecto)
                                        ->get();
                                    foreach ($distribuciones as $dist) {
                                        if ($dist->ramos > 0) {
                                            $pedidoModificacion = new CambiosPedido();
                                            $pedidoModificacion->id_cliente = $proyecto->id_cliente;
                                            $pedidoModificacion->id_planta = $dist->id_planta;
                                            $pedidoModificacion->id_variedad = $dist->id_variedad;
                                            $pedidoModificacion->fecha_actual = $fecha_actual;
                                            $pedidoModificacion->fecha_anterior = $fecha_anterior;
                                            $pedidoModificacion->piezas = null;
                                            $pedidoModificacion->id_usuario = session('id_usuario');
                                            $pedidoModificacion->ramos = $dist->ramos;
                                            $pedidoModificacion->tallos = $dist->tallos;
                                            $pedidoModificacion->ramos_x_caja = $detalle->ramos_x_caja;
                                            $pedidoModificacion->tallos_x_ramo = $detalle->tallos_x_ramo;
                                            $pedidoModificacion->longitud_ramo = $detalle->longitud_ramo;
                                            $pedidoModificacion->id_empaque_p = $detalle->id_empaque;
                                            $pedidoModificacion->id_empaque_c = $caja->id_empaque;
                                            $pedidoModificacion->cambio_fecha = 1;
                                            $pedidoModificacion->save();
                                        }
                                    }
                                }
                            }

                            /* TABLA PEDIDO_CONFIRMACION fecha actual*/
                            $ped_conf = PedidoConfirmacion::where('id_planta', $detalle->variedad->id_planta)
                                ->where('fecha', $fecha_actual)
                                ->first();
                            if ($ped_conf == '') {
                                $ped_conf = new PedidoConfirmacion();
                                $ped_conf->id_planta = $detalle->variedad->id_planta;
                                $ped_conf->fecha = $fecha_actual;
                                $ped_conf->ejecutado = 0;
                                $ped_conf->save();
                            }
                            /* TABLA PEDIDO_CONFIRMACION fecha anterior*/
                            $ped_conf = PedidoConfirmacion::where('id_planta', $detalle->variedad->id_planta)
                                ->where('fecha', $fecha_anterior)
                                ->first();
                            if ($ped_conf == '') {
                                $ped_conf = new PedidoConfirmacion();
                                $ped_conf->id_planta = $detalle->variedad->id_planta;
                                $ped_conf->fecha = $fecha_anterior;
                                $ped_conf->ejecutado = 0;
                                $ped_conf->save();
                            }
                        }
                    } else {    // tiene cambios en variedad, presentacion o longitud
                        // grabar cancelacion
                        if (hoy() == $fecha_anterior || hoy() == opDiasFecha('-', 1, $fecha_anterior)) { // solo importa la fecha anterior
                            $factor = -1;
                            $pedidoModificacion = new CambiosPedido();
                            $pedidoModificacion->id_cliente = $proyecto->id_cliente;
                            $pedidoModificacion->id_planta = $detalleOriginal['id_planta'];
                            $pedidoModificacion->id_variedad = $detalleOriginal['id_variedad'];
                            $pedidoModificacion->fecha_actual = $fecha_anterior;
                            $pedidoModificacion->fecha_anterior = $fecha_anterior;
                            $pedidoModificacion->piezas = $cajaOriginal->cantidad * $factor; // piezas
                            $pedidoModificacion->id_usuario = session('id_usuario');
                            $pedidoModificacion->ramos = $cajaOriginal->cantidad * $detalleOriginal['ramos_x_caja'] * $factor;
                            $pedidoModificacion->tallos = $cajaOriginal->cantidad * $detalleOriginal['ramos_x_caja'] * $detalleOriginal['tallos_x_ramo'] * $factor;
                            $pedidoModificacion->ramos_x_caja = $detalleOriginal['ramos_x_caja'];
                            $pedidoModificacion->tallos_x_ramo = $detalleOriginal['tallos_x_ramo'];
                            $pedidoModificacion->longitud_ramo = $detalleOriginal['longitud_ramo'];
                            $pedidoModificacion->id_empaque_p = $detalleOriginal['id_empaque'];
                            $pedidoModificacion->id_empaque_c = $cajaOriginal->id_empaque;
                            $pedidoModificacion->save();


                            // GRABAR CAMBIOS de MIXTOS
                            if ($detalleOriginal['assorted'] == 1) {
                                $distribuciones = Mixtos::where('id_detalle_caja_proyecto', $detalleOriginal['id_detalle_caja_proyecto'])
                                    ->get();
                                foreach ($distribuciones as $dist) {
                                    if ($dist->ramos > 0) {
                                        $pedidoModificacion = new CambiosPedido();
                                        $pedidoModificacion->id_cliente = $proyecto->id_cliente;
                                        $pedidoModificacion->id_planta = $dist->id_planta;
                                        $pedidoModificacion->id_variedad = $dist->id_variedad;
                                        $pedidoModificacion->fecha_actual = $fecha_actual;
                                        $pedidoModificacion->fecha_anterior = $fecha_actual;
                                        $pedidoModificacion->piezas = null;
                                        $pedidoModificacion->id_usuario = session('id_usuario');
                                        $pedidoModificacion->ramos = $dist->ramos * -1;
                                        $pedidoModificacion->tallos = $dist->tallos * -1;
                                        $pedidoModificacion->ramos_x_caja = $detalleOriginal['ramos_x_caja'];
                                        $pedidoModificacion->tallos_x_ramo = $detalleOriginal['tallos_x_ramo'];
                                        $pedidoModificacion->longitud_ramo = $detalleOriginal['longitud_ramo'];
                                        $pedidoModificacion->id_empaque_p = $detalleOriginal['id_empaque'];
                                        $pedidoModificacion->id_empaque_c = $cajaOriginal->id_empaque;
                                        $pedidoModificacion->save();
                                    }
                                }
                                DB::select('delete from mixtos where id_detalle_caja_proyecto = ' . $detalleOriginal['id_detalle_caja_proyecto']);
                            }
                        }

                        // grabar aumento
                        if (hoy() == $fecha_actual || hoy() == opDiasFecha('-', 1, $fecha_actual)) { // solo importa la fecha actual
                            $factor = 1;
                            $pedidoModificacion = new CambiosPedido();
                            $pedidoModificacion->id_cliente = $proyecto->id_cliente;
                            $pedidoModificacion->id_planta = $detalle->variedad->id_planta;
                            $pedidoModificacion->id_variedad = $detalle->id_variedad;
                            $pedidoModificacion->fecha_actual = $fecha_actual;
                            $pedidoModificacion->fecha_anterior = $fecha_actual;
                            $pedidoModificacion->piezas = $caja->cantidad * $factor; // piezas
                            $pedidoModificacion->id_usuario = session('id_usuario');
                            $pedidoModificacion->ramos = $caja->cantidad * $detalle->ramos_x_caja * $factor;
                            $pedidoModificacion->tallos = $caja->cantidad * $detalle->ramos_x_caja * $detalle->tallos_x_ramo * $factor;
                            $pedidoModificacion->ramos_x_caja = $detalle->ramos_x_caja;
                            $pedidoModificacion->tallos_x_ramo = $detalle->tallos_x_ramo;
                            $pedidoModificacion->longitud_ramo = $detalle->longitud_ramo;
                            $pedidoModificacion->id_empaque_p = $detalle->id_empaque;
                            $pedidoModificacion->id_empaque_c = $caja->id_empaque;
                            $pedidoModificacion->save();
                        }
                    }
                }

                DB::select('delete from caja_proyecto_marcacion where id_caja_proyecto = ' . $caja->id_caja_proyecto);
                foreach ($det_ped->valores_marcaciones as $marcacion) {
                    // NUEVA CAJA PROYECTO MARCACION
                    if ($marcacion->valor_marcacion != '') {
                        $caja_marcacion = new CajaProyectoMarcacion();
                        $caja_marcacion->id_caja_proyecto = $caja->id_caja_proyecto;
                        $caja_marcacion->id_dato_exportacion = $marcacion->id_marcacion;
                        $caja_marcacion->valor = $marcacion->valor_marcacion;
                        $caja_marcacion->save();
                    }
                }
                if ($pos_det == 0)
                    $cajas_actuales = $caja->id_caja_proyecto;
                else
                    $cajas_actuales .= ', ' . $caja->id_caja_proyecto;
            }

            // GRABAR CAMBIOS de las cajas eliminadas
            if (hoy() == $fecha_anterior || hoy() == opDiasFecha('-', 1, $fecha_anterior)) { // solo importa la fecha anterior
                $factor = -1;
                $delete_cajas = CajaProyecto::where('id_proyecto', $request->id_proyecto)
                    ->whereNotIn('id_caja_proyecto', explode(', ', $cajas_actuales))
                    ->get();
                foreach ($delete_cajas as $caja) {
                    foreach ($caja->detalles as $detalle) {
                        $pedidoModificacion = new CambiosPedido();
                        $pedidoModificacion->id_cliente = $proyecto->id_cliente;
                        $pedidoModificacion->id_planta = $detalle->variedad->id_planta;
                        $pedidoModificacion->id_variedad = $detalle->id_variedad;
                        $pedidoModificacion->fecha_actual = $fecha_anterior;
                        $pedidoModificacion->fecha_anterior = $fecha_anterior;
                        $pedidoModificacion->piezas = $caja->cantidad * $factor; // piezas
                        $pedidoModificacion->id_usuario = session('id_usuario');
                        $pedidoModificacion->ramos = $caja->cantidad * $detalle->ramos_x_caja * $factor;
                        $pedidoModificacion->tallos = $caja->cantidad * $detalle->ramos_x_caja * $detalle->tallos_x_ramo * $factor;
                        $pedidoModificacion->ramos_x_caja = $detalle->ramos_x_caja;
                        $pedidoModificacion->tallos_x_ramo = $detalle->tallos_x_ramo;
                        $pedidoModificacion->longitud_ramo = $detalle->longitud_ramo;
                        $pedidoModificacion->id_empaque_p = $detalle->id_empaque;
                        $pedidoModificacion->id_empaque_c = $caja->id_empaque;
                        $pedidoModificacion->save();


                        // GRABAR CAMBIOS de MIXTOS
                        if ($detalle->variedad->assorted == 1) {
                            $distribuciones = Mixtos::where('id_detalle_caja_proyecto', $detalle->id_detalle_caja_proyecto)
                                ->get();
                            foreach ($distribuciones as $dist) {
                                if ($dist->ramos > 0) {
                                    $pedidoModificacion = new CambiosPedido();
                                    $pedidoModificacion->id_cliente = $proyecto->id_cliente;
                                    $pedidoModificacion->id_planta = $dist->id_planta;
                                    $pedidoModificacion->id_variedad = $dist->id_variedad;
                                    $pedidoModificacion->fecha_actual = $fecha_actual;
                                    $pedidoModificacion->fecha_anterior = $fecha_actual;
                                    $pedidoModificacion->piezas = null;
                                    $pedidoModificacion->id_usuario = session('id_usuario');
                                    $pedidoModificacion->ramos = $dist->ramos * -1;
                                    $pedidoModificacion->tallos = $dist->tallos * -1;
                                    $pedidoModificacion->ramos_x_caja = $detalle->ramos_x_caja;
                                    $pedidoModificacion->tallos_x_ramo = $detalle->tallos_x_ramo;
                                    $pedidoModificacion->longitud_ramo = $detalle->longitud_ramo;
                                    $pedidoModificacion->id_empaque_p = $detalle->id_empaque;
                                    $pedidoModificacion->id_empaque_c = $caja->id_empaque;
                                    $pedidoModificacion->save();
                                }
                            }
                        }
                    }
                }
            }

            // ACTUALIZAR COSECHA ESTIMADA
            jobCosechaEstimada::dispatch(0, 0, $fecha_anterior)
                ->onQueue('cosecha_estimada')
                ->onConnection('database');
            if ($fecha_anterior != $fecha_actual)
                jobCosechaEstimada::dispatch(0, 0, $fecha_actual)
                    ->onQueue('cosecha_estimada')
                    ->onConnection('database');

            DB::select('delete from caja_proyecto where id_proyecto = ' . $request->id_proyecto . ' and id_caja_proyecto not in (' . $cajas_actuales . ')');

            DB::commit();
            $success = true;
            $msg = 'Se ha <b>GRABADO</b> el pedido correctamente';
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function delete_pedido(Request $request)
    {
        try {
            DB::beginTransaction();
            $proyecto = Proyecto::find($request->id);
            $fecha = $proyecto->fecha;
            // GRABAR CAMBIOS de las cajas eliminadas
            if (hoy() == $fecha || hoy() == opDiasFecha('-', 1, $fecha)) {
                foreach ($proyecto->cajas as $caja) {
                    foreach ($caja->detalles as $detalle) {
                        $pedidoModificacion = new CambiosPedido();
                        $pedidoModificacion->id_cliente = $proyecto->id_cliente;
                        $pedidoModificacion->id_planta = $detalle->variedad->id_planta;
                        $pedidoModificacion->id_variedad = $detalle->id_variedad;
                        $pedidoModificacion->fecha_actual = $fecha;
                        $pedidoModificacion->fecha_anterior = $fecha;
                        $pedidoModificacion->piezas = $caja->cantidad * -1; // piezas
                        $pedidoModificacion->id_usuario = session('id_usuario');
                        $pedidoModificacion->ramos = $caja->cantidad * $detalle->ramos_x_caja * -1;
                        $pedidoModificacion->tallos = $caja->cantidad * $detalle->ramos_x_caja * $detalle->tallos_x_ramo * -1;
                        $pedidoModificacion->ramos_x_caja = $detalle->ramos_x_caja;
                        $pedidoModificacion->tallos_x_ramo = $detalle->tallos_x_ramo;
                        $pedidoModificacion->longitud_ramo = $detalle->longitud_ramo;
                        $pedidoModificacion->id_empaque_p = $detalle->id_empaque;
                        $pedidoModificacion->id_empaque_c = $caja->id_empaque;
                        $pedidoModificacion->save();


                        // GRABAR CAMBIOS de MIXTOS
                        if ($detalle->variedad->assorted == 1) {
                            $distribuciones = Mixtos::where('id_detalle_caja_proyecto', $detalle->id_detalle_caja_proyecto)
                                ->get();
                            foreach ($distribuciones as $dist) {
                                if ($dist->ramos > 0) {
                                    $pedidoModificacion = new CambiosPedido();
                                    $pedidoModificacion->id_cliente = $proyecto->id_cliente;
                                    $pedidoModificacion->id_planta = $dist->id_planta;
                                    $pedidoModificacion->id_variedad = $dist->id_variedad;
                                    $pedidoModificacion->fecha_actual = $fecha;
                                    $pedidoModificacion->fecha_anterior = $fecha;
                                    $pedidoModificacion->piezas = null;
                                    $pedidoModificacion->id_usuario = session('id_usuario');
                                    $pedidoModificacion->ramos = $dist->ramos * -1;
                                    $pedidoModificacion->tallos = $dist->tallos * -1;
                                    $pedidoModificacion->ramos_x_caja = $detalle->ramos_x_caja;
                                    $pedidoModificacion->tallos_x_ramo = $detalle->tallos_x_ramo;
                                    $pedidoModificacion->longitud_ramo = $detalle->longitud_ramo;
                                    $pedidoModificacion->id_empaque_p = $detalle->id_empaque;
                                    $pedidoModificacion->id_empaque_c = $caja->id_empaque;
                                    $pedidoModificacion->save();
                                }
                            }
                        }
                    }
                }
            }

            foreach ($proyecto->cajas as $caja) {
                foreach ($caja->detalles as $detalle) {
                    jobCosechaEstimada::dispatch($detalle->id_variedad, $detalle->longitud_ramo, $fecha)
                        ->onQueue('cosecha_estimada')
                        ->onConnection('database');
                }
            }

            $proyecto->delete();

            DB::commit();
            $success = true;
            $msg = 'Se ha <b>CANCELADO</b> el pedido correctamente';
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function mover_fecha_orden_fija(Request $request)
    {
        $pedido = Proyecto::find($request->id);
        $ordenes = Proyecto::where('fecha', '>=', $pedido->fecha)
            ->where('orden_fija', $pedido->orden_fija)
            ->where('tipo', 'SO')
            ->where('id_cliente', $pedido->id_cliente)
            ->orderBy('fecha')
            ->get();
        return view('adminlte.gestion.comercializacion.proyectos.forms.mover_fecha_orden_fija', [
            'pedido' => $pedido,
            'ordenes' => $ordenes,
        ]);
    }

    public function store_mover_fechas(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach (json_decode($request->data) as $d) {
                $proyecto = Proyecto::find($d->id_ped);
                $pos_dia = transformDiaPhp(date('w', strtotime($proyecto->fecha)));
                $dias = $d->dia - $pos_dia;
                $fecha_anterior = $proyecto->fecha;
                $proyecto->fecha = opDiasFecha($dias > 0 ? '+' : '-', $dias > 0 ? $dias : ($dias * (-1)), $proyecto->fecha);
                $fecha_actual = $proyecto->fecha;

                if ($fecha_anterior != $fecha_actual) { // cambio de fecha
                    foreach ($proyecto->cajas as $caja) {
                        foreach ($caja->detalles as $detalle) {
                            if (hoy() == $fecha_anterior || hoy() == opDiasFecha('-', 1, $fecha_anterior)) {    // fecha anterior
                                $pedidoModificacion = new CambiosPedido();
                                $pedidoModificacion->id_cliente = $proyecto->id_cliente;
                                $pedidoModificacion->id_planta = $detalle->variedad->id_planta;
                                $pedidoModificacion->id_variedad = $detalle->id_variedad;
                                $pedidoModificacion->fecha_actual = $fecha_anterior;
                                $pedidoModificacion->fecha_anterior = $fecha_actual;
                                $pedidoModificacion->piezas = $caja->cantidad * -1; // piezas
                                $pedidoModificacion->id_usuario = session('id_usuario');
                                $pedidoModificacion->ramos = $caja->cantidad * $detalle->ramos_x_caja * -1;
                                $pedidoModificacion->tallos = $caja->cantidad * $detalle->ramos_x_caja * $detalle->tallos_x_ramo * -1;
                                $pedidoModificacion->ramos_x_caja = $detalle->ramos_x_caja;
                                $pedidoModificacion->tallos_x_ramo = $detalle->tallos_x_ramo;
                                $pedidoModificacion->longitud_ramo = $detalle->longitud_ramo;
                                $pedidoModificacion->id_empaque_p = $detalle->id_empaque;
                                $pedidoModificacion->id_empaque_c = $caja->id_empaque;
                                $pedidoModificacion->cambio_fecha = 1;
                                $pedidoModificacion->save();

                                // GRABAR CAMBIOS de MIXTOS
                                if ($detalle->variedad->assorted == 1) {
                                    $distribuciones = Mixtos::where('id_detalle_caja_proyecto', $detalle->id_detalle_caja_proyecto)
                                        ->get();
                                    foreach ($distribuciones as $dist) {
                                        if ($dist->ramos > 0) {
                                            $pedidoModificacion = new CambiosPedido();
                                            $pedidoModificacion->id_cliente = $proyecto->id_cliente;
                                            $pedidoModificacion->id_planta = $dist->id_planta;
                                            $pedidoModificacion->id_variedad = $dist->id_variedad;
                                            $pedidoModificacion->fecha_actual = $fecha_anterior;
                                            $pedidoModificacion->fecha_anterior = $fecha_actual;
                                            $pedidoModificacion->piezas = null;
                                            $pedidoModificacion->id_usuario = session('id_usuario');
                                            $pedidoModificacion->ramos = $dist->ramos * -1;
                                            $pedidoModificacion->tallos = $dist->tallos * -1;
                                            $pedidoModificacion->ramos_x_caja = $detalle->ramos_x_caja;
                                            $pedidoModificacion->tallos_x_ramo = $detalle->tallos_x_ramo;
                                            $pedidoModificacion->longitud_ramo = $detalle->longitud_ramo;
                                            $pedidoModificacion->id_empaque_p = $detalle->id_empaque;
                                            $pedidoModificacion->id_empaque_c = $caja->id_empaque;
                                            $pedidoModificacion->cambio_fecha = 1;
                                            $pedidoModificacion->save();
                                        }
                                    }
                                }
                            }
                            if (hoy() == $fecha_actual || hoy() == opDiasFecha('-', 1, $fecha_actual)) {    // fecha anterior
                                $pedidoModificacion = new CambiosPedido();
                                $pedidoModificacion->id_cliente = $proyecto->id_cliente;
                                $pedidoModificacion->id_planta = $detalle->variedad->id_planta;
                                $pedidoModificacion->id_variedad = $detalle->id_variedad;
                                $pedidoModificacion->fecha_actual = $fecha_actual;
                                $pedidoModificacion->fecha_anterior = $fecha_anterior;
                                $pedidoModificacion->piezas = $caja->cantidad; // piezas
                                $pedidoModificacion->id_usuario = session('id_usuario');
                                $pedidoModificacion->ramos = $caja->cantidad * $detalle->ramos_x_caja;
                                $pedidoModificacion->tallos = $caja->cantidad * $detalle->ramos_x_caja * $detalle->tallos_x_ramo;
                                $pedidoModificacion->ramos_x_caja = $detalle->ramos_x_caja;
                                $pedidoModificacion->tallos_x_ramo = $detalle->tallos_x_ramo;
                                $pedidoModificacion->longitud_ramo = $detalle->longitud_ramo;
                                $pedidoModificacion->id_empaque_p = $detalle->id_empaque;
                                $pedidoModificacion->id_empaque_c = $caja->id_empaque;
                                $pedidoModificacion->cambio_fecha = 1;
                                $pedidoModificacion->save();

                                // GRABAR CAMBIOS de MIXTOS
                                if ($detalle->variedad->assorted == 1) {
                                    $distribuciones = Mixtos::where('id_detalle_caja_proyecto', $detalle->id_detalle_caja_proyecto)
                                        ->get();
                                    foreach ($distribuciones as $dist) {
                                        if ($dist->ramos > 0) {
                                            $pedidoModificacion = new CambiosPedido();
                                            $pedidoModificacion->id_cliente = $proyecto->id_cliente;
                                            $pedidoModificacion->id_planta = $dist->id_planta;
                                            $pedidoModificacion->id_variedad = $dist->id_variedad;
                                            $pedidoModificacion->fecha_actual = $fecha_actual;
                                            $pedidoModificacion->fecha_anterior = $fecha_anterior;
                                            $pedidoModificacion->piezas = null;
                                            $pedidoModificacion->id_usuario = session('id_usuario');
                                            $pedidoModificacion->ramos = $dist->ramos;
                                            $pedidoModificacion->tallos = $dist->tallos;
                                            $pedidoModificacion->ramos_x_caja = $detalle->ramos_x_caja;
                                            $pedidoModificacion->tallos_x_ramo = $detalle->tallos_x_ramo;
                                            $pedidoModificacion->longitud_ramo = $detalle->longitud_ramo;
                                            $pedidoModificacion->id_empaque_p = $detalle->id_empaque;
                                            $pedidoModificacion->id_empaque_c = $caja->id_empaque;
                                            $pedidoModificacion->cambio_fecha = 1;
                                            $pedidoModificacion->save();
                                        }
                                    }
                                }
                            }
                            /* TABLA PEDIDO_CONFIRMACION fecha actual*/
                            $ped_conf = PedidoConfirmacion::where('id_planta', $detalle->variedad->id_planta)
                                ->where('fecha', $fecha_actual)
                                ->first();
                            if ($ped_conf == '') {
                                $ped_conf = new PedidoConfirmacion();
                                $ped_conf->id_planta = $detalle->variedad->id_planta;
                                $ped_conf->fecha = $fecha_actual;
                                $ped_conf->ejecutado = 0;
                                $ped_conf->save();
                            }
                            /* TABLA PEDIDO_CONFIRMACION fecha anterior*/
                            $ped_conf = PedidoConfirmacion::where('id_planta', $detalle->variedad->id_planta)
                                ->where('fecha', $fecha_anterior)
                                ->first();
                            if ($ped_conf == '') {
                                $ped_conf = new PedidoConfirmacion();
                                $ped_conf->id_planta = $detalle->variedad->id_planta;
                                $ped_conf->fecha = $fecha_anterior;
                                $ped_conf->ejecutado = 0;
                                $ped_conf->save();
                            }

                            jobCosechaEstimada::dispatch($detalle->id_variedad, $detalle->longitud_ramo, $fecha_anterior)
                                ->onQueue('cosecha_estimada')
                                ->onConnection('database');
                            jobCosechaEstimada::dispatch($detalle->id_variedad, $detalle->longitud_ramo, $fecha_actual)
                                ->onQueue('cosecha_estimada')
                                ->onConnection('database');
                        }
                    }
                }
                $proyecto->save();

                bitacora('proyecto', $proyecto->id_proyecto, 'U', 'CAMBIO DE FECHA DE LA ORDEN FIJA #' . $proyecto->orden_fija);
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

    public function pre_factura(Request $request)
    {
        $pedido = Proyecto::find($request->id);

        return PDF::loadView('adminlte.gestion.comercializacion.proyectos.partials.pre_factura', compact('pedido'))->stream();
        /* return view('adminlte.gestion.postcocecha.pedidos.partials.pre_factura', [
            'pedido' => $pedido
        ]); */
    }

    public function descargar_packing(Request $request)
    {
        $pedidos = Proyecto::whereIn('id_proyecto', [$request->id])->get();

        return PDF::loadView('adminlte.gestion.comercializacion.proyectos.partials.packing', compact('pedidos'))
            ->setPaper([0, 0, 650, 841.89], 'portrait')
            ->stream();
    }

    public function delete_orden_fija(Request $request)
    {
        DB::beginTransaction();
        try {
            $pedido = Proyecto::find($request->id_ped);

            $pedidos_futuros = Proyecto::where('fecha', '>=', $pedido->fecha)
                ->where('tipo', 'SO')
                ->where('orden_fija', $pedido->orden_fija)
                ->where('id_cliente', $pedido->id_cliente)
                ->get();
            $resumen_variedades = [];
            foreach ($pedidos_futuros as $ped) {
                foreach ($ped->cajas as $caja) {
                    foreach ($caja->detalles as $det) {
                        if (!in_array([
                            'variedad' => $det->id_variedad,
                            'longitud' => $det->longitud_ramo,
                            'fecha' => $ped->fecha
                        ], $resumen_variedades)) {
                            $resumen_variedades[] = [
                                'variedad' => $det->id_variedad,
                                'longitud' => $det->longitud_ramo,
                                'fecha' => $ped->fecha
                            ];
                        }
                    }
                }

                // GRABAR CAMBIOS de las cajas eliminadas
                $fecha = $ped->fecha;
                $proyecto = $ped;
                if (hoy() == $fecha || hoy() == opDiasFecha('-', 1, $fecha)) {
                    foreach ($proyecto->cajas as $caja) {
                        foreach ($caja->detalles as $detalle) {
                            $pedidoModificacion = new CambiosPedido();
                            $pedidoModificacion->id_cliente = $proyecto->id_cliente;
                            $pedidoModificacion->id_planta = $detalle->variedad->id_planta;
                            $pedidoModificacion->id_variedad = $detalle->id_variedad;
                            $pedidoModificacion->fecha_actual = $fecha;
                            $pedidoModificacion->fecha_anterior = $fecha;
                            $pedidoModificacion->piezas = $caja->cantidad * -1; // piezas
                            $pedidoModificacion->id_usuario = session('id_usuario');
                            $pedidoModificacion->ramos = $caja->cantidad * $detalle->ramos_x_caja * -1;
                            $pedidoModificacion->tallos = $caja->cantidad * $detalle->ramos_x_caja * $detalle->tallos_x_ramo * -1;
                            $pedidoModificacion->ramos_x_caja = $detalle->ramos_x_caja;
                            $pedidoModificacion->tallos_x_ramo = $detalle->tallos_x_ramo;
                            $pedidoModificacion->longitud_ramo = $detalle->longitud_ramo;
                            $pedidoModificacion->id_empaque_p = $detalle->id_empaque;
                            $pedidoModificacion->id_empaque_c = $caja->id_empaque;
                            $pedidoModificacion->save();

                            // GRABAR CAMBIOS de MIXTOS
                            if ($detalle->variedad->assorted == 1) {
                                $distribuciones = Mixtos::where('id_detalle_caja_proyecto', $detalle->id_detalle_caja_proyecto)
                                    ->get();
                                foreach ($distribuciones as $dist) {
                                    if ($dist->ramos > 0) {
                                        $pedidoModificacion = new CambiosPedido();
                                        $pedidoModificacion->id_cliente = $proyecto->id_cliente;
                                        $pedidoModificacion->id_planta = $dist->id_planta;
                                        $pedidoModificacion->id_variedad = $dist->id_variedad;
                                        $pedidoModificacion->fecha_actual = $fecha;
                                        $pedidoModificacion->fecha_anterior = $fecha;
                                        $pedidoModificacion->piezas = null;
                                        $pedidoModificacion->id_usuario = session('id_usuario');
                                        $pedidoModificacion->ramos = $dist->ramos * -1;
                                        $pedidoModificacion->tallos = $dist->tallos * -1;
                                        $pedidoModificacion->ramos_x_caja = $detalle->ramos_x_caja;
                                        $pedidoModificacion->tallos_x_ramo = $detalle->tallos_x_ramo;
                                        $pedidoModificacion->longitud_ramo = $detalle->longitud_ramo;
                                        $pedidoModificacion->id_empaque_p = $detalle->id_empaque;
                                        $pedidoModificacion->id_empaque_c = $caja->id_empaque;
                                        $pedidoModificacion->save();
                                    }
                                }
                            }
                        }
                    }
                }
                bitacora('proyecto', $ped->id_proyecto, 'E', 'ELIMINAR PEDIDO DE LA ORDEN FIJA #' . $pedido->orden_fija . ' con fecha ' . $ped->fecha . ' DESDE LA OPCION CANCELAR_TODA_ORDEN_FIJA');
                $ped->delete();
            }

            foreach ($resumen_variedades as $r) {
                jobCosechaEstimada::dispatch($r['variedad'], $r['longitud'], $r['fecha'])
                    ->onQueue('cosecha_estimada')
                    ->onConnection('database');
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

    public function copiar_pedido(Request $request)
    {
        $pedido = Proyecto::find($request->pedido);
        return view('adminlte.gestion.comercializacion.proyectos.forms.copiar_pedido', [
            'pedido' => $pedido,
        ]);
    }

    public function store_copiar_pedido(Request $request)
    {
        try {
            $pedOriginal = Proyecto::find($request->id_ped);
            foreach (json_decode($request->data) as $d) {
                DB::beginTransaction();
                // NUEVO PROYECTO
                $proyecto = new Proyecto();
                $proyecto->id_cliente = $pedOriginal->id_cliente;
                $proyecto->fecha = $d;
                $proyecto->tipo = 'OM';
                $proyecto->id_consignatario = $pedOriginal->id_consignatario;
                $proyecto->id_agencia_carga = $pedOriginal->id_agencia_carga;
                $proyecto->save();
                $proyecto->id_proyecto = DB::table('proyecto')
                    ->select(DB::raw('max(id_proyecto) as id'))
                    ->get()[0]->id;

                foreach ($pedOriginal->cajas as $det_ped) {
                    // NUEVA CAJA PROYECTO
                    $caja = new CajaProyecto();
                    $caja->id_proyecto = $proyecto->id_proyecto;
                    $caja->cantidad = $det_ped->cantidad;
                    $caja->id_empaque = $det_ped->id_empaque;
                    $caja->save();
                    $caja->id_caja_proyecto = DB::table('caja_proyecto')
                        ->select(DB::raw('max(id_caja_proyecto) as id'))
                        ->get()[0]->id;
                    foreach ($det_ped->detalles as $det_caj) {
                        // NUEVO DETALLE CAJA PROYECTO
                        $detalle = new DetalleCajaProyecto();
                        $detalle->id_caja_proyecto = $caja->id_caja_proyecto;
                        $detalle->id_variedad = $det_caj->id_variedad;
                        $detalle->id_empaque = $det_caj->id_empaque;
                        $detalle->ramos_x_caja = $det_caj->ramos_x_caja;
                        $detalle->tallos_x_ramo = $det_caj->tallos_x_ramo;
                        $detalle->precio = $det_caj->precio;
                        $detalle->longitud_ramo = $det_caj->longitud_ramo;
                        $detalle->save();

                        $fecha = $d;
                        /* GUARDAR EN LA TABLA PEDIDO_MODIFICACION */
                        if (hoy() == $fecha || hoy() == opDiasFecha('-', 1, $fecha)) {
                            $pedidoModificacion = new CambiosPedido();
                            $pedidoModificacion->id_cliente = $proyecto->id_cliente;
                            $pedidoModificacion->id_planta = $detalle->variedad->id_planta;
                            $pedidoModificacion->id_variedad = $detalle->id_variedad;
                            $pedidoModificacion->fecha_actual = $fecha;
                            $pedidoModificacion->fecha_anterior = $fecha;
                            $pedidoModificacion->piezas = $caja->cantidad; // piezas
                            $pedidoModificacion->id_usuario = session('id_usuario');
                            $pedidoModificacion->ramos = $caja->cantidad * $detalle->ramos_x_caja;
                            $pedidoModificacion->tallos = $caja->cantidad * $detalle->ramos_x_caja * $detalle->tallos_x_ramo;
                            $pedidoModificacion->ramos_x_caja = $detalle->ramos_x_caja;
                            $pedidoModificacion->tallos_x_ramo = $detalle->tallos_x_ramo;
                            $pedidoModificacion->longitud_ramo = $detalle->longitud_ramo;
                            $pedidoModificacion->id_empaque_p = $detalle->id_empaque;
                            $pedidoModificacion->id_empaque_c = $caja->id_empaque;
                            $pedidoModificacion->save();
                        }

                        jobCosechaEstimada::dispatch($detalle->id_variedad, $detalle->longitud_ramo, $fecha)
                            ->onQueue('cosecha_estimada')
                            ->onConnection('database');
                    }
                    foreach ($det_ped->marcaciones as $marcacion) {
                        // NUEVA CAJA PROYECTO MARCACION
                        if ($marcacion->valor != '') {
                            $caja_marcacion = new CajaProyectoMarcacion();
                            $caja_marcacion->id_caja_proyecto = $caja->id_caja_proyecto;
                            $caja_marcacion->id_dato_exportacion = $marcacion->id_dato_exportacion;
                            $caja_marcacion->valor = $marcacion->valor;
                            $caja_marcacion->save();
                        }
                    }
                }
                DB::commit();
            }

            $success = true;
            $msg = 'Se ha <b>COPIADO</b> el pedido correctamente';
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

    public function generar_packings(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach (json_decode($request->data) as $d) {
                $proyecto = Proyecto::find($d);
                if ($proyecto->packing == '') {
                    $last_packing = DB::table('proyecto')
                        ->select(DB::raw('max(packing) as id'))
                        ->get()[0]->id;
                    $proyecto->packing = $last_packing + 1;
                    $proyecto->save();
                    bitacora('proyecto', $proyecto->id_proyecto, 'U', 'GENERAR PACKING');
                }
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

    public function combinar_pedidos(Request $request)
    {
        DB::beginTransaction();
        try {
            $pedidoOriginal = Proyecto::find(json_decode($request->data)[0]);
            $tipo = $pedidoOriginal->tipo;
            foreach (json_decode($request->data) as $pos => $d) {
                if ($pos > 0) {
                    $proyecto = Proyecto::find($d);
                    if ($pedidoOriginal->fecha == $proyecto->fecha) {
                        if ($proyecto->id_cliente == $pedidoOriginal->id_cliente) {
                            foreach ($proyecto->cajas as $caja) {
                                $caja->id_proyecto = $pedidoOriginal->id_proyecto;
                                $caja->save();
                                bitacora('caja_proyecto', $caja->id_caja_proyecto, 'U', 'COMBINAR PEDIDOS');
                            }
                            for ($m = 0; $m < count($proyecto->mixtos); $m++) {
                                $mixto = $proyecto->mixtos[$m];
                                $mixto->id_proyecto = $pedidoOriginal->id_proyecto;
                                $mixto->save();
                                bitacora('mixtos', $mixto->id_mixtos, 'U', 'COMBINAR PEDIDOS');
                            }
                            $detalles_hr = DetalleHojaRuta::where('id_proyecto', $proyecto->id_proyecto)->get();
                            foreach ($detalles_hr as $det_hr) {
                                $det_hr->delete();
                            }

                            $proyecto->delete();
                        } else {
                            DB::rollBack();
                            return [
                                'success' => false,
                                'mensaje' => '<div class="alert alert-warning text-center"><h3>Debe seleccionar pedidos del mismo cliente</h3></div>',
                            ];
                        }
                    } else {
                        DB::rollBack();
                        return [
                            'success' => false,
                            'mensaje' => '<div class="alert alert-warning text-center"><h3>Debe seleccionar pedidos de la misma fecha</h3></div>',
                        ];
                    }
                }
            }
            $pedidoOriginal->tipo = $tipo;
            $pedidoOriginal->orden_fija = null;
            $pedidoOriginal->save();

            $success = true;
            $msg = 'Se han <b>COMBINADO</b> los pedidos satisfactoriamente';
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

    public function separar_pedidos(Request $request)
    {
        DB::beginTransaction();
        try {
            $pedidoOriginal = Proyecto::find($request->id_proyecto);
            if (count($pedidoOriginal->cajas) != count(json_decode($request->data))) {
                // NUEVO PROYECTO
                $proyecto = new Proyecto();
                $proyecto->id_cliente = $pedidoOriginal->id_cliente;
                $proyecto->fecha =  $pedidoOriginal->fecha;
                $proyecto->tipo = 'OM';
                $proyecto->id_consignatario = $pedidoOriginal->id_consignatario;
                $proyecto->id_agencia_carga = $pedidoOriginal->id_agencia_carga;
                $proyecto->save();
                $proyecto->id_proyecto = DB::table('proyecto')
                    ->select(DB::raw('max(id_proyecto) as id'))
                    ->get()[0]->id;
                bitacora('proyecto', $proyecto->id_proyecto, 'I', 'Nuevo pedido a partir de Separar el Pedido: ' . $request->id_proyecto);

                foreach (json_decode($request->data) as $d) {
                    $caja = CajaProyecto::find($d);
                    $caja->id_proyecto = $proyecto->id_proyecto;
                    $caja->save();
                    bitacora('caja_proyecto', $caja->id_caja_proyecto, 'U', 'SEPARAR PEDIDO desde id_proy=' . $request->id_proyecto);
                }
            } else {
                DB::rollBack();
                return [
                    'success' => false,
                    'mensaje' => '<div class="alert alert-warning text-center"><h3>No se pueden seleccionar todas las cajas del pedido</h3></div>',
                ];
            }
            $success = true;
            $msg = 'Se ha <b>SEPARADO</b> el pedido satisfactoriamente';
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
        $pedido = Proyecto::find($request->id_ped);
        $fechas = DB::table('proyecto')
            ->select('fecha')->distinct()
            ->where('tipo', 'SO')
            ->where('id_cliente', $pedido->id_cliente)
            ->where('orden_fija', $pedido->orden_fija)
            ->where('fecha', '>', $pedido->fecha)
            ->orderBy('fecha')
            ->get()->pluck('fecha')->toArray();
        return view(
            'adminlte.gestion.comercializacion.proyectos.forms.obtener_historial_orden_fija',
            [
                'pedido' => $pedido,
                'fechas' => $fechas,
            ]
        );
    }

    public function update_orden_fija(Request $request)
    {
        DB::beginTransaction();
        try {
            $pedidoOriginal = Proyecto::find($request->id_ped);
            $fechas_futuras = json_decode($request->fechas);

            //$queue = getQueueForUpdateOrdenFija($request->id_ped);
            //$pos_progreso = 1;
            foreach ($fechas_futuras as $pos_f => $f) {
                $pedidoFuturo = Proyecto::where('orden_fija', $pedidoOriginal->orden_fija)
                    ->where('id_cliente', $pedidoOriginal->id_cliente)
                    ->where('fecha', $f)
                    ->where('tipo', 'SO')
                    ->get()
                    ->first();
                $pedidoFuturo->id_consignatario = $pedidoOriginal->id_consignatario;
                $pedidoFuturo->id_agencia_carga = $pedidoOriginal->id_agencia_carga;
                $pedidoFuturo->save();
                DB::select('DELETE from caja_proyecto where id_proyecto = ' . $pedidoFuturo->id_proyecto);

                foreach ($pedidoOriginal->cajas as $pos_d => $cajaOriginal) {
                    // NUEVA CAJA PROYECTO
                    $caja = new CajaProyecto();
                    $caja->id_proyecto = $pedidoFuturo->id_proyecto;
                    $caja->cantidad = $cajaOriginal->cantidad;
                    $caja->id_empaque = $cajaOriginal->id_empaque;
                    $caja->save();
                    $caja->id_caja_proyecto = DB::table('caja_proyecto')
                        ->select(DB::raw('max(id_caja_proyecto) as id'))
                        ->get()[0]->id;
                    foreach ($cajaOriginal->detalles as $det_cajOriginal) {
                        // NUEVO DETALLE CAJA PROYECTO
                        $detalle = new DetalleCajaProyecto();
                        $detalle->id_caja_proyecto = $caja->id_caja_proyecto;
                        $detalle->id_variedad = $det_cajOriginal->id_variedad;
                        $detalle->id_empaque = $det_cajOriginal->id_empaque;
                        $detalle->ramos_x_caja = $det_cajOriginal->ramos_x_caja;
                        $detalle->tallos_x_ramo = $det_cajOriginal->tallos_x_ramo;
                        $detalle->precio = $det_cajOriginal->precio;
                        $detalle->longitud_ramo = $det_cajOriginal->longitud_ramo;
                        $detalle->save();

                        $fecha = $f;
                        /* TABLA PEDIDO_CONFIRMACION */
                        $ped_conf = PedidoConfirmacion::where('id_planta', $detalle->variedad->id_planta)
                            ->where('fecha', $fecha)
                            ->first();
                        if ($ped_conf == '') {
                            $ped_conf = new PedidoConfirmacion();
                            $ped_conf->id_planta = $detalle->variedad->id_planta;
                            $ped_conf->fecha = $fecha;
                            $ped_conf->ejecutado = 0;
                            $ped_conf->save();
                        }
                    }
                    foreach ($cajaOriginal->marcaciones as $marcacion) {
                        // NUEVA CAJA PROYECTO MARCACION
                        if ($marcacion->valor != '') {
                            $caja_marcacion = new CajaProyectoMarcacion();
                            $caja_marcacion->id_caja_proyecto = $caja->id_caja_proyecto;
                            $caja_marcacion->id_dato_exportacion = $marcacion->id_dato_exportacion;
                            $caja_marcacion->valor = $marcacion->valor;
                            $caja_marcacion->save();
                        }
                    }
                }

                jobCosechaEstimada::dispatch(0, 0, $f)
                    ->onQueue('cosecha_estimada')
                    ->onConnection('database');
            }

            DB::commit();
            $success = true;
            $msg = 'Se ha <b>ACTUALIZADO</b> la orden fija correctamente';
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

    public function descargar_packings_all(Request $request)
    {
        $pedidos = Proyecto::join('agencia_carga as ac', 'ac.id_agencia_carga', '=', 'proyecto.id_agencia_carga')
            ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'proyecto.id_cliente')
            ->select(
                'proyecto.*',
                'ac.nombre as nombre_agencia_carga',
                'dc.nombre as nombre_cliente'
            )->distinct()
            ->where('dc.estado', 1)
            ->whereIn('proyecto.id_proyecto', json_decode($request->data))
            ->orderBy('dc.nombre')
            ->get();

        return PDF::loadView('adminlte.gestion.comercializacion.proyectos.partials.packing', compact('pedidos'))
            ->setPaper([0, 0, 650, 841.89], 'portrait')
            ->stream();
    }

    public function descargar_flor_postco(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_flor_postco($spread, $request);

        $fileName = "Preparacion_de_flor.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_flor_postco($spread, $request)
    {
        $request = json_decode($request->datos);
        $proyectos = DB::table('proyecto as p')
            ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
            ->join('detalle_caja_proyecto as det', 'det.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
            ->leftJoin('consignatario as cons', 'cons.id_consignatario', '=', 'p.id_consignatario')
            ->leftJoin('agencia_carga as age', 'age.id_agencia_carga', '=', 'p.id_agencia_carga')
            ->join('detalle_cliente as det_c', 'det_c.id_cliente', '=', 'p.id_cliente')
            ->join('variedad as v', 'v.id_variedad', '=', 'det.id_variedad')
            ->select(
                'p.id_proyecto',
                'p.id_cliente',
                'p.id_consignatario',
                'p.fecha',
                'p.tipo',
                'p.packing',
                'p.orden_fija',
                'cons.nombre as nombre_consignatario',
                'age.nombre as nombre_agencia',
                'det_c.nombre as nombre_cliente',
            )->distinct()
            ->where('p.fecha', '>=', $request->desde)
            ->where('p.fecha', '<=', $request->hasta)
            ->where('det_c.estado', 1);
        if ($request->tipo != '')
            $proyectos = $proyectos->where('p.tipo', $request->tipo);
        if ($request->cliente != '')
            $proyectos = $proyectos->where('p.id_cliente', $request->cliente);
        if ($request->planta != '')
            $proyectos = $proyectos->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $proyectos = $proyectos->where('det.id_variedad', $request->variedad);
        $proyectos = $proyectos->orderBy('p.fecha')
            ->orderBy('det_c.nombre')
            ->get();
        $listado = [];
        foreach ($proyectos as $proy) {
            $cajas = DB::table('caja_proyecto as cp')
                ->join('empaque as c', 'c.id_empaque', '=', 'cp.id_empaque')
                ->join('detalle_caja_proyecto as det', 'det.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                ->join('variedad as v', 'v.id_variedad', '=', 'det.id_variedad')
                ->select(
                    'cp.id_caja_proyecto',
                    'cp.cantidad',
                    'c.nombre',
                    'c.siglas',
                )->distinct()
                ->where('cp.id_proyecto', $proy->id_proyecto);
            if ($request->planta != '')
                $cajas = $cajas->where('v.id_planta', $request->planta);
            if ($request->variedad != '')
                $cajas = $cajas->where('det.id_variedad', $request->variedad);
            $cajas = $cajas->get();
            $valores_cajas = [];
            foreach ($cajas as $caja) {
                $detalles = DB::table('detalle_caja_proyecto as det')
                    ->join('empaque as pres', 'pres.id_empaque', '=', 'det.id_empaque')
                    ->join('variedad as v', 'v.id_variedad', '=', 'det.id_variedad')
                    ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                    ->select(
                        'det.id_detalle_caja_proyecto',
                        'det.id_empaque',
                        'det.id_variedad',
                        'det.ramos_x_caja',
                        'det.tallos_x_ramo',
                        'det.precio',
                        'det.longitud_ramo',
                        'pres.nombre as nombre_presentacion',
                        'v.nombre as nombre_variedad',
                        'p.nombre as nombre_planta',
                        'v.assorted',
                    )->distinct()
                    ->where('det.id_caja_proyecto', $caja->id_caja_proyecto);
                if ($request->planta != '')
                    $detalles = $detalles->where('v.id_planta', $request->planta);
                if ($request->variedad != '')
                    $detalles = $detalles->where('det.id_variedad', $request->variedad);
                $detalles = $detalles->get();

                $marcaciones = DB::table('caja_proyecto_marcacion as cm')
                    ->join('dato_exportacion as m', 'm.id_dato_exportacion', '=', 'cm.id_dato_exportacion')
                    ->select(
                        'cm.id_dato_exportacion',
                        'm.nombre',
                        'cm.valor',
                    )->distinct()
                    ->where('cm.id_caja_proyecto', $caja->id_caja_proyecto)
                    ->get();

                $valores_cajas[] = [
                    'caja' => $caja,
                    'detalles' => $detalles,
                    'marcaciones' => $marcaciones,
                ];
            }
            $listado[] = [
                'proyecto' => $proy,
                'valores_cajas' => $valores_cajas,
            ];
        }

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('PEDIDOS ' . $request->desde . ' ' . $request->hasta);

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'FECHA');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'ANO');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'DIA');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'DIA TEXTO');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'CLIENTE');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'CONSIGNATARIO');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'PO');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'UPC');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'MARCACION');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TALLOS X RAMO');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'RAMOS X CAJA');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'FLOR');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'COLOR');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'MEDIDA');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'CAJAS');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TIPO DE CAJA');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'PRECIO X BUNCHE');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'PRESENTACION');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTAL TALLOS');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTAL RAMOS');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'MES');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'SEMANA');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'CARGUERA');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TIPO DE PEDIDO');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTAL FACTURA');
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $pos_p => $pedido) {
            $fecha_formateada = explode('-', $pedido['proyecto']->fecha);
            $getMonto = Proyecto::find($pedido['proyecto']->id_proyecto)->getMonto();
            foreach ($pedido['valores_cajas'] as $pos_c => $caja) {
                foreach ($caja['detalles'] as $pos_d => $detalle) {
                    $mixtos = [];
                    if ($detalle->assorted == 1) {
                        $mixtos = Mixtos::where('id_detalle_caja_proyecto', $detalle->id_detalle_caja_proyecto)
                            ->where('ramos', '>', 0)
                            ->get();
                    }

                    if ($detalle->assorted == 0 || ($detalle->assorted == 1 && count($mixtos) == 0)) {
                        $row++;
                        $col = 0;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $fecha_formateada[2] . '/' . $fecha_formateada[1] . '/' . $fecha_formateada[0]);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, explode('-', $pedido['proyecto']->fecha)[0]);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, explode('-', $pedido['proyecto']->fecha)[2]);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, strtoupper(date('l', strtotime($pedido['proyecto']->fecha))));
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pedido['proyecto']->nombre_cliente);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pedido['proyecto']->nombre_consignatario);
                        $po = '';
                        $upc = '';
                        $marcacion = '';
                        foreach ($caja['marcaciones'] as $marc) {
                            if ($marc->nombre == 'PO')
                                $po = $marc->valor;
                            if ($marc->nombre == 'UPC')
                                $upc = $marc->valor;
                            if ($marc->nombre == 'MARCACION')
                                $marcacion = $marc->valor;
                        }
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $po);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $upc);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $marcacion);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $detalle->tallos_x_ramo);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $detalle->ramos_x_caja);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $detalle->nombre_planta);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $detalle->nombre_variedad);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $detalle->longitud_ramo . ' cm');
                        $col++;
                        if ($pos_d == 0)
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $caja['caja']->cantidad);
                        $col++;
                        if ($pos_d == 0)
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, explode('|', $caja['caja']->nombre)[0]);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, '$' . $detalle->precio);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $detalle->nombre_presentacion);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $caja['caja']->cantidad * $detalle->ramos_x_caja * $detalle->tallos_x_ramo);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $caja['caja']->cantidad * $detalle->ramos_x_caja);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, strtoupper(date('F', strtotime($pedido['proyecto']->fecha))));
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, substr(getSemanaByDate($pedido['proyecto']->fecha)->codigo, 2));
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pedido['proyecto']->nombre_agencia);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pedido['proyecto']->tipo == 'SO' ? 'STANDING ORDER' : 'OPEN MARKET');
                        if ($pos_d == 0 && $pos_c == 0) {
                            $col++;
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $getMonto);
                        }
                    } else {
                        // distribuciones hechas
                        foreach ($mixtos as $pos_mix => $m) {
                            $row++;
                            $col = 0;
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $fecha_formateada[2] . '/' . $fecha_formateada[1] . '/' . $fecha_formateada[0]);
                            $col++;
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, explode('-', $pedido['proyecto']->fecha)[0]);
                            $col++;
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, explode('-', $pedido['proyecto']->fecha)[2]);
                            $col++;
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, strtoupper(date('l', strtotime($pedido['proyecto']->fecha))));
                            $col++;
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pedido['proyecto']->nombre_cliente);
                            $col++;
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pedido['proyecto']->nombre_consignatario);
                            $po = '';
                            $upc = '';
                            $marcacion = '';
                            foreach ($caja['marcaciones'] as $marc) {
                                if ($marc->nombre == 'PO')
                                    $po = $marc->valor;
                                if ($marc->nombre == 'UPC')
                                    $upc = $marc->valor;
                                if ($marc->nombre == 'MARCACION')
                                    $marcacion = $marc->valor;
                            }
                            $col++;
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $po);
                            $col++;
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $upc);
                            $col++;
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $marcacion);
                            $col++;
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $detalle->tallos_x_ramo);
                            $col++;
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $m->ramos);
                            $col++;
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $detalle->nombre_planta);
                            $col++;
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $m->variedad->nombre);
                            $col++;
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $detalle->longitud_ramo . ' cm');
                            $col++;
                            if ($pos_d == 0 && $pos_mix == 0)
                                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $caja['caja']->cantidad);
                            $col++;
                            if ($pos_d == 0 && $pos_mix == 0)
                                setValueToCeldaExcel($sheet, $columnas[$col] . $row, explode('|', $caja['caja']->nombre)[0]);
                            $col++;
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, '$' . $detalle->precio);
                            $col++;
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $detalle->nombre_presentacion);
                            $col++;
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $caja['caja']->cantidad * $m->ramos * $detalle->tallos_x_ramo);
                            $col++;
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $caja['caja']->cantidad * $m->ramos);
                            $col++;
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, strtoupper(date('F', strtotime($pedido['proyecto']->fecha))));
                            $col++;
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, substr(getSemanaByDate($pedido['proyecto']->fecha)->codigo, 2));
                            $col++;
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pedido['proyecto']->nombre_agencia);
                            $col++;
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pedido['proyecto']->tipo == 'SO' ? 'STANDING ORDER' : 'OPEN MARKET');
                            if ($pos_d == 0 && $pos_c == 0) {
                                $col++;
                                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $getMonto);
                            }
                        }
                    }
                }
            }
        }

        setBorderToCeldaExcel($sheet, $columnas[0] . 1 . ':' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function descargar_jire(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_jire($spread, $request);

        $fileName = "JIRE.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_jire($spread, $request)
    {
        $proyectos = Proyecto::whereIn('id_proyecto', json_decode($request->data))->get();

        $columnas = getColumnasExcel();
        // hoja de INTERNACIONALES
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('INTERNACIONALES');

        $row = 0;
        foreach ($proyectos as $proyecto) {
            $consignatario = $proyecto->consignatario;
            if ($proyecto->codigo_pais != '')
                $codigo_pais =  $proyecto->codigo_pais;
            elseif ($consignatario != '')
                $codigo_pais = $consignatario->codigo_pais;
            else
                $codigo_pais = '';
            if ($codigo_pais != 'EC') { // INTERNACIONALES
                $cliente = $proyecto->cliente->detalle();
                $pais = $cliente->pais;
                $nombre_pais = $pais != '' ? $pais->nombre : '';
                $agencia_carga = $proyecto->agencia_carga;
                $aerolinea = $proyecto->aerolinea;
                $cajaFin = 0;
                $contador = 1;
                foreach ($proyecto->cajas as $caja) {
                    $empaque = $caja->empaque;
                    $marcaciones = $caja->marcaciones;
                    $upc = [];
                    $marcacion = [];
                    $po = [];
                    foreach ($marcaciones as $m) {
                        if ($m->dato_exportacion->nombre == 'UPC') {
                            $upc[] = $m->valor;
                        }
                        if ($m->dato_exportacion->nombre == 'MARCACION') {
                            $marcacion[] = $m->valor;
                        }
                        if ($m->dato_exportacion->nombre == 'PO') {
                            $po[] = $m->valor;
                        }
                    }
                    $cajaInicio = $cajaFin + 1;
                    $cajaFin += $caja->cantidad;
                    foreach ($caja->detalles as $pos_d => $detalle) {
                        $variedad = $detalle->variedad;
                        $planta = $variedad->planta;
                        $l50 = 0;
                        $l60 = 0;
                        $l70 = 0;
                        $l80 = 0;
                        $l90 = 0;
                        if ($detalle->longitud_ramo == 50) $l50 = $detalle->ramos_x_caja;
                        if ($detalle->longitud_ramo == 60) $l60 = $detalle->ramos_x_caja;
                        if ($detalle->longitud_ramo == 70 || $detalle->longitud_ramo == 0) $l70 = $detalle->ramos_x_caja;
                        if ($detalle->longitud_ramo == 80) $l80 = $detalle->ramos_x_caja;
                        if ($detalle->longitud_ramo == 90) $l90 = $detalle->ramos_x_caja;

                        $tipo_caja = '';

                        switch (explode('|', $empaque->nombre)[1]) {
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

                        $row++;
                        setValueToCeldaExcel($sheet, 'A' . $row, $proyecto->packing);   // packing
                        setValueToCeldaExcel($sheet, 'B' . $row, $cliente->ruc); // ruc del cliente
                        setValueToCeldaExcel($sheet, 'C' . $row, $agencia_carga->identificacion);   // codigo de agencia
                        setValueToCeldaExcel($sheet, 'D' . $row, $cliente->telefono); // telefono cliente
                        setValueToCeldaExcel($sheet, 'E' . $row, $consignatario != '' ? $consignatario->identificacion : '');   // identificacion consignatario
                        setValueToCeldaExcel($sheet, 'F' . $row, $cliente->direccion);   // direccion cliente
                        setValueToCeldaExcel($sheet, 'G' . $row, $consignatario != '' ? $consignatario->nombre : '');   // nombre consignatario
                        setValueToCeldaExcel($sheet, 'H' . $row, '404-216-0851');   // siempre
                        setValueToCeldaExcel($sheet, 'I' . $row, $cliente->provincia . ', ' . $nombre_pais);   // ciudad cliente
                        setValueToCeldaExcel($sheet, 'J' . $row, $proyecto->fecha);   // fecha de pedido
                        setValueToCeldaExcel($sheet, 'K' . $row, $proyecto->guia_madre);   // guia madre
                        setValueToCeldaExcel($sheet, 'L' . $row, $proyecto->guia_hija);   // guia hija
                        setValueToCeldaExcel($sheet, 'M' . $row, $aerolinea != '' ? $aerolinea->codigo : '');   // codigo aerolinea
                        setValueToCeldaExcel($sheet, 'N' . $row, $proyecto->dae);   // dae
                        setValueToCeldaExcel($sheet, 'O' . $row, $proyecto->getTotalFulls());   // fulls
                        setValueToCeldaExcel($sheet, 'P' . $row, $proyecto->getTotalPiezas());   // piezas
                        setValueToCeldaExcel($sheet, 'Q' . $row, 'P');   // siempre p
                        setValueToCeldaExcel($sheet, 'R' . $row, 'E');   // siempre e de extranjeros
                        setValueToCeldaExcel($sheet, 'S' . $row, implode(', ', $upc));   // upc
                        setValueToCeldaExcel($sheet, 'T' . $row, implode(', ', $marcacion));   // marcacion
                        setValueToCeldaExcel($sheet, 'U' . $row, '');   // vacio
                        setValueToCeldaExcel($sheet, 'V' . $row, implode(', ', $po));   // po

                        ///////////// DETALLE PACKING ///////////////
                        setValueToCeldaExcel($sheet, 'W' . $row, $cajaInicio);   // caja inicio
                        setValueToCeldaExcel($sheet, 'X' . $row, $cajaFin);   // caja inicio
                        setValueToCeldaExcel($sheet, 'Y' . $row, $proyecto->packing);   // packing
                        setValueToCeldaExcel($sheet, 'Z' . $row, '');   // vacio
                        setValueToCeldaExcel($sheet, 'AA' . $row, $planta->siglas);   // siglas de la planta
                        setValueToCeldaExcel($sheet, 'AB' . $row, $variedad->siglas);   // siglas de la variedad
                        setValueToCeldaExcel($sheet, 'AC' . $row, $detalle->tallos_x_ramo);   // tallos x ramo
                        setValueToCeldaExcel($sheet, 'AD' . $row, $l50 * $caja->cantidad);   // ramos de 50cm
                        setValueToCeldaExcel($sheet, 'AE' . $row, $l60 * $caja->cantidad);   // ramos de 60cm
                        setValueToCeldaExcel($sheet, 'AF' . $row, $l70 * $caja->cantidad);   // ramos de 70cm
                        setValueToCeldaExcel($sheet, 'AG' . $row, $l80 * $caja->cantidad);   // ramos de 80cm
                        setValueToCeldaExcel($sheet, 'AH' . $row, $l90 * $caja->cantidad);   // ramos de 90cm
                        setValueToCeldaExcel($sheet, 'AI' . $row, ($l50 + $l60 + $l70 + $l80 + $l90) * $caja->cantidad);   // total ramos
                        setValueToCeldaExcel($sheet, 'AJ' . $row, 5);   // siempre 5
                        setValueToCeldaExcel($sheet, 'AK' . $row, $planta->nombre . ' ' . $variedad->nombre . ' / ' . $detalle->tallos_x_ramo . ' STEMS');   // descripcion
                        setValueToCeldaExcel($sheet, 'AL' . $row, $tipo_caja == 3 ? 0 : $contador);   // tipo de caja
                        setValueToCeldaExcel($sheet, 'AM' . $row, $proyecto->tipo);   // tipo de pedido
                        setValueToCeldaExcel($sheet, 'AN' . $row, $detalle->precio);   // precio x unitario
                        setValueToCeldaExcel($sheet, 'AO' . $row, $pos_d == 0 ? $tipo_caja : '');   // cantidad de cajas

                        if ($tipo_caja == 3)
                            $contador = 0;
                    }
                    $contador++;
                }
            }
        }
        $col = 42;
        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);

        // hoja de nacionales
        $sheet = $spread->createSheet();
        $sheet->setTitle('NACIONALES');

        $row = 0;
        foreach ($proyectos as $proyecto) {
            $consignatario = $proyecto->consignatario;
            if ($proyecto->codigo_pais != '')
                $codigo_pais =  $proyecto->codigo_pais;
            elseif ($consignatario != '')
                $codigo_pais = $consignatario->codigo_pais;
            else
                $codigo_pais = '';
            if ($codigo_pais == 'EC') { // INTERNACIONALES
                $cliente = $proyecto->cliente;
                $pais = $cliente->pais;
                $nombre_pais = $pais != '' ? $pais->nombre : '';
                $agencia_carga = $proyecto->agencia_carga;
                $aerolinea = $proyecto->aerolinea;
                $cajaFin = 0;
                $contador = 1;
                foreach ($proyecto->cajas as $caja) {
                    $empaque = $caja->empaque;
                    $marcaciones = $caja->marcaciones;
                    $upc = [];
                    $marcacion = [];
                    $po = [];
                    foreach ($marcaciones as $m) {
                        if ($m->dato_exportacion->nombre == 'UPC') {
                            $upc[] = $m->valor;
                        }
                        if ($m->dato_exportacion->nombre == 'MARCACION') {
                            $marcacion[] = $m->valor;
                        }
                        if ($m->dato_exportacion->nombre == 'PO') {
                            $po[] = $m->valor;
                        }
                    }
                    $cajaInicio = $cajaFin + 1;
                    $cajaFin += $caja->cantidad;
                    foreach ($caja->detalles as $pos_d => $detalle) {
                        $variedad = $detalle->variedad;
                        $planta = $variedad->planta;
                        $l50 = 0;
                        $l60 = 0;
                        $l70 = 0;
                        $l80 = 0;
                        $l90 = 0;
                        if ($detalle->longitud_ramo == 50) $l50 = $detalle->ramos_x_caja;
                        if ($detalle->longitud_ramo == 60) $l60 = $detalle->ramos_x_caja;
                        if ($detalle->longitud_ramo == 70 || $detalle->longitud_ramo == 0) $l70 = $detalle->ramos_x_caja;
                        if ($detalle->longitud_ramo == 80) $l80 = $detalle->ramos_x_caja;
                        if ($detalle->longitud_ramo == 90) $l90 = $detalle->ramos_x_caja;

                        $tipo_caja = '';

                        switch (explode('|', $empaque->nombre)[1]) {
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

                        $row++;
                        setValueToCeldaExcel($sheet, 'A' . $row, $proyecto->packing);   // packing
                        setValueToCeldaExcel($sheet, 'B' . $row, $cliente->detalle()->ruc); // ruc del cliente
                        setValueToCeldaExcel($sheet, 'C' . $row, $agencia_carga->identificacion);   // codigo de agencia
                        setValueToCeldaExcel($sheet, 'D' . $row, $cliente->telefono); // telefono cliente
                        setValueToCeldaExcel($sheet, 'E' . $row, $consignatario != '' ? $consignatario->identificacion : '');   // identificacion consignatario
                        setValueToCeldaExcel($sheet, 'F' . $row, $cliente->direccion);   // direccion cliente
                        setValueToCeldaExcel($sheet, 'G' . $row, $consignatario != '' ? $consignatario->nombre : '');   // nombre consignatario
                        setValueToCeldaExcel($sheet, 'H' . $row, '404-216-0851');   // siempre
                        setValueToCeldaExcel($sheet, 'I' . $row, $cliente->provincia . ', ' . $nombre_pais);   // ciudad cliente
                        setValueToCeldaExcel($sheet, 'J' . $row, $proyecto->fecha);   // fecha de pedido
                        setValueToCeldaExcel($sheet, 'K' . $row, $proyecto->guia_madre);   // guia madre
                        setValueToCeldaExcel($sheet, 'L' . $row, $proyecto->guia_hija);   // guia hija
                        setValueToCeldaExcel($sheet, 'M' . $row, $aerolinea != '' ? $aerolinea->codigo : '');   // codigo aerolinea
                        setValueToCeldaExcel($sheet, 'N' . $row, $proyecto->dae);   // dae
                        setValueToCeldaExcel($sheet, 'O' . $row, $proyecto->getTotalFulls());   // fulls
                        setValueToCeldaExcel($sheet, 'P' . $row, $proyecto->getTotalPiezas());   // piezas
                        setValueToCeldaExcel($sheet, 'Q' . $row, 'P');   // siempre p
                        setValueToCeldaExcel($sheet, 'R' . $row, 'N');   // siempre n de nacianles
                        setValueToCeldaExcel($sheet, 'S' . $row, implode(', ', $upc));   // upc
                        setValueToCeldaExcel($sheet, 'T' . $row, implode(', ', $marcacion));   // marcacion
                        setValueToCeldaExcel($sheet, 'U' . $row, '');   // vacio
                        setValueToCeldaExcel($sheet, 'V' . $row, implode(', ', $po));   // po

                        ///////////// DETALLE PACKING ///////////////
                        setValueToCeldaExcel($sheet, 'W' . $row, $cajaInicio);   // caja inicio
                        setValueToCeldaExcel($sheet, 'X' . $row, $cajaFin);   // caja inicio
                        setValueToCeldaExcel($sheet, 'Y' . $row, $proyecto->packing);   // packing
                        setValueToCeldaExcel($sheet, 'Z' . $row, '');   // vacio
                        setValueToCeldaExcel($sheet, 'AA' . $row, $planta->siglas);   // siglas de la planta
                        setValueToCeldaExcel($sheet, 'AB' . $row, $variedad->siglas);   // siglas de la variedad
                        setValueToCeldaExcel($sheet, 'AC' . $row, $detalle->tallos_x_ramo);   // tallos x ramo
                        setValueToCeldaExcel($sheet, 'AD' . $row, $l50 * $caja->cantidad);   // ramos de 50cm
                        setValueToCeldaExcel($sheet, 'AE' . $row, $l60 * $caja->cantidad);   // ramos de 60cm
                        setValueToCeldaExcel($sheet, 'AF' . $row, $l70 * $caja->cantidad);   // ramos de 70cm
                        setValueToCeldaExcel($sheet, 'AG' . $row, $l80 * $caja->cantidad);   // ramos de 80cm
                        setValueToCeldaExcel($sheet, 'AH' . $row, $l90 * $caja->cantidad);   // ramos de 90cm
                        setValueToCeldaExcel($sheet, 'AI' . $row, ($l50 + $l60 + $l70 + $l80 + $l90) * $caja->cantidad);   // total ramos
                        setValueToCeldaExcel($sheet, 'AJ' . $row, 5);   // siempre 5
                        setValueToCeldaExcel($sheet, 'AK' . $row, $planta->nombre . ' ' . $variedad->nombre . ' / ' . $detalle->tallos_x_ramo . ' STEMS');   // descripcion
                        setValueToCeldaExcel($sheet, 'AL' . $row, $tipo_caja == 3 ? 0 : $contador);   // tipo de caja
                        setValueToCeldaExcel($sheet, 'AM' . $row, $proyecto->tipo);   // tipo de pedido
                        setValueToCeldaExcel($sheet, 'AN' . $row, $detalle->precio);   // precio x unitario
                        setValueToCeldaExcel($sheet, 'AO' . $row, $pos_d == 0 ? $tipo_caja : '');   // cantidad de cajas

                        if ($tipo_caja == 3)
                            $contador = 0;
                    }
                    $contador++;
                }
            }
        }
        $col = 42;
        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function buscar_cambios_diarios(Request $request)
    {
        $fechas = [
            hoy(),
            opDiasFecha('+', 1, hoy())
        ];
        $listado = CambiosPedido::where('fecha_actual', $fechas)
            ->orderBy('fecha_registro', 'desc')
            ->get();
        return view(
            'adminlte.gestion.comercializacion.proyectos.partials._buscar_cambios_diarios',
            [
                'listado' => $listado,
            ]
        );
    }
}
