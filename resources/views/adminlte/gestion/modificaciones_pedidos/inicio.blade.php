@extends('layouts.adminlte.master')

@section('titulo')
    Modificaciones de pedidos
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Modificaciones de pedidos
            <small class="text-color_yura">módulo de comercialización</small>
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
                            class="form-control text-center" style="width: 100%" value="{{ hoy() }}">
                    </div>
                </td>
                <td>
                    <div class="input-group div_filtro">
                        <span class="input-group-addon bg-yura_dark">
                            Cliente
                        </span>
                        <select name="filtro_cliente" id="filtro_cliente" class="form-control select2" style="width: 100%">
                            <option value="">Todos</option>
                            @foreach ($clientes as $p)
                                <option value="{{ $p->id_cliente }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group div_filtro">
                        <span class="input-group-addon bg-yura_dark">
                            Variedad
                        </span>
                        <select name="filtro_planta" id="filtro_planta" class="form-control select2" style="width: 100%">
                            <option value="">Todas</option>
                            @foreach ($plantas as $p)
                                <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-yura_dark" onclick="listar_reporte()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                            <button type="button" class="btn btn-yura_default" onclick="exportar_reporte()"
                                title="Exportar Excel">
                                <i class="fa fa-fw fa-file-excel-o"></i>
                            </button>
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        <div id="div_listado" style="margin-top: 10px; overflow-x: scroll; overflow-y: scroll; max-height: 700px">
        </div>
    </section>

    <style>
        #tr_fija_top_0 th {
            position: sticky;
            top: 0;
            z-index: 9;
        }

        .columna_fija_left_0 {
            position: sticky;
            left: 0;
            z-index: 9;
        }

        div.div_filtro span.select2-selection {
            border-radius: 0;
            height: 34px;
            border-color: #707070;
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.modificaciones_pedidos.script')
@endsection
