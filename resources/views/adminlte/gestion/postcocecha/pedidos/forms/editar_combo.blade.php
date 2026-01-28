<div style="overflow-y: scroll; overflow-x: scroll; max-height: 500px">
    <input type="hidden" id="id_esp_emp"
        value="{{ $detalle->cliente_especificacion->especificacion->especificacionesEmpaque[0]->id_especificacion_empaque }}">
    <input type="hidden" id="id_det_ped" value="{{ $detalle->id_detalle_pedido }}">
    <table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%" id="table_editar_combo">
        <tr>
            <th class="text-center th_yura_green padding_lateral_5">
                Cajas
            </th>
            <th class="text-center th_yura_green padding_lateral_5">
                Variedad
            </th>
            <th class="text-center th_yura_green padding_lateral_5">
                Color
            </th>
            <th class="text-center th_yura_green padding_lateral_5">
                Peso
            </th>
            <th class="text-center th_yura_green padding_lateral_5">
                Presentacion
            </th>
            <th class="text-center th_yura_green padding_lateral_5">
                R.xCaja
            </th>
            <th class="text-center th_yura_green padding_lateral_5">
                T.xRamos
            </th>
            <th class="text-center th_yura_green padding_lateral_5">
                Longitud
            </th>
            <th class="text-center th_yura_green padding_lateral_5">
                Precio
            </th>
            <th class="text-center th_yura_green padding_lateral_5" style="width: 80px">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_default" onclick="add_fila_combo()">
                        <i class="fa fa-fw fa-plus"></i>
                    </button>
                </div>
            </th>
        </tr>
        @foreach ($detalle->cliente_especificacion->especificacion->especificacionesEmpaque[0]->detalles as $pos => $det_esp)
            @php
                $getRamosXCajaModificado = getRamosXCajaModificado($detalle->id_detalle_pedido, $det_esp->id_detalle_especificacionempaque);
                $ramos_x_caja = isset($getRamosXCajaModificado) ? $getRamosXCajaModificado->cantidad : $det_esp->cantidad;
                $precio = getPrecioByDetEsp($detalle->precio, $det_esp->id_detalle_especificacionempaque);
            @endphp
            <tr id="edit_tr_combo_{{ $det_esp->id_detalle_especificacionempaque }}">
                @if ($pos == 0)
                    <td class="text-center" style="border-color: #9d9d9d" id="td_cajas_combo"
                        rowspan="{{ count($detalle->cliente_especificacion->especificacion->especificacionesEmpaque[0]->detalles) }}">
                        {{ $detalle->cantidad }}
                    </td>
                @endif
                <td class="text-center" style="border-color: #9d9d9d">
                    <select id="edit_planta_combo_{{ $det_esp->id_detalle_especificacionempaque }}" style="width: 100%"
                        onchange="select_planta($(this).val(), 'edit_variedad_combo_{{ $det_esp->id_detalle_especificacionempaque }}', 'edit_variedad_combo_{{ $det_esp->id_detalle_especificacionempaque }}', '<option value=>Seleccione</option>')">
                        <option value="">Seleccione</option>
                        @foreach ($plantas as $p)
                            <option value="{{ $p->id_planta }}"
                                {{ $p->id_planta == $det_esp->variedad->id_planta ? 'selected' : '' }}>
                                {{ $p->nombre }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <select id="edit_variedad_combo_{{ $det_esp->id_detalle_especificacionempaque }}"
                        style="width: 100%">
                        <option value="{{ $det_esp->id_variedad }}">{{ $det_esp->variedad->nombre }}</option>
                    </select>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <select id="edit_clasificacion_ramo_combo_{{ $det_esp->id_detalle_especificacionempaque }}"
                        style="width: 100%">
                        @foreach ($clasificaciones_ramo as $p)
                            <option value="{{ $p->id_clasificacion_ramo }}"
                                {{ $p->id_clasificacion_ramo == $det_esp->id_clasificacion_ramo ? 'selected' : '' }}>
                                {{ $p->nombre }}{{ $p->unidad_medida->siglas }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <select id="edit_presentacion_combo_{{ $det_esp->id_detalle_especificacionempaque }}"
                        style="width: 100%">
                        @foreach ($presentaciones as $p)
                            <option value="{{ $p->id_empaque }}"
                                {{ $p->id_empaque == $det_esp->id_empaque_p ? 'selected' : '' }}>
                                {{ $p->nombre }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" value="{{ $ramos_x_caja }}" class="text-center" style="width: 100%"
                        id="edit_ramos_x_caja_combo_{{ $det_esp->id_detalle_especificacionempaque }}" required>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" value="{{ $det_esp->tallos_x_ramos }}" class="text-center"
                        style="width: 100%"
                        id="edit_tallos_x_ramos_combo_{{ $det_esp->id_detalle_especificacionempaque }}" required>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" value="{{ $det_esp->longitud_ramo }}" class="text-center" style="width: 100%"
                        id="edit_longitud_combo_{{ $det_esp->id_detalle_especificacionempaque }}" required>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" value="{{ $precio }}" class="text-center" style="width: 100%"
                        id="edit_precio_combo_{{ $det_esp->id_detalle_especificacionempaque }}" required>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_primary"
                            onclick="update_det_esp('{{ $det_esp->id_detalle_especificacionempaque }}')">
                            <i class="fa fa-fw fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_danger"
                            onclick="delete_det_esp('{{ $det_esp->id_detalle_especificacionempaque }}')">
                            <i class="fa fa-fw fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
    </table>
</div>

<select id="select_plantas_combo" class="hidden">
    <option value="">Seleccione</option>
    @foreach ($plantas as $p)
        <option value="{{ $p->id_planta }}">
            {{ $p->nombre }}</option>
    @endforeach
</select>
<select id="select_clasificaciones_ramo_combo" class="hidden">
    @foreach ($clasificaciones_ramo as $p)
        <option value="{{ $p->id_clasificacion_ramo }}">
            {{ $p->nombre }}{{ $p->unidad_medida->siglas }}
        </option>
    @endforeach
</select>
<select id="select_presentaciones_combo" class="hidden">
    <option value="">Seleccione</option>
    @foreach ($presentaciones as $p)
        <option value="{{ $p->id_empaque }}">
            {{ $p->nombre }}</option>
    @endforeach
</select>

<script>
    var cantidad_new = {{ isset($pos) ? $pos + 1 : 0 }};

    function add_fila_combo() {
        rowspan = parseInt($('#td_cajas_combo').prop('rowspan'));
        rowspan++;
        $('#td_cajas_combo').prop('rowspan', rowspan);
        cantidad_new++;

        select_plantas_combo = $('#select_plantas_combo').html();
        select_clasificaciones_ramo_combo = $('#select_clasificaciones_ramo_combo').html();
        select_presentaciones_combo = $('#select_presentaciones_combo').html();
        parametros_select_planta = [
            "'new_variedad_combo_" + cantidad_new + "'",
            "'<option value=>Seleccione</option>'",
        ];
        $('#table_editar_combo').append('<tr id="new_tr_combo_' + cantidad_new + '">' +
            '<td class="text-center" style="border-color: #9d9d9d" id="td_new_planta_combo_' + cantidad_new + '">' +
            '<select id="new_planta_combo_' + cantidad_new + '" style="width: 100%" ' +
            'onchange="select_planta($(this).val(), ' + parametros_select_planta[0] + ', ' +
            parametros_select_planta[0] + ', ' + parametros_select_planta[1] + ')">' +
            select_plantas_combo +
            '</select>' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<select id="new_variedad_combo_' + cantidad_new + '" style="width: 100%">' +
            '</select>' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<select id="new_clasificacion_ramo_combo_' + cantidad_new + '" style="width: 100%">' +
            select_clasificaciones_ramo_combo +
            '</select>' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<select id="new_presentacion_combo_' + cantidad_new + '" style="width: 100%">' +
            select_presentaciones_combo +
            '</select>' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" value="0" class="text-center" style="width: 100%"' +
            'id="new_ramos_x_caja_combo_' + cantidad_new + '" required>' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" value="0" class="text-center"' +
            'style="width: 100%" id="new_tallos_x_ramos_combo_' + cantidad_new + '" required>' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" value="0" class="text-center" style="width: 100%"' +
            'id="new_longitud_combo_' + cantidad_new + '" required>' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" value="0" class="text-center" style="width: 100%"' +
            'id="new_precio_combo_' + cantidad_new + '" required>' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<div class="btn-group" id="new_btn-group_combo_' + cantidad_new + '">' +
            '<button type="button" class="btn btn-xs btn-yura_primary" onclick="store_det_esp(' + cantidad_new +
            ')">' +
            '<i class="fa fa-fw fa-save"></i>' +
            '</button>' +
            '<button type="button" class="btn btn-xs btn-yura_danger" onclick="delete_fila_combo(' + cantidad_new +
            ')">' +
            '<i class="fa fa-fw fa-minus"></i>' +
            '</button>' +
            '</div>' +
            '</td>' +
            '</tr>');
    }

    function delete_fila_combo(pos) {
        $('#new_tr_combo_' + pos).remove();

        rowspan = parseInt($('#td_cajas_combo').prop('rowspan'));
        rowspan--;
        $('#td_cajas_combo').prop('rowspan', rowspan);
    }

    function store_det_esp(pos) {
        datos = {
            _token: '{{ csrf_token() }}',
            id_esp_emp: $('#id_esp_emp').val(),
            id_det_ped: $('#id_det_ped').val(),
            id_planta: $('#new_planta_combo_' + pos).val(),
            id_variedad: $('#new_variedad_combo_' + pos).val(),
            clasificacion: $('#new_clasificacion_ramo_combo_' + pos).val(),
            empaque: $('#new_presentacion_combo_' + pos).val(),
            ramos_x_caja: $('#new_ramos_x_caja_combo_' + pos).val(),
            tallos_x_ramos: $('#new_tallos_x_ramos_combo_' + pos).val(),
            longitud: $('#new_longitud_combo_' + pos).val(),
            precio: $('#new_precio_combo_' + pos).val(),
        }
        $('#new_tr_combo_' + pos).LoadingOverlay('show');
        $.post('{{ url('pedidos/store_det_esp') }}', datos, function(retorno) {
            if (retorno.success) {
                mini_alerta('success', retorno.mensaje, 5000);
                $('#new_variedad_combo_' + pos).prop('id', 'edit_variedad_combo_' + retorno.id_det_esp);
                $('#new_clasificacion_ramo_combo_' + pos).prop('id', 'edit_clasificacion_ramo_combo_' + retorno
                    .id_det_esp);
                $('#new_presentacion_combo_' + pos).prop('id', 'edit_presentacion_combo_' + retorno.id_det_esp);
                $('#new_ramos_x_caja_combo_' + pos).prop('id', 'edit_ramos_x_caja_combo_' + retorno.id_det_esp);
                $('#new_tallos_x_ramos_combo_' + pos).prop('id', 'edit_tallos_x_ramos_combo_' + retorno
                    .id_det_esp);
                $('#new_longitud_combo_' + pos).prop('id', 'edit_longitud_combo_' + retorno.id_det_esp);
                $('#new_precio_combo_' + pos).prop('id', 'edit_precio_combo_' + retorno.id_det_esp);

                parametros_select_planta = [
                    "'edit_variedad_combo_" + retorno.id_det_esp + "'",
                    "'<option value=>Seleccione</option>'",
                ];
                $('#td_new_planta_combo_' + pos).html('<select id="edit_planta_combo_' + retorno.id_det_esp +
                    '" style="width: 100%" ' +
                    'onchange="select_planta($(this).val(), ' + parametros_select_planta[0] + ', ' +
                    parametros_select_planta[0] + ', ' + parametros_select_planta[1] + ')">' +
                    select_plantas_combo +
                    '</select>');
                $('#edit_planta_combo_' + retorno.id_det_esp).val(datos['id_planta']);

                $('#new_btn-group_combo_' + pos).addClass('hidden');

                listar_resumen_pedidos(
                    document.getElementById('fecha_pedidos_search').value,
                    true,
                    document.getElementById('id_configuracion_pedido').value,
                    document.getElementById('id_cliente').value
                );
            } else {
                alerta(retorno.mensaje);
            }
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
            alerta('Ha ocurrido un problema al enviar la información');
        }).always(function() {
            $('#new_tr_combo_' + pos).LoadingOverlay('hide');
        });
    }

    function update_det_esp(det_esp) {
        datos = {
            _token: '{{ csrf_token() }}',
            det_esp: det_esp,
            id_esp_emp: $('#id_esp_emp').val(),
            id_det_ped: $('#id_det_ped').val(),
            id_planta: $('#new_planta_combo_' + det_esp).val(),
            id_variedad: $('#edit_variedad_combo_' + det_esp).val(),
            clasificacion: $('#edit_clasificacion_ramo_combo_' + det_esp).val(),
            empaque: $('#edit_presentacion_combo_' + det_esp).val(),
            ramos_x_caja: $('#edit_ramos_x_caja_combo_' + det_esp).val(),
            tallos_x_ramos: $('#edit_tallos_x_ramos_combo_' + det_esp).val(),
            longitud: $('#edit_longitud_combo_' + det_esp).val(),
            precio: $('#edit_precio_combo_' + det_esp).val(),
        }
        post_jquery_m('{{ url('pedidos/update_det_esp') }}', datos, function(retorno) {
            listar_resumen_pedidos(
                document.getElementById('fecha_pedidos_search').value,
                true,
                document.getElementById('id_configuracion_pedido').value,
                document.getElementById('id_cliente').value
            );
        });
    }

    function delete_det_esp(det_esp) {
        mensaje = {
            title: '<i class="fa fa-fw fa-trash"></i> Eliminar detalle del combo',
            mensaje: '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de Eliminar detalle del combo?</div>',
        };
        modal_quest('modal_delete_det_esp', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    det_esp: det_esp,
                    id_det_ped: $('#id_det_ped').val(),
                }
                post_jquery_m('{{ url('pedidos/delete_det_esp') }}', datos, function(retorno) {
                    $('#edit_tr_combo_' + det_esp).remove();
                    listar_resumen_pedidos(
                        document.getElementById('fecha_pedidos_search').value,
                        true,
                        document.getElementById('id_configuracion_pedido').value,
                        document.getElementById('id_cliente').value
                    );
                });
            });
    }
</script>
