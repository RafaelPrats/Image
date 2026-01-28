@extends('layouts.adminlte.master')

@section('titulo')
    Etiquetas
@endsection

@section('contenido')
    <section class="content-header">
        <h1>
            Etiquetas <b class="text-color_yura_danger">OLD</b>
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
    <section class="content">
        <div id="div_content_etiquetas">
            <table style="width: 100%">
                <tr>
                    <td>
                        <div class="input-group">
                            <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                                Fecha
                            </span>
                            <input type="date" id="desde" name="desde" required
                                class="form-control input-yura_default text-center" style="width: 100%"
                                value="{{ hoy() }}">
                        </div>
                    </td>
                    <td>
                        <div class="input-group div_group_filtro">
                            <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                                Clientes
                            </span>
                            <select id="id_cliente" name="id_cliente" class="form-control input-yura_default"
                                onchange="filtrar_listado_etiquetas_js(this,'filtro_cliente')" style="width: 100%">
                                <option value="T">TODOS</option>
                                @foreach ($clientes as $cli)
                                    <option value="{{ $cli->id_cliente }}">{{ $cli->nombre }}</option>
                                @endforeach
                            </select>
                            <span class="input-group-addon bg-yura_dark">
                                Agencia de carga
                            </span>
                            <select id="id_agencia_carga" name="id_agencia_carga" class="form-control input-yura_default"
                                style="width: 100%" onchange="filtrar_listado_etiquetas_js(this,'filtro_agencia_carga')">
                                <option value="T">TODOS</option>
                                @foreach ($agenciasCarga as $ac)
                                    <option value="{{ $ac->id_agencia_carga }}">{{ $ac->nombre }}</option>
                                @endforeach
                            </select>
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-yura_dark" onclick="listado_etiquetas()">
                                    <i class="fa fa-fw fa-search"></i>
                                </button>
                                @if (!es_server())
                                    <button type="button" class="btn btn-yura_primary" onclick="imprimir_etiquetas()">
                                        <i class="fa fa-file-excel-o" aria-hidden="true"></i> Imprimir Etiqueta
                                    </button>
                                @endif
                            </span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div id="div_listado_etiquetas" style="margin-top: 5px"></div>
    </section>

    <style>
        div.div_group_filtro span.select2-selection {
            top: 0px;
            border-radius: 0px;
            height: 34px;
        }

        #tr_fija_top_0 th {
            position: sticky;
            top: 0;
            z-index: 9;
        }

        .progress-bar-full {
            width: 100% !important;
            background-color: #00B388 !important;
        }

        .progress-bar-bench {
            background-color: #b32a00 !important;
        }

        .progress-bar-bench-50 {
            background-color: #b37a00 !important;
        }

        .progress-bar-bench-80 {
            background-color: #00B388 !important;
        }

        .progress-bar {
            padding: auto 8px !important;
        }

        .progress {
            background-color: #5A7177 !important;
            border-radius: 10px !important;
        }
    </style>
@endsection
@section('script_final')
    @include('adminlte.gestion.postcocecha.etiquetas.script')
@endsection
