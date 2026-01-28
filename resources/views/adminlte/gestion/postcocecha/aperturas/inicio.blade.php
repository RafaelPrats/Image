@extends('layouts.adminlte.master')

@section('titulo')
    {{explode('|',getConfiguracionEmpresa()->postcocecha)[2]}}  {{--Apertura--}}
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{explode('|',getConfiguracionEmpresa()->postcocecha)[2]}}
            <small class="text-color_yura">módulo de postcosecha</small>

        </h1>

        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" onclick="cargar_url('')" class="text-color_yura"><i class="fa fa-home"></i> Inicio</a></li>
            <li class="text-color_yura">
                {{$submenu->menu->grupo_menu->nombre}}
            </li>
            <li class="text-color_yura">
                {{$submenu->menu->nombre}}
            </li>

            <li class="active">
                <a href="javascript:void(0)" onclick="cargar_url('{{$submenu->url}}')" class="text-color_yura">
                    <i class="fa fa-fw fa-refresh"></i> {{$submenu->nombre}}
                </a>
            </li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <table style="width: 100%; margin-bottom: 5px">
            <tr>
                <td>
                    <div class="pull-right">
                        <label for="variedad_search" style="margin-left: 10px">Variedad</label>
                        <select name="variedad_search" id="variedad_search" onchange="buscar_listado()" class="input-yura_default">
                            <option value="">Variedad</option>
                            @foreach($variedades as $item)
                                <option value="{{$item->id_variedad}}">
                                    {{$item->planta->nombre}} - {{$item->nombre}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="pull-right" id="div_form_group_coches" style="display: none;">
                        <label for="check_coches">Tallos por coches</label>

                        <select name="tallos_x_coche" id="tallos_x_coche" onchange="calcular_tallos_x_coche()" class="input-yura_default">
                            <option value="">Cantidad de tallos</option>
                            @foreach(getUnitarias() as $unitaria)
                                <option value="{{$unitaria->tallos_x_ramo * $unitaria->ramos_x_balde * getConfiguracionEmpresa()->baldes_x_coche}}"
                                        style="background-color: {{explode('|',$unitaria->color)[0]}}; ">
                                    {{$unitaria->tallos_x_ramo * $unitaria->ramos_x_balde * getConfiguracionEmpresa()->baldes_x_coche}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="pull-right" style="margin-right: 10px;">
                        <label for="clasificacion_ramo_search" style="margin-right: 5px">Calibre del ramo</label>
                        <select name="clasificacion_ramo_search" id="clasificacion_ramo_search" onchange="calcularConvercion($(this).val())"
                                class="input-yura_default">
                            @foreach(getCalibresRamo() as $calibre)
                                @if($calibre->unidad_medida->tipo == 'P')
                                    <option value="{{$calibre->nombre}}" {{$calibre->nombre == getCalibreRamoEstandar()->nombre ? 'selected' : ''}}>
                                        {{$calibre->nombre}}{{$calibre->unidad_medida->siglas}}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="pull-right" style="margin-right: 10px;">
                        <label for="check_dont_verify" style="margin-right: 5px" class="mouse-hand">Sacar siempre</label>
                        <input type="checkbox" class="pull-right mouse-hand" id="check_dont_verify">
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="btn-group pull-right">
                        <button type="button" class="btn btn-xs btn-yura_primary" id="btn_sacar" title="Sacar de apertura"
                                style="display: none" onclick="sacar_aperturas()">
                            <i class="fa fa-fw fa-share-square-o"></i> Sacar
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_dark" id="html_current_sacar" title="Ramos seleccionados" disabled
                                style="display: none">
                        </button>
                    </div>
                </td>
            </tr>
        </table>

        <div id="div_content_aperturas">
            <div id="div_listado_aperturas"></div>
        </div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.postcocecha.aperturas.script')
@endsection
