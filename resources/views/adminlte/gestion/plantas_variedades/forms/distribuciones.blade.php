<input type="hidden" id="id_planta" value="{{ $planta->id_planta }}">
<table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
    <tr id="tr_encabezado">
        <th class="text-center th_yura_green">
            Variedades de {{ $planta->nombre }}
            <div class="btn-group pull-right">
                <button type="button" class="btn btn-xs btn-yura_dark" onclick="add_distribucion()">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
                <button type="button" class="btn btn-xs btn-yura_danger" onclick="delete_inputs()">
                    <i class="fa fa-fw fa-trash"></i>
                </button>
            </div>
        </th>
        @php
            $totales = [];
        @endphp
        @foreach ($longitudes as $pos => $l)
            @php
                $totales[] = 0;
            @endphp
            <th class="text-center">
                <input type="number" id="new_longitud_{{ $pos + 1 }}" placeholder="Longitud"
                    class="text-center bg-yura_dark input_del" style="width:100%" value="{{ $l->longitud }}">
            </th>
            <th class="text-center" style="border-right: 2px solid black">
                <select id="new_unidad_{{ $pos + 1 }}" style="width: 100%; height: 26px" class="bg-yura_dark">
                    @foreach ($unidades as $u)
                        <option value="{{ $u->id_unidad_medida }}"
                            {{ $u->id_unidad_medida == $l->longitud ? ' selected' : '' }}>
                            {{ $u->siglas }}
                        </option>
                    @endforeach
                </select>
            </th>
        @endforeach
    </tr>
    @foreach ($listado as $item)
        <tr id="tr_var_{{ $item['var']->siglas }}">
            <th class="text-center bg-yura_dark" style="border-color: #9d9d9d">
                <input type="hidden" class="ids_variedad" value="{{ $item['var']->siglas }}">
                {{ $item['var']->nombre }}
            </th>
            @foreach ($item['valores'] as $pos => $val)
                @php
                    $totales[$pos] += $val != '' ? $val->valor : 0;
                @endphp
                <td class="text-center" colspan="2" style="border-right: 2px solid black">
                    <input type="number" id="new_valor_{{ $pos + 1 }}_var_{{ $item['var']->siglas }}"
                        placeholder="Porcentaje" class="text-center input_del" style="width:100%" min="0"
                        value="{{ $val != '' ? $val->valor : '' }}" onchange="calcular_totales()">
                </td>
            @endforeach
        </tr>
    @endforeach
    <tr id="tr_total">
        <th class="text-center bg-yura_dark" style="border-color: #9d9d9d">
            TOTALES
        </th>
        @foreach ($totales as $pos => $val)
            <td class="text-center" colspan="2" style="border-right: 2px solid black">
                <input type="number" id="total_{{ $pos + 1 }}" class="text-center input_del bg-yura_dark"
                    style="width:100%" value="{{ $val }}" readonly>
            </td>
        @endforeach
    </tr>
</table>

<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary" onclick="store_distribucion()">
        <i class="fa fa-fw fa-save"></i> Guardar
    </button>
</div>

<script>
    var num_dist = {{ count($longitudes) }};

    function add_distribucion() {
        num_dist++;
        $('#tr_encabezado').append('<th class="text-center">' +
            '<input type="number" id="new_longitud_' + num_dist +
            '" placeholder="Longitud" class="text-center input_del bg-yura_dark" style="width:100%">' +
            '</th>' +
            '<th class="text-center" style="border-right: 2px solid black">' +
            '<select id="new_unidad_' + num_dist + '" style="width: 100%; height: 26px" class="bg-yura_dark">' +
            @foreach ($unidades as $u)
                '<option value="{{ $u->id_unidad_medida }}">{{ $u->siglas }}</option>' +
            @endforeach
            '</select>' +
            '</th>');

        ids_variedad = $('.ids_variedad');
        for (i = 0; i < ids_variedad.length; i++) {
            id_var = ids_variedad[i].value;

            $('#tr_var_' + id_var).append(
                '<td class="text-center input_del" colspan="2" style="border-right: 2px solid black">' +
                '<input type="number" min="0" id="new_valor_' + num_dist + '_var_' + id_var +
                '" placeholder="Porcentaje" class="text-center" style="width:100%" min="0"' +
                ' onchange="calcular_totales()">' +
                '</td>');
        }

        $('#tr_total').append('<th class="text-center" colspan="2" style="border-right: 2px solid black">' +
            '<input type="number" id="total_' + num_dist + '" class="text-center input_del bg-yura_dark" ' +
            'style = "width:100%" readonly> ' +
            '</th>');
    }

    function store_distribucion() {
        ids_variedad = $('.ids_variedad');
        data = [];
        for (n = 1; n <= num_dist; n++) {
            for (i = 0; i < ids_variedad.length; i++) {
                id_var = ids_variedad[i].value;
                if ($('#new_longitud_' + n).val() != '' && $('#new_valor_' + n + '_var_' + id_var).val() != '')
                    data.push({
                        var: id_var,
                        longitud: $('#new_longitud_' + n).val(),
                        unidad: $('#new_unidad_' + n).val(),
                        valor: $('#new_valor_' + n + '_var_' + id_var).val(),
                    });
            }
        }
        datos = {
            _token: '{{ csrf_token() }}',
            planta: $('#id_planta').val(),
            data: data
        };
        post_jquery_m('{{ url('plantas_variedades/store_distribucion') }}', datos, function() {
            cerrar_modals();
            distribuciones(datos['planta']);
        });
    }

    function delete_inputs() {
        $('.input_del').val('');
    }

    function calcular_totales() {
        ids_variedad = $('.ids_variedad');
        for (p = 1; p <= num_dist; p++) {
            total = 0;
            for (i = 0; i < ids_variedad.length; i++) {
                id_var = ids_variedad[i].value;
                valor = $('#new_valor_' + p + '_var_' + id_var).val();
                if (valor != '') {
                    total += parseInt(valor);
                }
            }
            $('#total_' + p).val(total);
        }
    }
</script>
