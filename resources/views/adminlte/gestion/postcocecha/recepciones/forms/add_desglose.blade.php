<table class="table-bordered" width="100%" style="border: 1px solid #9d9d9d;" id="tabla_desglose">
    <tr>
        <th class="text-center bg-yura_dark">
            Módulo
        </th>
        <th class="text-center bg-yura_dark">
            Variedad
        </th>
        <th class="text-center bg-yura_dark" style="width: 90px">
            Cantidad Mallas
        </th>
        <th class="text-center bg-yura_dark" style="width: 90px">
            Tallos x Mallas
        </th>
        <th class="text-center bg-yura_dark" style="width: 90px">
            <button type="button" class="btn btn-yura_default btn-xs" onclick="add_row_desglose()">
                <i class="fa fa-fw fa-plus"></i>
            </button>
        </th>
    </tr>
    <tr>
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="id_modulo_desglose" style="width: 100%; height: 26px;" required>
                <option value="">Default</option>
            </select>
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="id_variedad_desglose" style="width: 100%; height: 26px;" required>
                @foreach ($variedades as $item)
                    <option value="{{ $item->id_variedad }}">
                        {{ $item->nombre }}
                    </option>
                @endforeach
            </select>
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="number" id="cantidad_mallas_desglose" required style="width: 100%" value=""
                class="text-center" onkeypress="return isNumber(event)">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="number" id="tallos_x_malla_desglose" required style="width: 100%;" value=""
                class="text-center" onkeypress="return isNumber(event)">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
        </th>
    </tr>
</table>
<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary" title="Guardar" onclick="store_recepcion()">
        <i class="fa fa-fw fa-save"></i> GRABAR COSECHA
    </button>
</div>

<script>
    num_row = 1;

    function add_row_desglose() {
        num_row++;
        html = '<tr id="row_desglose_' + num_row + '">';
        html += '<td class="text-center" style="border-color: #9d9d9d">';
        html += '<select id="id_modulo_desglose_' + num_row + '" style="width: 100%; height: 26px;" required>';
        html += $('#id_modulo_desglose').html();
        html += '</select>';
        html += '</td>';
        html += '<td class="text-center" style="border-color: #9d9d9d">';
        html += '<select id="id_variedad_desglose_' + num_row + '" style="width: 100%; height: 26px;" required>';
        html += $('#id_variedad_desglose').html();
        html += '</select>';
        html += '</td>';
        html += '<td class="text-center" style="border-color: #9d9d9d">';
        html += '<input type="number" id="cantidad_mallas_desglose_' + num_row +
            '" required style="width: 100%" value="" class="text-center" onkeypress="return isNumber(event)">';
        html += '</td>';
        html += '<td class="text-center" style="border-color: #9d9d9d">';
        html += '<input type="number" id="tallos_x_malla_desglose_' + num_row +
            '" required style="width: 100%;" value="" class="text-center" onkeypress="return isNumber(event)">';
        html += '</td>';
        html += '<td class="text-center" style="border-color: #9d9d9d">';
        html += '<button type="button" class="btn btn-yura_danger btn-xs" onclick="$(\'#row_desglose_' + num_row +
            '\').remove()">';
        html += '<i class="fa fa-fw fa-close"></i>';
        html += '</button>';
        html += '</td>';
        html += '</tr>';

        $('#tabla_desglose').append(html);
    }

    function store_recepcion() {
        data = [];
        for (i = 1; i <= num_row; i++) {
            if ($('#id_modulo_desglose_' + i).length > 0) {
                if ($('#cantidad_mallas_desglose_' + i).val() > 0 && $('#tallos_x_malla_desglose_' + i).val() > 0)
                    data.push({
                        id_modulo: $('#id_modulo_desglose_' + i).val(),
                        id_variedad: $('#id_variedad_desglose_' + i).val(),
                        cantidad_mallas: $('#cantidad_mallas_desglose_' + i).val(),
                        tallos_x_malla: $('#tallos_x_malla_desglose_' + i).val(),
                    });
            }
        }
        modal_quest('modal_quest_store_recepcion',
            '<div class="alert alert-info text-center">¿Está seguro de guardar la recepción?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de alerta', true, false,
            '{{ isPC() ? '45%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    data: JSON.stringify(data),
                    fecha: $('#filtro_fecha').val(),
                }
                post_jquery_m('{{ url('recepcion/store_recepcion') }}', datos, function() {
                    cerrar_modals();
                    listar_reporte();
                });
            });
    }
</script>
