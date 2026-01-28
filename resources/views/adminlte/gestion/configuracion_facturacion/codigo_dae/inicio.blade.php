@extends('layouts.adminlte.master')

@section('titulo')
    Código DAE
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Código DAE
            <small class="text-color_yura">módulo de parametros de facturación</small>
        </h1>
        <ol class="breadcrumb">
            <li class="text-color_yura"><a href="javascript:void(0)" onclick="cargar_url('')"><i class="fa fa-home"></i> Inicio</a></li>
            <li class="text-color_yura">
                {{$submenu->menu->grupo_menu->nombre}}
            </li>
            <li class="text-color_yura">
                {{$submenu->menu->nombre}}
            </li>

            <li class="active">
                <a href="javascript:void(0)" onclick="cargar_url('{{$submenu->url}}')" class="text-color_yura">
                    <i class="fa fa-fw fa-refresh"></i> {!! $submenu->nombre !!}
                </a>
            </li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div id="div_content_codigo_dae">
            <table width="100%" style="margin-bottom: 5px">
                <tr>
                    <td>
                        <div class="input-group">
                            <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                                País
                            </div>
                            <input type="text" class="form-control input-yura_default text-center" id="input_busqueda_dae"
                                   placeholder="Búsqueda por país">
                            <div class="input-group-addon bg-yura_dark">
                                Año
                            </div>
                            <input type="number" class="form-control input-yura_default text-center" id="input_busqueda_anno">
                            <div class="input-group-addon bg-yura_dark">
                                Mes
                            </div>
                            <input type="number" class="form-control input-yura_default text-center" id="input_busqueda_mes">
                            <div class="input-group-btn">
                                <button class="btn btn-yura_dark" onclick="buscar_listado()">
                                    <i class="fa fa-fw fa-search"></i> Buscar
                                </button>
                                <button class="btn btn-yura_primary" onclick="add_codigo_dae()">
                                    <i class="fa fa-file-excel-o"></i> Exportar paises
                                </button>
                                <button class="btn btn-yura_default" onclick="subir_codigo_dae()">
                                    <i class="fa fa-upload"></i> Subir códigos DAE
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            <div id="div_listado_codigo_dae"></div>
        </div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.configuracion_facturacion.codigo_dae.script')
@endsection
