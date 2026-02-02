<?php

namespace yura\Http\Controllers\Comercializacion;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use DB;
use yura\Modelos\Cliente;
use yura\Modelos\Especificaciones;

class EspecificacionesController extends Controller
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
        $tipos_caja = DB::table('empaque')
            ->select('siglas')->distinct()
            ->whereNotNull('siglas')
            ->where('estado', 1)
            ->orderBy('siglas')
            ->get()->pluck('siglas')->toArray();

        return view('adminlte.gestion.comercializacion.especificaciones.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
            'clientes' => $clientes,
            'tipos_caja' => $tipos_caja,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $listado = Especificaciones::join('empaque as c', 'c.id_empaque', '=', 'especificaciones.id_empaque_c')
            ->select('especificaciones.*')->distinct()
            ->where('especificaciones.id_cliente', $request->cliente);
        if ($request->planta != '')
            $listado = $listado->where('especificaciones.id_planta', $request->planta);
        if ($request->variedad != '')
            $listado = $listado->where('especificaciones.id_variedad', $request->variedad);
        if ($request->longitud != '')
            $listado = $listado->where('especificaciones.longitud_ramo', $request->longitud);
        if ($request->peso != '')
            $listado = $listado->where('especificaciones.peso_ramo', $request->peso);
        if ($request->tipo_caja != '')
            $listado = $listado->where('c.siglas', $request->tipo_caja);
        $listado = $listado->get();

        $plantas = Planta::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $cajas = DB::table('empaque')
            ->select('*')->distinct()
            ->where('tipo', 'C')
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $presentaciones = DB::table('empaque')
            ->select('*')->distinct()
            ->where('tipo', 'P')
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.comercializacion.especificaciones.partials.listado', [
            'listado' => $listado,
            'plantas' => $plantas,
            'cajas' => $cajas,
            'presentaciones' => $presentaciones,
            'cliente' => Cliente::find($request->cliente)
        ]);
    }

    public function add_especificaciones(Request $request)
    {
        $plantas = Planta::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $cajas = DB::table('empaque')
            ->select('*')->distinct()
            ->where('tipo', 'C')
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $presentaciones = DB::table('empaque')
            ->select('*')->distinct()
            ->where('tipo', 'P')
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.comercializacion.especificaciones.forms.add_especificaciones', [
            'plantas' => $plantas,
            'cajas' => $cajas,
            'presentaciones' => $presentaciones,
            'cliente' => Cliente::find($request->cliente)
        ]);
    }

    public function store_especificaciones(Request $request)
    {
        try {
            DB::beginTransaction();
            foreach (json_decode($request->data) as $data) {
                $variedades = DB::table('variedad')
                    ->select('*')->distinct()
                    ->where('id_planta', $data->planta)
                    ->where('estado', 1);
                if ($data->variedad != 'T')
                    $variedades = $variedades->where('id_variedad', $data->variedad);
                $variedades = $variedades->get()->pluck('id_variedad')->toArray();
                foreach ($variedades as $var) {
                    $model = new Especificaciones();
                    $model->id_planta = $data->planta;
                    $model->id_empaque_c = $data->caja;
                    $model->id_variedad = $var;
                    $model->id_empaque_p = $data->presentacion;
                    $model->ramos_x_caja = $data->ramos_x_caja;
                    $model->tallos_x_ramos = $data->tallos_x_ramos;
                    $model->longitud_ramo = $data->longitud;
                    $model->peso_ramo = $data->peso;
                    $model->id_cliente = $request->cliente;
                    $model->save();
                    $model->id_especificaciones = DB::table('especificaciones')
                        ->select(DB::raw('max(id_especificaciones) as id'))
                        ->get()[0]->id;
                    bitacora('especificaciones', $model->id_especificaciones, 'I', 'CREACION nueva ESPECIFICACION');
                }
            }

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>GRABADO</strong> las especificaciones correctamente';
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

    public function update_especificaciones(Request $request)
    {
        try {
            DB::beginTransaction();
            foreach (json_decode($request->data) as $data) {
                $model = Especificaciones::find($data->id_esp);
                $model->id_planta = $data->planta;
                $model->id_empaque_c = $data->caja;
                $model->id_variedad = $data->variedad;
                $model->id_empaque_p = $data->presentacion;
                $model->ramos_x_caja = $data->ramos_x_caja;
                $model->tallos_x_ramos = $data->tallos_x_ramos;
                $model->longitud_ramo = $data->longitud_ramo;
                $model->peso_ramo = $data->peso_ramo;
                $model->save();
                bitacora('especificaciones', $model->id_especificaciones, 'U', 'MODIFICAR la ESPECIFICACION');
            }

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>ACTUALIZADO</strong> las especificaciones correctamente';
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

    public function delete_especificaciones(Request $request)
    {
        try {
            DB::beginTransaction();
            foreach (json_decode($request->data) as $data) {
                $model = Especificaciones::find($data->id_esp);
                bitacora('especificaciones', $data->id_esp, 'D', 'ELIMINAR la ESPECIFICACION del cliente: ' . $model->id_cliente);
                $model->delete();
            }

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>ACTUALIZADO</strong> las especificaciones correctamente';
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
}
