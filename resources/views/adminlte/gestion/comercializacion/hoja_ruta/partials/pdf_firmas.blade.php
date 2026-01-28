@php
    $hoja_ruta = $data['hoja_ruta'];
    $conductor = $hoja_ruta->conductor;
@endphp
<table style="width:100%">
    <tr>
        <td style="text-align: center">
            <img src="{{ public_path('images/img_nintanga_despacho.png') }}" style="width: 300px" />
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        @php setlocale(LC_TIME,"es_ES.UTF-8") @endphp

        <td style="text-align: right">Guaytacama, {{ convertDateToText($hoja_ruta->fecha) }}</td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td>Estimados Señores,</td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td>
            La empresa NINTANGA S.A. solicita, vuestra gentil colaboración en la verificación de la numeración del SELLO
            DE SEGURIDAD con el que ingresa el camión que trasporta nuestras cajas de flores y que es conducido por el
            Sr {{ strtoupper($conductor->nombre) }}.
            <br /> Dicha confirmación, es de vital importancia para la seguridad de nuestra empresa, por lo que le pido,
            que la información sea clara y legible.
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td>Fecha: {{ $hoja_ruta->fecha }}</td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td>Transportista: {{ strtoupper($conductor->nombre) }}</td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td>Placa del camión: {{ strtoupper($hoja_ruta->placa) }}</td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td>En caso de no coincidir con esta numeración, registrar el otro número: ................................</td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td>Observaciones:
            ........................................................................................................................
        </td>
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
</table>

<table style="width:100%; font-size: 11px; border: none; position: relative; top: 5px">
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
