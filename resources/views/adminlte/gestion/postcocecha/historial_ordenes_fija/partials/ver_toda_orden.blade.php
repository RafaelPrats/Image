<legend class="text-center" style="font-size: 1.2em; margin-bottom: 0">
    <b>Orden Fija #{{ $orden_fija }}</b>
    <div class="input-group">
        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
            Renovacion
        </span>
        <select id="renovacion_{{ $orden_fija }}" class="input-yura_default form-control" style="width: 100%">
            <option value="7" {{ isset($renovacion) && $renovacion->renovacion == 7 ? 'selected' : '' }}>
                Cada 7 dias
            </option>
            <option value="14" {{ isset($renovacion) && $renovacion->renovacion == 14 ? 'selected' : '' }}>
                Cada 14 dias
            </option>
            <option value="" {{ !isset($renovacion) ? 'selected' : '' }}>
                No renovar más
            </option>
        </select>
        <span class="input-group-btn">
            <button class="btn btn-yura_primary" type="button" title="Ver segundo plano"
                onclick="store_renovacion('{{ $orden_fija }}')">
                <i class="fa fa-save"></i> Actualizar renovacion
            </button>
        </span>
    </div>
</legend>
<div style="overflow-y: scroll; max-height: 500px">
    <table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%">
        <tr style="position: sticky;">
            <th class="text-center th_yura_green">
                <input type="checkbox" onchange="$('.check_listado_orden').prop('checked', $(this).prop('checked'))"
                    class="mouse-hand" checked>
            </th>
            <th class="text-center th_yura_green">
                Todas las fechas
            </th>
            <th class="text-center th_yura_green">
                Cajas
            </th>
            <th class="text-center th_yura_green">
                Dia de la Semana
            </th>
            <th class="text-center th_yura_green">
                <button type="button" class="btn btn-xs btn-yura_dark pull-right" title="Agregar una nueva fecha"
                    onclick="agregar_nueva_fecha('{{ $orden_fija }}')">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </th>
        </tr>
        @php
            $fecha_actual = $fecha;
        @endphp
        @foreach ($listado as $pos => $o)
            @php
                $pos_dia = transformDiaPhp(date('w', strtotime($o->fecha_pedido)));
            @endphp
            <tr class="tr_orden_fecha_{{ $o->fecha_pedido }} tr_orden_fecha" id="tr_orden_fecha_{{ $o->fecha_pedido }}"
                onmouseover="$(this).addClass('bg-yura_dark')" onmouseleave="$(this).removeClass('bg-yura_dark')"
                style="background-color: {{ $pos == 0 ? '#65ffe3' : '' }}">
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="checkbox" checked class="check_listado_orden mouse-hand"
                        id="check_listado_orden_{{ $o->fecha_pedido }}" data-fecha="{{ $o->fecha_pedido }}">
                    @if ($pos == 0)
                        <input type="hidden" id="fecha_selected" value="{{ $o->fecha_pedido }}">
                    @endif
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    <label for="check_listado_orden_{{ $o->fecha_pedido }}" class="mouse-hand">
                        {{ getDias(TP_ABREVIADO, FR_ARREGLO)[$pos_dia] }}
                        {{ convertDateToText($o->fecha_pedido) }}
                        @if ($pos > 0)
                            <sup>+{{ difFechas($o->fecha_pedido, $fecha_actual)->days }} dias</sup>
                        @endif
                    </label>
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $o->cajas }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ getDias(TP_COMPLETO, FR_ARREGLO)[$pos_dia] }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_default" title="Seleccionar fecha como guia"
                            onclick="seleccionar_fecha('{{ $o->fecha_pedido }}')">
                            <i class="fa fa-fw fa-arrow-left"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_danger"
                            onclick="eliminar_pedido_orden_fija('{{ $orden_fija }}', '{{ $o->fecha_pedido }}')">
                            <i class="fa fa-fw fa-trash"></i>
                        </button>
                    </div>
                </th>
            </tr>
            @php
                $fecha_actual = $o->fecha_pedido;
            @endphp
        @endforeach
    </table>
</div>

<div style="margin-top: 5px;" class="text-center">
    <button type="button" class="btn btn-yura_primary" onclick="update_orden_fija('{{ $orden_fija }}')">
        <i class="fa fa-fw fa-save"></i>
        Actualizar Orden Fija usando la FECHA GUIA
    </button>
</div>


<script>
    function agregar_nueva_fecha(orden_fija) {
        datos = {
            _token: '{{ csrf_token() }}',
            orden_fija: orden_fija,
            fecha: $('#filtro_fecha').val(),
        }
        get_jquery('{{ url('historial_ordenes_fija/agregar_nueva_fecha') }}', datos, function(retorno) {
            modal_view('modal_agregar_nueva_fecha', retorno,
                '<i class="fa fa-fw fa-calendar"></i> Agregar un nuevo pedido a la Orden Fija #' +
                orden_fija,
                true, false, '{{ isPC() ? '50%' : '' }}',
                function() {});
        });
    }

    function eliminar_pedido_orden_fija(orden_fija, fecha) {
        mensaje = {
            title: '<i class="fa fa-fw fa-trash"></i> Mensaje de Confirmacion',
            mensaje: '<div class="alert alert-info text-center" style="font-size: 16px"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de <b>ELIMINAR</b> el pedido?</div>',
        };
        modal_quest('modal_eliminar_pedido_orden_fija', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    orden_fija: orden_fija,
                    fecha: fecha,
                }
                post_jquery_m('{{ url('historial_ordenes_fija/eliminar_pedido_orden_fija') }}', datos, function() {
                    $('.tr_orden_fecha_' + fecha).remove();
                });
            });
    }

    function update_orden_fija(orden_fija) {
        mensaje = {
            title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de Confirmacion',
            mensaje: '<div class="alert alert-info text-center" style="font-size: 16px">¿Está seguro de <b>ACTUALIZAR</b> la orden fija?</div>',
        };
        modal_quest('modal_update_orden_fija', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                fechas = [];
                check_listado_orden = $('.check_listado_orden');
                for (f = 0; f < check_listado_orden.length; f++) {
                    id = check_listado_orden[f].id;
                    if ($('#' + id).prop('checked') == true) {
                        fecha = $('#' + id).attr('data-fecha');
                        if (fecha != $('#fecha_selected').val())
                            fechas.push(fecha);
                    }
                }
                datos = {
                    _token: '{{ csrf_token() }}',
                    orden_fija: orden_fija,
                    fecha: $('#fecha_selected').val(),
                    fechas: JSON.stringify(fechas),
                }
                post_jquery_m('{{ url('historial_ordenes_fija/update_orden_fija') }}', datos, function() {
                    cerrar_modals();
                });
            });
    }

    function seleccionar_fecha(fecha) {
        $('#fecha_selected').val(fecha);
        $('.tr_orden_fecha').css('background-color', '');
        $('#tr_orden_fecha_' + fecha).css('background-color', '#65ffe3');
    }

    function store_renovacion(orden_fija) {
        mensaje = {
            title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de Confirmacion',
            mensaje: '<div class="alert alert-info text-center" style="font-size: 16px">¿Está seguro de <b>ACTUALIZAR</b> la renovacion de la orden fija?</div>',
        };
        modal_quest('modal_store_renovacion', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '50%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    orden_fija: orden_fija,
                    renovacion: $('#renovacion_' + orden_fija).val(),
                }
                post_jquery_m('{{ url('historial_ordenes_fija/store_renovacion') }}', datos, function() {});
            });
    }
</script>
