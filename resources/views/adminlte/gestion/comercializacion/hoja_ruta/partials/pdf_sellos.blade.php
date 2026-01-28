@php
    $hoja_ruta = $data['hoja_ruta'];
    $conductor = $hoja_ruta->conductor;
@endphp
<table style="width:100%; font-size: 11px; border: none; position: relative; top: -20px">
    @foreach ($hoja_ruta->sellos as $ag => $sello)
        @if ($sello->sello != '')
            <tr>
                <td colspan="3"><u>Número del sello de seguridad: {{ $sello->sello }}</u></td>
            </tr>
            <tr>
                <td></td>
            </tr>
            <tr>
                <td></td>
            </tr>
            <tr>
                <td>DESPACHO NINTANGA </td>
                <td>TRANSPORTISTA </td>
                <td>{{ $sello->agencia_carga->nombre }}</td>
            </tr>
            <tr>
                <td></td>
            </tr>
            <tr>
                <td></td>
            </tr>
            <tr>
                <td></td>
            </tr>
            <tr>
                <td></td>
            </tr>
            <tr>
                <td></td>
            </tr>
            <tr>
                <td></td>
            </tr>
            <tr>
                <td></td>
            </tr>
            <tr>
                <td></td>
            </tr>
            <tr>
                <td>
                    ...........................................<br />
                    {{ strtoupper($hoja_ruta->responsable) }}<br />
                    {{ $hoja_ruta->identificacion_responsable }}
                </td>

                </td>
                <td>
                    ...........................................<br />
                    {{ strtoupper($conductor->nombre) }}<br />
                    {{ $conductor->ruc }}
                </td>
                <td style="vertical-align: top">
                    AG...........................................
                </td>
            </tr>
        @endif
    @endforeach
</table>
