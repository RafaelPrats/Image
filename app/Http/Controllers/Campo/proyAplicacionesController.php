<?php

namespace yura\Http\Controllers\Campo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Aplicacion;
use yura\Modelos\DetalleProyeccionCampoSemanalAplicacion;
use yura\Modelos\Modulo;
use yura\Modelos\Planta;
use yura\Modelos\ProyeccionCampoSemanal;
use yura\Modelos\ProyeccionCampoSemanalAplicacion;
use yura\Modelos\Submenu;
use Validator;
use yura\Modelos\UnidadMedida;

class proyAplicacionesController extends Controller
{
    public function inicio(Request $request)
    {
        $hasta = getSemanaByDate(opDiasFecha('+', 98, date('Y-m-d')));
        $plantas = Planta::where('estado', 1)->orderBy('nombre')->get();
        return view('adminlte.gestion.campo.proyecciones.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semana_hasta' => $hasta,
            'plantas' => $plantas,
        ]);
    }

    public function buscar_listado(Request $request)
    {
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('codigo', '>=', $request->desde)
            ->where('codigo', '<=', $request->hasta)
            ->where('estado', 1)
            ->orderBy('codigo')
            ->get();
        if ($request->reporte == 'M') { // listar x módulos
            $resumen_semana = [];
            $matriz = [];
            if (count($semanas) > 0) {
                $semana_desde = $semanas[0];
                $semana_hasta = $semanas[count($semanas) - 1];

                $modulos = DB::table('ciclo as c')
                    ->join('modulo as m', 'm.id_modulo', '=', 'c.id_modulo')
                    ->select('c.id_modulo', 'm.nombre')->distinct()
                    ->where('c.estado', 1)
                    ->where('c.activo', 1)
                    ->where('c.id_variedad', $request->variedad)
                    ->Where(function ($q) use ($semana_desde, $semana_hasta) {
                        $q->where('c.fecha_fin', '>=', $semana_desde->fecha_inicial)
                            ->where('c.fecha_fin', '<=', $semana_hasta->fecha_final)
                            ->orWhere(function ($q) use ($semana_desde, $semana_hasta) {
                                $q->where('c.fecha_inicio', '>=', $semana_desde->fecha_inicial)
                                    ->where('c.fecha_inicio', '<=', $semana_hasta->fecha_final);
                            })
                            ->orWhere(function ($q) use ($semana_desde, $semana_hasta) {
                                $q->where('c.fecha_inicio', '<', $semana_desde->fecha_inicial)
                                    ->where('c.fecha_fin', '>', $semana_hasta->fecha_final);
                            });
                    });
                $modulos = $modulos->orderBy('fecha_inicio')
                    ->get();

                $ids_modulos = [];
                foreach ($modulos as $m) {
                    $proys = ProyeccionCampoSemanal::where('id_modulo', $m->id_modulo)
                        ->where('semana', '>=', $request->desde)
                        ->where('semana', '<=', $request->hasta)
                        ->where('id_variedad', $request->variedad)
                        ->orderBy('semana')
                        ->get();
                    array_push($matriz, [
                        'm' => $m,
                        'proys' => $proys,
                    ]);
                    $ids_modulos[] = $m->id_modulo;
                }

                foreach ($semanas as $sem) {
                    $labores = DB::table('proyeccion_campo_semanal_aplicacion as pa')
                        ->join('proyeccion_campo_semanal as p', 'p.id_proyeccion_campo_semanal', '=', 'pa.id_proyeccion_campo_semanal')
                        ->select('pa.app_nombre')->distinct()
                        ->where('pa.app_uso', $request->uso)
                        ->where('p.semana', $sem->codigo)
                        ->where('p.id_variedad', $request->variedad)
                        ->whereIn('p.id_modulo', $ids_modulos)
                        ->orderBy('pa.app_nombre')
                        ->get();
                    $resumen_semana[] = $labores;
                }
            }

            $view = 'listado';
            $datos = [
                'matriz' => $matriz,
                'semanas' => $semanas,
                'resumen_semana' => $resumen_semana,
            ];
        }

        if ($request->reporte == 'L') { // listar x labores
            $labores = DB::table('proyeccion_campo_semanal_aplicacion as pa')
                ->join('proyeccion_campo_semanal as p', 'p.id_proyeccion_campo_semanal', '=', 'pa.id_proyeccion_campo_semanal')
                ->select('pa.app_nombre')->distinct()
                ->where('pa.estado', '!=', 'C')
                ->where('pa.app_uso', $request->uso)
                ->where('p.id_variedad', $request->variedad)
                ->where('p.semana', '>=', $request->desde)
                ->where('p.semana', '<=', $request->hasta)
                ->orderBy('pa.app_nombre')
                ->get();

            $matriz = [];
            foreach ($labores as $pos => $item) {
                $modulos = [];
                foreach ($semanas as $sem) {
                    $query = DB::table('proyeccion_campo_semanal_aplicacion as pa')
                        ->join('proyeccion_campo_semanal as p', 'p.id_proyeccion_campo_semanal', '=', 'pa.id_proyeccion_campo_semanal')
                        ->join('modulo as m', 'm.id_modulo', '=', 'p.id_modulo')
                        ->select('p.id_modulo', 'm.nombre', 'p.semana')->distinct()
                        ->where('pa.estado', '!=', 'C')
                        ->where('pa.app_uso', $request->uso)
                        ->where('pa.app_nombre', $item->app_nombre)
                        ->where('p.id_variedad', $request->variedad)
                        ->where('p.semana', $sem->codigo)
                        ->orderBy('m.nombre')
                        ->get();
                    array_push($modulos, $query);
                }

                array_push($matriz, [
                    'app_nombre' => $item->app_nombre,
                    'modulos' => $modulos,
                ]);
            }

            $view = 'listado_x_labores';
            $datos = [
                'semanas' => $semanas,
                'matriz' => $matriz,
            ];
        }

        return view('adminlte.gestion.campo.proyecciones.partials.' . $view, $datos);
    }

    public function select_celda(Request $request)
    {
        $proy = ProyeccionCampoSemanal::All()
            ->where('id_modulo', $request->mod)
            ->where('semana', $request->sem)
            ->where('id_variedad', $request->variedad)
            ->first();
        return view('adminlte.gestion.campo.proyecciones.forms.select_celda', [
            'proy' => $proy,
            'aplicaciones' => $proy != '' ? $proy->aplicaciones : [],
            'modulo' => Modulo::find($request->mod),
            'semana' => $request->sem,
            'variedad' => $request->variedad,
        ]);
    }

    public function cargar_labor_semanal(Request $request)
    {
        $semana = getObjSemana($request->semana);
        $semana_actual = getSemanaByDate(hoy());
        $modulos = DB::table('ciclo as c')
            ->join('modulo as m', 'm.id_modulo', '=', 'c.id_modulo')
            ->select('c.id_modulo', 'm.nombre')->distinct()
            ->where('c.estado', 1)
            ->where('c.id_variedad', $request->variedad)
            ->Where(function ($q) use ($semana) {
                $q->where('c.fecha_fin', '>=', $semana->fecha_inicial)
                    ->where('c.fecha_fin', '<=', $semana->fecha_final)
                    ->orWhere(function ($q) use ($semana) {
                        $q->where('c.fecha_inicio', '>=', $semana->fecha_inicial)
                            ->where('c.fecha_inicio', '<=', $semana->fecha_final);
                    })
                    ->orWhere(function ($q) use ($semana) {
                        $q->where('c.fecha_inicio', '<', $semana->fecha_inicial)
                            ->where('c.fecha_fin', '>', $semana->fecha_final);
                    });
            })
            ->where('c.activo', 1)
            ->orderBy('c.fecha_inicio')
            ->get();
        $ids_modulos = [];
        foreach ($modulos as $m)
            $ids_modulos[] = $m->id_modulo;

        $listado = ProyeccionCampoSemanalAplicacion::join('proyeccion_campo_semanal as p', 'p.id_proyeccion_campo_semanal', '=', 'proyeccion_campo_semanal_aplicacion.id_proyeccion_campo_semanal')
            ->join('modulo as mod', 'mod.id_modulo', '=', 'p.id_modulo')
            ->select('mod.nombre', 'p.num_sem', 'p.poda_siembra', 'proyeccion_campo_semanal_aplicacion.*');
        if ($semana->codigo <= $semana_actual->codigo)
            $listado = $listado->whereIn('p.id_modulo', $ids_modulos);
        $listado = $listado->where('p.id_variedad', $request->variedad)
            ->where('p.semana', $semana->codigo)
            ->where('proyeccion_campo_semanal_aplicacion.app_nombre', $request->nombre)
            ->orderBy('mod.nombre', 'asc')
            ->orderBy('proyeccion_campo_semanal_aplicacion.fecha', 'asc')
            ->get();

        $detalles = DB::table('detalle_proyeccion_campo_semanal_aplicacion as det')
            ->join('proyeccion_campo_semanal_aplicacion as pa', 'pa.id_proyeccion_campo_semanal_aplicacion', '=', 'det.id_proyeccion_campo_semanal_aplicacion')
            ->join('proyeccion_campo_semanal as p', 'p.id_proyeccion_campo_semanal', '=', 'pa.id_proyeccion_campo_semanal')
            ->leftJoin('mano_obra as mo', 'mo.id_mano_obra', '=', 'det.id_mano_obra')
            ->leftJoin('producto as ins', 'ins.id_producto', '=', 'det.id_producto')
            ->select('det.id_producto', 'ins.nombre as producto', 'det.id_mano_obra', 'mo.nombre as mano_obra')->distinct()
            ->where('p.semana', $semana->codigo)
            ->where('pa.app_nombre', $request->nombre)
            ->orderBy('ins.nombre', 'mo.nombre')
            ->get();

        $unidades_medida = UnidadMedida::where('estado', 1)->orderBy('siglas')->get();
        return view('adminlte.gestion.campo.proyecciones.forms.cargar_labor_semanal', [
            'listado' => $listado,
            'semana' => $semana,
            'app_nombre' => $request->nombre,
            'app_uso' => $request->uso,
            'detalles' => $detalles,
            'unidades_medida' => $unidades_medida,
        ]);
    }

    public function add_adicional(Request $request)
    {
        $modulos = DB::table('proyeccion_campo_semanal as p')
            ->join('modulo as m', 'm.id_modulo', '=', 'p.id_modulo')
            ->select('p.id_proyeccion_campo_semanal', 'p.id_modulo', 'm.nombre', 'p.num_sem', 'm.area')
            ->where('p.id_variedad', $request->variedad)
            ->where('p.semana', $request->semana)
            ->orderBy('m.nombre')
            ->orderBy('p.num_sem')
            ->get();
        $aplicacion = Aplicacion::All()
            ->where('nombre', $request->app_nombre)
            ->where('estado', 1)
            ->first();
        $detalles = $aplicacion->getDetallesParametrizados();
        $unidades_medida = UnidadMedida::where('estado', 1)->orderBy('siglas')->get();
        return view('adminlte.gestion.campo.proyecciones.forms.add_adicional', [
            'modulos' => $modulos,
            'aplicacion' => $aplicacion,
            'semana' => getObjSemana($request->semana),
            'app_nombre' => $request->app_nombre,
            'app_uso' => $request->uso,
            'detalles' => $detalles,
            'unidades_medida' => $unidades_medida,
        ]);
    }

    public function cambiar_estado_labor(Request $request)
    {
        $proy_app = ProyeccionCampoSemanalAplicacion::find($request->proy_app);
        if ($request->estado == 'E') {   //  ejectuar
            $proy_app->fecha = $request->fecha;
            $proy_app->app_repeticion = $request->repeticion;
            $proy_app->app_litro_x_cama = $request->litro_x_cama;
            $proy_app->camas = $request->camas;
            $proy_app->horas_trabajo = $request->horas_trabajo;
        }
        $proy_app->estado = $request->estado;
        $proy_app->save();

        return [
            'success' => true,
            'mensaje' => '',
        ];
    }

    public function cambiar_estado_labor_continua(Request $request)
    {
        foreach ($request->data as $item) {
            $proy_app = ProyeccionCampoSemanalAplicacion::find($item['proy_app']);
            if ($item['estado'] == 'E') {   //  ejectuar
                $proy_app->fecha = $item['fecha'];
                $proy_app->app_repeticion = $item['repeticion'];
                $proy_app->app_litro_x_cama = $item['litro_x_cama'];
                $proy_app->camas = $item['camas'];
                $proy_app->horas_trabajo = $item['horas_trabajo'];
            }
            $proy_app->estado = $item['estado'];
            $proy_app->save();
        }
        return [
            'success' => true,
            'mensaje' => '',
        ];
    }

    public function modificar_labor(Request $request)
    {
        $proy_app = ProyeccionCampoSemanalAplicacion::find($request->proy_app);
        $proy_app->app_repeticion = $request->repeticion;
        $proy_app->app_litro_x_cama = $request->litro_x_cama;
        $proy_app->camas = $request->camas;
        $proy_app->horas_trabajo = $request->horas_trabajo;
        if ($proy_app->estado == 'P')
            $proy_app->estado = 'M';
        $proy_app->save();

        $fecha_proy_app = $proy_app->fecha;
        if ($fecha_proy_app != $request->fecha) {
            $proy_campo = $proy_app->proyeccion_campo_semanal;
            $semana = getObjSemana($proy_campo->semana);
            $semana_req = getSemanaByDate($request->fecha);
            if ($semana->codigo != $semana_req->codigo) {   // hay que mover la aplicacion como ADICIONAL a la semana $semana_req
                if ($semana_req->codigo < $semana->codigo) {   // hacia adelante
                    if ($proy_app->app_repeticion > 1) {    // no es la primera repeticion
                        $anteriores = DB::table('proyeccion_campo_semanal_aplicacion as pa')
                            ->join('proyeccion_campo_semanal as p', 'p.id_proyeccion_campo_semanal', '=', 'pa.id_proyeccion_campo_semanal')
                            ->select('pa.*')->distinct()
                            ->where('pa.app_nombre', $proy_app->app_nombre)
                            ->whereIn('pa.estado', ['E', 'X', 'P', 'M'])
                            ->where('p.semana', '>=', $semana_req->codigo)
                            ->where('p.semana', '<', $semana->codigo)
                            ->where('p.id_modulo', $proy_campo->id_modulo)
                            ->where('p.id_variedad', $proy_campo->id_variedad)
                            ->get();
                        if (count($anteriores) == 0) {  // no existen labores anteriores en el rango de semanas
                            $this->mover_labores($proy_app, $semana_req, $proy_campo->id_modulo, $proy_campo->id_variedad, $semana, $request->fecha);
                        } else {
                            return [
                                'success' => false,
                                'mensaje' => '<div class="alert alert-warning text-center">No se puede mover porque existen labores previas en el rango especificado</div>',
                            ];
                        }
                    } else {    // es la primera repeticion
                        $this->mover_labores($proy_app, $semana_req, $proy_campo->id_modulo, $proy_campo->id_variedad, $semana, $request->fecha);
                    }
                } else {    // hacia atras
                    $this->mover_labores($proy_app, $semana_req, $proy_campo->id_modulo, $proy_campo->id_variedad, $semana, $request->fecha);
                }
            }
        }

        return [
            'success' => true,
            'mensaje' => '',
        ];
    }

    public function store_adicional(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'id_proyeccion_campo_semanal' => 'required',
            'repeticion' => 'required',
            'estado' => 'required',
        ], [
            'id_proyeccion_campo_semanal.required' => 'El módulo es obligatorio',
            'estado.required' => 'El estado es obligatorio',
            'repeticion.required' => 'La repetición es obligatoria',
        ]);
        if (!$valida->fails()) {
            $model = new ProyeccionCampoSemanalAplicacion();
            $model->id_proyeccion_campo_semanal = $request->id_proyeccion_campo_semanal;
            $model->app_nombre = $request->app_nombre;
            $model->app_uso = $request->app_uso;
            $model->fecha = $request->fecha;
            $model->estado = $request->estado;
            $model->app_repeticion = $request->repeticion;
            $model->app_litro_x_cama = $request->litro_x_cama;
            $model->camas = $request->camas;
            $model->horas_trabajo = $request->horas_trabajo;
            if ($model->save()) {
                $model = ProyeccionCampoSemanalAplicacion::All()->last();
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se ha agregado la aplicación satisfactoriamente</p>'
                    . '</div>';
                bitacora('proyeccion_campo_semanal_aplicacion', $model->id_proyeccion_campo_semanal_aplicacion, 'I', 'Inserción satisfactoria de una nueva aplicación');

                /* AGREGAR DETALLES en la tabla detalle_proyeccion_campo_semanal_aplicacion */
                foreach ($request->detalles as $det) {
                    $model_det = new DetalleProyeccionCampoSemanalAplicacion();
                    $model_det->id_proyeccion_campo_semanal_aplicacion = $model->id_proyeccion_campo_semanal_aplicacion;
                    $model_det->id_mano_obra = $det['id_mo'];
                    $model_det->id_producto = $det['id_producto'];
                    $model_det->dosis = $det['dosis'];
                    $model_det->rendimiento = $det['rendimiento'];
                    $model_det->id_unidad_medida = $det['id_um'];
                    $model_det->factor_conversion = $det['factor_conversion'];
                    $model_det->id_unidad_conversion = $det['id_um_conversion'];
                    $model_det->save();
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
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

    public function update_detalle_app(Request $request)
    {
        $model = DetalleProyeccionCampoSemanalAplicacion::All()
            ->where('id_proyeccion_campo_semanal_aplicacion', $request->id_proy)
            ->where('id_mano_obra', $request->mo)
            ->where('id_producto', $request->prod)
            ->first();
        if ($model == '') {
            $model = new DetalleProyeccionCampoSemanalAplicacion();
            $model->id_proyeccion_campo_semanal_aplicacion = $request->id_proy;
            $model->id_mano_obra = $request->mo;
            $model->id_producto = $request->prod;
        }
        if ($request->campo == 'unidad_medida')
            $model->id_unidad_medida = $request->valor;
        if ($request->campo == 'valor')
            if ($request->id_mano_obra != '')   // rendimiento MO
                $model->rendimiento = $request->valor;
            else    // dosis insumo
                $model->dosis = $request->valor;

        if ($model->save()) {
            $success = true;
            $msg = '';
        } else {
            $success = false;
            $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la información</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    /* --------------------------------------------------------------------------- */
    function mover_labores($proy_app, $semana_new, $id_modulo, $id_variedad, $old_semana, $fecha)
    {
        $aplicacion = Aplicacion::All()
            ->where('nombre', $proy_app->app_nombre)
            ->where('estado', 1)
            ->first();
        $sem_last_app = getSemanaByDate(opDiasFecha('+', 7 * ($aplicacion->repeticiones - $proy_app->app_repeticion), $old_semana->fecha_inicial));
        $posteriores = ProyeccionCampoSemanalAplicacion::join('proyeccion_campo_semanal as p', 'p.id_proyeccion_campo_semanal', '=', 'proyeccion_campo_semanal_aplicacion.id_proyeccion_campo_semanal')
            ->select('proyeccion_campo_semanal_aplicacion.*', 'p.semana')->distinct()
            ->where('proyeccion_campo_semanal_aplicacion.app_nombre', $proy_app->app_nombre)
            ->whereIn('proyeccion_campo_semanal_aplicacion.estado', ['X', 'P', 'M'])
            ->where('p.id_modulo', $id_modulo)
            ->where('p.id_variedad', $id_variedad)
            ->where('p.semana', '>=', $old_semana->codigo)
            ->where('p.semana', '<=', $sem_last_app->codigo)
            ->get();

        foreach ($posteriores as $pos_p => $item) {
            $semana_req = getSemanaByDate(opDiasFecha('+', 7 * ($pos_p + 1) - 7, $semana_new->fecha_inicial));
            $proy_campo_req = ProyeccionCampoSemanal::All()
                ->where('id_modulo', $id_modulo)
                ->where('id_variedad', $id_variedad)
                ->where('semana', $semana_req->codigo)
                ->first();

            $item->id_proyeccion_campo_semanal = $proy_campo_req->id_proyeccion_campo_semanal;
            $item->save();
        }
        $proy_app->fecha = $fecha;
        $proy_app->save();
    }
}