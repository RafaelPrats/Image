@extends('layouts.adminlte.master')

@section('titulo')
    Cosecha diaria
@endsection

@section('script_inicio')
    <script></script>
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Reporte
            <small class="text-color_yura">Cosecha diaria</small>
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
                    <i class="fa fa-fw fa-refresh"></i> {!! $submenu->nombre !!}
                </a>
            </li>
        </ol>
    </section>

    <section class="content">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            Variedad
                        </span>
                        <select name="filtro_predeterminado_planta" id="filtro_predeterminado_planta"
                            class="form-control">
                            <option value="T">Todas las variedades</option>
                            @foreach ($plantas as $p)
                                <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Desde
                        </span>
                        <input type="date" class="form-control" id="filtro_predeterminado_desde"
                            name="filtro_predeterminado_desde" required value="{{ $desde }}">
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Hasta
                        </span>
                        <input type="date" class="form-control" id="filtro_predeterminado_hasta"
                            name="filtro_predeterminado_hasta" required value="{{ $hasta }}">
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Tipo
                        </span>
                        <select class="form-control" id="filtro_predeterminado_tipo"
                            name="filtro_predeterminado_tipo">
                            <option value="C">Cosecha</option>
                            <option value="S">Sobrantes</option>
                        </select>
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_primary" onclick="listar_reporte()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <div id="div_reporte" style="margin-top: 5px; overflow-y: scroll; overflow-x: scroll; max-height: 700px"></div>
        </div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.crm.cosecha_diaria.script')
@endsection
