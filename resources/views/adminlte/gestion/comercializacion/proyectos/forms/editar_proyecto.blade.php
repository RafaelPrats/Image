<div style="overflow-x: scroll">
    <table style="width: 100%;">
        <tr>
            <th class="form_fecha">
                <div class="input-group">
                    <span class="input-group-addon bg-yura_dark">
                        Fecha
                    </span>
                    <input type="date" id="form_fecha" class="form-control" value="{{ $proyecto->fecha }}">
                </div>
            </th>
            <th>
                <div class="input-group">
                    <span class="input-group-addon bg-yura_dark">
                        Cliente
                    </span>
                    <select id="form_cliente" class="form-control">
                        <option value="{{ $cliente->id_cliente }}">
                            {{ $cliente->detalle()->nombre }}
                        </option>
                    </select>
                </div>
            </th>
            <th>
                <div class="input-group">
                    <span class="input-group-addon bg-yura_dark">
                        Consignatario
                    </span>
                    <select id="form_consignatario" class="form-control">
                        <option value="">Seleccione</option>
                        @foreach ($consignatarios as $item)
                            <option value="{{ $item->id_consignatario }}"
                                {{ $item->id_consignatario == $proyecto->id_consignatario ? 'selected' : '' }}>
                                {{ $item->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </th>
            <th>
                <div class="input-group">
                    <span class="input-group-addon bg-yura_dark">
                        Agencia
                    </span>
                    <select id="form_agencia" class="form-control">
                        <option value="">Seleccione</option>
                        @foreach ($agencias as $item)
                            <option value="{{ $item->id_agencia_carga }}"
                                {{ $item->id_agencia_carga == $proyecto->id_agencia_carga ? 'selected' : '' }}>
                                {{ $item->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </th>
            <th>
                <div class="input-group">
                    <span class="input-group-addon bg-yura_dark">
                        Tipo
                    </span>
                    <select id="form_tipo_pedido" class="form-control">
                        <option value="OM" {{ $proyecto->tipo == 'OM' ? 'selected' : '' }}>OPEN MARKET</option>
                        @if ($proyecto->tipo == 'SO')
                            <option value="SO" {{ $proyecto->tipo == 'SO' ? 'selected' : '' }}>
                                STANDING ORDER
                            </option>
                        @endif
                    </select>
                </div>
            </th>
        </tr>
    </table>
</div>

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
        @include('adminlte.gestion.comercializacion.proyectos.forms._pedidos_simples')
    </div>
    <div id="tab-combos" class="tab-pane fade">
        @include('adminlte.gestion.comercializacion.proyectos.forms._pedidos_combos')
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
                    @foreach ($datos_exportacion as $dat_exp)
                        <th class="text-center bg-yura_dark" style="min-width: 100px">
                            {{ $dat_exp->nombre }}
                            <input type="hidden" class="ids_marcaciones" value="{{ $dat_exp->id_dato_exportacion }}">
                        </th>
                    @endforeach
                    <th class="text-center th_yura_green">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_default"
                                onclick="separar_pedidos('{{ $proyecto->id_proyecto }}');">
                                Separar pedidos
                            </button>
                        </div>
                    </th>
                    <th class="text-center th_yura_green" style="width: 40px">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_danger" title="Vaciar Pedido"
                                onclick="$('#tbody_form_contenido_pedido').html(''); form_cant_detalles = 0; calcular_totales_pedido();">
                                <i class="fa fa-fw fa-trash"></i>
                            </button>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody id="tbody_form_contenido_pedido">
                @foreach ($proyecto->cajas as $pos_c => $caja)
                    @php
                        $detalles = $caja->detalles;
                        $marcaciones = $caja->marcaciones;
                    @endphp
                    @if (count($detalles) == 1)
                        @foreach ($detalles as $pos_d => $detalle)
                            @php
                                $variedad = $detalle->variedad;
                            @endphp
                            <tr class="tr_form_ped_{{ $pos_c + 1 }}"
                                onmouseover="$('.tr_form_ped_{{ $pos_c + 1 }}').addClass('bg-yura_dark')"
                                onmouseleave="$('.tr_form_ped_{{ $pos_c + 1 }}').removeClass('bg-yura_dark')">
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <input type="number" style="width: 100%; color: black" class="text-center"
                                        onchange="calcular_totales_pedido()" onkeyup="calcular_totales_pedido()"
                                        id="ped_piezas_{{ $pos_c + 1 }}" min="0"
                                        value="{{ $caja->cantidad }}"
                                        data-id_caja_proyecto="{{ $caja->id_caja_proyecto }}">
                                    <input type="hidden" class="pos_ped_especificaciones"
                                        value="{{ $pos_c + 1 }}">
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <select id="ped_planta_{{ $pos_c + 1 }}" style="width: 100%; color: black"
                                        onchange="select_planta($(this).val(), 'ped_variedad_{{ $pos_c + 1 }}', 'ped_variedad_{{ $pos_c + 1 }}', '', '')">
                                        @foreach ($plantas as $pta)
                                            <option value="{{ $pta->id_planta }}"
                                                {{ $pta->id_planta == $variedad->id_planta ? 'selected' : '' }}>
                                                {{ $pta->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <select id="ped_variedad_{{ $pos_c + 1 }}" style="width: 100%; color: black"
                                        data-id_detalle_caja_proyecto="{{ $detalle->id_detalle_caja_proyecto }}">
                                        @foreach ($variedad->planta->variedades->where('estado', 1)->sortBy('orden') as $var)
                                            <option value="{{ $var->id_variedad }}"
                                                {{ $var->id_variedad == $variedad->id_variedad ? 'selected' : '' }}>
                                                {{ $var->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <select id="ped_caja_{{ $pos_c + 1 }}" style="width: 100%; color: black">
                                        @foreach ($cajas as $cj)
                                            <option value="{{ $cj->id_empaque_c }}"
                                                {{ $cj->id_empaque_c == $caja->id_empaque ? 'selected' : '' }}>
                                                {{ explode('|', $cj->nombre)[0] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <select id="ped_presentacion_{{ $pos_c + 1 }}"
                                        style="width: 100%; color: black">
                                        @foreach ($presentaciones as $pres)
                                            <option value="{{ $pres->id_empaque_p }}"
                                                {{ $pres->id_empaque_p == $detalle->id_empaque ? 'selected' : '' }}>
                                                {{ $pres->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <input type="number" style="width: 100%; color: black" class="text-center"
                                        onchange="calcular_totales_pedido()" onkeyup="calcular_totales_pedido()"
                                        id="ped_ramos_x_caja_{{ $pos_c + 1 }}" min="0"
                                        value="{{ $detalle->ramos_x_caja }}">
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <input type="number" style="width: 100%; color: black" class="text-center"
                                        id="ped_total_ramos_{{ $pos_c + 1 }}" readonly="" disabled=""
                                        value="{{ $caja->cantidad * $detalle->ramos_x_caja }}">
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <input type="number" style="width: 100%; color: black" class="text-center"
                                        onchange="calcular_totales_pedido()" onkeyup="calcular_totales_pedido()"
                                        id="ped_tallos_x_ramos_{{ $pos_c + 1 }}" min="0"
                                        value="{{ $detalle->tallos_x_ramo }}">
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <input type="number" style="width: 100%; color: black" class="text-center"
                                        id="ped_total_tallos_{{ $pos_c + 1 }}" readonly="" disabled=""
                                        value="{{ $caja->cantidad * $detalle->ramos_x_caja * $detalle->tallos_x_ramo }}">
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <input type="number" style="width: 100%; color: black" class="text-center"
                                        id="ped_longitud_{{ $pos_c + 1 }}" value="{{ $detalle->longitud_ramo }}">
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <input type="number" style="width: 100%; color: black" class="text-center"
                                        onchange="calcular_totales_pedido()" onkeyup="calcular_totales_pedido()"
                                        id="ped_precio_esp_{{ $pos_c + 1 }}" min="0"
                                        value="{{ $detalle->precio }}">
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <input type="text" style="width: 100%; color: black" class="text-center"
                                        id="ped_total_precio_caja_{{ $pos_c + 1 }}" readonly=""
                                        disabled="">
                                </td>
                                @foreach ($datos_exportacion as $dat_exp)
                                    @php
                                        $valor = '';
                                        foreach ($marcaciones as $m) {
                                            if ($m->id_dato_exportacion == $dat_exp->id_dato_exportacion) {
                                                $valor = $m->valor;
                                            }
                                        }
                                    @endphp
                                    <td class="text-center" style="border-color: #9d9d9d">
                                        <input type="text" style="width: 100%; color: black" class="text-center"
                                            id="ped_marcacion_{{ $dat_exp->id_dato_exportacion }}_{{ $pos_c + 1 }}"
                                            value="{{ $valor }}">
                                    </td>
                                @endforeach
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <input type="checkbox" class="check_caja_proyecto"
                                        id="check_caja_proyecto_{{ $pos_c + 1 }}"
                                        data-id_caja_proyecto="{{ $caja->id_caja_proyecto }}">
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <button type="button" class="btn btn-xs btn-yura_danger"
                                        onclick="delete_contenido_pedido('{{ $pos_c + 1 }}')"
                                        title="Eliminar Pieza">
                                        <i class="fa fa-fw fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        @foreach ($detalles as $pos_d => $detalle)
                            @php
                                $variedad = $detalle->variedad;
                            @endphp
                            <tr class="tr_form_ped_{{ $pos_c + 1 }}"
                                onmouseover="$('.tr_form_ped_{{ $pos_c + 1 }}').addClass('bg-yura_dark')"
                                onmouseleave="$('.tr_form_ped_{{ $pos_c + 1 }}').removeClass('bg-yura_dark')">
                                @if ($pos_d == 0)
                                    <td class="text-center" style="border-color: #9d9d9d"
                                        rowspan="{{ count($detalles) }}">
                                        <input type="number" style="width: 100%; color: black" class="text-center"
                                            onchange="calcular_totales_pedido()" onkeyup="calcular_totales_pedido()"
                                            id="ped_piezas_{{ $pos_c + 1 }}" min="0"
                                            value="{{ $caja->cantidad }}"
                                            data-id_caja_proyecto="{{ $caja->id_caja_proyecto }}">
                                        <input type="hidden" class="pos_ped_especificaciones pos_ped_combo"
                                            value="{{ $pos_c + 1 }}">
                                        <input type="hidden" id="cant_detalles_combo_{{ $pos_c + 1 }}"
                                            value="{{ count($detalles) }}">
                                    </td>
                                @endif
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <select id="ped_planta_{{ $pos_c + 1 }}_{{ $pos_d }}"
                                        style="width: 100%; color: black"
                                        onchange="select_planta($(this).val(), 'ped_variedad_{{ $pos_c + 1 }}_{{ $pos_d }}', 'ped_variedad_{{ $pos_c + 1 }}_{{ $pos_d }}', '', '')">
                                        @foreach ($plantas as $pta)
                                            <option value="{{ $pta->id_planta }}"
                                                {{ $pta->id_planta == $variedad->id_planta ? 'selected' : '' }}>
                                                {{ $pta->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <select id="ped_variedad_{{ $pos_c + 1 }}_{{ $pos_d }}"
                                        style="width: 100%; color: black"
                                        data-id_detalle_caja_proyecto="{{ $detalle->id_detalle_caja_proyecto }}">
                                        @foreach ($variedad->planta->variedades->where('estado', 1)->sortBy('orden') as $var)
                                            <option value="{{ $var->id_variedad }}"
                                                {{ $var->id_variedad == $variedad->id_variedad ? 'selected' : '' }}>
                                                {{ $var->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                @if ($pos_d == 0)
                                    <td class="text-center" style="border-color: #9d9d9d"
                                        rowspan="{{ count($detalles) }}">
                                        <select id="ped_caja_{{ $pos_c + 1 }}" style="width: 100%; color: black">
                                            @foreach ($cajas as $cj)
                                                <option value="{{ $cj->id_empaque_c }}"
                                                    {{ $cj->id_empaque_c == $caja->id_empaque ? 'selected' : '' }}>
                                                    {{ explode('|', $cj->nombre)[0] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                @endif
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <select id="ped_presentacion_{{ $pos_c + 1 }}_{{ $pos_d }}"
                                        style="width: 100%; color: black">
                                        @foreach ($presentaciones as $pres)
                                            <option value="{{ $pres->id_empaque_p }}"
                                                {{ $pres->id_empaque_p == $detalle->id_empaque ? 'selected' : '' }}>
                                                {{ $pres->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <input type="number" style="width: 100%; color: black" class="text-center"
                                        onchange="calcular_totales_pedido()" onkeyup="calcular_totales_pedido()"
                                        id="ped_ramos_x_caja_{{ $pos_c + 1 }}_{{ $pos_d }}" min="0"
                                        value="{{ $detalle->ramos_x_caja }}">
                                </td>
                                @if ($pos_d == 0)
                                    <td class="text-center" style="border-color: #9d9d9d"
                                        rowspan="{{ count($detalles) }}">
                                        <input type="number" style="width: 100%; color: black" class="text-center"
                                            id="ped_total_ramos_{{ $pos_c + 1 }}" readonly="" disabled=""
                                            value="{{ $caja->cantidad * $detalle->ramos_x_caja }}">
                                    </td>
                                @endif
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <input type="number" style="width: 100%; color: black" class="text-center"
                                        onchange="calcular_totales_pedido()" onkeyup="calcular_totales_pedido()"
                                        id="ped_tallos_x_ramos_{{ $pos_c + 1 }}_{{ $pos_d }}"
                                        min="0" value="{{ $detalle->tallos_x_ramo }}">
                                </td>
                                @if ($pos_d == 0)
                                    <td class="text-center" style="border-color: #9d9d9d"
                                        rowspan="{{ count($detalles) }}">
                                        <input type="number" style="width: 100%; color: black" class="text-center"
                                            id="ped_total_tallos_{{ $pos_c + 1 }}" readonly="" disabled=""
                                            value="{{ $caja->cantidad * $detalle->ramos_x_caja * $detalle->tallos_x_ramo }}">
                                    </td>
                                @endif
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <input type="number" style="width: 100%; color: black" class="text-center"
                                        id="ped_longitud_{{ $pos_c + 1 }}_{{ $pos_d }}"
                                        value="{{ $detalle->longitud_ramo }}">
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <input type="number" style="width: 100%; color: black" class="text-center"
                                        onchange="calcular_totales_pedido()" onkeyup="calcular_totales_pedido()"
                                        id="ped_precio_esp_{{ $pos_c + 1 }}_{{ $pos_d }}" min="0"
                                        value="{{ $detalle->precio }}">
                                </td>
                                @if ($pos_d == 0)
                                    <td class="text-center" style="border-color: #9d9d9d"
                                        rowspan="{{ count($detalles) }}">
                                        <input type="text" style="width: 100%; color: black" class="text-center"
                                            id="ped_total_precio_caja_{{ $pos_c + 1 }}" readonly=""
                                            disabled="">
                                    </td>
                                    @foreach ($datos_exportacion as $dat_exp)
                                        @php
                                            $valor = '';
                                            foreach ($marcaciones as $m) {
                                                if ($m->id_dato_exportacion == $dat_exp->id_dato_exportacion) {
                                                    $valor = $m->valor;
                                                }
                                            }
                                        @endphp
                                        <td class="text-center" style="border-color: #9d9d9d"
                                            rowspan="{{ count($detalles) }}">
                                            <input type="text" style="width: 100%; color: black"
                                                class="text-center"
                                                id="ped_marcacion_{{ $dat_exp->id_dato_exportacion }}_{{ $pos_c + 1 }}"
                                                value="{{ $valor }}">
                                        </td>
                                    @endforeach
                                    <td class="text-center" style="border-color: #9d9d9d"
                                        rowspan="{{ count($detalles) }}">
                                        <input type="checkbox" class="check_caja_proyecto"
                                            id="check_caja_proyecto_{{ $pos_c + 1 }}"
                                            data-id_caja_proyecto="{{ $caja->id_caja_proyecto }}">
                                    </td>
                                    <td class="text-center" style="border-color: #9d9d9d"
                                        rowspan="{{ count($detalles) }}">
                                        <button type="button" class="btn btn-xs btn-yura_danger"
                                            onclick="delete_contenido_pedido('{{ $pos_c + 1 }}')"
                                            title="Eliminar Pieza">
                                            <i class="fa fa-fw fa-trash"></i>
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div style="overflow-x: scroll">
    <table style="margin-top: 0; width: 100%">
        <tbody>
            <tr>
                <td rowspan="4" style="text-align: right; padding-right: 20px; min-width: 320px">
                    <div class="btn-group">
                        <button type="button" class="btn btn-yura_primary"
                            onclick="update_proyecto('{{ $proyecto->id_proyecto }}')">
                            <i class="fa fa-fw fa-save"></i> Grabar Pedido
                        </button>
                        <button type="button" class="btn btn-yura_default"
                            onclick="cerrar_modals(); editar_proyecto('{{ $proyecto->id_proyecto }}')">
                            <span class="badge bg-yura_dark" id="span_total_monto_pedido">$0</span>
                            <i class="fa fa-fw fa-refresh"></i> Reiniciar Formulario
                        </button>
                        @if ($proyecto->tipo == 'SO')
                            <button type="button" class="btn btn-yura_primary"
                                onclick="update_orden_fija('{{ $proyecto->id_proyecto }}')">
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
                <td colspan="13"></td>
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
        </tbody>
    </table>
</div>

<style>
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
                dropdownParent: $('#div_modal-modal-view_editar_proyecto')
            });
        $('.select2-container').css('width', '100%');
        $('.select2-selection').css('height', '34px');
    }, 500);

    form_cant_detalles = {{ count($proyecto->cajas) }};
    calcular_totales_pedido();

    options_plantas = '<option value="">Seleccione</option>';
    @foreach ($plantas as $pta)
        options_plantas += '<option value="{{ $pta->id_planta }}">{{ $pta->nombre }}</option>';
    @endforeach
    options_cajas = '<option value="">Seleccione</option>';
    @foreach ($cajas as $caj)
        options_cajas += '<option value="{{ $caj->id_empaque_c }}">{{ $caj->nombre }}</option>';
    @endforeach
    $('#form_planta').html(options_plantas);
    $('#form_caja').html(options_cajas);
    $('#form_combos_planta_1').html(options_plantas);
    $('#form_combos_caja').html(options_cajas);

    function seleccionar_cliente() {
        datos = {
            _token: '{{ csrf_token() }}',
            cliente: $('#form_cliente').val(),
        }
        $.LoadingOverlay('show');
        $.post('{{ url('proyectos/seleccionar_cliente') }}', datos, function(retorno) {
            $('#form_consignatario').html(retorno.options_consignatario);
            $('#form_agencia').html(retorno.options_agencia);
            $('#form_planta').html(retorno.options_plantas);
            $('#form_caja').html(retorno.options_cajas);
            $('#form_combos_planta_1').html(retorno.options_plantas);
            $('#form_combos_caja').html(retorno.options_cajas);
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
        };
        if (datos['cliente'] != '' && datos['planta'] != '')
            get_jquery('{{ url('proyectos/buscar_form_especificaciones') }}', datos, function(retorno) {
                $('#div_form_especificacion').html(retorno);
            });
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
            $('#ped_total_precio_caja_' + num_pos).val('$' + Math.round(precio_caja * 100) / 100);

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

    function update_proyecto(id_proyecto) {
        tipo = $('#form_tipo_pedido').val();
        fallos = false;
        fecha = $('#form_fecha').val();

        cliente = $('#form_cliente').val();
        consignatario = $('#form_consignatario').val();
        agencia = $('#form_agencia').val();

        // DETALLES PEDIDO
        pos_ped_especificaciones = $('.pos_ped_especificaciones');
        detalles_pedido = [];
        for (y = 0; y < pos_ped_especificaciones.length; y++) {
            num_pos = pos_ped_especificaciones[y].value;

            id_caja_proyecto = $('#ped_piezas_' + num_pos).data('id_caja_proyecto');
            piezas = parseInt($('#ped_piezas_' + num_pos).val());
            caja = $('#ped_caja_' + num_pos).val();
            cant_detalles_combo = $('#cant_detalles_combo_' + num_pos);
            detalles_combo = [];
            if (cant_detalles_combo.length) {
                for (c = 0; c < cant_detalles_combo.val(); c++) {
                    id_detalle_caja_proyecto = $('#ped_variedad_' + num_pos + '_' + c).data('id_detalle_caja_proyecto');
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
                    if (piezas > 0 && ramos_x_caja != '' && tallos_x_ramos != '' && precio_ped != '') {
                        detalles_combo.push({
                            id_detalle_caja_proyecto: id_detalle_caja_proyecto,
                            variedad: variedad,
                            presentacion: presentacion,
                            longitud: longitud,
                            ramos_x_caja: ramos_x_caja,
                            tallos_x_ramos: tallos_x_ramos,
                            precio_ped: precio_ped,
                        });
                    } else {
                        fallos = true;
                        if ($('#ped_piezas_' + num_pos).val() == '' || piezas <= 0)
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

                if (piezas > 0 && detalles_combo.length > 0) {
                    detalles_pedido.push({
                        id_caja_proyecto: id_caja_proyecto,
                        piezas: piezas,
                        caja: caja,
                        valores_marcaciones: valores_marcaciones,
                        detalles_combo: detalles_combo,
                    });
                }
            } else {
                id_detalle_caja_proyecto = $('#ped_variedad_' + num_pos).data('id_detalle_caja_proyecto');
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
                if (piezas > 0 && ramos_x_caja != '' && tallos_x_ramos != '' && precio_ped != '') {

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
                        id_detalle_caja_proyecto: id_detalle_caja_proyecto,
                        variedad: variedad,
                        presentacion: presentacion,
                        longitud: longitud,
                        ramos_x_caja: ramos_x_caja,
                        tallos_x_ramos: tallos_x_ramos,
                        precio_ped: precio_ped,
                    });

                    detalles_pedido.push({
                        id_caja_proyecto: id_caja_proyecto,
                        piezas: piezas,
                        caja: caja,
                        valores_marcaciones: valores_marcaciones,
                        detalles_combo: detalles_combo,
                    });
                } else {
                    fallos = true;
                    if ($('#ped_piezas_' + num_pos).val() == '' || piezas <= 0)
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
                        modal.setId('modal_quest_update_proyecto');
                        arreglo_modals_form.push(modal);
                        $('#btn_no_' + 'modal_quest_update_proyecto').addClass('btn-yura_default');
                        $('#btn_continue_' + 'modal_quest_update_proyecto').addClass('btn-yura_primary');
                    },
                    callback: function() {
                        arreglo_modals_form = [];
                    },
                    buttons: [{
                        id: 'btn_no_' + 'modal_quest_update_proyecto',
                        label: 'No',
                        icon: 'fa fa-fw fa-times',
                        action: function(modal) {
                            modal.close();
                        }
                    }, {
                        id: 'btn_continue_' + 'modal_quest_update_proyecto',
                        label: 'Continuar',
                        icon: 'fa fa-fw fa-check',
                        cssClass: 'btn btn-primary',
                        action: function(modal) {
                            $('#div_mensaje_confirmacion').html(
                                '<i class="fa fa-fw fa-search"></i> <b>VALIDANDO</b> el pedido')
                            datos = {
                                _token: '{{ csrf_token() }}',
                                id_proyecto: id_proyecto,
                                tipo: tipo,
                                fecha: fecha,
                                cliente: cliente,
                                consignatario: consignatario,
                                agencia: agencia,
                                detalles_pedido: JSON.stringify(detalles_pedido),
                            }
                            $.LoadingOverlay('show');
                            $.post('{{ url('proyectos/update_proyecto') }}', datos, function(
                                retorno) {
                                if (retorno.success) {
                                    mini_alerta('success', retorno.mensaje, 5000);
                                    listar_reporte();
                                    cerrar_modals();
                                    //editar_proyecto(id_proyecto);
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

    function separar_pedidos(id_proyecto) {
        data = [];
        check_caja_proyecto = $('.check_caja_proyecto');
        for (i = 0; i < check_caja_proyecto.length; i++) {
            id = check_caja_proyecto[i].id;
            if ($('#' + id).prop('checked') == true) {
                data.push($('#' + id).data('id_caja_proyecto'));
            }
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                id_proyecto: id_proyecto,
                data: JSON.stringify(data),
            }
            post_jquery_m('{{ url('proyectos/separar_pedidos') }}', datos, function() {
                cerrar_modals();
                listar_reporte();
            })
        }
    }

    function update_orden_fija(id_ped) {
        datos = {
            id_ped: id_ped
        }
        get_jquery('{{ url('proyectos/obtener_historial_orden_fija') }}', datos, function(retorno) {
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
                    post_jquery_m('{{ url('proyectos/update_orden_fija') }}', datos, function(retorno) {
                        //listar_reporte();
                    });
                });
        });
    }
</script>
