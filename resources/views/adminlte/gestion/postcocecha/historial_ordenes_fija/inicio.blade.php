@extends('layouts.adminlte.master')

@section('titulo')
    Historial Orden Fija
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Historial Orden Fija
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
                            Fecha
                        </span>
                        <input type="date" id="filtro_fecha" name="filtro_fecha" required
                            class="form-control input-yura_default text-center" style="width: 100%"
                            value="{{ hoy() }}" onchange="listar_reporte()">
                    </div>
                </td>
                <td class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            Cliente
                        </span>
                        <select name="filtro_cliente" id="filtro_cliente" class="input-yura_default form-control"
                            style="width: 100%">
                            <option value="">Todos</option>
                            @foreach ($clientes as $c)
                                <option value="{{ $c->id_cliente }}">{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-yura_dark" onclick="listar_reporte()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                            <button class="btn btn-yura_default" type="button" title="Ver segundo plano"
                                onclick="ver_segundo_plano()">
                                <i class="fa fa-cogs"></i>
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

        <div id="div_listado" style="margin-top: 10px">
        </div>
    </section>

    <style>
        .tr_fija_top_0 {
            position: sticky;
            top: 0;
            z-index: 9;
        }

        .columna_fija_left_0 {
            position: sticky;
            left: 0;
            z-index: 8;
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.postcocecha.historial_ordenes_fija.script')
@endsection
