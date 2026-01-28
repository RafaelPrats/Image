<legend class="text-center" style="font-size: 1.2em; margin-bottom: 0">
    <b>Orden Fija #{{ $pedido->orden_fija != '' ? $pedido->orden_fija : 'UNIFICADA' }}</b>
</legend>
<div style="overflow-y: scroll; max-height: 500px">
    @php
        $pos_dia = transformDiaPhp(date('w', strtotime($pedido->fecha_pedido)));
    @endphp
    <table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%">
        <tr style="position: sticky;">
            <th class="text-center th_yura_green">
                <input type="checkbox" id="check_fecha_all"
                    onchange="$('.check_fecha').prop('checked', $(this).prop('checked'))">
            </th>
            <th class="text-center th_yura_green">
                <label for="check_fecha_all" class="mouse-hand">
                    Todas las fechas
                </label>
            </th>
            <th class="text-center th_yura_green">
                <select id="select_all_fechas" style="width: 100%" class="th_yura_green" onchange="select_all_dia()">
                    <option value="0" {{ $pos_dia == 0 ? 'selected' : '' }}>Lunes</option>
                    <option value="1" {{ $pos_dia == 1 ? 'selected' : '' }}>Martes</option>
                    <option value="2" {{ $pos_dia == 2 ? 'selected' : '' }}>Miercoles</option>
                    <option value="3" {{ $pos_dia == 3 ? 'selected' : '' }}>Jueves</option>
                    <option value="4" {{ $pos_dia == 4 ? 'selected' : '' }}>Viernes</option>
                    <option value="5" {{ $pos_dia == 5 ? 'selected' : '' }}>Sabado</option>
                    <option value="6" {{ $pos_dia == 6 ? 'selected' : '' }}>Domingo</option>
                </select>
            </th>
        </tr>
        @php
            $fecha_actual = $pedido->fecha_pedido;
        @endphp
        @foreach ($ordenes as $pos => $o)
            @php
                $pos_dia = transformDiaPhp(date('w', strtotime($o->fecha_pedido)));
            @endphp
            <tr>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="checkbox" id="check_fecha_{{ $o->id_pedido }}" class="check_fecha"
                        value="{{ $o->id_pedido }}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <label for="check_fecha_{{ $o->id_pedido }}" class="mouse-hand">
                        {{ getDias(TP_ABREVIADO, FR_ARREGLO)[$pos_dia] }}
                        {{ convertDateToText($o->fecha_pedido) }}
                    </label>
                    @if ($pos > 0)
                        <sup>+{{ difFechas($o->fecha_pedido, $fecha_actual)->days }}</sup>
                    @endif
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <select id="select_fecha_{{ $o->id_pedido }}" style="width: 100%; height: 28px;">
                        <option value="0" {{ $pos_dia == 0 ? 'selected' : '' }}>Lunes</option>
                        <option value="1" {{ $pos_dia == 1 ? 'selected' : '' }}>Martes</option>
                        <option value="2" {{ $pos_dia == 2 ? 'selected' : '' }}>Miercoles</option>
                        <option value="3" {{ $pos_dia == 3 ? 'selected' : '' }}>Jueves</option>
                        <option value="4" {{ $pos_dia == 4 ? 'selected' : '' }}>Viernes</option>
                        <option value="5" {{ $pos_dia == 5 ? 'selected' : '' }}>Sabado</option>
                        <option value="6" {{ $pos_dia == 6 ? 'selected' : '' }}>Domingo</option>
                    </select>
                </th>
            </tr>
            @php
                $fecha_actual = $o->fecha_pedido;
            @endphp
        @endforeach
    </table>
</div>

<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary" onclick="store_mover_fechas('{{ $pedido->id_pedido }}')">
        <i class="fa fa-fw fa-check"></i> GRABAR CAMBIOS
    </button>
</div>

<script>
    function select_all_dia() {
        all = $('#select_all_fechas').val();
        check_fecha = $('.check_fecha');
        for (i = 0; i < check_fecha.length; i++) {
            if ($('#' + check_fecha[i].id).prop('checked') == true) {
                id_ped = check_fecha[i].value;
                $('#select_fecha_' + id_ped).val(all);
            }
        }
    }

    function store_mover_fechas(pedido) {
        check_fecha = $('.check_fecha');
        data = [];
        for (i = 0; i < check_fecha.length; i++) {
            if ($('#' + check_fecha[i].id).prop('checked') == true) {
                id_ped = check_fecha[i].value;
                dia = $('#select_fecha_' + id_ped).val();
                data.push({
                    id_ped: id_ped,
                    dia: dia,
                });
            }
        }
        datos = {
            _token: '{{ csrf_token() }}',
            data: JSON.stringify(data),
        }
        post_jquery_m('{{ url('pedidos/store_mover_fechas') }}', datos, function(retorno) {
            cerrar_modals();
            mover_fecha_orden_fija(pedido);
            listar_resumen_pedidos(
                document.getElementById('fecha_pedidos_search').value,
                true,
                document.getElementById('id_configuracion_pedido').value,
                document.getElementById('id_cliente').value
            );
        });
    }
</script>
