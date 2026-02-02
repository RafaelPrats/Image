<legend class="text-center" style="margin-bottom: 5px; font-size: 1.3em">
    <b>NUEVAS</b> Especificaciones para cliente <b>{{ $cliente->detalle()->nombre }}</b>
</legend>
<input type="hidden" id="cliente_selected" value="{{ $cliente->id_cliente }}">
<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_add">
    <tr>
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
        <th class="text-center th_yura_green" style="width: 80px">
            LONGITUD
        </th>
        <th class="text-center th_yura_green" style="width: 80px">
            PESO
        </th>
        <th class="text-center th_yura_green">
            <button type="button" class="btn btn-yura_default btn-xs" onclick="add_row()">
                <i class="fa fa-fw fa-plus"></i>
            </button>
        </th>
    </tr>
    <tr id="add_row_1">
        <td class="text-center" style="border-color: #9d9d9d">
            <select id="add_planta_1" style="width: 100%; height: 26px;"
                onchange="select_planta($(this).val(), 'add_variedad_1', 'td_cargar_variedades_1', '<option value=T>Todos</option>', '')">
                <option value="">Seleccione</option>
                @foreach ($plantas as $p)
                    <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                @endforeach
            </select>
        </td>
        <td class="text-center" style="border-color: #9d9d9d" id="td_cargar_variedades_1">
            <select id="add_variedad_1" style="width: 100%; height: 26px;">
                <option value="">Seleccione una Variedad</option>
            </select>
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <select id="add_caja_1" style="width: 100%; height: 26px;">
                <option value="">Seleccione</option>
                @foreach ($cajas as $c)
                    <option value="{{ $c->id_empaque }}">{{ explode('|', $c->nombre)[0] }}</option>
                @endforeach
            </select>
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%" id="add_ramos_x_caja_1" class="text-center">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <select id="add_presentacion_1" style="width: 100%; height: 26px;">
                <option value="">Seleccione</option>
                @foreach ($presentaciones as $p)
                    <option value="{{ $p->id_empaque }}">{{ $p->nombre }}</option>
                @endforeach
            </select>
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%" id="add_tallos_x_ramos_1" class="text-center">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%" id="add_longitud_1" class="text-center">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%" id="add_peso_1" class="text-center">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
        </td>
    </tr>
</table>

<div class="text-center" style="margin-top: 5px">
    <div class="btn-group">
        <button type="button" class="btn btn-yura_primary" onclick="store_especificaciones()">
            <i class="fa fa-fw fa-save"></i> GRABAR ESPECIFICACIONES
        </button>
    </div>
</div>

<script>
    row = 1;

    function add_row() {
        row++;
        parametros = [
            "'add_variedad_" + row + "'",
            "'td_cargar_variedades_" + row + "'",
            "'<option value=T>Todos</option>'",
            "''",
        ];
        html_select_planta = $('#add_planta_1').html();
        html_select_caja = $('#add_caja_1').html();
        html_select_presentacion = $('#add_presentacion_1').html();
        $('#table_add').append('<tr id="add_row_' + row + '">' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<select id="add_planta_' + row + '" style="width: 100%; height: 26px;"' +
            'onchange="select_planta($(this).val(), ' + parametros[0] + ', ' + parametros[1] + ', ' + parametros[
                2] + ', ' + parametros[3] + ')">' +
            html_select_planta +
            '</select>' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d" id="td_cargar_variedades_' + row + '">' +
            '<select id="add_variedad_' + row + '" style="width: 100%; height: 26px;">' +
            '<option value="">Seleccione una Variedad</option>' +
            '</select>' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<select id="add_caja_' + row + '" style="width: 100%; height: 26px;">' +
            html_select_caja +
            '</select>' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%" id="add_ramos_x_caja_' + row + '" class="text-center">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<select id="add_presentacion_' + row + '" style="width: 100%; height: 26px;">' +
            html_select_presentacion +
            '</select>' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%" id="add_tallos_x_ramos_' + row + '" class="text-center">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%" id="add_longitud_' + row + '" class="text-center">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%" id="add_peso_' + row + '" class="text-center">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<button type="button" class="btn btn-yura_danger btn-xs" onclick="del_row(' + row + ')">' +
            '<i class="fa fa-fw fa-times"></i>' +
            '</button>' +
            '</td>' +
            '</tr>');
    }

    function del_row(i) {
        $('#add_row_' + i).remove();
    }

    function store_especificaciones() {
        data = [];
        for (i = 1; i <= row; i++) {
            if ($('#add_row_' + i).length) { // existe el add_row_1
                planta = $('#add_planta_' + i).val();
                variedad = $('#add_variedad_' + i).val();
                caja = $('#add_caja_' + i).val();
                ramos_x_caja = $('#add_ramos_x_caja_' + i).val();
                presentacion = $('#add_presentacion_' + i).val();
                tallos_x_ramos = $('#add_tallos_x_ramos_' + i).val();
                longitud = $('#add_longitud_' + i).val();
                peso = $('#add_peso_' + i).val();
                if (planta != '' && caja != '' && ramos_x_caja > 0 && presentacion != '' && tallos_x_ramos > 0 &&
                    longitud >= 0) {
                    data.push({
                        planta: planta,
                        variedad: variedad,
                        caja: caja,
                        ramos_x_caja: ramos_x_caja,
                        presentacion: presentacion,
                        tallos_x_ramos: tallos_x_ramos,
                        longitud: longitud,
                        peso: peso,
                    });
                }
            }
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                data: JSON.stringify(data),
                cliente: $('#cliente_selected').val(),
            }
            post_jquery_m('{{ url('especificaciones/store_especificaciones') }}', datos, function() {
                listar_reporte();
            })
        }
    }
</script>
