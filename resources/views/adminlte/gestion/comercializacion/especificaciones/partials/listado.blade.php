<legend class="text-center" style="margin-bottom: 5px; font-size: 1.3em">
    Especificaciones del cliente <b>{{ $cliente->detalle()->nombre }}</b>
</legend>
<div style="overflow-x: scroll; overflow-y: scroll; width: 100%; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="text-center th_yura_green" style="width: 30px">
                    <input type="checkbox" id="check_all"
                        onchange="$('.check_esp').prop('checked', $(this).prop('checked'))">
                </th>
                <th class="text-center th_yura_green">
                    VARIEDAD
                </th>
                <th class="text-center th_yura_green">
                    COLOR
                </th>
                <th class="text-center th_yura_green">
                    CAJA
                </th>
                <th class="text-center th_yura_green" style="width: 60px">
                    RxC
                </th>
                <th class="text-center th_yura_green">
                    PRESENTACION
                </th>
                <th class="text-center th_yura_green" style="width: 60px">
                    TxR
                </th>
                <th class="text-center th_yura_green" style="width: 90px">
                    LONGITUD
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $item)
                <tr>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="checkbox" class="check_esp mouse-hand" id="check_{{ $item->id_especificaciones }}"
                            data-id_esp="{{ $item->id_especificaciones }}">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <select id="edit_planta_{{ $item->id_especificaciones }}" style="width: 100%; height: 26px;"
                            onchange="select_planta($(this).val(), 'edit_variedad_{{ $item->id_especificaciones }}', 'td_cargar_variedades_{{ $item->id_especificaciones }}', '', ''); $('#check_{{ $item->id_especificaciones }}').prop('checked', true)">
                            @foreach ($plantas as $o)
                                <option value="{{ $o->id_planta }}"
                                    {{ $o->id_planta == $item->id_planta ? 'selected' : '' }}>
                                    {{ $o->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d"
                        id="td_cargar_variedades_{{ $item->id_especificaciones }}">
                        <select id="edit_variedad_{{ $item->id_especificaciones }}" style="width: 100%; height: 26px;"
                            onchange="$('#check_{{ $item->id_especificaciones }}').prop('checked', true)">
                            @foreach ($item->planta->variedades as $var)
                                <option value="{{ $var->id_variedad }}"
                                    {{ $var->id_variedad == $item->id_variedad ? 'selected' : '' }}>
                                    {{ $var->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <select id="edit_caja_{{ $item->id_especificaciones }}" style="width: 100%; height: 26px;"
                            onchange="$('#check_{{ $item->id_especificaciones }}').prop('checked', true)">
                            @foreach ($cajas as $o)
                                <option value="{{ $o->id_empaque }}"
                                    {{ $o->id_empaque == $item->id_empaque_c ? 'selected' : '' }}>
                                    {{ explode('|', $o->nombre)[0] }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" id="edit_ramos_x_caja_{{ $item->id_especificaciones }}"
                            style="width: 100%; height: 26px;"
                            onchange="$('#check_{{ $item->id_especificaciones }}').prop('checked', true)"
                            class="text-center" value="{{ $item->ramos_x_caja }}">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <select id="edit_presentacion_{{ $item->id_especificaciones }}"
                            style="width: 100%; height: 26px;"
                            onchange="$('#check_{{ $item->id_especificaciones }}').prop('checked', true)">
                            @foreach ($presentaciones as $o)
                                <option value="{{ $o->id_empaque }}"
                                    {{ $o->id_empaque == $item->id_empaque_p ? 'selected' : '' }}>
                                    {{ $o->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" id="edit_tallos_x_ramos_{{ $item->id_especificaciones }}"
                            style="width: 100%; height: 26px;"
                            onchange="$('#check_{{ $item->id_especificaciones }}').prop('checked', true)"
                            class="text-center" value="{{ $item->tallos_x_ramos }}">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" id="edit_longitud_ramo_{{ $item->id_especificaciones }}"
                            style="width: 100%; height: 26px;"
                            onchange="$('#check_{{ $item->id_especificaciones }}').prop('checked', true)"
                            class="text-center" value="{{ $item->longitud_ramo }}">
                    </td>
                </tr>
                <script>
                    /*select_planta($('#edit_planta_{{ $item->id_especificaciones }}').val(),
                                                        'edit_variedad_{{ $item->id_especificaciones }}', 'td_cargar_variedades_{{ $item->id_especificaciones }}',
                                                        '', '');
                                                    $('#edit_variedad_{{ $item->id_especificaciones }}').LoadingOverlay('show');
                                                    setTimeout(() => {
                                                        $('#edit_variedad_{{ $item->id_especificaciones }}').val('{{ $item->id_variedad }}');
                                                        $('#edit_variedad_{{ $item->id_especificaciones }}').LoadingOverlay('hide');
                                                    }, 3500);*/
                </script>
            @endforeach
        </tbody>
    </table>
</div>
<div class="text-center" style="margin-top: 1px">
    <div class="btn-group">
        <button type="button" class="btn btn-yura_warning" onclick="update_especificaciones()">
            <i class="fa fa-fw fa-save"></i> ACTUALIZAR
        </button>
        <button type="button" class="btn btn-yura_danger" onclick="delete_especificaciones()">
            <i class="fa fa-fw fa-trash"></i> ELIMINAR
        </button>
    </div>
</div>

<script>
    function update_especificaciones() {
        data = [];
        check_esp = $('.check_esp');
        for (i = 0; i < check_esp.length; i++) {
            if ($('#' + check_esp[i].id).prop('checked') == true) {
                id_esp = $('#' + check_esp[i].id).data('id_esp');
                planta = $('#edit_planta_' + id_esp).val();
                variedad = $('#edit_variedad_' + id_esp).val();
                caja = $('#edit_caja_' + id_esp).val();
                ramos_x_caja = $('#edit_ramos_x_caja_' + id_esp).val();
                presentacion = $('#edit_presentacion_' + id_esp).val();
                tallos_x_ramos = $('#edit_tallos_x_ramos_' + id_esp).val();
                longitud_ramo = $('#edit_longitud_ramo_' + id_esp).val();
                if (planta != '' && variedad != '' && caja != '' && ramos_x_caja > 0 && presentacion != '' &&
                    tallos_x_ramos > 0 && longitud_ramo >= 0) {
                    data.push({
                        id_esp: id_esp,
                        planta: planta,
                        variedad: variedad,
                        caja: caja,
                        ramos_x_caja: ramos_x_caja,
                        presentacion: presentacion,
                        tallos_x_ramos: tallos_x_ramos,
                        longitud_ramo: longitud_ramo,
                    })
                }
            }
        }

        mensaje = {
            title: '<i class="fa fa-fw fa-save"></i> Actualizar especificaciones',
            mensaje: '<div class="alert alert-warning text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de <b>ACTUALIZAR</b> estas especificaciones?</div>',
        };
        modal_quest('modal_update_especificaciones', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    data: JSON.stringify(data),
                };
                post_jquery_m('{{ url('especificaciones/update_especificaciones') }}', datos, function() {
                    cerrar_modals();
                    //listar_reporte();
                });
            });
    }

    function delete_especificaciones() {
        data = [];
        check_esp = $('.check_esp');
        for (i = 0; i < check_esp.length; i++) {
            if ($('#' + check_esp[i].id).prop('checked') == true) {
                id_esp = $('#' + check_esp[i].id).data('id_esp');
                data.push({
                    id_esp: id_esp,
                })
            }
        }

        mensaje = {
            title: '<i class="fa fa-fw fa-trash"></i> ELIMINAR especificaciones',
            mensaje: '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de <b>ELIMINAR</b> estas especificaciones?</div>',
        };
        modal_quest('modal_delete_especificaciones', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    data: JSON.stringify(data),
                };
                post_jquery_m('{{ url('especificaciones/delete_especificaciones') }}', datos, function() {
                    cerrar_modals();
                    listar_reporte();
                });
            });
    }
</script>
