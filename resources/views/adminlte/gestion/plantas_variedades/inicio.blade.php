@extends('layouts.adminlte.master')

@section('titulo')
    Plantas y variedades
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Plantas y variedades
            <small>módulo de administrador</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" onclick="cargar_url('')"><i class="fa fa-home"></i> Inicio</a></li>
            <li>
                {{ $submenu->menu->grupo_menu->nombre }}
            </li>
            <li>
                {{ $submenu->menu->nombre }}
            </li>

            <li class="active">
                <a href="javascript:void(0)" onclick="cargar_url('{{ $submenu->url }}')">
                    <i class="fa fa-fw fa-refresh"></i> {{ $submenu->nombre }}
                </a>
            </li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div id="div_content_menu_sistema">
            <div class="row">
                <div class="col-md-4" id="div_content_grupos_menu">
                    @include('adminlte.gestion.plantas_variedades.partials.listado_plantas')
                </div>
                <div class="col-md-8" id="div_content_menus">
                    <table width="100%" class="table-responsive table-bordered"
                        style="font-size: 0.8em; border-color: #9d9d9d" id="table_content_variedades">
                        <thead>
                            <tr>
                                <th class="text-center th_yura_green">TIPO</th>
                                @if (es_server())
                                    <th class="text-center th_yura_green">
                                        @if (count($plantas) > 0)
                                            <button type="button" class="btn btn-xs btn-yura_default" title="Añadir variedad"
                                                onclick="add_variedad()">
                                                <i class="fa fa-fw fa-plus"></i>
                                            </button>
                                        @endif
                                    </th>
                                @endif
                            </tr>
                        </thead>
                        <tr>
                            <td class="text-center" style="border-color: #9d9d9d" colspan="2">
                                No se ha seleccionado ninguna planta
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.plantas_variedades.script')
@endsection
