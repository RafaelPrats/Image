<div id="table_etiqueta" style="overflow-x: scroll; overflow-y: scroll; max-height: 700px">
    @if ($pedidos->count() > 0)
        <table width="100%" class="table-stripped table-bordered" style="border-color: #9d9d9d"
            id="table_content_etiqueta">
            <thead>
                <tr style="background-color: #dd4b39; color: white" id="tr_fija_top_0">
                    <th class="text-center th_yura_green" style="width:130px">
                        <input type="checkbox" id="exportar_todos" name="exportar_todos"
                            onchange="select_exportar(this);onChangeSelection();"
                            onclick="select_exportar(this);onChangeSelection();">
                        <label for="exportar_todos"> Seleccionar / Imp</label>
                    </th>
                    <th class="text-center th_yura_green padding_lateral_5" style="vertical-align: middle">
                        Packing
                    </th>
                    <th class="text-center th_yura_green padding_lateral_5" style="vertical-align: middle">
                        Observación
                    </th>
                    <th class="text-center th_yura_green" style="vertical-align: middle">
                        Cliente
                    </th>
                    <th class="text-center th_yura_green" style="vertical-align: middle">
                        País
                    </th>
                    <th class="text-center th_yura_green" style="vertical-align: middle">
                        Agencia de carga
                    </th>
                    <th class="text-center th_yura_green" style="vertical-align: middle">
                        Marcaciones
                    </th>
                    <th class="text-center th_yura_green" style="vertical-align: middle">
                        Piezas
                    </th>
                    @if (!isset($vista))
                        <th class="text-center th_yura_green" style="width:60px;vertical-align: middle">
                            <input type="checkbox" id="doble_todos" name="doble_todos"
                                onchange="select_doble(this);onChangeSelection();"
                                onclick="select_doble(this);onChangeSelection();">
                            <label for="doble_todos">Doble</label>
                        </th>
                    @else
                        <th class="text-center th_yura_green" style="width:130px">
                            Generar Etiqueta
                        </th>
                    @endif
                    <th class="text-center th_yura_green" style="vertical-align: middle">
                        PDF ETIQUETA
                    </th>
                    <th class="text-center th_yura_green" style="vertical-align: middle">
                        PDF PACKING
                    </th>
                </tr>
            </thead>
            <tbody id="tbody_etiquetas_facturas">
                @foreach ($pedidos as $x => $pedido)
                    <tr onmouseover="$(this).css('background-color','#add8e6')"
                        onmouseleave="$(this).css('background-color','')" id="tr_etiqueta_{{ $pedido->id_pedido }}">
                        <td style="border-color: #9d9d9d;vertical-align: middle;width:130px" class="text-center">
                            <input type="checkbox" name="pedido_seleccionado " value="{{ $pedido->id_pedido }}"
                                data-cantidad_piezas="{{ $pedido->getCajasFisicas() }}" onchange="onChangeSelection();"
                                class="pedido_seleccionado exportar" id="check_pedido_{{ $pedido->id_pedido }}">
                        </td>
                        <td style="border-color: #9d9d9d;vertical-align:middle;background-color:{{ $pedido->eitqueta_impresa ? '#03c504;color:white' : '' }}"
                            class="text-center">
                            {{ $pedido->packing }}
                        </td>
                        <td style="border-color: #9d9d9d;vertical-align:middle;background-color:{{ $pedido->eitqueta_impresa ? '#03c504;color:white' : '' }}"
                            class="text-center padding_lateral_5">
                            @if ($pedido->codigo_pais != 'EC')
                                <ul style="list-style: none;padding: 0;margin:0">
                                    @if (!isset($pedido->guia_madre) || $pedido->guia_madre == '')
                                        <li>*Falta guía madre</li>
                                    @endif
                                    @if (!isset($pedido->guia_hija) || $pedido->guia_hija == '')
                                        <li>*Falta guía hija</li>
                                    @endif
                                    @if (!isset($pedido->dae) || $pedido->dae == '')
                                        <li>*Falta Dae</li>
                                    @endif
                                    @if (!isPedidoDistribuido($pedido->id_pedido))
                                        <li class="error">*Falta Distribucion</li>
                                    @endif
                                    @if ($pedido->eitqueta_impresa)
                                        <b><i class="fa fa-tag"></i> ETIQUETE IMPRESA</b>
                                    @endif
                                </ul>
                            @endif
                        </td>
                        <td style="border-color: #9d9d9d" class="text-center filtro_cliente padding_lateral_5">
                            {{ $pedido->cli_nombre }}
                        </td>
                        <td style="border-color: #9d9d9d;vertical-align:middle;background-color:{{ isset($pedido->codigo_pais) && $pedido->codigo_pais != '' && $pedido->codigo_pais == 'EC' ? '#00a65a;color:white' : '' }}"
                            class="text-center">
                            {{ isset($pedido->codigo_pais) && $pedido->codigo_pais != '' ? getPais($pedido->codigo_pais)->nombre : '' }}
                        </td>
                        <td style="border-color: #9d9d9d" class="text-center filtro_agencia_carga">
                            {{ isset($pedido->detalles[0]) ? $pedido->detalles[0]->agencia_carga->nombre : '' }}
                        </td>
                        <td style="border-color: #9d9d9d; font-size: 0.8em" class="text-center">
                            @foreach($pedido->getMarcaciones() as $pos_m => $m)
                                @if($pos_m == 0)
                                    {{ $m }}
                                @else
                                    <br>
                                    {{ $m }}
                                @endif
                            @endforeach
                        </td>
                        <td style="border-color: #9d9d9d" class="text-center">
                            {{ $pedido->getCajasFisicas() }}
                        </td>
                        @if (!isset($vista))
                            <td style="border-color: #9d9d9d;width:60px" class="text-center">
                                <input type="checkbox" name="doble_{{ $x + 1 }}"
                                    data-id_pedido="{{ $pedido->id_pedido }}" class="doble double_print_checkbox"
                                    id="doble_{{ $x + 1 }}" onchange="onChangeSelection();"
                                    {{ $pedido->impresion == 'doble' ? 'checked' : '' }}>
                            </td>
                        @else
                            <td style="border-color: #9d9d9d;width:60px" class="text-center">
                                <button type="button" class="btn btn-primary btn-xs"
                                    title="Generar etiqueta por factura"
                                    onclick="form_etiqueta_factura('{{ $item->id_comprobante }}')">
                                    <i class="fa fa-file-excel-o"></i>
                                </button>
                            </td>
                        @endif
                        <td style="border-color: #9d9d9d;width:60px;padding: 8px;" class="text-center">
                            <button type="button" class="btn btn-xs btn-yura_dark"
                                onclick="pdf_etiqueta('{{ $pedido->id_pedido }}')">
                                NEW
                            </button>
                            <a target="_blank" style="color: black"
                                onclick="descarga_pdf(event, [{id_pedido: {{ $pedido->id_pedido }}, isDoublePage: $('#doble_{{ $x + 1 }}').is(':checked')}])">
                                <button type="button" class="btn btn-xs btn-yura_danger btn-descargar-pdf">
                                    <i class="fa fa-download"></i> Descargar PDF
                                </button>
                            </a>
                            <div class="progress" style="height: 24px; margin-top: 10px;">
                                <div id="progress-bar_{{ $pedido->id_pedido }}"
                                    class="progress-bar progress-bar-striped active {{ $pedido->etiqueta_descargada == '1' ? 'progress-bar-full' : '' }}"
                                    role="progressbar" aria-valuemin="0" aria-valuemax="100">
                                    <span class="sr-only"
                                        style="position: unset;">{{ $pedido->etiqueta_descargada == '1' ? 'Descargado' : '' }}</span>
                                </div>
                            </div>
                        </td>
                        <td style="border-color: #9d9d9d;width:60px" class="text-center">
                            <a target="_blank" style="color: black"
                                href="{{ url('pedidos/crear_packing_list', $pedido->id_pedido) }}">
                                <button type="button" class="btn btn-xs btn-yura_primary">
                                    <i class="fa fa-file-pdf-o"></i>
                                </button>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if (!isset($vista) && !es_server())
            <div class="text-center" style="margin-top: 20px;">
                <button type="button" class="btn btn-yura_primary" onclick="imprimir_etiquetas()">
                    <i class="fa fa-file-excel-o" aria-hidden="true"></i> Imprimir Etiqueta
                </button>
            </div>
        @endif
    @else
        <div class="alert alert-info text-center" style="margin-top: 15px">No se han encontrado coincidencias</div>
    @endif
</div>
<div style="padding-left: 8px;"><span id="box-selection">0</span><span> etiquetas seleccionadas. (Solo puede descargar
        hasta 200 etiquetas en un mismo PDF)</span></div>
<div class="text-center" style="margin-top: 20px;">
    <div class="btn-group">
        <button type="button" class="btn btn-yura_danger" style="display: flex;align-items: center;margin: 0 auto;"
            onclick="descargar_etiquetas_all(event)">
            <i class="fa fa-download" aria-hidden="true"></i>&nbsp;&nbsp;Descargar ETIQUETAS seleccionadas en
            PDF&nbsp;&nbsp;<div id="spinner-download-pdf" class="benchflow-spinner hidden"></div>
        </button>
        <button type="button" class="btn btn-yura_warning" style="display: flex;align-items: center;margin: 0 auto;"
            onclick="descargar_all_packings(event)">
            <i class="fa fa-download" aria-hidden="true"></i>&nbsp;&nbsp;Descargar PACKINGs seleccionadas en
            PDF&nbsp;&nbsp;<div id="spinner-download-pdf" class="benchflow-spinner hidden"></div>
        </button>
    </div>
</div>

<script>
    function pdf_etiqueta(ped) {
        $.LoadingOverlay('show');
        window.open('{{ url('etiqueta/pdf_etiqueta') }}?pedido=' + ped, '_blank');
        $.LoadingOverlay('hide');
    }

    function descargar_all_packings() {
        $.LoadingOverlay('show');
        data = [];
        pedido_seleccionado = $('.pedido_seleccionado');
        for (i = 0; i < pedido_seleccionado.length; i++) {
            id = pedido_seleccionado[i].id;
            id_pedido = pedido_seleccionado[i].value;
            if ($('#' + id).prop('checked')) {
                data.push(id_pedido)
            }
        }
        data = JSON.stringify(data);
        window.open('{{ url('etiqueta/descargar_all_packings') }}?data=' + data, '_blank');
        $.LoadingOverlay('hide');
    }
</script>
