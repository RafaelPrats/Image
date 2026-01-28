<div id="table_etiqueta">
    @if ($pedidos->count() > 0)
        <table width="100%" class="table-responsive table-bordered" style="font-size: 0.8em; border-color: #9d9d9d"
            id="table_content_etiqueta">
            <thead>
                <tr style="background-color: #dd4b39; color: white">
                    <th class="text-center th_yura_green" style="vertical-align: middle">
                        <input type="checkbox" class="check_select_all_packing" onchange="check_filtro_guia_dae()"
                            style="width: 16px;height: 16px;">
                        PACKING
                    </th>
                    <th class="text-center th_yura_green" style="vertical-align: middle">
                        CLIENTE
                    </th>
                    <th class="text-center th_yura_green" style="vertical-align: middle">
                        FECHA
                    </th>
                    <th class="text-center th_yura_green" style="vertical-align: middle">
                        PIEZAS
                    </th>
                    <th class="text-center th_yura_green" style="vertical-align: middle">
                        CAJAS FULL
                    </th>
                    <th class="text-center th_yura_green" style="vertical-align: middle">
                        TOTAL CAJAS FULL
                    </th>
                    <th class="text-center th_yura_green" style="vertical-align: middle">
                        MARCACIONES
                    </th>
                    <th class="text-center th_yura_green" style="vertical-align: middle; width: 130px">
                        DAE
                    </th>
                    <th class="text-center th_yura_green" style="vertical-align: middle">
                        AGENCIA DE CARGA
                    </th>
                    <th class="text-center th_yura_green" style="vertical-align: middle">
                        PÁIS
                    </th>
                    <th class="text-center th_yura_green" style="vertical-align: middle">
                        GUÍA MADRE
                    </th>
                    <th class="text-center th_yura_green" style="vertical-align: middle">
                        GUÍA HIJA
                    </th>
                    <th class="text-center th_yura_green" style="vertical-align: middle">
                        AEROLINEA
                    </th>
                    <th class="text-center th_yura_green" style="vertical-align: middle">
                        CONSIGNATARIO
                    </th>
                </tr>
            </thead>
            <tbody id="tbody_etiquetas_facturas">
                @php $y = 0; @endphp
                @foreach ($pedidos as $x => $pedido)
                    @php
                        $envio = $pedido->envios[0];
                        $aerolinea = $envio->getAerolinea();
                        $marcaciones = [];
                        $p = $envio->id_consignatario == '' ? $pedido->pais_cliente : $envio->consignatario->codigo_pais;
                        
                        if (isset($envio->dae) && $envio->dae != '') {
                            $dae = $envio->dae;
                            $class_bg_dae = 'text-color_yura';
                        } else {
                            $mes = $carbon::parse(opDiasFecha('+', 1, $pedido->fecha_pedido))->format('m');
                            $anno = $carbon::parse(opDiasFecha('+', 1, $pedido->fecha_pedido))->format('Y');
                            $d = getCodigoDae(strtoupper($p), $mes, $anno);
                        
                            $dae = isset($d->codigo_dae) ? $d->codigo_dae : '';
                            $class_bg_dae = 'text-color_yura_danger';
                        }
                        
                        $consignatarios = $ClienteConsignatario
                            ::where('id_cliente', $pedido->id_cliente)
                            ->join('consignatario as c', 'cliente_consignatario.id_consignatario', 'c.id_consignatario')
                            ->get();
                        
                        if (isset($idCliente) && $idCliente == $pedido->id_cliente) {
                            $y++;
                        } else {
                            $y = 0;
                            $idCliente = $pedido->id_cliente;
                        }
                        
                        foreach ($pedido->detalles as $det_ped) {
                            foreach ($det_ped->detalle_pedido_dato_exportacion as $datoExp) {
                                if (!in_array($datoExp->valor, $marcaciones)) {
                                    $marcaciones[] = $datoExp->valor;
                                }
                            }
                        }
                        
                    @endphp
                    <tr onmouseover="$(this).css('background-color','#add8e6')"
                        onmouseleave="$(this).css('background-color','')"
                        class="tr_pedido {{ $dae == '' || $envio->guia_madre == '' || $envio->guia_hija == '' ? 'error' : '' }}"
                        data-id_envio="{{ $envio->id_envio }}" data-packing="{{ $pedido->packing }}"
                        style="display: table-row;">
                        <td style="border-color: #9d9d9d;vertical-align:middle;" class="text-center">
                            <input type="checkbox" class="check_guia_dae">
                            <br>
                            <b>{{ $pedido->packing }}</b>
                        </td>
                        <td style="border-color: #9d9d9d"
                            class="text-center id_cliente id_cliente_{{ $pedido->id_cliente }}">
                            {{ $pedido->cli_nombre }}
                        </td>
                        <td style="border-color: #9d9d9d"
                            class="text-center id_cliente id_cliente_{{ $pedido->id_cliente }}">
                            {{ explode('del ', convertDateToText($pedido->fecha_pedido))[0] }}
                        </td>
                        <td style="border-color: #9d9d9d;vertical-align:middle;" class="text-center">
                            {{ $pedido->getCajasFisicas() }}
                        </td>
                        <td style="border-color: #9d9d9d;vertical-align:middle;"
                            class="text-center cajas_full_cliente_{{ $pedido->id_cliente }}">
                            {{ $pedido->getCajasFull() }}
                        </td>
                        @if ($y == 0)
                            <td style="border-color: #9d9d9d;vertical-align:middle;"
                                class="text-center total_cajas_full_cliente"
                                data-id_cliente="{{ $pedido->id_cliente }}">
                                $pedido
                            </td>
                        @endif
                        <td style="border-color: #9d9d9d;vertical-align: middle" class="text-center">
                            @foreach ($marcaciones as $m)
                                <div>{{ $m }}</div>
                            @endforeach
                        </td>
                        <td style="border-color: #9d9d9d;vertical-align: middle" class="text-center">
                            <input type="text" class="form-control {{ $class_bg_dae }}" id="dae"
                                name="dae" value="{{ $dae }}" style="padding:0"
                                onchange="check_automatico(this)" onkeyup="check_automatico(this)">
                        </td>
                        <td style="border-color: #9d9d9d;vertical-align: middle"
                            class="text-center filtro_agencia_carga">
                            {{ isset($pedido->detalles[0]) ? $pedido->detalles[0]->agencia_carga->nombre : '' }}
                        </td>
                        <td style="border-color: #9d9d9d;vertical-align: middle" class="text-center">
                            <select id="codigo_pais" name="codigo_pais" class="form-control" required>
                                @foreach ($paises as $pais)
                                    <option {{ $p == $pais->codigo ? 'selected' : '' }} value="{{ $pais->codigo }}">
                                        {{ $pais->nombre }}</option>
                                @endforeach
                            </select>
                            <select id="aerolinea" name="aerolinea" class="form-control hide" required>
                                @isset($envio->detalles[0])
                                    @if ($envio->detalles[0]->id_aerolinea == null)
                                        <option selected disabled value="">Seleccione</option>
                                    @endif
                                    @foreach ($aerolineas as $a)
                                        <option {!! $envio->detalles[0]->id_aerolinea == $a->id_aerolinea ? 'selected' : '' !!} value="{{ $a->codigo }}"
                                            data-id_aerolinea="{{ $a->id_aerolinea }}">{{ $a->nombre }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </td>
                        <td style="border-color: #9d9d9d;vertical-align: middle" class="text-center">
                            <input type="text" class="form-control" id="guia_madre" name="guia_madre"
                                style="padding:0" value="{{ $envio->guia_madre }}"
                                onkeyup="set_aerolinea(this); check_automatico(this)"
                                onchange="set_aerolinea(this); check_automatico(this)">
                        </td>
                        <td style="border-color: #9d9d9d;vertical-align: middle" class="text-center">
                            <input type="text" class="form-control" id="guia_hija" name="guia_hija"
                                style="padding:0" value="{{ $envio->guia_hija }}" onchange="check_automatico(this)"
                                onkeyup="check_automatico(this)">
                        </td>
                        <td style="border-color: #9d9d9d;vertical-align: middle"
                            class="text-center padding_lateral_5">
                            {{ $aerolinea != '' ? $aerolinea->nombre : '' }}
                        </td>
                        <td style="border-color: #9d9d9d;vertical-align: middle" class="text-center">
                            <select class="form-control" id="consignatario" name="consignatario">
                                <option value=""> Mismo cliente </option>
                                @foreach ($consignatarios as $c)
                                    <option
                                        {{ $envio->id_consignatario != '' ? ($envio->id_consignatario == $c->id_consignatario ? 'selected' : '') : ($c->default ? 'selected' : '') }}
                                        value="{{ $c->id_consignatario }}">
                                        {{ $c->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            @if (es_server())
                <tr>
                    <td colspan="71 " class="text-center">
                        <button type="button" class="btn btn-yura_primary" style="margin-top:10px"
                            title="Ver tinturado" onclick="actualizar_envio_pedido()">
                            <i class="fa fa-floppy-o" aria-hidden="true"></i> GUARDAR GUÍAS Y DAES
                        </button>
                    </td>
                </tr>
            @endif
        </table>
    @else
        <div class="alert alert-info text-center" style="margin-top: 15px">No se han encontrado coincidencias</div>
    @endif
</div>

<script>
    $(document).ready(function() {

        $.each($("td.total_cajas_full_cliente"), function() {

            let idCliente = $(this).data('id_cliente')
            let rows = 0
            let total_cajas_full = 0

            $.each($("td.cajas_full_cliente_" + idCliente), function() {

                total_cajas_full += parseFloat($(this).html())
                rows++
            })

            $(this).html(total_cajas_full).attr('rowspan', rows)

        })

    })

    function check_automatico(input) {

        let tr = $(input).parent().parent()

        if (tr.find('input#dae').val().length > 0 && tr.find('input#guia_madre').val().length > 0 && tr.find(
                'input#guia_hija').val().length > 0)
            tr.find('input.check_guia_dae').prop('checked', true)
        else
            tr.find('input.check_guia_dae').prop('checked', false)

    }
</script>
