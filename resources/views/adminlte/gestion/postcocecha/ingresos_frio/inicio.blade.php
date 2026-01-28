@extends('layouts.adminlte.master')

@section('titulo')
    Ingresos a Cuarto Frío
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Ingresos a Cuarto Frío
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
                <a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('{{ $submenu->url }}')">
                    <i class="fa fa-fw fa-refresh"></i> {{ $submenu->nombre }}
                </a>
            </li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <table style="width: 100%">
            <tr>
                <td class="text-center" style="border-color: #9d9d9d; width: 25%">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            Variedad
                        </span>
                        <select name="filtro_planta" id="filtro_planta" class="form-control" style="width: 100%"
                            onchange="select_planta($(this).val(), 'filtro_variedad', 'td_cargar_variedades', '<option value=T>Todos</option>', '')">
                            <option value="T">Todas</option>
                            @foreach ($plantas as $p)
                                <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td class="text-center" style="border-color: #9d9d9d;" id="td_cargar_variedades">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Color
                        </span>
                        <select name="filtro_variedad" id="filtro_variedad" class="form-control" style="width: 100%"
                            required>
                            <option value="T">Todos</option>
                        </select>
                    </div>
                </td>
                <td class="text-center" style="border-color: #9d9d9d; width: 35%">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Presentación
                        </span>
                        <select name="filtro_presentacion" id="filtro_presentacion" class="form-control" style="width: 100%"
                            required>
                            <option value="T">Todas</option>
                            @foreach ($presentaciones as $p)
                                <option value="{{ $p->id_empaque }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="text-center" style="border-color: #9d9d9d;">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-fw fa-calendar"></i> Desde
                        </span>
                        <input type="date" name="filtro_desde" id="filtro_desde" class="form-control" style="width: 100%"
                            required min="{{ $rango_fechas->desde }}" max="{{ $rango_fechas->hasta }}"
                            value="{{ hoy() }}">
                    </div>
                </td>
                <td class="text-center" style="border-color: #9d9d9d;">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            <i class="fa fa-fw fa-calendar"></i> Hasta
                        </span>
                        <input type="date" name="filtro_hasta" id="filtro_hasta" class="form-control" style="width: 100%"
                            required min="{{ $rango_fechas->desde }}" max="{{ $rango_fechas->hasta }}"
                            value="{{ hoy() }}">
                    </div>
                </td>
                <td class="text-center" style="border-color: #9d9d9d; width: 15%">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Medida
                        </span>
                        <select name="filtro_longitud" id="filtro_longitud" class="form-control" style="width: 100%"
                            required>
                            <option value="T">Todas</option>
                            @foreach ($longitudes as $p)
                                <option value="{{ $p }}">{{ $p }}cm</option>
                            @endforeach
                        </select>
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-yura_dark" onclick="listar_reporte()"
                                title="Buscar Inventarios">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                            {{-- <button type="button" class="btn btn-yura_default" title="Exportar"
                                onclick="exportar_inventarios()">
                                <i class="fa fa-fw fa-file-excel-o"></i>
                            </button> --}}
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        <div id="div_listado" style="margin-top: 5px">
        </div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.postcocecha.ingresos_frio.script')
@endsection
