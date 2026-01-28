@foreach ($pedidos as $pos_ped => $pedido)
    @php
        $totalPiezas = 0;
        $ramos_estandar = 0;
        $full_equivalente_real = 0;
        $empresa = $pedido->empresa;
        $consignatario = $pedido->consignatario;
        $agencia_carga = $pedido->agencia_carga;
        $cliente = $pedido->cliente->detalle();
    @endphp

    <div style="position: relative; top: -20px; left: -30px; width: 835px">
        <table style="width:100%; font-family: arial, sans-serif;border-collapse: collapse;">
            <tr>
                <td>
                    <table style="width:100%;font-family: arial, sans-serif;border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 20px;font-weight:bold">AGROFARMING</td>
                        </tr>
                        <tr>
                            <td style="font-size: 20px;font-weight:bold">{{ $empresa->nombre }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 11px;">{{ strtoupper($empresa->direccion_establecimiento) }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 11px;">PHONE : {{ $empresa->telefono }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 11px;">E-mail : {{ $empresa->correo }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 11px;">CURRIER : {{ $agencia_carga->nombre }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 11px;"><b>SOLD TO : {{ $cliente->nombre }}</b></td>
                        </tr>
                        <tr>
                            <td style="font-size: 11px;">CONSIGNEE :
                                {{ isset($consignatario) ? $consignatario->nombre : '' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 11px;">ADDRESS :
                                {{ isset($consignatario) ? $consignatario->direccion : '' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 11px;"><b>NOTIFY : SPRING</b></td>
                        </tr>
                        <tr>
                            <td style="font-size: 11px;">CONTACT PERSON : </td>
                        </tr>
                        <tr>
                            <td style="font-size: 11px;">CITY COUNTRY : {{ $cliente->pais->nombre }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table style="width:100%;font-family: arial, sans-serif;border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 14px;font-weight:bold">
                                <b>PACKING LIST N. {{ $pedido->packing }}</b>
                            </td>
                        </tr>
                        <tr>
                            <td style="color:white">.</td>
                        </tr>
                        <tr>
                            <td style="color:white">.</td>
                        </tr>
                        <tr>
                            <td style="color:white">.</td>
                        </tr>
                        <tr>
                            <td style="color:white">.</td>
                        </tr>
                        <tr>
                            <td>DATE: {{ $pedido->fecha }}</td>
                        </tr>
                        <tr>
                            <td>M.A.W.B: {{ $pedido->guia_madre }}</td>
                        </tr>
                        <tr>
                            <td>H.A.W.B: {{ $pedido->guia_hija }}</td>
                        </tr>
                        <tr>
                            <td>AIR LINE: </td>
                        </tr>
                        <tr>
                            <td>DAE N.: {{ $pedido->dae }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table style="width:100%;font-family: arial, sans-serif;border-collapse: collapse;">
            <thead style="border: 1px solid black">
                @php
                    $datosExportacion = [];
                    foreach ($pedido->cajas as $caja) {
                        foreach ($caja->marcaciones as $de) {
                            if (!in_array($de->dato_exportacion->nombre, array_column($datosExportacion, 'nombre'))) {
                                $datosExportacion[] = [
                                    'id_dato_exportacion' => $de->id_dato_exportacion,
                                    'nombre' => $de->dato_exportacion->nombre,
                                ];
                            }
                        }
                    }
                @endphp
                <tr>
                    <td style="border: 1px solid black; font-size: 0.7em">TB</td>
                    <td style="border: 1px solid black; font-size: 0.7em">BX.D</td>
                    <td style="border: 1px solid black; font-size: 0.7em">BX.H</td>
                    @foreach ($datosExportacion as $de)
                        <td style="border: 1px solid black; font-size: 0.5em">{{ $de['nombre'] }}</td>
                    @endforeach
                    <td style="border: 1px solid black; font-size: 0.7em">PROD.</td>
                    <td style="border: 1px solid black; font-size: 0.7em;">COLOR</td>
                    <td style="border: 1px solid black; font-size: 0.7em">PRES.</td>
                    <td style="border: 1px solid black; font-size: 0.7em">STEMS</td>
                    <td style="border: 1px solid black; font-size: 0.7em">90</td>
                    <td style="border: 1px solid black; font-size: 0.7em">80</td>
                    <td style="border: 1px solid black; font-size: 0.7em">70</td>
                    <td style="border: 1px solid black; font-size: 0.7em">60</td>
                    <td style="border: 1px solid black; font-size: 0.7em">50</td>
                    <td style="border: 1px solid black; font-size: 0.7em">Nac.</td>
                    <td style="border: 1px solid black; font-size: 0.7em">BUNCH/ Box</td>
                    <td style="border: 1px solid black; font-size: 0.7em">BUNCHS</td>
                    <td style="border: 1px solid black; font-size: 0.7em">T. STEMS</td>
                </tr>
            </thead>
            <tbody style="border: 1px solid black">
                @php
                    $cajaFin = 0;
                    $totalTallos = 0;
                    $totalRamos = 0;
                @endphp
                @foreach ($pedido->cajas as $x => $caja)
                    @php
                        $totalPiezas += $caja->cantidad;
                        $cajaInicio = $cajaFin + 1;
                        $cajaFin += $caja->cantidad;
                    @endphp
                    @foreach ($caja->detalles as $z => $det_caja)
                        @php
                            $variedad = $det_caja->variedad;
                            $distribucionAssorted =
                                $variedad->assorted == 1 ? $det_caja->mixtos->where('ramos', '>', 0) : [];
                        @endphp
                        @if ($z == 0)
                            @php $full_equivalente_real += explode("|",$caja->empaque->nombre)[1]* $caja->cantidad; @endphp
                        @endif
                        @if (count($distribucionAssorted) > 0)
                            @foreach ($distribucionAssorted as $disAssorted)
                                <tr>
                                    <td style="padding-left: 5px;font-size:11px;border:1px solid black">
                                        @if ($z == 0)
                                            @switch(explode('|',$caja->empaque->nombre)[1])
                                                @case('0.5')
                                                    H
                                                @break

                                                @case('0.25')
                                                    Q
                                                @break

                                                @case('0.125')
                                                    E
                                                @break

                                                @case('0.0625')
                                                    SB
                                                @break
                                            @endswitch
                                        @endif
                                    </td>
                                    <td
                                        style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                        {{ $cajaInicio }}
                                    </td>
                                    <td
                                        style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                        {{ $cajaFin }}
                                    </td>
                                    @foreach ($datosExportacion as $de)
                                        <td
                                            style="padding-left: 5px;font-size:11px;border:1px solid black; font-size: 0.6em">
                                            @php $ddE= $caja->marcaciones->where('id_dato_exportacion',$de['id_dato_exportacion'])->first() @endphp
                                            {{ isset($ddE) ? $ddE->valor : '' }}
                                        </td>
                                    @endforeach
                                    <td style="padding-left: 5px;font-size:11px;border:1px solid black">
                                        {{ $disAssorted->planta->nombre }}
                                    </td>
                                    <td style="padding-left: 5px;font-size:11px;border:1px solid black;">
                                        {{ $disAssorted->variedad->nombre }} - ASSORTED
                                    </td>
                                    <td style="padding-left: 5px;font-size:11px;border:1px solid black;">
                                        {{ $det_caja->empaque->nombre }}
                                    </td>
                                    <td
                                        style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                        {{ $det_caja->tallos_x_ramo }}
                                    </td>
                                    <td
                                        style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                        @if ($det_caja->longitud_ramo == 90)
                                            {{ $disAssorted->ramos * $caja->cantidad }}
                                        @endif
                                    </td>
                                    <td
                                        style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                        @if ($det_caja->longitud_ramo == 80)
                                            {{ $disAssorted->ramos * $caja->cantidad }}
                                        @endif
                                    </td>
                                    <td
                                        style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                        @if ($det_caja->longitud_ramo == 70)
                                            {{ $disAssorted->ramos * $caja->cantidad }}
                                        @endif
                                    </td>
                                    <td
                                        style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                        @if ($det_caja->longitud_ramo == 60)
                                            {{ $disAssorted->ramos * $caja->cantidad }}
                                        @endif
                                    </td>
                                    <td
                                        style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                        @if ($det_caja->longitud_ramo == 50)
                                            {{ $disAssorted->ramos * $caja->cantidad }}
                                        @endif
                                    </td>
                                    <td
                                        style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                        @if ($det_caja->longitud_ramo == 0)
                                            {{ $disAssorted->ramos * $caja->cantidad }}
                                        @endif
                                    </td>
                                    <td
                                        style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                        {{ $disAssorted->ramos }}
                                    </td>
                                    <td
                                        style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                        {{ $disAssorted->ramos * $caja->cantidad }}
                                    </td>
                                    <td
                                        style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:right ">
                                        {{ $disAssorted->tallos }}
                                    </td>
                                </tr>
                                @php
                                    $totalRamos += $disAssorted->ramos * $caja->cantidad;
                                    $totalTallos += $disAssorted->tallos;
                                @endphp
                            @endforeach
                        @else
                            <tr>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black">
                                    @if ($z == 0)
                                        @switch(explode('|',$caja->empaque->nombre)[1])
                                            @case('0.5')
                                                H
                                            @break

                                            @case('0.25')
                                                Q
                                            @break

                                            @case('0.125')
                                                E
                                            @break

                                            @case('0.0625')
                                                SB
                                            @break
                                        @endswitch
                                    @endif
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                    {{ $cajaInicio }}
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                    {{ $cajaFin }}
                                </td>
                                @foreach ($datosExportacion as $de)
                                    <td
                                        style="padding-left: 5px;font-size:11px;border:1px solid black; font-size: 0.6em">
                                        @php $ddE= $caja->marcaciones->where('id_dato_exportacion',$de['id_dato_exportacion'])->first() @endphp
                                        {{ isset($ddE) ? $ddE->valor : '' }}
                                    </td>
                                @endforeach
                                <td
                                    style="padding-left: 5px;font-size:11px;padding-left: 5px;font-size:11px;border:1px solid black;">
                                    {{ $variedad->planta->nombre }}
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;">
                                    {{ $variedad->nombre }}
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;">
                                    {{ $det_caja->empaque->nombre }}
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                    {{ $det_caja->tallos_x_ramo }}
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                    @if ($det_caja->longitud_ramo == 90)
                                        {{ $caja->cantidad * $det_caja->ramos_x_caja }}
                                    @endif
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                    @if ($det_caja->longitud_ramo == 80)
                                        {{ $caja->cantidad * $det_caja->ramos_x_caja }}
                                    @endif
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                    @if ($det_caja->longitud_ramo == 70)
                                        {{ $caja->cantidad * $det_caja->ramos_x_caja }}
                                    @endif
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                    @if ($det_caja->longitud_ramo == 60)
                                        {{ $caja->cantidad * $det_caja->ramos_x_caja }}
                                    @endif
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                    @if ($det_caja->longitud_ramo == 50)
                                        {{ $caja->cantidad * $det_caja->ramos_x_caja }}
                                    @endif
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                    @if ($det_caja->longitud_ramo == 0)
                                        {{ $caja->cantidad * $det_caja->ramos_x_caja }}
                                    @endif
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                    {{ $det_caja->ramos_x_caja }}
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                    {{ $caja->cantidad * $det_caja->ramos_x_caja }}
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:right">
                                    {{ $caja->cantidad * $det_caja->ramos_x_caja * $det_caja->tallos_x_ramo }}
                                </td>
                            </tr>
                            @php
                                $totalRamos += $caja->cantidad * $det_caja->ramos_x_caja;
                                $totalTallos += $caja->cantidad * $det_caja->ramos_x_caja * $det_caja->tallos_x_ramo;
                            @endphp
                        @endif
                    @endforeach
                @endforeach
                <tr>
                    <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:right"
                        colspan="{{ count($datosExportacion) + 6 }}">Total</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                        <b>{{ $totalRamos }}</b>
                    </td>
                    <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:right">
                        <b>{{ $totalTallos }}</b>
                    </td>
                </tr>
            </tbody>
        </table>
        <table style="width:100%;margin-top: 20px;font-family: arial, sans-serif">
            <tr>
                <td style="font-size:20px" colspan="2">
                    <b>TOTAL FULL BOXES {{ round($full_equivalente_real, 2) }}</b>
                </td>
            </tr>
            <tr>
                <td style="font-size:20px" colspan="2">
                    <b>TOTAL PIEZAS {{ $totalPiezas }}</b>
                </td>
            </tr>
        </table>
    </div>

    @if ($pos_ped < count($pedidos) - 1)
        <div style="page-break-after:always;"></div>
    @endif
@endforeach
