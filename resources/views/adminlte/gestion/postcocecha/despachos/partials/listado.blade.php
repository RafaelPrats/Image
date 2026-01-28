<div id="table_despachos">
    @if (count($listado) > 0)
        @php
            $n_despachos = [];
        @endphp

        @foreach ($listado as $x => $pedido)
            @php
                if (
                    !empty($pedido->n_despacho) &&
                    !in_array($pedido->n_despacho, array_column($n_despachos, 'n_despacho'))
                ) {
                    $n_despacho = new stdClass();
                    $n_despacho->id_despacho = $pedido->id_despacho;
                    $n_despacho->n_despacho = $pedido->n_despacho;
                    // Agregar propiedad bg_color al objeto $n_despacho
                    if ($pedido->id_despacho % 2) {
                        $n_despacho->bg_color = '#f7d9e9';
                    } else {
                        $n_despacho->bg_color = '#c5e9dc';
                    }

                    $n_despachos[] = $n_despacho;
                }
            @endphp
        @endforeach
        <div style="width: 100%;overflow-x:auto">
            <table style="width:100%">
                <tr>
                    <th style="border-color: #9d9d9d; background-color: #e9ecef" colspan="2">
                        <ul class="list-unstyled">
                            <li>
                                Semana:
                                {{ isset(getSemanaByDate($fecha)->codigo) ? getSemanaByDate($fecha)->codigo : 'Semana no programada' }}
                            </li>
                            <li>
                                Día:
                                {{ getDias(TP_COMPLETO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime($fecha)))] }}
                            </li>
                        </ul>
                    </th>
                    <th style="border-color: #9d9d9d; background-color: #e9ecef" class="text-right"
                        colspan="{{ $opciones ? '13' : '14' }}">
                        <div class="btn-group">
                            @if (!$opciones)
                                <button type="button" class="btn btn-xs btn-yura_dark" onclick="ver_despachos()">
                                    <i class="fa fa-eye" aria-hidden="true"></i> Ver despachos
                                </button>
                                @if (es_server())
                                    <button type="button" class="btn btn-xs btn-yura_dark" onclick="crear_despacho()">
                                        <i class="fa fa-truck" aria-hidden="true"></i> Crear despacho
                                    </button>
                                @endif
                            @endif
                            @if ($opciones)
                                <button type="button" class="btn btn-xs btn-yura_warning" style="border-color: white"
                                    onclick="exportar_jire()">
                                    <i class="fa fa-fw fa-download"></i> Importar al JIRE
                                </button>
                            @endif
                            @if (es_server() && $opciones)
                                {{--
                                <button type="button" class="btn btn-xs btn-yura_dark" style="border-color: white"
                                    onclick="dividir_marcaciones('{{ csrf_token() }}')">
                                    <i class="fa fa-fw fa-list-alt"></i> Dividir marcaciones
                                </button>
                                 --}}
                                <button type="button" class="btn btn-xs btn-yura_warning" style="border-color: white"
                                    onclick="generar_packings()">
                                    <i class="fa fa-gift"></i> Generar packings
                                </button>
                                <button type="button" class="btn btn-xs btn-yura_dark" style="border-color: white"
                                    onclick="descargar_packings('{{ csrf_token() }}')">
                                    <i class="fa fa-file-pdf-o"></i> Descargar packings
                                </button>
                                {{--
                                <button type="button" class="btn btn-xs btn-yura_dark" style="border-color: white"
                                    onclick="unificar_pedidos('{{ csrf_token() }}')">
                                    <i class="fa fa-fw fa-clone"></i> Unificar pedidos
                                </button>
                                 --}}
                                <button type="button" class="btn btn-xs btn-yura_dark" style="border-color: white"
                                    onclick="combinar_pedidos()">
                                    <i class="fa fa-fw fa-clone"></i> Combinar pedidos
                                </button>
                            @endif
                            <button type="button" class="btn btn-xs btn-yura_dark"
                                onclick="exportar_excel_flor_posco()" style="border-color: white">
                                <i class="fa fa-fw fa-file-excel-o"></i> Flor Posco
                            </button>
                            {{-- <button type="button" class="btn btn-xs btn-primary" onclick="exportar_listado_cuarto_frio('{{csrf_token()}}')">
                            <i class="fa fa-fw fa-file-excel-o"></i> Exportar a Excel Cuarto Frio
                        </button> --}}
                            @if ($opciones)
                                <button type="button" class="btn btn-xs btn-yura_default"
                                    onclick="exportar_listado_despacho(document.getElementById('id_configuracion_pedido').value)">
                                    <i class="fa fa-fw fa-file-excel-o"></i> Exportar a Excel
                                </button>
                            @else
                                <button type="button" class="btn btn-xs btn-success"
                                    onclick="exportar_excel_listado_despacho_script()">
                                    <i class="fa fa-fw fa-file-excel-o"></i> Exportar a Excel
                                </button>
                            @endif
                        </div>
                    </th>
                </tr>
            </table>
            <div id="parent">
                <table width="100%" class="table-responsive table-bordered"
                    style="font-size: 0.8em; border-color: #9d9d9d;overflow-x: auto" id="table_content_aperturas">
                    <thead>
                        <tr>
                            @if ($opciones)
                                <th class="text-center th_yura_green"></th>
                            @endif
                            @if (!$opciones)
                                <th class="text-center th_yura_green" style="width:80px">
                                    {{-- <input type="checkbox" onchange="select_orden(this)" style="width: 16px;height:16px" id="check_select_orden"> --}}
                                    SELECCIONAR
                                    <input type="checkbox" onchange="select_all_to_dispatch(this)"
                                        id="check_select_all_orden" style="width: 18px;height:18px">
                                </th>
                            @endif
                            @if (!$opciones)
                                <th class="text-center th_yura_green" style="width:80px">
                                    DESPACHO #
                                </th>
                            @endif
                            <th class="text-center th_yura_green">PACKING</th>
                            <th class="text-center th_yura_green">
                                CLIENTE
                            </th>
                            @if ($opciones)
                                <th class="text-center th_yura_green">
                                    TIPO
                                </th>
                            @endif
                            <th class="text-center th_yura_green" style="width:80px">
                                FECHA
                            </th>
                            @if ($opciones)
                                <th class="text-center th_yura_green">
                                    MARCACIONES
                                </th>
                            @endif
                            @if ($opciones)
                                <th class="text-center th_yura_green">
                                    FLOR
                                </th>
                                <th class="text-center th_yura_green">
                                    EMPAQUE
                                </th>
                                <th class="text-center th_yura_green">
                                    PRESENTACIÓN
                                </th>
                            @endif
                            <th class="text-center th_yura_green">
                                CAJAS
                            </th>
                            @if ($opciones)
                                <th class="text-center th_yura_green">
                                    BUNCHES
                                </th>
                                <th class="text-center th_yura_green">
                                    STEMS
                                </th>
                                <th class="text-center th_yura_green">
                                    TOTAL STEMS
                                </th>
                                <th class="text-center th_yura_green">
                                    PRECIO
                                </th>
                            @else
                                <th class="text-center th_yura_green">
                                    FULL
                                </th>
                                <th class="text-center th_yura_green">
                                    HALF
                                </th>
                                <th class="text-center th_yura_green">
                                    CUARTOS
                                </th>
                                <th class="text-center th_yura_green">
                                    OCTAVOS
                                </th>
                                <th class="text-center th_yura_green">
                                    SB
                                </th>
                            @endif
                            <th class="text-center th_yura_green">
                                @if ($opciones)
                                    CUARTO FRÍO
                                @else
                                    AGENCIA DE CARGA
                                @endif
                            </th>
                            @if (es_server())
                                <th class="text-center th_yura_green" style="">
                                    OPCIONES
                                </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $piezas_totales = 0;
                            $ramos_totales = 0;
                            $variedades = [];
                            $marcaciones = [];
                            $tipo_cajas = [];
                            $full_total = 0;
                            $half_total = 0;
                            $half_full_equivalente = 0;
                            $cuarto_total = 0;
                            $cuarto_full_equivalente = 0;
                            $octavo_total = 0;
                            $octavo_full_equivalente = 0;
                            $sb_total = 0;
                            $sb_full_equivalente = 0;
                        @endphp
                        @foreach ($listado as $x => $pedido)
                            @php
                                $env = $envio::where('id_pedido', $pedido->id_pedido)->first();
                                $listar = true;
                                if (!$opciones && getFacturaAnulada($pedido->id_pedido)) {
                                    $listar = false;
                                }
                            @endphp
                            @if ($listar)
                                @php
                                    $despachado = getCantDespacho($pedido->id_pedido);
                                    $despachoObj = getDespacho($pedido->id_pedido);
                                    $ped = getPedido($pedido->id_pedido);

                                    if (isset($ped->envios[0]->id_envio)) {
                                        $firmado = null; /* getFacturado($ped->envios[0]->id_envio,1) */
                                        $facturado = null; /* getFacturado($ped->envios[0]->id_envio,5) */
                                    } else {
                                        $firmado = null;
                                        $facturado = null;
                                    }

                                    $orden = [];
                                    $full = 0;
                                    $half = 0;
                                    $cuarto = 0;
                                    $sexto = 0;
                                    $octavo = 0;
                                    $sb = 0;
                                    $idsDetPed = [];

                                    if (isset($id_marcacion)) {
                                        $detallePedidos = $ped->detalles
                                            ->filter(function ($obj) use ($id_marcacion) {
                                                return DB::table('detallepedido_datoexportacion as dpde')
                                                    ->where('dpde.id_detalle_pedido', $obj->id_detalle_pedido)
                                                    ->where('dpde.valor', $id_marcacion)
                                                    ->exists();
                                            })
                                            ->values();
                                    } else {
                                        $detallePedidos = $ped->detalles;
                                    }

                                @endphp

                                @foreach ($detallePedidos as $pos_det_ped => $det_ped)
                                    @if (
                                        !empty($det_ped->cliente_especificacion) &&
                                            !empty($det_ped->cliente_especificacion->especificacion) &&
                                            !empty($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque))
                                        @foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp)
                                            @php

                                                switch (explode('|', $esp_emp->empaque->nombre)[1]) {
                                                    case '0.5':
                                                        $half += $det_ped->cantidad;
                                                        $half_total += $det_ped->cantidad;
                                                        $half_full_equivalente +=
                                                            $det_ped->cantidad *
                                                            explode('|', $esp_emp->empaque->nombre)[1];
                                                        break;
                                                    case '0.25':
                                                        $cuarto += $det_ped->cantidad;
                                                        $cuarto_total += $det_ped->cantidad;
                                                        $cuarto_full_equivalente +=
                                                            $det_ped->cantidad *
                                                            explode('|', $esp_emp->empaque->nombre)[1];
                                                        break;
                                                    case '0.125':
                                                        $octavo += $det_ped->cantidad;
                                                        $octavo_total += $det_ped->cantidad;
                                                        $octavo_full_equivalente +=
                                                            $det_ped->cantidad *
                                                            explode('|', $esp_emp->empaque->nombre)[1];
                                                        break;
                                                    case '0.0625':
                                                        $sb += $det_ped->cantidad;
                                                        $sb_total += $det_ped->cantidad;
                                                        $sb_full_equivalente +=
                                                            $det_ped->cantidad *
                                                            explode('|', $esp_emp->empaque->nombre)[1];
                                                        break;
                                                    case '1':
                                                        $full += $det_ped->cantidad;
                                                        $full_total += $det_ped->cantidad;
                                                        break;
                                                }

                                                $piezas_despacho = $half + $cuarto + $octavo + $full + $sb;

                                                if (count($listadoVariedades)) {
                                                    foreach (
                                                        $esp_emp->detalles
                                                            ->whereIn('id_variedad', $listadoVariedades)
                                                            ->values()
                                                        as $det_esp_emp
                                                    ) {
                                                        $idsDetPed[] = $det_ped->id_detalle_pedido;
                                                    }
                                                } else {
                                                    foreach ($esp_emp->detalles as $det_esp_emp) {
                                                        $idsDetPed[] = $det_ped->id_detalle_pedido;
                                                    }
                                                }

                                            @endphp
                                        @endforeach
                                    @endif
                                @endforeach

                                @foreach ($detallePedidos->whereIn('id_detalle_pedido', $idsDetPed)->values() as $pos_det_ped => $det_ped)
                                    @if (
                                        !empty($det_ped->cliente_especificacion) &&
                                            !empty($det_ped->cliente_especificacion->especificacion) &&
                                            !empty($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque))
                                        @foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $pos_esp_emp => $esp_emp)
                                            @php
                                                $total_tallos = 0;

                                                $getCantidadDetallesEspecificacionByPedido = count($listadoVariedades)
                                                    ? $esp_emp->detalles
                                                        ->whereIn('id_variedad', $listadoVariedades)
                                                        ->count()
                                                    : $esp_emp->detalles->count();

                                                foreach ($esp_emp->detalles as $dep) {
                                                    $ramos_modificado = getRamosXCajaModificado(
                                                        $det_ped->id_detalle_pedido,
                                                        $dep->id_detalle_especificacionempaque,
                                                    );
                                                    $total_tallos +=
                                                        $det_ped->cantidad *
                                                        $dep->tallos_x_ramos *
                                                        (isset($ramos_modificado)
                                                            ? $ramos_modificado->cantidad
                                                            : $dep->cantidad);
                                                }

                                                $piezas_totales += $esp_emp->cantidad * $det_ped->cantidad;

                                                $detalleEspecificacionEmpaque = count($listadoVariedades)
                                                    ? $esp_emp->detalles
                                                        ->whereIn('id_variedad', $listadoVariedades)
                                                        ->values()
                                                    : $esp_emp->detalles;
                                            @endphp
                                            @php
                                                $bg_td_color = '#ffffff';
                                                if (!$opciones) {
                                                    foreach ($n_despachos as $n => $n_despacho) {
                                                        if (
                                                            $n_despacho->id_despacho == $pedido->id_despacho &&
                                                            $despachado > 0
                                                        ) {
                                                            $bg_td_color = $n_despacho->bg_color;
                                                        }
                                                    }
                                                }
                                            @endphp
                                            @foreach ($detalleEspecificacionEmpaque as $pos_det_esp => $det_esp)
                                                <tr style="border-bottom: 1px solid #9d9d9d;background-color: {{ $bg_td_color }};"
                                                    title="{{ !in_array($det_esp->id_variedad, explode('|', $pedido->variedad)) ? 'Confirmado' : 'Por confirmar' }}">
                                                    @if ($pos_det_esp == 0 && $pos_esp_emp == 0 && $pos_det_ped == 0)
                                                        @if (!$opciones)
                                                            <th class="text-center"
                                                                style="border-color: #9d9d9d;vertical-align: middle"
                                                                rowspan="{{ $getCantidadDetallesEspecificacionByPedido }}">
                                                                @if ($despachado > 0)
                                                                    <i class="fa fa-2x fa-check-circle text-success"
                                                                        title="Despachado" aria-hidden="true"></i>
                                                                @else
                                                                    <input type="checkbox" name="orden_despacho"
                                                                        class="orden_despacho"
                                                                        id="{{ $pedido->id_pedido }}"
                                                                        data-id_pedido="{{ $pedido->id_pedido }}"
                                                                        style="width: 18px;height:18px">
                                                                @endif
                                                            </th>
                                                            <th class="text-center"
                                                                style="border-color: #9d9d9d;font-weight: bold;font-size: 14px;"
                                                                rowspan="{{ count($idsDetPed) }}">
                                                                @if ($despachado > 0)
                                                                    {{ $despachoObj->n_despacho }}
                                                                @endif
                                                            </th>
                                                        @endif
                                                        @if ($opciones)
                                                            <th class="text-center" style="border-color: #9d9d9d"
                                                                rowspan="{{ count($idsDetPed) }}">
                                                                @if ($pedido->estado == 1)
                                                                    <input type="checkbox"
                                                                        name="check_unir_pedidos_{{ $pedido->id_pedido }}"
                                                                        id="check_unir_pedidos_{{ $pedido->id_pedido }}"
                                                                        onchange="habilita_unir_pedidos(this)"
                                                                        value="{{ $pedido->id_pedido }}"
                                                                        class="check_unir_pedidos"
                                                                        style="width:17px;height:17px"
                                                                        data-fecha_pedido="{{ $pedido->fecha_pedido }}"
                                                                        data-tipo_pedido="{{ $pedido->tipo_pedido }}">
                                                                @endif
                                                            </th>
                                                        @endif
                                                        @if ($pos_det_esp == 0 && $pos_esp_emp == 0 && $pos_det_ped == 0)
                                                            <th class="text-center" style="border-color: #9d9d9d"
                                                                rowspan="{{ count($idsDetPed) }}">
                                                                <b style="font-size: 14px">{{ $ped->packing }}</b>
                                                            </th>
                                                        @endif
                                                        <th class="text-center" style="border-color: #9d9d9d"
                                                            rowspan="{{ count($idsDetPed) }}">
                                                            <label for="check_unir_pedidos_{{ $pedido->id_pedido }}">
                                                                <span
                                                                    class="cliente_pedido">{{ getCliente($pedido->id_cliente)->detalle()->nombre }}</span>
                                                                <br />
                                                                Consignatario:
                                                                {{ isset($env) && isset($env->consignatario) ? $env->consignatario->nombre : '' }}
                                                                @php $precio_pedido = $ped->getPrecioByPedido() @endphp
                                                                @if ($opciones)
                                                                    <br>
                                                                    <b> Venta: ${{ number_format($precio_pedido, 2) }}
                                                                    </b>
                                                                    <br>
                                                                    <b> Cajas: {{ $ped->getCajasFisicas() }} </b>
                                                                    <br>
                                                                    <b> Ramos: {{ $ped->getRamosPedido() }} </b>
                                                                @endif
                                                                @if ($facturado != null)
                                                                    <br />
                                                                    <span class="badge bg-green"
                                                                        style="margin-top:8px;font-size: 13px;">
                                                                        <i class="fa fa-check-circle-o"
                                                                            aria-hidden="true"></i> Facturado
                                                                    </span>
                                                                @endif
                                                                @if (isset($ped->envios[0]->comprobante) && $ped->envios[0]->comprobante->estado == 6)
                                                                    <br />
                                                                    <span class="badge bg-red"
                                                                        style="margin-top:8px;font-size: 13px;">
                                                                        <i class="fa fa-times" aria-hidden="true"></i>
                                                                        Anulado
                                                                    </span>
                                                                @endif
                                                            </label>
                                                            <br>
                                                            <div id="status_pedido_proceso_{{ $pedido->id_pedido }}">
                                                            </div>
                                                        </th>
                                                        @if ($opciones)
                                                            <th class="text-center" style="border-color: #9d9d9d"
                                                                rowspan="{{ count($idsDetPed) }}">
                                                                {{ $pedido->tipo_pedido }}
                                                                @if ($pedido->orden_fija != '')
                                                                    <br>
                                                                    #{{ $pedido->orden_fija }}
                                                                @endif
                                                            </th>
                                                        @endif
                                                        <th class="text-center" style="border-color: #9d9d9d"
                                                            rowspan="{{ count($idsDetPed) }}">
                                                            @php setlocale(LC_TIME,"es_ES.UTF-8") @endphp
                                                            {{ getDiaSemanaByFecha($pedido->fecha_pedido) }}
                                                            <br>
                                                            {{ $pedido->fecha_pedido }}
                                                        </th>
                                                    @endif
                                                    @if ($opciones)
                                                        @if ($pos_det_esp == 0 && $pos_esp_emp == 0)
                                                            <th style="border-color: {{ (!isset($id_variedad) || $id_variedad == $det_esp->id_variedad) && (!isset($id_planta) || $id_planta == $det_esp->variedad->planta->id_planta) ? '#9d9d9d' : '#f4f4f4' }};vertical-align: middle"
                                                                id="td_datos_exportacion_{{ $pedido->id_pedido }}"
                                                                rowspan="{{ $getCantidadDetallesEspecificacionByPedido }}">
                                                                @if (count(getDatosExportacionCliente($det_ped->id_detalle_pedido)))
                                                                    <div style="display:flex;align-items: center;">
                                                                        @if (es_server())
                                                                            {{--
                                                                            <div style="margin-left: 3px;">
                                                                                <input type="checkbox"
                                                                                    name="check_marcacion_{{ $det_ped->id_detalle_pedido }}"
                                                                                    id="check_marcacion_{{ $det_ped->id_detalle_pedido }}"
                                                                                    data-id_pedido="{{ $det_ped->pedido->id_pedido }}"
                                                                                    data-id_det_pedido="{{ $det_ped->id_detalle_pedido }}"
                                                                                    class="check_pedido_marcacion"
                                                                                    data-tipo_pedido="{{ $ped->tipo_pedido }}">
                                                                            </div>
                                                                             --}}
                                                                        @endif
                                                                        <div style="margin-left: 3px;">
                                                                            <label
                                                                                for="check_marcacion_{{ $det_ped->id_detalle_pedido }}">
                                                                                <ul
                                                                                    style="padding: 0;margin-bottom: 0">
                                                                                    @foreach (getDatosExportacionCliente($det_ped->id_detalle_pedido) as $de)
                                                                                        <li style="list-style: none"
                                                                                            class="valor_marcacion">
                                                                                            {{-- <b>{{strtoupper($de->nombre)}}:</b> --}}
                                                                                            {{ $de->valor }} </li>
                                                                                    @endforeach
                                                                                </ul>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                                {{-- <sup><b>{{$det_ped->id_detalle_pedido}}</b></sup> --}}
                                                            </th>
                                                        @endif
                                                    @endif
                                                    @if ($opciones)
                                                        <th class="text-center"
                                                            style="border-color:{{ (!isset($id_variedad) || $id_variedad == $det_esp->id_variedad) && (!isset($id_planta) || $id_planta == $det_esp->variedad->planta->id_planta) ? '#9d9d9d' : '#f4f4f4' }}">
                                                            @if (!isset($id_variedad) || $id_variedad == $det_esp->id_variedad)
                                                                @if (!isset($id_planta) || $id_planta == $det_esp->variedad->planta->id_planta)
                                                                    {{ $det_esp->variedad->planta->nombre . ' - ' . $det_esp->variedad->nombre }}
                                                                    {{ $det_esp->longitud_ramo }}
                                                                    {{ $det_esp->unidad_medida->siglas }}
                                                                @endif
                                                            @endif
                                                        </th>
                                                        @if ($pos_det_esp == 0 && $pos_esp_emp == 0)
                                                            <th class="text-center"
                                                                style="border-color: {{ (!isset($id_variedad) || $id_variedad == $det_esp->id_variedad) && (!isset($id_planta) || $id_planta == $det_esp->variedad->planta->id_planta) ? '#9d9d9d' : '#f4f4f4' }}"
                                                                rowspan="{{ $getCantidadDetallesEspecificacionByPedido }}">
                                                                {{ explode('|', $esp_emp->empaque->nombre)[0] }}
                                                            </th>
                                                        @endif
                                                        <th class="text-center"
                                                            style="border-color: {{ (!isset($id_variedad) || $id_variedad == $det_esp->id_variedad) && (!isset($id_planta) || $id_planta == $det_esp->variedad->planta->id_planta) ? '#9d9d9d' : '#f4f4f4' }}">
                                                            {{ explode('|', $det_esp->empaque_p->nombre)[0] }}
                                                        </th>
                                                    @endif
                                                    @if ($opciones)
                                                        @if ($pos_det_esp == 0 && $pos_esp_emp == 0)
                                                            <th class="text-center"
                                                                style="border-color: {{ (!isset($id_variedad) || $id_variedad == $det_esp->id_variedad) && (!isset($id_planta) || $id_planta == $det_esp->variedad->planta->id_planta) ? '#9d9d9d' : '#f4f4f4' }}"
                                                                rowspan="{{ $getCantidadDetallesEspecificacionByPedido }}">
                                                                {{ $esp_emp->cantidad * $det_ped->cantidad }}
                                                            </th>
                                                        @endif
                                                    @else
                                                        @if ($pos_det_esp == 0 && $pos_esp_emp == 0 && $pos_det_ped == 0)
                                                            <th class="text-center" style="border-color: #9d9d9d"
                                                                rowspan="{{ $getCantidadDetallesEspecificacionByPedido }}">
                                                                {{ $piezas_despacho }}
                                                            </th>
                                                        @endif
                                                    @endif
                                                    @php
                                                        $ramos_modificado = getRamosXCajaModificado(
                                                            $det_ped->id_detalle_pedido,
                                                            $det_esp->id_detalle_especificacionempaque,
                                                        );
                                                        if (!getFacturaAnulada($pedido->id_pedido)) {
                                                            $ramos_totales +=
                                                                (isset($ramos_modificado)
                                                                    ? $ramos_modificado->cantidad
                                                                    : $det_esp->cantidad) *
                                                                $esp_emp->cantidad *
                                                                $det_ped->cantidad;
                                                            if (!in_array($det_esp->id_variedad, $variedades)) {
                                                                array_push($variedades, $det_esp->id_variedad);
                                                            }
                                                        }
                                                    @endphp
                                                    @if ($opciones)
                                                        <th class="text-center"
                                                            style="border-color: {{ (!isset($id_variedad) || $id_variedad == $det_esp->id_variedad) && (!isset($id_planta) || $id_planta == $det_esp->variedad->planta->id_planta) ? '#9d9d9d' : '#f4f4f4' }}">
                                                            {{ isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp->cantidad }}
                                                        </th>
                                                        <th class="text-center"
                                                            style="border-color: {{ (!isset($id_variedad) || $id_variedad == $det_esp->id_variedad) && (!isset($id_planta) || $id_planta == $det_esp->variedad->planta->id_planta) ? '#9d9d9d' : '#f4f4f4' }}">
                                                            {{ $det_esp->tallos_x_ramos }}
                                                        </th>
                                                    @else
                                                        @if ($pos_det_esp == 0 && $pos_esp_emp == 0 && $pos_det_ped == 0)
                                                            <th class="text-center" style="border-color: #9d9d9d"
                                                                rowspan="{{ $getCantidadDetallesEspecificacionByPedido }}">
                                                                {{ $full }}</th>
                                                            <th class="text-center" style="border-color: #9d9d9d"
                                                                rowspan="{{ $getCantidadDetallesEspecificacionByPedido }}">
                                                                {{ $half }}</th>
                                                            <th class="text-center" style="border-color: #9d9d9d"
                                                                rowspan="{{ $getCantidadDetallesEspecificacionByPedido }}">
                                                                {{ $cuarto }}</th>
                                                            <th class="text-center" style="border-color: #9d9d9d"
                                                                rowspan="{{ $getCantidadDetallesEspecificacionByPedido }}">
                                                                {{ $octavo }}</th>
                                                            <th class="text-center" style="border-color: #9d9d9d"
                                                                rowspan="{{ $getCantidadDetallesEspecificacionByPedido }}">
                                                                {{ $sb }}</th>
                                                        @endif
                                                    @endif
                                                    @if ($opciones)
                                                        @if ($pos_det_esp == 0 && $pos_esp_emp == 0)
                                                            <th class="text-center"
                                                                style="border-color: {{ (!isset($id_variedad) || $id_variedad == $det_esp->id_variedad) && (!isset($id_planta) || $id_planta == $det_esp->variedad->planta->id_planta) ? '#9d9d9d' : '#f4f4f4' }}"
                                                                rowspan="{{ $getCantidadDetallesEspecificacionByPedido }}">
                                                                {{ $total_tallos }}
                                                            </th>
                                                        @endif
                                                        <th class="text-center"
                                                            style="border-color: {{ (!isset($id_variedad) || $id_variedad == $det_esp->id_variedad) && (!isset($id_planta) || $id_planta == $det_esp->variedad->planta->id_planta) ? '#9d9d9d' : '#f4f4f4' }}">
                                                            @if (isset(explode('|', $det_ped->precio)[$pos_det_esp]))
                                                                ${{ explode(';', explode('|', $det_ped->precio)[$pos_det_esp])[0] }}
                                                            @else
                                                                <span><b class="error">ERROR</b></span>
                                                            @endif
                                                        </th>
                                                    @endif
                                                    @if ($pos_det_esp == 0 && $pos_esp_emp == 0 && $pos_det_ped == 0)
                                                        <th style="border-color: #9d9d9d" class="text-center"
                                                            rowspan="{{ count($idsDetPed) }}">
                                                            <span
                                                                class="agencia_pedido">{{ getAgenciaCarga($det_ped->id_agencia_carga)->nombre }}</span>
                                                        </th>
                                                        @if ($opciones)
                                                            @if (es_server())
                                                                <th class="text-center" style="border-color: #9d9d9d"
                                                                    id="td_opciones_{{ $pedido->id_pedido }}"
                                                                    rowspan="{{ count($idsDetPed) }}">
                                                                    @if ($ped->estado == 1)
                                                                        <div class="input-group-btn">
                                                                            <button type="button"
                                                                                class="btn btn-yura_default btn-xs dropdown-toggle"
                                                                                data-toggle="dropdown"
                                                                                aria-expanded="false">
                                                                                Acciones <span
                                                                                    class="fa fa-caret-down"></span>
                                                                            </button>
                                                                            <ul class="dropdown-menu dropdown-menu-right sombra_pequeña"
                                                                                style="background-color: #c8c8c8">
                                                                                <li>
                                                                                    <a class="link_opciones_{{ $pedido->id_pedido }}"
                                                                                        href="javascript:void(0)"
                                                                                        style="color: black"
                                                                                        onclick="modificar_pedido('{{ $pedido->id_pedido }}')">
                                                                                        <i
                                                                                            class="fa fa-fw fa-pencil"></i>
                                                                                        Editar pedido <sup
                                                                                            class="text-color_yura_danger"><em>new</em></sup>
                                                                                    </a>
                                                                                </li>
                                                                                @if ($ped->tipo_pedido != 'STANDING ORDER')
                                                                                    {{--
                                                                                    <li>
                                                                                        <a class="link_opciones_{{ $pedido->id_pedido }}" href="javascript:void(0)"
                                                                                            style="color: black"
                                                                                            onclick="editar_pedido('{{ $pedido->id_cliente }}','{{ $pedido->id_pedido }}')">
                                                                                            <i class="fa fa-fw fa-pencil"
                                                                                                aria-hidden="true"></i>
                                                                                            Editar pedido diario
                                                                                        </a>
                                                                                    </li>
                                                                                     --}}
                                                                                @else
                                                                                    {{--
                                                                                    <li>
                                                                                        <a style="color: black"
                                                                                            href="javascript:void(0)"
                                                                                            onclick="editar_pedido('{{ $pedido->id_cliente }}','{{ $pedido->id_pedido }}','{{ $ped->tipo_pedido }}')">
                                                                                            <i
                                                                                                class="fa fa-fw fa-pencil"></i>
                                                                                            Editar pedido fijo
                                                                                        </a>
                                                                                    </li>
                                                                                    --}}
                                                                                    <li>
                                                                                        <a class="link_opciones_{{ $pedido->id_pedido }}"
                                                                                            style="color: black"
                                                                                            href="javascript:void(0)"
                                                                                            onclick="mover_fecha_orden_fija('{{ $pedido->id_pedido }}')">
                                                                                            <i
                                                                                                class="fa fa-fw fa-calendar"></i>
                                                                                            Mover Fecha de Orden Fija
                                                                                        </a>
                                                                                    </li>
                                                                                @endif
                                                                                <li>
                                                                                    <a target="_blank"
                                                                                        style="color: black"
                                                                                        href="{{ url('pedidos/desglose_pedido', [$ped->id_pedido]) }}">
                                                                                        <i
                                                                                            class="fa fa-fw fa-file-text-o"></i>
                                                                                        Pre factura
                                                                                    </a>
                                                                                </li>
                                                                                <li>
                                                                                    <a class="link_opciones_{{ $pedido->id_pedido }}"
                                                                                        style="color: black"
                                                                                        href="javascript:void(0)"
                                                                                        onclick="restaurar_recetas('{{ $pedido->id_pedido }}')">
                                                                                        <i
                                                                                            class="fa fa-fw fa-gift"></i>
                                                                                        Restaurar Recetas
                                                                                    </a>
                                                                                </li>
                                                                                @if (
                                                                                    (isset($ped->envios[0]->comprobante) && $ped->envios[0]->comprobante->estado != 6) ||
                                                                                        !isset($ped->envios[0]->comprobante))
                                                                                    @if ($facturado == null)
                                                                                        <li>
                                                                                            <a class="link_opciones_{{ $pedido->id_pedido }}"
                                                                                                href="javascript:void(0)"
                                                                                                id="edit_pedidos"
                                                                                                style="color: black"
                                                                                                onclick="cancelar_pedidos('{{ $pedido->id_pedido }}','','{{ $det_ped->estado }}','{{ @csrf_token() }}','{{ $pedido->tipo_pedido }}')">
                                                                                                <i class="fa fa-fw fa-{!! $det_ped->estado == 1 ? 'trash' : 'undo' !!}"
                                                                                                    aria-hidden="true"></i>
                                                                                                {!! $det_ped->estado == 1 ? 'Cancelar pedido' : 'Activar pedido' !!}
                                                                                            </a>
                                                                                        </li>
                                                                                        @if ($pedido->tipo_pedido == 'STANDING ORDER')
                                                                                            <li>
                                                                                                <a class="link_opciones_{{ $pedido->id_pedido }}"
                                                                                                    href="javascript:void(0)"
                                                                                                    style="color: black"
                                                                                                    onclick="delete_orden_fija('{{ $pedido->id_pedido }}')">
                                                                                                    <i
                                                                                                        class="fa fa-fw fa-trash"></i>
                                                                                                    Cancelar toda la
                                                                                                    Orden
                                                                                                    Fija
                                                                                                </a>
                                                                                            </li>
                                                                                        @endif
                                                                                        @if ($pedido->empaquetado == 0)
                                                                                            @if ($ped->haveDistribucion() == 1)
                                                                                                <li>
                                                                                                    <a class="link_opciones_{{ $pedido->id_pedido }}"
                                                                                                        href="javascript:void(0)"
                                                                                                        style="color: black"
                                                                                                        onclick="distribuir_orden_semanal('{{ $pedido->id_pedido }}')">
                                                                                                        <i
                                                                                                            class="fa fa-fw fa-gift"></i>
                                                                                                        Distribuir
                                                                                                    </a>
                                                                                                </li>
                                                                                            @elseif($ped->haveDistribucion() == 2)
                                                                                                <li>
                                                                                                    <a class="link_opciones_{{ $pedido->id_pedido }}"
                                                                                                        href="javascript:void(0)"
                                                                                                        onclick="ver_distribucion_orden_semanal('{{ $pedido->id_pedido }}')"
                                                                                                        style="cursor:pointer; padding:5px 3px; width:100%; color: black">
                                                                                                        <em> Ver
                                                                                                            distribución</em>
                                                                                                    </a>
                                                                                                </li>
                                                                                            @endif
                                                                                            <li>
                                                                                                <a class="link_opciones_{{ $pedido->id_pedido }}"
                                                                                                    href="javascript:void(0)"
                                                                                                    style="color: black"
                                                                                                    onclick="copiar_pedido('{{ $pedido->id_pedido }}')">
                                                                                                    <i class="fa fa-files-o fa-fw"
                                                                                                        aria-hidden="true"></i>
                                                                                                    Copiar pedido
                                                                                                </a>
                                                                                            </li>
                                                                                            {{-- <li>
                                                                                                <a class="link_opciones_{{ $pedido->id_pedido }}" href="javascript:void(0)" style="color: black"
                                                                                                onclick="duplicar_pedido('{{$pedido->id_pedido}}','{{$pedido->id_cliente}}','{{$pedido->tipo_pedido}}')">
                                                                                                    <i class="fa fa-files-o fa-fw" aria-hidden="true"></i> Duplicar pedido
                                                                                                </a>
                                                                                            </li> --}}
                                                                                            {{-- <li>
                                                                                                <a class="link_opciones_{{ $pedido->id_pedido }}" href="javascript:void(0)" style="color: black"
                                                                                                onclick="empaquetar_pedido('{{$pedido->id_pedido}}','{{csrf_token()}}')">
                                                                                                    <i class="fa fa-cube fa-fw"></i> Empaquetar pedido
                                                                                                </a>
                                                                                            </li> --}}
                                                                                        @endif
                                                                                        {{-- @if (!isset($ped->envios[0]->comprobante) || (isset($ped->envios[0]->comprobante) && !$ped->envios[0]->comprobante->ficticio))
                                                                                            <li>
                                                                                                <a class="link_opciones_{{ $pedido->id_pedido }}" href="javascript:void(0)" style="color: black"
                                                                                                    onclick="facturar_pedido('{{$pedido->id_pedido}}')">
                                                                                                    <i class="fa fa-usd fa-fw" aria-hidden="true"></i> Completar envío
                                                                                                </a>
                                                                                            </li>
                                                                                        @endif --}}
                                                                                    @else
                                                                                        <li>
                                                                                            <a target="_blank"
                                                                                                style="color: black"
                                                                                                href="{{ url('pedidos/documento_pre_factura', [$pedido->id_pedido, true]) }}"
                                                                                                class="btn btn-info btn-xs">
                                                                                                <i class="fa fa-user-circle-o fa-fw"
                                                                                                    aria-hidden="true"></i>
                                                                                                Ver factura Cliente
                                                                                            </a>
                                                                                        </li>
                                                                                    @endif
                                                                                    @if ($firmado != null)
                                                                                        <li>
                                                                                            <a target="_blank"
                                                                                                style="color: black"
                                                                                                href="{{ url('pedidos/documento_pre_factura', [$pedido->id_pedido, true]) }}">
                                                                                                <i class="fa fa-user-circle-o fa-fw"
                                                                                                    aria-hidden="true"></i>
                                                                                                Ver factura Cliente
                                                                                            </a>
                                                                                        </li>
                                                                                    @endif
                                                                                    <li>
                                                                                        <a target="_blank"
                                                                                            style="color: black"
                                                                                            href="{{ url('pedidos/crear_packing_list', $pedido->id_pedido) }}">
                                                                                            <i
                                                                                                class="fa fa-cubes fa-fw"></i>
                                                                                            Descargar packing list
                                                                                        </a>
                                                                                    </li>
                                                                                    {{-- <li>
                                                                                        <a class="link_opciones_{{ $pedido->id_pedido }}" href="javascript:void(0)" style="color: black"  onclick="eliminar_detalle_pedido_masivo('{{$pedido->id_pedido}}')">
                                                                                            <i class="fa fa-trash"></i> Eliminar detalles masivamente
                                                                                        </a>
                                                                                    </li> --}}
                                                                                @endif

                                                                            </ul>
                                                                        </div>
                                                                    @endif
                                                                </th>
                                                            @endif
                                                        @endif
                                                        @if (!$opciones)
                                                            <th rowspan="{{ $getCantidadDetallesEspecificacionByPedido }}"
                                                                class="text-left"
                                                                style="border-color: #9d9d9d;padding: 8px;">
                                                                @if ($despachado > 0)
                                                                    <span
                                                                        style="font-weight:bold;font-size:14px;">Reasignar
                                                                        pedido</span>
                                                                    <select
                                                                        id="select_detalle_despacho{{ $pedido->id_detalle_despacho }}"
                                                                        class="form-control form-group"
                                                                        data-id_despacho_anterior="{{ $pedido->id_despacho }}"
                                                                        onchange="update_despacho_detalle(event, '{{ $pedido->id_detalle_despacho }}')"
                                                                        style="margin-top: 8px;">
                                                                        <option value="">Seleccione despacho
                                                                        </option>
                                                                        @foreach ($n_despachos as $n => $n_despacho)
                                                                            <option
                                                                                value="{{ $n_despacho->id_despacho }}"
                                                                                {{ $n_despacho->id_despacho == $pedido->id_despacho ? 'selected' : '' }}>
                                                                                Despacho {{ $n_despacho->n_despacho }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                @endif
                                                                @if ($pedido->tipo_especificacion === 'T')
                                                                    <a target="_blank"
                                                                        href="{{ url('pedidos/crear_packing_list', [$pedido->id_pedido, true]) }}"
                                                                        class="btn btn-info btn-xs"
                                                                        title="Packing list">
                                                                        <i class="fa fa-cubes"></i>
                                                                    </a>
                                                                @endif
                                                            </th>
                                                        @endif
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="row" style="margin-top: 10px">
            </div>
            <div id="div_resumen_pedidos">
                {{-- <div class="col-md-6">
                    <table class="table-striped table-bordered table-responsive" width="100%"
                        style="border: 1px solid #9d9d9d; font-size: 0.8em">
                        <tr>
                            <th class="text-center th_yura_green">
                                TOTALES POR PRODUCTO
                            </th>
                            <th class="text-center th_yura_green">
                                TALLOS
                            </th>
                            <th class="text-center th_yura_green">
                                RAMOS
                            </th>
                        </tr>
                        @php
                            $totalRamos = 0;
                            $totalTallos = 0;
                        @endphp
                        @foreach ($ramos_x_variedad as $producto => $item)
                            <tr>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    {{ $producto }}
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    {{ array_sum(array_column($item, 'tallos')) }}
                                    @php
                                        $totalRamos += array_sum(array_column($item, 'ramos'));
                                    @endphp
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    {{ array_sum(array_column($item, 'ramos')) }}
                                    @php
                                        $totalTallos += array_sum(array_column($item, 'tallos'));
                                    @endphp
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <th class="text-center th_yura_green">
                                TOTALES
                            </th>
                            <td class="text-center th_yura_green">
                                <b> {{ $totalTallos }}</b>
                            </td>
                            <td class="text-center th_yura_green">
                                <b> {{ $totalRamos }}</b>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table-striped table-bordered table-responsive" width="100%"
                        style="border: 1px solid #9d9d9d; font-size: 0.8em">
                        <tr>
                            <th class="text-center th_yura_green">
                                CAJAS TOTALESxx
                            </th>
                            <td class="text-center" style="border-color: #9d9d9d" colspan="2">
                                {{ $piezas_totales }}
                            </td>
                        </tr>
                        <tr>
                            <th class="text-center th_yura_green">
                                CAJAS FULL
                            </th>
                            <td class="text-center" style="border-color: #9d9d9d">
                                {{ $full_total }}
                            </td>
                            <td class="text-center" style="border-color: #9d9d9d">
                                <b>Full:</b> {{ $full_total }}
                            </td>
                        </tr>
                        <tr>
                            <th class="text-center th_yura_green">
                                CAJAS TABACO
                            </th>
                            <td class="text-center" style="border-color: #9d9d9d">
                                {{ $half_total }}
                            </td>
                            <td class="text-center" style="border-color: #9d9d9d">
                                <b>Full:</b> {{ $half_full_equivalente }}
                            </td>
                        </tr>
                        <tr>
                            <th class="text-center th_yura_green">
                                CAJAS CUARTO
                            </th>
                            <td class="text-center" style="border-color: #9d9d9d">
                                {{ $cuarto_total }}
                            </td>
                            <td class="text-center" style="border-color: #9d9d9d">
                                <b>Full:</b> {{ $cuarto_full_equivalente }}
                            </td>
                        </tr>
                        <tr>
                            <th class="text-center th_yura_green">
                                CAJAS OCTAVO
                            </th>
                            <td class="text-center" style="border-color: #9d9d9d">
                                {{ $octavo_total }}
                            </td>
                            <td class="text-center" style="border-color: #9d9d9d">
                                <b>Full:</b> {{ $octavo_full_equivalente }}
                            </td>
                        </tr>
                    </table>
                </div> --}}
            </div>
        </div>
    @else
        <div class="alert alert-info text-center">No se han encontrado pedidos para esta fecha</div>
    @endif
</div>
<style>
    .modal {
        z-index: 9999999999 !important
    }

    /*.swal2-container {
        z-index: 99999999999 !important
    }*/

    /*.select2-container {
        z-index: 99999999999 !important
    }*/

    table#table_content_aperturas table {
        width: 100%;
    }

    div#parent {
        height: 600px;
        overflow-y: auto;
    }

    table#table_content_aperturas thead tr th {
        position: sticky;
        top: 0;
    }

    table#table_content_aperturas table {
        border-collapse: collapse;
    }

    table#table_content_aperturas table {
        width: 100%;
        font-family: sans-serif;
    }

    table#table_content_aperturas table td {
        padding: 16px;
    }

    table#table_content_aperturas tbody tr {
        border-bottom: 2px solid #e8e8e8;
    }

    table#table_content_aperturas thead {
        font-weight: 500;
        color: rgba(0, 0, 0, 0.85);
        z-index: 99;
        position: relative;
    }

    table#table_content_aperturas tbody tr:hover {
        background: #e6f7ff;
    }
</style>

<script>
    function exportar_listado_despacho(id_configuracion_empresa) {
        $.LoadingOverlay('show');
        window.open('{{ url('despachos/exportar_pedidos_despacho') }}?fecha_desde=' + $('#fecha_pedidos_search')
            .val() +
            '&fecha_hasta=' + $('#fecha_pedidos_search_hasta').val() +
            '&id_configuracion_empresa=' + id_configuracion_empresa, '_blank');
        $.LoadingOverlay('hide');
    }

    function exportar_excel_listado_despacho_script() {
        $.LoadingOverlay('show');
        window.open('{{ url('despachos/exportar_listado_pedidos_despacho') }}?fecha_pedido=' + $(
                '#fecha_pedidos_search').val() +
            '&fecha_pedido_hasta=' + $('#fecha_pedidos_search_hasta').val() +
            '&fecha=' + $('#fecha_pedidos_search').val() +
            '&id_configuracion_empresa=' + $('#id_configuracion_empresa_despacho').val(), '_blank');
        $.LoadingOverlay('hide');
    }

    function delete_orden_fija(id_ped) {
        mensaje = {
            title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmacion',
            mensaje: '<div class="alert alert-danger text-center">¿Está seguro de ELIMINAR la orden fija?</div>',
        };
        modal_quest('modal_delete_orden_fija', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id_ped: id_ped,
                }
                post_jquery_m('{{ url('pedidos/delete_orden_fija') }}', datos, function(retorno) {
                    listar_resumen_pedidos(
                        document.getElementById('fecha_pedidos_search').value,
                        true,
                        document.getElementById('id_configuracion_pedido').value,
                        document.getElementById('id_cliente').value
                    );
                });
            });
    }

    function mover_fecha_orden_fija(pedido) {
        datos = {
            pedido: pedido
        }
        get_jquery('{{ url('pedidos/mover_fecha_orden_fija') }}', datos, function(retorno) {
            modal_view('modal_mover_fecha_orden_fija', retorno,
                '<i class="fa fa-fw fa-calendar"></i> Formulario Orden Fija',
                true, false, '{{ isPC() ? '50%' : '' }}',
                function() {});
        });
    }

    function copiar_pedido(pedido) {
        datos = {
            pedido: pedido
        }
        get_jquery('{{ url('pedidos/copiar_pedido') }}', datos, function(retorno) {
            modal_view('modal_copiar_pedido', retorno, '<i class="fa fa-fw fa-calendar"></i> Copiar Pedido',
                true, false, '{{ isPC() ? '50%' : '' }}',
                function() {});
        });
    }

    function modificar_pedido(pedido) {
        datos = {
            pedido: pedido
        }
        get_jquery('{{ url('pedidos/modificar_pedido') }}', datos, function(retorno) {
            modal_view('modal-view_modificar_pedido', retorno,
                '<i class="fa fa-fw fa-plus"></i> Modificar Pedido', true, false,
                '{{ isPC() ? '95%' : '' }}');

        });
    }

    function exportar_jire() {
        $.LoadingOverlay('show');
        window.open('{{ url('despachos/exportar_jire_cabecera') }}?fecha_desde=' + $('#fecha_pedidos_search').val() +
            '&fecha_hasta=' + $('#fecha_pedidos_search_hasta').val(), '_blank');
        $.LoadingOverlay('hide');
    }

    function generar_packings(id_ped) {
        mensaje = {
            title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmacion',
            mensaje: '<div class="alert alert-info text-center" style="font-size: 16px">¿Está seguro de <b>GENERAR</b> los numeros de packings?</div>',
        };
        modal_quest('modal_generar_packings', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    desde: $('#fecha_pedidos_search').val(),
                    hasta: $('#fecha_pedidos_search_hasta').val(),
                }
                post_jquery_m('{{ url('pedidos/generar_packings') }}', datos, function(retorno) {
                    listar_resumen_pedidos(
                        document.getElementById('fecha_pedidos_search').value,
                        true,
                        document.getElementById('id_configuracion_pedido').value,
                        document.getElementById('id_cliente').value
                    );
                });
            });
    }

    function combinar_pedidos(id_ped) {
        mensaje = {
            title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmacion',
            mensaje: '<div class="alert alert-info text-center" style="font-size: 16px">¿Está seguro de <b>COMBINAR</b> los pedidos?</div>',
        };
        modal_quest('modal_combinar_pedidos', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                check_unir_pedidos = $('.check_unir_pedidos');
                data = [];
                for (i = 0; i < check_unir_pedidos.length; i++) {
                    id = check_unir_pedidos[i].id;
                    if ($('#' + id).prop('checked') == true) {
                        id_pedido = check_unir_pedidos[i].value;
                        data.push(id_pedido);
                    }
                }
                if (data.length > 0) {
                    datos = {
                        _token: '{{ csrf_token() }}',
                        data: JSON.stringify(data),
                    }
                    post_jquery_m('{{ url('pedidos/combinar_pedidos') }}', datos, function(retorno) {
                        listar_resumen_pedidos(
                            document.getElementById('fecha_pedidos_search').value,
                            true,
                            document.getElementById('id_configuracion_pedido').value,
                            document.getElementById('id_cliente').value
                        );
                    });
                }
            });
    }

    function checkProcesosPendientes() {

        var arrIdsPedido = [];

        $('.check_unir_pedidos').each(function() {
            var value = $(this).val();
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
                        $('#check_unir_pedidos_' + pedido.id_pedido).attr('disabled', 'disabled');
                        $('#status_pedido_proceso_' + pedido.id_pedido).html(`
                        <div style="border: 2px solid #000000;background-color:#555555;padding: 8px;margin-top: 8px;" title="${pedido.descripcion}">
                            <span style="font-weight: normal;color: #FFFFFF;font-size: 12px;">Proceso de orden en curso<br>
                                Completado en un <span style="font-weight: bold;font-size: 14px;">${pedido.progreso}%</span>.
                                <br>
                                    Proceso iniciado por: <span style="font-weight: bold;">${pedido.username}</span>
                                <br>
                                <br>
                            </span>
                            <div>
                                <div style="margin: 0 auto;" class="benchflow-spinner" bis_skin_checked="1"></div>
                            </div>
                        </div>`);
                        $(`.link_opciones_${pedido.id_pedido}`).each(function() {
                            if (!$(this).hasClass('link-disabled')) {
                                $(this).addClass("link-disabled");
                            }
                        });
                    } else {
                        $('#check_unir_pedidos_' + pedido.id_pedido).attr('disabled', null);
                        $('#status_pedido_proceso_' + pedido.id_pedido).html('');
                        $(`.link_opciones_${pedido.id_pedido}`).each(function() {
                            $(this).removeClass("link-disabled");
                        });
                    }
                });
            }
        });
    }
    @if ($opciones)
        checkProcesosPendientes();
        setInterval(checkProcesosPendientes, 15000);
        ver_resumen();
    @endif
</script>
