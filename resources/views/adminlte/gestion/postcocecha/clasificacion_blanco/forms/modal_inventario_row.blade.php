<legend style="font-size: 1.1em; margin-bottom: 5px" class="text-center">
    Inventario actual de <b>{{ $variedad->planta->nombre }}-{{ $variedad->nombre }}</b>
    <br>
    <b>{{ $presentacion->nombre }}</b>, <b>{{ $longitud_ramo }}cm</b>, <b>{{ $tallos_x_ramo }}</b> tallos x ramo
</legend>
<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="text-center th_yura_green">
            Fecha y Hora
        </th>
        <th class="text-center th_yura_green" style="width: 60px">
            Ramos
        </th>
        <th class="text-center th_yura_green">
            Marcacion
        </th>
        <th class="text-center th_yura_green" style="width: 90px">
            Dispo. Actual
        </th>
    </tr>
    @php
        $total_ingreso = 0;
        $total_disponibles = 0;
    @endphp
    @foreach ($listado as $pos => $item)
        @php
            $total_ingreso += $item->cantidad;
            $total_disponibles += $item->disponibles;
        @endphp
        <tr>
            <th class="text-center" style="border-color: #9d9d9d; background-color: #eeeeee">
                {{ $item->fecha_registro }}
            </th>
            <th class="text-center" style="border-color: #9d9d9d; background-color: #eeeeee">
                {{ $item->cantidad }}
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="text"
                    style="width: 100%; background-color: {{ !in_array($item->valor_marcacion, $array_marcaciones) ? 'yellow' : '' }}"
                    value="{{ $item->valor_marcacion }}"
                    onchange="update_marcacion_inventario('{{ $item->id_inventario_frio }}', $(this).val())">
            </th>
            <th class="text-center" style="border-color: #9d9d9d; background-color: #eeeeee">
                {{ $item->disponibles }}
            </th>
        </tr>
    @endforeach
    <tr>
        <th class="text-center th_yura_green">
            TOTALES
        </th>
        <th class="text-center th_yura_green">
            {{ number_format($total_ingreso) }}
        </th>
        <th class="text-center th_yura_green">
        </th>
        <th class="text-center th_yura_green">
            {{ number_format($total_disponibles) }}
        </th>
    </tr>
    {{-- MARCACIONES RESUMEN --}}
    <tr>
        <th class="padding_lateral_5 text-right bg-yura_dark">
            MARCACIONES de PEDIDOS
        </th>
        <th class="text-center bg-yura_dark">
            PEDIDOS
        </th>
        <th class="text-center bg-yura_dark">
        </th>
        <th class="text-center bg-yura_dark">
            INVENTARIO
        </th>
    </tr>
    @php
        $total_pedidos = 0;
        $total_armados = 0;
    @endphp
    @foreach ($listado_marcaciones as $mar)
        @php
            $total_pedidos += $mar['pedidos'];
            $total_armados += $mar['armados'];
        @endphp
        <tr>
            <th style="border-color: #9d9d9d; background-color: #eeeeee">
                <input type="text" readonly value="{{ $mar['marcacion']->marcacion }}" style="width: 100%">
            </th>
            <th class="text-center" style="border-color: #9d9d9d; background-color: #eeeeee">
                {{ $mar['pedidos'] }}
            </th>
            <th class="text-center" style="border-color: #9d9d9d; background-color: #eeeeee">
                @php
                    $saldo = $mar['pedidos'] - $mar['armados'];
                @endphp
                @if ($saldo > 0)
                    <span class="btn btn-xs btn-yura_danger">
                        {{ $saldo }}
                    </span>
                @else
                    <span class="btn btn-xs btn-yura_primary">
                        {{ abs($saldo) }}
                    </span>
                @endif
            </th>
            <th class="text-center" style="border-color: #9d9d9d;">
                {{ $mar['armados'] }}
            </th>
        </tr>
    @endforeach
    <tr>
        <th class="padding_lateral_5 text-right bg-yura_dark">
            TOTAL PEDIDO
        </th>
        <th class="text-center bg-yura_dark">
            {{ number_format($total_pedidos) }}
        </th>
        <th class="text-center bg-yura_dark">
        </th>
        <th class="text-center bg-yura_dark">
            {{ number_format($total_armados) }}
        </th>
    </tr>
</table>

<script>
    function update_marcacion_inventario(id, valor) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
            valor: valor
        };
        modal_quest('modal_quest_update_marcacion_inventario', '<div class="alert alert-info text-center">' +
            '¿Está seguro de <strong>MODIFICAR</strong> la marcacion?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de alerta', true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                post_jquery_m('{{ url('clasificacion_blanco/update_marcacion_inventario') }}', datos,
                    function() {});
            });
    }
</script>
