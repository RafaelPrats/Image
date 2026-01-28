<div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green columna_fija_left_0">
                <div style="width: 220px">
                    @php
                        $pos_dia = transformDiaPhp(date('w', strtotime($fecha)));
                    @endphp
                    Ordenes del {{ getDias(TP_COMPLETO, FR_ARREGLO)[$pos_dia] }}
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div style="width: 70px">
                    Renovacion
                </div>
            </th>
            @foreach ($fechas as $f)
                <th class="text-center bg-yura_dark">
                    <div style="width: 140px">
                        {{ convertDateToText($f) }}
                    </div>
                </th>
            @endforeach
        </tr>
        @foreach ($listado as $pos => $item)
            <tr data-id_pedido="{{ $item['pedido']->id_pedido }}" class="tr_historial_pedidos"
                id="tr_historial_pedido_{{ $item['pedido']->id_pedido }}"
                onmouseover="$(this).css('background-color','#ADD8E6')"
                onmouseleave="$(this).css('background-color','')">
                <th class="text-center bg-yura_dark columna_fija_left_0" style="padding: 8px;">
                    <a href="javascript:void(0)" id="check_orden_fija_{{ $item['pedido']->id_pedido }}"
                        onclick="ver_toda_orden('{{ $item['pedido']->orden_fija }}')" style="color: white">
                        #{{ $item['pedido']->orden_fija }}
                        <sup>{{ $item['pedido']->nombre_cliente }}</sup>
                    </a>
                    <div id="status_pedido_proceso_{{ $item['pedido']->id_pedido }}"></div>
                </th>
                <th class="text-center bg-yura_dark">
                    {{ isset($item['renovacion']) ? $item['renovacion']->renovacion . ' dias' : '-' }}
                </th>
                @php
                    $anterior = $item['valores'][0];
                @endphp
                @foreach ($item['valores'] as $pos_v => $v)
                    @php
                        $color_bg = '';
                        if ($v == 0) {
                            if ($item['cancelados'][$pos_v] != '') {
                                $color_bg = 'background: linear-gradient(to right, #d01c62, #d01c614f, #d01c62)';
                            } else {
                                if ($item['unificados'][$pos_v] != '') {
                                    $color_bg = 'background: linear-gradient(to right, yellow, #ffff003f, yellow)';
                                } else {
                                    $color_bg = 'background: linear-gradient(to right, #ff8d00, #ff8c0050, #ff8d00)';
                                }
                            }
                        } elseif ($anterior != $v) {
                            $color_bg = 'background: linear-gradient(to right, #65ffe3, #65ffe31f)';
                            $anterior = $v;
                        }
                    @endphp
                    <th class="text-center" style="border-color: #9d9d9d; {{ $color_bg }}">
                        @if ($v > 0)
                            {{ $v }}
                        @endif
                    </th>
                    @php
                    @endphp
                @endforeach
            </tr>
        @endforeach
    </table>
</div>

<legend style="font-size: 1em; margin-bottom: 5px" class="text-right">
    <b>Leyenda</b>
</legend>
<div style="font-size: 1em" class="text-right">
    <span class="badge" style="background-color: yellow; color: black;">Unificado</span>
    <br>
    <span class="badge" style="background-color: #d01c62; color: white;">Cancelado</span>
    <br>
    <span class="badge" style="background-color: #ff8d00; color: black;">Faltante</span>
    <br>
    <span class="badge" style="background-color: #65ffe3; color: black;">Cambio</span>
</div>
<script>
    function checkProcesosPendientes() {

        var arrIdsPedido = [];

        $('.tr_historial_pedidos').each(function() {
            var value = $(this).attr('data-id_pedido');
            arrIdsPedido.push(value);
        });

        var csrfToken = "{{ csrf_token() }}";
        var datos = {
            arrIdsPedido: arrIdsPedido,
            _token: csrfToken
        };

        $.ajax({
            url: '/despachos/check_pending_processes',
            type: 'POST',
            data: datos,
            dataType: 'json',
            success: function(result) {
                result.forEach(function(pedido) {
                    if (pedido.estado === 'P') {
                        // Deshabilitar el input correspondiente
                        if (!$('#check_orden_fija_' + pedido.id_pedido).hasClass('link-disabled')) {
                            $('#check_orden_fija_' + pedido.id_pedido).addClass("link-disabled");
                        }
                        $('#status_pedido_proceso_' + pedido.id_pedido).html(`
                    <div title="${pedido.descripcion}">
                        <span style="font-weight: normal;color: #FFFFFF;font-size: 12px;">Proceso de orden en curso<br>
                                    Completado en un <span style="font-weight: bold;font-size: 14px;">${pedido.progreso}%</span>.
                            <br>
                                Proceso iniciado por: <span style="font-weight: bold;">${pedido.username}</span>
                            <br>
                        </span>
                        <div>
                            <div style="margin: 0 auto;" class="benchflow-spinner" bis_skin_checked="1"></div>
                        </div>
                    </div>`);
                    } else {
                        $('#status_pedido_proceso_' + pedido.id_pedido).html('');
                        $('#check_orden_fija_' + pedido.id_pedido).removeClass("link-disabled");

                    }
                });
            }
        });
    }
    checkProcesosPendientes();
    setInterval(checkProcesosPendientes, 3000);
</script>
