@extends('layouts.adminlte.master')
@section('titulo')
    Resumen total de proyecciones
@endsection

@section('contenido')
    <section class="content-header">
        <h1>
            Proyecciones
            <small class="text-color_yura">resumen total</small>
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
                    <i class="fa fa-fw fa-refresh"></i> {{$submenu->nombre}}
                </a>
            </li>
        </ol>
    </section>
    <section class="content">
        <table style="width: 100%">
            <tr>
                <td style="padding-right: 10px">
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-fw fa-calendar"></i> Desde
                        </div>
                        <input type="number" class="form-control input-yura_default" id="filtro_predeterminado_desde"
                               name="filtro_predeterminado_desde" readonly
                               style="" required value="{{$desde->codigo}}">
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-fw fa-calendar"></i> Hasta
                        </div>
                        <input type="number" class="form-control input-yura_default" id="filtro_predeterminado_hasta"
                               name="filtro_predeterminado_hasta" required
                               value="{{$hasta->codigo}}" style="">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_dark" onclick="listar_proyecciones_resumen_total()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                            <button type="button" class="btn btn-yura_primary" onclick="exportar_reporte()" title="Exportar Excel">
                                <i class="fa fa-fw fa-file-excel-o"></i>
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <div id="listado_proyecciones_resumen_total" style="margin-top: 10px"></div>
    </section>
@endsection
@section('script_final')
    @include('adminlte.gestion.proyecciones.resumen_total.script')
@endsection
