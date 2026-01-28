@if (count($inventarios) > 0)
    <div style="overflow-x: scroll; overflow-y: scroll; max-height: 700px;">
        <table class="table-bordered table-striped" width="100%" style="border: 2px solid #9d9d9d;"
            id="table_cuarto_frio">
            <thead>
                <tr id="tr_fija_top_0">
                    <th class="text-center th_yura_green" rowspan="2">
                        Variedad
                    </th>
                    <th class="text-center th_yura_green" rowspan="2">
                        Color
                    </th>
                    <th class="text-center th_yura_green" rowspan="2">
                        Presentación
                    </th>
                    <th class="text-center th_yura_green" rowspan="2">
                        Tallos x ramo
                    </th>
                    <th class="text-center th_yura_green" rowspan="2">
                        Longitud
                    </th>
                    <th class="text-center th_yura_green" colspan="5">
                        Días
                    </th>
                    <th class="text-center th_yura_green" rowspan="2">
                        Total Ramos
                    </th>
                    <th class="text-center th_yura_green" rowspan="2">
                        Total Tallos
                    </th>
                    <th class="text-center th_yura_green" rowspan="2">
                        Opciones
                    </th>
                </tr>
                <tr id="tr_fija_top_1">
                    @php
                        $totales_dia = [];
                    @endphp
                    @for ($i = 0; $i <= 4; $i++)
                        @php
                            $fecha = opDiasFecha('-', $i, date('Y-m-d'));
                            $fecha = getDias(TP_COMPLETO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime(substr($fecha, 0, 10))))] . ' ' . convertDateToText(substr($fecha, 0, 10));
                            $totales_dia[] = 0;
                        @endphp
                        <th class="text-center" style="border-color: #0a0a0a; background-color: #e9ecef" width="40px"
                            title="{{ $fecha }}">
                            <span style="padding: 2px">
                                {{ $i == 4 ? $i . '...' : $i }}
                            </span>
                        </th>
                    @endfor
                </tr>
            </thead>

            <tbody>
                @php
                    $total_ramos = 0;
                    $total_tallos = 0;
                @endphp
                @foreach ($inventarios as $pos_inv => $inv)
                    <tr onmouseover="$(this).addClass('bg-aqua')" onmouseleave="$(this).removeClass('bg-aqua')">
                        <th class="text-center" style="border-color: #9d9d9d">
                            <input type="hidden" id="variedad_{{ $pos_inv }}"
                                value="{{ $inv['variedad']->id_variedad }}">
                            <input type="hidden" id="presentacion_{{ $pos_inv }}"
                                value="{{ $inv['presentacion']->id_empaque }}">
                            <input type="hidden" id="tallos_x_ramo_{{ $pos_inv }}"
                                value="{{ $inv['tallos_x_ramo'] }}">
                            <input type="hidden" id="longitud_ramo_{{ $pos_inv }}"
                                value="{{ $inv['longitud_ramo'] }}">
                            <input type="hidden" id="unidad_medida_{{ $pos_inv }}"
                                value="{{ $inv['unidad_medida']->id_unidad_medida }}">

                            {{ $inv['variedad']->planta->nombre }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $inv['variedad']->nombre }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ explode('|', $inv['presentacion']->nombre)[0] }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $inv['tallos_x_ramo'] }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            @if (intval($inv['longitud_ramo']) != '' || intval($inv['longitud_ramo']) >= 0)
                                {{ intval($inv['longitud_ramo']) . '' . $inv['unidad_medida']->siglas }}
                            @endif
                        </th>
                        @foreach ($inv['dias'] as $pos_dia => $dia)
                            @php
                                $fecha = opDiasFecha('-', $pos_dia, date('Y-m-d'));
                                $fecha = getDias(TP_COMPLETO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime(substr($fecha, 0, 10))))] . ' ' . convertDateToText(substr($fecha, 0, 10));
                                $totales_dia[$pos_dia] += $dia['cantidad'];
                            @endphp
                            <td class="text-center"
                                style="border-color: #0a0a0a; background-color: #e9ecef; color: #0a0a0a;"
                                title="{{ $fecha }}">
                                <div class="btn-group span_editar_{{ $pos_dia }}"
                                    id="span_editar_{{ $pos_inv }}_{{ $pos_dia }}">
                                    <span type="button" id="btn_editar_{{ $pos_inv }}_{{ $pos_dia }}"
                                        class="dropdown-toggle mouse-hand span_editar_{{ $pos_dia }}"
                                        style="padding-bottom: 0; padding-top: 0; padding-left: 20px; padding-right: 20px"
                                        data-toggle="dropdown" aria-expanded="false">
                                        {{ $dia['cantidad'] != '' ? number_format($dia['cantidad']) : '-' }}
                                    </span>
                                    <ul class="dropdown-menu">
                                        @if ($tipo == 'R')
                                            @if ($dia['cantidad'] != '')
                                                <li>
                                                    <a href="javascript:void(0)"
                                                        onclick="editar_dia('{{ $pos_inv }}', '{{ $pos_dia }}')"
                                                        title="Editar">
                                                        <i class="fa fa-fw fa-edit"></i> Editar
                                                    </a>
                                                </li>
                                            @endif
                                            <li>
                                                <a href="javascript:void(0)"
                                                    onclick="add_dia('{{ $pos_inv }}', '{{ $pos_dia }}')"
                                                    title="Ingresar">
                                                    <i class="fa fa-fw fa-plus-circle"></i> Ingresar
                                                </a>
                                            </li>
                                        @else
                                            <li>
                                                <a href="javascript:void(0)">
                                                    Debe usar el reporte en RAMOS
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                                <input type="number" onkeypress="return isNumber(event)"
                                    id="input_editar_{{ $pos_inv }}_{{ $pos_dia }}"
                                    max="{{ $dia['cantidad'] }}" min="0" style="width: 100%; display: none"
                                    maxlength="5" class="text-center">
                                <input type="number" onkeypress="return isNumber(event)"
                                    id="input_add_{{ $pos_inv }}_{{ $pos_dia }}"
                                    name="add_{{ $pos_inv }}" min="0" value="0"
                                    style="width: 100%; display: none" maxlength="5"
                                    class="text-center input_add_{{ $pos_dia }}">
                                <input type="text" id="input_accion_{{ $pos_inv }}_{{ $pos_dia }}"
                                    style="display: none">
                            </td>
                        @endforeach
                        <th class="text-center" style="border-color: #9d9d9d">
                            @if ($tipo == 'R')
                                {{ number_format($inv['disponibles']) }}
                            @else
                                {{ number_format($inv['disponibles'] / $inv['tallos_x_ramo']) }}
                            @endif
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            @if ($tipo == 'R')
                                {{ number_format($inv['disponibles'] * $inv['tallos_x_ramo']) }}
                            @else
                                {{ number_format($inv['disponibles']) }}
                            @endif
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            <div class="btn-group">
                                <button type="button" class="btn btn-xs btn-yura_primary" title="Aceptar"
                                    id="btn_save_{{ $pos_inv }}" style="display: none"
                                    onclick="editar_inventario('{{ $pos_inv }}')">
                                    <i class="fa fa-fw fa-save"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-yura_danger" title="Botar"
                                    id="btn_botar_{{ $pos_inv }}" style="display: none"
                                    onclick="botar_inventario('{{ $pos_inv }}')">
                                    <i class="fa fa-fw fa-trash"></i>
                                </button>
                            </div>
                        </th>
                    </tr>
                    @php
                        $total_ramos += $tipo == 'R' ? $inv['disponibles'] : $inv['disponibles'] / $inv['tallos_x_ramo'];
                        $total_tallos += $tipo == 'R' ? $inv['disponibles'] * $inv['tallos_x_ramo'] : $inv['disponibles'];
                    @endphp
                @endforeach
            </tbody>
            <tr id="tr_fija_bottom_0">
                <th class="text-center th_yura_green" colspan="5">
                    Opciones por día
                </th>
                @for ($i = 0; $i <= 4; $i++)
                    @php
                        $fecha = opDiasFecha('-', $i, date('Y-m-d'));
                        $fecha = getDias(TP_COMPLETO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime(substr($fecha, 0, 10))))] . ' ' . convertDateToText(substr($fecha, 0, 10));
                    @endphp
                    <th class="text-center" style="border-color: #0a0a0a; background-color: #e9ecef" width="40px"
                        title="{{ $fecha }}">
                        {{ number_format($totales_dia[$i]) }}
                        <br>
                        @if (in_array(Session::get('id_usuario'), [9, 1, 2]))
                            <div class="dropup">
                                <button type="button" class="dropdown-toggle btn btn-xs"
                                    style="padding-bottom: 0; padding-top: 0; padding-left: 20px; padding-right: 20px"
                                    data-toggle="dropdown" aria-expanded="false">
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" style="width: 30px">
                                    <li id="btn_save_dia_{{ $i }}" style="display: none;">
                                        <a href="javascript:void(0)" onclick="save_dia('{{ $i }}')">
                                            <i class="fa fa-fw fa-save"></i> Guardar
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" onclick="delete_dia('{{ $i }}')">
                                            <i class="fa fa-fw fa-trash"></i> Botar todo
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <input type="hidden" id="inventario_target_{{ $i }}">
                        @endif
                    </th>
                @endfor
                <th class="text-center th_yura_green">
                    {{ $total_ramos }}
                </th>
                <th class="text-center th_yura_green">
                    {{ $total_tallos }}
                </th>
                <th class="th_yura_green"></th>
            </tr>
        </table>
    </div>
@else
    <div class="alert alert-info text-center">
        El cuarto frío se encuentra vacío en estos momentos
    </div>
@endif

<style>
    #tr_fija_bottom_0 th {
        position: sticky;
        bottom: 0;
        z-index: 9;
    }

    #tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 9;
    }

    #tr_fija_top_1 th {
        position: sticky;
        top: 21px;
        z-index: 9;
    }
</style>
