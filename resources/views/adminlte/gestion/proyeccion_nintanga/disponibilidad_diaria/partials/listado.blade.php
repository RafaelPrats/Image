<div style="overflow-y: scroll; overflow-x: scroll; max-height: 500px">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr id="tr_cortes" class="tr_fija_top_0">
            <th class="text-center th_yura_green" rowspan="2">
                <input type="checkbox" style="width: 100%"
                    onchange="$('.checkboxes').prop('checked', $(this).prop('checked'))">
            </th>
            <th class="text-center th_yura_green" style="width: 120px" rowspan="2">
                PLANTA
            </th>
            <th class="text-center th_yura_green" style="width: 120px" rowspan="2">
                COLOR
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                DISPONIBILIDAD TALLOS
            </th>
            <th class="text-center th_yura_green">
                TIPO CAJA
            </th>
            <th class="text-center th_yura_green">
                TALLOS X RAMO
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                RAMOS
            </th>
            <th class="text-center th_yura_green">
                RAMOS X CAJA
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                TOTAL CAJAS
            </th>
            <th class="text-center th_yura_green">
                PRECIO
            </th>
        </tr>
        <tr class="tr_fija_top_1">
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="text" class="text-center bg-yura_dark" style="width: 100%; text-transform: uppercase"
                    onchange="input_all('tipo_caja', $(this).val())" onkeyup="input_all('tipo_caja', $(this).val())">
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="number" class="text-center bg-yura_dark" style="width: 100%"
                    onchange="input_all('tallos_x_ramo', $(this).val())"
                    onkeyup="input_all('tallos_x_ramo', $(this).val())">
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="number" class="text-center bg-yura_dark" style="width: 100%"
                    onchange="input_all('ramos_x_caja', $(this).val())"
                    onkeyup="input_all('ramos_x_caja', $(this).val())">
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="number" class="text-center bg-yura_dark" style="width: 100%"
                    onchange="input_all('precio', $(this).val())" onkeyup="input_all('precio', $(this).val())">
            </th>
        </tr>
        @php
            $total_saldo = 0;
        @endphp
        @foreach ($listado as $pos_pta => $pta)
            @foreach ($pta['longitudes'] as $pos_long => $long)
                @php
                    $total_saldo_planta_longitud = 0;
                @endphp
                @foreach ($long['valores'] as $pos_val => $val)
                    @php
                        $total_saldo_planta_longitud += $val['saldo'];
                        $total_saldo += $val['saldo'];
                    @endphp
                    <tr>
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                            <input type="checkbox"
                                id="check_{{ $val['variedad']->id_variedad }}_{{ $long['longitud']->nombre }}"
                                class="checkboxes">
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                            <input type="hidden" class="ids"
                                value="{{ $val['variedad']->id_variedad }}_{{ $long['longitud']->nombre }}">
                            {{ $pta['planta']->nombre }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                            {{ $val['variedad']->nombre }} {{ $long['longitud']->nombre }}cm
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d;">
                            <input type="number" class="text-center tallos_x_ramo" style="width: 100%" readonly
                                id="saldo_{{ $val['variedad']->id_variedad }}_{{ $long['longitud']->nombre }}"
                                value="{{ $val['saldo'] }}" disabled>
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d;">
                            <input type="text" class="text-center tipo_caja"
                                style="width: 100%; text-transform: uppercase"
                                id="tipo_caja_{{ $val['variedad']->id_variedad }}_{{ $long['longitud']->nombre }}"
                                value="{{ $val['dispo'] != '' ? $val['dispo']->tipo_caja : '' }}">
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d;">
                            <input type="number" class="text-center tallos_x_ramo" style="width: 100%" min="1"
                                id="tallos_x_ramo_{{ $val['variedad']->id_variedad }}_{{ $long['longitud']->nombre }}"
                                onchange="calcular_tabla()" onkeyup="calcular_tabla()"
                                value="{{ $val['dispo'] != '' ? $val['dispo']->tallos_x_ramo : '' }}">
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d;">
                            <input type="number" class="text-center" readonly style="width: 100%" min="1"
                                disabled
                                id="ramos_{{ $val['variedad']->id_variedad }}_{{ $long['longitud']->nombre }}">
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d;">
                            <input type="number" class="text-center ramos_x_caja" style="width: 100%"
                                min="1"
                                id="ramos_x_caja_{{ $val['variedad']->id_variedad }}_{{ $long['longitud']->nombre }}"
                                value="{{ $val['dispo'] != '' ? $val['dispo']->ramos_x_caja : '' }}">
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d;">
                            <input type="number" class="text-center" readonly style="width: 100%" min="1"
                                disabled
                                id="cajas_{{ $val['variedad']->id_variedad }}_{{ $long['longitud']->nombre }}">
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d;">
                            <input type="number" class="text-center precio" style="width: 100%" min="0"
                                id="precio_{{ $val['variedad']->id_variedad }}_{{ $long['longitud']->nombre }}"
                                value="{{ $val['dispo'] != '' ? $val['dispo']->precio : '' }}">
                        </th>
                    </tr>
                @endforeach
                <tr>
                    <th class="text-center bg-yura_dark" colspan="3">
                        {{ $pta['planta']->nombre }} {{ $long['longitud']->nombre }}cm
                    </th>
                    <th class="text-center bg-yura_dark">
                        {{ number_format($total_saldo_planta_longitud) }}
                    </th>
                    <th class="text-center bg-yura_dark" colspan="6">
                    </th>
                </tr>
            @endforeach
        @endforeach
        <tr>
            <th class="text-center th_yura_green" colspan="3">
                TOTALES
            </th>
            <th class="text-center th_yura_green">
                {{ number_format($total_saldo) }}
            </th>
            <th class="text-center th_yura_green" colspan="6">
            </th>
        </tr>
    </table>
</div>

<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary" onclick="store_disponibilidad_diaria()">
        <i class="fa fa-fw fa-save"></i> Grabar
    </button>
</div>

<script>
    calcular_tabla();

    @if ($new_dispo)
        modal_quest('modal_quest_new_dispo', '<div class="alert alert-info text-center">' +
            'Aún no se han guardado los datos para esta fecha. <br>¿Desea guardar los datos usando el ultimo dia ingresado?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de alerta', true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                store_disponibilidad_diaria();
            });
    @endif

    function input_all(campo, valor) {
        ids = $('.ids');
        for (i = 0; i < ids.length; i++) {
            id = ids[i].value;
            if ($('#check_' + id).prop('checked') == true) {
                $('#' + campo + '_' + id).val(valor);
            }
        }
        calcular_tabla();
    }

    function calcular_tabla() {
        ids = $('.ids');
        for (i = 0; i < ids.length; i++) {
            id = ids[i].value;
            saldo = parseInt($('#saldo_' + id).val());
            tallos_x_ramo = parseInt($('#tallos_x_ramo_' + id).val());
            ramos = parseInt(saldo / tallos_x_ramo);
            $('#ramos_' + id).val(ramos);
            ramos_x_caja = parseInt($('#ramos_x_caja_' + id).val());
            cajas = parseInt(ramos / ramos_x_caja);
            $('#cajas_' + id).val(cajas);
        }
    }

    function store_disponibilidad_diaria() {
        ids = $('.ids');
        data = [];
        for (i = 0; i < ids.length; i++) {
            id = ids[i].value;
            saldo = parseInt($('#saldo_' + id).val());
            tallos_x_ramo = parseInt($('#tallos_x_ramo_' + id).val());
            ramos = parseInt($('#ramos_' + id).val());
            ramos_x_caja = parseInt($('#ramos_x_caja_' + id).val());
            tipo_caja = $('#tipo_caja_' + id).val();
            precio = parseFloat($('#precio_' + id).val());
            if (saldo > 0 && tallos_x_ramo > 0 && ramos_x_caja > 0 && tipo_caja != '' && precio > 0)
                data.push({
                    id: id,
                    saldo: saldo,
                    tallos_x_ramo: tallos_x_ramo,
                    ramos: ramos,
                    ramos_x_caja: ramos_x_caja,
                    tipo_caja: tipo_caja,
                    precio: precio,
                });
            else {
                alerta('<div class="alert alert-warning text-center">Faltan datos necesarios</div>');
                return false;
            }
        }
        datos = {
            _token: '{{ csrf_token() }}',
            data: JSON.stringify(data),
            fecha: $('#filtro_fecha').val(),
        }
        post_jquery_m('{{ url('disponibilidad_diaria/store_disponibilidad_diaria') }}', datos, function() {

        });
    }
</script>
