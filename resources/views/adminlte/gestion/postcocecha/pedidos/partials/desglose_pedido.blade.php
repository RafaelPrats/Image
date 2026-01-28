@php
    $detalleFactura = isset($pedido->envios[0]->comprobante->detalle_factura) ? $pedido->envios[0]->comprobante->detalle_factura : null;
    $cliente = $pedido->cliente->detalle();
    $consignatario = isset($pedido->envios[0]->consignatario) ? $pedido->envios[0]->consignatario : null;
    $envio = $pedido->envios[0];
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

    foreach ($pedido->detalles as $det_ped) {
        foreach ($det_ped->detalle_pedido_dato_exportacion as $de) {
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
        <td style="font-size: 40px"><b>{{ $pedido->empresa->nombre }}</b></td>
    </tr>
    <tr>
        <td style="font-size: 13px">
            <b>R.U.C.</b> {{ $pedido->empresa->ruc }} <b><i>CONTRIBUYENTE ESPECIAL RESOL. No 636 DIC. 29/2005</i></b>
        </td>
    </tr>
    <tr>
        <td style="font-size: 13px">
            <b>MATRIZ.</b> {{ $pedido->empresa->direccion_matriz }}
        </td>
    </tr>
    <tr>
        <td style="font-size: 13px">
            <b>Tlf.</b> {{ $pedido->empresa->telefono }}
        </td>
    </tr>
    <tr>
        <td style="font-size: 13px">
            <b>Plantanción agricola.</b> {{ $pedido->empresa->direccion_establecimiento }} <b>FAX:</b>
            {{ $pedido->empresa->fax }}
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
                    <td style="font-size:17px"><b>{{ $pedido->empresa->nombre }}</b></td>
                </tr>
                <tr>
                    <td style="font-size:17px">{{ $pedido->empresa->ruc }}</td>
                </tr>
                <tr>
                    <td style="font-size:12px">{{ $pedido->empresa->direccion_matriz }}</td>
                </tr>
                <tr>
                    <td style="font-size:12px">Quito - {{ getPais($pedido->empresa->codigo_pais)->nombre }}</td>
                </tr>
                <tr>
                    <td style="font-size:12px">PHONE: {{ $pedido->empresa->telefono }} - FAX:
                        {{ $pedido->empresa->fax }}</td>
                </tr>
                <tr>
                    <td style="font-size:12px">CONTACT: VENTAS NINTANGA</td>
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
                        {{ strtoupper(strftime('%h %d, %G', gmmktime(12, 0, 0, \Carbon\Carbon::parse($pedido->fecha_pedido)->format('m'), \Carbon\Carbon::parse($pedido->fecha_pedido)->format('d'), \Carbon\Carbon::parse($pedido->fecha_pedido)->format('Y')))) }}
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="font-size: 17px">
                        Packing List No.: {{ $pedido->packing }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="font-size: 17px">Carrier name:
                        {{ $pedido->detalles[0]->agencia_carga->nombre }}</td>
                </tr>
                <tr>
                    <td>MAWB No.:</td>
                    <td>HAWB No.:</td>
                </tr>
                <tr>
                    <td style="font-size: 18px;border:1px solid black">
                        <p style="margin:0px">{{ isset($pedido->envios) ? $pedido->envios[0]->guia_madre : '' }}</p>
                    </td>
                    <td style="font-size: 18px;border:1px solid black"><b></b>
                        <p style="margin:0px">{{ isset($pedido->envios) ? $pedido->envios[0]->guia_hija : '' }}</p>
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
            <b>{{ $pedido->cliente->detalle()->nombre }}</b>
        </td>
        <td style="border:1px solid black;width:100%;text-align:center">
            <table>
                <tr>
                    <td>COUNTRY CODE: </td>
                    <td>EC</td>
                </tr>
                <tr>
                    <td>DAE No: </td>
                    <td>{{ isset($envio) ? $envio->dae : '' }}</td>
                </tr>
                <tr>
                    <td> FREIGHT FORWARDER: </td>
                    <td>{{ $pedido->detalles[0]->agencia_carga->nombre }}</td>
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
<table style="width:100%;font-family:arial, sans-serif;" cellpadding="0" cellspacing="0">
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
            <th style="font-size: 14px;vertical-align: middle;border: 1px solid">
                SPI
            </th>
            <th style="font-size: 14px;vertical-align: middle;border: 1px solid">
                USHTS
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
        @if ($pedido->tipo_especificacion === 'N')
            @foreach ($pedido->detalles as $x => $det_ped)
                @php
                    $precio = explode('|', $det_ped->precio);
                    $i = 0;
                @endphp
                @foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $m => $esp_emp)
                    @foreach ($esp_emp->detalles as $n => $det_esp_emp)
                        @php
                            if ($n == 0) {
                                $full_equivalente_real += explode('|', $esp_emp->empaque->nombre)[1] * $det_ped->cantidad;
                            }
                            $getRamosXCajaModificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);
                            $ramos_x_caja = isset($getRamosXCajaModificado) ? $getRamosXCajaModificado->cantidad : $det_esp_emp->cantidad;
                        @endphp
                        <tr>
                            @if ($n == 0)
                                <td style="font-size:11px;vertical-align:middle;text-align:center;border:1px solid"
                                    rowspan="{{ $esp_emp->detalles->count() }}">
                                    @switch(explode('|',$esp_emp->empaque->nombre)[1])
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
                                    rowspan="{{ $esp_emp->detalles->count() }}">
                                    {{ $det_ped->cantidad }}
                                    @php $total_piezas += $det_ped->cantidad; @endphp
                                </td>
                            @endif
                            <td
                                style="font-size:11px;text-align:center;vertical-align:middle;border:1px solid;padding-left: 5px">
                                {{ $ramos_x_caja * $det_ped->cantidad }}
                                @php $total_ramos += $ramos_x_caja*$det_ped->cantidad; @endphp
                            </td>
                            <td
                                style="font-size:11px;text-align:center;vertical-align:middle;border:1px solid;padding-left: 5px">
                                {{ $ramos_x_caja * $det_ped->cantidad * $det_esp_emp->tallos_x_ramos }}
                                @php $total_tallos += $ramos_x_caja*$det_ped->cantidad*$det_esp_emp->tallos_x_ramos; @endphp
                            </td>
                            <td
                                style="font-size:11px;text-align:center;vertical-align:middle;border:1px solid;padding-left: 5px">
                                {{ $det_esp_emp->tallos_x_ramos }}
                            </td>
                            <td style="font-size:11px;vertical-align:middle;border:1px solid;padding-left: 5px">
                                {{ $det_esp_emp->variedad->planta->nombre . '  ' . $det_esp_emp->variedad->nombre }}
                            </td>
                            <td style="font-size:11px;vertical-align:middle;border:1px solid;padding-left: 5px">A</td>
                            <td style="font-size:11px;vertical-align:middle;border:1px solid;padding-left: 5px">
                                0603199090</td>
                            @if (count($getDatosExportacion))
                                @foreach ($getDatosExportacion as $de)
                                    @if ($de->nombre != 'PO')
                                        @php
                                            $getValorMarcacionByDatoExportacion = $det_ped->getValorMarcacionByDatoExportacion($de->id_dato_exportacion);
                                        @endphp
                                        <td
                                            style="font-size:11px;vertical-align:middle;border:1px solid;padding-left: 5px">
                                            {{ $getValorMarcacionByDatoExportacion != '' ? $getValorMarcacionByDatoExportacion->valor : '' }}
                                        </td>
                                    @endif
                                @endforeach
                            @endif
                            <td style="font-size:11px;border:1px solid;padding-left: 5px;text-align:center">
                                {{ "$" . number_format(explode(';', $precio[$i])[0], 2, '.', '') }} </td>
                            <td style="font-size:11px;border:1px solid;padding-left: 5px;text-align:right">
                                @php
                                    $precioTotalVariedad = number_format((isset($ramos_modificado) ? $ramos_modificado->cantidad : $ramos_x_caja) * ((float) explode(';', $precio[$i])[0]) * $esp_emp->cantidad * $det_ped->cantidad, 2, '.', '');
                                @endphp
                                {{ "$" . $precioTotalVariedad }}
                            </td>
                        </tr>
                        @php
                            if ($esp_emp->especificacion->tipo != 'O') {
                                $precio_total_sin_impuestos += (isset($ramos_modificado) ? $ramos_modificado->cantidad : $ramos_x_caja) * (float) explode(';', $precio[$i])[0] * $esp_emp->cantidad * $det_ped->cantidad;
                            } else {
                                $precio_total_sin_impuestos += $det_ped->total_tallos() * (float) explode(';', $precio[$i])[0];
                            }
                        @endphp
                        @php  $i++;  @endphp
                    @endforeach
                @endforeach
            @endforeach
        @endif
        <tr>
            <td>TOTALES</td>
            <td style="text-align: center;border: 0px">{{ $total_piezas }}</td>
            <td style="text-align: center;border: 0px">{{ $total_ramos }}</td>
            <td style="text-align: center;border: 0px">{{ $total_tallos }}</td>
            <td colspan="10" style="font-size: 15px;border: 0px"><b>TERMS OF PAYMENT: FCA, AS PER AGREEMENT.</b></td>
            <td></td>
        </tr>
        <tr>
            <td style="text-align: right;border: 0px" colspan="11"><b>TOTAL VALUE FCA US
                    $:{{ $pedido->getPrecio() }}</b></td>
            <td></td>
        </tr>
        <tr>
            <td style="text-align: left;border: 0px" colspan="5"><b>REMINDER: STOCK MAUVE IS 60cm</b></td>
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
                MARIA BELEN VARGAS CARVAJAL
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
