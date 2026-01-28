@php
    $totalPiezas = 0;
    $ramos_estandar = 0;
    $full_equivalente_real = 0;
    $empresa = getConfiguracionEmpresa();
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
                        <td style="font-size: 11px;">CURRIER : {{ $pedido->detalles[0]->agencia_carga->nombre }}</td>
                    </tr>
                    {{-- <tr>
                    <td style="font-size: 11px;">PHONE : </td>
                </tr> --}}
                    <tr>
                        <td style="font-size: 11px;"><b>SOLD TO : {{ $pedido->cliente->detalle()->nombre }}</b></td>
                    </tr>
                    <tr>
                        <td style="font-size: 11px;">CONSIGNEE :
                            {{ isset($pedido->envios[0]) && isset($pedido->envios[0]->consignatario) ? $pedido->envios[0]->consignatario->nombre : '' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 11px;">ADDRESS :
                            {{ isset($pedido->envios[0]) && isset($pedido->envios[0]->consignatario) ? $pedido->envios[0]->consignatario->direccion : '' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 11px;"><b>NOTIFY : SPRING</b></td>
                    </tr>
                    <tr>
                        <td style="font-size: 11px;">CONTACT PERSON : </td>
                    </tr>
                    <tr>
                        <td style="font-size: 11px;">CITY COUNTRY : {{ $pedido->cliente->detalle()->pais->nombre }}</td>
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
                        <td>DATE: {{ $pedido->fecha_pedido }}</td>
                    </tr>
                    <tr>
                        <td>M.A.W.B: {{ isset($pedido->envios[0]) ? $pedido->envios[0]->guia_madre : '' }}</td>
                    </tr>
                    <tr>
                        <td>H.A.W.B: {{ isset($pedido->envios[0]) ? $pedido->envios[0]->guia_hija : '' }}</td>
                    </tr>
                    <tr>
                        <td>AIR LINE: </td>
                    </tr>
                    <tr>
                        <td>DAE N.: {{ isset($pedido->envios[0]) ? $pedido->envios[0]->dae : '' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table style="width:100%;font-family: arial, sans-serif;border-collapse: collapse;">
        <thead style="border: 1px solid black">
            @php
                $datosExportacion = [];
                foreach ($pedido->detalles as $det_ped) {
                    foreach ($det_ped->detalle_pedido_dato_exportacion as $de) {
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
            @foreach ($pedido->detalles as $x => $det_ped)
                @php
                    $totalPiezas += $det_ped->cantidad;
                    $cajaInicio = $cajaFin + 1;
                    $cajaFin += $det_ped->cantidad;
                @endphp
                @foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $y => $esp_emp)
                    @foreach ($esp_emp->detalles as $z => $det_esp_emp)
                        @php
                            
                            $ramos_modificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);
                            
                            $distribucionAssorted = \yura\Modelos\DistribucionMixtos::where('ramos', '>', 0)
                                //->select('id_planta', 'siglas')->distinct()
                                ->where('fecha', opDiasFecha('-', 1, $pedido->fecha_pedido))
                                ->where('id_cliente', $pedido->id_cliente)
                                ->where('id_pedido', $pedido->id_pedido)
                                ->where('id_detalle_pedido', $det_ped->id_detalle_pedido)
                                ->where('id_detalle_especificacionempaque', $det_esp_emp->id_detalle_especificacionempaque)
                                ->get();
                        @endphp
                        @if ($z == 0)
                            @php $full_equivalente_real += explode("|",$esp_emp->empaque->nombre)[1]* $det_ped->cantidad; @endphp
                        @endif
                        @if ($distribucionAssorted->count())
                            @foreach ($distribucionAssorted as $disAssorted)
                                <tr>
                                    <td style="padding-left: 5px;font-size:11px;border:1px solid black">
                                        @if ($y == 0)
                                            @switch(explode('|',$esp_emp->empaque->nombre)[1])
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
                                        <td style="padding-left: 5px;font-size:11px;border:1px solid black; font-size: 0.6em">
                                            @php $ddE= $det_ped->detalle_pedido_dato_exportacion->where('id_dato_exportacion',$de['id_dato_exportacion'])->first() @endphp
                                            {{ isset($ddE) ? $ddE->valor : '' }}
                                        </td>
                                    @endforeach
                                    <td style="padding-left: 5px;font-size:11px;border:1px solid black">
                                        {{ $disAssorted->planta->nombre }}
                                    </td>
                                    <td style="padding-left: 5px;font-size:11px;border:1px solid black;">
                                        {{ $disAssorted->variedad()->nombre }} - ASSORTED
                                    </td>
                                    <td style="padding-left: 5px;font-size:11px;border:1px solid black;">
                                        {{ $det_esp_emp->empaque_p->nombre }}
                                    </td>
                                    <td
                                        style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                        {{ $det_esp_emp->tallos_x_ramos }}
                                    </td>
                                    <td
                                        style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                        @if ($det_esp_emp->longitud_ramo == 90)
                                            {{ $disAssorted->ramos * $det_ped->cantidad }}
                                        @endif
                                    </td>
                                    <td
                                        style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                        @if ($det_esp_emp->longitud_ramo == 80)
                                            {{ $disAssorted->ramos * $det_ped->cantidad }}
                                        @endif
                                    </td>
                                    <td
                                        style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                        @if ($det_esp_emp->longitud_ramo == 70)
                                            {{ $disAssorted->ramos * $det_ped->cantidad }}
                                        @endif
                                    </td>
                                    <td
                                        style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                        @if ($det_esp_emp->longitud_ramo == 60)
                                            {{ $disAssorted->ramos * $det_ped->cantidad }}
                                        @endif
                                    </td>
                                    <td
                                        style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                        @if ($det_esp_emp->longitud_ramo == 50)
                                            {{ $disAssorted->ramos * $det_ped->cantidad }}
                                        @endif
                                    </td>
                                    <td
                                        style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                        @if ($det_esp_emp->longitud_ramo == 0)
                                            {{ $disAssorted->ramos * $det_ped->cantidad }}
                                        @endif
                                    </td>
                                    <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                        {{$disAssorted->ramos}}
                                    </td>
                                    <td
                                        style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                        {{ $disAssorted->ramos * $det_ped->cantidad }}
                                    </td>
                                    <td
                                        style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:right ">
                                        {{ $disAssorted->tallos }}
                                    </td>
                                </tr>
                                @php
                                    $totalRamos += $disAssorted->ramos * $det_ped->cantidad;
                                    $totalTallos += $disAssorted->tallos;
                                @endphp
                            @endforeach
                        @else
                            <tr>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black">
                                    @if ($y == 0)
                                        @switch(explode('|',$esp_emp->empaque->nombre)[1])
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
                                    <td style="padding-left: 5px;font-size:11px;border:1px solid black; font-size: 0.6em">
                                        @php $ddE= $det_ped->detalle_pedido_dato_exportacion->where('id_dato_exportacion',$de['id_dato_exportacion'])->first() @endphp
                                        {{ isset($ddE) ? $ddE->valor : '' }}
                                    </td>
                                @endforeach
                                <td
                                    style="padding-left: 5px;font-size:11px;padding-left: 5px;font-size:11px;border:1px solid black;">
                                    {{ $det_esp_emp->variedad->planta->nombre }}
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;">
                                    {{ $det_esp_emp->variedad->nombre }}
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;">
                                    {{ $det_esp_emp->empaque_p->nombre }}
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                    {{ $det_esp_emp->tallos_x_ramos }}
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                    @if ($det_esp_emp->longitud_ramo == 90)
                                        {{ $det_ped->cantidad * (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad) }}
                                    @endif
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                    @if ($det_esp_emp->longitud_ramo == 80)
                                        {{ $det_ped->cantidad * (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad) }}
                                    @endif
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                    @if ($det_esp_emp->longitud_ramo == 70)
                                        {{ $det_ped->cantidad * (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad) }}
                                    @endif
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                    @if ($det_esp_emp->longitud_ramo == 60)
                                        {{ $det_ped->cantidad * (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad) }}
                                    @endif
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                    @if ($det_esp_emp->longitud_ramo == 50)
                                        {{ $det_ped->cantidad * (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad) }}
                                    @endif
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                    @if ($det_esp_emp->longitud_ramo == 0)
                                        {{ $det_ped->cantidad * (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad) }}
                                    @endif
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                    {{$det_esp_emp->cantidad}}
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:center">
                                    {{ $det_ped->cantidad * (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad) }}
                                </td>
                                <td style="padding-left: 5px;font-size:11px;border:1px solid black;text-align:right">
                                    {{ $det_ped->cantidad * (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad) * $det_esp_emp->tallos_x_ramos }}
                                </td>
                            </tr>
                            @php
                                $totalRamos += $det_ped->cantidad * (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad);
                                $totalTallos += $det_ped->cantidad * (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad) * $det_esp_emp->tallos_x_ramos;
                            @endphp
                        @endif
                    @endforeach
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
