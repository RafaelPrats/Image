@extends('layouts.adminlte.master')

@section('titulo')
    Especificaciones
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Especificaciones <b class="text-color_yura">NEW</b>
            <small class="text-color_yura">módulo de comercializacion</small>
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
                            Cliente <span class="error">*</span>
                        </span>
                        <select name="filtro_cliente" id="filtro_cliente" class="form-control">
                            <option value="">Seleccione</option>
                            @foreach ($clientes as $c)
                                <option value="{{ $c->id_cliente }}">{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Variedad
                        </span>
                        <select name="filtro_planta" id="filtro_planta" class="form-control" style="width: 100%"
                            onchange="select_planta($(this).val(), 'filtro_variedad', 'td_cargar_variedades', '<option value=>Todos</option>', '')">
                            <option value="">Seleccione</option>
                            @foreach ($plantas as $p)
                                <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td class="text-center" style="border-color: #9d9d9d" id="td_cargar_variedades">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Color
                        </span>
                        <select name="filtro_variedad" id="filtro_variedad" class="form-control" style="width: 100%">
                            <option value="">Todos</option>
                        </select>
                    </div>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Tipo Caja
                        </span>
                        <select name="filtro_tipo_caja" id="filtro_tipo_caja" class="form-control" style="width: 100%">
                            <option value="">Todas</option>
                            @foreach ($tipos_caja as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td class="text-center" style="border-color: #9d9d9d" id="td_cargar_longitudes">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Longitud
                        </span>
                        <input name="filtro_longitud" id="filtro_longitud" class="form-control" style="width: 100%">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-yura_dark" onclick="listar_reporte()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                            <button type="button" class="btn btn-yura_default" title="Agregar nuevas especificaciones"
                                onclick="add_especificaciones()">
                                <i class="fa fa-fw fa-plus"></i>
                            </button>
                            <!--<button type="button" class="btn btn-yura_default" onclick="exportar_reporte()"
                                                title="Exportar Excel">
                                                <i class="fa fa-fw fa-file-excel-o"></i>
                                            </button>-->
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
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.comercializacion.especificaciones.script')
@endsection
