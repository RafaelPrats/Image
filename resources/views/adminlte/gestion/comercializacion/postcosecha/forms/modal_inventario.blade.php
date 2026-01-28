<legend style="margin-bottom: 5px" class="text-center">
    <span id="span_seleccion" class="badge table-inventario hidden"></span>
    <b>{{ $variedad->planta->nombre }} {{ $variedad->nombre }}</b> {{ $empaque->nombre }} {{ $tallos_x_ramo }}tallos
    {{ $longitud_ramo }}cm
</legend>
<input type="hidden" id="variedad_selected" value="{{ $variedad->id_variedad }}">
<input type="hidden" id="empaque_selected" value="{{ $empaque->id_empaque }}">
<input type="hidden" id="tallos_x_ramo_selected" value="{{ $tallos_x_ramo }}">
<input type="hidden" id="longitud_ramo_selected" value="{{ $longitud_ramo }}">
<table class="table-bordered table-inventario" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="padding_lateral_5 th_yura_green">
            Fecha
        </th>
        <th class="padding_lateral_5 th_yura_green" style="width: 60px">
            Dias
        </th>
        <th class="padding_lateral_5 th_yura_green" style="width: 90px">
            Disponibles
        </th>
        <th class="text-center bg-yura_dark" style="width: 70px">
            Cambiar
        </th>
        <th class="padding_lateral_5 th_yura_green" style="width: 90px">

        </th>
    </tr>
    @foreach ($inventarios as $pos_inv => $inv)
        <tr id="tr_inv_{{ $pos_inv }}" data-fecha="{{ $inv->fecha }}">
            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                {{ $inv->fecha }}
            </th>
            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                {{ difFechas(hoy(), $inv->fecha)->days }}
            </th>
            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                {{ $inv->disponibles }}
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="number" max="{{ $inv->disponibles }}" class="text-center"
                    id="cambiar_{{ $inv->fecha }}">
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <button class="btn btn-xs btn-yura_dark" onclick="seleccionar_inventario('{{ $inv->fecha }}')">
                    <i class="fa fa-fw fa-caret-left"></i> Seleccionar
                </button>
            </th>
        </tr>
    @endforeach
</table>
<input type="hidden" id="inventario_selected">
<div class="table-inventario hidden">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr>
            <th class="padding_lateral_5 th_yura_green">
                Color
            </th>
            <th class="padding_lateral_5 th_yura_green">
                Presentacion
            </th>
            <th class="padding_lateral_5 th_yura_green">
                Tallos
            </th>
            <th class="padding_lateral_5 th_yura_green">
                Longitud
            </th>
            <th class="text-center bg-yura_dark">
                Por Armar
            </th>
            <th class="text-center bg-yura_dark">
                Inventario
            </th>
            <th class="text-center th_yura_green" style="width: 70px">
                Cantidad
            </th>
        </tr>
        @foreach ($listado as $pos => $item)
            @if (
                $item['item']->id_empaque != $empaque->id_empaque ||
                    $item['item']->tallos_x_ramo != $tallos_x_ramo ||
                    $item['item']->longitud_ramo != $longitud_ramo)
                @php
                    $por_armar = $item['ramos_inventario'] - $item['ramos'];
                @endphp
                <tr>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item['item']->var_nombre }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item['item']->pres_nombre }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item['item']->tallos_x_ramo }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item['item']->longitud_ramo }}cm
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <button type="button"
                            class="btn btn-xs btn-yura_{{ $por_armar >= 0 ? 'primary' : 'danger' }}">
                            {{ abs($por_armar) }}
                        </button>
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $item['ramos_inventario'] }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <input type="number" class="text-center cambiar_a" style="width: 100%"
                            id="cambiar_a_{{ $pos }}" data-id_variedad="{{ $item['item']->id_variedad }}"
                            data-id_empaque="{{ $item['item']->id_empaque }}"
                            data-tallos_x_ramo="{{ $item['item']->tallos_x_ramo }}"
                            data-longitud_ramo="{{ $item['item']->longitud_ramo }}">
                    </th>
                </tr>
            @endif
        @endforeach
        <tr>
            <th class="padding_lateral_5 bg-yura_dark" colspan="4">
                Basura
            </th>
            <th class="text-center" style="border-color: #9d9d9d" colspan="3">
                <input type="number" class="text-center" style="width: 100%" id="cambiar_a_basura">
            </th>
        </tr>
    </table>
    <div class="text-center" style="margin-top: 5px">
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-yura_default"
                onclick="$('.table-inventario').toggleClass('hidden')">
                <i class="fa fa-fw fa-arrow-left"></i> VOLVER
            </button>
            <button type="button" class="btn btn-sm btn-yura_primary" onclick="store_cambios()">
                <i class="fa fa-fw fa-save"></i> GRABAR CAMBIOS
            </button>
        </div>
    </div>
</div>

<script>
    function seleccionar_inventario(fecha) {
        valor = parseInt($('#cambiar_' + fecha).val());
        if (valor <= parseInt($('#cambiar_' + fecha).prop('max')) && valor > 0) {
            $('#inventario_selected').val(fecha);
            $('.table-inventario').toggleClass('hidden');
            $('#span_seleccion').html('Cambiar ' + valor + ' ramos de');
        } else {
            alerta(
                '<div class="alert alert-warning text-center"><h3>Debe seleccionar una cantidad de ramos <b>igual o menor</b> a los ramos disponibles del inventario</h3></div>'
            );
        }
    }

    function store_cambios() {
        let data_crear = [];
        let cambiar_a = $('.cambiar_a');

        for (let i = 0; i < cambiar_a.length; i++) {
            let cantidad = cambiar_a[i].value;
            if (cantidad > 0) {
                let id_variedad = cambiar_a[i].getAttribute('data-id_variedad');
                let id_empaque = cambiar_a[i].getAttribute('data-id_empaque');
                let tallos_x_ramo = cambiar_a[i].getAttribute('data-tallos_x_ramo');
                let longitud_ramo = cambiar_a[i].getAttribute('data-longitud_ramo');

                data_crear.push({
                    id_variedad: id_variedad,
                    id_empaque: id_empaque,
                    tallos_x_ramo: tallos_x_ramo,
                    longitud_ramo: longitud_ramo,
                    cantidad: cantidad,
                });
            }
        }

        let fecha_inv = $('#inventario_selected').val();
        let data_quitar = {
            fecha: fecha_inv,
            cantidad: $('#cambiar_' + fecha_inv).val()
        };

        if (data_crear.length > 0 || $('#cambiar_a_basura').val() > 0) {
            let datos = {
                _token: '{{ csrf_token() }}',
                fecha: $('#filtro_fecha').val(),
                data_crear: JSON.stringify(data_crear),
                data_quitar: JSON.stringify(data_quitar),
                basura: $('#cambiar_a_basura').val(),
                variedad: $('#variedad_selected').val(),
                empaque: $('#empaque_selected').val(),
                tallos_x_ramo: $('#tallos_x_ramo_selected').val(),
                longitud_ramo: $('#longitud_ramo_selected').val(),
            };

            post_jquery_m('{{ url('postcosecha/store_cambios') }}', datos, function() {
                cerrar_modals();
                listar_reporte();
            });
        }
    }
</script>
