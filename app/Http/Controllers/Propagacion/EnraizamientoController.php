<?php

namespace yura\Http\Controllers\Propagacion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use yura\Http\Controllers\Controller;
use yura\Modelos\DetalleEnraizamientoSemanal;
use yura\Modelos\EnraizamientoSemanal;
use yura\Modelos\Submenu;

class EnraizamientoController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.propagacion.enraizamiento.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function store_enraizamiento(Request $request)
    {
        $msg = '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>';
        $success = true;
        $semana_ini = getSemanaByDate($request->fecha);
        foreach ($request->data as $d) {
            $enr_sem = EnraizamientoSemanal::All()
                ->where('semana_ini', $semana_ini->codigo)
                ->where('id_variedad', $d['variedad'])
                ->first();
            if ($enr_sem == '') {
                $new_enr = true;
                $enr_sem = new EnraizamientoSemanal();
                $enr_sem->semana_ini = $semana_ini->codigo;
                $enr_sem->id_variedad = $d['variedad'];
                $enr_sem->cantidad_siembra = $d['cantidad'];
            } else {
                $new_enr = false;
                $enr_sem->cantidad_siembra += $d['cantidad'];
            }
            $enr_sem->cantidad_semanas = $d['semanas'];
            $semana_fin = getSemanaByDate(opDiasFecha('+', ($d['semanas'] * 7), $semana_ini->fecha_inicial));
            $enr_sem->semana_fin = $semana_fin->codigo;
            if ($enr_sem->save()) {
                if ($new_enr)
                    $enr_sem = EnraizamientoSemanal::All()->last();

                /* =============== DetalleEnraizamientoSemanal ================== */
                $det_enr = new DetalleEnraizamientoSemanal();
                $det_enr->id_enraizamiento_semanal = $enr_sem->id_enraizamiento_semanal;
                $det_enr->fecha = $request->fecha;
                $det_enr->cantidad_siembra = $d['cantidad'];
                $det_enr->save();

                Artisan::call('update:propag_disponibilidad', [
                    'semana_desde' => $enr_sem->semana_ini,
                    'semana_hasta' => getLastSemanaByVariedad($enr_sem->id_variedad)->codigo,
                    'variedad' => $enr_sem->id_variedad,
                ]);
            } else {
                $success = false;
                $msg = '<div class="alert alert-danger text-center">Ha ocurrido un problema al guardar el enraizamiento</div>';
            }
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function buscar_enraizamiento_semanal(Request $request)
    {
        $semana_ini = getSemanaByDate($request->fecha);
        $model = EnraizamientoSemanal::All()
            ->where('semana_ini', $semana_ini->codigo)
            ->where('id_variedad', $request->variedad)
            ->first();
        if ($model != '')
            return [
                'cantidad_semanas' => $model->cantidad_semanas
            ];
        else
            return [
                'cantidad_semanas' => ''
            ];
    }

    public function listar_enraizamientos(Request $request)
    {
        $detalles = DetalleEnraizamientoSemanal::join('enraizamiento_semanal as es', 'es.id_enraizamiento_semanal', '=', 'detalle_enraizamiento_semanal.id_enraizamiento_semanal')
            ->join('variedad as v', 'v.id_variedad', '=', 'es.id_variedad')
            ->select('detalle_enraizamiento_semanal.*')
            ->where('detalle_enraizamiento_semanal.fecha', $request->fecha)
            ->orderBy('v.nombre')->get();
        return view('adminlte.gestion.propagacion.enraizamiento.partials.listado', [
            'detalles' => $detalles,
        ]);
    }

    public function update_enraizamiento(Request $request)
    {
        if ($request->cantidad > 0) {
            $enrz = EnraizamientoSemanal::find($request->id);
            $enrz->cantidad_semanas = $request->cantidad;
            $semana_fin = getSemanaByDate(opDiasFecha('+', ($enrz->cantidad_semanas * 7), $enrz->semana_ini()->fecha_inicial));
            $enrz->semana_fin = $semana_fin->codigo;
            if ($enrz->save()) {
                Artisan::call('update:propag_disponibilidad', [
                    'semana_desde' => $enrz->semana_ini,
                    'semana_hasta' => getLastSemanaByVariedad($enrz->id_variedad)->codigo,
                    'variedad' => $enrz->id_variedad,
                ]);
                $success = true;
                $msg = '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>';
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">Ha ocurrido un problema al guardar la información</div>';
            }
        } else {
            $success = false;
            $msg = '<div class="alert alert-warning text-center">La cantidad es obligatoria</div>';
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function update_detalle_enraizamiento(Request $request)
    {
        if ($request->cantidad > 0) {
            $det = DetalleEnraizamientoSemanal::find($request->id);
            $det->cantidad_siembra = $request->cantidad;
            if ($det->save()) {
                $enr = $det->enraizamiento_semanal;
                $total = 0;
                foreach ($enr->detalles as $d)
                    $total += $d->cantidad_siembra;
                $enr->cantidad_siembra = $total;
                $enr->save();

                Artisan::call('update:propag_disponibilidad', [
                    'semana_desde' => $enr->semana_ini,
                    'semana_hasta' => getLastSemanaByVariedad($enr->id_variedad)->codigo,
                    'variedad' => $enr->id_variedad,
                ]);

                $success = true;
                $msg = '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>';
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">Ha ocurrido un problema al guardar la información</div>';
            }
        } else {
            $success = false;
            $msg = '<div class="alert alert-warning text-center">La cantidad es obligatoria</div>';
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function delete_detalle_enraizamiento(Request $request)
    {
        $det = DetalleEnraizamientoSemanal::find($request->id);
        $enr = $det->enraizamiento_semanal;

        if ($det->delete()) {
            $total = 0;
            foreach ($enr->detalles as $d)
                $total += $d->cantidad_siembra;
            $enr->cantidad_siembra = $total;
            $enr->save();
            Artisan::call('update:propag_disponibilidad', [
                'semana_desde' => $enr->semana_ini,
                'semana_hasta' => getLastSemanaByVariedad($enr->id_variedad)->codigo,
                'variedad' => $enr->id_variedad,
            ]);

            $success = true;
            $msg = '<div class="alert alert-success text-center">Se ha eliminado la siembra satisfactoriamente</div>';
        } else {
            $success = false;
            $msg = '<div class="alert alert-warning text-center">Ha ocurrido un problema al eliminar la siembra</div>';
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }
}
