@extends('layouts.adminlte.master')

@section('titulo')
    Distribucion
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Distribucion
            <small class="text-color_yura">módulo de postcosecha</small>
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
                <td class="text-center" style="width: 25%">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            Variedad
                        </span>
                        <select name="filtro_planta" id="filtro_planta" class="form-control" style="width: 100%"
                            onchange="select_planta($(this).val(), 'filtro_variedad', 'td_cargar_variedades', '<option value=>Todos</option>', '')">
                            <option value="">Todas</option>
                            @foreach ($plantas as $p)
                                <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td class="text-center" id="td_cargar_variedades" style="width: 25%">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            <i class="fa fa-fw fa-leaf"></i>
                        </span>
                        <select name="filtro_variedad" id="filtro_variedad" class="form-control" style="width: 100%"
                            required>
                            <option value="">Todos los colores</option>
                        </select>
                    </div>
                </td>
                <td class="text-center" style="width: 25%">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            <i class="fa fa-fw fa-leaf"></i>
                        </span>
                        <input type="date" name="filtro_fecha" id="filtro_fecha" class="form-control" style="width: 100%"
                            required value="{{ hoy() }}">
                    </div>
                </td>
                <td class="text-center" style="width: 25%">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Operario
                        </span>
                        <select name="filtro_clasificador" id="filtro_clasificador" class="form-control"
                            style="width: 100%">
                            <option value="">Todos</option>
                            @foreach ($clasificadores as $c)
                                <option value="{{ $c->id_clasificador }}">{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-yura_dark" onclick="listar_reporte()">
                                <i class="fa fa-fw fa-search"></i>
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
        .tr_fija_top_0 {
            position: sticky;
            top: 0;
            z-index: 9;
        }

        .tr_fija_bottom_0 {
            position: sticky;
            bottom: 0;
            z-index: 9;
        }

        .columna_fija_left_0 {
            position: sticky;
            left: 0;
            z-index: 9;
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.postcocecha.distribucion_posco.script')
@endsection
