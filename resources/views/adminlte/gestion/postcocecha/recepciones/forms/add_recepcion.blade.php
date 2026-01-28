<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_add_recepcion">
    <tr>
        <th class="text-center th_yura_green">
            Variedad/Color
        </th>
        <th class="text-center th_yura_green" style="width: 100px">
            Mallas
        </th>
        <th class="text-center th_yura_green" style="width: 100px">
            Tallos x Malla
        </th>
        <th class="text-center bg-yura_dark" style="width: 100px">
            SOBRANTES
        </th>
    </tr>
    @php
        $num_row = 1;
        $existe_sobrantes = false;
    @endphp
    @foreach ($plantas as $pos_p => $p)
        @foreach ($p['longitudes'] as $pos_l => $long)
            <tr>
                <th class="padding_lateral_5 bg-yura_dark mouse-hand" colspan="4"
                    onclick="$('.tr_planta_{{ $p['planta']->id_planta }}_{{ $long->nombre }}').toggleClass('hidden')">
                    {{ $p['planta']->nombre }} {{ $long->nombre }} cm
                    <i class="fa fa-fw fa-caret-down pull-right"></i>
                </th>
            </tr>
            @foreach ($p['planta']->variedades->where('estado', 1)->where('assorted', 0)->sortBy('orden') as $pos_v => $var)
                @php
                    $sobrante = getSobranteByVariedadLongitudFecha($var->id_variedad, $long->nombre, $fecha);
                    if (isset($sobrante)) {
                        $existe_sobrantes = true;
                    }
                @endphp
                <tr class="tr_planta_{{ $p['planta']->id_planta }}_{{ $long->nombre }} hidden">
                    <td class="text-right padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $var->nombre }} {{ $long->nombre }} cm
                        <input type="hidden" class="num_row" value="{{ $num_row }}">
                        <input type="hidden" id="variedad_{{ $num_row }}" value="{{ $var->id_variedad }}">
                        <input type="hidden" id="longitud_{{ $num_row }}" value="{{ $long->nombre }}">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="text-center" id="mallas_{{ $num_row }}">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="text-center" id="tallos_{{ $num_row }}">
                    </td>
                    <th class="text-center">
                        <input type="number" class="text-center bg-yura_dark" id="sobrante_{{ $num_row }}"
                            min="1" required style="width: 100%"
                            value="{{ isset($sobrante) ? $sobrante->cantidad : '' }}">
                    </th>
                </tr>
                @php
                    $num_row++;
                @endphp
            @endforeach
        @endforeach
    @endforeach
</table>

<div class="text-center" style="margin-top: 5px">
    <div class="btn-group">
        <button type="button" class="btn btn-yura_primary" onclick="store_recepcion()">
            <i class="fa fa-fw fa-save"></i> Grabar COSECHA
        </button>
        <button type="button" class="btn btn-yura_dark" {{ $existe_sobrantes ? 'disabled' : '' }}
            @if (!$existe_sobrantes) onclick="store_sobrantes()" @endif>
            <i class="fa fa-fw fa-save"></i> Grabar SOBRANTES
        </button>
    </div>
</div>

<script>
    function store_recepcion() {
        num_row = $('.num_row');
        data = [];
        for (i = 0; i < num_row.length; i++) {
            pos = num_row[i].value;
            mallas = $('#mallas_' + pos).val();
            tallos_x_malla = $('#tallos_' + pos).val();
            if (tallos_x_malla > 0 && mallas > 0) {
                variedad = $('#variedad_' + pos).val();
                longitud = $('#longitud_' + pos).val();
                data.push({
                    variedad: variedad,
                    longitud: longitud,
                    mallas: mallas,
                    tallos_x_malla: tallos_x_malla,
                });
            }
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                fecha: $('#filtro_fecha').val(),
                data: data,
            }
            post_jquery_m('{{ url('recepcion/store_recepcion') }}', datos, function() {
                cerrar_modals();
                buscar_listado_recepcion();
            });
        } else {
            alerta('<div class="text-center alert alert-warning">Faltan datos necesarios</div>')
        }
    }

    function store_sobrantes() {
        num_row = $('.num_row');
        data = [];
        for (i = 0; i < num_row.length; i++) {
            pos = num_row[i].value;
            sobrante = $('#sobrante_' + pos).val();
            if (sobrante > 0) {
                variedad = $('#variedad_' + pos).val();
                longitud = $('#longitud_' + pos).val();
                data.push({
                    variedad: variedad,
                    longitud: longitud,
                    sobrante: sobrante,
                });
            }
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                fecha: $('#filtro_fecha').val(),
                data: JSON.stringify(data),
            }
            post_jquery_m('{{ url('recepcion/store_sobrantes') }}', datos, function() {
                cerrar_modals();
            });
        } else {
            alerta('<div class="text-center alert alert-warning">Faltan datos necesarios</div>')
        }
    }
</script>
