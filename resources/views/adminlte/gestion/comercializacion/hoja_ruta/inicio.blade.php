@extends('layouts.adminlte.master')

@section('titulo')
    Hoja de Ruta
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Hoja de Ruta <b class="text-color_yura">NEW</b>
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
                            Cliente
                        </span>
                        <select name="filtro_cliente" id="filtro_cliente" class="form-control" style="width: 100%">
                            <option value="T">Todos</option>
                            @foreach ($clientes as $c)
                                <option value="{{ $c->id_cliente }}">{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Agencia
                        </span>
                        <select name="filtro_agencia" id="filtro_agencia" class="form-control" style="width: 100%">
                            <option value="T">Todas</option>
                            @foreach ($agencias as $t)
                                <option value="{{ $t->id_agencia_carga }}">{{ $t->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Desde
                        </span>
                        <input type="date" name="filtro_desde" id="filtro_desde" class="form-control" style="width: 100%"
                            value="{{ hoy() }}">
                    </div>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Hasta
                        </span>
                        <input type="date" name="filtro_hasta" id="filtro_hasta" class="form-control" style="width: 100%"
                            value="{{ hoy() }}">
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
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.comercializacion.hoja_ruta.script')
@endsection
