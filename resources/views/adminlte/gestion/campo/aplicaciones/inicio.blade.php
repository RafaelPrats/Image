@extends('layouts.adminlte.master')

@section('titulo')
    Matriz de labores
@endsection

@section('script_inicio')
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Matriz de labores
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
        <table style="width: 100%">
            <tr>
                <td style="width: 25%">
                    <div class="form-group input-group">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-leaf"></i> Variedad
                        </div>
                        <select name="filtro_planta" id="filtro_planta" class="form-control input-yura_default"
                                onchange="select_planta($(this).val(), 'filtro_variedad', 'div_cargar_variedades', '<option value=T selected>Todos los tipos</option>')">
                            <option value="T">Todas las variedades</option>
                            @foreach($plantas as $p)
                                <option value="{{$p->id_planta}}">{{$p->nombre}}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td style="padding-left: 5px; width: 30%">
                    <div class="form-group input-group">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-leaf"></i> Tipo
                        </div>
                        <select name="filtro_variedad" id="filtro_variedad"
                                class="form-control input-yura_default">
                            <option value="T" selected>Todos los tipos</option>
                        </select>
                    </div>
                </td>
                <td style="padding-left: 5px">
                    <div class="form-group input-group">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            Poda/Siembras
                        </div>
                        <select name="filtro_poda_siembra" id="filtro_poda_siembra"
                                class="form-control input-yura_default">
                            <option value="T">Todas</option>
                            <option value="P">Podas</option>
                            <option value="S">Siembras</option>
                        </select>
                    </div>
                </td>
                <td style="padding-left: 5px">
                    <div class="form-group input-group">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            Tipo de labor
                        </div>
                        <select name="filtro_tipo" id="filtro_tipo"
                                class="form-control input-yura_default">
                            <option value="S">Sanidad</option>
                            <option value="C">Cultural</option>
                        </select>
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_dark" onclick="buscar_listado()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <div id="div_content_aplicaciones">
        </div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.campo.aplicaciones.script')
@endsection