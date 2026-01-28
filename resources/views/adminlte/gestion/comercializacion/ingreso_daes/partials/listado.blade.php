<div style="overflow-x: scroll; overflow-y: scroll; width: 100%; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; font-size: 0.8em" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="text-center th_yura_green" style="width: 30px">
                    <input type="checkbox" id="check_all"
                        onchange="$('.check_proy').prop('checked', $(this).prop('checked'))">
                    PACKING
                </th>
                <th class="text-center th_yura_green">
                    CLIENTE
                </th>
                <th class="text-center th_yura_green">
                    FECHA
                </th>
                <th class="text-center th_yura_green" style="width: 40px">
                    PIEZAS
                </th>
                <th class="text-center th_yura_green" style="width: 40px">
                    CAJAS FULL
                </th>
                <th class="text-center th_yura_green">
                    MARCACIONES
                </th>
                <th class="text-center th_yura_green">
                    <div style="min-width: 100px">
                        DAE
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    AGENCIA de CARGA
                </th>
                <th class="text-center th_yura_green">
                    PAIS
                </th>
                <th class="text-center th_yura_green">
                    GUIA MADRE
                </th>
                <th class="text-center th_yura_green">
                    GUIA HIJA
                </th>
                <th class="text-center th_yura_green">
                    AEROLINEA
                </th>
                <th class="text-center th_yura_green">
                    CONSIGNATARIO
                </th>
            </tr>
        </thead>
        <tbody>
            @php
                $resumen_clientes = [];
                $resumen_agencias = [];
            @endphp
            @foreach ($listado as $item)
                @php
                    $cliente = $item->cliente->detalle();
                    $consignatario = $item->consignatario;
                    $agencia_carga = $item->agencia_carga;
                    if ($item->codigo_pais != '') {
                        $codigo_pais = $item->codigo_pais;
                    } elseif ($consignatario != '') {
                        $codigo_pais = $consignatario->codigo_pais;
                    } else {
                        $codigo_pais = '';
                    }
                    $dae = $item->dae != '' ? $item->dae : $item->getCodigoDae();

                    $getTotalPiezas = $item->getTotalPiezas();
                    $getTotalFulls = $item->getTotalFulls();

                    // resumen por cliente
                    $pos_en_resumen = -1;
                    foreach ($resumen_clientes as $pos => $r) {
                        if ($r['cliente'] == $cliente->nombre) {
                            $pos_en_resumen = $pos;
                        }
                    }
                    if ($pos_en_resumen != -1) {
                        $resumen_clientes[$pos_en_resumen]['piezas'] += $getTotalPiezas;
                        $resumen_clientes[$pos_en_resumen]['fulls'] += $getTotalFulls;
                    } else {
                        $resumen_clientes[] = [
                            'cliente' => $cliente->nombre,
                            'piezas' => $getTotalPiezas,
                            'fulls' => $getTotalFulls,
                        ];
                    }

                    // resumen por agencia
                    $pos_en_resumen = -1;
                    foreach ($resumen_agencias as $pos => $r) {
                        if ($r['agencia'] == $item->agencia_carga->nombre) {
                            $pos_en_resumen = $pos;
                        }
                    }
                    if ($pos_en_resumen != -1) {
                        $resumen_agencias[$pos_en_resumen]['piezas'] += $getTotalPiezas;
                        $resumen_agencias[$pos_en_resumen]['fulls'] += $getTotalFulls;
                    } else {
                        $resumen_agencias[] = [
                            'agencia' => $item->agencia_carga->nombre,
                            'piezas' => $getTotalPiezas,
                            'fulls' => $getTotalFulls,
                        ];
                    }
                @endphp
                <tr onmouseover="$(this).css('background-color', 'cyan')"
                    onmouseleave="$(this).css('background-color', '')"
                    class="{{ $item->dae == '' || $item->guia_madre == '' || $item->guia_hija == '' ? 'error' : '' }}">
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="checkbox" class="check_proy mouse-hand" id="check_proy_{{ $item->id_proyecto }}"
                            data-id_proy="{{ $item->id_proyecto }}">
                        <br>
                        {{ $item->packing }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $cliente->nombre }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ explode(' del ', convertDateToText($item->fecha))[0] }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $getTotalPiezas }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $getTotalFulls }}
                    </td>
                    <td class="padding_lateral_5" style="border-color: #9d9d9d">
                        @foreach ($item->getMarcaciones() as $val)
                            -{{ $val }} <br>
                        @endforeach
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="text" style="width: 100%; height: 26px;"
                            class="text-center {{ $item->dae == '' || $item->guia_madre == '' || $item->guia_hija == '' ? 'error' : '' }}"
                            value="{{ $dae }}" id="dae_{{ $item->id_proyecto }}">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $agencia_carga->nombre }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <select style="width: 100%; height: 26px;" id="pais_{{ $item->id_proyecto }}">
                            <option value="">Seleccione</option>
                            @foreach ($paises as $p)
                                <option value="{{ $p->codigo }}"
                                    {{ $codigo_pais == $p->codigo ? 'selected' : '' }}>
                                    {{ $p->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="text" style="width: 100%; height: 26px;"
                            class="text-center {{ $item->dae == '' || $item->guia_madre == '' || $item->guia_hija == '' ? 'error' : '' }}"
                            onkeyup="set_aerolinea('{{ $item->id_proyecto }}'); check_automatico('{{ $item->id_proyecto }}')"
                            value="{{ $item->guia_madre }}" id="guia_madre_{{ $item->id_proyecto }}">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="text" style="width: 100%; height: 26px;"
                            class="text-center {{ $item->dae == '' || $item->guia_madre == '' || $item->guia_hija == '' ? 'error' : '' }}"
                            value="{{ $item->guia_hija }}" id="guia_hija_{{ $item->id_proyecto }}"
                            onkeyup="set_aerolinea('{{ $item->id_proyecto }}'); check_automatico('{{ $item->id_proyecto }}')">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <select style="width: 100%; height: 26px;" id="aerolinea_{{ $item->id_proyecto }}">
                            <option value=""></option>
                            @foreach ($aerolineas as $p)
                                <option value="{{ $p->id_aerolinea }}"
                                    class="option_aerolinea_{{ $item->id_proyecto }}"
                                    data-codigo="{{ $p->codigo }}"
                                    {{ $item->id_aerolinea == $p->id_aerolinea ? 'selected' : '' }}>
                                    {{ $p->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $consignatario != '' ? $consignatario->nombre : '' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="text-center" style="margin-top: 5px">
    <div class="btn-group">
        <button type="button" class="btn btn-yura_primary" onclick="update_daes()">
            <i class="fa fa-fw fa-save"></i> GUARDAR
        </button>
    </div>
</div>

<legend style="margin-top: 5px; margin-bottom: 5px; font-size: 1.3em" class="text-center">
    <b>Resumenes</b>
</legend>
<div class="row">
    <div class="col-md-6">
        <div style="overflow-y: scroll; max-height: 450px">
            <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; font-size: 0.9em"
                id="table_resumen_clientes">
                <thead>
                    <tr class="tr_fija_top_0">
                        <th class="padding_lateral_5 th_yura_green">
                            Cliente
                        </th>
                        <th class="padding_lateral_5 th_yura_green" style="width: 70px">
                            Piezas
                        </th>
                        <th class="padding_lateral_5 th_yura_green" style="width: 70px">
                            Fulls
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($resumen_clientes as $r)
                        <tr onmouseover="$(this).css('background-color', 'cyan')"
                            onmouseleave="$(this).css('background-color', '')">
                            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                                {{ $r['cliente'] }}
                            </th>
                            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                                {{ $r['piezas'] }}
                            </th>
                            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                                {{ $r['fulls'] }}
                            </th>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-6">
        <div style="overflow-y: scroll; max-height: 450px">
            <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; font-size: 0.9em"
                id="table_resumen_agencias">
                <thead>
                    <tr class="tr_fija_top_0">
                        <th class="padding_lateral_5 th_yura_green">
                            Agencia
                        </th>
                        <th class="padding_lateral_5 th_yura_green" style="width: 70px">
                            Piezas
                        </th>
                        <th class="padding_lateral_5 th_yura_green" style="width: 70px">
                            Fulls
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($resumen_agencias as $r)
                        <tr onmouseover="$(this).css('background-color', 'cyan')"
                            onmouseleave="$(this).css('background-color', '')">
                            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                                {{ $r['agencia'] }}
                            </th>
                            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                                {{ $r['piezas'] }}
                            </th>
                            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                                {{ $r['fulls'] }}
                            </th>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    estructura_tabla('table_listado');
    estructura_tabla('table_resumen_clientes');
    estructura_tabla('table_resumen_agencias');

    function set_aerolinea(id_proy) {
        valor = $('#guia_madre_' + id_proy).val();
        if (valor.length >= 3) {
            valor = valor.substring(0, 3).toUpperCase();
            option_aerolinea = $('.option_aerolinea_' + id_proy);
            for (i = 0; i < option_aerolinea.length; i++) {
                codigo = option_aerolinea[i].getAttribute('data-codigo');
                if (codigo == valor) {
                    id_aerolinea = option_aerolinea[i].value;
                    $('#aerolinea_' + id_proy).val(id_aerolinea);
                }
            }
        }
    }

    function check_automatico(id_proy) {
        dae = $('#dae_' + id_proy).val();
        guia_madre = $('#guia_madre_' + id_proy).val();
        guia_hija = $('#guia_hija_' + id_proy).val();
        if (dae != '' && guia_madre != '' && guia_hija != '') {
            $('#check_proy_' + id_proy).prop('checked', true);
        } else {
            $('#check_proy_' + id_proy).prop('checked', false);
        }
    }

    function update_daes() {
        data = [];
        check_proy = $('.check_proy');
        for (i = 0; i < check_proy.length; i++) {
            id_proy = check_proy[i].getAttribute('data-id_proy');
            if ($('#check_proy_' + id_proy).prop('checked') == true) {
                dae = $('#dae_' + id_proy).val();
                pais = $('#pais_' + id_proy).val();
                guia_madre = $('#guia_madre_' + id_proy).val();
                guia_hija = $('#guia_hija_' + id_proy).val();
                aerolinea = $('#aerolinea_' + id_proy).val();
                data.push({
                    id: id_proy,
                    dae: dae,
                    pais: pais,
                    guia_madre: guia_madre,
                    guia_hija: guia_hija,
                    aerolinea: aerolinea,
                });
            }
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                data: JSON.stringify(data)
            }
            post_jquery_m('{{ url('ingreso_daes/update_daes') }}', datos, function() {
                listar_reporte();
            })
        }
    }
</script>
