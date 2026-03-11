@extends('layouts.adminlte.master')

@section('titulo')
    Cosecha
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Cosecha
            <small class="text-color_yura">módulo de Cosecha</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" onclick="cargar_url('')" class="text-color_yura">
                    <i class="fa fa-home text-color_yura"></i>
                    Inicio</a></li>
            <li class="text-color_yura">
                {{ $submenu->menu->grupo_menu->nombre }}
            </li>
            <li class="text-color_yura">
                {{ $submenu->menu->nombre }}
            </li>

            <li class="active">
                <a href="javascript:void(0)" onclick="cargar_url('{{ $submenu->url }}')" class="text-color_yura">
                    <i class="fa fa-fw fa-refresh text-color_yura"></i> {{ $submenu->nombre }}
                </a>
            </li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <table width="100%" style="margin-bottom: 0">
            <tr>
                <td>
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-fw fa-calendar"></i> Fecha de Cosecha
                        </div>
                        <input type="date" id="filtro_fecha" name="filtro_fecha" required
                            class="form-control input-yura_default text-center" onchange="buscar_listado_recepcion()"
                            style="width: 100% !important;" value="{{ hoy() }}" max="{{ hoy() }}">
                    </div>
                </td>
                <td class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            Planta
                        </span>
                        <select name="filtro_planta" id="filtro_planta" class="input-yura_default form-control"
                            style="width: 100%"
                            onchange="buscarLongitudesByPlanta($(this).val(), 'filtro_longitud', 'td_cargar_longitudes', '')">
                            <option value="">Todas las flores</option>
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
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_primary" onclick="buscar_listado_recepcion()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                            <button type="button" class="btn btn-yura_dark" onclick="add_recepcion()">
                                <i class="fa fa-fw fa-plus"></i>
                            </button>
                            {{-- <button type="button" class="btn btn-yura_default" onclick="ver_sobrantes()"
                                title="Ver Sobrantes">
                                <i class="fa fa-fw fa-gift"></i>
                            </button> --}}
                            {{-- <button type="button" class="btn btn-yura_default" title="Exportar"
                                onclick="exportar_recepcion()">
                                <i class="fa fa-fw fa-file-excel-o"></i>
                            </button> --}}
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <div id="div_listado_recepciones" style="margin-top: 5px; overflow-y: scroll; overflow-x: scroll; height: 700px;">
        </div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.postcocecha.recepciones.script')
@endsection
