@if (es_server())
    <table width="100%" class="table-responsive table-bordered"
        style="border-color: #9d9d9d; border-radius: 18px 18px 0 0; margin-bottom: 5px">
        <thead>
            <tr style="background-color: #dd4b39; color: white">
                <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0">
                    VARIEDAD
                </th>
                <th class="text-center th_yura_green">
                    TIPO
                </th>
                <th class="text-center th_yura_green">
                    CALIBRE
                </th>
                <th class="text-center th_yura_green">
                    CAJA
                </th>
                <th class="text-center th_yura_green">
                    RAMO X CAJA
                </th>
                <th class="text-center th_yura_green">
                    PRESENTACIÓN
                </th>
                <th class="text-center th_yura_green">
                    TALLOS X RAMO
                </th>
                <th class="text-center th_yura_green" colspan="2">
                    LONGITUD
                </th>
                <th style="width: 165px; border-radius: 0 18px 0 0" class="text-center th_yura_green">
                    OPCIONES
                </th>
            </tr>
        </thead>
        <tbody id="div_nueva_especificacion">
            <tr id="tr_nueva_especificacion_1">
                <td style="border-color: #9d9d9d;width:120px">
                    <select id="id_planta_1" style="width: 100%" name="id_planta" class="form-control"
                        onchange="seleccionar_variedad('1')">
                        <option selected disabled>Seleccione</option>
                        @foreach ($plantas as $p)
                            <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                        @endforeach
                    </select>
                </td>
                <td style="border-color: #9d9d9d;width:120px">
                    <select id="id_variedad_1" style="width: 100%" name="id_variedad" class="form-control">
                        {{-- <option selected disabled>Seleccione</option> --}}
                    </select>
                </td>
                <td style="border-color: #9d9d9d">
                    <select id="id_clasificacion_ramo_1" style="width: 100%" name="id_clasificacion_ramo"
                        class="form-control">
                        {{-- <option selected disabled>Seleccione</option> --}}
                        @foreach ($clasificacion_ramo as $c)
                            <option value="{{ $c->id_clasificacion_ramo }}">{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </td>
                <td style="border-color: #9d9d9d">
                    <select id="id_empaque_1" style="width: 100%" name="id_empaque" class="form-control">
                        @foreach ($empaque as $e)
                            <option value="{{ $e->id_empaque }}">{{ mb_strtoupper(explode('|', $e->nombre)[0]) }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td style="border-color: #9d9d9d;width:80px">
                    <input type="text" class="form-control" placeholder="Cantidad" id="ramo_x_caja_1"
                        style="width: 100%" value="1" name="ramo_x_caja_1" required>
                </td>
                <td style="border-color: #9d9d9d">
                    <select id="id_presentacion_1" style="width: 100%" name="id_presentacion" class="form-control">
                        {{-- <option selected disabled>Seleccione</option> --}}
                        @foreach ($presentacion as $p)
                            <option value="{{ $p->id_empaque }}">{{ $p->nombre }}</option>
                        @endforeach
                    </select>
                </td>
                <td style="border-color: #9d9d9d;width:80px">
                    <input type="text" placeholder="Cantidad" id="tallos_x_ramo_1" style="width: 100%"
                        name="tallos_x_ramo_1" class="form-control">
                </td>
                <td style="border-color: #9d9d9d; width: 80px">
                    <input type="text" placeholder="Cantidad" id="longitud_1" style="width: 100%" name="longitud_1"
                        class="form-control">
                </td>
                <td style="border-color: #9d9d9d; width: 80px">
                    <select id="id_unidad_medida_1" name="id_unidad_medida_1" style="width: 100%" class="form-control">
                        {{-- <option value="">Seleccione</option> --}}
                        @foreach ($unidad_medida as $u)
                            <option value="{{ $u->id_unidad_medida }}">{{ $u->siglas }}</option>
                        @endforeach
                    </select>
                </td>
                <td id="td_btn_add_store_1" style="border-color: #9d9d9d" class="text-center">
                    <div class='btn-group'>
                        <button type="button" class="btn btn-yura_primary" id="btn_add_row_especificacion_1"
                            title="Crear fila" onclick="add_row_especificacion()">
                            <i class="fa fa-plus" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="btn btn-yura_default" title="Guardar"
                            onclick="store_nueva_especificacion()">
                            <i class="fa fa-floppy-o" aria-hidden="true"></i> Guardar
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
@endif

<div id="table_especificaciones" style="overflow-y: scroll; max-height: 700px">
    <table width="100%" class="table-responsive table-bordered"
        style="border-color: #9d9d9d; margin-top: 10px; border-radius: 18px 18px 0 0"
        id="table_content_especificaciones">
        <thead>
            <tr style="background-color: #dd4b39; color: white" id="tr_fija_top_0">
                <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0; width:30px;">
                    <input type="checkbox" onchange="select_checks(this)">
                </th>
                <th class="text-center th_yura_green">
                    VARIEDAD
                </th>
                <th class="text-center th_yura_green">
                    TIPO
                </th>
                <th class="text-center th_yura_green">
                    CALIBRE
                </th>
                <th class="text-center th_yura_green">
                    CAJA
                </th>
                <th class="text-center th_yura_green">
                    RAMO X CAJA
                </th>
                <th class="text-center th_yura_green">
                    PRESENTACIÓN
                </th>
                <th class="text-center th_yura_green">
                    TALLOS X RAMO
                </th>
                <th class="text-center th_yura_green">
                    LONGITUD
                </th>
                <th class="text-center th_yura_green" style="width:100px">
                    CLIENTES
                </th>
                <th style="width: 80px; border-radius: 0 18px 0 0" class="text-center th_yura_green">
                    OPCIONES
                </th>
            </tr>
        </thead>
        @if (sizeof($listado) > 0)
            @php  $anterior = ''; @endphp
            @foreach ($listado as $x => $item)
                @php
                    $all_clientes = $item->clientes;
                @endphp
                @foreach ($item->especificacionesEmpaque as $y => $esp_emp)
                    @foreach ($esp_emp->detalles as $z => $det_esp_emp)
                        <tr
                            style="border-top: {{ $item->id_especificacion != $anterior ? '2px solid #9d9d9d;' : '' }}">
                            <td style="width:30px;border-color: #9d9d9d; padding: 0px; vertical-align: middle; {{ $item->clientes == '' ? 'background:orange' : '' }} "
                                class="text-center">
                                @if (count(explode(' | ', $all_clientes)) == 1)
                                    <input type="checkbox" class="input_especificacion" name="input_especificacion"
                                        data-id_det_esp_emp="{{ $det_esp_emp->id_detalle_especificacionempaque }}"
                                        data-id_esp="{{ $item->id_especificacion }}"
                                        onchange="habilitar_edicion_especificacion(this)">
                                @endif
                            </td>
                            <td style="border-color: #9d9d9d; padding: 0px; vertical-align: middle; width: 100px;{{ $item->clientes == '' ? 'background:orange' : '' }} "
                                class="text-center td_planta_edicion_especificacion">
                                <select id="id_planta_edicion_especificacion" style="width: 100%;display:none"
                                    name="id_planta_edicion_especificacion"
                                    onchange="set_variedad_edicion_especificacion(this)"
                                    class="form-control id_planta_edicion_especificacion">
                                    @foreach ($plantas as $p)
                                        <option
                                            {{ $det_esp_emp->variedad->planta->id_planta == $p->id_planta ? 'selected' : '' }}
                                            value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                                    @endforeach
                                </select>
                                <span
                                    class="span_id_planta_edicion_especificacion">{{ $det_esp_emp->variedad->planta->nombre }}</span>
                            </td>
                            <td style="border-color: #9d9d9d; padding: 0px; vertical-align: middle; width: 100px;{{ $item->clientes == '' ? 'background:orange' : '' }} "
                                class="text-center td_variedad_edicion_especificacion">
                                <select id="id_variedad_edicion_especificacion" style="width: 100%;display:none"
                                    name="id_variedad_edicion_especificacion"
                                    class="form-control id_variedad_edicion_especificacion">
                                    @foreach ($det_esp_emp->variedad->planta->variedades as $variedad)
                                        <option
                                            {{ $det_esp_emp->variedad->id_variedad == $variedad->id_variedad ? 'selected' : '' }}
                                            value="{{ $variedad->id_variedad }}">{{ $variedad->nombre }}</option>
                                    @endforeach
                                </select>
                                <span
                                    class="span_id_variedad_edicion_especificacion">{{ $det_esp_emp->variedad->nombre }}</span>
                            </td>
                            <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;{{ $item->clientes == '' ? 'background:orange' : '' }}"
                                class="text-center">
                                <select id="id_edicion_clasificacion_ramo" style="width: 100%;display:none"
                                    name="id_edicion_clasificacion_ramo"
                                    class="form-control id_edicion_clasificacion_ramo">
                                    @foreach ($clasificacion_ramo as $c)
                                        <option
                                            {{ $det_esp_emp->clasificacion_ramo->id_clasificacion_ramo == $c->id_clasificacion_ramo ? 'selected' : '' }}
                                            value="{{ $c->id_clasificacion_ramo }}">{{ $c->nombre }}</option>
                                    @endforeach
                                </select>
                                <span
                                    class="span_id_edicion_clasificacion_ramo">{{ $det_esp_emp->clasificacion_ramo->nombre }}</span>
                            </td>
                            @if ($z == 0)
                                <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;{{ $item->clientes == '' ? 'background:orange' : '' }}"
                                    class="text-center td_edicion_empaque" rowspan="{{ count($esp_emp->detalles) }}">
                                    <select id="id_edicion_empaque" style="width: 100%;display:none"
                                        name="id_edicion_empaque" class="form-control id_edicion_empaque">
                                        @foreach ($empaque as $e)
                                            <option
                                                {{ $esp_emp->empaque->id_empaque == $e->id_empaque ? 'selected' : '' }}
                                                value="{{ $e->id_empaque }}">
                                                {{ mb_strtoupper(explode('|', $e->nombre)[0]) }}</option>
                                        @endforeach
                                    </select>
                                    <span
                                        class="span_id_edicion_empaque">{{ explode('|', $esp_emp->empaque->nombre)[0] }}</span>
                                </td>
                            @endif
                            <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;{{ $item->clientes == '' ? 'background:orange' : '' }}"
                                class="text-center">
                                <input type="number" class="form-control edicion_ramo_x_caja text-center"
                                    style="width: 100%;display:none" value="{{ $det_esp_emp->cantidad }}"
                                    name="edicion_ramo_x_caja" required>
                                <span class="span_edicion_ramo_x_caja">{{ $det_esp_emp->cantidad }}</span>
                            </td>
                            <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;{{ $item->clientes == '' ? 'background:orange' : '' }}"
                                class="text-center td_edicion_presentacion">
                                <select id="id_edicion_presentacion" style="width: 100%;display:none"
                                    name="id_edicion_presentacion" class="form-control id_edicion_presentacion">
                                    @foreach ($presentacion as $p)
                                        <option
                                            {{ $det_esp_emp->empaque_p->id_empaque == $p->id_empaque ? 'selected' : '' }}
                                            value="{{ $p->id_empaque }}">{{ $p->nombre }}</option>
                                    @endforeach
                                </select>
                                <span
                                    class="span_id_edicion_presentacion">{{ $det_esp_emp->empaque_p->nombre }}</span>
                            </td>
                            <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;{{ $item->clientes == '' ? 'background:orange' : '' }}"
                                class="text-center">
                                <input type="number" placeholder="Cantidad" id="edicion_tallos_x_ramo"
                                    style="width: 100%;display:none;text-align:center" name="edicion_tallos_x_ramo"
                                    class="form-control edicion_tallos_x_ramo"
                                    value="{{ $det_esp_emp->tallos_x_ramos }}">
                                <span
                                    class="span_edicion_tallos_x_ramo">{{ isset($det_esp_emp->tallos_x_ramos) ? $det_esp_emp->tallos_x_ramos : '-' }}</span>
                            </td>
                            <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;{{ $item->clientes == '' ? 'background:orange' : '' }}"
                                class="text-center">
                                <input type="number" placeholder="Long. Cm" id="edicion_longitud"
                                    style="width: 100%;display:none;text-align:center" name="edicion_longitud"
                                    class="form-control edicion_longitud"
                                    value="{{ isset($det_esp_emp->longitud_ramo) ? $det_esp_emp->longitud_ramo : '' }}">
                                <span
                                    class="span_edicion_longitud">{{ isset($det_esp_emp->longitud_ramo) ? $det_esp_emp->longitud_ramo . ' ' . $det_esp_emp->unidad_medida->siglas : '-' }}</span>
                            </td>
                            <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;{{ $item->clientes == '' ? 'background:orange' : '' }}"
                                class="text-center">
                                <div title="{{ $all_clientes }}">
                                    {{ count(explode(' | ', $all_clientes)) }} CLIENTE(s)
                                </div>
                            </td>
                            @if ($item->id_especificacion != $anterior)
                                <td style="border-color: #9d9d9d;padding: 0px 0px; vertical-align: middle;"
                                    class="text-center" id="td_precio_{{ $x + 1 }}"
                                    rowspan="{{ getCantDetEspEmp($item->id_especificacion) }}">
                                    <div class="btn-group">
                                        @if ($item->tipo == 'N' && $item->estado == 1)
                                            <button type="button" class="btn btn-yura_default btn-xs"
                                                title="Ver asignaciones"
                                                onclick="asignar_especificacicon('{{ $item->id_especificacion }}')">
                                                <i class="fa fa-user-circle-o" aria-hidden="true"></i>
                                            </button>
                                        @endif
                                        @if (es_server())
                                            <button type="button"
                                                class="btn btn-yura_{{ $item->estado == 1 ? 'danger' : 'success' }} btn-xs"
                                                title="{{ $item->estado == 1 ? 'Deshabilitar' : 'Habilitar' }}"
                                                onclick="update_especificacion('{{ $item->id_especificacion }}','{{ $item->estado }}','{{ csrf_token() }}')">
                                                <i class="fa fa-fw fa-{{ $item->estado == 1 ? 'trash' : 'undo' }}"
                                                    style="color: white"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                        @php  $anterior = $item->id_especificacion;  @endphp
                    @endforeach
                @endforeach
            @endforeach
        @else
            <div class="alert alert-info text-center">No se han encontrado coincidencias</div>
        @endif
    </table>

</div>

<style>
    #tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 9;
    }

    span.select2-selection {
        width: 140px !important;
    }

    table#table_content_especificaciones .select2-container {
        display: none
    }
</style>

<script>
    function select_checks(check) {

        let input = $("input.input_especificacion")
        if ($(check).is(':checked')) {
            input.prop('checked', true)
        } else {
            input.prop('checked', false)
        }

        habilitar_edicion_especificacion(input)

    }

    function habilitar_edicion_especificacion(check) {

        let tr = $(check).parent().parent()

        if ($(check).is(':checked')) {

            tr.find(
                    'td.td_planta_edicion_especificacion .select2-container, td.td_variedad_edicion_especificacion .select2-container, .id_edicion_clasificacion_ramo, td.td_edicion_empaque .select2-container, .edicion_ramo_x_caja, td.td_edicion_presentacion .select2-container, .edicion_tallos_x_ramo, .edicion_longitud'
                )
                .css('display', 'grid');
            tr.find(
                    '.span_id_planta_edicion_especificacion, .span_id_variedad_edicion_especificacion, .span_id_edicion_clasificacion_ramo, .span_id_edicion_empaque, .span_edicion_ramo_x_caja, .span_id_edicion_presentacion, .span_edicion_tallos_x_ramo, .span_edicion_longitud'
                )
                .css('display', 'none');

        } else {

            tr.find(
                    'td.td_planta_edicion_especificacion .select2-container, td.td_variedad_edicion_especificacion .select2-container, .id_edicion_clasificacion_ramo, td.td_edicion_empaque .select2-container, .edicion_ramo_x_caja, td.td_edicion_presentacion .select2-container, .edicion_tallos_x_ramo, .edicion_longitud'
                )
                .css('display', 'none');
            tr.find(
                    '.span_id_planta_edicion_especificacion, .span_id_variedad_edicion_especificacion, .span_id_edicion_clasificacion_ramo, .span_id_edicion_empaque, .span_edicion_ramo_x_caja, .span_id_edicion_presentacion, .span_edicion_tallos_x_ramo, .span_edicion_longitud'
                )
                .css('display', 'grid');

        }

    }

    function set_variedad_edicion_especificacion(input) {

        datos = {
            _token: '{{ csrf_token() }}',
            id_planta: input.value,
        };

        $.post('{{ url('especificacion/seleccionar_variedad_especificacion') }}', datos, function(retorno) {

            let options = "";

            retorno.forEach(v => {
                options += `<option value='${v.id_variedad}'>${v.nombre}</option>`
            })

            $(input).parent().parent().find('select.id_variedad_edicion_especificacion').html(options)

        }, 'json').fail(function(retorno) {
            alerta_errores(retorno.responseText);
            alerta('Ha ocurrido un problema al enviar la información')
        }).always(function() {
            $.LoadingOverlay('hide')
        })

    }

    $(document).ready(() => {
        $("select[name='id_planta'], select[name='id_variedad'], select[name='id_clasificacion_ramo'], select[name='id_empaque'], select[name='id_presentacion'], .id_planta_edicion_especificacion, .id_variedad_edicion_especificacion, .id_edicion_empaque, .id_edicion_presentacion")
            .select2()
    })
</script>
