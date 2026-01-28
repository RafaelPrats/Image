<div style="overflow-x: scroll">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr id="tr_cortes">
            <th class="text-center th_yura_green columna_fija_left_0" rowspan="2">
                <div style="width: 150px">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Dias
                        </span>
                        <input type="number" id="dias_cosecha" required style="width: 100%"
                            class="form-control text-center" value="{{ $dias_cosecha_semana }}"
                            @if (es_server()) onchange="update_dias_cosecha_semana()" @endif
                            max="7" min="5">
                        <input type="hidden" id="semana_anterior" value="{{ $semana_anterior->codigo }}">
                    </div>
                </div>
            </th>
            <th class="text-center th_yura_green" style="width: 180px" rowspan="2">
                <div style="width: 100px">
                    SOLIDOS
                </div>
            </th>
            <th class="text-center columna_fija_left_1"
                style="width: 180px; background-color: #f39c12; border-color: black" rowspan="2">
                <div style="width: 100px">
                    MIXTOS ({{ number_format($tallos_mixtos) }})
                </div>
                <input type="hidden" id="tallos_mixtos" value="{{ $tallos_mixtos }}">
            </th>
            <th class="text-center th_yura_green" style="width: 180px" rowspan="2">
                <div style="width: 100px">
                    PROY SEM
                </div>
            </th>
            @foreach ($fechas as $pos => $f)
                <input type="hidden" class="fechas" value="{{ $pos }}">
                <th class="text-center th_yura_green" style="width: 180px; border-right: 2px solid" colspan="5">
                    {{ getDiaSemanaByFecha($f['fecha']) }}
                </th>
            @endforeach
        </tr>
        <tr>
            @php
                $total_solidos = 0;
                $total_mixtos = 0;
                $total_proy = 0;
                $totales = [];
            @endphp
            @foreach ($fechas as $pos => $f)
                @php
                    $totales[] = [
                        'solidos' => 0,
                        'mixtos' => 0,
                        'proy' => 0,
                        'saldo' => 0,
                    ];
                @endphp
                <th class="text-center bg-yura_dark" style="width: 180px">
                    <div style="width: 100px">
                        SOLIDOS
                    </div>
                </th>
                <th class="text-center bg-yura_dark" style="width: 180px">
                    <div style="width: 130px">
                        MIXTOS ({{ number_format($f['mixtos']) }})
                    </div>
                    <input type="hidden" id="mixto_fecha_{{ $pos }}" value="{{ $f['mixtos'] }}">
                </th>
                <th class="text-center bg-yura_dark" style="width: 180px">
                    <div style="width: 100px">
                        TOTAL
                    </div>
                </th>
                <th class="text-center bg-yura_dark" style="width: 180px">
                    <div style="width: 100px">
                        PROY
                    </div>
                </th>
                <th class="text-center {{ $pos == count($fechas) - 1 ? 'columna_fija_right_0' : 'bg-yura_dark' }}"
                    style="width: 180px; border-right: 2px solid; {{ $pos == count($fechas) - 1 ? 'background-color: #f39c12; border-color: black' : '' }}">
                    <div style="width: 100px">
                        SALDO
                    </div>
                </th>
            @endforeach
        </tr>
        @foreach ($listado as $item)
            @php
                $mixtos = $item['mixtos'] != '' ? $item['mixtos']->cantidad : porcentaje($item['distribucion'], $tallos_mixtos, 2);
                $total_mixtos += $mixtos;
                $total_solidos += $item['pedidos_solidos'];
                $total_proy += $item['proy_sem'];
                $saldo = 0;
            @endphp
            <input type="hidden" class="ids_variedades" value="{{ $item['var']->siglas }}">
            <tr id="tr_var_{{ $item['var']->siglas }}">
                <th class="text-center bg-yura_dark columna_fija_left_0">
                    {{ $item['var']->nombre }}
                </th>
                <th class="text-center bg-yura_dark">
                    {{ number_format($item['pedidos_solidos']) }}
                </th>
                <th class="text-center columna_fija_left_1">
                    <input type="number" style="width: 100%; background-color: #efc27c; border-color: black"
                        class="text-center" value="{{ round($mixtos) }}" id="mixtos_var_{{ $item['var']->siglas }}"
                        onchange="calcular_totales()" onkeyup="calcular_totales()" min="0" required>
                </th>
                <th class="text-center bg-yura_dark">
                    {{ number_format($item['proy_sem']) }}
                </th>
                @foreach ($item['valores'] as $pos => $val)
                    @php
                        $proy = round($item['proy_sem'] / 7);
                        // es LUNES
                        if ($dias_cosecha_semana == 5) {
                            if (date('N', strtotime($fechas[$pos]['fecha'])) == 1) {
                                $proy = $proy * 3;
                            } elseif (in_array(date('N', strtotime($fechas[$pos]['fecha'])), [6, 7])) {
                                $proy = 0;
                            }
                        } elseif ($dias_cosecha_semana == 6) {
                            if (date('N', strtotime($fechas[$pos]['fecha'])) == 1) {
                                $proy = $proy * 2;
                            } elseif (in_array(date('N', strtotime($fechas[$pos]['fecha'])), [7])) {
                                $proy = 0;
                            }
                        }
                        $mixtos_f = porcentaje($item['distribucion'], $fechas[$pos]['mixtos'], 2);
                        $saldo += $proy - $val['solidos'] - $mixtos_f;
                        $totales[$pos]['solidos'] += $val['solidos'];
                        $totales[$pos]['mixtos'] += $mixtos_f;
                        $totales[$pos]['proy'] += $proy;
                    @endphp
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" class="text-center" style="width: 100%" value="{{ $val['solidos'] }}"
                            disabled id="solidos_var_{{ $item['var']->siglas }}_fecha_{{ $pos }}">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" class="text-center" style="width: 100%" value="{{ $mixtos_f }}"
                            readonly id="mixtos_var_{{ $item['var']->siglas }}_fecha_{{ $pos }}">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" class="text-center" style="width: 100%"
                            value="{{ $val['solidos'] + $mixtos_f }}" readonly disabled
                            id="total_var_{{ $item['var']->siglas }}_fecha_{{ $pos }}">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" class="text-center" style="width: 100%" value="{{ $proy }}"
                            disabled id="proy_var_{{ $item['var']->siglas }}_fecha_{{ $pos }}">
                    </td>
                    <th class="text-center {{ $pos == count($fechas) - 1 ? 'columna_fija_right_0' : '' }}"
                        style="border-color: #9d9d9d; border-right: 2px solid">
                        <input type="number" class="text-center"
                            style="width: 100%; {{ $pos == count($fechas) - 1 ? 'background-color: #efc27c; border-color: black' : '' }}"
                            value="{{ $saldo }}" readonly
                            id="saldo_var_{{ $item['var']->siglas }}_fecha_{{ $pos }}">
                    </th>
                @endforeach
            </tr>
        @endforeach
        <tr>
            <th class="text-center th_yura_green columna_fija_left_0">
                TOTALES
            </th>
            <th class="text-center th_yura_green">
                {{ number_format($total_solidos) }}
            </th>
            <th class="text-center columna_fija_left_1">
                <input type="number" readonly style="width: 100%; background-color: #f39c12; border-color: black"
                    class="text-center" value="{{ $total_mixtos }}" id="total_mixtos">
            </th>
            <th class="text-center th_yura_green">
                {{ number_format($total_proy) }}
            </th>
            @php
                $saldo_total = 0;
            @endphp
            @foreach ($totales as $pos => $val)
                @php
                    $saldo_total += $val['proy'] - $val['solidos'] - $val['mixtos'];
                @endphp
                <th class="text-center bg-yura_dark">
                    {{ number_format($val['solidos']) }}
                </th>
                <th class="text-center">
                    <input type="number" readonly style="width: 100%" class="text-center bg-yura_dark"
                        value="{{ $val['mixtos'] }}" id="total_mixtos_{{ $pos }}">
                </th>
                <th class="text-center">
                    <input type="number" readonly style="width: 100%" class="text-center bg-yura_dark"
                        value="{{ $val['solidos'] + $val['mixtos'] }}" id="total_fecha_{{ $pos }}"
                        disabled>
                </th>
                <th class="text-center bg-yura_dark">
                    {{ number_format($val['proy']) }}
                </th>
                <th class="text-center {{ $pos == count($fechas) - 1 ? 'columna_fija_right_0' : '' }}"
                    style="border-right: 2px solid">
                    <input type="number" readonly
                        style="width: 100%; {{ $pos == count($fechas) - 1 ? 'background-color: #f39c12; border-color: black' : '' }}"
                        class="text-center {{ $pos == count($fechas) - 1 ? '' : 'bg-yura_dark' }}"
                        value="{{ $saldo_total }}" id="total_saldos_{{ $pos }}">
                </th>
            @endforeach
        </tr>
    </table>
</div>

@if (es_server())
    <div class="text-center" style="margin-top: 5px">
        <button type="button" class="btn btn-yura_primary" onclick="store_distribucion()">
            <i class="fa fa-fw fa-save"></i> Guardar
        </button>
    </div>
@endif

<style>
    .columna_fija_left_0 {
        position: sticky;
        left: 0;
        z-index: 9;
    }

    .columna_fija_right_0 {
        position: sticky;
        right: 0;
        z-index: 9;
    }

    .columna_fija_left_1 {
        position: sticky;
        left: 150px;
        z-index: 9;
    }
</style>

<script>
    calcular_totales();

    function update_dias_cosecha_semana() {
        datos = {
            _token: '{{ csrf_token() }}',
            semana: $('#semana_anterior').val(),
            cantidad: $('#dias_cosecha').val(),
        };
        post_jquery_m('{{ url('distribucion_semana/update_dias_cosecha_semana') }}', datos, function() {
            listar_formulario();
        }, 'dias_cosecha');
    }

    function calcular_totales() {
        fechas = $('.fechas');
        variedades = $('.ids_variedades');
        mixtos_total = parseInt($('#tallos_mixtos').val());

        totales_mixtos = 0;
        totales = [];
        for (x = 0; x < fechas.length; x++)
            totales.push({
                solidos: 0,
                mixtos: 0,
                saldos: 0,
            });

        for (v = 0; v < variedades.length; v++) {
            id_var = variedades[v].value;
            mixtos_var = parseInt($('#mixtos_var_' + id_var).val());
            totales_mixtos += mixtos_var;
            porcentaje = (mixtos_var / mixtos_total) * 100;
            porcentaje = Math.round(porcentaje * 100) / 100;
            saldo_var_fecha = 0;
            for (f = 0; f < fechas.length; f++) {
                fecha = fechas[f].value;
                mixto_fecha = parseInt($('#mixto_fecha_' + fecha).val());
                mixto_var_fecha = mixto_fecha > 0 ? parseInt((porcentaje * mixto_fecha) / 100) : 0;
                $('#mixtos_var_' + id_var + '_fecha_' + fecha).val(mixto_var_fecha);
                solidos_var_fecha = parseInt($('#solidos_var_' + id_var + '_fecha_' + fecha).val());
                total_var_fecha = parseInt(solidos_var_fecha + mixto_var_fecha);
                $('#total_var_' + id_var + '_fecha_' + fecha).val(total_var_fecha);
                proy_var_fecha = parseInt($('#proy_var_' + id_var + '_fecha_' + fecha).val());
                saldo_var_fecha += parseInt(proy_var_fecha - solidos_var_fecha - mixto_var_fecha);
                $('#saldo_var_' + id_var + '_fecha_' + fecha).val(saldo_var_fecha);
                totales[f]['solidos'] += solidos_var_fecha;
                totales[f]['mixtos'] += mixto_var_fecha;
                totales[f]['saldos'] += saldo_var_fecha;
            }
        }

        $('#total_mixtos').val(totales_mixtos);
        for (x = 0; x < fechas.length; x++) {
            $('#total_mixtos_' + x).val(totales[x]['mixtos']);
            $('#total_fecha_' + x).val(totales[x]['solidos'] + totales[x]['mixtos']);
            $('#total_saldos_' + x).val(totales[x]['saldos']);
        }
    }

    function store_distribucion() {
        variedades = $('.ids_variedades');
        mixtos_total = parseInt($('#tallos_mixtos').val());

        data = [];
        for (v = 0; v < variedades.length; v++) {
            id_var = variedades[v].value;
            mixtos_var = parseInt($('#mixtos_var_' + id_var).val());
            if (mixtos_var >= 0)
                data.push({
                    siglas: id_var,
                    cantidad: mixtos_var
                });
        }

        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                data: data,
                semana: $('#filtro_semana').val(),
                planta: $('#filtro_planta').val(),
                longitud: $('#filtro_longitud').val(),
            }
            post_jquery_m('{{ url('distribucion_semana/store_distribucion') }}', datos, function() {
                listar_formulario();
            });
        }
    }
</script>
