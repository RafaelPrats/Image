@extends('layouts.adminlte.master')

@section('titulo')
    Postcosecha
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Postcosecha <b class="text-color_yura">NEW</b>
            <small class="text-color_yura">módulo de postcosecha</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('')"><i class="fa fa-home"></i>
                    Inicio</a></li>
            <li class="text-color_yura">
                {{ $submenu->menu->grupo_menu->nombre }}
            </li>
            <li class="text-color_yura">
                {{ $submenu->menu->nombre }}
            </li>

            <li class="active">
                <a href="javascript:void(0)" onclick="cargar_url('{{ $submenu->url }}')" class="text-color_yura">
                    <i class="fa fa-fw fa-refresh"></i> {{ $submenu->nombre }}
                </a>
            </li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <table style="width: 100%">
            <tr>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-fw fa-calendar"></i>
                        </span>
                        <input type="date" name="filtro_fecha" id="filtro_fecha" class="form-control" style="width: 100%"
                            value="{{ hoy() }}">
                    </div>
                </td>
                <td class="text-center">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            <i class="fa fa-fw fa-leaf"></i>
                        </span>
                        <select name="filtro_planta" id="filtro_planta" class="form-control" style="width: 100%"
                            onchange="select_planta($(this).val(), 'filtro_variedad', 'td_cargar_variedades', '<option value=>Todos</option>', ''); buscar_presentaciones()">
                            <option value="">Seleccione</option>
                            @foreach ($plantas as $item)
                                <option value="{{ $item->id_planta }}">{{ $item->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td class="text-center" id="td_cargar_variedades">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Color
                        </span>
                        <select name="filtro_variedad" id="filtro_variedad" class="form-control" style="width: 100%"
                            required="">
                            <option value="">Todos</option>
                        </select>
                    </div>
                </td>
                <td class="text-center">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Dias
                        </span>
                        <select name="filtro_dias" id="filtro_dias" class="form-control" style="width: 100%"
                            onchange="buscar_presentaciones()">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Pres.
                        </span>
                        <select name="filtro_presentacion" id="filtro_presentacion" class="form-control" style="width: 100%"
                            required="">
                            <option value="">Todos</option>
                        </select>
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-yura_dark" onclick="listar_reporte()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        <div id="div_listado" style="margin-top: 5px;">
        </div>
    </section>

    <style>
        .columna_fija_left_0 {
            position: sticky;
            left: 0;
            z-index: 9;
        }

        .tr_fija_bottom_0 {
            position: sticky;
            bottom: 0;
            z-index: 9;
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.comercializacion.postcosecha.script')
@endsection
