<table style="width:100%">
    <tbody>
        <tr>
            <th style="border-color: #9d9d9d; background-color: #e9ecef" colspan="2">
                <ul class="list-unstyled">
                    <li>
                        Semana: {{ getSemanaByDate($fecha)->codigo }}
                    </li>
                    <li>
                        Día: {{ getDias(TP_COMPLETO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime($fecha)))] }}
                    </li>
                </ul>
            </th>
            <th style="border-color: #9d9d9d; background-color: #e9ecef" class="text-right" colspan="14">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_dark" onclick="ver_despachos()">
                        <i class="fa fa-eye" aria-hidden="true"></i> Ver despachos
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_dark" onclick="crear_despacho()">
                        <i class="fa fa-truck" aria-hidden="true"></i> Crear despacho
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_default" onclick="descargar_flor_postco()">
                        <i class="fa fa-fw fa-file-excel-o"></i> Flor Posco
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_default" onclick="descargar_disponibilidad()">
                        <i class="fa fa-fw fa-file-excel-o"></i> Disponibilidad
                    </button>
                </div>
            </th>
        </tr>
    </tbody>
</table>
<div style="overflow-x: scroll; overflow-y: scroll; width: 100%; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; font-size: 0.8em" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="text-center th_yura_green" style="width: 30px">
                    <input type="checkbox" id="check_all"
                        onchange="$('.check_proy').prop('checked', $(this).prop('checked'))">
                </th>
                <th class="text-center th_yura_green">
                    # DESPACHO
                </th>
                <th class="text-center th_yura_green" style="width: 60px">
                    PACKING
                </th>
                <th class="text-center th_yura_green" style="width: 40px">
                    CLIENTE
                </th>
                <th class="text-center th_yura_green" style="width: 40px">
                    MARCACIONES
                </th>
                <th class="text-center th_yura_green">
                    FECHA
                </th>
                <th class="text-center th_yura_green">
                    CAJAS
                </th>
                <th class="text-center th_yura_green">
                    FB
                </th>
                <th class="text-center th_yura_green">
                    HB
                </th>
                <th class="text-center th_yura_green">
                    QB
                </th>
                <th class="text-center th_yura_green">
                    EB
                </th>
                <th class="text-center th_yura_green">
                    SB
                </th>
                <th class="text-center th_yura_green">
                    AGENCIA
                </th>
                <th class="text-center th_yura_green">
                    OPCIONES
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $item)
                @php
                    $hoja_ruta = $item->getHojaRuta();
                    $cajas = 0;
                    $fb = 0;
                    $hb = 0;
                    $qb = 0;
                    $eb = 0;
                    $sb = 0;
                    foreach ($item->cajas as $caja) {
                        $empaque = $caja->empaque;
                        $cajas += $caja->cantidad;
                        if ($empaque->siglas == 'FB') {
                            $fb += $caja->cantidad;
                        }
                        if ($empaque->siglas == 'HB') {
                            $hb += $caja->cantidad;
                        }
                        if ($empaque->siglas == 'QB') {
                            $qb += $caja->cantidad;
                        }
                        if ($empaque->siglas == 'EB') {
                            $eb += $caja->cantidad;
                        }
                        if ($empaque->siglas == 'SB') {
                            $sb += $caja->cantidad;
                        }
                    }
                @endphp
                <tr onmouseover="$(this).css('background-color', 'cyan')"
                    onmouseleave="$(this).css('background-color', '')">
                    <th class="text-center" style="border-color: #9d9d9d">
                        @if ($hoja_ruta == '')
                            <input type="checkbox" class="check_proy mouse-hand"
                                id="check_proy_{{ $item->id_proyecto }}" data-id_proy="{{ $item->id_proyecto }}">
                        @endif
                    </th>
                    <th class="text-center"
                        style="border-color: #9d9d9d; background-color: {{ $hoja_ruta != '' ? '#d4edda' : '#f8d7da' }}">
                        @if ($hoja_ruta != '')
                            <span style="font-size: 1.3em">
                                {{ '#' . $hoja_ruta->id_hoja_ruta }}
                            </span>
                            -
                            {{ $hoja_ruta->conductor->nombre }}
                        @endif
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $item->packing }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $item->nombre_cliente }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        @foreach ($item->getMarcaciones() as $val)
                            -{{ $val }} <br>
                        @endforeach
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $item->fecha }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $cajas }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $fb }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $hb }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $qb }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $eb }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $sb }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $item->nombre_agencia_carga }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        @if (count($despachos) > 0)
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-xs btn-yura_default dropdown-toggle"
                                    data-toggle="dropdown" aria-expanded="true" style="width: 100%;">
                                    <i class="fa fa-fw fa-bars"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right sombra_pequeña"
                                    style="background-color: #c8c8c8">
                                    @if ($hoja_ruta == '')
                                        <li class="header">
                                            <i class="fa fa-fw fa-plus"></i> Agegar al Despacho...
                                        </li>
                                        @foreach ($despachos as $despacho)
                                            <li>
                                                <a href="javascript:void(0)" style="color: black"
                                                    onclick="agregar_a_despacho({{ $item->id_proyecto }}, {{ $despacho->id_hoja_ruta }})">
                                                    {{ '#' . $despacho->id_hoja_ruta . ' - ' . $despacho->conductor->nombre }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                    @if ($hoja_ruta != '')
                                        <li class="header">
                                            <i class="fa fa-fw fa-refresh"></i> Cambiar al Despacho...
                                        </li>
                                        @foreach ($despachos as $despacho)
                                            @if ($despacho->id_hoja_ruta != $hoja_ruta->id_hoja_ruta)
                                                <li>
                                                    <a href="javascript:void(0)" style="color: black"
                                                        onclick="cambiar_a_despacho({{ $item->id_proyecto }}, {{ $despacho->id_hoja_ruta }})">
                                                        {{ '#' . $despacho->id_hoja_ruta . ' - ' . $despacho->conductor->nombre }}
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    @endif
                                </ul>
                            </div>
                        @endif
                    </th>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    estructura_tabla('table_listado');

    function crear_despacho() {
        data = [];
        check_proy = $('.check_proy');
        for (i = 0; i < check_proy.length; i++) {
            id_check = check_proy[i].id;
            if ($('#' + id_check).prop('checked') == true) {
                id_proy = $('#' + id_check).data('id_proy');
                data.push(id_proy);
            }
        }
        if (data.length > 0) {
            datos = {
                data: JSON.stringify(data),
                fecha: $('#filtro_desde').val(),
            }
            get_jquery('{{ url('hoja_ruta/crear_despacho') }}', datos, function(retorno) {
                modal_view('moda-view_crear_despacho', retorno,
                    '<i class="fa fa-fw fa-balance-scale"></i> Crear Despacho', true, false,
                    '{{ isPC() ? '95%' : '' }}');
            });
        }
    }

    function agregar_a_despacho(proy, hoja_ruta) {
        datos = {
            id_proy: proy,
            id_hoja_ruta: hoja_ruta,
        }
        get_jquery('{{ url('hoja_ruta/agregar_a_despacho') }}', datos, function(retorno) {
            modal_view('moda-view_agregar_a_despacho', retorno,
                '<i class="fa fa-fw fa-plus-circle"></i> Agregar a Despacho', true, false,
                '{{ isPC() ? '65%' : '' }}');
        });
    }

    function cambiar_a_despacho(proy, hoja_ruta) {
        datos = {
            id_proy: proy,
            id_hoja_ruta: hoja_ruta,
        }
        get_jquery('{{ url('hoja_ruta/cambiar_a_despacho') }}', datos, function(retorno) {
            modal_view('moda-view_cambiar_a_despacho', retorno,
                '<i class="fa fa-fw fa-exchange"></i> Cambiar a Despacho', true, false,
                '{{ isPC() ? '50%' : '' }}');
        });
    }

    function ver_despachos() {
        datos = {
            desde: $('#filtro_desde').val(),
            hasta: $('#filtro_hasta').val(),
        }
        get_jquery('{{ url('hoja_ruta/ver_despachos') }}', datos, function(retorno) {
            modal_view('moda-view_ver_despachos', retorno,
                '<i class="fa fa-fw fa-eye"></i> Ver Despachos', true, false,
                '{{ isPC() ? '60%' : '' }}');
        });
    }

    function exportar_despacho(id) {
        $.LoadingOverlay('show');
        window.open('{{ url('hoja_ruta/exportar_despacho') }}?id=' + id, '_blank');
        $.LoadingOverlay('hide');
    }

    function descargar_flor_postco() {
        $.LoadingOverlay('show');
        window.open('{{ url('hoja_ruta/descargar_flor_postco') }}?cliente=' + $('#filtro_cliente').val() +
            '&agencia=' + $('#filtro_agencia').val() +
            '&desde=' + $('#filtro_desde').val() +
            '&hasta=' + $('#filtro_hasta').val(), '_blank');
        $.LoadingOverlay('hide');
    }

    function descargar_disponibilidad() {
        $.LoadingOverlay('show');
        window.open('{{ url('hoja_ruta/descargar_disponibilidad') }}?cliente=' + $('#filtro_cliente').val() +
            '&agencia=' + $('#filtro_agencia').val() +
            '&desde=' + $('#filtro_desde').val() +
            '&hasta=' + $('#filtro_hasta').val(), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
