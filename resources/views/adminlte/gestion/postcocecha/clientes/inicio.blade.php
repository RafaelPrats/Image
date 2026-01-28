@extends('layouts.adminlte.master')

@section('titulo')
    Clientes
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    @include('adminlte.gestion.partials.breadcrumb')
    <!-- Main content -->
    <section class="content">
        <table width="100%">
            <tr>
                <td>
                    <div class="input-group" style="padding: 0px">
                        <input type="text" class="form-control" placeholder="Búsqueda" id="busqueda_clientes"
                            name="busqueda_clientes">
                        <span class="input-group-btn">
                            <button class="btn btn-default" onclick="buscar_listado()"
                                onmouseover="$('#title_btn_buscar').html('Buscar')"
                                onmouseleave="$('#title_btn_buscar').html('')">
                                <i class="fa fa-fw fa-search" style="color: #0c0c0c"></i> <em id="title_btn_buscar"></em>
                            </button>
                        </span>
                        @if (es_server())
                            <span class="input-group-btn">
                                <button class="btn btn-primary" onclick="add_cliente()"
                                    onmouseover="$('#title_btn_add').html('Añadir')"
                                    onmouseleave="$('#title_btn_add').html('')">
                                    <i class="fa fa-fw fa-plus" style="color: #0c0c0c"></i> <em id="title_btn_add"></em>
                                </button>
                            </span>
                        @endif
                        <span class="input-group-btn">
                            <button class="btn btn-success" onclick="exportar_clientes()"
                                onmouseover="$('#title_btn_exportar').html('Exportar')"
                                onmouseleave="$('#title_btn_exportar').html('')">
                                <i class="fa fa-fw fa-file-excel-o" style="color: #0c0c0c"></i> <em
                                    id="title_btn_exportar"></em>
                            </button>
                        </span>
                        {{-- @if (es_server())
                                    <span class="input-group-btn">
                                        <button class="btn btn-success"  onclick="form_importar_clientes()"
                                                onmouseover="$('#title_btn_importar_excel').html('Importar nuevos clientes')"
                                                onmouseleave="$('#title_btn_importar_excel').html('')">
                                            <i class="fa fa-upload" aria-hidden="true"></i>
                                            <em id="title_btn_importar_excel"></em>
                                        </button>
                                    </span>
                                @endif --}}
                    </div>
                </td>
            </tr>
        </table>
        <div id="div_listado_clientes" style="margin-top: 5px; overflow-y: scroll; max-height: 700px"></div>
    </section>

    <style>
        .tr_fija_top_0 {
            position: sticky;
            top: 0;
            z-index: 9;
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.postcocecha.clientes.script')
@endsection
