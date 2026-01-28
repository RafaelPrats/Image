<?php

namespace yura\Http\Controllers\Campo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Aplicacion;
use yura\Modelos\AplicacionMatriz;
use yura\Modelos\AplicacionMezcla;
use yura\Modelos\AplicacionVariedad;
use yura\Modelos\DetalleAplicacion;
use yura\Modelos\ManoObra;
use yura\Modelos\ParametroAplicacion;
use yura\Modelos\ParametroDetalleAplicacion;
use yura\Modelos\Planta;
use yura\Modelos\Producto;
use yura\Modelos\Submenu;
use Validator;
use yura\Modelos\UnidadMedida;
use yura\Modelos\Variedad;

class AplicacionesController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = Planta::where('estado', 1)->orderBy('nombre')->get();
        return view('adminlte.gestion.campo.aplicaciones.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function buscar_listado(Request $request)
    {
        $listado = Aplicacion::leftJoin('aplicacion_variedad as av', 'av.id_aplicacion', '=', 'aplicacion.id_aplicacion')
            ->leftJoin('variedad as v', 'v.id_variedad', '=', 'av.id_variedad')
            ->select('aplicacion.*')->distinct()
            ->where('aplicacion.tipo', $request->tipo);
        if ($request->variedad != 'T')
            $listado = $listado->where('v.id_variedad', $request->variedad);
        elseif ($request->planta != 'T')
            $listado = $listado->where('v.id_planta', $request->planta);
        elseif ($request->poda_siembra != 'T')
            $listado = $listado->where('aplicacion.poda_siembra', $request->poda_siembra);
        $listado = $listado->orderBy('aplicacion.nombre')->get();
        return view('adminlte.gestion.campo.aplicaciones.partials.listado', [
            'listado' => $listado,
            'tipo' => $request->tipo,
            'aplicaciones_matriz' => AplicacionMatriz::orderBy('nombre')->get(),
        ]);
    }

    public function store_app(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250|unique:aplicacion',
            'semana_ini' => 'required',
            'repeticiones' => 'required',
            'veces_x_semana' => 'required',
            'app_matriz' => 'required',
        ], [
            'app_matriz.required' => 'La aplicación matriz es obligatoria',
            'nombre.required' => 'El nombre es obligatorio',
            'semana_ini.required' => 'La semana de inicio es obligatoria',
            'repeticiones.required' => 'Las repeticiones son obligatorias',
            'veces_x_semana.required' => 'Las veces por semana son obligatorias',
            'nombre.max' => 'El nombre es muy grande',
            'nombre.unique' => 'El nombre ya existe',
        ]);
        $id_model = '';
        if (!$valida->fails()) {
            $existe = Aplicacion::All()->where('nombre', str_limit(mb_strtoupper(espacios($request->nombre)), 250))->first();
            if ($existe == '') {
                $model = new Aplicacion();
                $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);
                $model->semana_ini = $request->semana_ini;
                $model->repeticiones = $request->repeticiones;
                $model->veces_x_semana = $request->veces_x_semana;
                $model->poda_siembra = $request->poda_siembra;
                $model->dia_ini = $request->dia_ini;
                $model->litro_x_cama = $request->litro_x_cama;
                $model->tipo = $request->tipo;
                $model->frecuencia = $request->frecuencia;
                $model->continua = $request->continua;
                $model->id_aplicacion_matriz = $request->app_matriz;

                if ($model->save()) {
                    $model = Aplicacion::All()->last();
                    $id_model = $model->id_aplicacion;
                    $success = true;
                    $msg = '<div class="alert alert-success text-center">' .
                        '<p> Se ha guardado una nueva aplicación satisfactoriamente</p>'
                        . '</div>';
                    bitacora('aplicacion', $model->id_aplicacion, 'I', 'Inserción satisfactoria de una nueva aplicación');
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                        . '</div>';
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<p> El nombre de la aplicación ya existe</p>'
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
            'id_model' => $id_model,
        ];
    }

    public function update_app(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250',
            'semana_ini' => 'required',
            'repeticiones' => 'required',
            'veces_x_semana' => 'required',
            'id_app' => 'required',
            'app_matriz' => 'required',
        ], [
            'app_matriz.required' => 'La aplicación matriz es obligatoria',
            'nombre.required' => 'El nombre es obligatorio',
            'id_app.required' => 'La aplicacion es obligatoria',
            'semana_ini.required' => 'La semana de inicio es obligatoria',
            'repeticiones.required' => 'Las repeticiones son obligatorias',
            'veces_x_semana.required' => 'Las veces por semana son obligatorias',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        if (!$valida->fails()) {
            $model = Aplicacion::find($request->id_app);
            $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);
            $model->semana_ini = $request->semana_ini;
            $model->repeticiones = $request->repeticiones;
            $model->veces_x_semana = $request->veces_x_semana;
            $model->poda_siembra = $request->poda_siembra;
            $model->dia_ini = $request->dia_ini;
            $model->litro_x_cama = $request->litro_x_cama;
            $model->tipo = $request->tipo;
            $model->frecuencia = $request->frecuencia;
            $model->continua = $request->continua;
            $model->id_aplicacion_matriz = $request->app_matriz;

            if ($model->save()) {
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se ha actualizado la aplicación satisfactoriamente</p>'
                    . '</div>';
                bitacora('aplicacion', $model->id_aplicacion, 'U', 'Modificacion satisfactoria de una aplicación');
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
            'success' => $success
        ];
    }

    public function get_row_listado(Request $request)
    {
        return view('adminlte.gestion.campo.aplicaciones.partials._row_listado', [
            'app' => Aplicacion::find($request->id_app),
        ]);
    }

    public function desactivar_app(Request $request)
    {
        $model = Aplicacion::find($request->id_app);
        $model->estado = $model->estado == 1 ? 0 : 1;
        $model->save();
        $borrar = false;
        if ($model->estado == 0) {
            foreach ($model->detalles as $det)
                $det->delete();
            $model->delete();
            $borrar = true;
        }
        return [
            'success' => true,
            'mensaje' => '',
            'borrar' => $borrar,
        ];
    }

    public function mezclas_app(Request $request)
    {
        $mo = ManoObra::where('estado', 1)->orderBy('nombre')->get();
        $insumos = Producto::where('estado', 1)->orderBy('nombre')->get();
        $app = AplicacionMatriz::find($request->id_app);
        $mezclas = $app->mezclas;
        return view('adminlte.gestion.campo.aplicaciones.forms.mezclas', [
            'app' => $app,
            'mezclas' => $mezclas,
            'mo' => $mo,
            'insumos' => $insumos,
        ]);
    }

    public function store_mezcla(Request $request)
    {
        $model = new AplicacionMezcla();
        $model->id_aplicacion_matriz = $request->app;
        $model->nombre = espacios(mb_strtoupper(str_limit($request->nombre, 250)));

        $model->litro_x_cama = $request->litro_x_cama;
        $array_repeticiones = [];
        foreach (explode('-', $request->repeticiones) as $pos => $r) {
            $r = intval($r);
            if (!in_array($r, $array_repeticiones)) {
                $array_repeticiones[] = $r;
            }
        }
        for ($i = 0; $i < count($array_repeticiones) - 1; $i++) {
            for ($y = $i + 1; $y < count($array_repeticiones); $y++)
                if ($array_repeticiones[$i] > $array_repeticiones[$y]) {
                    $temp = $array_repeticiones[$i];
                    $array_repeticiones[$i] = $array_repeticiones[$y];
                    $array_repeticiones[$y] = $temp;
                }
        }
        $repeticiones = '';
        foreach ($array_repeticiones as $pos => $r) {
            if ($pos == 0)
                $repeticiones = $r;
            else
                $repeticiones .= '-' . $r;
        }
        $model->repeticiones = $repeticiones;
        $array_litros = explode('-', $request->litros_x_repeticiones);
        $model->litros_x_repeticiones = $request->litros_x_repeticiones;

        $model->litro_x_cama_poda = $request->litro_x_cama_poda;
        $array_repeticiones_poda = [];
        foreach (explode('-', $request->repeticiones_poda) as $pos => $r) {
            $r = intval($r);
            if (!in_array($r, $array_repeticiones_poda)) {
                $array_repeticiones_poda[] = $r;
            }
        }
        for ($i = 0; $i < count($array_repeticiones_poda) - 1; $i++) {
            for ($y = $i + 1; $y < count($array_repeticiones_poda); $y++)
                if ($array_repeticiones_poda[$i] > $array_repeticiones_poda[$y]) {
                    $temp = $array_repeticiones_poda[$i];
                    $array_repeticiones_poda[$i] = $array_repeticiones_poda[$y];
                    $array_repeticiones_poda[$y] = $temp;
                }
        }
        $repeticiones_poda = '';
        foreach ($array_repeticiones_poda as $pos => $r) {
            if ($pos == 0)
                $repeticiones_poda = $r;
            else
                $repeticiones_poda .= '-' . $r;
        }
        $model->repeticiones_poda = $repeticiones_poda;
        $array_litros_poda = explode('-', $request->litros_x_repeticiones_poda);
        $model->litros_x_repeticiones_poda = $request->litros_x_repeticiones_poda;

        if (count($array_repeticiones) > 0 && count($array_repeticiones) == count($array_litros) &&
            count($array_repeticiones_poda) > 0 && count($array_repeticiones_poda) == count($array_litros_poda)) {
            $model->save();
            return [
                'success' => true,
                'mensaje' => 'Se ha <strong>ACTUALIZADO</strong> la mezcla',
            ];
        } else {
            return [
                'success' => false,
                'mensaje' => '<div class="alert alert-warning text-center">La cantidad de repeticiones y litros deben coincidir</div>',
            ];
        }
    }

    public function update_mezcla(Request $request)
    {
        $model = AplicacionMezcla::find($request->mezcla);
        $model->nombre = espacios(mb_strtoupper(str_limit($request->nombre, 250)));

        $model->litro_x_cama = $request->litro_x_cama;
        $array_repeticiones = [];
        foreach (explode('-', $request->repeticiones) as $pos => $r) {
            $r = intval($r);
            if (!in_array($r, $array_repeticiones)) {
                $array_repeticiones[] = $r;
            }
        }
        for ($i = 0; $i < count($array_repeticiones) - 1; $i++) {
            for ($y = $i + 1; $y < count($array_repeticiones); $y++)
                if ($array_repeticiones[$i] > $array_repeticiones[$y]) {
                    $temp = $array_repeticiones[$i];
                    $array_repeticiones[$i] = $array_repeticiones[$y];
                    $array_repeticiones[$y] = $temp;
                }
        }
        $repeticiones = '';
        foreach ($array_repeticiones as $pos => $r) {
            if ($pos == 0)
                $repeticiones = $r;
            else
                $repeticiones .= '-' . $r;
        }
        $model->repeticiones = $repeticiones;
        $array_litros = explode('-', $request->litros_x_repeticiones);
        $model->litros_x_repeticiones = $request->litros_x_repeticiones;

        $model->litro_x_cama_poda = $request->litro_x_cama_poda;
        $array_repeticiones_poda = [];
        foreach (explode('-', $request->repeticiones_poda) as $pos => $r) {
            $r = intval($r);
            if (!in_array($r, $array_repeticiones_poda)) {
                $array_repeticiones_poda[] = $r;
            }
        }
        for ($i = 0; $i < count($array_repeticiones_poda) - 1; $i++) {
            for ($y = $i + 1; $y < count($array_repeticiones_poda); $y++)
                if ($array_repeticiones_poda[$i] > $array_repeticiones_poda[$y]) {
                    $temp = $array_repeticiones_poda[$i];
                    $array_repeticiones_poda[$i] = $array_repeticiones_poda[$y];
                    $array_repeticiones_poda[$y] = $temp;
                }
        }
        $repeticiones_poda = '';
        foreach ($array_repeticiones_poda as $pos => $r) {
            if ($pos == 0)
                $repeticiones_poda = $r;
            else
                $repeticiones_poda .= '-' . $r;
        }
        $model->repeticiones_poda = $repeticiones_poda;
        $array_litros_poda = explode('-', $request->litros_x_repeticiones_poda);
        $model->litros_x_repeticiones_poda = $request->litros_x_repeticiones_poda;

        if (count($array_repeticiones) > 0 && count($array_repeticiones) == count($array_litros) &&
            count($array_repeticiones_poda) > 0 && count($array_repeticiones_poda) == count($array_litros_poda)) {
            $model->save();
            return [
                'success' => true,
                'mensaje' => 'Se ha <strong>ACTUALIZADO</strong> la mezcla',
            ];
        } else {
            return [
                'success' => false,
                'mensaje' => '<div class="alert alert-warning text-center">La cantidad de repeticiones y litros deben coincidir</div>',
            ];
        }
    }

    public function delete_mezcla(Request $request)
    {
        $model = AplicacionMezcla::find($request->mezcla);
        foreach ($model->detalles as $d) {
            foreach ($d->parametros as $p)
                $p->delete();
            $d->delete();
        }
        $model->delete();
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>ELIMINADO</strong> la mezcla',
        ];
    }

    public function variedades_app(Request $request)
    {
        $app = Aplicacion::find($request->id_app);
        return view('adminlte.gestion.campo.aplicaciones.forms.variedades_app', [
            'app' => $app,
            'app_variedades' => $app->variedades,
            'variedades' => Variedad::where('estado', 1)->orderBy('nombre')->get(),
        ]);
    }

    public function store_detalle_app(Request $request)
    {
        $existe = DetalleAplicacion::All()
            ->where('id_aplicacion', $request->id_app)
            ->where('id_aplicacion_mezcla', $request->mezcla)
            ->where('id_mano_obra', $request->mo)
            ->where('id_producto', $request->insumo)
            ->first();
        $model = [];
        if ($existe == '') {
            if ($request->mo != '' || $request->insumo != '') {
                $det_app = new DetalleAplicacion();
                $det_app->id_aplicacion = $request->id_app;
                $det_app->id_aplicacion_mezcla = $request->mezcla;
                $det_app->id_mano_obra = $request->mo;
                $det_app->id_producto = $request->insumo;
                $det_app->save();
                $det_app = DetalleAplicacion::All()->last();
                $success = true;
                $msg = '';
                $model = [
                    'id_aplicacion' => $request->id_app,
                    'id_aplicacion_mezcla' => $request->mezcla,
                    'id_det' => $det_app->id_detalle_aplicacion,
                    'id_mano_obra' => $request->mo,
                    'id_producto' => $request->insumo,
                    'mo' => $request->mo != '' ? $det_app->mano_obra->nombre : '',
                    'producto' => $request->insumo != '' ? $det_app->producto->nombre : '',
                ];
            } else {
                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<p> Al menos seleccione una mano de obra o un insumo</p>'
                    . '</div>';
            }
        } else {
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> El detalle de la aplicación ya existe</p>'
                . '</div>';
        }
        return [
            'model' => $model,
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function delete_det_app(Request $request)
    {
        $model = DetalleAplicacion::find($request->id_det);
        $model->delete();
        return [
            'success' => true,
            'mensaje' => '',
        ];
    }

    public function parametrizar_det(Request $request)
    {
        $det = DetalleAplicacion::find($request->id_det);
        $parametros = $det->parametros;
        $unidades_medida = UnidadMedida::where('estado', 1)->where('uso', $det->id_mano_obra != '' ? 'C' : 'S')->orderBy('siglas')->get();
        return view('adminlte.gestion.campo.aplicaciones.forms.parametrizar', [
            'det' => $det,
            'parametros' => $parametros,
            'unidades_medida' => $unidades_medida,
        ]);
    }

    public function store_parametro(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'tipo' => 'required',
            'desde' => 'required',
            'hasta' => 'required',
        ], [
            'tipo.required' => 'El TIPO es obligatorio',
            'desde.required' => 'El rango DESDE es obligatorio',
            'hasta.required' => 'El rango HASTA es obligatorio',
        ]);
        $model = [];
        if (!$valida->fails()) {
            $existe = ParametroDetalleAplicacion::All()
                ->where('id_detalle_aplicacion', $request->id_det)
                ->where('tipo', $request->tipo)
                ->where('desde', $request->desde)
                ->where('hasta', $request->hasta)
                ->where('dosis', $request->dosis)
                ->where('cantidad_mo', $request->cantidad_mo)
                ->where('id_unidad_medida', $request->unidad_medida)
                ->where('factor_conversion', $request->factor_conversion)
                ->where('id_unidad_conversion', $request->unidad_conversion)
                ->first();
            if ($existe == '') {
                $par_det = new ParametroDetalleAplicacion();
                $par_det->id_detalle_aplicacion = $request->id_det;
                $par_det->tipo = $request->tipo;
                $par_det->desde = $request->desde;
                $par_det->hasta = $request->hasta;
                $par_det->dosis = $request->dosis;
                $par_det->cantidad_mo = $request->cantidad_mo;
                $par_det->id_unidad_medida = $request->unidad_medida;
                $par_det->factor_conversion = $request->factor_conversion;
                $par_det->id_unidad_conversion = $request->unidad_conversion;
                $par_det->save();
                $par_det = ParametroDetalleAplicacion::All()->last();
                $success = true;
                $msg = '';
                $tipos = [
                    'E' => 'Estandar',
                    'T' => 'Temperatura',
                    'D' => 'Delta Acum. 10 días',
                    'L' => 'Lluvia Acum. 21 días',
                    'A' => 'Altura',
                ];
                $model = [
                    'id_par' => $par_det->id_parametro_detalle_aplicacion,
                    'desde' => $request->desde,
                    'hasta' => $request->hasta,
                    'tipo' => $tipos[$request->tipo],
                    'dosis' => $request->dosis,
                    'cantidad_mo' => $request->cantidad_mo,
                    'unidad_medida' => $par_det->unidad_medida != '' ? $par_det->unidad_medida->siglas : '',
                    'factor_conversion' => $request->factor_conversion != '' ? $request->factor_conversion : '',
                    'unidad_conversion' => $par_det->unidad_conversion != '' ? $par_det->unidad_conversion->siglas : '',
                ];
            } else {
                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<p> El parámetro del detalle ya existe</p>'
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
            'model' => $model,
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function delete_par(Request $request)
    {
        $model = ParametroDetalleAplicacion::find($request->id_par);
        $model->delete();
        return [
            'success' => true,
            'mensaje' => '',
        ];
    }

    public function parametrizar_app(Request $request)
    {
        $app = Aplicacion::find($request->id_app);
        $parametros = $app->parametros;
        return view('adminlte.gestion.campo.aplicaciones.forms.parametrizar_app', [
            'app' => $app,
            'parametros' => $parametros,
            'campo' => $request->campo,
        ]);
    }

    public function store_parametro_app(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'campo' => 'required',
            'tipo' => 'required',
            'desde' => 'required',
            'hasta' => 'required',
            'valor' => 'required',
        ], [
            'campo.required' => 'El CAMPO es obligatorio',
            'valor.required' => 'El VALOR es obligatorio',
            'tipo.required' => 'El TIPO es obligatorio',
            'desde.required' => 'El rango DESDE es obligatorio',
            'hasta.required' => 'El rango HASTA es obligatorio',
        ]);
        $model = [];
        if (!$valida->fails()) {
            $existe = ParametroAplicacion::All()
                ->where('id_aplicacion', $request->id_app)
                ->where('campo', $request->campo)
                ->where('tipo', $request->tipo)
                ->where('desde', $request->desde)
                ->where('hasta', $request->hasta)
                ->where('valor', $request->valor)
                ->first();
            if ($existe == '') {
                $par_app = new ParametroAplicacion();
                $par_app->id_aplicacion = $request->id_app;
                $par_app->campo = $request->campo;
                $par_app->tipo = $request->tipo;
                $par_app->desde = $request->desde;
                $par_app->hasta = $request->hasta;
                $par_app->valor = $request->valor;
                $par_app->save();
                $par_app = ParametroAplicacion::All()->last();
                $success = true;
                $msg = '';
                $model = [
                    'id_par' => $par_app->id_parametro_aplicacion,
                    'desde' => $request->desde,
                    'hasta' => $request->hasta,
                    'tipo' => $par_app->getTipo(),
                    'campo' => $par_app->getCampo(),
                    'valor' => $request->valor,
                ];
            } else {
                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<p> El parámetro de la aplicación ya existe</p>'
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
            'model' => $model,
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function delete_par_app(Request $request)
    {
        $model = ParametroAplicacion::find($request->id_par);
        $model->delete();
        return [
            'success' => true,
            'mensaje' => '',
        ];
    }

    public function seleccionar_app_variedad(Request $request)
    {
        $model = AplicacionVariedad::All()
            ->where('id_aplicacion', $request->app)
            ->where('id_variedad', $request->variedad)
            ->first();
        if ($model == '') {
            $model = new AplicacionVariedad();
            $model->id_aplicacion = $request->app;
            $model->id_variedad = $request->variedad;
            $model->save();
        } else {
            $model->delete();
        }
        return [
            'success' => true,
            'mensaje' => '',
        ];
    }

    public function add_matriz(Request $request)
    {
        $listado = AplicacionMatriz::orderBy('nombre')->get();
        return view('adminlte.gestion.campo.aplicaciones.partials._matriz', [
            'listado' => $listado,
        ]);
    }
}
