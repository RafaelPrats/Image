<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css">

    <style>
        /*@font-face {
            font-family: 'TriumVirateCondensed';
            font-style: normal;
            src: url('fonts/triumviratecondensed.ttf') format('truetype');
        }

        @font-face {
            font-family: 'TriumVirateCondensedBold';
            font-style: normal;
            src: url('fonts/triumviratecondensedBold.ttf') format('truetype');
        }*/

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
            font-family: 'TriumVirateCondensedBold', sans-serif;
            font-size: 8px;
            line-height: 8px;
            font-weight: bold;
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
    @foreach ($datos['pedidos'] as $pedido)
        @php
            $nCaja = 1;
            $id_pedidoPrevio = '';
            if ($id_pedidoPrevio !== $pedido->id_pedido) {
                $nCaja = 1;
                $id_pedidoPrevio = $pedido->id_pedido;
            }
            $cliente = $pedido->cliente->detalle();
            $empresa = $pedido->empresa;
            $codigo_dae = $pedido->dae;
            $nombre_pais = $pedido->codigo_pais != null ? getPais($pedido->codigo_pais)->nombre : 'SIN PAÍS';
            $agencia_carga = $pedido->agencia_carga;
            $consignatario = $pedido->consignatario;
            $consignatario_nombre = $consignatario != null ? $consignatario->nombre : 'SIN CONSIGNATARIO';
        @endphp
        @foreach ($pedido->cajas as $pos_det => $caja)
            @php
                $marcaciones = $caja->marcaciones->pluck('valor')->toArray();
                $piezas = $caja->cantidad;
                /*if (filter_var($pedido->isDoublePage, FILTER_VALIDATE_BOOLEAN)) {
                $piezas = $piezas * 2;
            }*/
            @endphp
            @foreach (range(1, $piezas) as $pieza)
                <div style="width:100%;padding: 0px 15px;">
                    <div style="text-align: center;padding-top: 1px;padding-bottom: 1px;">
                        <img src="{{ public_path('images/Logo-Senae.jpg') }}" width="50px">
                    </div>
                    <div style="width: 100%;" cellspacing="0" cellspanding="0">
                        <div class="barcode"
                            style="width: 100%;text-align: center;display: flex;justify-content: center;">
                            {!! $barCode->getBarcode($codigo_dae != '' ? $codigo_dae : '0123456789', $barCode::TYPE_CODE_128, 1) !!}
                        </div>
                        <div style="text-align: center;padding-top:1px;padding-bottom:1px;">
                            <span
                                style="line-height: 1;font-size: 10px;">{{ $codigo_dae != '' ? $codigo_dae : '0123456789' }}</span>
                        </div>
                    </div>
                    <table style="width: 100%;" cellspacing="0" cellspanding="0" width="100%">
                        <tr>
                            <td colspan="4" style="padding-top: 0px;padding-bottom:1px;">
                                <span style="font-size:9px">
                                    País de destino:
                                    {{ $nombre_pais }}
                                </span>
                            </td>
                        </tr>
                        @if ($cliente->nombre_empresa_etiqueta)
                            <tr>
                                <td colspan="4"
                                    style="text-align: center;padding-top:1px;padding-right:0;padding-bottom:1px;padding-left:0;">
                                    <div style="font-size: 12px">
                                        {{ strtoupper($empresa->nombre) }}
                                    </div>
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td class="subtitle" valign="top" colspan="4">
                                SOLD TO: {{ strtoupper($cliente->nombre) }}
                            </td>
                        </tr>
                        <tr>
                            <td valign="top" colspan="4">
                                CONSIGNEE:
                                {{ $consignatario_nombre }}
                            </td>
                        </tr>
                        <tr>
                            <td valign="top" colspan="2" style="font-size: 9px">
                                MAWB: {{ $pedido->guia_madre != '' ? $pedido->guia_madre : 'SIN GUIA' }}
                            </td>
                            <td valign="top" colspan="2" style="font-size: 9px; text-align: right;">
                                HAWB: {{ $pedido->guia_hija != '' ? $pedido->guia_hija : 'SIN GUIA' }}
                            </td>
                        </tr>
                        <tr>
                            <td valign="top" colspan="4" style="font-size: 9px">
                                CARGO: {{ $agencia_carga != '' ? $agencia_carga->nombre : 'SIN AGENCIA' }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="1">
                                PACK DETAIL:
                            </td>
                            <td valign="bottom" colspan="3" rowspan="2">
                                <table style="width: 100%;padding: 0;" cellspacing="0" cellspanding="0" width="100%">
                                    <tr>
                                        <td valign="bottom" style="font-size: 14px;padding: 0; text-align: left;">
                                            @php
                                                switch (explode('|', $caja->empaque->nombre)[1]) {
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
                                            {{ $emp }}
                                        </td>
                                        @if (isset($marcaciones) && !empty($marcaciones))
                                            <td valign="bottom" style="font-size: 14px;padding: 0; text-align: right;">
                                                {{ implode(' - ', $marcaciones) }}
                                            </td>
                                        @endif
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="1">
                                BOX#: {{ $nCaja }}
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
                        @foreach ($caja->detalles as $detalle)
                            @php
                                $variedad = $detalle->variedad;
                                $planta = $variedad->planta;
                                $distribucionAssorted =
                                    $variedad->assorted == 1 ? $detalle->mixtos->where('ramos', '>', 0) : [];

                                $L50 = '';
                                $L60 = '';
                                $L70 = '';
                                $L80 = '';
                                $L90 = '';

                                if ($detalle->longitud_ramo == 50) {
                                    $L50 = $detalle->ramos_x_caja;
                                }

                                if ($detalle->longitud_ramo == 60) {
                                    $L60 = $detalle->ramos_x_caja;
                                }

                                if ($detalle->longitud_ramo == 70) {
                                    $L70 = $detalle->ramos_x_caja;
                                }

                                if ($detalle->longitud_ramo == 80) {
                                    $L80 = $detalle->ramos_x_caja;
                                }

                                if ($detalle->longitud_ramo == 90) {
                                    $L90 = $detalle->ramos_x_caja;
                                }
                            @endphp

                            @if (count($distribucionAssorted) > 0)
                                @foreach ($distribucionAssorted as $disAssorted)
                                    <tr>
                                        <td class="border" style="text-align: left;padding: 0;">
                                            {{ $disAssorted->planta->nombre }} <sup>MIX</sup>
                                        </td>
                                        <td class="border">
                                            {{ $detalle->tallos_x_ramo }}
                                        </td>
                                        <td class="border" style="text-align: left;padding: 0;">
                                            {{ $disAssorted->variedad->nombre }}
                                        </td>
                                        <td class="border">
                                            @if ($detalle->longitud_ramo == 90)
                                                {{ $disAssorted->ramos }}
                                            @else
                                                <span class="basasma">0</span>
                                            @endif
                                        </td>
                                        <td class="border">
                                            @if ($detalle->longitud_ramo == 80)
                                                {{ $disAssorted->ramos }}
                                            @else
                                                <span class="basasma">0</span>
                                            @endif
                                        </td>
                                        <td class="border">
                                            @if ($detalle->longitud_ramo == 70)
                                                {{ $disAssorted->ramos }}
                                            @else
                                                <span class="basasma">0</span>
                                            @endif
                                        </td>
                                        <td class="border">
                                            @if ($detalle->longitud_ramo == 60)
                                                {{ $disAssorted->ramos }}
                                            @else
                                                <span class="basasma">0</span>
                                            @endif
                                        </td>
                                        <td class="border">
                                            @if ($detalle->longitud_ramo == 50)
                                                {{ $disAssorted->ramos }}
                                            @else
                                                <span class="basasma">0</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    @if (strlen($planta->nombre) <= 12)
                                        <td class="border" style="text-align: left;padding: 0;">
                                            {{ $planta->nombre }} </td>
                                    @else
                                        <td class="border" style="text-align: left">
                                            {{ $planta->nombre }}
                                        </td>
                                    @endif
                                    <td class="border">
                                        {{ $detalle->tallos_x_ramo }} </td>
                                    <td class="border" style="text-align: left;padding: 0;">
                                        {{ $variedad->nombre }} </td>
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
                {{-- @if ($caja < $caja->cantidad)
                <div style="page-break-after:always;"></div>
            @endif --}}

                <div style="page-break-after:always;"></div>
            @endforeach
        @endforeach
    @endforeach
</body>

</html>
