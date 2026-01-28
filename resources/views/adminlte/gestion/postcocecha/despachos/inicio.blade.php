@extends('layouts.adminlte.master')

@section('titulo')
    Despachos
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Despachos <b class="text-color_yura_danger">OLD</b>
            <small>módulo de postcosecha</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" onclick="cargar_url('')"><i class="fa fa-home"></i> Inicio</a></li>
            <li>
                {{ $submenu->menu->grupo_menu->nombre }}
            </li>
            <li>
                {{ $submenu->menu->nombre }}
            </li>

            <li class="active">
                <a href="javascript:void(0)" onclick="cargar_url('{{ $submenu->url }}')">
                    <i class="fa fa-fw fa-refresh"></i> {{ $submenu->nombre }}
                </a>
            </li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">
                    Empaquetado
                </h3>
                <div class="form-group pull-right" style="margin: 0">
                    {{-- <label for="fecha_pedidos_search" style="margin-right: 10px">Fecha de pedidos</label> --}}
                    <select id="id_cliente" name="id_cliente" style="height: 26px;width:250px"
                        onchange="filtrar_listado_despachos_js(this,'cliente_pedido')">
                        <option value="">Clientes</option>
                        @foreach ($clientes as $cliente)
                            <option value="{{ $cliente->id_cliente }}"> {{ $cliente->nombre }} </option>
                        @endforeach
                    </select>
                    <select id="id_agencia_carga" name="id_agencia_carga" style="height: 26px;width:250px"
                        onchange="filtrar_listado_despachos_js(this,'agencia_pedido')">
                        <option value="">Agencias</option>
                        @foreach ($agenciasCarga as $ac)
                            <option value="{{ $ac->id_agencia_carga }}"> {{ $ac->nombre }} </option>
                        @endforeach
                    </select>
                    <input type="date" name="fecha_pedidos_search" id="fecha_pedidos_search"
                        value="{{ \Carbon\Carbon::now()->toDateString() }}">
                    <input type="date" name="fecha_pedidos_search_hasta" id="fecha_pedidos_search_hasta"
                        value="{{ \Carbon\Carbon::now()->toDateString() }}">
                    <select id="id_configuracion_empresa_despacho" name="id_configuracion_empresa_despacho"
                        style="height: 26px;" onchange="desbloquea_pedido()">
                        {{-- <option value="">Ver pedido de:</option> --}}
                        @foreach ($empresas as $x => $emp)
                            <option {{ $x == 0 ? 'selected' : '' }} value="{{ $emp->id_configuracion_empresa }}">
                                {{ $emp->nombre }}</option>
                        @endforeach
                    </select>
                    <button id="button_busqueda_detalles_despacho" class="btn btn-sm btn-primary" style="position: relative;bottom: 2px;right: 3px;"
                        onclick="listar_resumen_pedidos(document.getElementById('fecha_pedidos_search').value,
                                    '',document.getElementById('id_configuracion_empresa_despacho').value,
                                    document.getElementById('id_cliente').value)">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="box-body" id="div_content_blanco">
                <div id="div_listado_blanco"></div>
            </div>
        </div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.postcocecha.despachos.script')
@endsection

<style>
    .select2-selection {
        height: 15px !important;
        z-index: 999999999 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #444;
        line-height: 22px !important;
    }

    .select2-container--default .select2-selection--single {
        height: 25px !important;
    }
</style>
