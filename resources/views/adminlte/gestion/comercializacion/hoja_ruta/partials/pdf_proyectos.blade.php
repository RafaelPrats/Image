@php
    $hoja_ruta = $data['hoja_ruta'];
    $conductor = $hoja_ruta->conductor;
@endphp

<body>
    <table class="borde_1"
        style="width: 1080px; position: relative; left: -25px; right: 0px; top: -30px; font-size: 10px;">
        <tr>
            <td class="borde_1 padding_5" colspan="2">
                {{ $hoja_ruta->fecha }}
            </td>
            <td class="borde_1 padding_5" colspan="13">
                CONDUCTOR: Sr. {{ $conductor->nombre }}
            </td>
        </tr>
        <tr>
            <th class="borde_1 padding_5" style="width: 10px">
                N°
            </th>
            <th class="borde_1 padding_5">
                CARGERAS
            </th>
            <th class="borde_1 padding_5">
                CLIENTE
            </th>
            <th class="borde_1 padding_5">
                CONSIGNATARIO
            </th>
            <th class="borde_1 padding_5">
                MARCACION
            </th>
            <th class="borde_1 padding_5" style="width: 30px">
                PACKING
            </th>
            <th class="borde_1 padding_5" style="width: 30px">
                PIEZAS
            </th>
            <th class="borde_1 padding_5" style="width: 30px">
                FULLS
            </th>
            <th class="borde_1 padding_5" style="width: 10px">
                FB
            </th>
            <th class="borde_1 padding_5" style="width: 10px">
                HB
            </th>
            <th class="borde_1 padding_5" style="width: 10px">
                QB
            </th>
            <th class="borde_1 padding_5" style="width: 10px">
                EB
            </th>
            <th class="borde_1 padding_5" style="width: 10px">
                SB
            </th>
            <th class="borde_1 padding_5">
                GUIA MADRE
            </th>
            <th class="borde_1 padding_5">
                GUIA HIJA
            </th>
        </tr>
        @php
            $total_cajas = 0;
            $cajas_fulls = 0;
            $total_fb = 0;
            $total_hb = 0;
            $total_qb = 0;
            $total_eb = 0;
            $total_sb = 0;
        @endphp
        @foreach ($hoja_ruta->detalles as $pos => $det)
            @php
                $proyecto = $det->proyecto;
            @endphp
            @if ($proyecto != '')
                @php
                    $cliente = $proyecto->cliente;
                    $consignatario = $proyecto->consignatario;
                    $cajas = 0;
                    $fulls = 0;
                    $fb = 0;
                    $hb = 0;
                    $qb = 0;
                    $eb = 0;
                    $sb = 0;
                    foreach ($proyecto->cajas as $caja) {
                        $empaque = $caja->empaque;
                        $cajas += $caja->cantidad;
                        $total_cajas += $caja->cantidad;
                        if (explode('|', $empaque->nombre)[1] == 1) {
                            $fb += $caja->cantidad;
                            $total_fb += $caja->cantidad;
                            $cajas_fulls += $caja->cantidad * 1;
                            $fulls += $caja->cantidad * 1;
                        }
                        if (explode('|', $empaque->nombre)[1] == 0.5) {
                            $hb += $caja->cantidad;
                            $total_hb += $caja->cantidad;
                            $cajas_fulls += $caja->cantidad * 0.5;
                            $fulls += $caja->cantidad * 0.5;
                        }
                        if (explode('|', $empaque->nombre)[1] == 0.25) {
                            $qb += $caja->cantidad;
                            $total_qb += $caja->cantidad;
                            $cajas_fulls += $caja->cantidad * 0.25;
                            $fulls += $caja->cantidad * 0.25;
                        }
                        if (explode('|', $empaque->nombre)[1] == 0.125) {
                            $eb += $caja->cantidad;
                            $total_eb += $caja->cantidad;
                            $cajas_fulls += $caja->cantidad * 0.125;
                            $fulls += $caja->cantidad * 0.125;
                        }
                        if (explode('|', $empaque->nombre)[1] == 0.0625) {
                            $sb += $caja->cantidad;
                            $total_sb += $caja->cantidad;
                            $cajas_fulls += $caja->cantidad * 0.0625;
                            $fulls += $caja->cantidad * 0.0625;
                        }
                    }
                @endphp
                <tr>
                    <td class="borde_1 padding_5 text-center">
                        {{ $det->orden }}
                    </td>
                    <td class="borde_1 padding_5 text-center">
                        {{ $proyecto->agencia_carga->nombre }}
                    </td>
                    <td class="borde_1 padding_5">
                        {{ $cliente->detalle()->nombre }}
                    </td>
                    <td class="borde_1 padding_5">
                        {{ $consignatario->nombre }}
                    </td>
                    <td class="borde_1 padding_5">
                        @foreach ($proyecto->getMarcaciones() as $pos_m => $val)
                            {{ $pos_m > 0 ? ' - ' : '' }}{{ $val }}
                        @endforeach
                    </td>
                    <td class="borde_1 padding_5">
                        {{ $proyecto->packing }}
                    </td>
                    <td class="borde_1 padding_5 text-center">
                        {{ $cajas }}
                    </td>
                    <td class="borde_1 padding_5 text-center">
                        {{ $fulls }}
                    </td>
                    <td class="borde_1 padding_5 text-center">
                        {{ $fb }}
                    </td>
                    <td class="borde_1 padding_5 text-center">
                        {{ $hb }}
                    </td>
                    <td class="borde_1 padding_5 text-center">
                        {{ $qb }}
                    </td>
                    <td class="borde_1 padding_5 text-center">
                        {{ $eb }}
                    </td>
                    <td class="borde_1 padding_5 text-center">
                        {{ $sb }}
                    </td>
                    <td class="borde_1 padding_5 text-center">
                        {{ $proyecto->guia_madre }}
                    </td>
                    <td class="borde_1 padding_5 text-center">
                        {{ $proyecto->guia_hija }}
                    </td>
                </tr>
            @endif
        @endforeach
        <tr>
            <th class="borde_1 padding_5 text-right" colspan="6">
                TOTALES
            </th>
            <th class="borde_1 padding_5 text-right">
                {{ $total_cajas }}
            </th>
            <th class="borde_1 padding_5 text-right">
                {{ $cajas_fulls }}
            </th>
            <th class="borde_1 padding_5 text-right">
                {{ $total_fb }}
            </th>
            <th class="borde_1 padding_5 text-right">
                {{ $total_hb }}
            </th>
            <th class="borde_1 padding_5 text-right">
                {{ $total_qb }}
            </th>
            <th class="borde_1 padding_5 text-right">
                {{ $total_eb }}
            </th>
            <th class="borde_1 padding_5 text-right">
                {{ $total_sb }}
            </th>
            <th class="borde_1 padding_5 text-right" colspan="2">
            </th>
        </tr>
    </table>
</body>

<style>
    body {
        font-family: 'Times New Roman', Times, serif;
    }

    .borde_1 {
        border: 1px solid #000;
        border-collapse: collapse;
    }

    .padding_5 {
        padding-left: 5px;
        padding-right: 5px;
    }
</style>
