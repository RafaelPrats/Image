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

        <td style="text-align: right">Guaytacama, {{ convertDateToText($data['despacho']->fecha_despacho) }}</td>
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
            Sr {{ strtoupper($data['despacho']->conductor->nombre) }}.
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
        <td>Fecha: {{ $data['despacho']->fecha_despacho }}</td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td>Transportista: {{ strtoupper($data['despacho']->conductor->nombre) }}</td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td>Placa del camión: {{ strtoupper(getCamion($data['despacho']->id_camion)->placa) }}</td>
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
<table style="width:100%">
    @php
        $arrs = explode('/', $data['despacho']->sellos);
        $sellos = [];

        foreach ($arrs as $key => $arr) {
            if (isset(explode(',', $arr)[1])) {
                $s = explode(',', $arr);
                $ag = yura\Modelos\AgenciaCarga::find(explode(',', $arr)[0]);
                unset($s[0]);
                $sellos[$ag->nombre] = $s;
            }
        }

    @endphp

    @foreach ($sellos as $ag => $sello)
        @foreach ($sello as $s)
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
                <td colspan="3"><u>Número del sello de seguridad: {{ $s }}</u></td>
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
                <td>{{ $ag }}</td>
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
                    .................................<br />
                    {{ strtoupper($data['despacho']->resp_ofi_despacho) }}<br />
                    {{ $data['despacho']->id_resp_ofi_despacho }}
                </td>

                </td>
                <td>
                    @php $conductor = getChofer($data['despacho']->id_conductor) @endphp
                    .................................<br />
                    {{ strtoupper($conductor->nombre) }}<br />
                    {{ $conductor->ruc }}
                </td>
                <td style="vertical-align: top">
                    AG.................................
                </td>
            </tr>
        @endforeach
    @endforeach
</table>
