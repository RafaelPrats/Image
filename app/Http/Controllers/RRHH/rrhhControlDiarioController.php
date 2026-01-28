<?php

namespace yura\Http\Controllers\RRHH;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Area;
use yura\Modelos\Actividad;
use yura\Modelos\Ausentismos;
use yura\Modelos\ManoObra;
use yura\Modelos\PersonalDetalle;
use yura\Modelos\Personal;
use yura\Modelos\ControlDiario;
use yura\Modelos\ControlPersonal;
use yura\Modelos\Submenu;

class rrhhControlDiarioController extends Controller
{
    public function inicio(Request $request)
    {
        $area = Area::ALL()->where('estado', 1);
        $actividad = Actividad::ALL()->where('estado', 1);
        return view('adminlte.gestion.rrhh.control_diario.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->
            get()[0],
            'actividad' => $actividad,
            'area' => $area,
            'semana' => getSemanaByDate(date('Y-m-d')),
        ]);
    }   
    
    public function todosTrabajadores(Request $request)
    {
//dd($request);

        $busqueda_personal = $request->busqueda_personal;
        $listado = ControlPersonal::where('nombre','like',"%$busqueda_personal%")
        ->orWhere('apellido','like',"%$busqueda_personal%")
        ->orWhere('cedula_identidad','like',"%$busqueda_personal%")
        ->orderBy('apellido', 'asc')->get();
      //  dd($listado);
        
       $resultados = [];
        foreach ($listado as $per) {
            if ($estado == 1 ) {
                if ($per->getDetalleActivoDesin() != '')
                    array_push($resultados, $per);
            } else
                if ($per->getDetalleActivoDesin() == '')
                    array_push($resultados, $per);
        }

        return view('adminlte.gestion.rrhh.personal.partials.listado', [
            'person' => $resultados,
            'estado' => $estado,
            
        ]);
    }


    public function buscar_control_diario(Request $request){
        // buscar el control diario de la fecha del request
        $model = ControlDiario::All()
            ->where('fecha', $request->fecha)
            ->where('id_actividad', $request->id_actividad)
            ->first();
        if($model == ''){   // no existe un control diario para la fecha
            // buscar el control diario anterior, o sea, el ultimo registro
            $anterior = ControlDiario::where('id_actividad', $request->id_actividad)
                ->orderBy('fecha', 'desc')
                ->first();
            if($anterior != ''){    // verifico q exista al menos un control diario en la base
                // crear un control diario igual al anterior (horarios parametros)
                $model = new ControlDiario();
                $model->fecha = $request->fecha;
                $model->id_actividad = $request->id_actividad;
                $model->inicio_hora_ordinario = $anterior->inicio_hora_ordinario;
                $model->fin_hora_ordinario = $anterior->fin_hora_ordinario;
                $model->inicio_hora_50 = $anterior->inicio_hora_50;
                $model->fin_hora_50 = $anterior->fin_hora_50;
                $model->inicio_hora_100 = $anterior->inicio_hora_100;
                $model->fin_hora_100 = $anterior->fin_hora_100;
                $model->inicio_hora_nocturno = $anterior->inicio_hora_nocturno;
                $model->fin_hora_nocturno = $anterior->fin_hora_nocturno;
                $model->save();
                $model = ControlDiario::All()->last();
            }
        }
        $semana = getSemanaByDate($request->fecha);
        return [
            'model' => $model,
            'semana' => $semana, 
        ];
    }

    public function buscar_control_diario_general(Request $request){
        //dd($request->all());
        // buscar el control diario de la fecha del request
        $model = ControlDiario::All()->where('fecha', $request->fecha);
        $semana = getSemanaByDate($request->fecha);
        return [
            'model' => $resultados,
            'semana' => $semana, 
          
        ];
    
    }

public function listar_grupo(Request $request)
{ 
    $ausentismos = Ausentismos::ALL()->where('estado', 1);
    $actividad = Actividad::ALL()->where('estado', 1);
    $listado = ControlPersonal::where('id_actividad', $request->id_actividad)
        ->where('id_control_diario', $request->id_control_diario)
        ->get();
    if(count($listado) > 0){   // existen registros
        $view = 'listado_edit_control_diario';
    } else {    // no existen registros
        $listado = PersonalDetalle::where('id_actividad', '=',$request->id_actividad)
            ->where('estado', 1)->get();
        $view = 'listado_control_diario';
    }
    return view('adminlte.gestion.rrhh.control_diario.partials.'.$view, [
        'listado' => $listado,
        'actividades' => $actividad,
        'ausentismos' => $ausentismos,
        'actividad'=>$request->id_actividad,
    ]);
}
public function listar_grupo_general(Request $request)
{ 
     //dd($request->all());
    $ausentismos = Ausentismos::ALL()->where('estado', 1);
    $actividad = Actividad::ALL()->where('estado', 1);
    $listado = ControlPersonal::ALL();
    //dd($listado);
    if($listado != ''){   // existen registros
        $view = 'listado_control_diario_general';
    } else {    // no existen registros
        $listado = PersonalDetalle::where('estado', 1)->get();
        $view = 'listado_control_diario_general';
    }
    return view('adminlte.gestion.rrhh.control_diario.partials.'.$view, [
        'listado' => $listado,
        'actividades' => $actividad,
        'ausentismos' => $ausentismos,
        'actividad'=>$request->id_actividad,
    ]);
}


public function update_control_personal(Request $request){
  //dd($request->all());
    if($request->id != '')
        if($request->hasta != '' && $request->desde != '' && $request->desde < $request->hasta){
            $model = ControlPersonal::find($request->id);
            $model->desde = $request->desde;
            $model->hasta = $request->hasta;
            $model->id_actividad = $request->id_actividad;
            $model->id_ausentismo = $request->id_ausentismo;

            if($model->save()){
                $msg = '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>';
                $success = true;
            } else {
                $msg = '<div class="alert alert-danger text-center">Ha ocurrido un problema al guardar la información</div>';
                $success = false;
            }
        } else {
            $msg = '<div class="alert alert-warning text-center">Las horas están incorrectas</div>';
            $success = false;
        }
    else{
        $msg = '<div class="alert alert-danger text-center">El registro no es válido</div>';
        $success = false;
    }
    return [
        'success' => $success,
        'mensaje' => $msg,
    ];
}

public function store_control_personal(Request $request){
     //dd($request->all());
    $new_id = '';
    if($request->hasta != '' && $request->desde != '' && $request->desde < $request->hasta){
        $model = new ControlPersonal();
        $model->id_personal_detalle = $request->id_personal_detalle;
        $model->id_control_diario = $request->id_control_diario;
        $model->id_actividad = $request->id_actividades;
        $model->desde = $request->desde;
        $model->hasta = $request->hasta;
        $model->id_ausentismo = $request->id_ausentismo;
        if($model->save()){
            $new_id = ControlPersonal::All()->last()->id_control_personal;
            $msg = '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>';
            $success = true;
        } else {
            $msg = '<div class="alert alert-danger text-center">Ha ocurrido un problema al guardar la información</div>';
            $success = false;
        }
    } else {
        $msg = '<div class="alert alert-warning text-center">Las horas están incorrectas</div>';
        $success = false;
    }
    return [
        'success' => $success,
        'mensaje' => $msg,
        'id' => $new_id,
    ];
}

public function guardar_control_diario(Request $request)
{
    $model = ControlDiario::All()
        ->where('fecha', $request->fecha)
        ->where('id_actividad', $request->id_actividad)
        ->first();
        if ($model == '') {
            $model = new ControlDiario();
            $model->fecha = $request->fecha;   
            $model->id_actividad = $request->id_actividad;
        }
            $model->inicio_hora_ordinario = $request->inicio_hora_ordinario;
            $model->fin_hora_ordinario = $request->fin_hora_ordinario;
            $model->inicio_hora_50 = $request->inicio_hora_50;
            $model->fin_hora_50 = $request->fin_hora_50;
            $model->inicio_hora_100 = $request->inicio_hora_100;
            $model->fin_hora_100 = $request->fin_hora_100;
            $model->inicio_hora_nocturno = $request->inicio_hora_nocturno;
            $model->fin_hora_nocturno = $request->fin_hora_nocturno;

            if($model->save()){
                $msg = '<div class="alert alert-success text-center">Se ha guardado el horario satisfactoriamente</div>';
                $success = true;
            } else {
                $msg = '<div class="alert alert-danger text-center">Ha ocurrido un problema al guardar la información</div>';
                $success = false;
            }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function guardar_control_personal(Request $request)
    {
         foreach($request->data as $d){
         //  dd($request->id_control_diario,$d['id_actividad'], $d['id_personal_detalle']);
             // crear el registro control_personal
             if($d['hasta'] != '' && $d['desde'] != '' && $d['desde'] < $d['hasta']){
                $control = new ControlPersonal();
                $control->id_personal_detalle = $d['id_personal_detalle'];
                $control->desde = $d['desde'];
                $control->hasta = $d['hasta'];
                $control->id_actividad= $d['id_actividades'];
                $control->id_ausentismo = $d['id_ausentismo'];
                $control->id_control_diario = $request->id_control_diario;
                //dd($control);
                if ($control->save()) {
                    $msg = '<div class="alert alert-success text-center">Se ha registrado el control horario del personal
                    satisfactoriamente</div>';
                    $success = true;
                } else {
                    $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
                    $success = false;
                    return [
                        'success' => $success,
                        'mensaje' => $msg,
                    ];
                }
            } else {
                $msg = '<div class="alert alert-warning text-center">Las horas están incorrectas</div>';
                $success = false;
                return [
                    'success' => $success,
                    'mensaje' => $msg,
                ];
            }
        }
        return [
           'success' => $success,
           'mensaje' => $msg,
       ];


    }

  

}