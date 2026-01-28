@extends('layouts.adminlte.master')

@section('titulo')
    Desecho Cuarto Frio
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Desecho Cuarto Frio
            <small class="text-color_yura">módulo de postcosecha</small>
        </h1>

        <ol class="breadcrumb">
            <li>
                <a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('')">
                    <i class="fa fa-home"></i>Inicio
                </a>
            </li>
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
                            Desde
                        </span>
                        <input type="date" id="filtro_desde" style="width: 100%" class="form-control text-center"
                            value="{{ hoy() }}">
                    </div>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Hasta
                        </span>
                        <input type="date" id="filtro_hasta" style="width: 100%" class="form-control text-center"
                            value="{{ hoy() }}">
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
    @include('adminlte.gestion.postcocecha.desechos_frio.script')
@endsection
