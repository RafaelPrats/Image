<script>
    var count = 0;

    function add_costo_horas() {
        count++;
        $('#table_costo_horas').append('<tr>' +
            '                    <td class="text-center" style="border-color: #9d9d9d">' +
            '                        <input type="text" class="text-center" style="width: 100%; text-transform: uppercase; background-color: #e9ecef" id="new_nombre_' + count + '">' +
            '                    </td>' +
            '                    <td class="text-center" style="border-color: #9d9d9d">' +
            '                        <input type="number" class="text-center" style="width: 100%" id="new_sueldo_promedio_' + count + '" ' +
            '                               onchange="calcular_new_costo_horas(' + count + ')"' +
            '                               onkeyup="calcular_new_costo_horas(' + count + ')">' +
            '                    </td>' +
            '                    <td class="text-center" style="border-color: #9d9d9d">' +
            '                        <input type="number" class="text-center" style="width: 100%; background-color: #e9ecef" id="new_valor_hora_' + count + '" readonly>' +
            '                    </td>' +
            '                    <td class="text-center" style="border-color: #9d9d9d">' +
            '                        <input type="number" class="text-center" style="width: 100%; background-color: #e9ecef" id="new_prov_dt_' + count + '" readonly>' +
            '                    </td>' +
            '                    <td class="text-center" style="border-color: #9d9d9d">' +
            '                        <input type="number" class="text-center" style="width: 100%; background-color: #e9ecef" id="new_prov_dc_' + count + '" readonly>' +
            '                    </td>' +
            '                    <td class="text-center" style="border-color: #9d9d9d">' +
            '                        <input type="number" class="text-center" style="width: 100%; background-color: #e9ecef" id="new_prov_reserva_' + count + '" readonly>' +
            '                    </td>' +
            '                    <td class="text-center" style="border-color: #9d9d9d">' +
            '                        <input type="number" class="text-center" style="width: 100%; background-color: #e9ecef" id="new_aporte_patronal_' + count + '" readonly>' +
            '                    </td>' +
            '                    <td class="text-center" style="border-color: #9d9d9d">' +
            '                        <input type="number" class="text-center" style="width: 100%; background-color: #e9ecef" id="new_total_provisiones_' + count + '" readonly>' +
            '                    </td>' +
            '                    <td class="text-center" style="border-color: #9d9d9d">' +
            '                        <input type="number" class="text-center" style="width: 100%; background-color: #e9ecef" id="new_valor_hora_provisiones_' + count + '" readonly>' +
            '                    </td>' +
            '                    <td class="text-center" style="border-color: #9d9d9d">' +
            '                        <button type="button" class="btn btn-xs btn-yura_primary" onclick="store_costo_horas(' + count + ')">' +
            '                           <i class="fa fa-fw fa-save"></i>' +
            '                        </button>' +
            '                    </td>' +
            '                </tr>');
        $('#btn_add_costo_horas').addClass('hidden');
        $('#new_nombre_' + count).focus();
    }

    function store_costo_horas(i) {
        datos = {
            _token: '{{csrf_token()}}',
            nombre: $('#new_nombre_' + i).val(),
            sueldo_promedio: $('#new_sueldo_promedio_' + i).val(),
            valor_hora: $('#new_valor_hora_' + i).val(),
            prov_dt: $('#new_prov_dt_' + i).val(),
            prov_dc: $('#new_prov_dc_' + i).val(),
            prov_reserva: $('#new_prov_reserva_' + i).val(),
            aporte_patronal: $('#new_aporte_patronal_' + i).val(),
            total_provisiones: $('#new_total_provisiones_' + i).val(),
            valor_hora_provisiones: $('#new_valor_hora_provisiones_' + i).val(),
        };
        post_jquery_m('{{url('costos_hora/store_costo_horas')}}', datos, function () {
            location.reload();
        });
    }

    function update_costo_horas(i) {
        datos = {
            _token: '{{csrf_token()}}',
            id: i,
            nombre: $('#nombre_' + i).val(),
            sueldo_promedio: $('#sueldo_promedio_' + i).val(),
            valor_hora: $('#valor_hora_' + i).val(),
            prov_dt: $('#prov_dt_' + i).val(),
            prov_dc: $('#prov_dc_' + i).val(),
            prov_reserva: $('#prov_reserva_' + i).val(),
            aporte_patronal: $('#aporte_patronal_' + i).val(),
            total_provisiones: $('#total_provisiones_' + i).val(),
            valor_hora_provisiones: $('#valor_hora_provisiones_' + i).val(),
        };
        post_jquery_m('{{url('costos_hora/update_costo_horas')}}', datos, function () {
            //location.reload();
        });
    }

    function calcular_new_costo_horas(id) {
        nombre = $('#new_nombre_' + id).val().toUpperCase();
        sueldo_promedio = parseFloat($('#new_sueldo_promedio_' + id).val());
        prov_dc = 0;
        if (nombre == 'ORDINARIA') {
            valor_hora = sueldo_promedio / 240;
            prov_dc = Math.round((((sueldo_promedio / 12) / 30) / 8) * 100) / 100;
        }
        if (nombre == '50%') {
            valor_hora = (sueldo_promedio / 240) * 1.5;
        }
        if (nombre == '100%') {
            valor_hora = (sueldo_promedio / 240) * 2;
        }
        $('#new_valor_hora_' + id).val(Math.round(valor_hora * 100) / 100);
        prov_dt = valor_hora / 12;
        $('#new_prov_dt_' + id).val(Math.round(prov_dt * 100) / 100);
        $('#new_prov_dc_' + id).val(Math.round(prov_dc * 100) / 100);
        prov_reserva = prov_dt;
        $('#new_prov_reserva_' + id).val(Math.round(prov_reserva * 100) / 100);
        aporte_patronal = valor_hora * 0.1215
        $('#new_aporte_patronal_' + id).val(Math.round(aporte_patronal * 100) / 100);
        total_provisiones = prov_dt + prov_dc + prov_reserva + aporte_patronal;
        $('#new_total_provisiones_' + id).val(Math.round(total_provisiones * 100) / 100);
        valor_hora_provisiones = valor_hora + total_provisiones;
        $('#new_valor_hora_provisiones_' + id).val(Math.round(valor_hora_provisiones * 100) / 100);
    }

    function calcular_costo_horas(id) {
        nombre = $('#nombre_' + id).val().toUpperCase();
        sueldo_promedio = parseFloat($('#sueldo_promedio_' + id).val());
        prov_dc = 0;
        if (nombre == 'ORDINARIA') {
            valor_hora = sueldo_promedio / 240;
            prov_dc = Math.round((((sueldo_promedio / 12) / 30) / 8) * 100) / 100;
        }
        if (nombre == '50%') {
            valor_hora = (sueldo_promedio / 240) * 1.5;
        }
        if (nombre == '100%') {
            valor_hora = (sueldo_promedio / 240) * 2;
        }
        $('#valor_hora_' + id).val(Math.round(valor_hora * 100) / 100);
        prov_dt = valor_hora / 12;
        $('#prov_dt_' + id).val(Math.round(prov_dt * 100) / 100);
        $('#prov_dc_' + id).val(Math.round(prov_dc * 100) / 100);
        prov_reserva = prov_dt;
        $('#prov_reserva_' + id).val(Math.round(prov_reserva * 100) / 100);
        aporte_patronal = valor_hora * 0.1215
        $('#aporte_patronal_' + id).val(Math.round(aporte_patronal * 100) / 100);
        total_provisiones = prov_dt + prov_dc + prov_reserva + aporte_patronal;
        $('#total_provisiones_' + id).val(Math.round(total_provisiones * 100) / 100);
        valor_hora_provisiones = valor_hora + total_provisiones;
        $('#valor_hora_provisiones_' + id).val(Math.round(valor_hora_provisiones * 100) / 100);
    }
</script>