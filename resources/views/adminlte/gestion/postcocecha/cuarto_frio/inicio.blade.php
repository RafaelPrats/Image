@extends('layouts.adminlte.master')

@section('titulo')
    Cuarto Frío
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Cuarto Frío <b class="text-color_yura_danger">OLD</b>
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
                <td class="text-center" style="border-color: #9d9d9d">
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
                <td class="text-center" style="border-color: #9d9d9d; width: 20%" id="td_cargar_variedades">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            <i class="fa fa-fw fa-leaf"></i>
                        </span>
                        <select name="filtro_variedad" id="filtro_variedad" class="form-control" style="width: 100%"
                            required>
                            <option value="T">Todos los colores</option>
                        </select>
                    </div>
                </td>
                <td class="text-center" style="border-color: #9d9d9d; width: 90px">
                    <div class="input-group">
                        <input type="number" name="filtro_longitud" id="filtro_longitud" class="form-control"
                            style="width: 100%" required placeholder="Medida">
                    </div>
                </td>
                <td class="text-center" style="border-color: #9d9d9d; width: 20%" id="td_cargar_variedades">
                    <div class="input-group">
                        <select name="filtro_presentacion" id="filtro_presentacion" class="form-control" style="width: 100%"
                            required>
                            <option value="T">Todas las presentaciones</option>
                            @foreach ($presentaciones as $p)
                                <option value="{{ $p->id_empaque_p }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="input-group">
                        <select name="filtro_tipo" id="filtro_tipo" class="form-control" style="width: 100%" required>
                            <option value="R">Ramos</option>
                            <option value="T">Tallos</option>
                            <option value="AR">Acumulado por Color (RAMOS)</option>
                            <option value="AT">Acumulado por Color (TALLOS)</option>
                        </select>
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-yura_dark" onclick="listar_inventarios()"
                                title="Buscar Inventarios">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                            <button type="button" class="btn btn-yura_primary" onclick="add_new_inventarios()"
                                title="Agregar Inventarios">
                                <i class="fa fa-fw fa-plus"></i>
                            </button>
                            <button type="button" class="btn btn-yura_default" title="Exportar"
                                onclick="exportar_inventarios()">
                                <i class="fa fa-fw fa-file-excel-o"></i>
                            </button>
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        <div id="div_content_cuarto_frio" style="margin-top: 5px">
        </div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.postcocecha.cuarto_frio.script')
@endsection
