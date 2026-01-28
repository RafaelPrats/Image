@extends('layouts.adminlte.master')

@section('titulo')
    Proyección de labores
@endsection

@section('script_inicio')
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Proyección de labores
            <small class="text-color_yura">módulo Campo</small>
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
                    <i class="fa fa-fw fa-refresh"></i> {{$submenu->nombre}}
                </a>
            </li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d">
            <tr>
                <td style="width: 155px">
                    <div class="form-group input-group">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-calendar"></i> Desde
                        </div>
                        <input type="number" class="form-control text-center input-yura_default" id="filtro_desde"
                               name="filtro_desde" required
                               value="{{getSemanaByDate(date('Y-m-d'))->codigo}}">
                    </div>
                </td>
                <td style="padding-left: 5px; padding-right: 5px; width: 160px">
                    <div class="form-group input-group">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-calendar"></i> Hasta
                        </div>
                        <input type="number" class="form-control text-center input-yura_default" id="filtro_hasta"
                               name="filtro_hasta" required value="{{$semana_hasta->codigo}}">
                    </div>
                </td>
                <td>
                    <div class="form-group input-group">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-leaf"></i> Var.
                        </div>
                        <select name="filtro_planta" id="filtro_planta" class="form-control input-yura_default"
                                onchange="select_planta($(this).val(), 'filtro_variedad', 'div_cargar_variedades', '<option value=T selected>Todos los tipos</option>')">
                            <option value="">Todas las variedades</option>
                            @foreach($plantas as $p)
                                <option value="{{$p->id_planta}}" {{$p->siglas == 'GYP' ? 'selected' : ''}}>{{$p->nombre}}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td style="padding-left: 5px">
                    <div class="form-group input-group">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-leaf"></i> Tipo
                        </div>
                        <select name="filtro_variedad" id="filtro_variedad"
                                class="form-control input-yura_default">
                            <option value="" selected>Seleccione</option>
                        </select>
                    </div>
                </td>
                <td style="padding-left: 5px">
                    <div class="form-group input-group">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-flask"></i> Uso
                        </div>
                        <select name="filtro_uso" id="filtro_uso" class="form-control input-yura_default">
                            <option value="S">Sanidad</option>
                            <option value="C">Cultural</option>
                        </select>
                    </div>
                </td>
                <td style="padding-left: 5px">
                    <div class="form-group input-group">
                        <select name="filtro_reporte" id="filtro_reporte" class="form-control input-yura_default">
                            <option value="M">Módulos</option>
                            <option value="L">Labores</option>
                        </select>
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_primary" onclick="buscar_listado()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <div id="div_content_proyecciones">
        </div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.campo.proyecciones.script')
@endsection