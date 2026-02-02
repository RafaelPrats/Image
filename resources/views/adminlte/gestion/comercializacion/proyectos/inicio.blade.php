@extends('layouts.adminlte.master')

@section('titulo')
    Pedidos
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Pedidos <b class="text-color_yura">NEW</b>
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
        <table style="width: 100%">
            <tr>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            Tipo
                        </span>
                        <select name="filtro_tipo" id="filtro_tipo" class="form-control">
                            <option value="">Todos</option>
                            <option value="SO">STANDING ORDER</option>
                            <option value="OM">OPEN MARKET</option>
                        </select>
                    </div>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Planta
                        </span>
                        <select name="filtro_planta" id="filtro_planta" class="form-control" style="width: 100%"
                            onchange="select_planta($(this).val(), 'filtro_variedad', 'td_cargar_variedades', '<option value=>Todos</option>', '')">
                            <option value="">Todas</option>
                            @foreach ($plantas as $p)
                                <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td class="text-center" style="border-color: #9d9d9d" id="td_cargar_variedades">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Variedad
                        </span>
                        <select name="filtro_variedad" id="filtro_variedad" class="form-control" style="width: 100%">
                            <option value="">Todos</option>
                        </select>
                    </div>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Marcacion
                        </span>
                        <select name="filtro_marcacion" id="filtro_marcacion" class="form-control" style="width: 100%">
                            <option value="">Todas</option>
                            @foreach ($valores_marcaciones as $m)
                                <option value="{{ $m->valor }}">{{ $m->valor }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            Cliente
                        </span>
                        <select name="filtro_cliente" id="filtro_cliente" class="form-control">
                            <option value="">Seleccione</option>
                            @foreach ($clientes as $c)
                                <option value="{{ $c->id_cliente }}">{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Desde
                        </span>
                        <input type="date" name="filtro_desde" id="filtro_desde" class="form-control" style="width: 100%"
                            value="{{ hoy() }}">
                    </div>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Hasta
                        </span>
                        <input type="date" name="filtro_hasta" id="filtro_hasta" class="form-control" style="width: 100%"
                            value="{{ hoy() }}">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-yura_dark" onclick="listar_reporte()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                            <button type="button" class="btn btn-yura_primary" onclick="add_proyecto()">
                                <i class="fa fa-fw fa-plus"></i>
                            </button>

                            <button type="button" class="btn btn-yura_default dropdown-toggle" data-toggle="dropdown"
                                aria-expanded="true">
                                <i class="fa fa-fw fa-cogs"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right sombra_pequeña" style="background-color: #c8c8c8">
                                <li>
                                    <a class="" href="javascript:void(0)" style="color: black"
                                        onclick="generar_packings()">
                                        <i class="fa fa-fw fa-file-o"></i>
                                        Generar packings
                                    </a>
                                </li>
                                <li>
                                    <a class="" href="javascript:void(0)" style="color: black"
                                        onclick="combinar_pedidos()">
                                        <i class="fa fa-fw fa-clone"></i>
                                        Combinar Pedidos
                                    </a>
                                </li>
                                <li>
                                    <a class="" href="javascript:void(0)" style="color: black"
                                        onclick="descargar_packings_all()">
                                        <i class="fa fa-fw fa-file-pdf-o"></i>
                                        Descargar packings
                                    </a>
                                </li>
                                <li>
                                    <a class="" href="javascript:void(0)" style="color: black"
                                        onclick="descargar_flor_postco()">
                                        <i class="fa fa-fw fa-file-excel-o"></i>
                                        Flor Postco
                                    </a>
                                </li>
                                <li>
                                    <a class="" href="javascript:void(0)" style="color: black"
                                        onclick="descargar_jire()">
                                        <i class="fa fa-fw fa-download"></i>
                                        Importar al JIRE
                                    </a>
                                </li>
                            </ul>
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        <div id="div_listado" style="margin-top: 5px;">
        </div>
    </section>

    <style>
        .tr_fija_bottom_0 {
            position: sticky;
            bottom: 0;
            z-index: 9;
        }

        .columna_fija_left_0 {
            position: sticky;
            left: 0;
            z-index: 9;
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.comercializacion.proyectos.script')
@endsection
