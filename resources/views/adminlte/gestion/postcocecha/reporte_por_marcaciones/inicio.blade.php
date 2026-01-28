@extends('layouts.adminlte.master')

@section('titulo')
    Reporte por Marcaciones
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Reporte por Marcaciones
            <small class="text-color_yura">módulo de postcosecha</small>
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
        <table style="width: 100%">
            <tr>
                <td class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-fw fa-leaf"></i> Variedad
                        </span>
                        <select name="filtro_planta" id="filtro_planta" class="input-yura_default form-control"
                            style="width: 100%" onchange="listar_filtros()">
                            <option value="">Seleccione</option>
                            @foreach ($plantas as $p)
                                <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-yura_dark" onclick="listar_filtros()"
                                title="Buscar Filtros">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        <div id="div_content_blanco" style="margin-top: 5px">
            <div id="div_listado_filtros">
            </div>
        </div>
    </section>
@endsection

@section('script_final')
    {{-- JS de Chart.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.min.js"></script>

    @include('adminlte.gestion.postcocecha.reporte_por_marcaciones.script')
@endsection
