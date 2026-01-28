@extends('layouts.adminlte.master')

@section('titulo')
    Configuración de Costos
@endsection

@section('script_inicio')
    <script>
    </script>
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Configuración
            <small class="text-color_yura">de Costos</small>
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
                    <i class="fa fa-fw fa-refresh"></i> {!! $submenu->nombre !!}
                </a>
            </li>
        </ol>
    </section>

    <section class="content">
        <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_costo_horas">
            <tr>
                <th class="text-center th_yura_green">
                    Tipo de Hora
                </th>
                <th class="text-center th_yura_green">
                    Sueldo Promedio
                </th>
                <th class="text-center th_yura_green">
                    Valor Hora
                </th>
                <th class="text-center th_yura_green">
                    Prov. DT
                </th>
                <th class="text-center th_yura_green">
                    Prov. DC
                </th>
                <th class="text-center th_yura_green">
                    Prov. Reserva
                </th>
                <th class="text-center th_yura_green">
                    A. Patronal
                </th>
                <th class="text-center th_yura_green">
                    Total Prov.
                </th>
                <th class="text-center th_yura_green">
                    Valor Hora con Prov.
                </th>
                @if (es_local())
                    <th class="text-center th_yura_green">
                        @if(es_super_administrador())
                            <div style="width: 80px">
                                <button type="button" class="btn btn-xs btn-yura_default" onclick="add_costo_horas()" id="btn_add_costo_horas">
                                    <i class="fa fa-fw fa-plus"></i>
                                </button>
                            </div>
                        @endif
                    </th>
                @endif
            </tr>
            @foreach($costo_horas as $c)
                <tr>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="text" class="text-center" style="width: 100%; text-transform: uppercase; background-color: #e9ecef"
                               id="nombre_{{$c->id_costo_horas}}" value="{{$c->nombre}}" {{!es_super_administrador() ? 'readonly' : ''}}>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" class="text-center" style="width: 100%" id="sueldo_promedio_{{$c->id_costo_horas}}"
                               value="{{$c->sueldo_promedio}}" onchange="calcular_costo_horas('{{$c->id_costo_horas}}')"
                               onkeyup="calcular_costo_horas('{{$c->id_costo_horas}}')">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" class="text-center" style="width: 100%; background-color: #e9ecef"
                               id="valor_hora_{{$c->id_costo_horas}}"
                               value="{{$c->valor_hora}}" readonly>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" class="text-center" style="width: 100%; background-color: #e9ecef"
                               id="prov_dt_{{$c->id_costo_horas}}"
                               value="{{$c->prov_dt}}" readonly>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" class="text-center" style="width: 100%; background-color: #e9ecef"
                               id="prov_dc_{{$c->id_costo_horas}}"
                               value="{{$c->prov_dc}}" readonly>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" class="text-center" style="width: 100%; background-color: #e9ecef"
                               id="prov_reserva_{{$c->id_costo_horas}}"
                               value="{{$c->prov_reserva}}" readonly>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" class="text-center" style="width: 100%; background-color: #e9ecef"
                               id="aporte_patronal_{{$c->id_costo_horas}}"
                               value="{{$c->aporte_patronal}}" readonly>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" class="text-center" style="width: 100%; background-color: #e9ecef"
                               id="total_provisiones_{{$c->id_costo_horas}}"
                               value="{{$c->total_provisiones}}" readonly>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" class="text-center" style="width: 100%; background-color: #e9ecef"
                               id="valor_hora_provisiones_{{$c->id_costo_horas}}"
                               value="{{$c->valor_hora_provisiones}}" readonly>
                    </td>
                    @if (es_local())
                        <td class="text-center" style="border-color: #9d9d9d">
                            <div class="btn-group">
                                <button type="button" class="btn btn-xs btn-yura_warning" onclick="update_costo_horas('{{$c->id_costo_horas}}')">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </button>
                                @if(es_super_administrador())
                                    <button type="button" class="btn btn-xs btn-yura_danger">
                                        <i class="fa fa-fw fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    @endif
                </tr>
            @endforeach
        </table>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.costos.costo_horas.script')
@endsection
