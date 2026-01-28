<form id="form_add_pedido" name="form_add_pedido">
    <input type="hidden" id='id_cliente' value="{{ $idCliente }}">
    <input type="hidden" id='id_pedido_creado' value="{{ $id_pedido }}">
    <input type="hidden" id="presentaciones" value="{{ $presentaciones }}">
    <input type="hidden" id="tipo" value="{{ $tipo }}">

    @if ($pedido_fijo)
        <div class="row">
            <div class="col-md-3">
                <div class="list-group">
                    <a href="javascript:void(0)" class="list-group-item list-group-item-action active">
                        OPCIONES DE ENTREGA
                    </a>
                    <a href="javascript:void(0)" class="list-group-item list-group-item-action"
                        onclick="div_opcion_pedido_fijo(1)">
                        DÍA SEMANA
                    </a>
                    <a href="javascript:void(0)" class="list-group-item list-group-item-action"
                        onclick="div_opcion_pedido_fijo(2)">
                        DÍA MES
                    </a>
                    <a href="javascript:void(0)" class="list-group-item list-group-item-action"
                        onclick="div_opcion_pedido_fijo(3)">
                        PERSONALIZADO
                    </a>
                </div>
            </div>
            <div class="col-md-9" id="div_opciones_pedido_fijo"></div>
        </div>
    @endif
    <div>
        <div id="table_recepciones">
            <div class="row" style="margin-bottom: 10px" id="filtros_pedido">
                @if (!$pedido_fijo)
                    <div class="col-md-2">
                        <label for="Fecha de entrega" style="font-size: 11pt">
                            <i class="fa fa-calendar"></i> Fecha de entrega </label>
                        <input type="date" id="fecha_de_entrega" name="fecha_de_entrega" class="form-control"
                            value="{{ \Carbon\Carbon::now()->toDateString() }}" required>
                    </div>
                @endif
                @if ($vista === 'pedidos')
                    <div class="col-md-2">
                        <label for="Cliente" style="font-size: 11pt">
                            <i class="fa fa-user-circle-o"></i> Cliente
                        </label>
                        <select class="form-control " id="id_cliente_venta" name="id_cliente_venta"
                            style="background:transparent" onchange="set_plantas_cajas_cliente()" required>
                            <option disabled selected> Seleccione</option>
                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id_cliente }}"> {{ $cliente->nombre }} </option>
                            @endforeach
                        </select>
                        <input type="hidden" id="iva_cliente" name="iva_cliente" value="">
                        <input type="hidden" id="calibre_estandar" name="calibre_estandar"
                            value="{{ getCalibreRamoEstandar()->nombre }}">
                        <input type="hidden" id="ramos_x_caja_conf_empresa" name="ramos_x_caja_conf_empresa"
                            value="{{ getConfiguracionEmpresa()->ramos_x_caja }}">
                    </div>
                    <div class="col-md-3 hide">
                        <label for="envio" style="font-size: 11pt;margin-top: 30px">Envío automático</label>
                        <button type='button' id="" class='btn btn-xs btn-default'>
                            <input type="checkbox" id="envio_automatico" name="envio_automatico" checked>
                        </button>
                    </div>
                    <div class="col-md-2">
                        <label for="id_configuracion_empresa" style="font-size: 11pt">
                            Variedad
                        </label>
                        <select class="form-control" id="filtro_planta" name="filtro_planta"
                            onchange="getVariedadesByPlanta(); cargar_espeicificaciones_cliente(true)">
                            <option value="">TODOS</option>
                            {{-- @foreach ($plantas as $p)
                                <option style=" color: black" value="{{$p->id_planta}}">{{$p->nombre}}</option>
                            @endforeach --}}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="id_configuracion_empresa" style="font-size: 11pt">
                            Color
                        </label>
                        <select class="form-control" id="filtro_variedad" name="filtro_variedad"
                            onchange="cargar_espeicificaciones_cliente(true)">
                            <option value="">TODOS</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <label for="id_configuracion_empresa" style="font-size: 11pt">
                                Caja
                            </label>
                            <div class="input-group select2_rxc">
                                <select class="form-control" id="filtro_caja" name="filtro_caja" style="width:120px"
                                    onchange="cargar_espeicificaciones_cliente(true)">
                                    <option value="">TODOS</option>
                                </select>
                                <span class="input-group-btn">
                                    <input type="number" class="form-control" id="search_rxc"
                                        style="width:80px;height: 33px;" placeholder="RXC">
                                </span>
                                <span class="input-group-btn">
                                    <span class="dropdown">
                                        <button class="btn btn-xs dropdown-toggle" type="button"
                                            data-toggle="dropdown"
                                            style="padding-top: 3px;padding-bottom: 3px;position: relative;bottom: 0px;height: 34px;"
                                            aria-expanded="true">
                                            Marcaciones
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            @foreach ($marcaciones as $m)
                                                <li onclick="ver_marcacion('{{ $m->nombre }}')"
                                                    class="btn btn-default text-left"
                                                    style="cursor:pointer;padding:5px 3px;display:grid">
                                                    <em> {{ $m->nombre }}</em>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </span>
                                </span>
                                @if (!$pedido_fijo)
                                    <span class="input-group-btn">
                                        <button type='button' class='btn btn-success'
                                            onclick='cargar_espeicificaciones_cliente(true)'>
                                            <i class='fa fa-search'></i>
                                        </button>
                                    </span>
                                @endif
                            </div>

                        </div>
                    </div>
                    <div class="col-md-2 hide">
                        <label for="id_configuracion_empresa" style="font-size: 11pt">
                            <i class="fa fa-building-o"></i> Facturar con:
                        </label>
                        <div class="input-group">
                            <select class="form-control" id="id_configuracion_empresa"
                                name="id_configuracion_empresa"
                                title="Seleccione un empresa para facturar los pedidos">
                                @foreach (getConfiguracionEmpresa(null, true) as $empresa)
                                    @php $lastPedido = getLastPedido(); @endphp
                                    <option
                                        {{ isset($lastPedido) ? ($lastPedido->id_configuracion_empresa === $empresa->id_configuracion_empresa ? 'selected' : '') : '' }}
                                        style=" color: black" value="{{ $empresa->id_configuracion_empresa }}">
                                        {{ $empresa->nombre }}</option>
                                @endforeach
                            </select>
                            @if (!$pedido_fijo)
                                <span class="input-group-btn">
                                    <button type='button' class='btn btn-success'
                                        onclick='cargar_espeicificaciones_cliente(true)'>
                                        <i class='fa fa-search'></i>
                                    </button>
                                </span>
                            @endif
                        </div>
                    </div>
                    @if ($pedido_fijo)
                        <div class="col-md-2">
                            <label for="id_configuracion_empresa" style="font-size: 11pt">
                                Tipo de pedido
                            </label>
                            <div class="input-group">
                                <select class="form-control" id="tipo_pedido_fijo" name="tipo_pedido_fijo">
                                    <option value="STANDING ORDER">STANDING ORDER</option>
                                    <option value="OPEN MARKET">OPEN MARKET</option>
                                </select>
                                <span class="input-group-btn">
                                    <button type='button' class='btn btn-success'
                                        onclick='cargar_espeicificaciones_cliente(true)'>
                                        <i class='fa fa-search'></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                    @endif
                @else
                    <input type="hidden" value="{{ $idCliente }}" id="id_cliente_venta">
                @endif
                <div class="col-md-2">
                    <label for="id_configuracion_empresa" style="font-size: 11pt">
                        Consignatario
                    </label>
                    <select class="form-control" id="filtro_consignatario" name="filtro_consignatario">
                        <option value="">TODOS</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12" id="table_campo_pedido" style="overflow-x: scroll"></div>
                <div class="text-danger col-md-12" id="error_codigo_venture"></div>
                {{-- <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-addon" style="background-color: #e9ecef">
                            Medida
                        </span>
                        <input class="form-control form-control-m" >
                    </div>
                </div> --}}
                <div class="col-md-12">
                    <div id="productos_seleccionados" style="margin-top: 15px">
                        <table width="100%" class="table-responsive table-bordered"
                            style="font-size: 0.8em; border-color: white" id="table_productos_pedidos">
                            <thead id="thead_productos_seleccionados"></thead>
                            <tbody id="tbody_productos_seleccionados">
                                @if (isset($detalles) && count($detalles))
                                    <input type="hidden" id="tipo_pedido_fijo"
                                        value="{{ $detalles[0]->pedido->tipo_pedido }}">
                                    @php $anterior = ''; @endphp
                                    @foreach ($detalles as $x => $det_ped)
                                        @php
                                            $b = 1;
                                            $det_ped_cliente_especificacion = $det_ped->cliente_especificacion;
                                            $det_ped_especificacion = $det_ped_cliente_especificacion->especificacion;
                                            $getCantidadDetallesByEspecificacion = getCantidadDetallesByEspecificacion($det_ped_especificacion->id_especificacion);
                                            $det_ped_data_tallos = $det_ped->data_tallos;
                                            $id_random = mt_rand(200, 9999999);
                                            $especificaciones = getEspecificacion($det_ped_especificacion->id_especificacion)->especificacionesEmpaque;
                                        @endphp
                                        @foreach ($especificaciones as $y => $esp_emp)
                                            @foreach ($esp_emp->detalles as $z => $det_esp_emp)
                                                @php
                                                    $ramos_modificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);
                                                    $det_esp_emp_variedad = $det_esp_emp->variedad;
                                                    $det_esp_emp_planta = $det_esp_emp_variedad->planta;
                                                    $det_esp_emp_clasificacion_ramo = $det_esp_emp->clasificacion_ramo;
                                                @endphp
                                                <tr style="border-top: {{ $det_ped_especificacion->id_especificacion != $anterior ? '2px solid #9d9d9d' : '' }}"
                                                    class="tr_pedido_combo_{{ $id_random }}">
                                                    @if ($det_ped_especificacion->id_especificacion != $anterior)
                                                        <td style="border-color: #9d9d9d;padding: 0px 0px; vertical-align: middle;width:60px"
                                                            class="text-center {{ $esp_emp->creada == 'PRE-ESTABLECIDA' ? 'td_piezas_simple' : 'td_piezas_combo' }}"
                                                            rowspan="{{ $getCantidadDetallesByEspecificacion }}"
                                                            data-cliente_predido_espcificacion_editada="false">
                                                            <input type="number" min="0"
                                                                id="cantidad_piezas_{{ $x + 1 }}"
                                                                style="width:100%;height: 34px;"
                                                                onkeyup="calcular_precio_pedido(null)"
                                                                onchange="calcular_precio_pedido(null)"
                                                                name="cantidad_piezas_{{ $det_ped_especificacion->id_especificacion }}"
                                                                class="text-center cantidad_{{ $x + 1 }} input_cantidad"
                                                                value="{{ isset($det_ped_data_tallos->mallas) ? $det_ped_data_tallos->mallas : $det_ped->cantidad }}"
                                                                data-id_cliente_pedido_especificacion="{{ $det_ped_cliente_especificacion->id_cliente_pedido_especificacion }}"
                                                                data-id_tr_producto="{{ $id_random }}">
                                                        </td>
                                                    @endif
                                                    <td style="border-color: #9d9d9d; padding: 0px; vertical-align: middle; width: 60px;"
                                                        class="text-center td_planta_editar_pedido">
                                                        <select class="id_planta_editar_pedido form-control"
                                                            onchange="set_variedades_editar_pedido(this);"
                                                            data-id_random="{{ $id_random }}">
                                                            @foreach ($plantas as $p)
                                                                <option
                                                                    {{ $p->id_planta == $det_esp_emp_variedad->planta->id_planta ? 'selected' : '' }}
                                                                    value="{{ $p->id_planta }}">
                                                                    {{ $p->planta }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <span
                                                            class="span_editar_pedido">{{ $det_esp_emp_variedad->planta->nombre }}</span>
                                                    </td>
                                                    <td style="border-color: #9d9d9d; padding: 0px; vertical-align: middle; width: 60px"
                                                        class="text-center td_variedad td_variedad_{{ $id_random }} td_variedad_editar_pedido"
                                                        data-id_variedad="{{ $det_esp_emp_variedad->id_variedad }}">
                                                        <select class="id_variedad_editar_pedido form-control"
                                                            onchange="$(this).parent().attr('data-id_variedad',this.value); cambiar_cliente_pedido_especificacion(this)"
                                                            data-id_random="{{ $id_random }}">
                                                        </select>
                                                        <span
                                                            class="span_editar_pedido">{{ $det_esp_emp_variedad->nombre }}</span>
                                                    </td>
                                                    <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;width:70px"
                                                        class="text-center td_calibre_{{ $x + 1 }}_{{ $b }} td_calibre_{{ $x + 1 }} td_calibre_{{ $id_random }} td_calibre td_calibre_editar_pedido"
                                                        data-id_det_esp_emp="{{ $det_esp_emp->id_detalle_especificacionempaque }}">
                                                        <span
                                                            class="span_editar_pedido">{{ $det_esp_emp_clasificacion_ramo->nombre }}
                                                            {{ $det_esp_emp_clasificacion_ramo->unidad_medida->siglas }}</span>
                                                        <select
                                                            class="id_clasificacion_ramo_editar_pedido form-control"
                                                            onchange="cambiar_cliente_pedido_especificacion(this)"
                                                            data-id_random="{{ $id_random }}">
                                                            @foreach ($clasificacionRamos as $cr)
                                                                <option
                                                                    {{ $det_esp_emp_clasificacion_ramo->id_clasificacion_ramo == $cr->id_clasificacion_ramo ? 'selected' : '' }}
                                                                    value="{{ $cr->id_clasificacion_ramo }}">
                                                                    {{ $cr->nombre }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    @if ($z == 0)
                                                        <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;"
                                                            class="text-center td_caja_editar_pedido"
                                                            rowspan="{{ count($esp_emp->detalles) }}">
                                                            <span
                                                                class="span_editar_pedido">{{ explode('|', $esp_emp->empaque->nombre)[0] }}</span>
                                                            <select class="id_caja_editar_pedido form-control"
                                                                onchange="cambiar_cliente_pedido_especificacion(this)"
                                                                data-id_random="{{ $id_random }}">
                                                                @foreach ($cajas as $c)
                                                                    <option
                                                                        {{ $c->id_empaque == $esp_emp->empaque->id_empaque ? 'selected' : '' }}
                                                                        value="{{ $c->id_empaque }}">
                                                                        {{ explode('|', $c->caja)[0] }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                    @endif
                                                    <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;width:65px"
                                                        class="text-center td_presentacion_{{ $x + 1 }}_{{ $b }} td_presentacion_{{ $x + 1 }} td_presentacion td_presentacion_editar_pedido"
                                                        data-id_presentacion="{{ $det_esp_emp->empaque_p->id_empaque }}">
                                                        <span
                                                            class="span_editar_pedido">{{ $det_esp_emp->empaque_p->nombre }}</span>
                                                        <select class="id_presentacion_editar_pedido form-control"
                                                            onchange="$(this).parent().attr('data-id_presentacion',this.value); cambiar_cliente_pedido_especificacion(this)"
                                                            data-id_random="{{ $id_random }}">
                                                        </select>
                                                    </td>
                                                    <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;width:62px"
                                                        class="text-center ramos_x_caja_{{ $x + 1 }} ramos_x_caja_{{ $x + 1 }}_{{ $b }}"
                                                        data-ramos_x_caja="{{ $det_esp_emp->cantidad }}"
                                                        data-id_det_esp_emp="{{ $det_esp_emp->id_detalle_especificacionempaque }}">
                                                        <span
                                                            class="span_editar_pedido">{{ isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad }}</span>
                                                        <input type="number" min="0"
                                                            id="ramos_x_caja_{{ $x + 1 }}_{{ $b }}"
                                                            value="{{ isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad }}"
                                                            style="width: 100%;padding-left: 1px;padding-right: 1px;text-align: center;display:none"
                                                            onchange="$(this).parent().find('.span_editar_pedido').html(this.value); calcular_precio_pedido(null); cambiar_cliente_pedido_especificacion(this,'input')"
                                                            onkeyup="$(this).parent().find('.span_editar_pedido').html(this.value); calcular_precio_pedido(null); cambiar_cliente_pedido_especificacion(this,'input')"
                                                            class="input_ramos_x_caja text-center form-control input_ramos_x_caja_{{ $id_random }}"
                                                            data-id_random="{{ $id_random }}">
                                                    </td>
                                                    <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;width:40px"
                                                        class="td_tallos_x_malla td_tallos_x_malla_{{ $x + 1 }} td_tallos_x_malla_{{ $x + 1 }}_{{ $b }}
                                                        {{ isset($det_ped_especificacion->tipo) && $det_ped_especificacion->tipo == 'O' ? '' : 'hide' }}">
                                                        <input type="number" min="0"
                                                            id="tallos_x_malla_{{ $x + 1 }}_{{ $b }}"
                                                            name="tallos_x_malla_{{ $x + 1 }}_{{ $b }}"
                                                            class="text-center tallos_x_malla_{{ $x + 1 }} tallos_x_malla_{{ $x + 1 }}_{{ $b }}"
                                                            value="{{ isset($det_ped_data_tallos->tallos_x_malla) ? $det_ped_data_tallos->tallos_x_malla : '' }}"
                                                            style="border: none;width: 100%;height: 34px;">
                                                        <input type="hidden"
                                                            id="tallos_x_caja_{{ $x + 1 }}_{{ $b }}"
                                                            name="tallos_x_caja_{{ $x + 1 }}_{{ $b }}"
                                                            class="text-center tallos_x_caja_{{ $x + 1 }} tallos_x_caja_{{ $x + 1 }}_{{ $b }}">
                                                    </td>
                                                    @if ($det_ped_especificacion->id_especificacion != $anterior)
                                                        <td id="td_total_ramos_{{ $x + 1 }}"
                                                            style="border-color: #9d9d9d; padding: 0px; vertical-align: middle; width: 45px;"
                                                            class="text-center td_total_ramos td_total_ramos_{{ $id_random }}"
                                                            rowspan="{{ $getCantidadDetallesByEspecificacion }}">
                                                            {{ isset($det_ped_especificacion->ramos_x_caja) ? $det_ped_especificacion->ramos_x_caja * $det_ped->cantidad : 0 }}
                                                        </td>
                                                    @endif
                                                    <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;width:60px"
                                                        class="text-center td_tallos_x_ramo_{{ $x + 1 }}_{{ $b }} td_tallos_x_ramo_{{ $x + 1 }} td_tallos_x_ramo_producto">
                                                        <span
                                                            class="span_editar_pedido">{{ $det_esp_emp->tallos_x_ramos }}</span>
                                                        <input
                                                            id="tallos_x_ramo_{{ $x + 1 }}_{{ $b }}"
                                                            name="tallos_x_ramo_{{ $x + 1 }}_{{ $b }}"
                                                            type="number"
                                                            value="{{ $det_esp_emp->tallos_x_ramos }}"
                                                            style="width: 100%;padding-left: 1px;padding-right: 1px;text-align: center;display:none"
                                                            onchange="$(this).parent().find('.span_editar_pedido').html(this.value); calcular_precio_pedido(null); cambiar_cliente_pedido_especificacion(this,'input')"
                                                            onkeyup="$(this).parent().find('.span_editar_pedido').html(this.value); calcular_precio_pedido(null); cambiar_cliente_pedido_especificacion(this,'input')"
                                                            class="tallos_x_ramo_{{ $x + 1 }}_{{ $b }} tallos_x_ramo_{{ $x + 1 }} input_editar_tallos_x_ramo text-center form-control"
                                                            data-id_random="{{ $id_random }}">
                                                    </td>
                                                    @if ($det_ped_especificacion->id_especificacion != $anterior)
                                                        <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;width:35px"
                                                            rowspan="{{ $getCantidadDetallesByEspecificacion }}"
                                                            class="text-center td_tallos_total_{{ $x + 1 }}_{{ $b }} td_tallos_total_{{ $x + 1 }} total_tallos_producto">
                                                            0
                                                        </td>
                                                    @endif
                                                    <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;width:60px"
                                                        class="text-center">
                                                        @if ($det_esp_emp->longitud_ramo != '' && $det_esp_emp->id_unidad_medida != '')
                                                            <span
                                                                class="span_editar_pedido">{{ $det_esp_emp->longitud_ramo }}{{ $det_esp_emp->unidad_medida->siglas }}</span>
                                                            <input type="number"
                                                                id="longitud_ramo_{{ $x + 1 }}_{{ $b }}"
                                                                name=""
                                                                class="longitud_ramo_{{ $x + 1 }} input_editar_pedido_longitud_ramo form-control"
                                                                onchange="$(this).parent().find('.span_editar_pedido').html(this.value+'cm'); cambiar_cliente_pedido_especificacion(this,'input')"
                                                                onkeyup="$(this).parent().find('.span_editar_pedido').html(this.value+'cm'); cambiar_cliente_pedido_especificacion(this,'input')"
                                                                value="{{ $det_esp_emp->longitud_ramo }}"
                                                                style="display:none;padding:0;text-align:center"
                                                                data-id_random="{{ $id_random }}">
                                                            <input type="hidden"
                                                                id="u_m_longitud_ramo_{{ $x + 1 }}_{{ $b }}"
                                                                name=""
                                                                class="u_m_longitud_ramo_{{ $x + 1 }}"
                                                                value="{{ $det_esp_emp->unidad_medida->id_unidad_medida }}">
                                                        @endif
                                                    </td>
                                                    <td id="td_precio_variedad_{{ $det_esp_emp->id_detalle_especificacionempaque }}_{{ $x + 1 }}"
                                                        style="border-color: #9d9d9d;padding: 0px 0px; vertical-align: middle;">
                                                        <input type="number" name="precio_{{ $x + 1 }}"
                                                            class="form-control text-center precio_x_ramo precio_x_ramo_{{ $id_random }}"
                                                            style="background-color: beige; width: 100%;text-align: center;padding-left: 1px;padding-right: 1px"
                                                            min="0" onchange="calcular_precio_pedido(null)"
                                                            onkeyup="calcular_precio_pedido(null)"
                                                            value="{{ isset(explode('|', $det_ped->precio)[$b - 1]) ? explode(';', explode('|', $det_ped->precio)[$b - 1])[0] : 0 }}"
                                                            required>
                                                    </td>
                                                    @if ($det_ped_especificacion->id_especificacion != $anterior)
                                                        <td id="td_precio_especificacion_{{ $x + 1 }}"
                                                            style="border-color: #9d9d9d;padding: 0px 0px; vertical-align: middle;width:70px"
                                                            class="text-center td_precio_total text-center td_precio_total_{{ $id_random }}"
                                                            rowspan="{{ $getCantidadDetallesByEspecificacion }}">
                                                        </td>
                                                        <td class="text-center agencia_carga"
                                                            style="border-color: #9d9d9d; vertical-align: middle;width:75px"
                                                            rowspan="{{ $getCantidadDetallesByEspecificacion }}">
                                                            <select
                                                                name="id_agencia_carga_{{ $det_ped_especificacion->id_especificacion }}"
                                                                id="id_agencia_carga_{{ $x + 1 }}"
                                                                class="text-center agencia_carga"
                                                                style="border: none; width:100%;height: 34px;"
                                                                onchange="agencia_selected(this)" required>
                                                                @foreach ($agenciasCarga as $agencia)
                                                                    <option {!! $det_ped->id_agencia_carga == $agencia->id_agencia_carga ? 'selected' : '' !!}
                                                                        value="{{ $agencia->id_agencia_carga }}">
                                                                        {{ $agencia->nombre }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        @foreach ($datos_exportacion as $de)
                                                            <td rowspan="{{ $getCantidadDetallesByEspecificacion }}"
                                                                style="border-color: #9d9d9d; vertical-align: middle">
                                                                @php $getDe = getDatosExportacion($det_ped->id_detalle_pedido,$de->id_dato_exportacion); @endphp
                                                                <input type="text"
                                                                    name="input_{{ strtoupper($de->datos_exportacion->nombre) }}_{{ $x + 1 }}"
                                                                    id="input_{{ strtoupper($de->datos_exportacion->nombre) }}_{{ $x + 1 }}"
                                                                    class="input_{{ strtoupper($de->datos_exportacion->nombre) }}"
                                                                    style="border: none;width:100%;height:34px"
                                                                    value="{{ isset($getDe->valor) ? $getDe->valor : '' }}">
                                                                <input type="hidden"
                                                                    name="id_dato_exportacion_{{ strtoupper($de->datos_exportacion->nombre) }}_{{ $x + 1 }}"
                                                                    class="id_dato_exportacion_{{ strtoupper($de->datos_exportacion->nombre) }}"
                                                                    id="id_dato_exportacion_{{ strtoupper($de->datos_exportacion->nombre) }}_{{ $x + 1 }}"
                                                                    value="{{ $de->id_dato_exportacion }}">
                                                            </td>
                                                        @endforeach
                                                        <td class="text-center"
                                                            style="border-color: #9d9d9d; vertical-align: middle;width:151px"
                                                            rowspan="{{ $getCantidadDetallesByEspecificacion }}"
                                                            id="{{ $y == 0 ? 'td-btn-delete-row' : '' }}">
                                                            <div class="btn-group">
                                                                @if ($det_ped->pedido->fecha_pedido > opDiasFecha('+', 1, hoy()) || 1)
                                                                    <button type="button"
                                                                        class="btn btn-yura_warning btn-sm btn-edit-seleccion"
                                                                        style="" title="Editar"
                                                                        onclick="habilitar_edicion_detalle_pedido(this,'{{ $id_random }}')">
                                                                        <i class="fa fa-pencil"></i>
                                                                    </button>
                                                                @endif
                                                                <button type="button"
                                                                    class="btn btn-yura_primary btn-sm btn-add-seleccion"
                                                                    style="" title="Duplicar"
                                                                    onclick="duplicar_producto_pedido(this,'{{ $id_random }}')">
                                                                    <i class="fa fa-plus"></i>
                                                                </button>
                                                                @if ($getCantidadDetallesByEspecificacion > 1)
                                                                    <button type="button"
                                                                        class="btn btn-yura_warning btn-sm"
                                                                        title="Editar Combo"
                                                                        onclick="editar_combo('{{ $det_ped->id_detalle_pedido }}')">
                                                                        <i class="fa fa-edit"></i>
                                                                    </button>
                                                                @endif
                                                                @if ($esp_emp->creada == 'PRE-ESTABLECIDA')
                                                                    <button type="button"
                                                                        class="btn btn-yura_danger btn-sm btn-remove-seleccion"
                                                                        style="" title="Eliminar del pedido"
                                                                        onclick="eliminar_producto_pedido(this,'{{ $det_ped->id_detalle_pedido }}')">
                                                                        <i class="fa fa-trash"></i>
                                                                    </button>
                                                                @else
                                                                    <button type="button"
                                                                        class="btn btn-yura_danger btn-sm btn-remove-seleccion"
                                                                        style="" title="Eliminar del pedido"
                                                                        onclick="eliminar_combo_pedido('{{ $id_random }}','{{ $det_ped->id_detalle_pedido }}')">
                                                                        <i class="fa fa-trash"></i>
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    @endif
                                                </tr>
                                                @php
                                                    $anterior = $det_ped_especificacion->id_especificacion;
                                                    $b++;
                                                @endphp
                                            @endforeach
                                        @endforeach
                                        @php $anterior = ''; @endphp
                                    @endforeach
                                @endif
                                <tr id="alert_sin_productos" class="{{ isset($especificaciones) ? 'hide' : '' }}">
                                    <td class="alert alert-warning text-center" colspan="200">
                                        NO SE HAN SELECCIONADO PRODUCTOS
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <table style="width: 100%;text-align: right;margin-top:20px">
                        <tr>
                            <td><b>TOTAL DE PIEZAS:</b></td>
                            <td style="vertical-align: middle;font-size: 14px;text-align: right; width: 8%;"
                                id="total_piezas">0</td>
                        </tr>
                        <tr>
                            <td><b>TOTAL DE RAMOS:</b></td>
                            <td style="vertical-align: middle;font-size: 14px;text-align: right; width: 8%;"
                                id="total_ramos">0</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    @if (es_server())
                        <table style="width: 100%;">
                            @if (
                                (isset($comprobante->estado) &&
                                    $comprobante->estado != 5 &&
                                    (isset($comprobante->estado) && $comprobante->estado != 6) &&
                                    (isset(getPedido($id_pedido)->envios[0]->comprobante->integrado) &&
                                        !getPedido($id_pedido)->envios[0]->comprobante->integrado)) ||
                                    !isset($comprobante->estado))
                                <tr>
                                    <td class="text-center" style="padding: 10px 0px 0px">
                                        <button type="button"
                                            class=" btn btn-app btn-xs btn-success store_pedido_normal"
                                            onclick="store_pedido('{{ $idCliente }}','@if ($pedido_fijo) {{ true }} @endif','{{ csrf_token() }}','{{ $vista }}','{{ $id_pedido }}')">
                                            <span class="badge bg-green monto_total_pedido">$0.00</span>
                                            <i class="fa fa-shopping-cart"></i> Guardar
                                        </button>
                                        <button type="button" class=" btn btn-app btn-xs btn-success"
                                            onclick="cerrar_modals(); editar_pedido('{{ $idCliente }}', '{{ $id_pedido }}', '{{ $tipo }}')">
                                            <i class="fa fa-refresh" aria-hidden="true"></i> Reiniciar orden
                                        </button>
                                        @if ($tipo == 'STANDING ORDER')
                                            <button type="button" class=" btn btn-app btn-xs btn-success"
                                                onclick="update_orden_fija('{{ $id_pedido }}')">
                                                <i class="fa fa-save" aria-hidden="true"></i>
                                                Actualizar Orden Fija
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    //$(seletcs2).select2("destroy")
    function input_numero_ficticio(check) {
        factura_ficticia = $("#numero_ficticio");
        if ($(check).is(':checked')) {
            factura_ficticia.removeAttr('disabled');
        } else {
            factura_ficticia.attr('disabled', true);
        }
    }

    function set_plantas_cajas_cliente() {

        let datos = {
            id_cliente: $("#id_cliente_venta").val()
        }

        get_jquery('/pedidos/set_plantas_cajas_cliente', datos, function(retorno) {

            let html = `<option value="">TODOS</option>`

            retorno.plantas.forEach(option => {
                html += `<option value="${option.id_planta}">${option.planta}</option>`
            })

            $("#filtro_planta").html(html)

            let html_cajas = `<option value="">TODOS</option>`

            retorno.cajas.forEach(option => {
                html_cajas +=
                    `<option value="${option.id_empaque}">${option.caja.split('|')[0]}</option>`
            })

            let html_consignatario = `<option value="">TODOS</option>`

            @if (isset($detalles) && count($detalles))
                let id_consignatario_default = '{{ $detalles[0]->pedido->envios[0]->id_consignatario }}'
                retorno.consignatario.forEach(option => {
                    html_consignatario +=
                        `<option  ${option.id_consignatario == id_consignatario_default ? 'selected': ''} value="${option.id_consignatario}">${option.nombre}</option>`
                })
            @else
                retorno.consignatario.forEach(option => {
                    html_consignatario +=
                        `<option ${option.default ? 'selected': ''} value="${option.id_consignatario}">${option.nombre}</option>`
                })
            @endif

            $("#filtro_caja").html(html_cajas)
            $("#filtro_consignatario").html(html_consignatario)


        }, 'filtro_planta')

    }

    setTimeout(() => {
        $("#id_cliente_venta, #filtro_planta, #filtro_variedad, #filtro_caja, #filtro_consignatario, select.id_planta_editar_pedido, select.id_variedad_editar_pedido, select.id_clasificacion_ramo_editar_pedido, select.id_caja_editar_pedido, select.id_presentacion_editar_pedido")
            .select2({
                dropdownParent: $('#div_modal-modal_add_pedido')
            })
    }, 500)

    function getVariedadesByPlanta() {

        let datos = {
            id_planta: $("#filtro_planta").val(),
        }
        get_jquery('/clientes/get_variedades_by_planta', datos, function(retorno) {

            let html = `<option value="">TODOS</option>`

            retorno.orden_variedades.forEach((option) => {
                html += `<option value="${option.id_variedad}">${option.nombre}</option>`
            })
            $("#filtro_variedad").html(html)

        }, 'filtro_variedad')

    }

    function ver_marcacion(nombre) {

        $.each($("input.input_" + nombre), function(i, obj) {

            if ($(obj).parent().hasClass('hide')) {
                $(obj).parent().removeClass('hide')
            } else {
                $(obj).parent().addClass('hide')
                $(obj).val('')
            }

        })

        $.each($("th.th_datos_exportacion_" + nombre), function(i, obj) {

            if ($(obj).hasClass('hide')) {
                $(obj).removeClass('hide')
            } else {
                $(obj).addClass('hide')
            }

        })

    }

    function duplicar_producto_pedido(ele, id) {

        let new_id = parseInt(Math.random() * 100000)
        let tr = $("tr.tr_pedido_combo_" + id)

        let btn_remove_seleccion = `
        <button type="button" class="btn btn-yura_danger btn-sm btn-remove-seleccion"
                title="Eliminar del pedido" onclick="eliminar_combo_pedido('${new_id}','')">
            <i class="fa fa-trash"></i>
        </button>
        `

        let btn_editar_seleccion = `
        <button type="button" class="btn btn-yura_warning btn-sm btn-edit-seleccion"
                title="Editar" onclick="habilitar_edicion_detalle_pedido(this,'${new_id}')">
            <i class="fa fa-pencil"></i>
        </button>
        `

        let btn_duplicar_seleccion = `
        <button type="button" class="btn btn-yura_primary btn-sm btn-add-seleccion"
                title="Editar" onclick="duplicar_producto_pedido(this,'${new_id}')">
            <i class="fa fa-plus"></i>
        </button>
        `

        let btns = btn_editar_seleccion + btn_duplicar_seleccion + btn_remove_seleccion

        let html = tr.clone().removeClass('tr_pedido_combo_' + id).addClass('tr_pedido_combo_' + new_id)
            .find('td.td_variedad').removeClass('td_variedad_' + id).addClass('td_variedad_' + new_id).end()
            .find('td.td_calibre').removeClass('td_calibre_' + id).addClass('td_calibre_' + new_id).end()
            .find('td.td_precio_total').removeClass('td_precio_total_' + id).addClass('td_precio_total_' + new_id).end()
            .find('td.td_total_ramos').removeClass('td_total_ramos_' + id).addClass('td_total_ramos_' + new_id).end()
            .find('button.btn-remove-seleccion, button.btn-edit-seleccion, button.btn-add-seleccion').remove().end()
            .find('td#td-btn-delete-row div.btn-group').append(btns).end()
            .find('input.input_cantidad').attr('data-id_tr_producto', new_id).end()
            .find('select.id_planta_editar_pedido').attr('data-id_random', new_id).end()
            .find('select.id_variedad_editar_pedido').attr('data-id_random', new_id).end()
            .find('select.id_clasificacion_ramo_editar_pedido').attr('data-id_random', new_id).end()
            .find('select.id_caja_editar_pedido').attr('data-id_random', new_id).end()
            .find('select.id_presentacion_editar_pedido').attr('data-id_random', new_id).end()
            .find('input.input_ramos_x_caja').attr('data-id_random', new_id).end()
            .find('input.input_editar_tallos_x_ramo').attr('data-id_random', new_id).end()
            .find('input.input_editar_pedido_longitud_ramo').attr('data-id_random', new_id).end()
            .find('input.precio_x_ramo').removeClass('precio_x_ramo_' + id).addClass('precio_x_ramo_' + new_id).attr(
                'data-id_random', new_id).end()
            .find(
                "td.td_planta_editar_pedido span.select2-container, td.td_variedad_editar_pedido span.select2-container, td.td_calibre span.select2-container, td.td_caja_editar_pedido span.select2-container, td.td_presentacion span.select2-container"
            ).remove().end()
            .find(
                "select.id_planta_editar_pedido, select.id_variedad_editar_pedido, select.id_clasificacion_ramo_editar_pedido, select.id_caja_editar_pedido, select.id_presentacion_editar_pedido"
            ).select2().end()

        $("tbody#tbody_productos_seleccionados").append(html)

    }

    async function habilitar_edicion_detalle_pedido(btn, id) {

        if ($(btn).find('i').hasClass('fa-pencil')) {
            $(btn).find('i').removeClass('fa-pencil').addClass('fa-undo')
        } else {
            $(btn).find('i').removeClass('fa-undo').addClass('fa-pencil')
        }

        for (i = 0; i <= $("tr.tr_pedido_combo_" + id).length; i++) {

            let ele = $("tr.tr_pedido_combo_" + id)[i]

            if ($(ele).find('span.select2-container').css('display') == 'none') {

                $(ele).find('span.select2-container').css('display', 'grid')
                $(ele).find('.span_editar_pedido').css('display', 'none')

                $(ele).find(
                    'input.input_ramos_x_caja, input.input_editar_tallos_x_ramo, input.input_editar_pedido_longitud_ramo'
                ).css('display', 'grid')

                await set_variedades_editar_pedido($(ele).find('select.id_planta_editar_pedido'), 1)

            } else {

                $(ele).find('span.select2-container').css('display', 'none')
                $(ele).find('.span_editar_pedido').css('display', 'inline-block')

                $(ele).find(
                    'input.input_ramos_x_caja, input.input_editar_tallos_x_ramo, input.input_editar_pedido_longitud_ramo'
                ).css('display', 'none')

            }

        }

        /* $.each($("tr.tr_pedido_combo_"+id), async function(i,j){

            if($(this).find('span.select2-container').css('display') == 'none'){

                $(this).find('span.select2-container').css('display','grid')
                $(this).find('.span_editar_pedido').css('display','none')

                $(this).find('input.input_ramos_x_caja, input.input_editar_tallos_x_ramo, input.input_editar_pedido_longitud_ramo').css('display','grid')

                await set_variedades_editar_pedido($(this).find('select.id_planta_editar_pedido'),1)

            }else{

                $(this).find('span.select2-container').css('display','none')
                $(this).find('.span_editar_pedido').css('display','inline-block')

                $(this).find('input.input_ramos_x_caja, input.input_editar_tallos_x_ramo, input.input_editar_pedido_longitud_ramo').css('display','none')

            }

        }) */

    }

    function set_variedades_editar_pedido(select, numero = 1) {

        return new Promise(resolve => {

            let datos = {
                id_planta: $(select).val(),
                id_cliente: $("#id_cliente_venta").val()
            }

            get_jquery('/clientes/get_variedades_by_planta_editar_pedido', datos, function(retorno) {

                let html_variedades = ``
                let html_presentaciones = ``

                let id_variedad_selected = $(select).parent().parent().find('td.td_variedad').data(
                    'id_variedad')
                let id_presentacion_selected = $(select).parent().parent().find('td.td_presentacion')
                    .data('id_presentacion')

                retorno.variedades.forEach((option) => {
                    html_variedades +=
                        `<option ${id_variedad_selected == option.id_variedad ? 'selected' : ''} value="${option.id_variedad}">${option.nombre}</option>`
                })

                retorno.presentaciones.forEach((option) => {
                    html_presentaciones +=
                        `<option ${id_presentacion_selected == option.id_empaque ? 'selected' : ''} value="${option.id_empaque}">${option.nombre}</option>`
                })

                $(select).parent().parent().find('select.id_variedad_editar_pedido').html(
                    html_variedades)
                $(select).parent().parent().find('select.id_presentacion_editar_pedido').html(
                    html_presentaciones)

                resolve()

                if (numero > 0)
                    cambiar_cliente_pedido_especificacion(select)

            }, 'eeeee')

        })

    }

    function cambiar_cliente_pedido_especificacion(input, tipo = 'select') {

        let id = $(input).attr('data-id_random')

        let detalle_especificacion = {
            id_empaque: $($("tr.tr_pedido_combo_" + id)[0]).find('select.id_caja_editar_pedido').val(),
            detalles_especificacion_empaque: []
        }

        $.each($("tr.tr_pedido_combo_" + id), function() {

            console.log($(this).find('select.id_variedad_editar_pedido').val())

            if ($(this).find('select.id_variedad_editar_pedido').val() != null) {

                detalle_especificacion.detalles_especificacion_empaque.push({
                    id_variedad: $(this).find('select.id_variedad_editar_pedido').val(),
                    id_clasificacion_ramo: $(this).find('select.id_clasificacion_ramo_editar_pedido')
                        .val(),
                    cantidad: $(this).find('input.input_ramos_x_caja').val(),
                    id_empaque_p: $(this).find('select.id_presentacion_editar_pedido').val(),
                    tallos_x_ramos: $(this).find('input.input_editar_tallos_x_ramo').val(),
                    longitud_ramo: $(this).find('input.input_editar_pedido_longitud_ramo').val(),
                    id_det_esp_emp: $(this).find('select.id_clasificacion_ramo_editar_pedido').parent()
                        .attr('data-id_det_esp_emp')
                })

            } else {

                detalle_especificacion.detalles_especificacion_empaque = []
                return false

            }


        })

        if (detalle_especificacion.detalles_especificacion_empaque.length) {

            let data = {
                _token: '{{ csrf_token() }}',
                id_cliente: $("#id_cliente_venta").val(),
                detalle_especificacion
            }

            if ($("tr.tr_pedido_combo_" + id).find('input.input_cantidad').parent().attr(
                    'data-cliente_predido_espcificacion_editada') == 'false') {

                post_jquery_m('/clientes/crear_detalle_pedido_edicion', data, function(res) {

                    if (res.success && res.idClientePedidoEspecificacion != null) {

                        $("tr.tr_pedido_combo_" + id).find('input.input_cantidad').attr(
                            'data-id_cliente_pedido_especificacion', res.idClientePedidoEspecificacion)

                        $("tr.tr_pedido_combo_" + id).find('input.input_cantidad').parent().attr(
                            'data-cliente_predido_espcificacion_editada', true)

                        $.each($("tr.tr_pedido_combo_" + id), function(i, j) {
                            $(this).find('td.td_calibre').attr('data-id_det_esp_emp', res.idsDetsEspemp[
                                i])
                        })

                    }

                }, 'table_productos_pedidos')

            } else {

                post_jquery_m('/clientes/actualizar_detalle_pedido_edicion', data, function(res) {


                }, 'eeee')

            }

        }

    }

    function editar_combo(det_ped) {
        datos = {
            detalle: det_ped
        }
        get_jquery('{{ url('pedidos/editar_combo') }}', datos, function(retorno) {
            modal_view('modal_editar_combo', retorno, '<i class="fa fa-fw fa-plus"></i> Editar Combo',
                true, false, '{{ isPC() ? '95%' : '' }}',
                function() {});
        });
    }

    function update_orden_fija(id_ped) {
        mensaje = {
            title: '<i class="fa fa-fw fa-refresh"></i> Actualizar toda la orden fija',
            mensaje: '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de ACTUALIZAR la orden fija?</div>',
        };
        modal_quest('modal_update_orden_fija', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id_ped: id_ped,
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
    }
</script>

<style>
    @if (isset($id_pedido))
        table#table_productos_pedidos td.td_caja_editar_pedido span.select2-container,
        table#table_productos_pedidos td.td_planta_editar_pedido span.select2-container,
        table#table_productos_pedidos td.td_variedad_editar_pedido span.select2-container,
        table#table_productos_pedidos td.td_calibre_editar_pedido span.select2-container,
        table#table_productos_pedidos td.td_presentacion_editar_pedido span.select2-container {
            display: none
        }
    @endif

    table#table_productos_pedidos span.selection {
        width: 108px
    }

    div#filtros_pedido .select2-container {
        display: block;
        width: 100% !important
    }

    .select2-selection--single {
        height: 33px !important;
        border-radius: 0px !important;
        border-color: #d2d6de !important;
    }

    div.select2_rxc span.select2-selection {
        width: 200px;
    }
</style>
