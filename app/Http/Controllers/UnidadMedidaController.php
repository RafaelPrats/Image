<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use yura\Modelos\Submenu;
use yura\Modelos\UnidadMedida;
use Validator;

class UnidadMedidaController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.unidad_medida.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function buscar_listado(Request $request)
    {
        return view('adminlte.gestion.unidad_medida.partials.listado', [
            'listado' => UnidadMedida::orderBy('nombre')->get(),
        ]);
    }

    public function store_unidad(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250|unique:unidad_medida',
            'siglas' => 'required',
            'tipo' => 'required',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'tipo.required' => 'El tipo es obligatorio',
            'siglas.required' => 'Las siglas son obligatorias',
            'nombre.max' => 'El nombre es muy grande',
            'nombre.unique' => 'El nombre ya existe',
        ]);
        if (!$valida->fails()) {
            $model = new UnidadMedida();
            $unidadMedida = UnidadMedida::orderBy('id_unidad_medida', 'desc')->first();
            $model->id_unidad_medida = isset($unidadMedida->id_unidad_medida) ? $unidadMedida->id_unidad_medida + 1 : 1;
            $model->nombre = str_limit(espacios($request->nombre), 250);
            $model->siglas = $request->siglas;
            $model->tipo = $request->tipo;
            $model->uso = $request->uso;

            if ($model->save()) {
                $model = UnidadMedida::All()->last();
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se ha guardado una nueva unidad de medida satisfactoriamente</p>'
                    . '</div>';
                bitacora('unidad_medida', $model->id_unidad_medida, 'I', 'Inserción satisfactoria de una nueva unidad de medida');
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

    public function update_unidad(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250',
            'siglas' => 'required',
            'tipo' => 'required',
            'id_unidad' => 'required',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'tipo.required' => 'El tipo es obligatorio',
            'id_unidad.required' => 'La unidad es obligatoria',
            'siglas.required' => 'Las siglas son obligatorias',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        if (!$valida->fails()) {
            $model = UnidadMedida::find($request->id_unidad);
            $model->nombre = str_limit(espacios($request->nombre), 250);
            $model->siglas = $request->siglas;
            $model->tipo = $request->tipo;
            $model->uso = $request->uso;

            if ($model->save()) {
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se ha actualizado la unidad de medida satisfactoriamente</p>'
                    . '</div>';
                bitacora('unidad_medida', $model->id_unidad_medida, 'U', 'Actualizacion satisfactoria de una unidad de medida');
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
}
