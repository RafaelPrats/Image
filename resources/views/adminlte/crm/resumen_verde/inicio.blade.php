@extends('layouts.adminlte.master')

@section('titulo')
    Resumen verde
@endsection

@section('script_inicio')
    <script>
    </script>
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Resumen
            <small class="text-color_yura">clasificación verde</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('')"><i class="fa fa-home"></i> Inicio</a></li>
            <li class="text-color_yura">
                {{$submenu->menu->grupo_menu->nombre}}
            </li>
            <li class="text-color_yura">
                {{$submenu->menu->nombre}}
            </li>

            <li class="active">
                <a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('{{$submenu->url}}')">
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
                        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-fw fa-calendar"></i> Desde
                        </div>
                        <input type="number" class="form-control input-yura_default" id="filtro_predeterminado_desde"
                               name="filtro_predeterminado_desde" required value="{{$desde->codigo}}">
                    </div>
                </td>
                <td style="padding-left: 5px">
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-fw fa-calendar"></i> Hasta
                        </div>
                        <input type="number" class="form-control input-yura_default" id="filtro_predeterminado_hasta"
                               name="filtro_predeterminado_hasta" required value="{{$hasta->codigo}}">
                    </div>
                </td>
                <td style="padding-left: 5px">
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-fw fa-list-alt"></i> Reporte
                        </div>
                        <select class="form-control input-yura_default" id="filtro_reporte">
                            <option value="1">Ventas</option>
                            <option value="2">Producción</option>
                        </select>
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_primary" onclick="buscar_resumen_verde()">
                                <i class="fa fa-fw fa-search"></i> Buscar por semanas
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="filtro_diarios hidden">
                <td style="padding-top: 5px">
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-fw fa-calendar"></i> Desde
                        </div>
                        <input type="date" class="form-control input-yura_default" id="filtro_diario_desde"
                               name="filtro_diario_desde" required>
                    </div>
                </td>
                <td style="padding-left: 5px; padding-top: 5px">
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-fw fa-calendar"></i> Hasta
                        </div>
                        <input type="date" class="form-control input-yura_default" id="filtro_diario_hasta"
                               name="filtro_diario_hasta" required>
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_dark" onclick="listar_resumen_verde_semanal($('#filtro_diario_desde').val(), $('#filtro_diario_hasta').val())">
                                <i class="fa fa-fw fa-search"></i> Buscar por días
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div id="div_listado_resumen_verde" style="margin-top: 5px"></div>
    </section>
@endsection

@section('script_final')

    @include('adminlte.crm.resumen_verde.script')
@endsection