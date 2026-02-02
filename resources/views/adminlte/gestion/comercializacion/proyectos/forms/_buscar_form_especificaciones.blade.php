<div style="overflow-x: scroll; overflow-y: scroll; max-height: 650px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; font-size: 0.9em"
        id="table_form_especificaciones">
        <tbody>
            <tr class="tr_fija_top_0">
                <th class="text-center th_yura_green" style="min-width: 60px">
                    PIEZAS
                </th>
                <th class="text-center th_yura_green" style="min-width: 100px">
                    VARIEDAD
                </th>
                <th class="text-center th_yura_green" style="min-width: 90px">
                    COLOR
                </th>
                <th class="text-center th_yura_green" style="min-width: 160px">
                    CAJA
                </th>
                <th class="text-center th_yura_green" style="min-width: 160px">
                    PRESENTACION
                </th>
                <th class="text-center th_yura_green" style="min-width: 60px">
                    R. X CAJA
                </th>
                <th class="text-center th_yura_green" style="min-width: 60px">
                    TOTAL RAMOS
                </th>
                <th class="text-center th_yura_green" style="min-width: 60px">
                    T. X RAMOS
                </th>
                <th class="text-center th_yura_green" style="min-width: 60px">
                    TOTAL TALLOS
                </th>
                <th class="text-center th_yura_green" style="min-width: 120px">
                    LONGITUD
                </th>
                <th class="text-center th_yura_green" style="min-width: 120px">
                    PESO
                </th>
                <th class="text-center th_yura_green" style="min-width: 60px">
                    PRECIO
                    <input type="number" style="width: 100%; color: black" class="text-center" min="0"
                        id="form_input_all_precio_esp" onchange="set_all_precio_esp()" onkeyup="set_all_precio_esp()">
                </th>
                @foreach ($datos_exportacion as $dat_exp)
                    <th class="text-center bg-yura_dark" style="min-width: 100px">
                        {{ $dat_exp->nombre }}
                        <input type="text" style="width: 100%; color: black"
                            class="text-center form_input_all_marcacion" min="0"
                            data-id_dato_exportacion="{{ $dat_exp->id_dato_exportacion }}"
                            id="form_input_all_marcacion_{{ $dat_exp->id_dato_exportacion }}"
                            onchange="set_all_marcacion('{{ $dat_exp->id_dato_exportacion }}')"
                            onkeyup="set_all_marcacion('{{ $dat_exp->id_dato_exportacion }}')">
                    </th>
                @endforeach
            </tr>
            @foreach ($listado as $item)
                <tr onmouseover="$(this).addClass('bg-yura_dark')" onmouseleave="$(this).removeClass('bg-yura_dark')"
                    class="">
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%; color: black" class="text-center"
                            onchange="calcular_totales_especificaciones('{{ $item->id_especificaciones }}')"
                            onkeyup="calcular_totales_especificaciones('{{ $item->id_especificaciones }}')"
                            id="form_piezas_{{ $item->id_especificaciones }}" min="0">

                        <input type="hidden" class="ids_form_especificaciones"
                            id="id_form_especificaciones_{{ $item->id_especificaciones }}"
                            value="{{ $item->id_especificaciones }}" data-nombre_planta="{{ $item->pta_nombre }}"
                            data-variedad="{{ $item->id_variedad }}" data-nombre_variedad="{{ $item->var_nombre }}"
                            data-caja="{{ $item->id_empaque_c }}"
                            data-nombre_caja="{{ explode('|', $item->caj_nombre)[0] }}"
                            data-presentacion="{{ $item->id_empaque_p }}"
                            data-nombre_presentacion="{{ $item->pres_nombre }}">
                    </td>
                    <td class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->pta_nombre }}
                    </td>
                    <td class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->var_nombre }}
                    </td>
                    <td class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                        {{ explode('|', $item->caj_nombre)[0] }}
                    </td>
                    <td class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->pres_nombre }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%; color: black" class="text-center"
                            id="form_ramos_x_caja_{{ $item->id_especificaciones }}" value="{{ $item->ramos_x_caja }}">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%; color: black" class="text-center"
                            id="form_total_ramos_{{ $item->id_especificaciones }}" readonly="">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%; color: black" class="text-center"
                            id="form_tallos_x_ramos_{{ $item->id_especificaciones }}"
                            value="{{ $item->tallos_x_ramos }}">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%; color: black" class="text-center"
                            id="form_total_tallos_{{ $item->id_especificaciones }}" readonly="">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="text" style="width: 100%; color: black" class="text-center"
                            id="form_longitud_{{ $item->id_especificaciones }}" value="{{ $item->longitud_ramo }}">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="text" style="width: 100%; color: black" class="text-center"
                            id="form_peso_{{ $item->id_especificaciones }}" value="{{ $item->peso_ramo }}">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%; color: black"
                            id="form_precio_esp_{{ $item->id_especificaciones }}" class="text-center">
                    </td>
                    @foreach ($datos_exportacion as $dat_exp)
                        <td class="text-center" style="border-color: #9d9d9d">
                            <input type="text" style="width: 100%; color: black" class="text-center"
                                id="form_marcacion_esp_{{ $dat_exp->id_dato_exportacion }}_{{ $item->id_especificaciones }}">
                        </td>
                    @endforeach
                </tr>
            @endforeach
            <tr class="tr_fija_bottom_0">
                <th class="text-center th_yura_green" colspan="15">
                    <div class="btn-group">
                        <button type="button" class="btn btn-yura_danger"
                            onclick="agregar_all_especificacion_pedido()">
                            <i class="fa fa-fw fa-plus"></i> Agregar Productos al Pedido
                        </button>
                    </div>
                </th>
            </tr>
        </tbody>
    </table>
</div>

<script>
    function calcular_totales_especificaciones(id_esp) {
        piezas = parseInt($('#form_piezas_' + id_esp).val());
        ramos_x_caja = parseInt($('#form_ramos_x_caja_' + id_esp).val());
        tallos_x_ramos = parseInt($('#form_tallos_x_ramos_' + id_esp).val());

        total_ramos = piezas * ramos_x_caja;
        total_tallos = piezas * ramos_x_caja * tallos_x_ramos;
        $('#form_total_ramos_' + id_esp).val(total_ramos);
        $('#form_total_tallos_' + id_esp).val(total_tallos);
    }

    function set_all_precio_esp() {
        precio = $('#form_input_all_precio_esp').val();
        ids_form_especificaciones = $('.ids_form_especificaciones');
        for (i = 0; i < ids_form_especificaciones.length; i++) {
            id = ids_form_especificaciones[i].id;
            id_esp = $('#' + id).val();
            piezas = parseInt($('#form_piezas_' + id_esp).val());
            if (piezas > 0) {
                $('#form_precio_esp_' + id_esp).val(precio);
            }
        }
    }

    function set_all_marcacion(marcacion) {
        valor = $('#form_input_all_marcacion_' + marcacion).val();
        ids_form_especificaciones = $('.ids_form_especificaciones');
        for (i = 0; i < ids_form_especificaciones.length; i++) {
            id = ids_form_especificaciones[i].id;
            id_esp = $('#' + id).val();
            piezas = parseInt($('#form_piezas_' + id_esp).val());
            if (piezas > 0) {
                $('#form_marcacion_esp_' + marcacion + '_' + id_esp).val(valor);
            }
        }
    }

    function agregar_especificacion_pedido(id_esp) {
        piezas = $('#form_piezas_' + id_esp).val();
        nombre_planta = $('#id_form_especificaciones_' + id_esp).data('nombre_planta');
        variedad = $('#id_form_especificaciones_' + id_esp).data('variedad');
        nombre_variedad = $('#id_form_especificaciones_' + id_esp).data('nombre_variedad');
        caja = $('#id_form_especificaciones_' + id_esp).data('caja');
        nombre_caja = $('#id_form_especificaciones_' + id_esp).data('nombre_caja');
        presentacion = $('#id_form_especificaciones_' + id_esp).data('presentacion');
        nombre_presentacion = $('#id_form_especificaciones_' + id_esp).data('nombre_presentacion');
        ramos_x_caja = $('#form_ramos_x_caja_' + id_esp).val();
        total_ramos = $('#form_total_ramos_' + id_esp).val();
        tallos_x_ramos = $('#form_tallos_x_ramos_' + id_esp).val();
        total_tallos = $('#form_total_tallos_' + id_esp).val();
        longitud = $('#form_longitud_' + id_esp).val();
        peso = $('#form_peso_' + id_esp).val();
        precio_esp = $('#form_precio_esp_' + id_esp).val();

        form_cant_detalles++;

        ids_marcaciones = $('.form_input_all_marcacion');
        celdas_marcaciones = '';
        for (m = 0; m < ids_marcaciones.length; m++) {
            id_marcacion = ids_marcaciones[m].getAttribute('data-id_dato_exportacion');
            valor_marcacion = $('#form_marcacion_esp_' + id_marcacion + '_' + id_esp).val();
            celdas_marcaciones += '<td class="text-center" style="border-color: #9d9d9d">' +
                '<input type="text" style="width: 100%; color: black" class="text-center" ' +
                'id="ped_marcacion_' + id_marcacion + '_' + form_cant_detalles + '" value="' + valor_marcacion + '">' +
                '</td>';
        }

        clase_tr = "'.tr_form_ped_" + form_cant_detalles + "'";
        clase_bg = "'bg-yura_dark'";
        $('#tbody_form_contenido_pedido').append('<tr class="tr_form_ped_' + form_cant_detalles + '" ' +
            'onmouseover="$(' + clase_tr + ').addClass(' + clase_bg + ')"' +
            'onmouseleave="$(' + clase_tr + ').removeClass(' + clase_bg + ')">' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%; color: black" class="text-center"' +
            'onchange="calcular_totales_pedido()"' +
            'onkeyup="calcular_totales_pedido()"' +
            'id="ped_piezas_' + form_cant_detalles + '" min="0" value="' + piezas + '">' +

            '<input type="hidden" class="pos_ped_especificaciones" value="' + form_cant_detalles + '">' +
            '<input type="hidden" id="ped_id_especificaciones_' + form_cant_detalles +
            '" value="' + id_esp + '">' +
            '<input type="hidden" id="ped_variedad_' + form_cant_detalles +
            '" value="' + variedad + '">' +
            '<input type="hidden" id="ped_caja_' + form_cant_detalles + '" value="' + caja + '">' +
            '<input type="hidden" id="ped_presentacion_' + form_cant_detalles + '" ' +
            'value = "' + presentacion + '" > ' +
            '<input type="hidden" id="ped_longitud_' + form_cant_detalles + '" ' +
            'value = "' + longitud + '" > ' +
            '<input type="hidden" id="ped_peso_' + form_cant_detalles + '" ' +
            'value = "' + peso + '" > ' +
            '</td>' +
            '<td class="text-center padding_lateral_5" style="border-color: #9d9d9d">' +
            nombre_planta +
            '</td>' +
            '<td class="text-center padding_lateral_5" style="border-color: #9d9d9d">' +
            nombre_variedad +
            '</td>' +
            '<td class="text-center padding_lateral_5" style="border-color: #9d9d9d">' +
            nombre_caja +
            '</td>' +
            '<td class="text-center padding_lateral_5" style="border-color: #9d9d9d">' +
            nombre_presentacion +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%; color: black" class="text-center" ' +
            'onchange="calcular_totales_pedido()" ' +
            'onkeyup="calcular_totales_pedido()" ' +
            'id="ped_ramos_x_caja_' + form_cant_detalles + '" min="0" value="' + ramos_x_caja + '">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%; color: black" class="text-center" ' +
            'id="ped_total_ramos_' + form_cant_detalles + '" readonly disabled value="' + total_ramos + '">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%; color: black" class="text-center" ' +
            'onchange="calcular_totales_pedido()" ' +
            'onkeyup="calcular_totales_pedido()" ' +
            'id="ped_tallos_x_ramos_' + form_cant_detalles + '" min="0" value="' + tallos_x_ramos + '">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%; color: black" class="text-center" ' +
            'id="ped_total_tallos_' + form_cant_detalles + '" readonly disabled value="' + total_tallos + '">' +
            '</td>' +
            '<td class="text-center padding_lateral_5" style="border-color: #9d9d9d">' +
            longitud + 'cm' +
            '</td>' +
            '<td class="text-center padding_lateral_5" style="border-color: #9d9d9d">' +
            peso + 'gr' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%; color: black" class="text-center" ' +
            'onchange="calcular_totales_pedido()" ' +
            'onkeyup="calcular_totales_pedido()" ' +
            'id="ped_precio_esp_' + form_cant_detalles + '" min="0" value="' + precio_esp + '">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="text" style="width: 100%; color: black" class="text-center" ' +
            'id="ped_total_precio_caja_' + form_cant_detalles + '" readonly disabled>' +
            '</td>' +
            celdas_marcaciones +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<button type="button" class="btn btn-xs btn-yura_danger" onclick="delete_contenido_pedido(' +
            form_cant_detalles + ')" title="Eliminar Pieza">' +
            '<i class="fa fa-fw fa-trash"></i>' +
            '</button>' +
            '</td>' +
            '</tr>');

        calcular_totales_pedido();
    }

    function agregar_all_especificacion_pedido() {
        ids_form_especificaciones = $('.ids_form_especificaciones');
        for (i = 0; i < ids_form_especificaciones.length; i++) {
            id = ids_form_especificaciones[i].id;
            id_esp = $('#' + id).val();
            piezas = parseInt($('#form_piezas_' + id_esp).val());
            if (piezas > 0) {
                agregar_especificacion_pedido(id_esp);
            }
        }
    }
</script>
