@extends('layouts.adminlte.master')

@section('titulo')
    Resumen Cosecha
@endsection

@section('css_inicio')
@endsection

@section('script_inicio')
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Resumen
            <small class="text-color_yura">Cosecha</small>
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
                <a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('{{ $submenu->url }}')">
                    <i class="fa fa-fw fa-refresh"></i> {{ $submenu->nombre }}
                </a>
            </li>
        </ol>
    </section>

    <section class="content">
        <table style="width: 100%">
            <tr>
                <td colspan="3">
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-calendar-check-o"></i> Rango
                        </div>
                        <select name="rango" id="rango" class="form-control input-yura_default"
                            onchange="$('.filtro_rango').toggleClass('hidden')">
                            <option value="S">Semanal</option>
                            <option value="D">Diario</option>
                        </select>

                        <div class="input-group-addon bg-yura_dark">
                            <i class="fa fa-calendar-check-o"></i> Desde
                        </div>
                        <input type="number" class="form-control text-center input-yura_default filtro_rango"
                            id="desde_semanal" placeholder="Desde" min="0" value="{{ $semana_desde }}"
                            onkeypress="return isNumber(event)" maxlength="4" required>
                        <input type="date" class="form-control text-center input-yura_default filtro_rango hidden"
                            id="desde_diario" placeholder="Desde" required
                            value="{{ opDiasFecha('-', 7, $semana_hasta->fecha_inicial) }}"
                            onkeypress="return isNumber(event)" maxlength="4">

                        <div class="input-group-addon bg-yura_dark">
                            <i class="fa fa-calendar-check-o"></i> Hasta
                        </div>
                        <input type="number" class="form-control text-center input-yura_default filtro_rango"
                            id="hasta_semanal" placeholder="Hasta" min="0" value="{{ $semana_hasta->codigo }}"
                            onkeypress="return isNumber(event)" maxlength="4" required>
                        <input type="date" class="form-control text-center input-yura_default filtro_rango hidden"
                            id="hasta_diario" placeholder="Hasta" required value="{{ $semana_hasta->fecha_final }}"
                            onkeypress="return isNumber(event)" maxlength="4">

                        <div class="input-group-btn">
                            <button type="button" id="btn_filtrar" class="btn btn-yura_dark" onclick="listar_reporte()"
                                title="Buscar">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                            {{-- <button type="button" id="btn_exportar" class="btn btn-yura_primary" onclick="exportar_tabla()"
                                title="Exportar">
                                <i class="fa fa-fw fa-file-excel-o"></i>
                            </button> --}}
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div id="div_contentido_tablas" style="margin-top: 5px"></div>
    </section>

    <style>
        div.div_input_group span.select2-selection {
            top: 0px;
            border-radius: 0px;
            height: 34px;
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.crm.resumen_cosecha.script')
@endsection
