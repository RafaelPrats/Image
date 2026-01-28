@extends('layouts.adminlte.master')

@section('titulo')
    Especificaciones
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->

    <section class="content-header">
        <h1>
            Especificaciones <b class="text-color_yura_danger">OLD</b>
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



    <!-- Main content -->
    <section class="content">
        <div id="div_content_especificaciones">
            <table width="100%">
                <tr>
                    <td style="width:220px">
                        <div class="input-group">
                            <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                                Cliente
                            </div>
                            <select id="cliente_id" name="cliente_id" class="form-control input-yura_default"
                                style="width: 100%">
                                <option value="">Seleccione</option>
                                @foreach ($clientes as $cliente)
                                    <option value="{{ $cliente->id_cliente }}">{{ $cliente->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </td>
                    <td style="width:220px">
                        <div class="input-group">
                            <div class="input-group-addon bg-yura_dark">
                                Variedad
                            </div>
                            <select id="planta_id" name="planta_id" class="form-control input-yura_default"
                                style="width: 100%" onchange="getVariedadesByPlanta()">
                                <option value="">TODOS</option>
                                @foreach ($plantas as $p)
                                    <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </td>
                    <td style="width:220px">
                        <div class="input-group">
                            <div class="input-group-addon bg-yura_dark">
                                Color
                            </div>
                            <select id="variedad_id" name="variedad_id" class="form-control input-yura_default"
                                style="width: 100%">
                                <option value="">TODOS</option>
                            </select>
                        </div>
                    </td>
                    <td style="width:220px">
                        <div class="input-group">
                            <div class="input-group-addon bg-yura_dark">
                                ESTADO
                            </div>
                            <select id="estado" name="estado" class="form-control input-yura_default"
                                style="width: 100%">
                                <option value="1">ACTIVO</option>
                                <option value="0">INACTIVO</option>
                            </select>
                        </div>
                    </td>
                    <td style="width:220px">
                        <div class="input-group">
                            <div class="input-group-addon bg-yura_dark">
                                TIPO
                            </div>
                            <select id="tipo" name="tipo" class="form-control input-yura_default"
                                style="width: 100%">
                                <option value="">TODOS</option>
                                <option value="NINTANGA">NINTANGA</option>
                                <option value="ESPECIALES">ESPECIALES</option>
                            </select>
                            <div class="input-group-btn">
                                <button class="btn btn-yura_dark" onclick="buscar_listado_especificaciones()">
                                    <i class="fa fa-fw fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="width:220px">
                        <div class="input-group">
                            <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                                Longitud <sup>cm</sup>
                            </div>
                            <input type="number" id="filtro_longitud" name="filtro_longitud"
                                class="form-control text-center" style="width: 100%">
                        </div>
                    </td>
                    <td style="width:220px">
                        <div class="input-group">
                            <div class="input-group-addon bg-yura_dark">
                                Tipo Caja
                            </div>
                            <select id="filtro_tipo_caja" name="filtro_tipo_caja" class="form-control input-yura_default"
                                style="width: 100%">
                                <option value="">TODAS</option>
                                @foreach ($tipos_caja as $item)
                                    <option value="{{ $item->siglas }}">{{ $item->siglas }}</option>
                                @endforeach
                            </select>
                        </div>
                    </td>
                    <td style="padding-left: 5px;width:120px;text-align:right" colspan="3">
                        <div class="btn-group" style="margin-top:8px; margin-right:8px">
                            <button class="btn btn-yura_primary" onclick="actualizar_especificacion_masivamente()"
                                title="Actualizar masivamente especificaciones" id="accion_masiva">
                                <i class="fa fa-fw fa-floppy-o" id="icon_accion_masiva"></i> Actualizar masivo
                            </button>
                            <button class="btn btn-yura_danger" onclick="elminar_especificacion_masivamente()"
                                title="Eliminar masivamente especificaciones" id="accion_masiva">
                                <i class="fa fa-fw fa-trash" id="icon_accion_masiva"></i> Eliminar masivo
                            </button>
                            <button class="btn btn-yura_default" onclick="exportar_especificaciones()"
                                title="Exportar Excel">
                                <i class="fa fa-fw fa-file-excel-o"></i> Descargar
                            </button>
                        </div>
                    </td>
                </tr>
            </table>
            <div id="div_listado_especificaciones" style="margin-top: 10px;"></div>
        </div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.postcocecha.especificacion.script')
@endsection
