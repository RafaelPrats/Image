<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_agregar">
    <tr>
        <th class="text-center th_yura_green">
            Fecha
        </th>
        <th class="text-center th_yura_green">
            Planta
        </th>
        <th class="text-center th_yura_green">
            Variedad
        </th>
        <th class="text-center th_yura_green">
            Presentacion
        </th>
        <th class="text-center th_yura_green">
            Tallos x Ramo
        </th>
        <th class="text-center th_yura_green">
            Longitud
        </th>
        <th class="text-center th_yura_green">
            Cantidad
        </th>
        <th class="text-center th_yura_green">
            Marcacion
        </th>
        <th class="text-center th_yura_green">
            Valor Marcacion
        </th>
        <th class="text-center th_yura_green" style="width: 30px">
            <button type="button" class="btn btn-xs btn-yura_default" title="Agregar" onclick="agregar_row()">
                <i class="fa fa-fw fa-plus"></i>
            </button>
        </th>
    </tr>
    <tr id="new_tr_1">
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="date" class="text-center" id="new_fecha_1" value="{{ hoy() }}" style="width: 100%">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="new_planta_1" style="width: 100%; height: 26px"
                onchange="select_planta($(this).val(), 'new_variedad_1', 'new_variedad_1', '<option value=>Seleccione</option>', '')">
                <option value="">Seleccione</option>
                @foreach ($plantas as $planta)
                    <option value="{{ $planta->id_planta }}">{{ $planta->nombre }}</option>
                @endforeach
            </select>
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="new_variedad_1" style="width: 100%; height: 26px">
            </select>
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="new_presentacion_1" style="width: 100%; height: 26px">
                <option value="">Seleccione</option>
                @foreach ($presentaciones as $presentacion)
                    <option value="{{ $presentacion->id_empaque }}">{{ $presentacion->nombre }}</option>
                @endforeach
            </select>
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="number" class="text-center" id="new_tallos_x_ramo_1" style="width: 100%">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="number" class="text-center" id="new_longitud_1" style="width: 100%">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="number" class="text-center" id="new_cantidad_1" style="width: 100%">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="new_dato_exportacion_1" style="width: 100%; height: 26px;">
                <option value="">Seleccione</option>
                @foreach ($datos_exportacion as $dato)
                    <option value="{{ $dato->id_dato_exportacion }}">{{ $dato->nombre }}</option>
                @endforeach
            </select>
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="text" class="text-center" id="new_valor_marcacion_1" style="width: 100%">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
        </th>
    </tr>
</table>

<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary" onclick="store_grabar_inventario()">
        <i class="fa fa-fw fa-save"></i> GRABAR INVENTARIO
    </button>
</div>

<script>
    num_row = 1;

    function agregar_row() {
        num_row++;
        fecha = $('#new_fecha_1').val();
        parametros = [
            "'new_variedad_" + num_row + "'",
            "'<option value=>Seleccione</option>'",
            "''"
        ];
        plantas = $('#new_planta_1').html();
        presentaciones = $('#new_presentacion_1').html();
        datos_exportacion = $('#new_dato_exportacion_1').html();
        nuevo_tr = '<tr id="new_tr_' + num_row + '">' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<input type="date" class="text-center" id="new_fecha_' + num_row +
            '" value="' + fecha + '" style="width: 100%">' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<select id="new_planta_' + num_row + '" style="width: 100%; height: 26px" ' +
            'onchange="select_planta($(this).val(), ' + parametros[0] + ', ' + parametros[0] + ', ' + parametros[1] +
            ', ' + parametros[2] + ')">' +
            plantas +
            '</select>' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<select id="new_variedad_' + num_row + '" style="width: 100%; height: 26px">' +
            '</select>' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<select id="new_presentacion_' + num_row + '" style="width: 100%; height: 26px">' +
            presentaciones +
            '</select>' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" class="text-center" id="new_tallos_x_ramo_' + num_row + '" style="width: 100%">' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" class="text-center" id="new_longitud_' + num_row + '" style="width: 100%">' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" class="text-center" id="new_cantidad_' + num_row + '" style="width: 100%">' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<select id="new_dato_exportacion_' + num_row + '" style="width: 100%; height: 26px;">' +
            datos_exportacion +
            '</select>' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<input type="text" class="text-center" id="new_valor_marcacion_' + num_row + '" style="width: 100%">' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<button type="button" class="btn btn-xs btn-yura_danger" title="Quitar" onclick="quitar_row(' + num_row +
            ')">' +
            '<i class="fa fa-fw fa-times"></i>' +
            '</button>' +
            '</th>' +
            '</tr>';
        $('#table_agregar').append(nuevo_tr);
    }

    function quitar_row(num) {
        $('#new_tr_' + num).remove();
    }

    function store_grabar_inventario() {
        data = [];
        for (i = 1; i <= num_row; i++) {
            if ($('#new_tr_' + i).length) {
                fecha = $('#new_fecha_' + i).val();
                planta = $('#new_planta_' + i).val();
                variedad = $('#new_variedad_' + i).val();
                presentacion = $('#new_presentacion_' + i).val();
                tallos_x_ramo = $('#new_tallos_x_ramo_' + i).val();
                longitud = $('#new_longitud_' + i).val();
                cantidad = $('#new_cantidad_' + i).val();
                dato_exportacion = $('#new_dato_exportacion_' + i).val();
                valor_marcacion = $('#new_valor_marcacion_' + i).val();

                if (fecha != '' && planta != '' && variedad != '' && presentacion != '' && cantidad != '' &&
                    tallos_x_ramo > 0 && longitud > 0) {
                    data.push({
                        fecha: fecha,
                        planta: planta,
                        variedad: variedad,
                        presentacion: presentacion,
                        tallos_x_ramo: tallos_x_ramo,
                        longitud: longitud,
                        cantidad: cantidad,
                        dato_exportacion: dato_exportacion,
                        valor_marcacion: valor_marcacion,
                    });
                }
            }
        }

        if (data.length > 0) {
            mensaje = {
                title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmacion',
                mensaje: '<div class="alert alert-info text-center" style="font-size: 16px">¿Está seguro de <b>GRABAR</b> el nuevo inventario?</div>',
            };
            modal_quest('modal_store_grabar_inventario', mensaje['mensaje'], mensaje['title'], true, false,
                '{{ isPC() ? '35%' : '' }}',
                function() {
                    datos = {
                        _token: '{{ csrf_token() }}',
                        data: JSON.stringify(data),
                    };
                    post_jquery_m('{{ url('inventario_cuarto_frio/store_grabar_inventario') }}', datos, function() {
                        cerrar_modals();
                        listar_reporte();
                    });
                });
        } else {
            alerta('<div class="alert alert-warning text-center">No hay datos para grabar</div>');
        }
    }
</script>
