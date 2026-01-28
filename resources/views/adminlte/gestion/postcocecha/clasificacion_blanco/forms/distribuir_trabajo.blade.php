<legend style="margin-bottom: 0" class="text-center">
    Seleccione los trabajadores para la distribucion del trabajo siguiente
</legend>

<table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%" id="table_distribucion">
    <tr>
        <th class="text-center th_yura_green">
            Variedad
        </th>
        <th class="text-center th_yura_green">
            Color
        </th>
        <th class="text-center th_yura_green">
            Presentacion
        </th>
        <th class="text-center th_yura_green">
            Tallos
        </th>
        <th class="text-center th_yura_green">
            Longitud
        </th>
        <th class="text-center bg-yura_dark">
            Pedidos
        </th>
        <th class="text-center bg-yura_dark">
            Actuales
        </th>
        <th class="text-center bg-yura_dark" style="width: 60px">
            Saldo
        </th>
        <th class="text-center" style="border-color: #9d9d9d; width: 60px" rowspan="2">
            <button type="button" class="btn btn-xs btn-yura_dark" title="Agregar Trabajador"
                onclick="add_trabajador()">
                <i class="fa fa-fw fa-plus"></i>
            </button>
        </th>
    </tr>
    @php
        $color = getColorByNombre($variedad->nombre);
        $bg_color = $color != '' ? $color->fondo : '';
        $text_color = $color != '' ? $color->texto : '';
    @endphp
    <tr>
        <th class="text-center"
            style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}">
            {{ $planta->nombre }}
        </th>
        <th class="text-center"
            style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}">
            {{ $variedad->nombre }}
        </th>
        <th class="text-center"
            style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}">
            {{ $empaque->nombre }}
        </th>
        <th class="text-center"
            style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}">
            {{ $tallos_x_ramo }}
        </th>
        <th class="text-center"
            style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}">
            {{ $longitud }}cm
        </th>
        <th class="text-center"
            style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}">
            {{ $pedidos }}
        </th>
        <th class="text-center"
            style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}">
            {{ $actuales }}
        </th>
        <th class="text-center" style="background-color: white !important; border-color: #9d9d9d !important">
            @if ($saldo >= 0)
                <button type="button" class="btn btn-xs btn-yura_primary" title="Armados">
                    {{ $saldo }}
                </button>
            @else
                <button type="button" class="btn btn-xs btn-yura_danger" title="Por armar">
                    {{ number_format(substr($saldo, 1), 0) }}
                </button>
            @endif
        </th>
    </tr>
    <tr>
        <th class="text-right th_yura_green padding_lateral_5" colspan="3">
            Nombre de Clasificador
        </th>
        <th class="text-right th_yura_green padding_lateral_5" colspan="4">
            MARCACION
        </th>
        <th class="text-center th_yura_green padding_lateral_5">
            Cantidad
        </th>
        <th class="text-center th_yura_green padding_lateral_5">
            Opciones
        </th>
    </tr>
    @foreach ($distribuciones as $dist)
        <tr id="tr_dist_{{ $dist->id_distribucion_posco }}">
            <th class="text-right padding_lateral_5" colspan="3" style="border-color: #9d9d9d">
                {{ $dist->clasificador->nombre }}
            </th>
            <th class="text-right padding_lateral_5" colspan="4" style="border-color: #9d9d9d">
                {{ $dist->valor_marcacion }}
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                {{ $dist->cantidad }}
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_danger"
                        onclick="delete_distribucion('{{ $dist->id_distribucion_posco }}')">
                        <i class="fa fa-fw fa-trash"></i>
                    </button>
                </div>
            </th>
        </tr>
    @endforeach
</table>

<div id="div_btn_store_distribucion" class="text-center hidden" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary" onclick="store_distribucion()">
        <i class="fa fa-fw fa-save"></i> GRABAR DISTRIBUCION
    </button>
</div>

<select id="select_clasificadores" class="hidden">
    @foreach ($clasificadores as $t)
        <option value="{{ $t->id_clasificador }}">{{ $t->nombre }}</option>
    @endforeach
</select>
<select id="select_marcaciones" class="hidden">
    <option></option>
    @foreach ($marcaciones as $t)
        <option value="{{ $t->id_dato_exportacion }}">{{ $t->marcacion }}</option>
    @endforeach
</select>
<input type="hidden" id="num_distribuciones" value="0">
<input type="hidden" id="variedad_dist" value="{{ $variedad->id_variedad }}">
<input type="hidden" id="empaque_dist" value="{{ $empaque->id_empaque }}">
<input type="hidden" id="tallos_x_ramo_dist" value="{{ $tallos_x_ramo }}">
<input type="hidden" id="longitud_dist" value="{{ $longitud }}">
<input type="hidden" id="fecha_dist" value="{{ $fecha }}">
<input type="hidden" id="pos_comb_dist" value="{{ $pos_comb }}">

<script>
    function add_trabajador() {
        num_distribuciones = $('#num_distribuciones').val();
        select_clasificadores = $('#select_clasificadores');
        select_marcaciones = $('#select_marcaciones');
        num_distribuciones++;
        $('#table_distribucion').append('<tr>' +
            '<td colspan="3" style="border-color: #9d9d9d">' +
            '<select id="dist_clasificador_' + num_distribuciones +
            '" style="width: 100%; height: 26px" class="text-right">' +
            select_clasificadores.html() +
            '</select>' +
            '</td>' +
            '<td colspan="4" style="border-color: #9d9d9d">' +
            '<select id="dist_marcacion_' + num_distribuciones +
            '" style="width: 100%; height: 26px" class="text-right">' +
            select_marcaciones.html() +
            '</select>' +
            '</td>' +
            '<td style="border-color: #9d9d9d" colspan="2">' +
            '<input type="number" class="text-center" id="dist_cantidad_' + num_distribuciones +
            '" value="0" min="0" style="width: 100%">' +
            '</td>' +
            '</tr>');
        $('#num_distribuciones').val(num_distribuciones);
        $('#div_btn_store_distribucion').removeClass('hidden');
    }

    function store_distribucion() {
        num_distribuciones = $('#num_distribuciones').val();
        data = [];
        for (i = 1; i <= num_distribuciones; i++) {
            cantidad = $('#dist_cantidad_' + i).val();
            if (cantidad > 0) {
                data.push({
                    clasificador: $('#dist_clasificador_' + i).val(),
                    id_marcacion: $('#dist_marcacion_' + i).val(),
                    valor_marcacion: $("#dist_marcacion_" + i + " option:selected").text(),
                    cantidad: cantidad
                });
            }
        }
        datos = {
            _token: '{{ csrf_token() }}',
            variedad: $('#variedad_dist').val(),
            empaque: $('#empaque_dist').val(),
            tallos_x_ramo: $('#tallos_x_ramo_dist').val(),
            longitud: $('#longitud_dist').val(),
            fecha: $('#fecha_dist').val(),
            data: JSON.stringify(data),
        };
        post_jquery_m('{{ url('clasificacion_blanco/store_distribucion') }}', datos, function() {
            cerrar_modals();
            pos_comb = $('#pos_comb_dist').val();
            listar_combinaciones_row(pos_comb);
        });
    }

    function delete_distribucion(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
        }
        modal_quest('modal_quest_delete_distribucion', '<div class="alert alert-info text-center">' +
            '¿Está seguro de <strong>ELIMINAR</strong> el trabajo?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de alerta', true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                post_jquery_m('{{ url('clasificacion_blanco/delete_distribucion') }}', datos, function() {
                    $('#tr_dist_' + id).remove();
                    pos_comb = $('#pos_comb_dist').val();
                    listar_combinaciones_row(pos_comb);
                });
            });
    }
</script>
