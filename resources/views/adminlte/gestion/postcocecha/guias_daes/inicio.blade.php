@extends('layouts.adminlte.master')

@section('titulo')
    Guías -Daes
@endsection

@section('contenido')
    <section class="content-header">
        <h1>
            Guías y Daes <b class="text-color_yura_danger">OLD</b>
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
        <div style="overflow-x: scroll">
            <table width="100%">
                <tr>
                    <td style="width: 50%">
                        <div class="input-group div_group_filtro">
                            <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                                Cliente
                            </span>
                            <select id="id_cliente" name="id_cliente" class="form-control input-yura_default"
                                style="width: 100%" onchange="filtrar_listado_guias_js(this,'id_cliente')">
                                <option value="">TODOS</option>
                                @foreach ($clientes as $cli)
                                    <option value="{{ $cli->id_cliente }}">{{ $cli->nombre }}</option>
                                @endforeach
                            </select>
                            <span class="input-group-addon bg-yura_dark">
                                Agencia de carga
                            </span>
                            <select id="id_agencia_carga" name="id_agencia_carga" class="form-control input-yura_default"
                                style="width: 100%" onchange="filtrar_listado_guias_js(this,'filtro_agencia_carga')">
                                <option value="">TODOS</option>
                                @foreach ($agenciasCarga as $ac)
                                    <option value="{{ $ac->id_agencia_carga }}">{{ $ac->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <span class="input-group-addon bg-yura_dark">
                                Desde
                            </span>
                            <input type="date" class="form-control input-yura_default text-center" style="width: 100%"
                                id="desde" name="desde" value="{{ now()->toDateString() }}">
                            <span class="input-group-addon bg-yura_dark">
                                Hasta
                            </span>
                            <input type="date" class="form-control input-yura_default text-center" style="width: 100%"
                                id="hasta" name="hasta" value="{{ now()->toDateString() }}">
                            <span class="input-group-btn">
                                <button class="btn btn-yura_dark" onclick="listado_guias_daes()">
                                    <i class="fa fa-fw fa-search" style="color: #0c0c0c"></i>
                                    Buscar
                                </button>
                            </span>
                        </div>
                    </td>
                    <td>
                        <label style="visibility: hidden">.</label><br />
                    </td>
                </tr>
            </table>
        </div>
        <div id="div_listado_guias_daes"></div>
        </div>
        </div>
    </section>

    <style>
        /*.select2-selection--single {
                                            height: 34px !important;
                                        }*/

        div.div_group_filtro span.select2-selection {
            top: 0px;
            border-radius: 0px;
            height: 34px;
        }
    </style>
@endsection
@section('script_final')
    @include('adminlte.gestion.postcocecha.guias_daes.script')
@endsection
