<input type="hidden" id="planta_selected" value="{{ $planta->id_planta }}">
<div style="overflow-y: scroll; overflow-x: scroll">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr id="tr_cortes">
            <th class="text-center th_yura_green" style="width: 250px">
                <div class="input-group">
                    <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                        Conversion
                    </span>
                    <input type="number" id="factor_conversion" name="factor_conversion" class="text-center"
                        style="width: 100%; height: 34px; color: black" value="{{ $planta->factor_conversion_proy }}"
                        onchange="update_factor_conversion()">
                    <div class="input-group-btn">
                        @if (es_server())
                            <button type="button" class="btn btn-yura_dark" title="Agregar Corte"
                                onclick="add_corte()">
                                <i class="fa fa-fw fa-plus"></i>
                            </button>
                            <button type="button" class="btn btn-yura_danger" title="Eliminar valores"
                                onclick="del_cortes()">
                                <i class="fa fa-fw fa-trash"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </th>
            @foreach ($cortes as $pos_c => $c)
                <th class="text-center bg-yura_dark">
                    <input type="text" id="corte_{{ $pos_c + 1 }}" style="width: 100%; height: 34px"
                        class="text-center bg-yura_dark" value="{{ $c->nombre }}" placeholder="Punto de corte">
                    <input type="hidden" id="id_corte_{{ $pos_c + 1 }}" value="{{ $c->id_proy_cortes }}">
                </th>
                <th class="text-center bg-yura_dark" id="th_uso_corte_{{ $c->id_proy_cortes }}">
                    @if (es_server())
                        <button type="button" id="btn_uso_corte_{{ $c->id_proy_cortes }}"
                            class="btn btn-xs btn-yura_{{ $c->usar == 1 ? 'primary' : 'danger' }}"
                            title="{{ $c->usar == 1 ? 'Tener en cuenta para las distribuciones' : 'No tener en cuenta' }}"
                            onclick="cambiar_uso_corte('{{ $c->id_proy_cortes }}', '{{ $c->usar }}')">
                            <i class="fa fa-fw fa-{{ $c->usar == 1 ? 'check' : 'times' }}"></i>
                        </button>
                    @endif
                    <input type="hidden" id="usar_corte_{{ $c->id_proy_cortes }}" value="{{ $c->usar }}">
                </th>
            @endforeach
        </tr>
        @foreach ($listado as $item)
            <input type="hidden" class="ids_variedades" value="{{ $item['var']->id_variedad }}">
            <tr id="tr_var_{{ $item['var']->id_variedad }}">
                <th class="text-center bg-yura_dark">
                    {{ $item['var']->nombre }}
                </th>
                @foreach ($item['valores'] as $pos_c => $v)
                    <td class="text-center" colspan="2">
                        <input type="number" onkeypress="return isNumber(event)" min="0"
                            id="cantidad_var_{{ $item['var']->id_variedad }}_corte_{{ $pos_c + 1 }}"
                            class="text-center input_proy" style="width: 100%" value="{{ $v }}">
                    </td>
                @endforeach
            </tr>
        @endforeach
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
    var num_cortes = {{ count($cortes) }};

    function add_corte() {
        num_cortes++;
        $('#tr_cortes').append('<th class="text-center">' +
            '<input type="text" id="corte_' + num_cortes + '" style="width: 100%" ' +
            'class="text-center bg-yura_dark" placeholder="Punto de corte">' +
            '</th>');
        ids_variedades = $('.ids_variedades');
        for (i = 0; i < ids_variedades.length; i++) {
            id_var = ids_variedades[i].value;
            $('#tr_var_' + id_var).append('<td class="text-center">' +
                '<input type="text" id="cantidad_var_' + id_var + '_corte_' + num_cortes +
                '" style="width: 100%" planceholder="#" onkeypress="return isNumber(event)" ' +
                'class="text-center input_proy">' +
                '</td>');
        }
        $('#corte_' + num_cortes).focus();
    }

    function del_cortes() {
        $('.input_proy').val('');
    }

    function grabar_proy() {
        mensaje = {
            title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmacion',
            mensaje: '<div class="alert alert-info text-center" style="font-size: 16px">Asegurese de ingresar los valores en <b>RAMOS</b></div>',
        };
        modal_quest('modal_grabar_proy', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                ids_variedades = $('.ids_variedades');
                data = [];
                cortes = [];
                for (i = 0; i < ids_variedades.length; i++) {
                    id_var = ids_variedades[i].value;
                    valores = [];
                    for (y = 1; y <= num_cortes; y++) {
                        cant = $('#cantidad_var_' + id_var + '_corte_' + y).val();
                        if (cant > 0) {
                            valores.push({
                                corte: $('#corte_' + y).val(),
                                id_corte: $('#id_corte_' + y).val(),
                                cant: cant
                            });
                        }
                        if (i == 0) {
                            cortes.push($('#corte_' + y).val());
                        }
                    }
                    data.push({
                        var: id_var,
                        valores: valores
                    });
                }
                datos = {
                    _token: '{{ csrf_token() }}',
                    fecha: $('#filtro_fecha').val(),
                    planta: $('#filtro_planta').val(),
                    factor_conversion: $('#factor_conversion').val(),
                    data: data,
                    cortes: cortes,
                }
                post_jquery_m('{{ url('ingresos_proy/grabar_proy') }}', datos, function() {
                    listar_formulario();
                });
            });
    }

    function cambiar_uso_corte(corte) {
        datos = {
            _token: '{{ csrf_token() }}',
            corte: corte,
        }
        post_jquery_m('{{ url('ingresos_proy/cambiar_uso_corte') }}', datos, function() {
            usar = $('#usar_corte_' + corte).val();
            if (usar == 1) { // marcar como NO tener en cuenta
                $('#btn_uso_corte_' + corte).removeClass('btn-yura_primary');
                $('#btn_uso_corte_' + corte).addClass('btn-yura_danger');
                $('#btn_uso_corte_' + corte).attr('title', 'No tener en cuenta en las distribuciones');
                $('#btn_uso_corte_' + corte).html('<i class="fa fa-fw fa-times"></i>');
                $('#usar_corte_' + corte).val(0);
            } else { // marcar como SI tener en cuenta
                $('#btn_uso_corte_' + corte).removeClass('btn-yura_danger');
                $('#btn_uso_corte_' + corte).addClass('btn-yura_primary');
                $('#btn_uso_corte_' + corte).attr('title', 'Tener en cuenta en las distribuciones');
                $('#btn_uso_corte_' + corte).html('<i class="fa fa-fw fa-check"></i>');
                $('#usar_corte_' + corte).val(1);
            }
        }, 'th_uso_corte_' + corte);
    }

    function update_factor_conversion() {
        mensaje = {
            title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmacion',
            mensaje: '<div class="alert alert-info text-center" style="font-size: 16px">¿Está seguro de <b>CAMBIAR</b> el factor de conversion?</div>',
        };
        modal_quest('modal_update_factor_conversion', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    planta: $('#planta_selected').val(),
                    valor: $('#factor_conversion').val(),
                }
                post_jquery_m('{{ url('ingresos_proy/update_factor_conversion') }}', datos, function(retorno) {

                });
            });
    }
</script>
