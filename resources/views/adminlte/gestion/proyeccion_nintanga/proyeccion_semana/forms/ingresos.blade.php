<div style="overflow-y: scroll; overflow-x: scroll">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr id="tr_semanas">
            <th class="text-center th_yura_green col_fija_left_0" id="th_encabezado"
                rowspan="{{ count($semanas) > 0 ? 2 : 1 }}">
                <div class="text-left" style="width: 150px">
                    @if (es_server())
                        <div class="btn-group pull-left">
                            <button type="button" class="btn btn-xs btn-yura_default" title="Administrar Longitudes"
                                onclick="add_longitudes('{{ $planta }}')">
                                <i class="fa fa-fw fa-gears"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-yura_dark" title="Agregar semana"
                                onclick="add_semana()">
                                <i class="fa fa-fw fa-plus"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-yura_danger" title="Eliminar valores"
                                onclick="del_semanas()">
                                <i class="fa fa-fw fa-trash"></i>
                            </button>
                        </div>
                    @endif
                </div>
            </th>
            @foreach ($semanas as $pos_sem => $sem)
                <input type="hidden" class="pos_sem" value="{{ $pos_sem + 1 }}">
                <th class="text-center bg-yura_dark" colspan="{{ count($longitudes) }}">
                    <input type="text" id="semana_{{ $pos_sem + 1 }}" style="width: 100%"
                        class="text-center bg-yura_dark" value="{{ $sem->semana }}" placeholder="Semana">
                </th>
            @endforeach
        </tr>
        <tr id="tr_longitudes">
            @foreach ($semanas as $pos_sem => $sem)
                @foreach ($longitudes as $pos_c => $c)
                    <input type="hidden" class="pos_longitudes" value="{{ $pos_sem + 1 }}_{{ $pos_c + 1 }}">
                    <input type="hidden" id="longitudes_{{ $pos_sem + 1 }}_{{ $pos_c + 1 }}"
                        value="{{ $c->id_proy_longitudes }}">
                    <th class="text-center" style="background-color: #cecbcb; border-color: #9d9d9d">
                        <div class="text-center" style="width: 100px">
                            {{ $c->nombre }}
                        </div>
                    </th>
                @endforeach
            @endforeach
        </tr>
        @foreach ($listado as $item)
            <input type="hidden" class="ids_variedades" value="{{ $item['var']->siglas }}">
            <tr id="tr_var_{{ $item['var']->siglas }}">
                <th class="text-center bg-yura_dark col_fija_left_0">
                    {{ $item['var']->nombre }}
                </th>
                @foreach ($item['valores'] as $pos_sem => $v)
                    @php
                        $bg_color = ($pos_sem + 1) % 2 == 0 ? 'white' : '#b8efaa';
                    @endphp
                    @foreach ($v as $pos_c => $val)
                        <td class="text-center">
                            <input type="text" onkeypress="return isNumber(event)" onchange="calcular_totales_proy()"
                                onkeyup="calcular_totales_proy()"
                                id="cantidad_var_{{ $item['var']->siglas }}_semana_{{ $pos_sem + 1 }}_{{ $pos_c + 1 }}"
                                class="text-center input_proy"
                                style="width: 100%; background-color: {{ $bg_color }}"
                                value="{{ $val }}">
                        </td>
                    @endforeach
                @endforeach
            </tr>
        @endforeach
        <tr id="tr_totales">
            <th class="text-center th_yura_green col_fija_left_0">
                TOTALES
            </th>
            @foreach ($semanas as $pos_sem => $sem)
                @foreach ($longitudes as $pos_c => $c)
                    <th class="text-center bg-yura_dark" id="th_total_{{ $pos_sem + 1 }}_{{ $pos_c + 1 }}">
                        0
                    </th>
                @endforeach
            @endforeach
        </tr>
    </table>
</div>

@if (es_server())
    <div class="text-center" style="margin-top: 10px">
        <button type="button" class="btn btn-yura_primary sombra_pequeña" onclick="grabar_proy()">
            <i class="fa fa-fw fa-save"></i> Grabar
        </button>
    </div>
@endif

<script>
    var num_semanas = {{ count($semanas) }};
    calcular_totales_proy();

    function add_semana() {
        datos = {
            _token: '{{ csrf_token() }}',
            semana: $('#semana_' + num_semanas).val(),
        }
        $.post('{{ url('proyeccion_semana/add_semana') }}', datos, function(retorno) {
            num_semanas++;
            bg_color = num_semanas % 2 == 0 ? 'white' : '#b8efaa';
            $('#th_encabezado').attr('rowspan', 2);
            $('#tr_semanas').append('<th class="text-center" colspan="{{ count($longitudes) }}">' +
                '<input type="hidden" class="pos_sem" value="' + num_semanas + '">' +
                '<input type="text" id="semana_' + num_semanas + '" style="width: 100%" ' +
                'class="text-center bg-yura_dark" placeholder="Semana" value="' + retorno.semana + '">' +
                '</th>');
            @foreach ($longitudes as $pos_c => $c)
                $('#tr_longitudes').append(
                    '<th class="text-center" style="background-color: #cecbcb; border-color: black;">' +
                    '<input type="hidden" class="pos_longitudes" value="' + num_semanas +
                    '_{{ $pos_c + 1 }}">' +
                    '<input type="hidden" id="longitudes_' + num_semanas +
                    '_{{ $pos_c + 1 }}" value="{{ $c->id_proy_longitudes }}">' +
                    '<div class="text-center" style="width: 100px">' +
                    '{{ $c->nombre }}</div></th>');
            @endforeach
            ids_variedades = $('.ids_variedades');
            for (i = 0; i < ids_variedades.length; i++) {
                id_var = ids_variedades[i].value;
                @foreach ($longitudes as $pos_c => $c)
                    $('#tr_var_' + id_var).append('<td class="text-center">' +
                        '<input type="text" id="cantidad_var_' + id_var + '_semana_' + num_semanas +
                        '_{{ $pos_c + 1 }}" style="width: 100%; background-color: ' + bg_color +
                        '" planceholder="#" onkeypress="return isNumber(event)" ' +
                        'class="text-center input_proy" onchange="calcular_totales_proy()" ' +
                        'onkeyup="calcular_totales_proy()">' +
                        '</td>');
                @endforeach
            }
            @foreach ($longitudes as $pos_c => $c)
                $('#tr_totales').append('<th class="text-center bg-yura_dark" ' +
                    'id="th_total_' + num_semanas + '_{{ $pos_c + 1 }}">' +
                    '0</th>');
            @endforeach
            $('#semana_' + num_semanas).focus();
        }, 'json');
    }

    function del_semanas() {
        $('.input_proy').val('');
    }

    function grabar_proy() {
        pos_longitudes = $('.pos_longitudes');
        ids_variedades = $('.ids_variedades');
        data = [];
        for (i = 0; i < ids_variedades.length; i++) {
            id_var = ids_variedades[i].value;
            valores = [];
            for (s = 0; s < pos_longitudes.length; s++) {
                sem_long = pos_longitudes[s].value;
                semana = sem_long.split('_')[0];
                cant = $('#cantidad_var_' + id_var + '_semana_' + sem_long).val();
                valores.push({
                    semana: $('#semana_' + semana).val(),
                    long: $('#longitudes_' + sem_long).val(),
                    cant: cant
                });
            }
            data.push({
                var: id_var,
                valores: valores
            });
        }
        datos = {
            _token: '{{ csrf_token() }}',
            planta: $('#filtro_planta').val(),
            data: data,
        }
        post_jquery_m('{{ url('proyeccion_semana/grabar_proy') }}', datos, function() {
            listar_formulario();
        });
    }

    function calcular_totales_proy() {
        pos_longitudes = $('.pos_longitudes');
        ids_variedades = $('.ids_variedades');
        for (s = 0; s < pos_longitudes.length; s++) {
            long = pos_longitudes[s].value;
            total = 0;
            for (v = 0; v < ids_variedades.length; v++) {
                variedad = ids_variedades[v].value;
                cant = parseInt($('#cantidad_var_' + variedad + '_semana_' + long).val());
                cant = cant > 0 ? cant : 0;
                total += cant;
            }
            $('#th_total_' + long).html(total);
        }
    }

    function add_longitudes(pta) {
        datos = {
            planta: pta,
        }
        get_jquery('{{ url('proyeccion_semana/add_longitudes') }}', datos, function(retorno) {
            modal_view('modal-view_add_longitudes', retorno,
                '<i class="fa fa-fw fa-filter"></i> Administrar longitudes', true, false, '50%');
        });
    }
</script>

<style>
    .col_fija_left_0 {
        position: sticky;
        left: 0;
        z-index: 9;
    }
</style>
