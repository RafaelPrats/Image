@if (count($marcaciones) > 0)
    <legend style="font-size: 1.1em; margin-bottom: 5px" class="text-center">
        Seleccione la MARCACION para los <b>{{ $armar }}</b> ramos de <b>{{ $variedad->nombre }}</b>
        <br>
        <b>{{ $presentacion->nombre }}</b>, <b>{{ $longitud_ramo }}cm</b>, <b>{{ $peso_ramo }}gr</b>, <b>{{ $tallos_x_ramo }}</b> tallos x ramo
    </legend>
    <table class="table table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="mouse-hand" onmouseover="$(this).addClass('bg-yura_dark')"
            onmouseleave="$(this).removeClass('bg-yura_dark')" style="background-color: rgb(255, 212, 192)"
            onclick="store_armar_row('{{ $pos_comb }}', '', '')">
            <th class="text-center" style="border-color: #9d9d9d; width: 20%;">
                <em>NINGUNA:</em>
                <i class="fa fa-fw fa-arrow-right pull-right"></i>
            </th>
            <th class="text-center" style="border-color: #9d9d9d;" colspan="4">
                NINGUNA
            </th>
        </tr>
        @foreach ($listado as $pos => $m)
            @if ($m['pedidos'] > 0)
                <tr class="mouse-hand" onmouseover="$(this).addClass('bg-yura_dark')"
                    onmouseleave="$(this).removeClass('bg-yura_dark')"
                    onclick="store_armar_row('{{ $pos_comb }}', '{{ $m['marcacion']->id_dato_exportacion }}', '{{ $m['marcacion']->marcacion }}')">
                    <th class="text-center" style="border-color: #9d9d9d; width: 20%;">
                        <em>{{ $m['marcacion']->nombre }}:</em>
                        <i class="fa fa-fw fa-arrow-right pull-right"></i>
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d;">
                        {{ $m['marcacion']->marcacion }}
                    </th>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        Pedidos
                        <br>
                        <b>{{ $m['pedidos'] }}</b>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        @php
                            $saldo = $m['pedidos'] - $m['armados'];
                        @endphp
                        @if ($saldo > 0)
                            Por Armar
                            <br>
                            <b>{{ $saldo }}</b>
                        @else
                            Sobrantes
                            <br>
                            <b>{{ abs($saldo) }}</b>
                        @endif
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        Inventario
                        <br>
                        <b>{{ $m['armados'] }}</b>
                    </td>
                </tr>
            @endif
        @endforeach
    </table>
@else
    <div class="alert alert-info text-center">
        <h3>
            GRABANDO RAMOS SIN <b>UPC</b>
        </h3>
    </div>
    <script>
        $.LoadingOverlay('show');
        store_armar_row('{{ $pos_comb }}', '', '');
        $.LoadingOverlay('hide');
        setTimeout(() => {
            cerrar_modals();
        }, 1500);
    </script>
@endif
