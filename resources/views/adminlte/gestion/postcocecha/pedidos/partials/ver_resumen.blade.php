<div style="overflow-x: scroll">
    <table style="width: 100%; font-size: 0.9em">
        <tr>
            <td style="vertical-align: top; width: 33%; min-width: 420px" class="padding_lateral_5">
                <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
                    <tr>
                        <th class="text-center th_yura_green" colspan="2">
                            VARIEDAD-LONGNITUD
                        </th>
                        <th class="text-center th_yura_green padding_lateral_5">
                            TALLOS
                        </th>
                        <th class="text-center th_yura_green padding_lateral_5">
                            RAMOS
                        </th>
                        <th class="text-center th_yura_green padding_lateral_5">
                            MONTO
                        </th>
                    </tr>
                    @php
                        $total_tallos = 0;
                        $total_ramos = 0;
                        $total_dinero = 0;
                    @endphp
                    @foreach ($resumen_variedades as $item)
                        @php
                            $total_tallos += $item['tallos'];
                            $total_ramos += $item['ramos'];
                            $total_dinero += $item['dinero'];
                        @endphp
                        <tr onmouseover="$(this).addClass('bg-yura_dark')"
                            onmouseleave="$(this).removeClass('bg-yura_dark')">
                            <th style="border-color: #9d9d9d;">
                                {{ $item['variedad']->planta->nombre }}
                            </th>
                            <th style="border-color: #9d9d9d; width: 25%">
                                {{ $item['variedad']->nombre }} {{ $item['longitud'] }}cm
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ number_format($item['tallos']) }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ number_format($item['ramos']) }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d">
                                ${{ number_format($item['dinero'], 2) }}
                            </th>
                        </tr>
                    @endforeach
                    <tr>
                        <th class="text-center th_yura_green" colspan="2">
                            TOTALES
                        </th>
                        <th class="text-center th_yura_green padding_lateral_5">
                            {{ number_format($total_tallos) }}
                        </th>
                        <th class="text-center th_yura_green padding_lateral_5">
                            {{ number_format($total_ramos) }}
                        </th>
                        <th class="text-center th_yura_green padding_lateral_5">
                            ${{ number_format($total_dinero, 2) }}
                        </th>
                    </tr>
                </table>
            </td>
            <td style="vertical-align: top; width: 33%; min-width: 420px" class="padding_lateral_5">
                <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
                    <tr>
                        <th class="text-center th_yura_green">
                            PRESENTACION
                        </th>
                        <th class="text-center th_yura_green padding_lateral_5">
                            TALLOS
                        </th>
                        <th class="text-center th_yura_green padding_lateral_5">
                            RAMOS
                        </th>
                        <th class="text-center th_yura_green padding_lateral_5">
                            MONTO
                        </th>
                    </tr>
                    @php
                        $total_tallos = 0;
                        $total_ramos = 0;
                        $total_dinero = 0;
                    @endphp
                    @foreach ($resumen_presentaciones as $item)
                        @php
                            $total_tallos += $item['tallos'];
                            $total_ramos += $item['ramos'];
                            $total_dinero += $item['dinero'];
                        @endphp
                        <tr onmouseover="$(this).addClass('bg-yura_dark')"
                            onmouseleave="$(this).removeClass('bg-yura_dark')">
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ $item['empaque']->nombre }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ number_format($item['tallos']) }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ number_format($item['ramos']) }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d">
                                ${{ number_format($item['dinero'], 2) }}
                            </th>
                        </tr>
                    @endforeach
                    <tr>
                        <th class="text-center th_yura_green">
                            TOTALES
                        </th>
                        <th class="text-center th_yura_green padding_lateral_5">
                            {{ number_format($total_tallos) }}
                        </th>
                        <th class="text-center th_yura_green padding_lateral_5">
                            {{ number_format($total_ramos) }}
                        </th>
                        <th class="text-center th_yura_green padding_lateral_5">
                            ${{ number_format($total_dinero, 2) }}
                        </th>
                    </tr>
                </table>
            </td>
            <td style="vertical-align: top; width: 33%; min-width: 250px" class="padding_lateral_5">
                <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
                    <tr>
                        <th class="text-center th_yura_green" colspan="2">
                            PIEZAS
                        </th>
                        <th class="text-center th_yura_green padding_lateral_5">
                            FULL
                        </th>
                    </tr>
                    @php
                        $total_piezas = 0;
                        $total_full = 0;
                    @endphp
                    @foreach ($resumen_cajas as $item)
                        @php
                            $total_piezas += $item['cantidad'];
                            $total_full += $item['cantidad'] * $item['tipo_caja'];
                            
                            switch ($item['tipo_caja']) {
                                case 0.125:
                                    $tipo_caja = 'CAJAS OCTAVO';
                                    break;
                                case 0.25:
                                    $tipo_caja = 'CAJAS CUARTO';
                                    break;
                                case 0.5:
                                    $tipo_caja = 'CAJAS TABACO';
                                    break;
                            
                                default:
                                    $tipo_caja = 'CAJAS FULL';
                                    break;
                            }
                        @endphp
                        <tr onmouseover="$(this).addClass('bg-yura_dark')"
                            onmouseleave="$(this).removeClass('bg-yura_dark')">
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ $tipo_caja }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ number_format($item['cantidad']) }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ number_format($item['cantidad'] * $item['tipo_caja'], 3) }}
                            </th>
                        </tr>
                    @endforeach
                    <tr>
                        <th class="text-center th_yura_green">
                            TOTALES
                        </th>
                        <th class="text-center th_yura_green padding_lateral_5">
                            {{ number_format($total_piezas) }}
                        </th>
                        <th class="text-center th_yura_green padding_lateral_5">
                            {{ number_format($total_full, 3) }}
                        </th>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
