@extends('layouts.adminlte.master')

@section('titulo')
    Monitoreo de Ciclos
@endsection

@section('script_inicio')
    <script>
    </script>
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Monitoreo
            <small class="text-color_yura">de ciclos</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('')"><i class="fa fa-home"></i> Inicio</a></li>
            <li class="text-color_yura">
                {{$submenu->menu->grupo_menu->nombre}}
            </li>
            <li class="text-color_yura">
                {{$submenu->menu->nombre}}
            </li>

            <li class="active">
                <a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('{{$submenu->url}}')">
                    <i class="fa fa-fw fa-refresh"></i> {!! $submenu->nombre !!}
                </a>
            </li>
        </ol>
    </section>

    <section class="content">
        <div class="input-group">
            <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                <i class="fa fa-fw fa-map-signs"></i>
            </div>
            <select name="filtro_sector" id="filtro_sector" class="form-control input-yura_default">
                <option value="T">Todos</option>
                @foreach($sectores as $s)
                    <option value="{{$s->id_sector}}">{{$s->nombre}}</option>
                @endforeach
            </select>
            <div class="input-group-addon bg-yura_dark">
                <i class="fa fa-fw fa-leaf"></i>
            </div>
            <select name="filtro_predeterminado_planta" id="filtro_predeterminado_planta" class="form-control input-yura_default"
                    onchange="select_planta($(this).val(), 'filtro_predeterminado_variedad', 'div_cargar_variedades', '<option value=T selected>Todos los tipos</option>')">
                <option value="">Todas las variedades</option>
                @foreach(getPlantas() as $p)
                    <option value="{{$p->id_planta}}" {{$p->siglas == 'GYP' ? 'selected' : ''}}>{{$p->nombre}}</option>
                @endforeach
            </select>
            <div class="input-group-addon bg-yura_dark" id="div_cargar_variedades">
                <i class="fa fa-fw fa-leaf"></i>
            </div>
            <select name="filtro_predeterminado_variedad" id="filtro_predeterminado_variedad" class="form-control input-yura_default">
                <option value="T" selected>Todos los tipos</option>
            </select>
            <div class="input-group-addon bg-yura_dark">
                P/S
            </div>
            <select name="filtro_poda_siembra" id="filtro_poda_siembra" class="form-control input-yura_default">
                <option value="P">Poda</option>
                <option value="S">Siembra</option>
            </select>
            <div class="input-group-addon bg-yura_dark">
                Desde
            </div>
            <input type="number" id="filtro_min_semanas" onkeypress="return isNumber(event)" class="form-control input-yura_default"
                   required value="6" min="1" style="width: 50px">
            <div class="input-group-addon span-input-group-yura-middle bg-yura_dark">
                Hasta
            </div>
            <input type="number" id="filtro_num_semanas" onkeypress="return isNumber(event)" class="form-control input-yura_default"
                   required value="18" min="1" style="width: 60px">
            <div class="input-group-addon bg-yura_dark">
                <i class="fa fa-fw fa-list-alt"></i>
            </div>
            <select name="filtro_reporte" id="filtro_reporte" class="form-control input-yura_default" onchange="listar_ciclos()">
                <option value="1">Alturas</option>
                <option value="2">Azúcar</option>
            </select>
            <div class="input-group-btn">
                <button type="button" class="btn btn-yura_primary" onclick="listar_ciclos()">
                    <i class="fa fa-fw fa-search"></i>
                </button>
            </div>
        </div>
        <div class="box-body" id="div_listado_ciclos" style="margin-top: 10px"></div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.proyecciones.monitoreo.script')
@endsection
