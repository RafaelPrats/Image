<?php

namespace yura\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use yura\Modelos\ClasificacionRamo;
use yura\Modelos\Cliente;
use yura\Modelos\Empaque;
use yura\Modelos\Variedad;
use yura\Modelos\Especificacion;
use yura\Modelos\EspecificacionEmpaque;
use yura\Modelos\DetalleEspecificacionEmpaque;
use yura\Modelos\ClientePedidoEspecificacion;
use yura\Modelos\UnidadMedida;
use yura\Modelos\Grosor;
use Validator;
use DB;
use Session;
use Storage as Almacenamiento;
use yura\Modelos\DetalleEspecificacionEmpaqueRamosXCajaPerdido;
use yura\Modelos\DetallePedido;
use yura\Modelos\DetallePedidoPerdido;
use yura\Modelos\DistribucionMixtos;
use yura\Modelos\Pedido;
use yura\Modelos\PedidoModificacion;
use yura\Modelos\PedidoPerdido;

class EspecificacionClienteController extends Controller
{
    public function admin_especificaciones(Request $request)
    {
        return view('adminlte.gestion.postcocecha.clientes.partials.especificaciones', [
            'cliente' => Cliente::find($request->id_cliente),
        ]);
    }

    public function add_especificacion(Request $request)
    {
        return view('adminlte.gestion.postcocecha.clientes.partials.forms.add_especificacion', [
            'cliente' => Cliente::find($request->id_cliente),
            'clientes' => Cliente::join('detalle_cliente as dc', 'cliente.id_cliente', 'dc.id_cliente')
                ->where('dc.estado', 1)->select('cliente.id_cliente', 'dc.nombre')->get()
        ]);
    }

    public function ver_especiaficacion(Request $request)
    {
        return view('adminlte.gestion.postcocecha.clientes.partials._forms.add_especificacion', [
            'especificacion' => Especificacion::find($request->id_especificacion),
        ]);
    }

    public function cargar_form_especificacion_empaque(Request $request)
    {
        return view('adminlte.gestion.postcocecha.clientes.partials.forms._detalles', [
            'cajas' => Empaque::All()->where('tipo', '=', 'C')->where('estado', '=', 1),
            'cant_detalles' => $request->cant_detalles,
        ]);
    }

    public function cargar_form_detalle_especificacion_empaque(Request $request)
    {
        return view('adminlte.gestion.postcocecha.clientes.partials.forms._desgloses', [
            //'envolturas'        => Empaque::All()->where('tipo', '=', 'E')->where('estado', '=', 1),
            'presentaciones'    => Empaque::All()->where('tipo', '=', 'P')->where('estado', '=', 1),
            'pesajes'           => ClasificacionRamo::All()->where('estado', '=', 1),
            'variedades'        => Variedad::All()->where('estado', '=', 1),
            'pos_form_detalles' => $request->pos_form_detalles,
            'cant_desgloses'    => $request->cant_desgloses,
            'unidad_medida'     => UnidadMedida::where('tipo', 'L')->get(),
            'grosor'            => Grosor::all()->where('estado', 1),
        ]);
    }

    public function store_especificacion(Request $request)
    {

        $validaDataGeneral = Validator::make($request->all(), [
            //'nombre' => 'required',
            //'descripcion' => 'required',
        ]);

        if (!$validaDataGeneral->fails()) {

            $success = true;
            $msg = '<div class="alert alert-success text-center">' .
                '<p> Se han guardado exitosamente todos los datos</p>'
                . '</div>';
            if (empty($request->id_especificacion)) {

                $objEspecificacion = new Especificacion;
                $esp = Especificacion::orderBy('id_especificacion', 'desc')->first();
                $objEspecificacion->id_especificacion = isset($esp->id_especificacion) ? $esp->id_especificacion + 1 : 1;
                $accion = 'Insercion';
                $accionLetra = 'I';
            } else {

                $objEspecificacion = Especificacion::find($request->id_especificacion);
                $accion = 'Actualizacion';
                $accionLetra = 'U';
            }

            /* $objEspecificacion->id_cliente  = $request->id_cliente;*/
            $objEspecificacion->nombre      = $request->nombre;
            $objEspecificacion->descripcion = $request->descripcion;
            $objEspecificacion->estado      = 1;
            $objEspecificacion->tipo        = "N";
            //
            if ($objEspecificacion->save()) {
                $modelEspcificacion = Especificacion::all()->last();
                bitacora('especificacion', $modelEspcificacion->nombre, $accionLetra, $accion . ' satisfactoria de un nuevo empaque');

                for ($i = 1; $i <= $request->cant_forms_detalles; $i++) {

                    //*********** VALIDA IMAGEN ************//
                    if ($request->hasFile('imagen_' . $i)) {

                        $archivo = $request->file('imagen_' . $i);
                        $input = array('image' => $archivo);
                        $reglas = array('image' => 'required|image|mimes:jpeg,jpeg,jpg|max:2000');
                        $validacion = Validator::make($input, $reglas);

                        if ($validacion->fails()) {

                            $msg = '<div class="alert alert-danger text-center">' .
                                '<p>¡Imagen no valida!</p>' .
                                '</div>';
                            $modelEspcificacion = Especificacion::find($modelEspcificacion->id_especificacion);
                            $modelEspcificacion->delete();
                            $success = false;
                        } else {

                            $nombre_original = $archivo->getClientOriginalName();
                            $extension = $archivo->getClientOriginalExtension();
                            $imagen = "imagen_especificaciones_" . date('Y_d_m_H_i_s') . mt_rand() . "-." . $extension;
                            $r1 = Almacenamiento::disk('imagenes')->put($imagen, \File::get($archivo));
                            if (!$r1) {
                                $msg = '<div class="alert alert-danger text-center">' .
                                    '<p>¡No se pudo subir la imagen!</p>' .
                                    '</div>';
                                $success = false;

                                $modelEspcificacion = Especificacion::find($modelEspcificacion->id_especificacion);
                                $modelEspcificacion->delete();
                            }
                        }
                    }
                    //*********** FIN VALIDA IMAGEN ************//

                    //************ INSERTA Ó ACTUALIZA ESPECIFICACIÓN EMPAQUE *************//
                    if (!$request->has('id_esp_empaque_' . $i)) {
                        $objEspecificacionEmpaque = new EspecificacionEmpaque;
                        $espEmpaque = EspecificacionEmpaque::orderBy('id_especificacion_empaque', 'desc')->first();
                        $objEspecificacionEmpaque->id_especificacion_empaque = isset($espEmpaque->id_especificacion_empaque) ? $espEmpaque->id_especificacion_empaque + 1 : 1;
                        $accion = 'Insercion';
                        $accionLetra = 'I';
                    } else {
                        $objEspecificacionEmpaque = EspecificacionEmpaque::find($request->id_esp_empaque_ . $i);
                        $accion = 'Actualizacion';
                        $accionLetra = 'U';
                    }

                    $objEspecificacionEmpaque->id_especificacion = $modelEspcificacion->id_especificacion;
                    $objEspecificacionEmpaque->id_empaque = $request->input('id_empaque_' . $i);
                    $objEspecificacionEmpaque->cantidad = $request->input('cantidad_' . $i);
                    $objEspecificacionEmpaque->imagen = isset($imagen) ? $imagen : '';
                    //
                    if ($objEspecificacionEmpaque->save()) {

                        $modelEspcificacionEmpaque = EspecificacionEmpaque::all()->last();

                        bitacora('especificacion_empaque', $modelEspcificacion->nombre, $accionLetra, $accion . ' satisfactoria de una nueva especificacion de empaque');
                        //************ FIN INSERTA Ó ACTUALIZA ESPECIFICACIÓN EMPAQUE *************//

                        //************ INSERTA Ó ACTUALIZA DETALLES ESPECIFICACION EMPAQUE (DESGLOSES) *************//
                        for ($j = 1; $j <= $request->input('cant_forms_desgloses_' . $i); $j++) {

                            if (!$request->has('id_detalle_esp_emp_' . $i . "_" . $j)) {
                                $objEspecificacionEmpaqueDetalle = new DetalleEspecificacionEmpaque;
                                $detEspEmpaque = DetalleEspecificacionEmpaque::orderBy('id_detalle_especificacionempaque', 'desc')->first();
                                $objEspecificacionEmpaqueDetalle->id_detalle_especificacionempaque = isset($detEspEmpaque->id_detalle_especificacionempaque) ? $detEspEmpaque->id_detalle_especificacionempaque + 1 : 1;
                                $accion = 'Insercion';
                                $accionLetra = 'I';
                            } else {
                                $objEspecificacionEmpaqueDetalle = DetalleEspecificacionEmpaque::find($request->id_detalle_espemp_ . $i . "_" . $j);
                                $accion = 'Actualizacion';
                                $accionLetra = 'U';
                            }

                            $objEspecificacionEmpaqueDetalle->id_especificacion_empaque = $modelEspcificacionEmpaque->id_especificacion_empaque;
                            $objEspecificacionEmpaqueDetalle->id_variedad              = $request->input('id_variedad_' . $i . '_' . $j);
                            $objEspecificacionEmpaqueDetalle->id_clasificacion_ramo    = $request->input('id_clasificacion_ramo_' . $i . '_' . $j);
                            $objEspecificacionEmpaqueDetalle->cantidad                 = $request->input('cantidad_' . $i . '_' . $j);
                            //$objEspecificacionEmpaqueDetalle->id_empaque_e             = $request->input('id_empaque_e_' . $i . '_' . $j);
                            $objEspecificacionEmpaqueDetalle->id_empaque_p             = $request->input('id_empaque_p_' . $i . '_' . $j);
                            !empty($request->input('tallos_x_ramos_' . $i . '_' . $j))  ? $objEspecificacionEmpaqueDetalle->tallos_x_ramos   = $request->input('tallos_x_ramos_' . $i . '_' . $j) : '';
                            !empty($request->input('id_ud_medida_' . $i . '_' . $j))    ? $objEspecificacionEmpaqueDetalle->id_unidad_medida = $request->input('id_ud_medida_' . $i . '_' . $j)   : '';
                            !empty($request->input('id_grosor_' . $i . '_' . $j))       ? $objEspecificacionEmpaqueDetalle->id_grosor_ramo   = $request->input('id_grosor_' . $i . '_' . $j)         : '';
                            !empty($request->input('long_ramo_' . $i . '_' . $j))       ? $objEspecificacionEmpaqueDetalle->longitud_ramo   = $request->input('long_ramo_' . $i . '_' . $j)      : '';

                            if ($objEspecificacionEmpaqueDetalle->save()) {
                                $modelEspecificacionEmpaqueDetalle = DetalleEspecificacionEmpaque::all()->last();
                                bitacora('detalle_especificacionempaque', $modelEspecificacionEmpaqueDetalle->id_detalle_especificacionempaque, $accionLetra, $accion . ' satisfactoria de un nuevo detalle de especificacion de empaque');
                            } else {

                                if ($accion === 'Insercion') {

                                    Almacenamiento::disk('imagenes')->delete($imagen);
                                    //$objEspecificacionEmpaqueDetalleDelete = DetalleEspecificacionEmpaque::find($modelEspcificacionEmpaque->id_especificacion_empaque);
                                    //$objEspecificacionEmpaqueDetalleDelete->delete();

                                    $objEspecificacionEmpaqueDelete = EspecificacionEmpaque::where('id_especificacion', $modelEspcificacion->id_especificacion);
                                    $objEspecificacionEmpaqueDelete->delete();

                                    $modelEspcificacion = Especificacion::find($modelEspcificacion->id_especificacion);
                                    $modelEspcificacion->delete();
                                }
                                $success = false;
                                $msg = '<div class="alert alert-warning text-center">' .
                                    '<p> Ha ocurrido un problema al guardar el desglose del detalle de la especificacion</p>';
                            }
                        }
                        //************ FIN INSERTA Ó ACTUALIZA DETALLES ESPECIFICACION EMPAQUE (DESGLOSES) *************//
                    } else {

                        if ($accion === 'Insercion') {

                            Almacenamiento::disk('imagenes')->delete($imagen);
                            //$objEspecificacionEmpaqueDelete = EspecificacionEmpaque::where('id_especificacion', $modelEspcificacion->id_especificacion);
                            //$objEspecificacionEmpaqueDelete->delete();

                            $modelEspcificacion = Especificacion::find($modelEspcificacion->id_especificacion);
                            $modelEspcificacion->delete();
                        }

                        $success = false;
                        $msg = '<div class="alert alert-warning text-center">' .
                            '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>';
                    }

                    if ($i === 1) {
                        $objClientePedidoEspecificacion = new ClientePedidoEspecificacion;
                        $cpe = ClientePedidoEspecificacion::orderBy('id_cliente_pedido_especificacion', 'desc')->first();
                        $objClientePedidoEspecificacion->id_cliente_pedido_especificacion = isset($cpe->id_cliente_pedido_especificacion) ? $cpe->id_cliente_pedido_especificacion + 1 : 1;
                        $objClientePedidoEspecificacion->id_cliente        = $request->id_cliente;
                        $objClientePedidoEspecificacion->id_especificacion = $modelEspcificacion->id_especificacion; //AQUI

                        if ($objClientePedidoEspecificacion->save()) {
                            $modelClientePedidoEspecificacion = ClientePedidoEspecificacion::all()->last();
                            bitacora('cliente_pedido_especificacion', $modelClientePedidoEspecificacion->id_cliente_pedido_especificacion, 'I', ' Asignacion exitosa de la especificacion ' . $modelEspcificacion->id_especificacion . ' al cliente ' . $modelClientePedidoEspecificacion->id_cliente . '');

                            if ($request->input('cant_forms_desgloses_' . $i) == 1) {

                                if (!valida_especificacion($request->input('id_variedad_' . $i . '_' . $j), $request->input('id_clasificacion_ramo_' . $i . '_' . $j), $request->input('id_empaque_' . $i), $request->input('cantidad_' . $i . '_' . $j))) {

                                    if ($accion === 'Insercion') {

                                        $objClientePedidoEspecificacion = ClientePedidoEspecificacion::find($modelClientePedidoEspecificacion->id_cliente_pedido_especificacion);
                                        $objClientePedidoEspecificacion->delete();

                                        $objDetalleEspecificacionEmpaque = DetalleEspecificacionEmpaque::find($modelEspecificacionEmpaqueDetalle->id_detalle_especificacionempaque);
                                        $objDetalleEspecificacionEmpaque->delete();

                                        $objEspecificacionEmpaqueDelete = EspecificacionEmpaque::where('id_especificacion', $modelEspcificacion->id_especificacion);
                                        $objEspecificacionEmpaqueDelete->delete();

                                        $modelEspcificacion = Especificacion::find($modelEspcificacion->id_especificacion);
                                        $modelEspcificacion->delete();

                                        $success = false;
                                        $msg = '<div class="alert alert-warning text-center">' .
                                            '<p> No se puede crear un paquete con las especificaciones de la caja N# ' . $i . ' ya que sobrepasa la cantidad de ramos por empaque configuradas o no existe el detalle del empaque</p>' .
                                            '</div>';
                                        return [
                                            'mensaje' => $msg,
                                            'success' => $success
                                        ];
                                    }
                                }
                            }
                        } else {
                            if ($accion === 'Insercion') {
                                Almacenamiento::disk('imagenes')->delete($imagen);

                                $objEspecificacionEmpaqueDetalleDelete = DetalleEspecificacionEmpaque::find($modelEspcificacionEmpaque->id_especificacion_empaque);
                                $objEspecificacionEmpaqueDetalleDelete->delete();

                                $objEspecificacionEmpaqueDelete = EspecificacionEmpaque::where('id_especificacion', $modelEspcificacion->id_especificacion);
                                $objEspecificacionEmpaqueDelete->delete();

                                $modelEspcificacion = Especificacion::find($modelEspcificacion->id_especificacion);
                                $modelEspcificacion->delete();
                            }
                            $success = false;
                            $msg = '<div class="alert alert-warning text-center">' .
                                '<p> Ha ocurrido un problema al guardar el desglose del detalle de la especificacion</p>';
                        }
                    }
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> Ha ocurrido un problema al guardar el nombre o descripcion de al especificacion</p>';
            }
        } else {
            $success = false;
            $errores = '';
            foreach ($validaDataGeneral->errors()->all() as $mi_error) {
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

    public function ver_especificaciones(Request $request)
    {

        return view('adminlte.gestion.postcocecha.clientes.partials.list_especificaciones', [
            'listar_todas' => $request->listar_todas
        ]);
    }

    public function listar_especificaciones(Request $request)
    {

        $listado = DB::table('especificacion as e')->where([
            ['tipo', 'N'],
            ['e.estado', 1]
        ])->orderBy('nombre', 'asc');
        if ($request->listar_todas != true) {
            $listado->join('cliente_pedido_especificacion as cpe', 'e.id_especificacion', 'cpe.id_especificacion')
                ->where([
                    ['cpe.id_cliente', $request->id_cliente],
                    ['e.estado', 1]
                ]);
        }

        $datos = [
            'listado' => $listado->paginate(20),
            'id_especificaciones' => ClientePedidoEspecificacion::where('id_cliente', $request->id_cliente)->select('id_especificacion')->get()
        ];
        return view('adminlte.gestion.postcocecha.clientes.partials.table_especificaciones', $datos);
    }

    public function update_especificaciones(Request $request)
    {
        $objEspecificaciones = Especificacion::find($request->id_especificacion);
        $objEspecificaciones->estado = ($request->estado == 1) ?  0 :  1;
        if ($objEspecificaciones->save()) {
            $success = true;
            $msg = '<div class="alert alert-success text-center">' .
                '<p> Se ha actualizado exitosamente el estado</p>'
                . '</div>';
        } else {
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Hubo un error al actualizar el estado, intente nuevamente</p>'
                . '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public  function asignar_especificacion(Request $request)
    {

        $success = false;
        $msg = '<div class="alert alert-success text-center">' .
            '<p> Hubo un error al procesar la peticion, intente nuevamente</p>'
            . '</div>';

        if ($request->accion == 1) {

            $existClienteEspecificacion =  DB::table('cliente_pedido_especificacion')->where([
                ['id_cliente', $request->id_cliente],
                ['id_especificacion',  $request->id_especificacion]
            ])->get();

            if (count($existClienteEspecificacion) > 0) {
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> La especificacion ya esta asignada a este cliente</p>'
                    . '</div>';
            } else {
                $objClientePedidoEspecificacion = new ClientePedidoEspecificacion;
                $cpe = ClientePedidoEspecificacion::orderBy('id_cliente_pedido_especificacion', 'desc')->first();
                $objClientePedidoEspecificacion->id_cliente_pedido_especificacion = isset($cpe->id_cliente_pedido_especificacion) ? $cpe->id_cliente_pedido_especificacion + 1 : 1;
                $objClientePedidoEspecificacion->id_cliente          = $request->id_cliente;
                $objClientePedidoEspecificacion->id_especificacion = $request->id_especificacion;

                if ($objClientePedidoEspecificacion->save()) {
                    $success = true;
                    $msg = '<div class="alert alert-success text-center">' .
                        '<p> Se ha agregado exitosamente la especificacion al cliente</p>'
                        . '</div>';
                }
            }
        } else {
            $objClientePedidoEspecificacion =  ClientePedidoEspecificacion::where([
                ['id_cliente', $request->id_cliente],
                ['id_especificacion',  $request->id_especificacion]
            ]);

            if ($objClientePedidoEspecificacion->delete()) {
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se ha eliminado la especificacion del cliente exitosamente</p>'
                    . '</div>';
            }
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function obtener_calsificacion_ramos(Request $request)
    {
        return UnidadMedida::where('tipo', $request->tipo_unidad_medida)
            ->join('clasificacion_ramo as cr', 'unidad_medida.id_unidad_medida', '=', 'cr.id_unidad_medida')
            ->get();
    }

    public function eliminar_especificaciones_masivamente(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'especificaciones' => 'required|array'
        ], [
            'especificaciones.required' => 'Debe seleccionar almenos 1 especificacion para eliminar',
            'especificaciones.array' => 'Las especificaciones deben ser una coleccion de datos'
        ]);

        if (!$valid->fails()) {

            DB::beginTransaction();

            try {

                foreach ($request->especificaciones as $idEsp) {

                    $objEspecificaciones = Especificacion::find($idEsp);
                    $objEspecificaciones->estado = ($request->estado == '1') ?  0 :  1;
                    $objEspecificaciones->save();
                }

                DB::commit();

                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se ha eliminado la especificacion del cliente exitosamente</p>'
                    . '</div>';
            } catch (\Exception $e) {

                DB::rollBack();
                $success = false;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Hubo un error al procesar la peticion</p>'
                    . '<p>' . $e->getMessage() . ' ' . $e->getLine() . '</p>'
                    . '</div>';
            }
        } else {
            $success = false;
            $errores = '';
            foreach ($valid->errors()->all() as $mi_error) {
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

    public function getVariedadesByPlanta(Request $request)
    {
        $idVariedad = Variedad::where('id_planta', $request->id_planta)->select('id_variedad')->get()->pluck('id_variedad')->toArray();

        $clasificacion_ramo = DB::table('cliente_pedido_especificacion as cpe')
            ->join('especificacion as esp', 'cpe.id_especificacion', 'esp.id_especificacion')
            ->join('especificacion_empaque as esp_emp', 'esp.id_especificacion', 'esp_emp.id_especificacion')
            ->join('detalle_especificacionempaque as det_esp_emp', 'esp_emp.id_especificacion_empaque', 'det_esp_emp.id_especificacion_empaque')
            ->join('clasificacion_ramo as cr', 'det_esp_emp.id_clasificacion_ramo', 'cr.id_clasificacion_ramo')
            ->where('esp.estado', true)->where('esp.tipo', 'N')->where('esp.creada', 'PRE-ESTABLECIDA')
            ->where('esp_emp.estado', true)->where('cr.estado', true)
            ->where('cpe.id_cliente', $request->id_cliente)
            ->select('cr.nombre', 'cr.id_clasificacion_ramo')->distinct()->get();

        $empaques = DB::table('cliente_pedido_especificacion as cpe')
            ->join('especificacion as esp', 'cpe.id_especificacion', 'esp.id_especificacion')
            ->join('especificacion_empaque as esp_emp', 'esp.id_especificacion', 'esp_emp.id_especificacion')
            ->join('empaque as emp', 'emp.id_empaque', 'esp_emp.id_empaque')
            ->join('detalle_especificacionempaque as det_esp_emp', 'esp_emp.id_especificacion_empaque', 'det_esp_emp.id_especificacion_empaque')
            ->join('variedad as v', 'det_esp_emp.id_variedad', 'v.id_variedad')
            ->where('esp.estado', true)->where('esp.tipo', 'N')->where('esp.creada', 'PRE-ESTABLECIDA')
            ->where('emp.tipo', 'C')->where('emp.estado', true)
            ->where('cpe.id_cliente', $request->id_cliente)
            ->where('v.id_planta', $request->id_planta)
            ->whereIn('det_esp_emp.id_variedad', $idVariedad)
            ->select('emp.nombre', 'emp.id_empaque', 'emp.siglas')->distinct()->get();

        $presentaciones = DB::table('cliente_pedido_especificacion as cpe')
            ->join('especificacion as esp', 'cpe.id_especificacion', 'esp.id_especificacion')
            ->join('especificacion_empaque as esp_emp', 'esp.id_especificacion', 'esp_emp.id_especificacion')
            ->join('detalle_especificacionempaque as det_esp_emp', 'esp_emp.id_especificacion_empaque', 'det_esp_emp.id_especificacion_empaque')
            ->join('empaque as emp', 'emp.id_empaque', 'det_esp_emp.id_empaque_p')
            ->join('variedad as v', 'det_esp_emp.id_variedad', 'v.id_variedad')
            ->join('planta as p', 'v.id_planta', 'p.id_planta')
            ->where('esp.estado', true)->where('esp.tipo', 'N')->where('esp.creada', 'PRE-ESTABLECIDA')
            ->where('emp.tipo', 'P')->where('emp.estado', true)
            ->where('esp_emp.estado', true)->where('det_esp_emp.estado', true)
            ->where('cpe.id_cliente', $request->id_cliente)
            ->where('p.id_planta', $request->id_planta)
            ->whereIn('det_esp_emp.id_variedad', $idVariedad)
            ->where(function ($w) use ($empaques) {

                if (count($empaques)) {

                    $w->where('esp_emp.id_empaque', $empaques[0]->id_empaque);
                }
            })->select('det_esp_emp.id_empaque_p as id_empaque', 'emp.nombre')->distinct()->get();
        //dd($request->id_cliente, $request->id_planta, $idVariedad, $empaques[0]->id_empaque, $presentaciones);

        return [
            'orden_variedades' => Variedad::where([
                ['estado', true],
                ['id_planta', $request->id_planta]
            ])->orderBy('orden', 'asc')->get(),
            'clasificacion_ramo' => $clasificacion_ramo,
            'empaques' => $empaques,
            'presentaciones' => $presentaciones
        ];
    }

    public function eliminar_detalle_pedido(Request $request)
    {
        try {

            DB::beginTransaction();

            $det_ped = DetallePedido::where('id_detalle_pedido', $request->id_detalle_pedido)->first();
            $cant_det_ped = DetallePedido::where('id_pedido', $det_ped->id_pedido)->count();
            $pedido = $det_ped->pedido;
            if (hoy() == $pedido->fecha_pedido || hoy() == opDiasFecha('-', 1, $pedido->fecha_pedido)) {

                foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {

                    foreach ($esp_emp->detalles as $det_esp_emp) {
                        $pedidoModificacion = new PedidoModificacion;
                        $pedidoModificacion->fecha_nuevo_pedido = $pedido->fecha_pedido;
                        $pedidoModificacion->id_cliente = $pedido->id_cliente;
                        $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                        $pedidoModificacion->fecha_anterior_pedido = $pedido->fecha_pedido;
                        $pedidoModificacion->cantidad = $det_ped->cantidad;
                        $pedidoModificacion->operador = '-';
                        $pedidoModificacion->id_usuario = Session::get('id_usuario');
                        $getRamosXCajaModificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);
                        $ramos_x_caja = isset($getRamosXCajaModificado) ? $getRamosXCajaModificado->cantidad : $det_esp_emp->cantidad;
                        $pedidoModificacion->ramos_x_caja = $ramos_x_caja;
                        $pedidoModificacion->save();

                        $distribuciones = DistribucionMixtos::where('id_detalle_especificacionempaque', $det_esp_emp->id_detalle_especificacionempaque)
                            ->where('id_pedido', $pedido->id_pedido)
                            ->where('id_cliente', $pedido->id_cliente)
                            ->where('id_detalle_pedido', $det_ped->id_detalle_pedido)
                            ->where('ramos', '>', 0)
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

            if ($request->registra_perdido == 'SI') {

                $pedido = Pedido::find($det_ped->id_pedido);
                $pedidoPerdido = new PedidoPerdido;
                $pedidoPerdido->id_cliente = $pedido->id_cliente;
                $pedidoPerdido->fecha_pedido = $pedido->fecha_pedido;
                $pedidoPerdido->id_usuario = session('id_usuario');
                $pedidoPerdido->fecha_registro = now()->toDateTimeString();
                $pedidoPerdido->save();

                $model = PedidoPerdido::orderBy('id_pedido_perdido', 'desc')->first();
                $detallePedido = DetallePedido::find($request->id_detalle_pedido);
                $detallePedidoPerdido = new DetallePedidoPerdido;
                $detallePedidoPerdido->id_cliente_especificacion = $detallePedido->id_cliente_especificacion;
                $detallePedidoPerdido->id_pedido_perdido = $model->id_pedido_perdido;
                $detallePedidoPerdido->id_agencia_carga = $detallePedido->id_agencia_carga;
                $detallePedidoPerdido->cantidad = $detallePedido->cantidad;
                $detallePedidoPerdido->precio = $detallePedido->precio;
                $detallePedidoPerdido->fecha_registro = now()->toDateTimeString();
                $detallePedidoPerdido->save();
                $detallePedidoPerdido = DetallePedidoPerdido::All()->last();

                foreach ($detallePedido->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                    foreach ($esp_emp->detalles as $det_esp_emp) {
                        $getRamosXCajaModificado = getRamosXCajaModificado($detallePedido->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);
                        $ramos_x_caja = isset($getRamosXCajaModificado) ? $getRamosXCajaModificado->cantidad : $det_esp_emp->cantidad;
                        $objDetEspEmpRxC = new DetalleEspecificacionEmpaqueRamosXCajaPerdido();
                        $detEspEmpRxC = DetalleEspecificacionEmpaqueRamosXCajaPerdido::orderBy('id_detalle_especificacionempaque_ramos_x_caja_perdido', 'desc')->first();
                        $objDetEspEmpRxC->id_detalle_especificacionempaque_ramos_x_caja_perdido = isset($detEspEmpRxC->id_detalle_especificacionempaque_ramos_x_caja_perdido) ? $detEspEmpRxC->id_detalle_especificacionempaque_ramos_x_caja_perdido + 1 : 1;
                        $objDetEspEmpRxC->id_detalle_pedido_perdido = $detallePedidoPerdido->id_detalle_pedido_perdido;
                        $objDetEspEmpRxC->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                        $objDetEspEmpRxC->cantidad = $ramos_x_caja;
                        $objDetEspEmpRxC->save();
                    }
                }
            }

            DetallePedido::destroy($request->id_detalle_pedido);

            if ($cant_det_ped == 1)
                Pedido::destroy($det_ped->id_pedido);

            $msg = 'Se ha eliminado el detalle del pedido';
            $success = true;
            DB::commit();
        } catch (\Exception $e) {

            $success = false;
            $msg = 'Ha ocurrido un error al eliminar el detalle del pedido ' . $e->getMessage() . ' ' . $e->getLine() . ' ' . $e->getFile();
            DB::rollBack();
        }

        return [
            'cant_det_ped' => $cant_det_ped,
            'success' => $success,
            'mensaje' => $msg
        ];
    }

    public function getLongitudEspecificacionCombo(Request $request)
    {
        $a = DB::table('cliente_pedido_especificacion as cpe')
            ->join('especificacion as esp', 'cpe.id_especificacion', 'esp.id_especificacion')
            ->join('especificacion_empaque as esp_emp', 'esp.id_especificacion', 'esp_emp.id_especificacion')
            ->join('detalle_especificacionempaque as det_esp_emp', 'esp_emp.id_especificacion_empaque', 'det_esp_emp.id_especificacion_empaque')
            ->join('clasificacion_ramo as cr', 'det_esp_emp.id_clasificacion_ramo', 'cr.id_clasificacion_ramo')
            ->where('esp.estado', true)->where('esp.tipo', 'N')->where('esp.creada', 'PRE-ESTABLECIDA')
            ->where('esp_emp.estado', true)->where('cr.estado', true)
            ->where('cpe.id_cliente', $request->id_cliente)
            ->where('det_esp_emp.id_variedad', $request->id_variedad)
            ->select('det_esp_emp.longitud_ramo', 'det_esp_emp.tallos_x_ramos')->distinct()->first();

        return [
            'datos' => $a
        ];
    }

    public function setPresentacionCombo(Request $request)
    {

        $pres = [];
        foreach ($request->planta_variedad as $pv) {

            $presentaciones = DB::table('cliente_pedido_especificacion as cpe')
                ->join('especificacion as esp', 'cpe.id_especificacion', 'esp.id_especificacion')
                ->join('especificacion_empaque as esp_emp', 'esp.id_especificacion', 'esp_emp.id_especificacion')
                ->join('detalle_especificacionempaque as det_esp_emp', 'esp_emp.id_especificacion_empaque', 'det_esp_emp.id_especificacion_empaque')
                ->join('empaque as emp', 'emp.id_empaque', 'det_esp_emp.id_empaque_p')
                ->join('variedad as v', 'det_esp_emp.id_variedad', 'v.id_variedad')
                ->join('planta as p', 'v.id_planta', 'p.id_planta')
                ->where('esp.estado', true)->where('esp.tipo', 'N')->where('esp.creada', 'PRE-ESTABLECIDA')
                ->where('emp.tipo', 'P')->where('emp.estado', true)
                ->where('esp_emp.estado', true)->where('det_esp_emp.estado', true)
                ->where('cpe.id_cliente', $request->id_cliente)
                ->where(function ($w) use ($pv, $request) {

                    $w->where('det_esp_emp.id_variedad', $pv['id_variedad'])
                        ->where('esp_emp.id_empaque', $request->id_empaque)
                        ->where('p.id_planta', $pv['id_planta']);
                })->select('det_esp_emp.id_empaque_p as id_empaque', 'emp.nombre')->distinct()->get();
            //dd($presentaciones, $request->all());
            $pres[] = $presentaciones;
        }


        return ['presentaciones' => $pres];
    }

    public function actualizar_especificaciones_masivamente(Request $request)
    { //dd($request->all());
        $valid = Validator::make($request->all(), [
            'especificaciones' => 'required|array'
        ], [
            'especificaciones.required' => 'Debe seleccionar almenos 1 especificacion para eliminar',
            'especificaciones.array' => 'Las especificaciones deben ser una coleccion de datos'
        ]);

        if (!$valid->fails()) {

            DB::beginTransaction();

            try {

                foreach ($request->especificaciones as $detEspEmp) {

                    $objDetEspEmp = DetalleEspecificacionEmpaque::find($detEspEmp['id_detalle_especificacionempaque']);
                    $objDetEspEmp->id_variedad = $detEspEmp['id_variedad'];
                    $objDetEspEmp->id_clasificacion_ramo = $detEspEmp['id_clasificacion_ramo'];
                    $objDetEspEmp->cantidad = $detEspEmp['ramos_x_caja'];
                    $objDetEspEmp->id_empaque_p = $detEspEmp['id_empaque_p'];
                    $objDetEspEmp->tallos_x_ramos = $detEspEmp['tallos_x_ramo'];
                    $objDetEspEmp->longitud_ramo = $detEspEmp['longitud'];
                    $objDetEspEmp->save();

                    $espEmp = EspecificacionEmpaque::find($objDetEspEmp->especificacion_empaque->id_especificacion_empaque);
                    $espEmp->id_empaque = $detEspEmp['id_caja'];
                    $espEmp->save();
                }

                DB::commit();

                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se han actualizado las especificaciones seleccionadas</p>'
                    . '</div>';
            } catch (\Exception $e) {

                DB::rollBack();
                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<p> Hubo un error al procesar la peticion</p>'
                    . '<p>' . $e->getMessage() . ' ' . $e->getLine() . '</p>'
                    . '</div>';
            }
        } else {
            $success = false;
            $errores = '';
            foreach ($valid->errors()->all() as $mi_error) {
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

    public function get_variedades_by_planta_editar_pedido(Request $request)
    {
        $idVariedad = Variedad::where('id_planta', $request->id_planta)->select('id_variedad')->get()->pluck('id_variedad')->toArray();

        $variedades = DB::table('cliente_pedido_especificacion as cpe')
            ->join('especificacion as esp', 'cpe.id_especificacion', 'esp.id_especificacion')
            ->join('especificacion_empaque as esp_emp', 'esp.id_especificacion', 'esp_emp.id_especificacion')
            ->join('detalle_especificacionempaque as det_esp_emp', 'esp_emp.id_especificacion_empaque', 'det_esp_emp.id_especificacion_empaque')
            ->join('variedad as v', function ($j) use ($request) {
                $j->on('det_esp_emp.id_variedad', 'v.id_variedad')->where('v.id_planta', $request->id_planta);
            })->where('esp.estado', true)
            ->where('esp.tipo', 'N')
            ->where('esp.creada', 'PRE-ESTABLECIDA')
            ->where('esp_emp.estado', true)
            ->where('cpe.id_cliente', $request->id_cliente)
            ->whereIn('det_esp_emp.id_variedad', $idVariedad)
            ->select('v.nombre', 'v.id_variedad')->distinct()->get();

        $presentaciones = DB::table('cliente_pedido_especificacion as cpe')
            ->join('especificacion as esp', 'cpe.id_especificacion', 'esp.id_especificacion')
            ->join('especificacion_empaque as esp_emp', 'esp.id_especificacion', 'esp_emp.id_especificacion')
            ->join('detalle_especificacionempaque as det_esp_emp', 'esp_emp.id_especificacion_empaque', 'det_esp_emp.id_especificacion_empaque')
            ->join('empaque as emp', 'emp.id_empaque', 'det_esp_emp.id_empaque_p')
            ->where('esp.estado', true)->where('esp.tipo', 'N')->where('esp.creada', 'PRE-ESTABLECIDA')
            ->where('emp.tipo', 'P')->where('emp.estado', true)
            ->where('esp_emp.estado', true)->where('det_esp_emp.estado', true)
            ->where('cpe.id_cliente', $request->id_cliente)
            ->select('emp.id_empaque', 'emp.nombre')->distinct()->get();

        return [
            'variedades' => $variedades,
            'presentaciones' => $presentaciones
        ];
    }

    public function crear_detalle_pedido_edicion(Request $request)
    { //dd($request->all());
        $valid = Validator::make($request->all(), [
            'id_cliente' => 'required|exists:cliente,id_cliente',
            'detalle_especificacion.detalles_especificacion_empaque' => ['required', function ($attribute, $value, $onFailure) {
                $x = 0;
                foreach ($value as $v) {

                    if (!isset($v['id_variedad']) || $v['id_variedad'] == '') {
                        $onFailure('Debe ingresar la variedad del detalle ' . ($x + 1));
                    } else if (!isset($v['id_clasificacion_ramo']) || $v['id_clasificacion_ramo'] == '') {
                        $onFailure('Debe ingresar el peso del detalle ' . ($x + 1));
                    } else if (!isset($v['cantidad']) || $v['cantidad'] == '') {
                        $onFailure('Debe ingresar los ramos x caja del detalle ' . ($x + 1));
                    } else if (!isset($v['id_empaque_p']) || $v['id_empaque_p'] == '') {
                        $onFailure('Debe ingresar la presentacion del detalle ' . ($x + 1));
                    } else if (!isset($v['tallos_x_ramos']) || $v['tallos_x_ramos'] == '') {
                        $onFailure('Debe ingresar los tallos x ramo del detalle ' . ($x + 1));
                    } else if (!isset($v['longitud_ramo']) || $v['longitud_ramo'] == '') {
                        $onFailure('Debe ingresar la longitu del detalle ' . ($x + 1));
                    }
                    $x++;
                }
            }]
        ], [
            'detalle_especificacion.required' => 'No hay detalles en el pedido',
            'id_cliente.required' => 'No se obtuvo al cliente del pedido'
        ]);

        if (!$valid->fails()) {

            DB::beginTransaction();

            try {

                $idsDetsEspemp = [];

                $idEspecificacion = Especificacion::orderBy('id_especificacion', 'desc')->first();

                $objEspecificacion = new Especificacion;
                $objEspecificacion->id_especificacion = isset($idEspecificacion) ? $idEspecificacion->id_especificacion + 1 : 1;
                $objEspecificacion->fecha_registro = now()->toDateTimeString();
                $objEspecificacion->estado = 1;
                $objEspecificacion->tipo = 'N';
                $objEspecificacion->creada = 'EJECUCION';
                $objEspecificacion->save();

                $objEspecificacionEmpaque = new EspecificacionEmpaque;

                $idEspecificacionEmpaque = EspecificacionEmpaque::orderBy('id_especificacion_empaque', 'desc')->first();

                $objEspecificacionEmpaque->id_especificacion_empaque = isset($idEspecificacionEmpaque) ? $idEspecificacionEmpaque->id_especificacion_empaque + 1 : 1;
                $objEspecificacionEmpaque->id_especificacion = $objEspecificacion->id_especificacion;
                $objEspecificacionEmpaque->id_empaque = $request->detalle_especificacion['id_empaque'];
                $objEspecificacionEmpaque->cantidad = 1;
                $objEspecificacionEmpaque->save();

                foreach ($request->detalle_especificacion['detalles_especificacion_empaque'] as $detEspEmp) {

                    $idDetEspEmp = DetalleEspecificacionEmpaque::orderBy('id_detalle_especificacionempaque', 'desc')->first();

                    $objDetEspEmp = new DetalleEspecificacionEmpaque;
                    $objDetEspEmp->id_detalle_especificacionempaque = isset($idDetEspEmp) ? $idDetEspEmp->id_detalle_especificacionempaque + 1 : 1;
                    $objDetEspEmp->id_especificacion_empaque = $objEspecificacionEmpaque->id_especificacion_empaque;
                    $objDetEspEmp->estado = 1;
                    $objDetEspEmp->id_variedad = $detEspEmp['id_variedad'];
                    $objDetEspEmp->id_clasificacion_ramo = $detEspEmp['id_clasificacion_ramo'];
                    $objDetEspEmp->cantidad = $detEspEmp['cantidad'];
                    $objDetEspEmp->id_empaque_p = $detEspEmp['id_empaque_p'];
                    $objDetEspEmp->tallos_x_ramos = $detEspEmp['tallos_x_ramos'];
                    $objDetEspEmp->longitud_ramo = $detEspEmp['longitud_ramo'];
                    $objDetEspEmp->id_unidad_medida = 1;
                    $objDetEspEmp->save();

                    $idsDetsEspemp[] = $objDetEspEmp->id_detalle_especificacionempaque;
                }

                $idCpe = ClientePedidoEspecificacion::orderBy('id_cliente_pedido_especificacion', 'desc')->first();

                $objCpe = new ClientePedidoEspecificacion;
                $objCpe->id_cliente_pedido_especificacion = isset($idCpe) ? $idCpe->id_cliente_pedido_especificacion + 1 : 1;
                $objCpe->id_cliente = $request->id_cliente;
                $objCpe->id_especificacion = $objEspecificacion->id_especificacion;
                $objCpe->estado = 1;
                $objCpe->save();

                $idClientePedidoEspecificacion = $objCpe->id_cliente_pedido_especificacion;
                $success = true;
                $msg = 'Listo para editar';

                DB::commit();
            } catch (\Exception $e) {

                DB::rollBack();
                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<p> Hubo un error al procesar la peticion</p>'
                    . '<p>' . $e->getMessage() . ' ' . $e->getLine() . '</p>'
                    . '</div>';
            }
        } else {
            $success = false;
            $errores = '';
            foreach ($valid->errors()->all() as $mi_error) {
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
            'idClientePedidoEspecificacion' => isset($idClientePedidoEspecificacion) ? $idClientePedidoEspecificacion : null,
            'idsDetsEspemp' => isset($idsDetsEspemp) ? $idsDetsEspemp : null
        ];
    }

    public function actualizar_detalle_pedido_edicion(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id_cliente' => 'required|exists:cliente,id_cliente',
            'detalle_especificacion.detalles_especificacion_empaque' => ['required', function ($attribute, $value, $onFailure) {

                $x = 0;

                foreach ($value as $v) {

                    if (!isset($v['id_variedad']) || $v['id_variedad'] == '') {
                        $onFailure('Debe ingresar la variedad del detalle ' . ($x + 1));
                    } else if (!isset($v['id_clasificacion_ramo']) || $v['id_clasificacion_ramo'] == '') {
                        $onFailure('Debe ingresar el peso del detalle ' . ($x + 1));
                    } else if (!isset($v['cantidad']) || $v['cantidad'] == '') {
                        $onFailure('Debe ingresar los ramos x caja del detalle ' . ($x + 1));
                    } else if (!isset($v['id_empaque_p']) || $v['id_empaque_p'] == '') {
                        $onFailure('Debe ingresar la presentacion del detalle ' . ($x + 1));
                    } else if (!isset($v['tallos_x_ramos']) || $v['tallos_x_ramos'] == '') {
                        $onFailure('Debe ingresar los tallos x ramo del detalle ' . ($x + 1));
                    } else if (!isset($v['longitud_ramo']) || $v['longitud_ramo'] == '') {
                        $onFailure('Debe ingresar la longitud del detalle ' . ($x + 1));
                    } else if (!isset($v['id_det_esp_emp']) || $v['id_det_esp_emp'] == '') {
                        $onFailure('No se obtuvo el detalle de la especifiacion ' . ($x + 1));
                    }

                    $x++;
                }
            }]
        ], [
            'detalle_especificacion.required' => 'No hay detalles en el pedido',
            'id_cliente.required' => 'No se obtuvo al cliente del pedido'
        ]);

        if (!$valid->fails()) {

            DB::beginTransaction();

            try {

                foreach ($request->detalle_especificacion['detalles_especificacion_empaque'] as $det_esp_emp) {

                    $objDetEspEmp = DetalleEspecificacionEmpaque::find($det_esp_emp['id_det_esp_emp']);
                    $objDetEspEmp->id_variedad = $det_esp_emp['id_variedad'];
                    $objDetEspEmp->id_clasificacion_ramo = $det_esp_emp['id_clasificacion_ramo'];
                    $objDetEspEmp->cantidad = $det_esp_emp['cantidad'];
                    $objDetEspEmp->id_empaque_p = $det_esp_emp['id_empaque_p'];
                    $objDetEspEmp->tallos_x_ramos = $det_esp_emp['tallos_x_ramos'];
                    $objDetEspEmp->longitud_ramo = $det_esp_emp['longitud_ramo'];
                    $objDetEspEmp->save();

                    //dump($det_esp_emp['id_variedad']);

                }

                $objEspEmp = EspecificacionEmpaque::find($objDetEspEmp->especificacion_empaque->id_especificacion_empaque);
                $objEspEmp->id_empaque = $request->detalle_especificacion['id_empaque'];
                $objEspEmp->save();

                $msg = 'No olvide guardar el pedido';
                $success = true;

                DB::commit();
            } catch (\Exception $e) {

                DB::rollBack();
                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<p> Hubo un error al procesar la peticion</p>'
                    . '<p>' . $e->getMessage() . ' ' . $e->getLine() . '</p>'
                    . '</div>';
            }
        } else {
            $success = false;
            $errores = '';
            foreach ($valid->errors()->all() as $mi_error) {
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
}
