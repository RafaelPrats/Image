<?php

namespace yura\Http\Controllers\Bodega;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Producto;
use yura\Modelos\Submenu;
use Validator;

class ProductosController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.bodega.productos.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $listado = Producto::where('nombre', 'like', '%' . mb_strtoupper($request->busqueda) . '%')
            ->orwhere('codigo_jire', 'like', '%' . mb_strtoupper($request->busqueda) . '%')
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.bodega.productos.partials.listado', [
            'listado' => $listado,
        ]);
    }

    public function store_producto(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:500|unique:producto',
            'codigo_jire' => 'required|max:500|unique:producto',
            'stock_minimo' => 'required',
            'disponibles' => 'required',
            'conversion' => 'required',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.required' => 'El nombre ya existe',
            'nombre.max' => 'El nombre es muy grande',
            'codigo_jire.required' => 'El codigo es obligatorio',
            'codigo_jire.required' => 'El codigo ya existe',
            'codigo_jire.max' => 'El codigo es muy grande',
            'stock_minimo.required' => 'El stock minimo es obligatorio',
            'disponibles.required' => 'Los disponibles son obligatorios',
            'conversion.required' => 'La conversion es obligatoria',
        ]);
        if (!$valida->fails()) {
            $model = new Producto();
            $model->codigo_jire = $request->codigo_jire;
            $model->nombre = espacios(mb_strtoupper($request->nombre));
            $model->stock_minimo = $request->stock_minimo;
            $model->disponibles = $request->disponibles;
            $model->conversion = $request->conversion;
            $model->save();
            $model = Producto::All()->last();
            bitacora('producto', $model->id_producto, 'I', 'Creacion del producto');
            $success = true;
            $msg = 'Se ha <strong>CREADO</strong> el producto satisfactoriamente';
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

    public function update_producto(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:500',
            'codigo_jire' => 'required|max:500',
            'stock_minimo' => 'required',
            'disponibles' => 'required',
            'conversion' => 'required',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
            'codigo_jire.required' => 'El codigo es obligatorio',
            'codigo_jire.max' => 'El codigo es muy grande',
            'stock_minimo.required' => 'El stock minimo es obligatorio',
            'disponibles.required' => 'Los disponibles son obligatorios',
            'conversion.required' => 'La conversion es obligatoria',
        ]);
        if (!$valida->fails()) {
            $existe_nombre = Producto::All()
                ->where('id_producto', '!=', $request->id)
                ->where('nombre', espacios(mb_strtoupper($request->nombre)))
                ->first();
            if ($existe_nombre != '') {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p>El nombre del producto y existe</p>'
                    . '</div>';
            } else {
                $existe_codigo = Producto::All()
                    ->where('id_producto', '!=', $request->id)
                    ->where('codigo_jire', espacios(mb_strtoupper($request->codigo_jire)))
                    ->first();
                if ($existe_codigo != '') {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p>El codigo del producto y existe</p>'
                        . '</div>';
                } else {
                    $model = Producto::find($request->id);
                    $model->codigo_jire = $request->codigo_jire;
                    $model->nombre = espacios(mb_strtoupper($request->nombre));
                    $model->stock_minimo = $request->stock_minimo;
                    $cambio_disponibles = false;
                    if ($model->disponibles != $request->disponibles)
                        $cambio_disponibles = true;
                    $model->disponibles = $request->disponibles;
                    $model->conversion = $request->conversion;
                    $model->save();

                    if ($model->save()) {
                        $success = true;
                        $msg = 'Se ha <strong>MODIFICADO</strong> el producto satisfactoriamente';
                        if (!$cambio_disponibles) {
                            bitacora('producto', $model->id_producto, 'U', 'Modifico el producto');
                        } else {
                            bitacora('producto', $model->id_producto, 'U', 'Modifico el proucto y/o cantidad DISPONIBLES');
                        }
                    } else {
                        $success = false;
                        $msg = '<div class="alert alert-warning text-center">' .
                            '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                            . '</div>';
                    }
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

    public function cambiar_estado_producto(Request $request)
    {
        $model = Producto::find($request->id);
        $model->estado = $model->estado == 1 ? 0 : 1;
        $model->save();

        $success = true;
        $msg = 'Se ha <strong>MODIFICADO</strong> el producto satisfactoriamente';
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }
}
