<div style="overflow-y: scroll; overflow-x: scroll">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr id="tr_cortes">
            <th class="text-center th_yura_green columna_fija_left_0">
                {{ $listado[0]['var']->planta->nombre }}
            </th>
            <th class="text-center th_yura_green">
                SOLIDOS
            </th>
            <th class="text-center th_yura_green">
                MIXTOS
            </th>
            <th class="text-center th_yura_green">
                TOTALES
            </th>
            <th class="text-center bg-yura_dark">
                CUARTO FRIO
                @if ($fecha == opDiasFecha('+', 2, hoy()))
                    <button type="button" class="btn btn-xs btn-yura_default pull-right"
                        title="Restar pedidos del dia anterior" onclick="restar_pedidos_dia_anterior()">
                        <i class="fa fa-fw fa-refresh"></i>
                    </button>
                @endif
            </th>
            <th class="text-center bg-yura_dark">
                SOBRANTE
            </th>
            <th class="text-center bg-yura_dark">
                COSECHA
            </th>
            <th class="text-center th_yura_green">
                SALDO
            </th>
            @if ($fecha < opDiasFecha('+', 2, hoy()))
                <th class="text-center th_yura_green">
                    CORTE
                </th>
            @endif
        </tr>
        @php
            $total_pedidos_solidos = 0;
            $total_armados_mixtos = 0;
            $total_inventario = 0;
            $total_cosecha = 0;
            $total_sobrante = 0;
        @endphp
        @foreach ($listado as $pos => $item)
            @php
                $solidos = $item['pedidos_solidos'] != null ? $item['pedidos_solidos'] : 0;
                $mixtos = $item['pedidos_mixtos'];
                $total_pedidos_solidos += $solidos;
                $total_armados_mixtos += $mixtos;
                $total_inventario += $item['cuarto_frio'];
                $total_sobrante += $item['sobrante'];
                $total_cosecha += $item['cosecha'];
                
                $bg_corte = '';
                if ($item['saldo'] < 0) {
                    if (count(explode('-', $item['corte_objetivo'])) > 1) {
                        $bg_corte = 'bg-yura_primary';
                    } else {
                        $bg_corte = 'bg-yura_warning';
                    }
                }
            @endphp
            <tr onmouseover="$(this).css('background-color','#ADD8E6')"
                onmouseleave="$(this).css('background-color','')">
                <th class="text-center bg-yura_dark columna_fija_left_0">
                    {{ $item['var']->nombre }}
                    <input type="hidden" class="pos_colores" value="{{ $pos }}">
                    <input type="hidden" id="solidos_{{ $pos }}" value="{{ $solidos }}">
                    <input type="hidden" id="solidos_anterior_{{ $pos }}"
                        value="{{ $item['pedidos_solidos_anterior'] }}">
                    <input type="hidden" id="mixtos_{{ $pos }}" value="{{ $mixtos }}">
                    <input type="hidden" id="mixtos_anterior_{{ $pos }}"
                        value="{{ $item['pedidos_mixtos_anterior'] }}">
                    <input type="hidden" id="cuarto_frio_{{ $pos }}" value="{{ $item['cuarto_frio'] }}">
                </th>
                <th class="text-center bg-yura_dark" id="celda_solidos_{{ $pos }}">
                    {{ number_format($solidos) }}
                </th>
                <th class="text-center bg-yura_dark" id="celda_mixtos_{{ $pos }}">
                    {{ number_format($mixtos) }}
                </th>
                <th class="text-center bg-yura_dark" id="celda_venta_{{ $pos }}">
                    {{ number_format($solidos + $mixtos) }}
                </th>
                <td class="text-center" style="border-color: #9d9d9d;" id="celda_cuarto_frio_{{ $pos }}">
                    {{ $item['cuarto_frio'] != '' ? number_format($item['cuarto_frio']) : '' }}
                </td>
                <td class="text-center" style="border-color: #9d9d9d;">
                    {{ number_format($item['sobrante']) }}
                </td>
                <td class="text-center" style="border-color: #9d9d9d;">
                    {{ number_format($item['cosecha']) }}
                </td>
                <th class="text-center bg-yura_dark" style="border-color: #9d9d9d;"
                    id="celda_saldo_{{ $pos }}">
                    {{ number_format($item['saldo']) }}
                </th>
                @if ($fecha < opDiasFecha('+', 2, hoy()))
                    <th class="text-center {{ $bg_corte }}" style="border-color: #9d9d9d;">
                        {{ count(explode('-', $item['corte_objetivo'])) > 1 ? explode('-', $item['corte_objetivo'])[1] : '' }}
                    </th>
                @endif
            </tr>
        @endforeach
        <tr>
            <th class="text-center th_yura_green columna_fija_left_0">
                TOTALES
            </th>
            <th class="text-center th_yura_green" id="celda_total_solidos">
                {{ number_format($total_pedidos_solidos) }}
            </th>
            <th class="text-center th_yura_green" id="celda_total_mixtos">
                {{ number_format($total_armados_mixtos) }}
            </th>
            <th class="text-center th_yura_green" id="celda_total_venta">
                {{ number_format($total_pedidos_solidos + $total_armados_mixtos) }}
            </th>
            <th class="text-center th_yura_green" id="celda_total_cuarto_frio">
                {{ number_format($total_inventario) }}
            </th>
            <th class="text-center th_yura_green">
                {{ number_format($total_sobrante) }}
            </th>
            <th class="text-center th_yura_green">
                {{ number_format($total_cosecha) }}
            </th>
            <th class="text-center th_yura_green" id="celda_total_saldo">
                {{ number_format($total_inventario + $total_cosecha - ($total_pedidos_solidos + $total_armados_mixtos)) }}
            </th>
            @if ($fecha < opDiasFecha('+', 2, hoy()))
                <th class="text-center th_yura_green">
                </th>
            @endif
        </tr>
    </table>
</div>
<legend style="font-size: 1em; margin-bottom: 5px" class="text-right">
    <b>Leyenda</b>
</legend>
<div style="font-size: 1em" class="text-right">
    <span class="badge" style="background-color: #ff8d00; color: black;">Faltante</span>
    <br>
    <span class="badge" style="background-color: #00b388; color: black;">Cosecha</span>
</div>

<script>
    function restar_pedidos_dia_anterior() {
        pos_colores = $('.pos_colores');
        total_venta = 0;
        total_cuarto_frio = 0;
        total_venta_anterior = 0;
        for (i = 0; i < pos_colores.length; i++) {
            pos = pos_colores[i].value;
            solidos = parseInt($('#solidos_' + pos).val());
            mixtos = parseInt($('#mixtos_' + pos).val());
            venta = solidos + mixtos;
            solidos_anterior = parseInt($('#solidos_anterior_' + pos).val());
            mixtos_anterior = parseInt($('#mixtos_anterior_' + pos).val());
            venta_anterior = solidos_anterior + mixtos_anterior;
            cuarto_frio = parseInt($('#cuarto_frio_' + pos).val());

            new_cuarto_frio = cuarto_frio - venta_anterior;
            new_saldo = new_cuarto_frio - venta;

            $('#celda_cuarto_frio_' + pos).html(new_cuarto_frio);
            $('#celda_saldo_' + pos).html(new_saldo);

            total_cuarto_frio += cuarto_frio;
            total_venta_anterior += venta_anterior;
            total_venta += venta;
        }

        new_cuarto_frio = total_cuarto_frio - total_venta_anterior;
        new_saldo = new_cuarto_frio - total_venta;

        $('#celda_total_cuarto_frio').html(new_cuarto_frio);
        $('#celda_total_saldo').html(new_saldo);
    }
</script>
