@extends('layouts.adminlte.master')

@section('titulo')
    Cuadre de Flor
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Cuadre de Flor
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
                            Fecha
                        </span>
                        <select id="filtro_fecha" style="width: 100%" class="form-control input-yura_default">
                            <option value="{{ hoy() }}" selected>
                                Hoy {{ convertDateToText(hoy()) }}
                            </option>
                            <option value="{{ opDiasFecha('+', 1, hoy()) }}">
                                Mañana {{ convertDateToText(opDiasFecha('+', 1, hoy())) }}
                            </option>
                            <option value="{{ opDiasFecha('+', 2, hoy()) }}">
                                Pasado Mañana {{ convertDateToText(opDiasFecha('+', 2, hoy())) }}
                            </option>
                        </select>
                    </div>
                </td>
                <td class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            Variedad
                        </span>
                        <select name="filtro_planta" id="filtro_planta" class="input-yura_default form-control"
                            style="width: 100%"
                            onchange="seleccionar_planta($(this).val(), 'filtro_longitud', 'td_cargar_longitudes', '')">
                            <option value="">Seleccione</option>
                            @foreach ($plantas as $p)
                                <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td class="text-center padding_lateral_5" style="border-color: #9d9d9d" id="td_cargar_longitudes">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            Longitudes
                        </span>
                        <select name="filtro_longitud" id="filtro_longitud" class="input-yura_default form-control"
                            style="width: 100%">
                            <option value="">Selccione una Planta</option>
                        </select>
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-yura_dark" onclick="listar_reporte()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                            <button type="button" class="btn btn-yura_default" title="Exportar"
                                onclick="exportar_reporte()">
                                <i class="fa fa-fw fa-file-excel-o"></i>
                            </button>
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        <div id="div_listado" style="margin-top: 10px">
        </div>
    </section>

    <style>
        .columna_fija_left_0 {
            position: sticky;
            left: 0;
            z-index: 9;
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.postcocecha.cuadre_flor.script')
@endsection
