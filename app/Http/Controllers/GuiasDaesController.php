<?php

namespace yura\Http\Controllers;

use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;
use yura\Modelos\Aerolinea;
use yura\Modelos\AgenciaCarga;
use yura\Modelos\Cliente;
use yura\Modelos\ClienteConsignatario;
use yura\Modelos\DetalleCliente;
use yura\Modelos\DetalleEnvio;
use yura\Modelos\Envio;
use yura\Modelos\Pais;
use yura\Modelos\Pedido;
use yura\Modelos\Submenu;

class GuiasDaesController extends Controller
{
    public function inicio(Request $request)
    {
        return view(
            'adminlte.gestion.postcocecha.guias_daes.inicio',
            [
                'url' => $request->getRequestUri(),
                'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
                'text' => ['titulo' => 'Guías y Daes', 'subtitulo' => 'módulo de postcocecha'],
                'clientes' => DetalleCliente::where('estado', true)->get(),
                'agenciasCarga' => AgenciaCarga::where('estado', true)->get()
            ]
        );
    }

    public function listado(Request $request)
    {
        $paises = Pais::all();
        $aerolineas = Aerolinea::where('estado', 1)->orderBy('nombre', 'asc')->get();

        $pedidos = Pedido::where([
            ['pedido.estado', true],
            ['dc.estado', true]
        ]);
        if ($request->id_cliente != '')
            $pedidos = $pedidos->where('pedido.id_cliente', $request->id_cliente);
        $pedidos = $pedidos->whereBetween('pedido.fecha_pedido', [$request->desde, $request->hasta])
            ->where(DB::raw('(SELECT COUNT(*) FROM detalle_pedido as dp WHERE dp.id_pedido = pedido.id_pedido)'), '>', 0)
            ->join('envio as e', 'pedido.id_pedido', 'e.id_pedido')
            ->join('cliente as cl', 'pedido.id_cliente', 'cl.id_cliente')
            ->join('detalle_cliente as dc', function ($j) {
                $j->on('cl.id_cliente', 'dc.id_cliente')->where('dc.estado', true);
            })->select(
                'pedido.id_pedido',
                'pedido.fecha_pedido',
                'pedido.id_cliente',
                'packing',
                'dc.nombre as cli_nombre',
                'e.guia_madre',
                'e.guia_hija',
                'e.dae',
                'dc.codigo_pais as pais_cliente',
                DB::raw("(SELECT id_aerolinea FROM detalle_envio AS de WHERE de.id_envio = e.id_envio LIMIT 1) AS id_aerolinea")
            )->distinct()
            ->orderBy('pedido.id_cliente', 'asc')->get();

        /*$longitud = count($pedidos);
        for ($i = 0; $i < $longitud; $i++) {
            for ($j = 0; $j < $longitud - 1; $j++) {
                $agencia_j = isset($pedidos[$j]->detalles[0]) ? $pedidos[$j]->detalles[0]->agencia_carga->nombre : '';
                $agencia_i = isset($pedidos[$i]->detalles[0]) ? $pedidos[$i]->detalles[0]->agencia_carga->nombre : '';
                if ($agencia_j > $agencia_i) {
                    $temporal = $pedidos[$j];
                    $pedidos[$j] = $pedidos[$i];
                    $pedidos[$i] = $temporal;
                }
            }
        }*/
        return view('adminlte.gestion.postcocecha.guias_daes.partials.listado', [
            'pedidos' => $pedidos,
            'paises' => $paises,
            'aerolineas' => $aerolineas,
            'carbon' => Carbon::class,
            'ClienteConsignatario' => ClienteConsignatario::class
        ]);
    }

    public function actualiza_datos_envio(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $valida = Validator::make($request->all(), [
            'datos_envios' => ['required', 'min:1', function ($attibute, $value, $onFailure) {

                foreach ($value as $val) {

                    if (!isset($val['pais']) || $val['pais'] == '') {

                        $onFailure('Debe seleccionar el país del packing ' . $val['packing']);
                    } else if (isset($val['guia_madre'])) {

                        if (strlen($val['guia_madre']) != 11)
                            $onFailure('La guía madre del packing ' . $val['packing'] . ' debe ser de 11 caracteres');
                    } else if (!isset($val['consignatario']) || $val['consignatario'] == '') {
                        $onFailure('Debe seleccionar el consignatario del packing ' . $val['packing']);
                    }
                }
            }]
        ], [
            'datos_envios.required' => 'Verifique que los datos en los pedidos esten completos'
        ]);

        if (!$valida->fails()) {

            DB::beginTransaction();

            try {

                foreach ($request->datos_envios as $de) {

                    $objEnvio = Envio::find($de['id_envio']);
                    $objEnvio->dae = isset($de['dae']) ? $de['dae'] : '';
                    $objEnvio->guia_madre = isset($de['guia_madre']) ? $de['guia_madre'] : '';
                    $objEnvio->guia_hija = isset($de['guia_hija']) ? $de['guia_hija'] : '';
                    $objEnvio->codigo_pais = $de['pais'];
                    $objEnvio->id_consignatario = $de['consignatario'];
                    $objEnvio->save();

                    bitacora('envio', $request->id_envio, 'U', 'Actualización satisfactoria del envío');

                    if (isset($de['aerolinea'])) {
                        $save_det_env = DetalleEnvio::All()->where('id_envio', $de['id_envio'])->first();
                        $save_det_env->id_aerolinea = $de['aerolinea'];
                        $save_det_env->save();
                    }
                }

                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se han actualizado exitosamente los datos del envío</p>'
                    . '</div>';

                DB::commit();
            } catch (\Exception $e) {

                DB::rollBack();
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> Ha ocurrido un problema ' . $e->getMessage() . '</p>'
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
