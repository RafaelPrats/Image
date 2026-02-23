@php
    $cliente = $pedido->cliente->detalle();
    $consignatario = isset($pedido->consignatario) ? $pedido->consignatario : null;
    $agencia_carga = isset($pedido->agencia_carga) ? $pedido->agencia_carga : null;
    $empresa = $pedido->empresa;
    $precio_total_sin_impuestos = 0.0;
    $total_ramos = 0.0;
    $total_piezas = 0.0;
    $full_equivalente_real = 0.0;
    $full = 0;
    $half = 0;
    $cuarto = 0;
    $sexto = 0;
    $octavo = 0;
    $peso_neto = 0;
    $peso_bruto = 0;
    $peso_caja = 0;
    $descripcion = '';
    $frac_piezas = 0;
    $total_tallos = 0;
    $datos_tinturados = [];
    $data_body_table = [];
    $pieza = 0;
    $datosExportacion = [];
    $getDatosExportacion = $pedido->getDatosExportacion();

    foreach ($pedido->cajas as $caja) {
        foreach ($caja->marcaciones as $de) {
            if (!in_array($de->valor, array_column($datosExportacion, 'valor'))) {
                $datosExportacion[] = [
                    'nombre' => $de->dato_exportacion->nombre,
                    'valor' => $de->valor,
                ];
            }
        }
    }
@endphp

<table style="width:100%;font-family:arial, sans-serif">
    <tr>
        <td style="font-size: 40px"><b>{{ $empresa->nombre }}</b></td>
    </tr>
    <tr>
        <td style="font-size: 13px">
            <b>R.U.C.</b> {{ $empresa->ruc }}
        </td>
    </tr>
    <tr>
        <td style="font-size: 13px">
            <b>MATRIZ.</b> {{ $empresa->direccion_matriz }}
        </td>
    </tr>
    <tr>
        <td style="font-size: 13px">
            <b>Tlf.</b> {{ $empresa->telefono }}
        </td>
    </tr>
    <tr>
        <td style="font-size: 13px">
            <b>Plantanción agricola.</b> {{ $empresa->direccion_establecimiento }}
        </td>
    </tr>
</table>
<table style="font-family:arial, sans-serif;width:100%">
    <tr>
        <td style="border:1px solid black;vertical-align:top;text-align:center;width:100%">
            <table style="text-align:center;width:100%">
                <tr>
                    <td style="font-size:17px"><b>SHIPPER:</b></td>
                </tr>
                <tr>
                    <td style="font-size:17px"><b>{{ $empresa->nombre }}</b></td>
                </tr>
                <tr>
                    <td style="font-size:17px">{{ $empresa->ruc }}</td>
                </tr>
                <tr>
                    <td style="font-size:12px">{{ $empresa->direccion_matriz }}</td>
                </tr>
                <tr>
                    <td style="font-size:12px">Quito - {{ getPais($empresa->codigo_pais)->nombre }}</td>
                </tr>
                <tr>
                    <td style="font-size:12px">PHONE: {{ $empresa->telefono }}</td>
                </tr>
            </table>
        </td>
        <td style="border:1px solid black;width:100%">
            <table style="text-align:center;width:100%">
                <tr>
                    <td>Farm Code:</td>
                    <td>Date:</td>
                </tr>
                <tr>
                    <td style="border:1px solid black;width:100%">NIN</td>
                    <td style="border:1px solid black;width:100%">
                        {{ strtoupper(strftime('%h %d, %G', gmmktime(12, 0, 0, \Carbon\Carbon::parse($pedido->fecha)->format('m'), \Carbon\Carbon::parse($pedido->fecha)->format('d'), \Carbon\Carbon::parse($pedido->fecha)->format('Y')))) }}
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="font-size: 17px">
                        Packing List No.: {{ $pedido->packing }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="font-size: 17px">Carrier name:
                        {{ $agencia_carga->nombre }}
                    </td>
                </tr>
                <tr>
                    <td>MAWB No.:</td>
                    <td>HAWB No.:</td>
                </tr>
                <tr>
                    <td style="font-size: 18px;border:1px solid black">
                        <p style="margin:0px">{{ $pedido->guia_madre }}</p>
                    </td>
                    <td style="font-size: 18px;border:1px solid black"><b></b>
                        <p style="margin:0px">{{ $pedido->guia_hija }}</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2">SOLD TO:</td>
    </tr>
    <tr>
        <td style="border:1px solid black;width:100%;text-align:center">
            <b>{{ $cliente->nombre }}</b>
        </td>
        <td style="border:1px solid black;width:100%;text-align:center">
            <table>
                <tr>
                    <td>COUNTRY CODE: </td>
                    <td>EC</td>
                </tr>
                <tr>
                    <td>DAE No: </td>
                    <td>{{ $pedido->dae }}</td>
                </tr>
                <tr>
                    <td> FREIGHT FORWARDER: </td>
                    <td>{{ $agencia_carga->nombre }}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2"> CONSIGNEE: </td>
    </tr>
    <tr>
        <td style="border:1px solid black;width:100%;text-align:center">
            <table style="text-align:center;width:100%">
                <tr>
                    <td style="text-align:center">
                        <b>{{ isset($consignatario) ? $consignatario->nombre : '' }}</b>
                    </td>
                </tr>
                <tr>
                    <td style="text-align:center">
                        {{ isset($consignatario) ? $consignatario->direccion : '' }}
                    </td>
                </tr>
                <tr>
                    <td style="text-align:center">
                        {{ isset($consignatario) ? (getPais($consignatario->codigo_pais) != null ? getPais($consignatario->codigo_pais)->nombre : '') : '' }}
                    </td>
                </tr>
                <tr>
                    <td style="text-align:center">
                        PHONE: {{ isset($consignatario) ? $consignatario->telefono : '' }}
                    </td>
                </tr>

            </table>
        </td>
        <td>
            <table style="font-family:arial, sans-serif;width:100%">
                @foreach ($datosExportacion as $de)
                    @if ($de['nombre'] == 'PO')
                        <tr>
                            <td style="border:1px solid black;width:100%;font-size:14px">
                                <b>{{ $de['nombre'] }}: {{ $de['valor'] }}</b>
                            </td>
                        </tr>
                    @endif
                @endforeach
                <tr>
                    <td style="border:1px solid black;width:100%;font-size:14px">
                        <b>Port for Entry:
                            {{ isset($pedido->envios) && getPais($pedido->envios[0]->codigo_pais) != null ? getPais($pedido->envios[0]->codigo_pais)->nombre : '' }}</b>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<table style="width:100%;font-family:arial, sans-serif;">
    <thead style="border-bottom: 1px solid;border-top: 1px solid">
        <tr>
            <th style="font-size: 14px;vertical-align: middle;border: 1px solid" colspan="2">
                PIECES
            </th>
            <th style="font-size: 14px;vertical-align: middle;border: 1px solid">
                BUNCHES
            </th>
            <th style="font-size: 14px;vertical-align: middle;border: 1px solid">
                TOTAL STEMS
            </th>
            <th style="font-size: 14px;vertical-align: middle;border: 1px solid">
                STEMS / BUNCHES
            </th>
            <th style="font-size: 14px;vertical-align: middle;border: 1px solid">
                DESCRIPTION
            </th>
            @if (count($getDatosExportacion))
                @foreach ($getDatosExportacion as $de)
                    @if ($de->nombre != 'PO')
                        <th style="font-size: 14px;vertical-align: middle;border: 1px solid;width:30px">
                            {{ $de->nombre }}
                        </th>
                    @endif
                @endforeach
            @endif
            <th style="font-size: 14px;vertical-align: middle;width:70px;border: 1px solid">
                UNIT PRCE
            </th>
            <th style="font-size: 14px;vertical-align: middle;border: 1px solid">
                TOTAL AMOUNT
            </th>
        </tr>
    </thead>
    <tbody style="border-bottom: 1px solid">
        @foreach ($pedido->cajas as $x => $caja)
            @php
                $empaque = $caja->empaque;
                $full_equivalente_real += explode('|', $empaque->nombre)[1] * $caja->cantidad;
                $total_piezas += $caja->cantidad;
            @endphp
            @foreach ($caja->detalles as $n => $det_caja)
                @php
                    $ramos_x_caja = $det_caja->ramos_x_caja;
                    $total_ramos += $ramos_x_caja * $caja->cantidad;
                    $total_tallos += $ramos_x_caja * $caja->cantidad * $det_caja->tallos_x_ramo;
                    $variedad = $det_caja->variedad;
                @endphp
                <tr>
                    @if ($n == 0)
                        <td style="font-size:11px;vertical-align:middle;text-align:center;border:1px solid"
                            rowspan="{{ $caja->detalles->count() }}">
                            @switch(explode('|',$empaque->nombre)[1])
                                @case('1')
                                    FB
                                @break

                                @case('0.5')
                                    HB
                                @break

                                @case('0.25')
                                    QB
                                @break

                                @case('0.125')
                                    EB
                                @break
                            @endswitch
                        </td>
                        <td style="font-size:11px;vertical-align:middle;text-align:center;border:1px solid"
                            rowspan="{{ $caja->detalles->count() }}">
                            {{ $caja->cantidad }}
                        </td>
                    @endif
                    <td
                        style="font-size:11px;text-align:center;vertical-align:middle;border:1px solid;padding-left: 5px">
                        {{ $ramos_x_caja * $caja->cantidad }}
                    </td>
                    <td
                        style="font-size:11px;text-align:center;vertical-align:middle;border:1px solid;padding-left: 5px">
                        {{ $ramos_x_caja * $caja->cantidad * $det_caja->tallos_x_ramo }}
                    </td>
                    <td
                        style="font-size:11px;text-align:center;vertical-align:middle;border:1px solid;padding-left: 5px">
                        {{ $det_caja->tallos_x_ramo }}
                    </td>
                    <td style="font-size:11px;vertical-align:middle;border:1px solid;padding-left: 5px">
                        {{ $variedad->planta->nombre . '  ' . $variedad->nombre }}
                    </td>
                    @if (count($getDatosExportacion))
                        @foreach ($getDatosExportacion as $de)
                            @if ($de->nombre != 'PO')
                                @php
                                    $getValorMarcacionByDatoExportacion = $caja->getValorMarcacionByDatoExportacion(
                                        $de->id_dato_exportacion,
                                    );
                                @endphp
                                <td style="font-size:11px;vertical-align:middle;border:1px solid;padding-left: 5px">
                                    {{ $getValorMarcacionByDatoExportacion != '' ? $getValorMarcacionByDatoExportacion->valor : '' }}
                                </td>
                            @endif
                        @endforeach
                    @endif
                    <td style="font-size:11px;border:1px solid;padding-left: 5px;text-align:center">
                        {{ "$" . number_format($det_caja->precio, 2, '.', '') }} </td>
                    <td style="font-size:11px;border:1px solid;padding-left: 5px;text-align:right">
                        @php
                            $precioTotalVariedad = number_format(
                                $ramos_x_caja * $det_caja->precio * $caja->cantidad,
                                2,
                                '.',
                                '',
                            );
                        @endphp
                        {{ "$" . $precioTotalVariedad }}
                    </td>
                </tr>
                @php
                    $precio_total_sin_impuestos += $ramos_x_caja * $det_caja->precio * $caja->cantidad;
                @endphp
            @endforeach
        @endforeach
        <tr>
            <td>TOTALES</td>
            <td style="text-align: center;border: 0px">{{ $total_piezas }}</td>
            <td style="text-align: center;border: 0px">{{ $total_ramos }}</td>
            <td style="text-align: center;border: 0px">{{ $total_tallos }}</td>
            <td colspan="10" style="font-size: 15px;border: 0px"><b>TERMS OF PAYMENT: FCA, AS PER AGREEMENT.</b></td>
            <td></td>
        </tr>
        <tr>
            <td style="text-align: right;border: 0px" colspan="11">
                <b>TOTAL VALUE FCA US $:{{ $pedido->getMonto() }}</b>
            </td>
            <td></td>
        </tr>
        <tr>
            <td style="text-align: left;border: 0px" colspan="5"></td>
            <td style="text-align: right;border: 0px" colspan="5">
                <b>TOTAL FULL BOXES: {{ $full_equivalente_real }}</b>
            </td>
            <td></td>
        </tr>
        <tr>
            <td colspan="10" style="color:white;border: 0px">.</td>
        </tr>
        <tr>
            <td colspan="10" style="color:white;border: 0px">.</td>
        </tr>
        <tr>
            <td colspan="5" style="text-align: center;border: 0px">
                <br>
                <b>ASSISTANT FOREING TRADE</b>
            </td>
        </tr>
        <tr>
            <td colspan="10" style="color:white;border: 0px">.</td>
        </tr>
        <tr>
            <td colspan="10" style="color:white;border: 0px">.</td>
        </tr>
        <tr>
            <td colspan="4" style="border: 0px"></td>
            <td colspan="5" style="text-align: center;border: 0px">
                FLOWERS & FOLIAGE ON THIS INVOICE
                WERE WHOLLY GROWM IN ECUADOR
            </td>
            <td colspan="4" style="border: 0px"></td>
        </tr>
    </tbody>
</table>
