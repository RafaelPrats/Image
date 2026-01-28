@extends('layouts.adminlte.master')

@section('titulo')
    Tabla - Postcosecha
@endsection

@section('css_inicio')
@endsection

@section('script_inicio')
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Tabla
            <small class="text-color_yura">Postcosecha</small>
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
                <td colspan="2">
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-calendar-check-o"></i> Rango
                        </div>
                        <select name="rango" id="rango" class="form-control input-yura_default"
                            onchange="$('.filtro_rango').toggleClass('hidden')">
                            <option value="S">Semanal</option>
                            <option value="M">Mensual</option>
                        </select>

                        <div class="input-group-btn bg-yura_dark filtro_rango hidden">
                            <button type="button" class="btn dropdown-toggle bg-yura_dark" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-calendar"></i> Desde <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                @foreach (getMeses() as $pos => $m)
                                    <li>
                                        <a href="javascript:void(0)"
                                            onclick="select_mes('{{ $pos + 1 }}', 'desde_mensual')"
                                            class="{{ $pos + 1 == 1 ? 'bg-aqua-active' : '' }} li_mes_desde_mensual"
                                            id="li_mes_desde_mensual_{{ $pos + 1 }}">
                                            {{ $m }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <input type="number" class="form-control text-center input-yura_default filtro_rango hidden"
                            id="desde_mensual" placeholder="Desde" min="1" max="12" value="01"
                            onkeypress="return isNumber(event)" readonly maxlength="2">

                        <div class="input-group-addon bg-yura_dark filtro_rango">
                            <i class="fa fa-calendar"></i> Desde
                        </div>
                        <input type="number" class="form-control input-yura_default text-center filtro_rango"
                            id="desde_semanal" placeholder="Hasta"
                            value="{{ substr(getSemanaByDate(opDiasFecha('-', 28, hoy()))->codigo, 2) }}" maxlength="2"
                            onkeypress="return isNumber(event)">

                        <div class="input-group-btn bg-gray filtro_rango hidden">
                            <button type="button" class="btn btn-default dropdown-toggle bg-yura_dark"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-calendar"></i> Hasta
                                <span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                @foreach (getMeses() as $pos => $m)
                                    <li>
                                        <a href="javascript:void(0)"
                                            onclick="select_mes('{{ $pos + 1 }}', 'hasta_mensual')"
                                            class="{{ $pos + 1 == date('m') ? 'bg-aqua-active' : '' }} li_mes_hasta_mensual"
                                            id="li_mes_hasta_mensual_{{ $pos + 1 }}">
                                            {{ $m }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <input type="number" class="form-control input-yura_default text-center filtro_rango hidden"
                            id="hasta_mensual" placeholder="Hasta" min="1" max="12"
                            value="{{ date('m') }}" onkeypress="return isNumber(event)" maxlength="2" readonly>

                        <div class="input-group-addon bg-yura_dark filtro_rango">
                            <i class="fa fa-calendar"></i> Hasta
                        </div>
                        <input type="number" class="form-control input-yura_default text-center filtro_rango"
                            id="hasta_semanal" placeholder="Hasta" maxlength="2"
                            value="{{ substr($semana_pasada->codigo, 2) }}" onkeypress="return isNumber(event)">

                        <div class="input-group-btn bg-yura_dark">
                            <button type="button" class="btn btn-default dropdown-toggle bg-yura_dark"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-calendar-minus-o"></i> Años
                                <span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                @foreach ($annos as $a)
                                    <li>
                                        <a href="javascript:void(0)" onclick="select_anno('{{ $a }}')"
                                            class="{{ $a == date('Y') ? 'bg-aqua-active' : '' }} li_anno"
                                            id="li_anno_{{ $a }}">
                                            {{ $a }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <input type="text" class="form-control text-center input-yura_default" placeholder="Años"
                            id="annos" name="annos" readonly value="{{ date('Y') }}">
                    </div>
                </td>
            </tr>
            <tr>
                <td style="width: 25%">
                    <div class="input-group div_input_group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-fw fa-filter"></i>
                        </span>
                        <select name="tipo_listado" id="tipo_listado" class="form-control" style="width: 100%">
                            <option value="P">Listar Variedades</option>
                            <option value="V">Listar Colores</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            <i class="fa fa-fw fa-tree"></i>
                        </span>
                        <select name="planta" id="planta" class="input-yura_default form-control"
                            style="width: 100%">
                            <option value="T">Todas las Flores</option>
                            @foreach ($plantas as $p)
                                <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                        <span class="input-group-addon bg-yura_dark">
                            <i class="fa fa-filter"></i>
                        </span>
                        <select name="criterio" id="criterio" class="form-control input-yura_default">
                            <option value="C">Cosecha</option>
                            <option value="P">Postcosecha</option>
                            <option value="D">Flor de Baja</option>
                        </select>
                        <div class="input-group-btn">
                            <button type="button" id="btn_filtrar" class="btn btn-yura_dark" onclick="filtrar_tablas()"
                                title="Buscar">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                            {{--<button type="button" id="btn_exportar" class="btn btn-yura_default"
                                onclick="exportar_tabla()" title="Exportar">
                                <i class="fa fa-fw fa-file-excel-o"></i>
                            </button>--}}
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
    @include('adminlte.crm.tbl_postcosecha.script')
@endsection
