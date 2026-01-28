@extends('layouts.adminlte.master')

@section('titulo')
    Control Diario
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Control Diario
            <small>módulo de rrhh</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" onclick="cargar_url('')" class="text-color_yura"><i class="fa fa-home"></i> Inicio</a></li>
            <li class="text-color_yura">
                {{$submenu->menu->grupo_menu->nombre}}
            </li>
            <li class="text-color_yura">
                {{$submenu->menu->nombre}}
            </li>
            <li class="active">
                <a href="javascript:void(0)" onclick="cargar_url('{{$submenu->url}}')" class="text-color_yura">
                    <i class="fa fa-fw fa-refresh"></i> {{$submenu->nombre}}
                </a>
            </li>
        </ol>
    </section>

    <section class="content">

    <div class="row">     
        <div class="col-md-2"> 
        <div class="form-group input-group">
            <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">Semana</span>
            <input  class="form-control input-yura_default" name="fecha" id="numero_semana" readonly
                                   value="{{$semana->codigo}}">
        </div>
        </div>
        <div class="col-md-3"> 
        <div class="form-group input-group">
            <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">Fecha</span>
            <input type="date" class="form-control input-yura_default" name="fecha" id="fecha"
                                   value="{{date('Y-m-d')}}">
        </div>
        </div>
        <div class="col-md-3">
                        <div class="form-group input-group">
                        <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">Grupo</span>
            <select name="id_actividad" id="id_actividad" class="form-control input-yura_default" onclick="buscar_control_diario()">
            
            @foreach($actividad as $b)
            <option value="{{$b->id_actividad}}">{{$b->area->nombre}} - {{$b->nombre}}</option>
            @endforeach  
            </select>
            <div class="input-group-btn">
                <button type="button" class="btn btn-yura_primary" onclick="buscar_control_diario_general()">
                    <i class="fa fa-fw fa-search"></i>
                </button>
            </div>
        </div>
    </div>
      
     
 
        <div class="col-md-4 text-center"> 
        
        <table class="table-bordered table-striped table-responsive" width="100%" style="border: 2px solid #9d9d9d;" id="tabla_horario">
    <tr>
        <th class="text-center th_yura_green" style="border-color: white; border-radius: 18px 0 0 0">Horario</th>
        <th class="text-center th_yura_green" style="border-color: white;">Desde</th>
        <th class="text-center th_yura_green" style="border-color: white; border-radius: 0 18px 0 0">Hasta</th>
        </th>
    </tr>
    <tr>
    <th class="text-center th_yura_green" style="border-color: white;">Ordinario</th>
        <th class="text-center" style="border-color: white;">
            <input type="time" style="width:100%" id="inicio_hora_ordinario" class="text-center" >
        </th>
        <th class="text-center" style="border-color: white;"><input type="time"  style="width:100%"  id="fin_hora_ordinario" value="fin_hora_ordinario" class="text-center"></th>
    </tr>
    <tr>
    <th class="text-center th_yura_green" style="border-color: white;">50%</th>
        <th class="text-center" style="border-color: white;"><input type="time" style="width:100%"  id="inicio_hora_50" value="inicio_hora_50" class="text-center"></th>
        <th class="text-center" style="border-color: white;"><input type="time" style="width:100%" id="fin_hora_50" value="fin_hora_50" class="text-center"></th>
    </tr>
    <tr>
    <th class="text-center th_yura_green" style="border-color: white;">100%</th>
        <th class="text-center" style="border-color: white;"><input type="time" style="width:100%" id="inicio_hora_100" value="inicio_hora_100" class="text-center"></th>
        <th class="text-center" style="border-color: white;"><input type="time" style="width:100%"  id="fin_hora_100" value="fin_hora_100" class="text-center"></th>
    </tr>
    <tr>
    <th class="text-center th_yura_green" style="border-color: white;">Nocturno</th>
        <th class="text-center" style="border-color: white;"><input type="time" style="width:100%"  id="inicio_hora_nocturno" value="inicio_hora_nocturno" class="text-center"></th>
        <th class="text-center" style="border-color: white;"><input type="time" style="width:100%"  id="fin_hora_nocturno" value="fin_hora_nocturno" class="text-center"></th>
    </tr>
    </table>
    <div class="row">     
        <div class="col-md-2"> 
        <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark "  onclick="listar_grupo_general()">Ver todos los controles</span>
        </div>
    </div>
<button type="button" class="btn btn-xs btn-yura_primary" style="margin-top:5px" onclick="guardar_horario()">
<i class="fa fa-fw fa-save"></i> Guardar
</button>
    
<div>

</div>
</div>
       </div>

       <input type="hidden" id="id_control_diario">

        <div id="div_control_diario" style="margin-top:5px">
        </div>
        </section>
@endsection
@section('script_final')
    @include('adminlte.gestion.rrhh.control_diario.script')
@endsection
<script>
 
 function desde(){
    //alert(7777)
    hora_inicio_fija = $('#inicio_hora_ordinario').val();
    hora_final_fija = $('#fin_hora_ordinario').val();
  //  alert(hora_final_fija)
    cant_registros = $('#cant_registros').val();
    for(i=0; i<=cant_registros; i++){
        if($('#check_trabajador_'+i).prop('checked') === true){
            $('#desde_'+i).val(hora_inicio_fija);
            $('#hasta_'+i).val(hora_final_fija);
        }
    }
}
</script>