{{-- ENCABEZADO --}}
<input type="hidden" id="id_pedido_selected" value="{{ $pedido->id_pedido }}">
<div style="overflow-x: scroll">
    <table style="width: 100%">
        <tr>
            <td style="min-width: 180px" class="form_fecha">
                <div class="input-group">
                    <span class="input-group-addon bg-yura_dark">
                        Fecha
                    </span>
                    <input type="date" id="form_fecha" class="form-control" value="{{ $pedido->fecha_pedido }}">
                </div>
            </td>
            <td style="min-width: 220px">
                <div class="input-group">
                    <span class="input-group-addon bg-yura_dark">
                        Cliente
                    </span>
                    <select id="form_cliente" class="form-control" style="width: 100%" disabled
                        onchange="form_seleccionar_cliente()">
                        <option value="">Seleccione</option>
                        @foreach ($clientes as $cliente)
                            <option value="{{ $cliente->id_cliente }}"
                                {{ $cliente->id_cliente == $pedido->id_cliente ? 'selected' : '' }}>
                                {{ $cliente->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </td>
            <td style="min-width: 220px">
                <div class="input-group">
                    <span class="input-group-addon bg-yura_dark">
                        Consignatario
                    </span>
                    <select id="form_consignatario" class="form-control" style="width: 100%">
                        <option value="" {{ $consignatario == '' ? 'selected' : '' }}>
                            Sin Consignatario</option>
                        @foreach ($consignatarios as $c)
                            <option value="{{ $c->id_consignatario }}"
                                {{ $consignatario != '' && $c->id_consignatario == $consignatario->id_consignatario ? 'selected' : '' }}>
                                {{ $c->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </td>
            <td style="min-width: 200px">
                <div class="input-group">
                    <span class="input-group-addon bg-yura_dark">
                        Agencia
                    </span>
                    <select id="form_agencia" class="form-control" style="width: 100%">
                        @foreach ($agencias as $a)
                            <option value="{{ $a->id_agencia_carga }}"
                                {{ $a->id_agencia_carga == $agencia->id_agencia_carga ? 'selected' : '' }}>
                                {{ $a->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </td>
            <td style="min-width: 200px">
                <div class="input-group">
                    <span class="input-group-addon bg-yura_dark">
                        Tipo
                    </span>
                    <select id="form_tipo_pedido" class="form-control" style="width: 100%">
                        <option value="OPEN MARKET" {{ $pedido->tipo_pedido == 'OPEN MARKET' ? 'selected' : '' }}>
                            OPEN MARKET
                        </option>
                        @if ($pedido->tipo_pedido == 'STANDING ORDER')
                            <option value="STANDING ORDER"
                                {{ $pedido->tipo_pedido == 'STANDING ORDER' ? 'selected' : '' }}>
                                STANDING ORDER
                            </option>
                        @endif
                    </select>
                </div>
            </td>
        </tr>
    </table>
</div>
{{-- TAB especificaciones-pedido --}}
<ul class="nav nav-pills nav-justified" style="margin-top: 5px">
    <li>
        <a data-toggle="tab" href="#tab-especificaciones">
            <i class="fa fa-fw fa-list"></i> Pedidos Simples
        </a>
    </li>
    <li>
        <a data-toggle="tab" href="#tab-combos">
            <i class="fa fa-fw fa-gift"></i> Pedidos Combos
        </a>
    </li>
    <li class="active">
        <a data-toggle="tab" href="#tab-contenido_pedido">
            <i class="fa fa-fw fa-shopping-cart"></i> Contenido del Pedido
            <sup><span class="badge" id="span_total_piezas_pedido">0 cajas</span></sup>
        </a>
    </li>
</ul>

<div class="tab-content" style="margin-top: 5px;">
    <div id="tab-especificaciones" class="tab-pane fade" style="overflow-x: scroll">
        <table style="width: 100%">
            <tr>
                <td>
                    <div class="input-group" style="min-width: 220px">
                        <span class="input-group-addon bg-yura_dark">
                            Variedad
                        </span>
                        <select id="form_planta" class="form-control" style="width: 100%"
                            onchange="form_seleccionar_planta(); buscar_form_especificaciones()">
                            {!! $option_plantas !!}
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group" style="min-width: 220px">
                        <span class="input-group-addon bg-yura_dark">
                            Color
                        </span>
                        <select id="form_variedad" class="form-control" style="width: 100%"
                            onchange="buscar_form_especificaciones()">
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group" style="min-width: 220px">
                        <span class="input-group-addon bg-yura_dark">
                            Cajas
                        </span>
                        <select id="form_caja" class="form-control" style="width: 100%"
                            onchange="buscar_form_especificaciones()">
                            {!! $option_cajas !!}
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group" style="min-width: 180px">
                        <span class="input-group-addon bg-yura_dark">
                            Ramos x Caja
                        </span>
                        <input type="number" id="form_ramos_x_caja" class="form-control text-center"
                            style="width: 100%" onchange="buscar_form_especificaciones()">
                    </div>
                </td>
                <td>
                    <div class="input-group" style="min-width: 180px">
                        <span class="input-group-addon bg-yura_dark">
                            Longitud
                        </span>
                        <input type="number" id="form_longitud" class="form-control text-center" style="width: 100%"
                            onchange="buscar_form_especificaciones()">
                        <div class="input-group-btn">
                            <button class="btn btn-yura_dark" onclick="buscar_form_especificaciones()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div style="margin-top: 5px; width: 100%" id="div_form_especificacion"></div>
    </div>
    <div id="tab-combos" class="tab-pane fade">
        @include('adminlte.gestion.postcocecha.pedidos.forms.form_combos')
    </div>
    <div id="tab-contenido_pedido" class="tab-pane fade in active" style="overflow-x: scroll; overflow-y: scroll">
        <table style="width: 100%; border: 1px solid #9d9d9d; font-size: 0.9em" class="table-bordered"
            id="table_form_contenido_pedido">
            <thead>
                <tr class="tr_fija_top_0">
                    <th class="text-center th_yura_green" style="min-width: 60px">
                        PIEZAS
                    </th>
                    <th class="text-center th_yura_green" style="min-width: 100px">
                        VARIEDAD
                    </th>
                    <th class="text-center th_yura_green" style="min-width: 90px">
                        COLOR
                    </th>
                    <th class="text-center th_yura_green" style="min-width: 160px">
                        CAJA
                    </th>
                    <th class="text-center th_yura_green" style="min-width: 160px">
                        PRESENTACION
                    </th>
                    <th class="text-center th_yura_green" style="min-width: 60px">
                        R. X CAJA
                    </th>
                    <th class="text-center th_yura_green" style="min-width: 60px">
                        TOTAL RAMOS
                    </th>
                    <th class="text-center th_yura_green" style="min-width: 60px">
                        T. X RAMOS
                    </th>
                    <th class="text-center th_yura_green" style="min-width: 60px">
                        TOTAL TALLOS
                    </th>
                    <th class="text-center th_yura_green" style="min-width: 80px">
                        LONGITUD
                    </th>
                    <th class="text-center th_yura_green" style="min-width: 60px">
                        PRECIO
                    </th>
                    <th class="text-center th_yura_green" style="min-width: 60px">
                        PRECIO CAJA
                    </th>
                    @foreach ($marcaciones as $m)
                        <th class="text-center bg-yura_dark" style="min-width: 100px">
                            {{ $m->nombre }}
                            <input type="hidden" class="ids_marcaciones" value="{{ $m->id_dato_exportacion }}">
                        </th>
                    @endforeach
                    <th class="text-center th_yura_green" style="min-width: 80px" colspan="2">
                        <button type="button" class="btn btn-xs btn-yura_default"
                            onclick="separar_pedido('{{ $pedido->id_pedido }}')">
                            Separar Pedido
                        </button>
                    </th>
                </tr>
            </thead>
            <tbody id="tbody_form_contenido_pedido">
                @foreach ($detalles_pedido as $pos_det_ped => $det_ped)
                    @php
                        $especificacion = $det_ped->cliente_especificacion->especificacion;
                        $especificaciones_empaque = $especificacion->especificacionesEmpaque;
                        $detalle_pedido_dato_exportacion = $det_ped->detalle_pedido_dato_exportacion;
                    @endphp
                    @foreach ($especificaciones_empaque as $pos_esp_emp => $esp_emp)
                        @php
                            if (count($esp_emp->detalles) > 1) {
                                $isCombo = true;
                            } else {
                                $isCombo = false;
                            }
                        @endphp
                        @foreach ($esp_emp->detalles as $pos_det_esp => $det_esp)
                            @php
                                $variedad = $det_esp->variedad;
                            @endphp
                            <tr class="tr_form_ped_{{ $pos_det_ped + 1 }}"
                                onmouseover="$('.tr_form_ped_{{ $pos_det_ped + 1 }}').addClass('bg-yura_dark')"
                                onmouseleave="$('.tr_form_ped_{{ $pos_det_ped + 1 }}').removeClass('bg-yura_dark')">
                                @if ($pos_det_esp == 0)
                                    <td class="text-center" style="border-color: #9d9d9d"
                                        rowspan="{{ count($esp_emp->detalles) }}">
                                        <input type="number" style="width: 100%; color: black" class="text-center"
                                            onchange="calcular_totales_pedido()" onkeyup="calcular_totales_pedido()"
                                            id="ped_piezas_{{ $pos_det_ped + 1 }}" min="0"
                                            value="{{ $det_ped->cantidad }}">
                                        <input type="hidden" class="pos_ped_especificaciones"
                                            value="{{ $pos_det_ped + 1 }}">
                                        <input type="hidden" id="ped_id_detalle_pedido_{{ $pos_det_ped + 1 }}"
                                            value="{{ $det_ped->id_detalle_pedido }}">
                                        @if ($isCombo)
                                            <input type="hidden" id="cant_detalles_combo_{{ $pos_det_ped + 1 }}"
                                                value="{{ count($esp_emp->detalles) }}">
                                        @endif
                                    </td>
                                @endif
                                <td class="text-center" style="border-color: #9d9d9d">
                                    @if ($variedad->assorted == 1)
                                        {{ $variedad->planta->nombre }}
                                    @endif
                                    @if (!$isCombo)
                                        <select id="ped_planta_{{ $pos_det_ped + 1 }}"
                                            class="{{ $variedad->assorted == 1 ? 'hidden' : '' }}"
                                            style="width: 100%; color: black"
                                            onchange="select_planta($(this).val(), 'ped_variedad_{{ $pos_det_ped + 1 }}', 'ped_variedad_{{ $pos_det_ped + 1 }}', ''); edit_seleccionar_planta('{{ $pos_det_ped + 1 }}')">
                                            @foreach ($plantas as $p)
                                                <option value="{{ $p->id_planta }}"
                                                    {{ $p->id_planta == $det_esp->variedad->id_planta ? 'selected' : '' }}>
                                                    {{ $p->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden"
                                            id="ped_id_detalle_especificacion_{{ $pos_det_ped + 1 }}"
                                            value="{{ $det_esp->id_detalle_especificacionempaque }}">
                                    @else
                                        <select id="ped_planta_{{ $pos_det_ped + 1 }}_{{ $pos_det_esp }}"
                                            class="{{ $variedad->assorted == 1 ? 'hidden' : '' }}"
                                            style="width: 100%; color: black"
                                            onchange="select_planta($(this).val(), 'ped_variedad_{{ $pos_det_ped + 1 }}_{{ $pos_det_esp }}', 'ped_variedad_{{ $pos_det_ped + 1 }}_{{ $pos_det_esp }}', ''); edit_seleccionar_planta('{{ $pos_det_ped + 1 }}_{{ $pos_det_esp }}')">
                                            @foreach ($plantas as $p)
                                                <option value="{{ $p->id_planta }}"
                                                    {{ $p->id_planta == $det_esp->variedad->id_planta ? 'selected' : '' }}>
                                                    {{ $p->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden"
                                            id="ped_id_detalle_especificacion_{{ $pos_det_ped + 1 }}_{{ $pos_det_esp }}"
                                            value="{{ $det_esp->id_detalle_especificacionempaque }}">
                                    @endif
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    @if ($variedad->assorted == 1)
                                        {{ $variedad->nombre }}
                                    @endif
                                    @if (!$isCombo)
                                        <select id="ped_variedad_{{ $pos_det_ped + 1 }}"
                                            style="width: 100%; color: black"
                                            class="{{ $variedad->assorted == 1 ? 'hidden' : '' }}">
                                            {!! getVariedadesByPlanta($det_esp->variedad->id_planta, 'option', $det_esp->id_variedad) !!}
                                        </select>
                                    @else
                                        <select id="ped_variedad_{{ $pos_det_ped + 1 }}_{{ $pos_det_esp }}"
                                            style="width: 100%; color: black"
                                            class="{{ $variedad->assorted == 1 ? 'hidden' : '' }}">
                                            {!! getVariedadesByPlanta($det_esp->variedad->id_planta, 'option', $det_esp->id_variedad) !!}
                                        </select>
                                    @endif
                                </td>
                                @if ($pos_det_esp == 0)
                                    <td class="text-center" style="border-color: #9d9d9d"
                                        rowspan="{{ count($esp_emp->detalles) }}">
                                        <select id="ped_caja_{{ $pos_det_ped + 1 }}"
                                            style="width: 100%; color: black">
                                            @foreach ($cajas as $c)
                                                <option value="{{ $c->id_empaque }}"
                                                    {{ $c->id_empaque == $esp_emp->id_empaque ? 'selected' : '' }}>
                                                    {{ explode('|', $c->nombre)[0] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden"
                                            id="ped_id_especificacion_empaque_{{ $pos_det_ped + 1 }}"
                                            value="{{ $esp_emp->id_especificacion_empaque }}">
                                    </td>
                                @endif
                                <td class="text-center" style="border-color: #9d9d9d">
                                    @if (!$isCombo)
                                        <select id="ped_presentacion_{{ $pos_det_ped + 1 }}"
                                            style="width: 100%; color: black">
                                            {!! getPresentacionesByClientePlanta($pedido->id_cliente, $det_esp->variedad->id_planta, $det_esp->id_empaque_p) !!}
                                        </select>
                                    @else
                                        <select id="ped_presentacion_{{ $pos_det_ped + 1 }}_{{ $pos_det_esp }}"
                                            style="width: 100%; color: black">
                                            {!! getPresentacionesByClientePlanta($pedido->id_cliente, $det_esp->variedad->id_planta, $det_esp->id_empaque_p) !!}
                                        </select>
                                    @endif
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    @if (!$isCombo)
                                        <input type="number" style="width: 100%; color: black" class="text-center"
                                            onchange="calcular_totales_pedido()" onkeyup="calcular_totales_pedido()"
                                            id="ped_ramos_x_caja_{{ $pos_det_ped + 1 }}" min="0"
                                            value="{{ $det_esp->cantidad }}">
                                    @else
                                        <input type="number" style="width: 100%; color: black" class="text-center"
                                            onchange="calcular_totales_pedido()" onkeyup="calcular_totales_pedido()"
                                            id="ped_ramos_x_caja_{{ $pos_det_ped + 1 }}_{{ $pos_det_esp }}"
                                            min="0" value="{{ $det_esp->cantidad }}">
                                    @endif
                                </td>
                                @if ($pos_det_esp == 0)
                                    <td class="text-center" style="border-color: #9d9d9d"
                                        rowspan="{{ count($esp_emp->detalles) }}">
                                        <input type="number" style="width: 100%; color: black" class="text-center"
                                            id="ped_total_ramos_{{ $pos_det_ped + 1 }}" readonly disabled>
                                    </td>
                                @endif
                                <td class="text-center" style="border-color: #9d9d9d">
                                    @if (!$isCombo)
                                        <input type="number" style="width: 100%; color: black" class="text-center"
                                            onchange="calcular_totales_pedido()" onkeyup="calcular_totales_pedido()"
                                            id="ped_tallos_x_ramos_{{ $pos_det_ped + 1 }}" min="0"
                                            value="{{ $det_esp->tallos_x_ramos }}">
                                    @else
                                        <input type="number" style="width: 100%; color: black" class="text-center"
                                            onchange="calcular_totales_pedido()" onkeyup="calcular_totales_pedido()"
                                            id="ped_tallos_x_ramos_{{ $pos_det_ped + 1 }}_{{ $pos_det_esp }}"
                                            min="0" value="{{ $det_esp->tallos_x_ramos }}">
                                    @endif
                                </td>
                                @if ($pos_det_esp == 0)
                                    <td class="text-center" style="border-color: #9d9d9d"
                                        rowspan="{{ count($esp_emp->detalles) }}">
                                        <input type="number" style="width: 100%; color: black" class="text-center"
                                            id="ped_total_tallos_{{ $pos_det_ped + 1 }}" readonly disabled>
                                    </td>
                                @endif
                                <td class="text-center" style="border-color: #9d9d9d">
                                    @if (!$isCombo)
                                        <select id="ped_longitud_{{ $pos_det_ped + 1 }}"
                                            style="width: 100%; color: black" class="text-center">
                                            {!! getLongitudesByClientePlanta($pedido->id_cliente, $det_esp->variedad->id_planta, $det_esp->longitud_ramo) !!}
                                        </select>
                                    @else
                                        <select id="ped_longitud_{{ $pos_det_ped + 1 }}_{{ $pos_det_esp }}"
                                            style="width: 100%; color: black" class="text-center">
                                            {!! getLongitudesByClientePlanta($pedido->id_cliente, $det_esp->variedad->id_planta, $det_esp->longitud_ramo) !!}
                                        </select>
                                    @endif
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    @if (!$isCombo)
                                        <input type="number" style="width: 100%; color: black" class="text-center"
                                            onchange="calcular_totales_pedido()" onkeyup="calcular_totales_pedido()"
                                            id="ped_precio_esp_{{ $pos_det_ped + 1 }}" min="0"
                                            value="{{ getPrecioByDetEsp($det_ped->precio, $det_esp->id_detalle_especificacionempaque) }}">
                                    @else
                                        <input type="number" style="width: 100%; color: black" class="text-center"
                                            onchange="calcular_totales_pedido()" onkeyup="calcular_totales_pedido()"
                                            id="ped_precio_esp_{{ $pos_det_ped + 1 }}_{{ $pos_det_esp }}"
                                            min="0"
                                            value="{{ getPrecioByDetEsp($det_ped->precio, $det_esp->id_detalle_especificacionempaque) }}">
                                    @endif
                                </td>
                                @if ($pos_det_esp == 0)
                                    <td class="text-center" style="border-color: #9d9d9d"
                                        rowspan="{{ count($esp_emp->detalles) }}">
                                        <input type="text" style="width: 100%; color: black" class="text-center"
                                            id="ped_total_precio_caja_{{ $pos_det_ped + 1 }}" readonly=""
                                            disabled="">
                                    </td>
                                    @foreach ($marcaciones as $m)
                                        @php
                                            $valor = '';
                                            foreach ($detalle_pedido_dato_exportacion as $item) {
                                                if ($item->id_dato_exportacion == $m->id_dato_exportacion) {
                                                    $valor = $item->valor;
                                                }
                                            }
                                        @endphp
                                        <td class="text-center" style="border-color: #9d9d9d"
                                            rowspan="{{ count($esp_emp->detalles) }}">
                                            <input type="text" style="width: 100%; color: black"
                                                class="text-center"
                                                id="ped_marcacion_{{ $m->id_dato_exportacion }}_{{ $pos_det_ped + 1 }}"
                                                value="{{ $valor }}">
                                        </td>
                                    @endforeach
                                    <td class="text-center" style="border-color: #9d9d9d"
                                        rowspan="{{ count($esp_emp->detalles) }}">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-xs btn-yura_primary"
                                                onclick="duplicar_contenido_pedido('{{ $pos_det_ped + 1 }}')"
                                                title="Duplicar Detalle">
                                                <i class="fa fa-fw fa-plus"></i>
                                            </button>
                                            <button type="button" class="btn btn-xs btn-yura_danger"
                                                onclick="borrar_detalle_pedido('{{ $pos_det_ped + 1 }}')"
                                                title="Eliminar Detalle">
                                                <i class="fa fa-fw fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="text-center" style="border-color: #9d9d9d"
                                        rowspan="{{ count($esp_emp->detalles) }}">
                                        <input type="checkbox" value="{{ $det_ped->id_detalle_pedido }}"
                                            id="check_detalle_pedido_{{ $det_ped->id_detalle_pedido }}"
                                            class="check_detalles_pedidos mouse-hand">
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- BOTONES --}}
<div style="overflow-x: scroll">
    <table style="margin-top: 0; width: 100%">
        <tr>
            <td rowspan="4" style="text-align: right; padding-right: 20px; min-width: 320px">
                <div class="btn-group">
                    <button type="button" class="btn btn-yura_primary" onclick="update_pedido()">
                        <i class="fa fa-fw fa-shopping-cart"></i> Grabar Pedido
                    </button>
                    <button type="button" class="btn btn-yura_default"
                        onclick="cerrar_modals(); modificar_pedido('{{ $pedido->id_pedido }}')">
                        <span class="badge bg-yura_dark" id="span_total_monto_pedido">$0</span>
                        <i class="fa fa-fw fa-refresh"></i> Reiniciar Formulario
                    </button>
                    @if ($pedido->tipo_pedido == 'STANDING ORDER')
                        <button type="button" class="btn btn-yura_primary"
                            onclick="update_orden_fija('{{ $pedido->id_pedido }}')">
                            <i class="fa fa-fw fa-save"></i>
                            Actualizar Orden Fija
                        </button>
                    @endif
                </div>
            </td>
            <th style="width: 25%; text-align: right; min-width: 120px">
                PIEZAS TOTALES:
            </th>
            <th id="th_total_piezas_pedido" style="text-align: right; padding-right: 5px; width: 10%">
                0
            </th>
        </tr>
        <tr>
            <th style="width: 25%; text-align: right; min-width: 120px">
                RAMOS TOTALES:
            </th>
            <th id="th_total_ramos_pedido" style="text-align: right; padding-right: 5px; width: 10%">
                0
            </th>
            <td colspan="{{ count($marcaciones) + 10 }}"></td>
        </tr>
        <tr>
            <th style="width: 25%; text-align: right; min-width: 120px">
                TALLOS TOTALES:
            </th>
            <th id="th_total_tallos_pedido" style="text-align: right; padding-right: 5px; width: 10%">
                0
            </th>
        </tr>
        <tr>
            <th style="width: 25%; text-align: right; min-width: 120px">
                MONTO TOTAL:
            </th>
            <th id="th_total_monto_pedido" style="text-align: right; padding-right: 5px; width: 10%">
                $0
            </th>
        </tr>
    </table>
</div>

<style>
    .tr_fija_top_0 {
        position: sticky;
        top: 0;
        z-index: 9;
    }

    .tr_fija_bottom_0 {
        position: sticky;
        bottom: 0;
        z-index: 9;
    }

    #span_total_monto_pedido {
        position: absolute;
        top: -8px;
        left: -22px;
        font-size: 11px;
        font-weight: 400;
        z-index: 9;
    }
</style>

<script>
    setTimeout(() => {
        $("#form_cliente, #form_consignatario, #form_agencia")
            .select2({
                dropdownParent: $('#div_modal-modal-view_modificar_pedido')
            })
    }, 500);
    calcular_totales_pedido();
    cargar_opciones_orden_fija(1);
    $('#form_combos_planta_1').html('{!! $option_plantas !!}');
    $('#form_combos_caja').html('{!! $option_cajas !!}');

    form_cant_detalles = {{ count($detalles_pedido) }};

    function form_seleccionar_cliente() {
        datos = {
            _token: '{{ csrf_token() }}',
            cliente: $('#form_cliente').val(),
        }
        $.LoadingOverlay('show');
        $.post('{{ url('pedidos/form_seleccionar_cliente') }}', datos, function(retorno) {
            $('#form_consignatario').html(retorno.consignatarios);
            $('#form_agencia').html(retorno.agencias);
            $('#form_planta').html(retorno.plantas);
            $('#form_combos_planta_1').html(retorno.plantas);
            $('#form_caja').html(retorno.cajas);
            $('#form_combos_caja').html(retorno.cajas);

            $('#form_variedad').html('');
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function() {
            $.LoadingOverlay('hide');
        })
    }

    function form_seleccionar_planta() {
        datos = {
            _token: '{{ csrf_token() }}',
            cliente: $('#form_cliente').val(),
            planta: $('#form_planta').val(),
        }
        $.LoadingOverlay('show');
        $.post('{{ url('pedidos/form_seleccionar_planta') }}', datos, function(retorno) {
            $('#form_variedad').html(retorno.variedades);
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function() {
            $.LoadingOverlay('hide');
        })
    }

    function buscar_form_especificaciones() {
        datos = {
            cliente: $('#form_cliente').val(),
            planta: $('#form_planta').val(),
            variedad: $('#form_variedad').val(),
            caja: $('#form_caja').val(),
            ramos_x_caja: $('#form_ramos_x_caja').val(),
            longitud: $('#form_longitud').val(),
        }
        if (datos['planta'] != '')
            get_jquery('{{ url('pedidos/buscar_form_especificaciones') }}', datos, function(retorno) {
                $('#div_form_especificacion').html(retorno);
            });
        else
            $('#div_form_especificacion').html('');
    }

    function delete_contenido_pedido(form_cant) {
        $('.tr_form_ped_' + form_cant).remove();
        calcular_totales_pedido();
    }

    function calcular_totales_pedido() {
        pos_ped_especificaciones = $('.pos_ped_especificaciones');
        total_piezas_pedido = 0;
        total_ramos_pedido = 0;
        total_tallos_pedido = 0;
        total_monto_pedido = 0;
        for (y = 0; y < pos_ped_especificaciones.length; y++) {
            num_pos = pos_ped_especificaciones[y].value;

            piezas = $('#ped_piezas_' + num_pos).val();
            piezas = piezas != '' ? parseInt(piezas) : 0;
            cant_detalles_combo = $('#cant_detalles_combo_' + num_pos);
            if (cant_detalles_combo.length) {
                total_ramos = 0;
                total_tallos = 0;
                precio_caja = 0;
                for (c = 0; c < cant_detalles_combo.val(); c++) {
                    ramos_x_caja = $('#ped_ramos_x_caja_' + num_pos + '_' + c).val();
                    ramos_x_caja = ramos_x_caja != '' ? parseInt(ramos_x_caja) : 0;
                    tallos_x_ramos = $('#ped_tallos_x_ramos_' + num_pos + '_' + c).val();
                    tallos_x_ramos = tallos_x_ramos != '' ? parseInt(tallos_x_ramos) : 0;
                    precio_ped = $('#ped_precio_esp_' + num_pos + '_' + c).val();
                    precio_ped = precio_ped != '' ? parseFloat(precio_ped) : 0;

                    total_ramos += piezas * ramos_x_caja;
                    total_tallos += piezas * ramos_x_caja * tallos_x_ramos;
                    precio_caja += Math.round((piezas * ramos_x_caja * precio_ped) * 100) / 100;
                }
            } else {
                ramos_x_caja = $('#ped_ramos_x_caja_' + num_pos).val();
                ramos_x_caja = ramos_x_caja != '' ? parseInt(ramos_x_caja) : 0;
                tallos_x_ramos = $('#ped_tallos_x_ramos_' + num_pos).val();
                tallos_x_ramos = tallos_x_ramos != '' ? parseInt(tallos_x_ramos) : 0;
                precio_ped = $('#ped_precio_esp_' + num_pos).val();
                precio_ped = precio_ped != '' ? parseFloat(precio_ped) : 0;

                total_ramos = piezas * ramos_x_caja;
                total_tallos = piezas * ramos_x_caja * tallos_x_ramos;
                precio_caja = Math.round((piezas * ramos_x_caja * precio_ped) * 100) / 100;
            }
            $('#ped_total_ramos_' + num_pos).val(total_ramos);
            $('#ped_total_tallos_' + num_pos).val(total_tallos);
            $('#ped_total_precio_caja_' + num_pos).val('$' + precio_caja);

            total_piezas_pedido += piezas;
            total_ramos_pedido += total_ramos;
            total_tallos_pedido += total_tallos;
            total_monto_pedido += precio_caja;
        }
        total_monto_pedido = Math.round(total_monto_pedido * 100) / 100;

        $('#span_total_piezas_pedido').html(total_piezas_pedido + ' cajas');
        $('#span_total_monto_pedido').html('$' + total_monto_pedido);
        $('#th_total_piezas_pedido').html(total_piezas_pedido);
        $('#th_total_ramos_pedido').html(total_ramos_pedido);
        $('#th_total_tallos_pedido').html(total_tallos_pedido);
        $('#th_total_monto_pedido').html('$' + total_monto_pedido);
    }

    function cargar_opciones_orden_fija(opcion) {
        datos = {
            opcion: opcion,
        };
        get_jquery('pedidos/cargar_opciones_orden_fija', datos, function(retorno) {
            $('#div_opciones_orden_fija').html(retorno);
        });
    }

    function add_fechas_pedido_fijo_personalizado() {
        form_cant_fechas_orden_fija++;
        $('#td_fechas_pedido_fijo_personalizado').append('<div class="col-md-4" id="div_' +
            form_cant_fechas_orden_fija + '">' +
            '<div class="input-group" style="min-width: 180px">' +
            '<span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">' +
            'Fecha ' + form_cant_fechas_orden_fija +
            '</span>' +
            '<input type="date" id="fecha_desde_pedido_fijo_' + form_cant_fechas_orden_fija +
            '" name="fecha_desde_pedido_fijo_' + form_cant_fechas_orden_fija + '"' +
            'class="form-control text-center input-yura_default" style="width: 100%" required>' +
            '</div>' +
            '</div>');
    }

    function update_pedido() {
        fallos = false;
        // ENCABEZADO
        id_pedido = $('#id_pedido_selected').val();
        fecha = $('#form_fecha').val();
        cliente = $('#form_cliente').val();
        consignatario = $('#form_consignatario').val();
        agencia = $('#form_agencia').val();
        tipo = $('#form_tipo_pedido').val();

        // DETALLES PEDIDO
        pos_ped_especificaciones = $('.pos_ped_especificaciones');
        detalles_pedido = [];
        for (y = 0; y < pos_ped_especificaciones.length; y++) {
            num_pos = pos_ped_especificaciones[y].value;

            id_detalle_pedido = $('#ped_id_detalle_pedido_' + num_pos).val();
            id_especificacion_empaque = $('#ped_id_especificacion_empaque_' + num_pos).val();
            piezas = $('#ped_piezas_' + num_pos).val();
            caja = $('#ped_caja_' + num_pos).val();
            cant_detalles_combo = $('#cant_detalles_combo_' + num_pos);
            detalles_combo = [];
            if (cant_detalles_combo.length) {
                for (c = 0; c < cant_detalles_combo.val(); c++) {
                    id_detalle_especificacion = $('#ped_id_detalle_especificacion_' + num_pos + '_' + c).val();
                    variedad = $('#ped_variedad_' + num_pos + '_' + c).val();
                    presentacion = $('#ped_presentacion_' + num_pos + '_' + c).val();
                    longitud = $('#ped_longitud_' + num_pos + '_' + c).val();
                    ramos_x_caja = $('#ped_ramos_x_caja_' + num_pos + '_' + c).val();
                    tallos_x_ramos = $('#ped_tallos_x_ramos_' + num_pos + '_' + c).val();
                    precio_ped = $('#ped_precio_esp_' + num_pos + '_' + c).val();

                    $('#ped_piezas_' + num_pos).removeClass('bg-red');
                    $('#ped_ramos_x_caja_' + num_pos + '_' + c).removeClass('bg-red');
                    $('#ped_tallos_x_ramos_' + num_pos + '_' + c).removeClass('bg-red');
                    $('#ped_precio_esp_' + num_pos + '_' + c).removeClass('bg-red');
                    if (piezas != '' && ramos_x_caja != '' && tallos_x_ramos != '' && precio_ped != '') {
                        detalles_combo.push({
                            id_detalle_especificacion: id_detalle_especificacion,
                            variedad: variedad,
                            presentacion: presentacion,
                            longitud: longitud,
                            ramos_x_caja: ramos_x_caja,
                            tallos_x_ramos: tallos_x_ramos,
                            precio_ped: precio_ped,
                        });
                    } else {
                        fallos = true;
                        if (piezas == '')
                            $('#ped_piezas_' + num_pos).addClass('bg-red');
                        if (ramos_x_caja == '')
                            $('#ped_ramos_x_caja_' + num_pos + '_' + c).addClass('bg-red');
                        if (tallos_x_ramos == '')
                            $('#ped_tallos_x_ramos_' + num_pos + '_' + c).addClass('bg-red');
                        if (precio_ped == '')
                            $('#ped_precio_esp_' + num_pos + '_' + c).addClass('bg-red');
                    }
                }

                ids_marcaciones = $('.ids_marcaciones');
                valores_marcaciones = [];
                for (m = 0; m < ids_marcaciones.length; m++) {
                    id_marcacion = ids_marcaciones[m].value;
                    valor_marcacion = $('#ped_marcacion_' + id_marcacion + '_' + num_pos).val();
                    valores_marcaciones.push({
                        id_marcacion: id_marcacion,
                        valor_marcacion: valor_marcacion,
                    });
                }

                detalles_pedido.push({
                    id_detalle_pedido: id_detalle_pedido,
                    id_especificacion_empaque: id_especificacion_empaque,
                    piezas: piezas,
                    caja: caja,
                    valores_marcaciones: valores_marcaciones,
                    detalles_combo: detalles_combo,
                });
            } else {
                id_detalle_especificacion = $('#ped_id_detalle_especificacion_' + num_pos).val();
                variedad = $('#ped_variedad_' + num_pos).val();
                presentacion = $('#ped_presentacion_' + num_pos).val();
                longitud = $('#ped_longitud_' + num_pos).val();
                ramos_x_caja = $('#ped_ramos_x_caja_' + num_pos).val();
                tallos_x_ramos = $('#ped_tallos_x_ramos_' + num_pos).val();
                precio_ped = $('#ped_precio_esp_' + num_pos).val();

                $('#ped_piezas_' + num_pos).removeClass('bg-red');
                $('#ped_ramos_x_caja_' + num_pos).removeClass('bg-red');
                $('#ped_tallos_x_ramos_' + num_pos).removeClass('bg-red');
                $('#ped_precio_esp_' + num_pos).removeClass('bg-red');
                if (piezas != '' && ramos_x_caja != '' && tallos_x_ramos != '' && precio_ped != '') {

                    ids_marcaciones = $('.ids_marcaciones');
                    valores_marcaciones = [];
                    for (m = 0; m < ids_marcaciones.length; m++) {
                        id_marcacion = ids_marcaciones[m].value;
                        valor_marcacion = $('#ped_marcacion_' + id_marcacion + '_' + num_pos).val();
                        valores_marcaciones.push({
                            id_marcacion: id_marcacion,
                            valor_marcacion: valor_marcacion,
                        });
                    }

                    detalles_combo.push({
                        id_detalle_especificacion: id_detalle_especificacion,
                        variedad: variedad,
                        presentacion: presentacion,
                        longitud: longitud,
                        ramos_x_caja: ramos_x_caja,
                        tallos_x_ramos: tallos_x_ramos,
                        precio_ped: precio_ped,
                    });

                    detalles_pedido.push({
                        id_detalle_pedido: id_detalle_pedido,
                        id_especificacion_empaque: id_especificacion_empaque,
                        piezas: piezas,
                        caja: caja,
                        valores_marcaciones: valores_marcaciones,
                        detalles_combo: detalles_combo,
                    });
                } else {
                    fallos = true;
                    if (piezas == '')
                        $('#ped_piezas_' + num_pos).addClass('bg-red');
                    if (ramos_x_caja == '')
                        $('#ped_ramos_x_caja_' + num_pos).addClass('bg-red');
                    if (tallos_x_ramos == '')
                        $('#ped_tallos_x_ramos_' + num_pos).addClass('bg-red');
                    if (precio_ped == '')
                        $('#ped_precio_esp_' + num_pos).addClass('bg-red');
                }
            }
        }

        if (detalles_pedido.length > 0)
            if (!fallos) {
                mensaje = {
                    title: '<i class="fa fa-fw fa-save"></i> Mensaje de confirmacion',
                    mensaje: '<div class="alert alert-info text-center" style="font-size: 1.3em" id="div_mensaje_confirmacion"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de <b>GRABAR</b> el pedido?</div>',
                };
                BootstrapDialog.show({
                    title: mensaje['title'],
                    closable: false,
                    draggable: true,
                    message: $('<div></div>').html(mensaje['mensaje']),
                    onshown: function(modal) {
                        $('#' + modal.getId()).css('overflow-y', 'scroll');
                        $('#' + modal.getId() + '>div').css('width', '{{ isPC() ? '50%' : '' }}');
                        modal.setId('modal_quest_update_pedido');
                        arreglo_modals_form.push(modal);
                        $('#btn_no_' + 'modal_quest_update_pedido').addClass('btn-yura_default');
                        $('#btn_continue_' + 'modal_quest_update_pedido').addClass('btn-yura_primary');
                    },
                    callback: function() {
                        arreglo_modals_form = [];
                    },
                    buttons: [{
                        id: 'btn_no_' + 'modal_quest_update_pedido',
                        label: 'No',
                        icon: 'fa fa-fw fa-times',
                        action: function(modal) {
                            modal.close();
                        }
                    }, {
                        id: 'btn_continue_' + 'modal_quest_update_pedido',
                        label: 'Continuar',
                        icon: 'fa fa-fw fa-check',
                        cssClass: 'btn btn-primary',
                        action: function(modal) {
                            $('#div_mensaje_confirmacion').html(
                                '<i class="fa fa-fw fa-search"></i> <b>VALIDANDO</b> el pedido')
                            datos = {
                                _token: '{{ csrf_token() }}',
                                id_pedido: id_pedido,
                                tipo: tipo,
                                fecha: fecha,
                                cliente: cliente,
                                consignatario: consignatario,
                                agencia: agencia,
                                detalles_pedido: JSON.stringify(detalles_pedido),
                            }
                            $.LoadingOverlay('show');
                            $.post('{{ url('pedidos/update_pedido') }}', datos, function(
                                retorno) {
                                if (retorno.success) {
                                    mini_alerta('success', retorno.mensaje, 5000);
                                    /*listar_resumen_pedidos(
                                        document.getElementById('fecha_pedidos_search')
                                        .value,
                                        true,
                                        document.getElementById(
                                            'id_configuracion_pedido')
                                        .value,
                                        document.getElementById('id_cliente').value
                                    );*/
                                    cerrar_modals();
                                } else {
                                    modal.close();
                                    alerta(retorno.mensaje);
                                }
                            }, 'json').fail(function(retorno) {
                                modal.close();
                                console.log(retorno);
                                alerta_errores(retorno.responseText);
                                alerta('Ha ocurrido un problema al enviar la información');
                            }).always(function() {
                                $.LoadingOverlay('hide');
                            });
                        }
                    }]
                });
            } else
                alerta('<div class="alert alert-warning text-center">Faltan datos por ingresar en el pedido</div>')
        else
            alerta('<div class="alert alert-warning text-center">El contenido del pedido esta vacio</div>')
    }

    function borrar_detalle_pedido(pos_det) {
        mensaje = {
            title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de Confirmacion',
            mensaje: '<div class="alert alert-warning text-center" style="font-size: 16px">Está a punto de <b>ELIMINAR</b> el detalle</div>' +
                '<div class="alert alert-info text-center" style="font-size: 16px">¿Desea guardarlo como <b>PÉRDIDA</b>?' +
                '<div style="display: flex;align-items: center;justify-content: space-evenly;">' +
                '<div>' +
                '<input type="radio" name="check_registrar_perdido" value="NO" style="width: 18px;height: 18px;" id="no_registrar">' +
                '<label style="font-size: 18px;position: relative;bottom: 4px;" for="no_registrar"><b>NO</b></label>' +
                '</div>' +
                '<div>' +
                '<input type="radio" name="check_registrar_perdido" value="SI" style="width: 18px;height: 18px;" id="si_registrar" checked>' +
                '<label style="font-size: 18px;position: relative;bottom: 4px;" for="si_registrar"><b>SI</b></label>' +
                '</div>' +
                '</div>' +
                '</div>',
        };
        modal_quest('modal_borrar_detalle_pedido', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                id_detalle_pedido = $('#ped_id_detalle_pedido_' + pos_det).val();
                caja = $('#ped_caja_' + pos_det).val();
                if ($('#no_registrar').prop('checked'))
                    registrar = 0;
                else
                    registrar = 1;

                datos = {
                    _token: '{{ csrf_token() }}',
                    id_detalle_pedido: id_detalle_pedido,
                    caja: caja,
                    perdida: registrar,
                }
                post_jquery_m('{{ url('pedidos/borrar_detalle_pedido') }}', datos, function(retorno) {
                    delete_contenido_pedido(pos_det);
                    /*listar_resumen_pedidos(
                        document.getElementById('fecha_pedidos_search').value,
                        true,
                        document.getElementById('id_configuracion_pedido').value,
                        document.getElementById('id_cliente').value
                    );*/
                });
            });
    }

    function duplicar_contenido_pedido(num_pos) {
        piezas = $('#ped_piezas_' + num_pos).val();
        caja = $('#ped_caja_' + num_pos).val();
        cant_detalles_combo = $('#cant_detalles_combo_' + num_pos);
        detalles_combo = [];
        if (cant_detalles_combo.length) { // es combo
            for (c = 0; c < cant_detalles_combo.val(); c++) {
                id_detalle_especificacion = $('#ped_id_detalle_especificacion_' + num_pos + '_' + c).val();
                variedad = $('#ped_variedad_' + num_pos + '_' + c).val();
                presentacion = $('#ped_presentacion_' + num_pos + '_' + c).val();
                longitud = $('#ped_longitud_' + num_pos + '_' + c).val();
                ramos_x_caja = $('#ped_ramos_x_caja_' + num_pos + '_' + c).val();
                tallos_x_ramos = $('#ped_tallos_x_ramos_' + num_pos + '_' + c).val();
                precio_ped = $('#ped_precio_esp_' + num_pos + '_' + c).val();

                detalles_combo.push({
                    id_detalle_especificacion: id_detalle_especificacion,
                    variedad: variedad,
                    presentacion: presentacion,
                    longitud: longitud,
                    ramos_x_caja: ramos_x_caja,
                    tallos_x_ramos: tallos_x_ramos,
                    precio_ped: precio_ped,
                });
            }

            ids_marcaciones = $('.ids_marcaciones');
            valores_marcaciones = [];
            for (m = 0; m < ids_marcaciones.length; m++) {
                id_marcacion = ids_marcaciones[m].value;
                valor_marcacion = $('#ped_marcacion_' + id_marcacion + '_' + num_pos).val();
                valores_marcaciones.push({
                    id_marcacion: id_marcacion,
                    valor_marcacion: valor_marcacion,
                });
            }
        } else { // es simple
            id_detalle_especificacion = $('#ped_id_detalle_especificacion_' + num_pos).val();
            variedad = $('#ped_variedad_' + num_pos).val();
            presentacion = $('#ped_presentacion_' + num_pos).val();
            longitud = $('#ped_longitud_' + num_pos).val();
            ramos_x_caja = $('#ped_ramos_x_caja_' + num_pos).val();
            tallos_x_ramos = $('#ped_tallos_x_ramos_' + num_pos).val();
            precio_ped = $('#ped_precio_esp_' + num_pos).val();

            ids_marcaciones = $('.ids_marcaciones');
            valores_marcaciones = [];
            for (m = 0; m < ids_marcaciones.length; m++) {
                id_marcacion = ids_marcaciones[m].value;
                valor_marcacion = $('#ped_marcacion_' + id_marcacion + '_' + num_pos).val();
                valores_marcaciones.push({
                    id_marcacion: id_marcacion,
                    valor_marcacion: valor_marcacion,
                });
            }

            detalles_combo.push({
                id_detalle_especificacion: id_detalle_especificacion,
                variedad: variedad,
                presentacion: presentacion,
                longitud: longitud,
                ramos_x_caja: ramos_x_caja,
                tallos_x_ramos: tallos_x_ramos,
                precio_ped: precio_ped,
            });
        }

        datos = {
            cliente: $('#form_cliente').val(),
            num_pos: form_cant_detalles,
            piezas: piezas,
            caja: caja,
            detalles_combo: JSON.stringify(detalles_combo),
            valores_marcaciones: JSON.stringify(valores_marcaciones),
        };
        get_jquery('pedidos/duplicar_contenido_pedido', datos, function(retorno) {
            $('#tbody_form_contenido_pedido').append(retorno);
            form_cant_detalles++;
            calcular_totales_pedido();
        });
    }

    function update_orden_fija(id_ped) {
        datos = {
            id_ped: id_ped
        }
        get_jquery('{{ url('pedidos/obtener_historial_orden_fija') }}', datos, function(retorno) {
            mensaje = {
                title: '<i class="fa fa-fw fa-refresh"></i> Actualizar toda la orden fija',
                mensaje: '<legend class="text-center" style="font-size: 16px; margin-bottom: 5px"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de ACTUALIZAR la orden fija?</legend>' +
                    retorno,
            };
            modal_quest('modal_update_orden_fija', mensaje['mensaje'], mensaje['title'], true, false,
                '{{ isPC() ? '35%' : '' }}',
                function() {
                    fechas = [];
                    check_all_fechas_update_of = $('.check_all_fechas_update_of');
                    for (f = 0; f < check_all_fechas_update_of.length; f++) {
                        id = check_all_fechas_update_of[f].id;
                        if ($('#' + id).prop('checked') == true) {
                            fecha = check_all_fechas_update_of[f].value;
                            fechas.push(fecha);
                        }
                    }
                    datos = {
                        _token: '{{ csrf_token() }}',
                        id_ped: id_ped,
                        fechas: JSON.stringify(fechas),
                    }
                    post_jquery_m('{{ url('pedidos/update_orden_fija') }}', datos, function(retorno) {
                        /*listar_resumen_pedidos(
                            document.getElementById('fecha_pedidos_search').value,
                            true,
                            document.getElementById('id_configuracion_pedido').value,
                            document.getElementById('id_cliente').value
                        );*/
                    });
                });
        });
    }

    function separar_pedido(id_ped) {
        mensaje = {
            title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de Confirmacion',
            mensaje: '<div class="alert alert-info text-center" style="font-size: 16px; margin-bottom: 5px">¿Está seguro de <b>SEPARAR</b> el pedido?</div><br>' +
                '<div class="alert alert-danger text-center" style="font-size: 16px; margin-bottom: 5px"><i class="fa fa-fw fa-exclamation-triangle"></i> Este proceso <b>ELIMINARA</b> el pedido de los <b>DESPACHOS</b> realizados</div>',
        };
        modal_quest('modal_separar_pedido', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                data = [];
                check_detalles_pedidos = $('.check_detalles_pedidos');
                for (i = 0; i < check_detalles_pedidos.length; i++) {
                    id = check_detalles_pedidos[i].id;
                    if ($('#' + id).prop('checked') == true) {
                        det_ped = check_detalles_pedidos[i].value;
                        data.push(det_ped);
                    }
                }
                if (data.length > 0) {
                    datos = {
                        _token: '{{ csrf_token() }}',
                        id_ped: id_ped,
                        data: JSON.stringify(data),
                    }
                    post_jquery_m('{{ url('pedidos/separar_pedido') }}', datos, function(retorno) {
                        listar_resumen_pedidos(
                            document.getElementById('fecha_pedidos_search').value,
                            true,
                            document.getElementById('id_configuracion_pedido').value,
                            document.getElementById('id_cliente').value
                        );
                        cerrar_modals();
                        modificar_pedido(id_ped);
                    });
                }
            });
    }

    function edit_seleccionar_planta(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            cliente: $('#form_cliente').val(),
            planta: $('#ped_planta_' + id).val(),
        }
        $('.tr_form_ped_' + id).LoadingOverlay('show');
        $.post('{{ url('pedidos/edit_seleccionar_planta') }}', datos, function(retorno) {
            $('#ped_presentacion_' + id).html(retorno.presentaciones);
            $('#ped_caja_' + id).html(retorno.cajas);
            $('#ped_longitud_' + id).html(retorno.longitudes);
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function() {
            $('.tr_form_ped_' + id).LoadingOverlay('hide');
        })
    }
</script>
