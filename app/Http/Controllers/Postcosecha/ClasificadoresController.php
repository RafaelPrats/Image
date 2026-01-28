<?php

namespace yura\Http\Controllers\Postcosecha;

use DB;
use Illuminate\Http\Request;
use Validator;
use yura\Http\Controllers\Controller;
use yura\Modelos\Clasificador;
use yura\Modelos\Submenu;

class ClasificadoresController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.postcocecha.clasificadores.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $listado = Clasificador::where('nombre', 'like', '%' . mb_strtoupper($request->busqueda) . '%')
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.postcocecha.clasificadores.partials.listado', [
            'listado' => $listado,
        ]);
    }

    public function store_clasificador(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250|unique:clasificador',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.required' => 'El nombre ya existe',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        if (!$valida->fails()) {
            $model = new Clasificador();
            $model->nombre = espacios(mb_strtoupper($request->nombre));
            $model->save();
            $model->id_clasificador = DB::table('clasificador')
                ->select(DB::raw('max(id_clasificador) as id'))
                ->get()[0]->id;
            bitacora('clasificador', $model->id_clasificador, 'I', 'Creacion del clasificador');
            $success = true;
            $msg = 'Se ha <strong>CREADO</strong> el clasificador satisfactoriamente';
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

    public function update_clasificador(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        if (!$valida->fails()) {
            $existe_nombre = Clasificador::All()
                ->where('id_clasificador', '!=', $request->id)
                ->where('nombre', espacios(mb_strtoupper($request->nombre)))
                ->first();
            if ($existe_nombre != '') {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p>El nombre del clasificador y existe</p>'
                    . '</div>';
            } else {
                $model = Clasificador::find($request->id);
                $model->nombre = espacios(mb_strtoupper($request->nombre));
                $model->save();

                if ($model->save()) {
                    $success = true;
                    $msg = 'Se ha <strong>MODIFICADO</strong> el clasificador satisfactoriamente';
                    bitacora('clasificador', $model->id_clasificador, 'U', 'Modifico el clasificador');
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                        . '</div>';
                }
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

    public function cambiar_estado_clasificador(Request $request)
    {
        $model = Clasificador::find($request->id);
        $model->estado = $model->estado == 1 ? 0 : 1;
        $model->save();

        $success = true;
        $msg = 'Se ha <strong>MODIFICADO</strong> el clasificador satisfactoriamente';
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }
}
