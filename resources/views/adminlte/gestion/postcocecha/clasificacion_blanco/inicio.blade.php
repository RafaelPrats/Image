@extends('layouts.adminlte.master')

@section('titulo')
    Clasificación
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Clasificación <b class="text-color_yura_danger">OLD</b>
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
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-fw fa-calendar"></i>
                        </span>
                        <input type="date" id="fecha_blanco" name="fecha_blanco" required
                            class="form-control text-center" onchange="listar_clasificacion_blanco()" style="width: 100%"
                            value="{{ isset($blanco) ? $blanco->fecha_ingreso : hoy() }}">
                    </div>
                </td>
                <td class="text-center">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Variedad
                        </span>
                        <select name="filtro_planta" id="filtro_planta" class="form-control" style="width: 100%"
                            onchange="select_planta($(this).val(), 'filtro_variedad', 'td_cargar_variedades', '<option value=>Todos</option>', '')">
                            <option value="">Seleccione</option>
                            @foreach ($plantas as $p)
                                <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td class="text-center" style="width: 20%" id="td_cargar_variedades">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            <i class="fa fa-fw fa-leaf"></i>
                        </span>
                        <select name="filtro_variedad" id="filtro_variedad" class="form-control" style="width: 100%"
                            required>
                            <option value="">Todos los colores</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            <i class="fa fa-fw fa-calendar"></i> Dias
                        </span>
                        <select name="filtro_dias" id="filtro_dias" class="form-control" style="width: 100%">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-yura_dark" onclick="listar_clasificacion_blanco()"
                                title="Buscar Clasificación Blanco">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                            <button class="btn btn-yura_default" onclick="rendimiento_mesas()">
                                <i class="fa fa-fw fa-cubes"></i> Mesas
                            </button>
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        <div id="div_content_blanco" style="margin-top: 5px">
            <div id="div_listado_blanco">
            </div>
        </div>
    </section>
@endsection

@section('script_final')
    {{-- JS de Chart.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.min.js"></script>

    @include('adminlte.gestion.postcocecha.clasificacion_blanco.script')
@endsection
