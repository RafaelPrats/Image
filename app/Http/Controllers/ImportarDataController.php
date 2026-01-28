<?php

namespace yura\Http\Controllers;

use DB;
use Exception;
use Illuminate\Http\Request;
use Validator;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Worksheet;
use yura\Modelos\Banco;
use yura\Modelos\Seguro;
use yura\Modelos\Cargo;
use yura\Modelos\CausaDesvinculacion;
use yura\Modelos\EstadoCivil;
use yura\Modelos\Sexo;
use yura\Modelos\Nacionalidad;
use yura\Modelos\Tipo_rol;
use yura\Modelos\TipoPago;
use yura\Modelos\TipoContrato;
use yura\Modelos\Grupo;
use yura\Modelos\Departamento;
use yura\Modelos\Discapacidad;
use yura\Modelos\Sucursal;
use yura\Modelos\GrupoInterno;
use yura\Modelos\GradoInstruccion;
use yura\Modelos\Area;
use yura\Modelos\Actividad;
use yura\Modelos\ManoObra;
use yura\Modelos\TipoCuenta;
use yura\Modelos\Plantilla;
use yura\Modelos\DetalleContrato;
use yura\Modelos\RelacionLaboral;
use yura\Modelos\Ciclo;
use yura\Modelos\HistoricoVentas;
use yura\Modelos\ClasificacionUnitaria;
use yura\Modelos\ClasificacionVerde;
use yura\Modelos\Cosecha;
use yura\Modelos\DesgloseRecepcion;
use yura\Modelos\DetalleClasificacionVerde;
use yura\Modelos\GrupoMenu;
use yura\Modelos\Modulo;
use yura\Modelos\Personal;
use yura\Modelos\PersonalDetalle;
use yura\Modelos\Recepcion;
use yura\Modelos\RecepcionClasificacionVerde;
use yura\Modelos\Submenu;
use yura\Http\Controllers\MarcaController;
use yura\Modelos\AgenciaCarga;
use yura\Modelos\ClasificacionRamo;
use yura\Modelos\Cliente;
use yura\Modelos\ClienteAgenciaCarga;
use yura\Modelos\ClienteConsignatario;
use yura\Modelos\ClienteDatoExportacion;
use yura\Modelos\Consignatario;
use yura\Modelos\ContactoConsignatario;
use yura\Modelos\DatosExportacion;
use yura\Modelos\DetalleCliente;
use yura\Modelos\Empaque;
use yura\Modelos\Marca;
use yura\Modelos\Planta;

class ImportarDataController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.importar_data.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'grupos_menu' => GrupoMenu::All()
        ]);
    }

    public function importar_cosecha(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $valida = Validator::make($request->all(), [
            'file_postcosecha' => 'required',
            'id_modulo_postcosecha' => 'required',
            'hora_inicio_postcosecha' => 'required',
            'personal_postcosecha' => 'required',
        ]);
        $msg = '';
        $success = true;
        if (!$valida->fails()) {

            $document = PHPExcel_IOFactory::load($request->file_postcosecha);
            $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

            $titles = $activeSheetData[1];
            foreach ($activeSheetData as $pos_row => $row) {
                if ($pos_row > 1) {
                    //dd($titles, $row, $request->all(),$activeSheetData);
                    if ($row['A'] != '') {
                        $fecha_cruda = explode('/', $row['A']);
                        $fecha = $fecha_cruda[2];
                        $fecha .= strlen($fecha_cruda[0]) == 1 ? '-0' . $fecha_cruda[0] : '-' . $fecha_cruda[0];
                        $fecha .= strlen($fecha_cruda[1]) == 1 ? '-0' . $fecha_cruda[1] : '-' . $fecha_cruda[1];

                        /* ============ COSECHA ============== */
                        if (count(Cosecha::All()->where('fecha_ingreso', $fecha)) == 0) {
                            $cosecha = new Cosecha();
                            $c = Cosecha::orderBy('id_cosecha','desc')->first();
                            $cosecha->id_cosecha = isset($c->id_cosecha) ? $c->id_cosecha + 1 : 1;
                            $cosecha->fecha_ingreso = $fecha;
                            $cosecha->personal = $request->personal_postcosecha;
                            $cosecha->hora_inicio = $request->hora_inicio_postcosecha;

                            if ($cosecha->save()) {
                                $cosecha = Cosecha::All()->last();
                                bitacora('cosecha', $cosecha->id_cosecha, 'I', 'Insercion de una nueva cosecha');

                                $semana = getSemanaByDate($fecha);
                                /* ============= RECEPCION =========== */
                                $recepcion = new Recepcion();
                                $r = Recepcion::orderBy('id_recepcion','desc')->first();
                                $recepcion->id_recepcion = isset($r->id_recepcion) ? $r->id_recepcion + 1 : 1;
                                $recepcion->id_semana = $semana->id_semana;
                                $recepcion->fecha_ingreso = $fecha . ' ' . $request->hora_inicio_postcosecha;
                                $recepcion->id_cosecha = $cosecha->id_cosecha;

                                /* ============= CLASIFICACION_VERDE =========== */
                                $verde = new ClasificacionVerde();
                                $cv = ClasificacionVerde::orderBy('id_clasificacion_verde','desc')->first();
                                $verde->id_clasificacion_verde = isset($cv->id_clasificacion_verde) ? $cv->id_clasificacion_verde + 1 : 1;
                                $verde->id_semana = $semana->id_semana;
                                $verde->fecha_ingreso = $fecha;
                                $verde->hora_inicio = $request->hora_inicio_postcosecha;
                                $verde->personal = $request->personal_postcosecha;
                                $verde->activo = $request->has('activo_postcosecha') ? 1 : 0;

                                if ($recepcion->save() && $verde->save()) {
                                    $recepcion = Recepcion::All()->last();
                                    $verde = ClasificacionVerde::All()->last();
                                    bitacora('recepcion', $recepcion->id_recepcion, 'I', 'Insercion de una nueva recepcion');
                                    bitacora('clasificacion_verde', $verde->id_clasificacion_verde, 'I', 'Insercion de una nueva clasificacion verde');

                                    /* ========== RECEPCION_CLASIFICACION_VERDE========== */
                                    $recep_verde = new RecepcionClasificacionVerde();
                                    $rcv = RecepcionClasificacionVerde::orderBy('id_recepcion_clasificacion_verde','desc')->first();
                                    $recep_verde->id_recepcion_clasificacion_verde = isset($rcv->id_recepcion_clasificacion_verde) ? $rcv->id_recepcion_clasificacion_verde + 1 : 1;
                                    $recep_verde->id_recepcion = $recepcion->id_recepcion;
                                    $recep_verde->id_clasificacion_verde = $verde->id_clasificacion_verde;

                                    if ($recep_verde->save()) {
                                        $recep_verde = RecepcionClasificacionVerde::All()->last();
                                        bitacora('recepcion_clasificacion_verde', $recep_verde->id_recepcion_clasificacion_verde, 'I', 'Insercion de una nueva recepcion_clasificacion_verde');

                                        $total_tallos = 0;
                                        $rest_tallos = 0;
                                        foreach ($row as $pos_col => $col) {
                                            if (explode('|', $titles[$pos_col])[2] == 'T') { // T => Total tallos
                                                /* ========== DESGLOSE RECEPCION ========== */
                                                $det_recep = new DesgloseRecepcion();
                                                $dr = DesgloseRecepcion::orderBy('id_desglose_recepcion','desc')->first();
                                                $det_recep->id_desglose_recepcion = isset($dr->id_desglose_recepcion) ? $dr->id_desglose_recepcion + 1 : 1;
                                                $det_recep->id_recepcion = $recepcion->id_recepcion;
                                                $det_recep->id_variedad = substr(explode('|', $titles[$pos_col])[0], 1);
                                                $det_recep->cantidad_mallas = 1;

                                                while (substr_count($col, '.') > 1) {
                                                    $col = str_replace_first('.', '', $col);
                                                }
                                                $f = substr_count($col, '.') == 0 ? 1 : 1000;
                                                $det_recep->tallos_x_malla = $col * $f;

                                                $det_recep->id_modulo = $request->id_modulo_postcosecha;

                                                if ($det_recep->save()) {
                                                    $det_recep = DesgloseRecepcion::All()->last();
                                                    bitacora('desglose_recepcion', $det_recep->id_desglose_recepcion, 'I', 'Insercion de una nueva desglose-recepcion');
                                                } else {
                                                    $success = false;
                                                    $msg .= '<li class="error">Ocurrió un problema con un desglose-recepción del día ' . $fecha .
                                                        ' con la variedad ' . getVariedad(substr(explode('|', $titles[$pos_col])[0], 1))->nombre . '</li>';
                                                }

                                                $total_tallos = $col * $f;
                                                $rest_tallos = 0;
                                            } else if (explode('|', $titles[$pos_col])[2] == 'V') { // V => tallos por calibre unitario
                                                /* ========== DETALLE CLASIFICACION VERDE ========== */
                                                $det_verde = new DetalleClasificacionVerde();
                                                $dcv = DetalleClasificacionVerde::orderBy('id_detalle_clasificacion_verde','desc')->first();
                                                $det_verde->id_detalle_clasificacion_verde = isset($dcv->id_detalle_clasificacion_verde) ? $dcv->id_detalle_clasificacion_verde + 1 : 1;
                                                $det_verde->fecha_ingreso = $fecha . ' ' . $request->hora_inicio_postcosecha;
                                                $det_verde->id_variedad = substr(explode('|', $titles[$pos_col])[0], 1);
                                                $det_verde->id_clasificacion_unitaria = substr(explode('|', $titles[$pos_col])[1], 1);
                                                $det_verde->id_clasificacion_verde = $verde->id_clasificacion_verde;
                                                $det_verde->cantidad_ramos = 1;

                                                if ($det_verde->id_clasificacion_unitaria == 7) {   // CALIBRE USA
                                                    $det_verde->tallos_x_ramos = $total_tallos - $rest_tallos;
                                                } else {
                                                    if ($request->has('cajas_postcosecha')) { // la informacion indica cajas
                                                        $estandar = $col * getConfiguracionEmpresa()->ramos_x_caja;
                                                        if ($det_verde->id_clasificacion_unitaria == 3) {    // CALIBRE CON 20 TALLOS x RAMO
                                                            $factor = 20;
                                                        } else {
                                                            $factor = explode('|', ClasificacionUnitaria::find($det_verde->id_clasificacion_unitaria)->nombre)[1];
                                                        }
                                                        $det_verde->tallos_x_ramos = round($estandar * $factor);
                                                    } else {    // la informacion indica tallos
                                                        while (substr_count($col, '.') > 1) {
                                                            $col = str_replace_first('.', '', $col);
                                                        }
                                                        $f = substr_count($col, '.') == 0 ? 1 : 1000;
                                                        $det_verde->tallos_x_ramos = $col * $f;
                                                    }
                                                    $rest_tallos += $det_verde->tallos_x_ramos;
                                                }
                                                if ($det_verde->save()) {
                                                    $det_verde = DetalleClasificacionVerde::All()->last();
                                                    bitacora('detalle_clasificacion_verde', $det_verde->id_detalle_clasificacion_verde, 'I', 'Insercion de una nuevo detalle_clasificacion_verde');
                                                } else {
                                                    $success = false;
                                                    $msg .= '<li class="error">Ocurrió un problema con un detalle_clasificacion_verde del día ' . $fecha .
                                                        ' con ' . getVariedad(substr(explode('|', $titles[$pos_col])[0], 1))->nombre . ' ' .
                                                        explode('|', ClasificacionUnitaria::find($det_verde->id_clasificacion_unitaria)->nombre)[0] . '</li>';
                                                }
                                            }
                                        }

                                    } else {
                                        $success = false;
                                        $msg .= '<li class="error">Ocurrió un problema con la recepción del día ' . $fecha . '</li>';
                                    }
                                } else {
                                    $success = false;
                                    $msg .= '<li class="error">Ocurrió un problema con la recepción del día ' . $fecha . '</li>';
                                }
                            } else {
                                $success = false;
                                $msg .= '<li class="error">Ocurrió un problema con la cosecha del día ' . $fecha . '</li>';
                            }
                        } else {
                            $success = false;
                            $msg .= '<li class="error">Ya se encuentra una cosecha del día ' . $fecha . '</li>';
                        }
                    }
                }

                if ($success) {
                    $msg = '<li class="bg-green">Se ha importado el archivo satisfactoriamente</li>';
                }
            }
        } else {
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $success = false;
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

    public function importar_venta(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $valida = Validator::make($request->all(), [
            'file_ventas' => 'required',
            'variedad_ventas' => 'required',
            'campo_ventas' => 'required',
            'anno_ventas' => 'required',
        ]);
        $msg = '';
        $success = true;
        if (!$valida->fails()) {
            $document = PHPExcel_IOFactory::load($request->file_ventas);
            $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

            //dd($activeSheetData, $request->all());
            $titles = $activeSheetData[1];
            foreach ($activeSheetData as $pos_row => $row) {
                if ($pos_row > 1) {
                    if ($row['A'] != '') {
                        $id_cliente = intval($row['A']);
                        foreach ($row as $pos_col => $col) {
                            if (str_replace('$', '', str_replace(',', '', $col)) > 0 && $titles[$pos_col] != '' && $pos_col != 'A') {
                                $historico = HistoricoVentas::All()
                                    ->where('id_cliente', $id_cliente)
                                    ->where('id_variedad', $request->variedad_ventas)
                                    ->where('mes', $titles[$pos_col])
                                    ->where('anno', $request->anno_ventas)
                                    ->first();

                                if ($historico != '') {
                                    if ($request->campo_ventas == 'V')
                                        $historico->valor = str_replace('$', '', str_replace(',', '', $col));
                                    if ($request->campo_ventas == 'F')
                                        $historico->cajas_fisicas = str_replace('$', '', str_replace(',', '', $col));
                                    if ($request->campo_ventas == 'Q')
                                        $historico->cajas_equivalentes = str_replace('$', '', str_replace(',', '', $col));
                                    if ($request->campo_ventas == 'P')
                                        if ($historico->precio_x_ramo > 0)
                                            $historico->precio_x_ramo = round(str_replace('$', '', str_replace(',', '', $col)) / $historico->precio_x_ramo, 2);
                                        else
                                            $historico->precio_x_ramo = str_replace('$', '', str_replace(',', '', $col));
                                } else {
                                    $historico = new HistoricoVentas();
                                    $historico->id_cliente = $id_cliente;
                                    $historico->id_variedad = $request->variedad_ventas;
                                    $historico->anno = $request->anno_ventas;
                                    $historico->mes = $titles[$pos_col];
                                    if ($request->campo_ventas == 'V')
                                        $historico->valor = str_replace('$', '', str_replace(',', '', $col));
                                    if ($request->campo_ventas == 'F')
                                        $historico->cajas_fisicas = str_replace('$', '', str_replace(',', '', $col));
                                    if ($request->campo_ventas == 'Q')
                                        $historico->cajas_equivalentes = str_replace('$', '', str_replace(',', '', $col));
                                    if ($request->campo_ventas == 'P')
                                        $historico->precio_x_ramo = str_replace('$', '', str_replace(',', '', $col));
                                }

                                $historico->save();
                            }
                        }
                    }
                }

                if ($success) {
                    $msg = '<li class="bg-green">Se ha importado el archivo satisfactoriamente</li>';
                }
            }
        } else {
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $success = false;
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

    public function importar_area(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $valida = Validator::make($request->all(), [
            'file_area' => 'required',
            'variedad_area' => 'required',
        ]);
        $msg = '';
        $success = true;
        if (!$valida->fails()) {

            $document = PHPExcel_IOFactory::load($request->file_area);
            $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

            //dd($activeSheetData, $request->all());
            $titles = $activeSheetData[1];
            foreach ($activeSheetData as $pos_row => $row) {
                if ($pos_row > 1) {
                    if ($row['A'] != '') {
                        $modulo = Modulo::All()->where('nombre', $row['A'])->first();

                        if ($modulo != '') {
                            $ciclo = new Ciclo();
                            $ciclo->id_modulo = $modulo->id_modulo;
                            $ciclo->id_variedad = $request->variedad_area;
                            $ciclo->activo = $request->activo_area == 'on' ? 1 : 0;
                            $ciclo->fecha_inicio = date("Y-m-d", strtotime($row['B']));
                            if ($row['C'] != '')
                                $ciclo->fecha_fin = opDiasFecha('+', $row['C'], $ciclo->fecha_inicio);
                            if ($row['E'] != '')
                                $ciclo->fecha_cosecha = opDiasFecha('+', $row['E'], $ciclo->fecha_inicio);
                            $ciclo->poda_siembra = $row['D'] != 0 ? 'P' : 'S';
                            $ciclo->area = str_replace(',', '', $row['F']);

                            if (!$ciclo->save()) {
                                $success = false;
                                $msg .= '<li class="error">Ha ocurrido un problema con el registro en la fila #' . $pos_row . '</li>';
                            }
                        }
                    }
                }

                if ($success) {
                    $msg = '<li class="bg-green">Se ha importado el archivo satisfactoriamente</li>';
                }
            }
        } else {
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $success = false;
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

    public function personal_activo(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $valida = Validator::make($request->all(), [

        ]);
        $msg = '';
        $success = true;
        if (!$valida->fails()) {

            $document = PHPExcel_IOFactory::load($request->file_personal_activo);
            $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

            $titles = $activeSheetData[1];
            foreach ($activeSheetData as $pos_row => $row) {
                if ($pos_row > 1) {
                   //dd($row);

                    /* --------------- buscar los parametros ---------------------- */
                    $banco = Banco::All()->where('nombre', $row['O'])->first();
                    $tipo_rol = Tipo_rol::All()->where('nombre', $row['L'])->first();
                    $area = Area::All()->where('nombre', $row['R'])->first();
                    $sexo = Sexo::All()->where('nombre', $row['AB'])->first();
                    $estado_civil = EstadoCivil::All()->where('nombre', $row['AC'])->first();
                    $nacionalidad = Nacionalidad::All()->where('nombre', $row['BL'])->first();
                    $tipo_contrato = TipoContrato::All()->where('nombre', $row['K'])->first();
                    $cargo = Cargo::All()->where('nombre', $row['W'])->first();
                    $tipo_pago = TipoPago::All()->where('nombre', $row['N'])->first();
                    $tipo_cuenta = TipoCuenta::All()->where('nombre', $row['P'])->first();
                    $sucursal = Sucursal::All()->where('nombre', $row['S'])->first();
                    $departamento = Departamento::All()->where('nombre', $row['U'])->first();
                    $actividad = Actividad::All()->where('nombre', $row['M'])->first();
                    $mano_obra = ManoObra::All()->where('nombre', $row['T'])->first();
                    $grupo_interno = GrupoInterno::All()->where('nombre', $row['V'])->first();
                    $grupo = Grupo::All()->where('nombre', $row['Y'])->first();
                    $plantilla = Plantilla::All()->where('nombre', $row['AY'])->first();
                    $grado_instruccion = GradoInstruccion::All()->where('nombre', $row['X'])->first();
                    $detalle_contrato = DetalleContrato::All()->where('nombre', $row['K'])->first();
                    $relacion_laboral = RelacionLaboral::All()->where('nombre', $row['AE'])->first();
                    $seguro = Seguro::All()->where('nombre', $row['AF'])->first();
                    /* --------------- verificar qexistan los parametros obligatorios ---------------------- */
                    if ($banco != '' ||
                        $tipo_rol != '' ||
                        $area != '' ||
                        $sexo != '' ||
                        $estado_civil != '' ||
                        $nacionalidad != '' ||
                        $tipo_contrato != '' ||
                        $cargo != '' ||
                        $tipo_pago != '' ||
                        $tipo_cuenta != '' ||
                        $sucursal != '' ||
                        $departamento != '' ||
                        $actividad != '' ||
                        $mano_obra != '' ||
                        $grupo_interno != '' ||
                        $grupo != '' ||
                        $plantilla != '' ||
                        $grado_instruccion != '' ||
                        $detalle_contrato != '' ||
                        $relacion_laboral != '' ||
                        $seguro != '' ) {
                        /* --------------- crear personal ---------------------- */
                        $personal = new Personal();
                        $personal->nombre = $row['C'];
                        $personal->apellido = $row['B'];
                        $personal->cedula_identidad = $row['A'];
                        $personal->id_sexo = $sexo->id_sexo;
                        $personal->id_nacionalidad = $nacionalidad->id_nacionalidad;
                        $personal->fecha_nacimiento = date("Y-m-d", strtotime($row['AK']));

                        $personal->save();
                        /* --------------- obtener el ultimo personal ---------------------- */
                        $personal = Personal::all()->last();


                     //dd($row);
                       //dd($detalle->all());
                        /* --------------- crear el detalle personal ---------------------- */
                        $detalle = new PersonalDetalle();
                        $detalle->id_personal = $personal->id_personal;
                        $detalle->fecha_ingreso = date("Y-m-d", strtotime($row['G']));
                        $detalle->id_departamento = $departamento->id_departamento;
                        $detalle->id_estado_civil  = $estado_civil->id_estado_civil;
                        $detalle->discapacidad = $row['J'] == 'No' ? 'N' : 'S';
                        $detalle->porcentaje_discapacidad = $row['BI'];
                        $detalle->id_cargo  = $cargo->id_cargo;
                        $detalle->telef  = $row['AQ'];
                        $detalle->cargas_familiares = $row['AD'];
                        $detalle->id_tipo_contrato = $tipo_contrato->id_tipo_contrato;
                        $detalle->lugar_residencia = $row['AM'];
                        $detalle->direccion = $row['AN'];
                        $detalle->correo = $row['AY'];
                        $detalle->sueldo = $row['E'];
                        $detalle->id_banco  = $banco->id_banco;
                        $detalle->id_tipo_rol  = $tipo_rol->id_tipo_rol;
                        $detalle->id_tipo_pago = $tipo_pago->id_tipo_pago;
                        $detalle->numero_cuenta  = $row['Q'];
                        $detalle->id_grado_instruccion = $grado_instruccion->id_grado_instruccion;
                        $detalle->id_sucursal  = $sucursal->id_sucursal;
                        $detalle->id_grupo  = $grupo->id_grupo;
                        $detalle->id_grupo_interno = $grupo_interno->id_grupo_interno;
                        $detalle->id_area = $area->id_area;
                        $detalle->id_actividad  = $actividad->id_actividad;
                        $detalle->id_mano_obra = $mano_obra->id_mano_obra;
                        $detalle->id_plantilla  = $plantilla->id_plantilla;
                        $detalle->id_tipo_cuenta = $tipo_cuenta->id_tipo_cuenta;
                        $detalle->id_relacion_laboral= $relacion_laboral->id_relacion_laboral;
                        $detalle->id_detalle_contrato = $detalle_contrato->id_detalle_contrato;
                        $detalle->id_seguro= $seguro->id_seguro;
                        $detalle->n_afiliacion = $row['AG'];
                        $detalle->save();

                    }
                    if ($success) {
                        $msg = '<li class="bg-green">Se ha importado el archivo satisfactoriamente</li>';
                    }
                }
            }
        } else {
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $success = false;
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

    public function importar_data_comercializacion(Request $request)
    {
        //ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        set_time_limit(600);
        ini_set('memory_limit', '-1');

        $valida = Validator::make($request->all(), [
            'file_datos_comercializacion' => 'required|mimes:xls,xlsx',
        ],[
            'file_datos_comercializacion.required' => 'Debe seleccionar un archivo',
            'file_datos_comercializacion.mimes' => 'El archivo debe ser del tipo xls o xlsx',
        ]);

        $msg = '';
        $success = true;

        DB::beginTransaction();

        if (!$valida->fails()) {

            try{

                $document = PHPExcel_IOFactory::load($request->file_datos_comercializacion);
                $cliente = new ClienteController;
                /* $agenciaCarga = new AgenciaCargaController;
                $marcacionEspceial = new DatosExportacionController;
                $planta = new PlantaController;
                $cajasPresentaciones = new CajasPresentacionesController; */

                //$hojaCajas = $document->getSheetByName('MARCAS DE CAJAS')->toArray(null, true, true, true);
                ///// MARCAS DE CAJAS /////
                //$marca = new MarcaController;

                /*for($i=2; $i <= count($hojaCajas); $i++){

                    if(isset($hojaCajas[$i]) && trim($hojaCajas[$i]['A']) != ''){

                        $validArrayMarcaCaja = $this->valida_marca_cajas($hojaCajas[$i],$i);

                        if(!is_string($validArrayMarcaCaja)){

                            $statusMarca = $marca->store_marcas(new Request([
                                'marca' => trim($hojaCajas[$i]['B']),
                                'descripcion' => trim($hojaCajas[$i]['A']),
                            ]));

                            if(!$statusMarca['success']){
                                $msgAuto =true;
                                throw new Exception($statusMarca['mensaje']);
                            }
                        }else{
                            $msgAuto =true;
                            throw new Exception($validArrayMarcaCaja);
                        }

                    }

                }

                ///// CLIENTES /////

                $hojaClientes = $document->getSheetByName('CLIENTES')->toArray(null, true, true, true);

                for($i=2; $i <= count($hojaClientes); $i++){

                    if(isset($hojaClientes[$i]) && trim($hojaClientes[$i]['B']) !== ''){

                        //$validArrayMarcaCaja = $this->valida_clientes(trim($hojaClientes[$i]),$i);

                        //if(!is_string($validArrayMarcaCaja)){

                            //$marca = Marca::where('descripcion',trim($hojaClientes[$i]['K']))->first();

                            //if(!isset($marca)){
                             //   $msgAuto=true;
                              //  throw new Exception('La marca '.trim($hojaClientes[$i]['K']). ' en la línea '.$i.' de la hoja CLIENTE no existe en el sistema');
                            //}

                            $statuscliente = $cliente->store_clientes(new Request([
                                'identificacion' => trim($hojaClientes[$i]['A']),
                                'nombre' => trim($hojaClientes[$i]['B']),
                                'telefono' => trim($hojaClientes[$i]['C']),
                                'pais' => trim($hojaClientes[$i]['D']),
                                'cod_pais_jire' => trim($hojaClientes[$i]['E']),
                                'cod_ciudad_jire' => trim($hojaClientes[$i]['F']),
                                'provincia' => trim($hojaClientes[$i]['G']),
                                'correo' => trim($hojaClientes[$i]['H']),
                                'puerto_entrada' => trim($hojaClientes[$i]['I']),
                                'direccion' => trim($hojaClientes[$i]['J']),
                                'tipo_impuesto' => 0,
                                'codigo_impuesto' => 2,
                                //'marca' => $marca->id_marca
                            ]));

                            if(!$statuscliente['success']){
                                $msgAuto =true;
                                throw new Exception($statuscliente['mensaje']);
                            }
                        //}else{
                          //  $msgAuto =true;
                            //throw new Exception($validArrayMarcaCaja);
                        //}

                    }

                }

                ///// CONTACTOS CLIENTES /////

                $hojaCClientes = $document->getSheetByName('CONTACTOS CLIENTE (OPCIONAL)')->toArray(null, true, true, true);

                for($i=2; $i <= count($hojaCClientes); $i++){

                    if(isset($hojaCClientes[$i]) && trim($hojaCClientes[$i]['A']) !== ''){

                        $validArrayCcliente = $this->valida_contato_clientes($hojaCClientes[$i],$i);

                        if(!is_string($validArrayCcliente)){

                            $objDetCliente = DetalleCliente::where('ruc',trim($hojaCClientes[$i]['A']))
                            ->where('estado',true)->select('id_detalle_cliente')->first();

                            if(!isset($objDetCliente)){
                                $msgAuto=true;
                                throw new Exception('El código '.trim($hojaCClientes[$i]['A']). ' en la línea '.$i.' de la hoja CONTACTOS CLIENTE no existe en el sistema');
                            }

                            $statuscliente = $cliente->store_contactos(new Request([
                                'id_detalle_cliente' => $objDetCliente->id_detalle_cliente,
                                'data_contactos' => [
                                    [
                                        trim($hojaCClientes[$i]['B']),
                                        trim($hojaCClientes[$i]['C']),
                                        trim($hojaCClientes[$i]['D']),
                                        trim($hojaCClientes[$i]['E']),
                                    ]
                                ]
                            ]));

                            if(!$statuscliente['success']){
                                $msgAuto =true;
                                throw new Exception($statuscliente['mensaje']);
                            }


                        }else{

                            $msgAuto =true;
                            throw new Exception($validArrayCcliente);

                        }

                    }

                }*/

                ////// CONSIGNATARIO //////

                $consignatario = new ConsignatarioController;

                $hojaConsignatarios = $document->getSheetByName('CONSIGNATARIOS')->toArray(null, true, true, true);

                for($i=2; $i <= count($hojaConsignatarios); $i++){

                    if(isset($hojaConsignatarios[$i]) && trim($hojaConsignatarios[$i]['A']) !== ''){

                        $validArrayConsignatario = $this->valida_consignatario($hojaConsignatarios[$i],$i);

                        if(!is_string($validArrayConsignatario)){

                            $existConsignatario = Consignatario::where('identificacion', $hojaConsignatarios[$i]['A'] )->first();

                            $statusConsignatario = $consignatario->storeConsignatario(new Request([
                                'id_consignatario' => isset($existConsignatario) ? $existConsignatario->id_consignatario : null,
                                'nombre' => $hojaConsignatarios[$i]['B'],
                                'identificacion' => $hojaConsignatarios[$i]['A'],
                                'cod_pais_jire' => $hojaConsignatarios[$i]['D'],
                                'ciudad' => $hojaConsignatarios[$i]['E'],
                                'correo' => $hojaConsignatarios[$i]['F'],
                                'telefono' => $hojaConsignatarios[$i]['C'],
                                'direccion' => $hojaConsignatarios[$i]['G'],
                                'contacto' => 'false',
                            ]));

                            if(!$statusConsignatario['success']){
                                $msgAuto =true;
                                throw new Exception($statusConsignatario['mensaje']);
                            }

                        }

                    }

                }

                /*///// CONTACTO CONSIGNATARIO /////

                $hojaCConsignatarios = $document->getSheetByName('CONTACTO CONSIGNATARIO (OPCIONA')->toArray(null, true, true, true);

                for($i=2; $i <= count($hojaCConsignatarios); $i++){

                    if(isset($hojaCConsignatarios[$i]) && trim($hojaCConsignatarios[$i]['F']) !== ''){

                        $validArrayCConsignatario = $this->valida_contacto_consignatario($hojaCConsignatarios[$i],$i);

                        if(!is_string($validArrayCConsignatario)){

                            $objConsignatario = Consignatario::where('identificacion',trim($hojaCConsignatarios[$i]['F']))->first();

                            if(!isset($objConsignatario)){
                                $msgAuto=true;
                                throw new Exception('El código de consignatario '.trim($hojaCConsignatarios[$i]['F']). ' en la línea '.$i.' de la hoja CONTACTOS CONSIGNATARIO no existe en el sistema');
                            }

                            $statusConsignatario = $consignatario->storeContactoConsignatario(new Request([
                                'id_consignatario' => $objConsignatario->id_consignatario,
                                'nombre_contacto' => $hojaCConsignatarios[$i]['A'],
                                'ciudad_contacto' => $hojaCConsignatarios[$i]['C'],
                                'correo_contacto' => $hojaCConsignatarios[$i]['D'],
                                'telefono_contacto' => $hojaCConsignatarios[$i]['B'],
                                'direccion_contacto' => $hojaCConsignatarios[$i]['E'],
                            ]));

                            if(!$statusConsignatario['success']){
                                $msgAuto =true;
                                throw new Exception($statusConsignatario['mensaje']);
                            }

                        }
                    }

                }*/

                ///// CLIENTE CONTACTO CONSIGNATARIO /////
                $hojaClienteConsignatarios = $document->getSheetByName('CLIENTE CONSIGNATARIO (OPCIONAL')->toArray(null, true, true, true);

                for($i=2; $i <= count($hojaClienteConsignatarios); $i++){

                    if(isset($hojaClienteConsignatarios[$i]) && trim($hojaClienteConsignatarios[$i]['A']) !== ''){

                        $validArrayClienteConsignatario = $this->valida_cliente_consignatario($hojaClienteConsignatarios[$i],$i);

                        if(!is_string($validArrayClienteConsignatario)){

                            $objDetCliente = DetalleCliente::where([
                                ['ruc',trim($hojaClienteConsignatarios[$i]['B'])],
                                ['estado',true]
                            ])->first();

                            $objConsignatario = Consignatario::where('identificacion',trim($hojaClienteConsignatarios[$i]['A']))->first();

                            if(isset($objConsignatario) && isset($objDetCliente)){

                                $existeClienteConsignatario= ClienteConsignatario::where([
                                    ['id_cliente',$objDetCliente->id_cliente],
                                    ['id_consignatario', $objConsignatario->id_consignatario]
                                ])->exists();

                                if(!$existeClienteConsignatario){

                                    $statusClienteConsignatario = $cliente->store_cliente_consignatario(new Request([
                                        'importar_data' => true,
                                        'id_cliente' => $objDetCliente->id_cliente,
                                        'arr_consignatarios' => [
                                            [
                                                'id_consignatario' => $objConsignatario->id_consignatario,
                                                'default' => true
                                            ]
                                        ]
                                    ]));

                                    if(!$statusClienteConsignatario['success']){
                                        $msgAuto =true;
                                        throw new Exception($statusClienteConsignatario['mensaje']);
                                    }

                                }

                            }

                        }else{

                            $msgAuto =true;
                            throw new Exception($validArrayClienteConsignatario);

                        }

                    }

                }

                ///// AGENCIA CARGA /////

                /*$hojaAgenciaCarga = $document->getSheetByName('AGENCIA CARGA')->toArray(null, true, true, true);

                for($i=2; $i <= count($hojaAgenciaCarga); $i++){

                    if(isset($hojaAgenciaCarga[$i]) && trim($hojaAgenciaCarga[$i]['A']) !== ''){

                        $validArrayAgenciaCarga = $this->valida_agencia_carga($hojaAgenciaCarga[$i],$i);

                        if(!is_string($validArrayAgenciaCarga)){

                            $storeAgenciaCarga = $agenciaCarga->storeAgenciaCarga(new Request([
                                'nombre' => $hojaAgenciaCarga[$i]['B'],
                                'identificacion' => $hojaAgenciaCarga[$i]['A'],
                            ]));

                            if(!$storeAgenciaCarga['success']){
                                $msgAuto =true;
                                throw new Exception($storeAgenciaCarga['mensaje']);
                            }

                        }else{

                            $msgAuto =true;
                            throw new Exception($validArrayAgenciaCarga);

                        }

                    }

                }

                ///// CLIENTE AGENCIA CARGA /////
                $hojaClienteAgenciaCarga = $document->getSheetByName('CLIENTE AGENCIA CARGA')->toArray(null, true, true, true);

                for($i=2; $i <= count($hojaClienteAgenciaCarga); $i++){

                    if(isset($hojaClienteAgenciaCarga[$i]) && trim($hojaClienteAgenciaCarga[$i]['B']) != ''){

                        //$validArrayClienteAgenciaCarga = $this->valida_cliente_agencia_carga($hojaClienteAgenciaCarga[$i],$i);

                        //if(!is_string($validArrayClienteAgenciaCarga)){

                            $objDetCliente = DetalleCliente::where([
                                ['ruc',(int)trim($hojaClienteAgenciaCarga[$i]['A'])],
                                ['estado',true]
                            ])->first();

                            $objAgenciaCarga = AgenciaCarga::where('identificacion',(int)trim($hojaClienteAgenciaCarga[$i]['B']))->first();

                            if(isset($objAgenciaCarga) && isset($objDetCliente)){

                                $existClienteAgenciaCarga = ClienteAgenciaCarga::where([
                                    ['id_cliente',$objDetCliente->id_cliente],
                                    ['id_agencia_carga', $objAgenciaCarga->id_agencia_carga]
                                ])->exists();

                                if(!$existClienteAgenciaCarga){

                                    $storeAgenciaCarga = $agenciaCarga->storeClienteAgenciaCarga(new Request([
                                        'id_agencia_carga' => $objAgenciaCarga->id_agencia_carga,
                                        'id_cliente' => $objDetCliente->id_cliente,
                                    ]));

                                    if(!$storeAgenciaCarga['success']){
                                        $msgAuto =true;
                                        throw new Exception($storeAgenciaCarga['mensaje']);
                                    }

                                }

                            }

                        //}else{

                          //  $msgAuto =true;
                            //throw new Exception($validArrayClienteAgenciaCarga);

                        //}

                    }

                }

                ///// CONTACTO CLIENTE AGENCIA CARGA /////
                $hojaContactoAgenciaCarga = $document->getSheetByName('CONTACTO AGENCIA CARGA (OPCIONA')->toArray(null, true, true, true);

                for($i=2; $i <= count($hojaContactoAgenciaCarga); $i++){

                    if(isset($hojaContactoAgenciaCarga[$i]) && trim($hojaContactoAgenciaCarga[$i]['A']) !== ''){

                        $validArrayContactoAgenciaCarga = $this->valida_contacto_agencia_carga($hojaContactoAgenciaCarga[$i],$i);

                        if(!is_string($validArrayContactoAgenciaCarga)){

                            $objAgenciaCarga = AgenciaCarga::where('identificacion',(int)trim($hojaContactoAgenciaCarga[$i]['A']))->first();

                            if(isset($objAgenciaCarga)){

                                $contactoAgenciaCarga = $agenciaCarga->storeContactoAgenciaCarga(new Request([
                                    'id_agencia_carga' => $objAgenciaCarga->id_agencia_carga,
                                    'nombre' => trim($hojaContactoAgenciaCarga[$i]['B']),
                                    'correo' => trim($hojaContactoAgenciaCarga[$i]['C']),
                                    'direccion' => trim($hojaContactoAgenciaCarga[$i]['D']),
                                ]));

                                if(!$contactoAgenciaCarga['success']){
                                    $msgAuto =true;
                                    throw new Exception($contactoAgenciaCarga['mensaje']);
                                }
                            }

                        }else{

                            $msgAuto = true;
                            throw new Exception($validArrayContactoAgenciaCarga);

                        }

                    }

                }

                ////// MARCACIONES ESPACIALES //////

                $hojaMarcacionesEspeciales= $document->getSheetByName('MARCACIONES ESPECIALES')->toArray(null, true, true, true);

                for($i=2; $i <= count($hojaMarcacionesEspeciales); $i++){

                    if(isset($hojaMarcacionesEspeciales[$i]) && trim($hojaMarcacionesEspeciales[$i]['A']) !== ''){

                        $validArrayMarcacionEspecial = $this->valida_marcacion_especial($hojaMarcacionesEspeciales[$i],$i);

                        if(!is_string($validArrayMarcacionEspecial)){

                            $existMarcacionEspecial = DatosExportacion::where('nombre',trim($hojaMarcacionesEspeciales[$i]['A']))->exists();

                            if(!$existMarcacionEspecial){

                                $contactoAgenciaCarga = $marcacionEspceial->store_datos_exportacion(new Request([
                                    'arrDatosExportacion' => [
                                        [
                                            'nombre' => trim($hojaMarcacionesEspeciales[$i]['A'])
                                        ]
                                    ]
                                ]));

                                if(!$contactoAgenciaCarga['success']){
                                    $msgAuto =true;
                                    throw new Exception($contactoAgenciaCarga['mensaje']);
                                }

                            }

                        }else{

                            $msgAuto =true;
                            throw new Exception($validArrayMarcacionEspecial);

                        }

                    }

                }

                ////// CLIENTE MARCACIONES ESPECIALES //////
                $hojaClienteMarcacionEspecial= $document->getSheetByName('CLIENTE MARCACION ESPECIAL')->toArray(null, true, true, true);

                for($i=2; $i <= count($hojaClienteMarcacionEspecial); $i++){

                    if(isset($hojaClienteMarcacionEspecial[$i]) && trim($hojaClienteMarcacionEspecial[$i]['A']) !== ''){

                        $validArrayMarcacionEspecial = $this->valida_cliente_marcacion_especial($hojaClienteMarcacionEspecial[$i],$i);

                        if(!is_string($validArrayMarcacionEspecial)){

                            $objDetCliente = DetalleCliente::where('ruc',trim($hojaClienteMarcacionEspecial[$i]['A']))
                            ->where('estado',true)->select('id_cliente')->first();

                            if(isset($objDetCliente)){

                                if(trim($hojaClienteMarcacionEspecial[$i]['B']) !==''){

                                    foreach(explode(';',trim($hojaClienteMarcacionEspecial[$i]['B'])) as $marc){

                                        $objDatoExportacion = DatosExportacion::where('nombre',trim($marc))->first();

                                        if(!isset($objDatoExportacion)){
                                            $msgAuto=true;
                                            throw new Exception('El código de la marcación especial '.trim($marc). ' en la línea '.$i.' de la hoja CLIENTE MARCACION ESPECIAL no existe en el sistema');
                                        }

                                        $existsCDE = ClienteDatoExportacion::where([
                                            ['id_cliente', $objDetCliente->id_cliente],
                                            ['id_dato_exportacion', $objDatoExportacion->id_dato_exportacion]
                                        ])->exists();

                                        if(isset($objDatoExportacion) && !$existsCDE){

                                            $clienteMarcacionEspecial = $marcacionEspceial->asignar_dato_exportacion(new Request([
                                                'id_cliente' => $objDetCliente->id_cliente,
                                                'id_dato_exportacion' => $objDatoExportacion->id_dato_exportacion,
                                                'check' => 'true'
                                            ]));

                                            if(!$clienteMarcacionEspecial['success']){
                                                $msgAuto =true;
                                                throw new Exception($clienteMarcacionEspecial['mensaje']);
                                            }

                                        }

                                    }

                                }

                            }

                        }else{

                            $msgAuto =true;
                            throw new Exception($validArrayMarcacionEspecial);

                        }

                    }

                }

                ////// PLANTAS  //////

                $hojaPlantas= $document->getSheetByName('PLANTAS')->toArray(null, true, true, true);

                for($i=2; $i <= count($hojaPlantas); $i++){

                    if(isset($hojaPlantas[$i]) && trim($hojaPlantas[$i]['A']) !== ''){

                        $validArrayPlantas = $this->valida_plantas($hojaPlantas[$i],$i);

                        if(!is_string($validArrayPlantas)){

                            $storePlanta = $planta->store_planta(new Request([
                                'nombre' => trim($hojaPlantas[$i]['B']),
                                'siglas' => trim($hojaPlantas[$i]['A']),
                                'tarifa' => trim($hojaPlantas[$i]['C']),
                                'nandina' => trim($hojaPlantas[$i]['D'])
                            ]));

                            if(!$storePlanta['success']){
                                $msgAuto =true;
                                throw new Exception($storePlanta['mensaje']);
                            }

                        }else{

                            $msgAuto =true;
                            throw new Exception($validArrayPlantas);

                        }

                    }

                }

                ////// VARIEDADES  //////
                $hojasVariedades = $document->getSheetByName('VARIEDADES')->toArray(null, true, true, true);

                for($i=2; $i <= count($hojasVariedades); $i++){

                    if(isset($hojasVariedades[$i]) && trim($hojasVariedades[$i]['A']) !== ''){

                        $validArrayVariedades = $this->valida_vairedad($hojasVariedades[$i],$i);

                        if(!is_string($validArrayVariedades)){

                            $existsPlanta = Planta::where('siglas',trim($hojasVariedades[$i]['C']))->first();

                            if(isset($existsPlanta)){

                                $storeVariedad = $planta->store_variedad(new Request([
                                    'nombre' => trim($hojasVariedades[$i]['B']),
                                    'siglas' => trim($hojasVariedades[$i]['A']),
                                    'id_planta' => $existsPlanta->id_planta,
                                    'tipo' => trim($hojasVariedades[$i]['D']),
                                    'tallos_x_malla' => trim($hojasVariedades[$i]['E']),
                                ]));

                                if(!$storeVariedad['success']){
                                    $msgAuto =true;
                                    throw new Exception($storeVariedad['mensaje']);
                                }

                            }

                        }

                    }

                }

                ////// PESOS RAMOS  //////
                $hojasPesosRamos = $document->getSheetByName('PESOS DEL RAMO')->toArray(null, true, true, true);

                for($i=2; $i <= count($hojasPesosRamos); $i++){

                    if(isset($hojasPesosRamos[$i]) && trim($hojasPesosRamos[$i]['A']) !== ''){

                        $existsClasRamo = ClasificacionRamo::where('nombre',trim($hojasPesosRamos[$i]['A']))->first();

                        if(!isset($existsClasRamo)){

                            $this->storeClasificacionRamo(new Request([
                                'nombre' => trim($hojasPesosRamos[$i]['A']),
                                'id_configuracion_empresa' => 1,
                                'id_unidad_medida' => 2
                            ]));

                        }

                    }

                }

                ////// PRESENTACIONES  //////
                $hojasPresentaciones = $document->getSheetByName('PRESENTACIONES')->toArray(null, true, true, true);

                for($i=2; $i <= count($hojasPresentaciones); $i++){

                    if(isset($hojasPresentaciones[$i]) && trim($hojasPresentaciones[$i]['A']) !== ''){

                        $existsClasRamo = Empaque::where('nombre',trim($hojasPresentaciones[$i]['A']))->first();

                        if(!isset($existsClasRamo)){

                            $cajasPresentaciones = new CajasPresentacionesController;

                            $cajasPresentaciones->store_empaque(new Request([
                                'nombre' => trim($hojasPresentaciones[$i]['A']),
                                'tipo' => 'P',
                            ]));

                        }

                    }

                }

                ////// CAJAS  //////
                $hojasCajas = $document->getSheetByName('CAJAS')->toArray(null, true, true, true);

                for($i=2; $i <= count($hojasCajas); $i++){

                    if(isset($hojasCajas[$i]) && trim($hojasCajas[$i]['A']) !== ''&& trim($hojasCajas[$i]['B']) !== ''){

                        $existsClasRamo = Empaque::where('nombre',trim($hojasCajas[$i]['A']))->first();

                        if(!isset($existsClasRamo)){

                            $cajasPresentaciones->store_empaque(new Request([
                                'nombre' => trim($hojasCajas[$i]['A']).'|'.trim($hojasCajas[$i]['B']).'|'.trim($hojasCajas[$i]['C']),
                                'cod_jire' => trim($hojasCajas[$i]['D']),
                                'tipo' => 'C',
                            ]));

                        }

                    }

                }*/

                DB::commit();
                $success = true;
                $msg = '<div class="alert alert-success text-center">
                            <p> Los datos del archivo se ha cargado exitosamente</p>
                        </div>';

            }catch(\Exception $e){

                $success=false;
                DB::rollBack();
                $msg = '<div class="alert alert-warning text-center">' .
                            '<p> Ha ocurrido un problema al guardar la información al sistema</p>
                            <p>'.$e->getMessage().' '.( !isset($msgAuto) ? $e->getFile().' '.$e->getLine() : '' ).'</p>
                        </div>';

            }

        } else {
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $success = false;
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

    public function valida_marca_cajas($data,$linea)
    {
        if($data['B'] === null) return 'El campo NOMBRE DE CAJA es obligatorio en la línea '.$linea. ' En el libro MARCA DE CAJAS';
        if($data['A'] === null) return 'El campo CODIGO CAJA es obligatorio en la línea '.$linea. ' En el libro MARCA DE CAJAS';

        return true;
    }

    public function valida_contato_clientes($data,$linea)
    {
        if($data['A'] === null) return 'El campo CODIGO CLIENTE es obligatorio'.$linea. ' En el libro CONTACTOS CLIENTES';
        if($data['B'] === null) return 'El campo NOMBRE es obligatorio'.$linea. ' En el libro CONTACTOS CLIENTES';
        //if($data['C'] === null) return 'El campo CORREO es obligatorio'.$linea. ' En el libro CONTACTOS CLIENTES';
        //if($data['D'] === null) return 'El campo TELEFONO es obligatorio'.$linea. ' En el libro CONTACTOS CLIENTES';
        //if($data['E'] === null) return 'El campo DIRECCION es obligatorio'.$linea. ' En el libro CONTACTOS CLIENTES';

        return true;
    }

    public function valida_consignatario($data,$linea)
    {
        if($data['A'] === null) return 'El campo CODIGO es obligatorio en la línea '.$linea. ' del libro CONSIGNATARIOS';
        if($data['B'] === null) return 'El campo NOMBRE es obligatorio en la línea '.$linea. ' del el libro CONSIGNATARIOS';
        //if($data['C'] === null) return 'El campo TELEFONO es obligatorio'.$linea. ' En el libro CONSIGNATARIOS';
        //if($data['D'] === null) return 'El campo PAIS es obligatorio'.$linea. ' En el libro CONSIGNATARIOS';
        //if($data['E'] === null) return 'El campo CIUDAD es obligatorio'.$linea. ' En el libro CONSIGNATARIOS';
        //if($data['F'] === null) return 'El campo CORREO es obligatorio'.$linea. ' En el libro CONSIGNATARIOS';
        //if($data['G'] === null) return 'El campo DIRECCION es obligatorio'.$linea. ' En el libro CONSIGNATARIOS';

        return true;
    }

    public function valida_contacto_consignatario($data,$linea)
    {
        if($data['A'] === null) return 'El campo NOMBRE es obligatorio'.$linea. ' En el libro CONTACTO CONSIGNATARIOS';
        if($data['B'] === null) return 'El campo TELEFONO es obligatorio'.$linea. ' En el libro CONTACTO CONSIGNATARIOS';
        if($data['C'] === null) return 'El campo CIUDAD es obligatorio'.$linea. ' En el libro CONTACTO CONSIGNATARIOS';
        if($data['D'] === null) return 'El campo CORREO es obligatorio'.$linea. ' En el libro CONTACTO CONSIGNATARIOS';
        if($data['E'] === null) return 'El campo DIRECCION es obligatorio'.$linea. ' En el libro CONTACTO CONSIGNATARIOS';
        if($data['F'] === null) return 'El campo CODIGO CONSIGNATARIO obligatorio'.$linea. ' En el libro CONTACTO CONSIGNATARIOS';

        return true;
    }

    public function valida_cliente_consignatario($data,$linea)
    {
        if($data['A'] === null) return 'El campo CODIGO CLIENTE es obligatorio'.$linea. ' En el libro CLIENTE CONSIGNATARIO CONSIGNATARIOS';
        if($data['B'] === null) return 'El campo CODIGO CONSIGNATARIO es obligatorio'.$linea. ' En el libro CLIENTE CONSIGNATARIO CONSIGNATARIOS';
       // if($data['C'] === null) return 'El campo POR DEFECTO es obligatorio'.$linea. ' En el libro CLIENTE CONSIGNATARIO CONSIGNATARIOS';

        return true;
    }

    public function valida_agencia_carga($data,$linea)
    {
        if($data['A'] === null) return 'El campo CODIGO es obligatorio'.$linea. ' En el libro AGENCIA CARGA';
        if($data['B'] === null) return 'El campo NOMBRE es obligatorio'.$linea. ' En el libro AGENCIA CARGA';

        return true;
    }

    public function valida_cliente_agencia_carga($data,$linea)
    {
        if($data['A'] === null) return 'El campo CODIGO CLIENTE es obligatorio'.$linea. ' En el libro CLIENTE AGENCIA CARGA';
        if($data['B'] === null) return 'El campo CODIGO CLIENTE AGENCIA CARGA es obligatorio'.$linea. ' En el libro CLIENET AGENCIA CARGA';

        return true;
    }

    public function valida_contacto_agencia_carga($data,$linea)
    {
        if($data['A'] === null) return 'El campo CODIGO AGENCIA CARGA es obligatorio en la línea '.$linea. ' del libro CONTACTO AGENCIA CARGA';
        if($data['B'] === null) return 'El campo NOMBRE es obligatorio en la línea '.$linea. ' del libro CONTACTO AGENCIA CARGA';
        //if($data['C'] === null) return 'El campo CORREO es obligatorio en la línea '.$linea. ' del libro CONTACTO AGENCIA CARGA';
        //if($data['D'] === null) return 'El campo DIRECCION es obligatorio en la línea '.$linea. ' del libro CONTACTO AGENCIA CARGA';

        return true;
    }

    public function valida_marcacion_especial($data,$linea)
    {
        if($data['A'] === null) return 'El campo CODIGO es obligatorio'.$linea. ' En el libro MARCACION ESPECIAL';

        return true;
    }

    public function valida_cliente_marcacion_especial($data,$linea)
    {
        if($data['A'] === null) return 'El campo CODIGO CLIENTE es obligatorio'.$linea. ' En el libro CLIENTE MARCACION ESPECIAL';
        if($data['B'] === null) return 'El campo CODIGO MARCACION es obligatorio'.$linea. ' En el libro CLIENTE MARCACION ESPECIAL';

        return true;
    }

    public function valida_plantas($data,$linea)
    {
        if($data['A'] === null) return 'El campo CODIGO es obligatorio'.$linea. ' En el libro PLANTAS';
        if($data['B'] === null) return 'El campo NOMBRE es obligatorio'.$linea. ' En el libro PLANTAS';

        return true;
    }

    public function valida_vairedad($data,$linea)
    {
        if($data['A'] === null) return 'El campo CODIGO es obligatorio en la línea '.$linea. ' En el libro VAIEDADES';
        if($data['B'] === null) return 'El campo NOMBRE es obligatorio en la línea '.$linea. ' En el libro VAIEDADES';
        if($data['C'] === null) return 'El campo CODIGO PLANTA es obligatorio en la línea '.$linea. ' En el libro VAIEDADES';
        if($data['D'] === null) return 'El campo TIPO es obligatorio en la línea '.$linea. ' En el libro VAIEDADES';
        if($data['D'] === null) return 'El campo TALLOS POR MALLA es obligatorio en la línea '.$linea. ' En el libro VAIEDADES';

        return true;
    }

    public function storeClasificacionRamo(Request $request)
    {
        $objClasifiXRamos = new ClasificacionRamo;
        $objClasifiXRamos->nombre = $request->nombre;
        $objClasifiXRamos->id_configuracion_empresa = $request->id_configuracion_empresa;
        $objClasifiXRamos->id_unidad_medida= $request->id_unidad_medida;
        $objClasifiXRamos->save();
    }

}
