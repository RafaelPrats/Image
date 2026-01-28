@php
    $nCaja = 1;
    $id_pedidoPrevio = '';
@endphp
<!DOCTYPE html>
<html>

<head>
    <style>
        @font-face {
            font-family: 'TriumVirateCondensed';
            font-style: normal;
            src: url('fonts/triumviratecondensed.ttf') format('truetype');
        }

        @font-face {
            font-family: 'TriumVirateCondensedBold';
            font-style: normal;
            src: url('fonts/triumviratecondensedBold.ttf') format('truetype');
        }

        @page {
            margin-top: 0;
            margin-right: 0;
            margin-bottom: 0;
            margin-left: 0;
        }

        .barcode div {
            margin: 0 auto;
        }

        body {
            margin: 0px;
            padding: 0px;
            font-family: 'TriumVirateCondensedBold', serif !important;
            font-size: 8px;
            line-height: 8px;
        }

        .subtitle {
            font-size: 10px;
        }

        .triumVirateCondensedRegular {
            font-family: 'TriumVirateCondensed', serif !important;
        }

        .border {
            border: 1px solid;
        }

        table {
            border-collapse: collapse;
            border-spacing: 0;
        }

        td,
        th {
            padding: 0;
            margin: 0;
        }

        #main_table td {
            padding: 1px 2px !important;
            line-height: 10px;
        }

        #main_table td.nopadding {
            padding: 0px !important;
        }

        #main_table td.p-t1 {
            padding-top: 1px !important;
        }

        #main_table td.p-t2 {
            padding-top: 2px !important;
        }

        .onlyborderbottom {
            border: none;
            border-bottom: 1px solid;
        }

        .txt-center {
            text-align: center !important;
        }

        #main_table .basasma {
            color: #ffffff !important;
            visibility: hidden !important;
        }
    </style>
</head>

<body>
    @foreach ($pedidos as $pedido)
        @php
            if ($id_pedidoPrevio !== $pedido->id_pedido) {
                $nCaja = 1;
                $id_pedidoPrevio = $pedido->id_pedido;
            }

        @endphp
        @foreach ($pedido->detalles as $det_ped)
            @php
                $marcaciones = yura\Modelos\DetallePedidoDatoExportacion::where([
                    ['id_detalle_pedido', $det_ped->id_detalle_pedido],
                ])
                    ->get()
                    ->pluck('valor')
                    ->toArray();

                $cantidad = $det_ped->cantidad;
                $agencia = $det_ped->agencia_carga;
            @endphp
            @php
                if (filter_var($pedido->isDoublePage, FILTER_VALIDATE_BOOLEAN)) {
                    $cantidad = $cantidad * 2;
                }
            @endphp
            @foreach (range(1, $cantidad) as $caja)
                <div style="width:100%;padding: 0px 15px;">
                    <div style="text-align: center;padding-top: 1px;padding-bottom: 1px;">
                        <img src="{{ public_path('images/Logo-Senae.jpg') }}" width="50px">
                    </div>
                    <div style="width: 100%;" cellspacing="0" cellspanding="0">
                        <div class="barcode"
                            style="width: 100%;text-align: center;display: flex;justify-content: center;">
                            {!! $barCode->getBarcode(
                                isset($pedido->envios) && $pedido->envios[0]->dae != '' ? strtoupper($pedido->envios[0]->dae) : '1234567890',
                                $barCode::TYPE_CODE_128,
                                1,
                            ) !!}
                        </div>
                        <div style="text-align: center;padding-top:1px;padding-bottom:1px;">
                            <span
                                style="line-height: 1;font-size: 10px;">{{ isset($pedido->envios) && $pedido->envios[0]->dae != '' ? strtoupper($pedido->envios[0]->dae) : '1234567890' }}</span>
                        </div>
                    </div>
                    <table style="width: 100%;" cellspacing="0" cellspanding="0" width="100%">
                        <tr>
                            <td colspan="4" style="padding-top: 0px;padding-bottom:1px;">
                                <span style="font-size:9px">
                                    País de destino:
                                    {{ isset($pedido->envios) && $pedido->envios[0]->pais != null ? $pedido->envios[0]->pais->nombre : 'SIN PAÍS' }}
                                </span>
                            </td>
                        </tr>
                        @if ($pedido->cliente->detalle()->nombre_empresa_etiqueta)
                            <tr>
                                <td colspan="4"
                                    style="text-align: center;padding-top:1px;padding-right:0;padding-bottom:1px;padding-left:0;">
                                    <div style="font-size: 12px">
                                        {{ strtoupper($pedido->empresa->nombre) }}
                                    </div>
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td class="subtitle" valign="top" colspan="4">
                                SOLD TO: {{ strtoupper($pedido->cliente->detalle()->nombre) }}
                            </td>
                        </tr>
                        <tr>
                            <td valign="top" colspan="4">
                                CONSIGNEE:
                                {{ isset($pedido->envios) && $pedido->envios[0]->consignatario != null ? $pedido->envios[0]->consignatario->nombre : 'SIN CONSIGNATARIO' }}
                            </td>
                        </tr>
                        <tr>
                            <td valign="top" colspan="2" style="font-size: 9px">
                                MAWB: {{ isset($pedido->envios) ? $pedido->envios[0]->guia_madre : 'SIN GUIA' }}
                            </td>
                            <td valign="top" colspan="2" style="font-size: 9px; text-align: right">
                                HAWB: {{ isset($pedido->envios) ? $pedido->envios[0]->guia_hija : 'SIN GUIA' }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" style="font-size: 10px">
                                CARGO: {{ $agencia->nombre }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="1">
                                PACK DETAIL:
                            </td>
                            <td colspan="3" style="font-size: 14px;padding-top: 5px; text-align: right">
                                @php
                                    switch (
                                        explode(
                                            '|',
                                            $det_ped->cliente_especificacion->especificacion->especificacionesEmpaque[0]
                                                ->empaque->nombre,
                                        )[1]
                                    ) {
                                        case '0.25':
                                            $emp = 'QB';
                                            break;
                                        case '0.5':
                                            $emp = 'HB';
                                            break;
                                        case '0.125':
                                            $emp = 'EB';
                                            break;
                                        case '0.0625':
                                            $emp = 'SB';
                                            break;
                                        default:
                                            $emp = '';
                                            break;
                                    }
                                @endphp
                                @if (isset($marcaciones) && !empty($marcaciones))
                                    {{ implode(' - ', $marcaciones) }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td colspan="1">
                                BOX#: {{ $nCaja }}
                                <span style="font-size: 12px; margin-left: 5px">{{ $emp }}</span>
                            </td>
                        </tr>
                    </table>
                    <table id="main_table" style="width: 100%;font-size: 8px;" cellspacing="0" cellspanding="0"
                        width="100%">
                        <tr>
                            <td class="border" width="44%" style="text-align: left;padding: 0;" rowspan="2">
                                PRODUCTO
                            </td>
                            <td class="border" width="8%" rowspan="2">
                                STEMS
                            </td>
                            <td class="border" width="18%" rowspan="2">
                                COLOR
                            </td>
                            <td class="border txt-center" colspan="5">
                                MEDIDA
                            </td>
                        </tr>
                        <tr>
                            <td class="border txt-center">90</td>
                            <td class="border txt-center">80</td>
                            <td class="border txt-center">70</td>
                            <td class="border txt-center">60</td>
                            <td class="border txt-center">50</td>
                        </tr>
                        @foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp)
                            @foreach ($esp_emp->detalles as $det_esp_emp)
                                @php

                                    $ramos_modificado = getRamosXCajaModificado(
                                        $det_ped->id_detalle_pedido,
                                        $det_esp_emp->id_detalle_especificacionempaque,
                                    );

                                    $distribucionAssorted = \yura\Modelos\DistribucionMixtos::where('ramos', '>', 0)
                                        ->where('fecha', opDiasFecha('-', 1, $pedido->fecha_pedido))
                                        ->where('id_cliente', $pedido->id_cliente)
                                        ->where('id_pedido', $pedido->id_pedido)
                                        ->where('id_detalle_pedido', $det_ped->id_detalle_pedido)
                                        ->where(
                                            'id_detalle_especificacionempaque',
                                            $det_esp_emp->id_detalle_especificacionempaque,
                                        )
                                        ->get();

                                    $L50 = '';
                                    $L60 = '';
                                    $L70 = '';
                                    $L80 = '';
                                    $L90 = '';

                                    if ($det_esp_emp->longitud_ramo == 50) {
                                        $L50 = isset($ramos_modificado)
                                            ? $ramos_modificado->cantidad
                                            : $det_esp_emp->cantidad;
                                    }

                                    if ($det_esp_emp->longitud_ramo == 60) {
                                        $L60 = isset($ramos_modificado)
                                            ? $ramos_modificado->cantidad
                                            : $det_esp_emp->cantidad;
                                    }

                                    if ($det_esp_emp->longitud_ramo == 70) {
                                        $L70 = isset($ramos_modificado)
                                            ? $ramos_modificado->cantidad
                                            : $det_esp_emp->cantidad;
                                    }

                                    if ($det_esp_emp->longitud_ramo == 80) {
                                        $L80 = isset($ramos_modificado)
                                            ? $ramos_modificado->cantidad
                                            : $det_esp_emp->cantidad;
                                    }

                                    if ($det_esp_emp->longitud_ramo == 90) {
                                        $L90 = isset($ramos_modificado)
                                            ? $ramos_modificado->cantidad
                                            : $det_esp_emp->cantidad;
                                    }
                                @endphp

                                @if ($distribucionAssorted->count())
                                    @foreach ($distribucionAssorted as $disAssorted)
                                        <tr>
                                            <td class="border" style="text-align: left;padding: 0;">
                                                {{ $disAssorted->planta->nombre }} <sup>MIX</sup></td>
                                            <td class="border">
                                                {{ $det_esp_emp->tallos_x_ramos }} </td>
                                            <td class="border" style="text-align: left;padding: 0;">
                                                {{ $disAssorted->variedad()->nombre }}</td>
                                            <td class="border">
                                                @if ($det_esp_emp->longitud_ramo == 90)
                                                    {{ $disAssorted->ramos }}
                                                @else
                                                    <span class="basasma">0</span>
                                                @endif
                                            </td>
                                            <td class="border">
                                                @if ($det_esp_emp->longitud_ramo == 80)
                                                    {{ $disAssorted->ramos }}
                                                @else
                                                    <span class="basasma">0</span>
                                                @endif
                                            </td>
                                            <td class="border">
                                                @if ($det_esp_emp->longitud_ramo == 70)
                                                    {{ $disAssorted->ramos }}
                                                @else
                                                    <span class="basasma">0</span>
                                                @endif
                                            </td>
                                            <td class="border">
                                                @if ($det_esp_emp->longitud_ramo == 60)
                                                    {{ $disAssorted->ramos }}
                                                @else
                                                    <span class="basasma">0</span>
                                                @endif
                                            </td>
                                            <td class="border">
                                                @if ($det_esp_emp->longitud_ramo == 50)
                                                    {{ $disAssorted->ramos }}
                                                @else
                                                    <span class="basasma">0</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        @if (strlen($det_esp_emp->variedad->planta->nombre) <= 12)
                                            <td class="border" style="text-align: left;padding: 0;">
                                                {{ $det_esp_emp->variedad->planta->nombre }} </td>
                                        @else
                                            <td class="border" style="text-align: left">
                                                {{ $det_esp_emp->variedad->planta->nombre }}
                                            </td>
                                        @endif
                                        <td class="border">
                                            {{ $det_esp_emp->tallos_x_ramos }} </td>
                                        <td class="border" style="text-align: left;padding: 0;">
                                            {{ $det_esp_emp->variedad->nombre }} </td>
                                        <td class="border">
                                            {{ $L90 }} </td>
                                        <td class="border">
                                            {{ $L80 }} </td>
                                        <td class="border">
                                            {{ $L70 }} </td>
                                        <td class="border">
                                            {{ $L60 }} </td>
                                        <td class="border">
                                            {{ $L50 }} </td>
                                    </tr>
                                @endif
                            @endforeach
                        @endforeach
                    </table>
                    <div style="width:100%;text-align: center;padding-top:1px;">
                        <div>
                            <img src="{{ public_path('images/logo_agro_calidad.png') }}"
                                style="margin-top: 0;display:block;" width="120px">
                        </div>
                        <div style="padding-top: 1px;">
                            <span style="font-size:8px;">1790996743001.05050802</span>
                        </div>
                    </div>
                </div>
                @php
                    if (filter_var($pedido->isDoublePage, FILTER_VALIDATE_BOOLEAN)) {
                        if ($caja % 2 == 0) {
                            $nCaja++;
                        }
                    } else {
                        $nCaja++;
                    }
                @endphp
                {{-- @if ($caja < $det_ped->cantidad)
                <div style="page-break-after:always;"></div>
            @endif --}}

                <div style="page-break-after:always;"></div>
            @endforeach
        @endforeach
    @endforeach
</body>

</html>
