@extends('layouts.adminlte.master')

@section('titulo')
    Resumen Plantas Madres
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Resumen Total
            <small>módulo de propagación</small>
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

    <!-- Main content -->
    <section class="content">
        <table style="width: 100%">
            <tr>
                <td style="padding-right: 5px">
                    <div class="input-group">
                        <select style="width: 100%" id="tipo_reporte" class="form-control input-yura_default">
                            <option value="enraizamiento">Enraizamiento</option>
                            <option value="plantas_madres">Ptas Madres</option>
                        </select>
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_primary" onclick="exportar_disponibilidades(true)" title="Exportar Excel">
                                <i class="fa fa-fw fa-file-excel-o"></i>
                            </button>
                        </div>
                    </div>
                </td>
                <td style="padding-right: 5px">
                    <div class="input-group">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-calendar"></i> Desde
                        </div>
                        <input type="number" class="form-control input-yura_default" id="filtro_predeterminado_desde"
                               name="filtro_predeterminado_desde" required
                               value="{{$semana_desde->codigo}}">
                    </div>
                </td>
                <td style="padding-right: 5px;">
                    <div class="input-group">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-calendar"></i> Hasta
                        </div>
                        <input type="number" class="form-control input-yura_default" id="filtro_predeterminado_hasta"
                               name="filtro_predeterminado_hasta" required
                               value="{{$semana_hasta->codigo}}">
                    </div>
                </td>
                <td style="padding-right: 5px;">
                    <div class="input-group">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-leaf"></i> Variedad
                        </div>
                        <select name="filtro_predeterminado_planta" id="filtro_predeterminado_planta" class="form-control input-yura_default"
                                onchange="select_planta($(this).val(), 'filtro_predeterminado_variedad', 'div_cargar_variedades', '<option value=T selected>Todos los tipos</option>')">
                            <option value="" selected="">Todas las variedades</option>
                            @foreach($plantas as $p)
                                <option value="{{$p->id_planta}}">{{$p->nombre}}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group" id="div_cargar_variedades">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-leaf"></i> Tipo
                        </div>
                        <select name="filtro_predeterminado_variedad" id="filtro_predeterminado_variedad"
                                class="form-control input-yura_default">
                            <option value="T" selected>Todos los tipos</option>
                        </select>
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_dark" onclick="listar_resumen()" title="Buscar">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div id="listado_resumen" style="margin-top: 10px"></div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.propagacion.resumen_ptas_madres.script')
@endsection