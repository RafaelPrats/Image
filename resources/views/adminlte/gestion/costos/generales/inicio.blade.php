@extends('layouts.adminlte.master')

@section('titulo')
    Costos - Generales
@endsection

@section('script_inicio')
    <script>
    </script>
@endsection

@section('css_inicio')
@endsection

@section('contenido')
    <section class="content-header">
        <h1>
            P y G Semanal
            <small class="text-color_yura">Reporte</small>
        </h1>
        <ol class="breadcrumb">
            <li class="text-color_yura"><a href="javascript:void(0)" onclick="cargar_url('')"><i class="fa fa-home"></i> Inicio</a></li>
            <li class="text-color_yura">
                {{$submenu->menu->grupo_menu->nombre}}
            </li>
            <li class="text-color_yura">
                {{$submenu->menu->nombre}}
            </li>

            <li class="active">
                <a href="javascript:void(0)" onclick="cargar_url('{{$submenu->url}}')" class="text-color_yura">
                    <i class="fa fa-fw fa-refresh"></i> {!! $submenu->nombre !!}
                </a>
            </li>
        </ol>
    </section>

    <section class="content">
        <div class="input-group">
            <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                Desde
            </div>
            <input type="number" id="desde" onkeypress="return isNumber(event)" class="form-control text-center input-yura_default"
                   value="{{$semana_desde->codigo}}">
            <div class="input-group-addon bg-yura_dark">
                Hasta
            </div>
            <input type="number" id="hasta" onkeypress="return isNumber(event)" class="form-control text-center input-yura_default"
                   value="{{$semana_actual->codigo}}">
            <div class="input-group-addon bg-yura_dark">
                Variedades
            </div>
            <select id="variedad" class="form-control input-yura_default">
                <option value="T">Todas</option>
                @foreach($variedades as $v)
                    <option value="{{$v->id_variedad}}">{{$v->nombre}}</option>
                @endforeach
            </select>

            <div class="input-group-btn">
                <button type="button" class="btn btn-yura_dark" title="Buscar" onclick="listar_reporte()">
                    <i class="fa fa-fw fa-search"></i>
                </button>
            </div>
        </div>
        <div style="overflow-x: scroll; margin-top: 10px" id="div_reporte"></div>
    </section>
@endsection

@section('script_final')
    <script>
        listar_reporte();

        function listar_reporte() {
            datos = {
                desde: $('#desde').val(),
                hasta: $('#hasta').val(),
                variedad: $('#variedad').val(),
            };

            get_jquery('{{url('costos_generales/listar_reporte')}}', datos, function (retorno) {
                $('#div_reporte').html(retorno);
            }, 'div_reporte');
        }
    </script>
@endsection