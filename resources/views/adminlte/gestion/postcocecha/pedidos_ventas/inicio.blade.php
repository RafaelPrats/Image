@extends('layouts.adminlte.master')

@section('titulo')
    Pedidos
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Pedidos <b class="text-color_yura_danger">OLD</b>
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
        <select id="id_configuracion_pedido" name="id_configuracion_empresa_pedido" class="hide" class="form-control">
            <option value="" disabled selected>Ver pedidos de:</option>
            @foreach ($empresas as $empresa)
                <option selected value="{{ $empresa->id_configuracion_empresa }}">{{ $empresa->nombre }}
                </option>
            @endforeach
        </select>

        <table style="width: 100%" class="div-filtros-pedidos">
            <tr>
                <td style="width: 25%">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            Marcaciones
                        </span>
                        <select id="id_marcacion" name="id_marcacion" class="form-control" style="width: 100%">
                            <option value="">Todas</option>
                        </select>
                    </div>
                </td>
                <td style="width: 25%">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Tipo
                        </span>
                        <select id="tipo_pedido" name="tipo_pedido" class="form-control" style="width: 100%">
                            <option value="">Todos</option>
                            <option value="STANDING ORDER">STANDING ORDER</option>
                            <option value="OPEN MARKET">OPEN MARKET</option>
                        </select>
                    </div>
                </td>
                <td style="width: 25%">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Flor
                        </span>
                        <select id="id_planta" name="id_planta" onchange="get_variedad()" class="form-control"
                            style="width: 100%">
                            <option value="">Todas</option>
                            @foreach ($plantas as $planta)
                                <option value="{{ $planta->id_planta }}">{{ $planta->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td style="width: 25%" colspan="2">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Color
                        </span>
                        <select id="id_variedad" name="id_variedad" class="form-control" style="width: 100%">
                            <option value="">Todos</option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            Clientes
                        </span>
                        <select id="id_cliente" name="id_cliente" class="form-control" style="width: 100%">
                            <option value="">Todos</option>
                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id_cliente }}"> {{ $cliente->nombre }} </option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Desde
                        </span>
                        <input type="date" name="fecha_pedidos_search" id="fecha_pedidos_search" class="form-control"
                            value="{{ \Carbon\Carbon::now()->toDateString() }}">
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Hasta
                        </span>
                        <input type="date" name="fecha_pedidos_search_hasta" id="fecha_pedidos_search_hasta"
                            class="form-control" value="{{ \Carbon\Carbon::now()->toDateString() }}">
                    </div>
                </td>
                <td>
                    <button class="btn btn-yura_dark btn-block" style="border-radius: 0 !important"
                        onclick="listar_resumen_pedidos(document.getElementById('fecha_pedidos_search').value, true,document.getElementById('id_configuracion_pedido').value, document.getElementById('id_cliente').value)">
                        <i class="fa fa-fw fa-search"></i>
                    </button>
                </td>
                <td>
                    <div class="btn-group" style="width: 100%">
                        {{-- <button class="btn btn-yura_default" type="button" title="Ver Resumen" onclick="ver_resumen()">
                        Resumen
                    </button> --}}
                        <button class="btn btn-yura_primary" type="button" onclick="agregar_pedido()"
                            style="border-radius: 0 !important">
                            <i class="fa fa-plus" aria-hidden="true"></i>
                            Agregar
                        </button>
                        <button class="btn btn-yura_default" type="button" title="Ver segundo plano"
                            onclick="ver_segundo_plano()">
                            <i class="fa fa-cogs"></i>
                        </button>
                        {{--
                            <ul class="dropdown-menu dropdown-menu-right sombra_estandar" style="background-color: #c8c8c8">
                                <li>
                                    <a href="javascript:void(0)" style="color: black" onclick="add_pedido('','','pedidos')">
                                        <i class="fa fa-fw fa-file-o"></i> Pedido - Open Market
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" style="color: black" onclick="add_pedido('', true,'pedidos')">
                                        <i class="fa fa-fw fa-copy"></i> Pedido fijo - Standing Order
                                    </a>
                                </li>
                            </ul>
                             --}}
                    </div>
                </td>
            </tr>
        </table>

        <div style="margin-top: 5px" id="div_content_pedidos">
            <div id="div_listado_blanco"></div>
        </div>
    </section>
    <style>
        td span.select2-selection {
            height: 34px !important;
            z-index: 1 !important;
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.postcocecha.pedidos_ventas.script')
@endsection
