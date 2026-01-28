<?php

namespace yura\Http\Controllers\Comercializacion;

use DB;
use Barryvdh\DomPDF\Facade as PDF;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Http\Controllers\Controller;
use yura\Modelos\AgenciaCarga;
use yura\Modelos\DatosExportacion;
use yura\Modelos\DetalleHojaRuta;
use yura\Modelos\HojaRuta;
use yura\Modelos\Mixtos;
use yura\Modelos\Proyecto;
use yura\Modelos\SelloHojaRuta;
use yura\Modelos\Submenu;
use yura\Modelos\Transportista;
use yura\Modelos\Usuario;

class HojaRutaController extends Controller
{
    public function inicio(Request $request)
    {
        $agencias = AgenciaCarga::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $clientes = DB::table('cliente as c')
            ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'c.id_cliente')
            ->select('dc.nombre', 'dc.id_cliente')
            ->where('c.estado', 1)
            ->where('dc.estado', 1)
            ->orderBy('dc.nombre')
            ->get();

        return view('adminlte.gestion.comercializacion.hoja_ruta.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'agencias' => $agencias,
            'clientes' => $clientes,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $listado = Proyecto::join('agencia_carga as ac', 'ac.id_agencia_carga', '=', 'proyecto.id_agencia_carga')
            ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'proyecto.id_cliente')
            ->select(
                'proyecto.*',
                'ac.nombre as nombre_agencia_carga',
                'dc.nombre as nombre_cliente'
            )->distinct()
            ->where('proyecto.fecha', '>=', $request->desde)
            ->where('proyecto.fecha', '<=', $request->hasta)
            ->where('dc.estado', 1);
        if ($request->cliente != 'T')
            $listado = $listado->where('proyecto.id_cliente', $request->cliente);
        if ($request->agencia != 'T')
            $listado = $listado->where('proyecto.id_agencia_carga', $request->agencia);
        $listado = $listado->orderBy('ac.orden')
            ->orderBy('dc.nombre')
            ->get();

        $despachos = HojaRuta::where('fecha', '>=', $request->desde)
            ->where('fecha', '<=', $request->hasta)
            ->get();

        return view('adminlte.gestion.comercializacion.hoja_ruta.partials.listado', [
            'listado' => $listado,
            'despachos' => $despachos,
            'fecha' => $request->desde,
        ]);
    }

    public function crear_despacho(Request $request)
    {
        $listado = Proyecto::join('agencia_carga as ac', 'ac.id_agencia_carga', '=', 'proyecto.id_agencia_carga')
            ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'proyecto.id_cliente')
            ->where('dc.estado', 1)
            ->whereIn('proyecto.id_proyecto', json_decode($request->data))
            ->orderBy('ac.orden')
            ->orderBy('dc.nombre')
            ->get();
        $transportistas = Transportista::where('estado', 1)->orderBy('nombre_empresa')->get();

        return view('adminlte.gestion.comercializacion.hoja_ruta.forms.crear_despachos', [
            'listado' => $listado,
            'transportistas' => $transportistas,
            'fecha' => $request->fecha,
            'usuario' => Usuario::find(session('id_usuario')),
        ]);
    }

    public function seleccionar_transportista(Request $request)
    {
        $options_camiones = '';
        $options_conductores = '';
        $transportista = Transportista::find($request->id);
        foreach ($transportista->camiones as $camion)
            $options_camiones .= '<option value="' . $camion->id_camion . '" data-placa="' . $camion->placa . '">' . $camion->modelo . ' (' . $camion->placa . ')</option>';
        foreach ($transportista->conductores as $conductor)
            $options_conductores .= '<option value="' . $conductor->id_conductor . '">' . $conductor->nombre . '</option>';
        return [
            'options_camiones' => $options_camiones,
            'options_conductores' => $options_conductores,
        ];
    }

    public function seleccionar_fecha(Request $request)
    {
        return [
            'codigo' => getSemanaByDate($request->fecha)->codigo,
        ];
    }

    public function store_despacho(Request $request)
    {
        try {
            DB::beginTransaction();
            $usuario = Usuario::where('nombre_completo', mb_strtoupper($request->responsable))->first();
            if ($usuario != null && $usuario->cedula != '') {
                $hoja_ruta = new HojaRuta();
                $hoja_ruta->id_transportista = $request->transportista;
                $hoja_ruta->id_camion = $request->camion;
                $hoja_ruta->id_conductor = $request->conductor;
                $hoja_ruta->placa = $request->placa;
                $hoja_ruta->responsable = mb_strtoupper($request->responsable);
                $hoja_ruta->identificacion_responsable = $usuario->cedula;
                $hoja_ruta->fecha = $request->fecha;
                $hoja_ruta->save();
                $id = $hoja_ruta->id_hoja_ruta;
                bitacora('hoja_ruta', $hoja_ruta->id_hoja_ruta, 'I', 'Nuevo DESPACHO creado');

                foreach (json_decode($request->detalles) as $data) {
                    $detalle = new DetalleHojaRuta();
                    $detalle->id_hoja_ruta  = $hoja_ruta->id_hoja_ruta;
                    $detalle->id_proyecto = $data->id_proyecto;
                    $detalle->orden = $data->orden;
                    $detalle->save();
                    bitacora('detalle_hoja_ruta', $detalle->id_detalle_hoja_ruta, 'I', 'Nuevo DETALLE DE DESPACHO creado');
                }

                foreach (json_decode($request->sellos) as $data) {
                    $sello = new SelloHojaRuta();
                    $sello->id_hoja_ruta  = $hoja_ruta->id_hoja_ruta;
                    $sello->id_agencia_carga = $data->id_agencia;
                    $sello->sello = $data->sello;
                    $sello->save();
                    bitacora('sello_hoja_ruta', $sello->id_sello_hoja_ruta, 'I', 'Nuevo Sello DE DESPACHO creado');
                }

                DB::commit();
                $success = true;
                $msg = 'Se ha <strong>GRABADO</strong> el despacho correctamente';
            } else {
                $id = '';
                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<p> El responsable del despacho no se encuentra registrado en el sistema o no tiene cédula asignada. '
                    . 'Por favor verifique la información e intente nuevamente. </p>'
                    . '</div>';
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $id = '';
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
            'id_despacho' => $id,
        ];
    }

    public function agregar_a_despacho(Request $request)
    {
        $hoja_ruta = HojaRuta::find($request->id_hoja_ruta);
        $proyecto = Proyecto::find($request->id_proy);
        $agencia = AgenciaCarga::find($proyecto->id_agencia_carga);
        $mis_agencias = DB::table('sello_hoja_ruta')
            ->where('id_hoja_ruta', $hoja_ruta->id_hoja_ruta)
            ->get()->pluck('id_agencia_carga')->toArray();
        return view('adminlte.gestion.comercializacion.hoja_ruta.forms.agregar_a_despacho', [
            'hoja_ruta' => $hoja_ruta,
            'proyecto' => $proyecto,
            'agencia' => $agencia,
            'mis_agencias' => $mis_agencias,
        ]);
    }

    public function agregar_a_despacho_confirmar(Request $request)
    {
        try {
            DB::beginTransaction();
            $detalle = new DetalleHojaRuta();
            $detalle->id_hoja_ruta  = $request->id_hoja_ruta;
            $detalle->id_proyecto = $request->id_proy;
            $detalle->orden = DetalleHojaRuta::where('id_hoja_ruta', $request->id_hoja_ruta)->count() + 1;
            $detalle->save();
            bitacora('detalle_hoja_ruta', $detalle->id_detalle_hoja_ruta, 'I', 'Nuevo DETALLE DE DESPACHO agregado');

            $sello = SelloHojaRuta::where('id_hoja_ruta', $request->id_hoja_ruta)
                ->where('id_agencia_carga', $request->id_agencia)
                ->first();
            if ($sello == null) {
                $sello = new SelloHojaRuta();
                $sello->id_hoja_ruta  = $request->id_hoja_ruta;
                $sello->id_agencia_carga = $request->id_agencia;
                $sello->sello = $request->sello;
                $sello->save();
                bitacora('sello_hoja_ruta', $sello->id_sello_hoja_ruta, 'I', 'Nuevo Sello DE DESPACHO agregado');
            }

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>AGREGADO</strong> el proyecto al despacho correctamente';
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

    public function cambiar_a_despacho(Request $request)
    {
        $hoja_ruta = HojaRuta::find($request->id_hoja_ruta);
        $proyecto = Proyecto::find($request->id_proy);
        $agencia = AgenciaCarga::find($proyecto->id_agencia_carga);
        $mis_agencias = DB::table('sello_hoja_ruta')
            ->where('id_hoja_ruta', $hoja_ruta->id_hoja_ruta)
            ->get()->pluck('id_agencia_carga')->toArray();
        return view('adminlte.gestion.comercializacion.hoja_ruta.forms.cambiar_a_despacho', [
            'hoja_ruta' => $hoja_ruta,
            'proyecto' => $proyecto,
            'agencia' => $agencia,
            'mis_agencias' => $mis_agencias,
        ]);
    }

    public function cambiar_a_despacho_confirmar(Request $request)
    {
        try {
            DB::beginTransaction();
            //QUITAR del despacho anterior
            DetalleHojaRuta::where('id_proyecto', $request->id_proy)
                ->where('id_hoja_ruta', '!=', $request->id_hoja_ruta)
                ->delete();

            //AGREGAR al nuevo despacho
            $detalle = new DetalleHojaRuta();
            $detalle->id_hoja_ruta  = $request->id_hoja_ruta;
            $detalle->id_proyecto = $request->id_proy;
            $detalle->orden = DetalleHojaRuta::where('id_hoja_ruta', $request->id_hoja_ruta)->count() + 1;
            $detalle->save();
            bitacora('detalle_hoja_ruta', $detalle->id_detalle_hoja_ruta, 'I', 'Nuevo DETALLE DE DESPACHO cambiado');

            $sello = SelloHojaRuta::where('id_hoja_ruta', $request->id_hoja_ruta)
                ->where('id_agencia_carga', $request->id_agencia)
                ->first();
            if ($sello == null) {
                $sello = new SelloHojaRuta();
                $sello->id_hoja_ruta  = $request->id_hoja_ruta;
                $sello->id_agencia_carga = $request->id_agencia;
                $sello->sello = $request->sello;
                $sello->save();
                bitacora('sello_hoja_ruta', $sello->id_sello_hoja_ruta, 'I', 'Nuevo Sello DE DESPACHO cambiado');
            }

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>CAMBIADO</strong> el proyecto al despacho correctamente';
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

    public function ver_despachos(Request $request)
    {
        $despachos = HojaRuta::where('fecha', '>=', $request->desde)
            ->where('fecha', '<=', $request->hasta)
            ->get();
        return view('adminlte.gestion.comercializacion.hoja_ruta.partials.ver_despachos', [
            'despachos' => $despachos,
        ]);
    }

    public function ver_hoja_ruta(Request $request)
    {
        $hoja_ruta = HojaRuta::find($request->id_hoja_ruta);
        $transportistas = Transportista::where('estado', 1)->orderBy('nombre_empresa')->get();
        return view('adminlte.gestion.comercializacion.hoja_ruta.forms.ver_hoja_ruta', [
            'hoja_ruta' => $hoja_ruta,
            'transportistas' => $transportistas,
        ]);
    }

    public function update_despacho(Request $request)
    {
        try {
            DB::beginTransaction();
            $usuario = Usuario::where('nombre_completo', mb_strtoupper($request->responsable))->first();
            if ($usuario != null && $usuario->cedula != '') {
                $hoja_ruta = HojaRuta::find($request->id_hoja_ruta);
                $hoja_ruta->id_transportista = $request->transportista;
                $hoja_ruta->id_camion = $request->camion;
                $hoja_ruta->id_conductor = $request->conductor;
                $hoja_ruta->placa = $request->placa;
                $hoja_ruta->responsable = mb_strtoupper($request->responsable);
                $hoja_ruta->identificacion_responsable = $usuario->cedula;
                $hoja_ruta->fecha = $request->fecha;
                $hoja_ruta->save();
                $id = $hoja_ruta->id_hoja_ruta;
                bitacora('hoja_ruta', $hoja_ruta->id_hoja_ruta, 'U', 'DESPACHO editado');

                //Eliminar detalles y sellos anteriores
                DetalleHojaRuta::where('id_hoja_ruta', $hoja_ruta->id_hoja_ruta)->delete();
                SelloHojaRuta::where('id_hoja_ruta', $hoja_ruta->id_hoja_ruta)->delete();

                foreach (json_decode($request->detalles) as $data) {
                    $detalle = new DetalleHojaRuta();
                    $detalle->id_hoja_ruta  = $hoja_ruta->id_hoja_ruta;
                    $detalle->id_proyecto = $data->id_proyecto;
                    $detalle->orden = $data->orden;
                    $detalle->save();
                    bitacora('detalle_hoja_ruta', $detalle->id_detalle_hoja_ruta, 'I', 'Nuevo DETALLE DE DESPACHO creado');
                }

                foreach (json_decode($request->sellos) as $data) {
                    $sello = new SelloHojaRuta();
                    $sello->id_hoja_ruta  = $hoja_ruta->id_hoja_ruta;
                    $sello->id_agencia_carga = $data->id_agencia;
                    $sello->sello = $data->sello;
                    $sello->save();
                    bitacora('sello_hoja_ruta', $sello->id_sello_hoja_ruta, 'I', 'Nuevo Sello DE DESPACHO creado');
                }

                DB::commit();
                $success = true;
                $msg = 'Se ha <strong>GRABADO</strong> el despacho correctamente';
            } else {
                $id = '';
                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<p> El responsable del despacho no se encuentra registrado en el sistema o no tiene cédula asignada. '
                    . 'Por favor verifique la información e intente nuevamente. </p>'
                    . '</div>';
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $id = '';
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
            'id_despacho' => $id,
        ];
    }

    public function exportar_despacho(Request $request)
    {
        $hoja_ruta = HojaRuta::find($request->id);
        $data = [
            'hoja_ruta' => $hoja_ruta,
        ];

        PDF::loadView('adminlte.gestion.comercializacion.hoja_ruta.partials.pdf_proyectos', compact('data'))
            ->setPaper('a4', 'landscape')->save(public_path('pdf/pdf1.pdf'));

        PDF::loadView('adminlte.gestion.comercializacion.hoja_ruta.partials.pdf_firmas', compact('data'))
            ->setPaper('a4', 'portrait')->save(public_path('pdf/pdf2.pdf'));

        /*if ($hoja_ruta->tieneSellos())
            PDF::loadView('adminlte.gestion.comercializacion.hoja_ruta.partials.pdf_sellos', compact('data'))
                ->setPaper('a4', 'portrait')->save(public_path('pdf/pdf3.pdf'));*/

        $oMerger = PDFMerger::init();

        $oMerger->addPDF(public_path('pdf/pdf1.pdf'), 'all');
        $oMerger->addPDF(public_path('pdf/pdf2.pdf'), 'all');
        /*if ($hoja_ruta->tieneSellos())
            $oMerger->addPDF(public_path('pdf/pdf3.pdf'), 'all');*/

        $oMerger->merge();
        dd($oMerger->stream());
    }

    public function delete_despacho(Request $request)
    {
        try {
            DB::beginTransaction();
            $hoja_ruta = HojaRuta::find($request->id_hoja_ruta);
            bitacora('detalle_hoja_ruta', $hoja_ruta->id_hoja_ruta, 'D', 'DESPACHO eliminado');
            $hoja_ruta->delete();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>ELIMINADO</strong> el despacho correctamente';
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al eliminar la informacion del sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
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
        if ($request->cliente != 'T')
            $proyectos = $proyectos->where('p.id_cliente', $request->cliente);
        if ($request->agencia != 'T')
            $proyectos = $proyectos->where('p.id_agencia_carga', $request->agencia);
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
                ->where('cp.id_proyecto', $proy->id_proyecto)
                ->get();
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
                    ->where('det.id_caja_proyecto', $caja->id_caja_proyecto)
                    ->get();

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
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'PRECIO TOTAL');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTAL FACTURA');
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $pos_p => $pedido) {
            $fecha_formateada = explode('-', $pedido['proyecto']->fecha);
            $getMonto = Proyecto::find($pedido['proyecto']->id_proyecto)->getMonto();
            foreach ($pedido['valores_cajas'] as $pos_c => $caja) {
                foreach ($caja['detalles'] as $pos_d => $detalle) {
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
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, '$' . $detalle->precio * $caja['caja']->cantidad * $detalle->ramos_x_caja);
                    if ($pos_d == 0 && $pos_c == 0) {
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $getMonto);
                    }
                }
            }
        }

        setBorderToCeldaExcel($sheet, $columnas[0] . 1 . ':' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function descargar_disponibilidad(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_disponibilidad($spread, $request);

        $fileName = "Disponibilidad_de_flor.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_disponibilidad($spread, $request)
    {
        $listado = Proyecto::join('agencia_carga as ac', 'ac.id_agencia_carga', '=', 'proyecto.id_agencia_carga')
            ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'proyecto.id_cliente')
            ->select(
                'proyecto.*',
                'ac.nombre as nombre_agencia_carga',
                'dc.nombre as nombre_cliente'
            )->distinct()
            ->where('proyecto.fecha', '>=', $request->desde)
            ->where('proyecto.fecha', '<=', $request->hasta)
            ->where('dc.estado', 1);
        if ($request->cliente != 'T')
            $listado = $listado->where('proyecto.id_cliente', $request->cliente);
        if ($request->agencia != 'T')
            $listado = $listado->where('proyecto.id_agencia_carga', $request->agencia);
        $listado = $listado->orderBy('ac.orden')
            ->orderBy('dc.nombre')
            ->get();

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('PEDIDOS ' . $request->desde . ' ' . $request->hasta);

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'FECHA');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'CLIENTE');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'CONSIGNATARIO');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'FACTURA');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'MARCACIONES');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'PIEZAS');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'CAJAS FULL');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'HALF');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'CUARTOS');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'OCTAVOS');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'SB');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'AGENCIA DE CARGA');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'FACTURADO POR');
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $pos_p => $proyecto) {
            $fecha_formateada = explode('-', $proyecto->fecha);
            $marcaciones = '';
            $list_marcaciones = [];
            $piezas = 0;
            $fb = 0;
            $hb = 0;
            $qb = 0;
            $eb = 0;
            $sb = 0;
            foreach ($proyecto->cajas as $pos_c => $caja) {
                foreach ($caja->marcaciones as $marc) {
                    if (!in_array($marc->valor, $list_marcaciones)) {
                        $marcaciones .= $marc->valor . ' ';
                        $list_marcaciones[] = $marc->valor;
                    }
                }
                $piezas += $caja->cantidad;
                $empaque = $caja->empaque;
                if (explode('|', $empaque->nombre)[1] == 1) {
                    $fb += $caja->cantidad;
                }
                if (explode('|', $empaque->nombre)[1] == 0.5) {
                    $hb += $caja->cantidad;
                    $fb += $caja->cantidad * 0.5;
                }
                if (explode('|', $empaque->nombre)[1] == 0.25) {
                    $qb += $caja->cantidad;
                    $fb += $caja->cantidad * 0.25;
                }
                if (explode('|', $empaque->nombre)[1] == 0.125) {
                    $eb += $caja->cantidad;
                    $fb += $caja->cantidad * 0.125;
                }
                if (explode('|', $empaque->nombre)[1] == 0.0625) {
                    $sb += $caja->cantidad;
                    $fb += $caja->cantidad * 0.0625;
                }
            }
            $cliente = $proyecto->cliente;
            $consignatario = $proyecto->consignatario;
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $fecha_formateada[2] . '-' . $fecha_formateada[1] . '-' . $fecha_formateada[0]);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $cliente->detalle()->nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $consignatario != '' ? $consignatario->nombre : '');
            $col++;
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $marcaciones);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $piezas);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $fb);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $hb > 0 ? $hb : '');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $qb > 0 ? $qb : '');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $eb > 0 ? $eb : '');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $sb > 0 ? $sb : '');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $proyecto->agencia_carga->nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'NINTANGA SAS');
        }

        setBorderToCeldaExcel($sheet, $columnas[0] . 1 . ':' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
