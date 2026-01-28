@extends('layouts.adminlte.master')

@section('titulo')
    {{explode('|',getConfiguracionEmpresa()->postcocecha)[1]}}  {{--Recepción--}}
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{explode('|',getConfiguracionEmpresa()->postcocecha)[1]}}
            <small class="text-color_yura">módulo de postcosecha</small>

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

    <!-- Main content -->
    <section class="content">
        <div class="input-group" style="margin-bottom: 0; margin-right: 10px">
            <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                <i class="fa fa-fw fa-calendar"></i> Fecha de trabajo
            </div>
            <input type="date" id="fecha_verde_search" name="fecha_verde_search" class="form-control input-yura_default"
                   onchange="buscar_listado()">
            <div class="input-group-btn">
                {{--<button class="btn btn-yura_dark" onclick="add_verde('')" onmouseover="$('#title_btn_add').html('Añadir')"
                        onmouseleave="$('#title_btn_add').html('')" style="color: white">
                    <i class="fa fa-fw fa-plus" style="color: #e9ecef"></i> <em id="title_btn_add"></em>
                </button>--}}
                @if (es_local())
                    <button class="btn btn-yura_dark" onclick="add_new_verde('')" onmouseover="$('#title_btn_add').html('Añadir')"
                            onmouseleave="$('#title_btn_add').html('')" style="color: white">
                        <i class="fa fa-fw fa-plus" style="color: #e9ecef"></i> <em id="title_btn_add"></em>
                    </button>
                @endif
                {{--<button class="btn btn-yura_default" onclick="rendimiento_mesas()" onmouseover="$('#title_btn_mesas').html('Mesas')"
                        onmouseleave="$('#title_btn_mesas').html('')">
                    <i class="fa fa-fw fa-cubes"></i> <em id="title_btn_mesas"></em>
                </button>--}}
                @if (es_local())
                    <button class="btn btn-yura_default" onclick="monitoreo_calibres()" onmouseover="$('#title_btn_calibres').html('Calibres')"
                            onmouseleave="$('#title_btn_calibres').html('')">
                        <i class="fa fa-fw fa-tree"></i> <em id="title_btn_calibres"></em>
                    </button>
                @endif
                <button class="btn btn-yura_primary" onclick="exportar_clasificaciones()" style="color: white"
                        onmouseover="$('#title_btn_exportar').html('Exportar')" onmouseleave="$('#title_btn_exportar').html('')">
                    <i class="fa fa-fw fa-file-excel-o" style="color: #e9ecef"></i> <em id="title_btn_exportar"></em>
                </button>
            </div>
        </div>
        <input type="checkbox" id="check_filtro_verde" style="display: none">
        <div id="div_content_clasificaciones" style="margin-top: 5px">
            <div class="row hide">
                <div class="col-md-12">
                    <label for="check_mandar_apertura_auto" class="pull-right" style="margin-left: 5px">Mandar automáticamente a
                        aperturas</label>
                    <input type="checkbox" id="check_mandar_apertura_auto" class="pull-right" checked>
                </div>
            </div>
            <div id="div_listado_clasificaciones"></div>
        </div>
    </section>
@endsection

@section('script_final')
    {{-- JS de Chart.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.min.js"></script>

    @include('adminlte.gestion.postcocecha.clasificacion_verde.script')
@endsection
