<div class="row" style="margin-top:15px">
    <div class="col-md-6">
        <button type="button" class="btn btn-info btn-block btn_pedidos_simples" data-toggle="collapse" data-target="#simple"
        onclick="collapse_pedido_combo()" style="margin-bottom: 15px">
            <b>PEDIDOS SIMPLES</b>
        </button>
    </div>
    <div class="col-md-6">
        <button type="button" class="btn btn-info btn-block" data-toggle="collapse" data-target="#combos"
            onclick="collapse_pedido_simple()" style="margin-bottom: 15px">
            <b>PEDIDOS COMBOS</b>
        </button>
    </div>
</div>

<div id="simple" class="collapse" style="padding:5px">
    <table width="100%" class="table-responsive table-bordered" style="font-size: 0.8em; border-color: white" id="table_content_recepciones">
        <thead id="thead_inputs_dinamicos">
            <tr style="background-color: #dd4b39; color: white">
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width: 30px">
                    PIEZAS
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d">
                    VARIEDAD
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d">
                    COLOR
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:40px">
                    PESO
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:75px">
                    CAJA
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:65px">
                    PRESENTACIÓN
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:45px">
                    R. X CAJA
                </th>
                <th class="text-center hide th_tallo_x_malla table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width:45px">
                    TALLOS X MALLA
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d">
                    TOTAL RAMOS
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:35px">
                    T. X RAMO
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:35px">
                    TOTAL TALLOS
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:35px">
                    LONGITUD
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width:60px">
                    PRECIO
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width:70px">
                    TOTAL VARIEDAD
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width:75px">
                    CARGUERA
                </th>
                @foreach($datos_exportacion as $key => $de)
                    <th class="th_datos_exportacion th_datos_exportacion_{{strtoupper($de->nombre)}}
                        text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}
                        {{( isset($id_pedido) || in_array('th_datos_exportacion_'.strtoupper($de->nombre) , $th_datos_exportacion) ) ? '' : 'hide'}}"
                        id="th_datos_exportacion_{{$key+1}}" style="border-color: #9d9d9d;width: 80px;">
                        {{strtoupper($de->nombre)}}
                    </th>
                @endforeach
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width:100px;width: 20px;">
                    OPCIONES
                </th>
            </tr>
        </thead>
        <tbody id="tbody_inputs_pedidos" class="tbody_inputs_pedidos_simples">
            @if(count($especificaciones) >0)
                @php $anterior = ''; @endphp
                @foreach($especificaciones as $x => $item)
                    @php $b=1 @endphp
                    @foreach(getEspecificacion($item->id_especificacion)->especificacionesEmpaque as $y => $esp_emp)
                        @foreach($esp_emp->detalles as $z => $det_esp_emp)
                            <tr style="border-top: 2px solid #9d9d9d">
                                <td style="border-color: #9d9d9d; padding: 0px; vertical-align: middle; width: 30px;"
                                    class="text-center td_piezas_simple" rowspan="{{getCantidadDetallesByEspecificacion($item->id_especificacion)}}">
                                    <input type="number" min="0" id="cantidad_piezas_{{($x+1)}}" style="border: none;width:100%;height: 34px;"
                                    onchange="calcular_precio_pedido()" onkeyup="calcular_precio_pedido()"
                                    name="cantidad_piezas_{{$item->id_especificacion}}" value=""
                                    class="text-center cantidad_{{($x+1)}} input_cantidad"
                                    data-id_cliente_pedido_especificacion="{{$item->id_cliente_pedido_especificacion}}">
                                </td>

                                <td style="border-color: #9d9d9d; padding: 0px; vertical-align: middle; width: 60px;" class="text-center">
                                    {{$det_esp_emp->variedad->planta->nombre}}
                                </td>
                                <td style="border-color: #9d9d9d; padding: 0px; vertical-align: middle; width: 60px;"
                                    class="text-center td_variedad" data-id_variedad="{{$det_esp_emp->id_variedad}}">
                                    {{$det_esp_emp->variedad->nombre}}
                                </td>
                                <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;width:40px"
                                    class="text-center td_calibre td_calibre_{{$x+1}}_{{$b}} td_calibre_{{$x+1}}"
                                    data-id_det_esp_emp="{{$det_esp_emp->id_detalle_especificacionempaque}}">
                                    <span>{{$det_esp_emp->clasificacion_ramo->nombre}}</span>{{$det_esp_emp->clasificacion_ramo->unidad_medida->siglas}}
                                    <input type="hidden" id="id_clasificacion_ramo_{{$x+1}}" name="id_clasificacion_ramo_{{$x+1}}"
                                        value="{{$det_esp_emp->clasificacion_ramo->id_clasificacion_ramo}}" class="id_clasificacion_ramo">
                                    <input type="hidden" id="u_m_clasificacion_ramo_{{$x+1}}" name="u_m_clasificacion_ramo_{{$x+1}}" class="u_m_clasificacion_ramo"
                                        value="{{$det_esp_emp->clasificacion_ramo->unidad_medida->id_unidad_medida}}">
                                </td>
                                <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;width:75px" class="text-center"
                                    rowspan="{{count($esp_emp->detalles)}}">
                                    <select id="empaque_{{$x+1}}" class="empaque_{{$x+1}} empaque caja_simple empaque text-center" name="empaque_{{$x+1}}"
                                        style="border:none;width:100%;height: 34px;" onchange="cuenta_ramos(this)"
                                        data-siglas_empaque="{{$esp_emp->empaque->siglas}}">
                                        <option value="{{$esp_emp->empaque->id_empaque}}">{{explode('|',$esp_emp->empaque->nombre)[0]}}</option>
                                    </select>
                                </td>
                                <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;width:65px"
                                    class="text-center td_presentacion_{{$x+1}} td_presentacion_{{$x+1}}_{{$b}} td_presentacion">
                                    <span>{{$det_esp_emp->empaque_p->nombre}}</span>
                                    <input type="hidden" id="input_presentacion_{{$x+1}}_{{$b}}" name="input_presentacion_{{$x+1}}_{{$b}}"
                                        value="{{$det_esp_emp->empaque_p->nombre}}" class="input_presentacion_{{$x+1}}">
                                </td>
                                <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;width:40px"
                                    class="text-center ramos_x_caja_{{$x+1}} ramos_x_caja_{{$x+1}}_{{$b}} td_ramos_x_caja"
                                    data-ramos_x_caja="{{$det_esp_emp->cantidad}}"
                                    data-id_det_esp_emp="{{$det_esp_emp->id_detalle_especificacionempaque}}">
                                    <input type="number" min="0" id="ramos_x_caja_{{$x+1}}_{{$b}}"
                                        value="{{$det_esp_emp->cantidad}}" style="width: 100%;padding-left: 1px;padding-right: 1px;text-align: center;"
                                        onchange="calcular_precio_pedido()" onkeyup="calcular_precio_pedido()"
                                        class="input_ramos_x_caja text-center form-control">
                                </td>
                                <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;width:40px"
                                    class="td_tallos_x_malla td_tallos_x_malla_{{$x+1}} td_tallos_x_malla_{{$x+1}}_{{$b}}
                                    {{(isset($det_ped->cliente_especificacion->especificacion->tipo) && $det_ped->cliente_especificacion->especificacion->tipo == "O") ? "" : "hide"}}">
                                    <input type="number" min="0" id="tallos_x_malla_{{$x+1}}_{{$b}}" name="tallos_x_malla_{{$x+1}}_{{$b}}"
                                        class="text-center tallos_x_malla_{{$x+1}} tallos_x_malla_{{$x+1}}_{{$b}}" value="0"
                                        onchange="calcular_precio_pedido(this)" style="border: none;width: 100%;height: 34px;">
                                    <input type="hidden" id="tallos_x_caja_{{$x+1}}_{{$b}}" name="tallos_x_caja_{{$x+1}}_{{$b}}"
                                        class="text-center tallos_x_caja_{{$x+1}} tallos_x_caja_{{$x+1}}_{{$b}}">
                                </td>

                                    <td style="border-color: #9d9d9d; padding: 0px; vertical-align: middle; width: 45px;"
                                        class="text-center td_total_ramos" rowspan="{{getCantidadDetallesByEspecificacion($item->id_especificacion)}}">
                                        0
                                    </td>

                                <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;width:35px"
                                    class="text-center td_tallos_x_ramo_{{$x+1}}_{{$b}} td_tallos_x_ramo_{{$x+1}} td_tallos_x_ramo_producto">
                                    <span>{{$det_esp_emp->tallos_x_ramos}}</span>
                                    <input id="tallos_x_ramo_{{$x+1}}_{{$b}}" name="tallos_x_ramo_{{$x+1}}_{{$b}}"
                                        type="hidden" value="{{$det_esp_emp->tallos_x_ramos}}"
                                        class="tallos_x_ramo_{{$x+1}}_{{$b}} tallos_x_ramo_{{$x+1}}">
                                </td>
                                <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;width:35px"
                                    class="text-center td_tallos_total_{{$x+1}}_{{$b}} td_tallos_total_{{$x+1}} total_tallos_producto">
                                </td>
                                <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;width:35px" class="text-center">
                                    @if($det_esp_emp->longitud_ramo != '' && $det_esp_emp->id_unidad_medida != '')
                                        {{$det_esp_emp->longitud_ramo}}{{$det_esp_emp->unidad_medida->siglas}}
                                        <input type="hidden" id="longitud_ramo_{{$x+1}}_{{$b}}" name="" class="longitud_ramo_{{$x+1}} longitud_ramo"
                                            value="{{$det_esp_emp->longitud_ramo}}">
                                        <input type="hidden" id="u_m_longitud_ramo_{{$x+1}}_{{$b}}" name="" class="u_m_longitud_ramo_{{$x+1}} u_m_longitud_ramo"
                                            value="{{$det_esp_emp->unidad_medida->id_unidad_medida}}">
                                    @endif
                                </td>
                                <td id="td_precio_variedad_{{$det_esp_emp->id_detalle_especificacionempaque}}_{{($x+1)}}"
                                    style="border-color: #9d9d9d;padding: 0px 0px; vertical-align: middle;width:60px">
                                    @if((getPrecioByClienteDetEspEmp($item->id_cliente, $det_esp_emp->id_detalle_especificacionempaque) != ''))
                                        <select name="precio_{{$det_esp_emp->id_detalle_especificacionempaque}}"
                                            ondblclick="cambiar_input_precio('{{$det_esp_emp->id_detalle_especificacionempaque}}','{{($x+1)}}','{{$b}}')"
                                            id="precio_{{($x+1)}}_{{$b}}" style="background-color: beige; width: 100%;height: 34px;"
                                            onchange="calcular_precio_pedido()" onkeyup="calcular_precio_pedido()"
                                            class="precio_{{($x+1)}} form-control" required>
                                            @foreach(explode('|',getPrecioByClienteDetEspEmp($item->id_cliente, $det_esp_emp->id_detalle_especificacionempaque)->cantidad) as $precio)
                                                <option value="{{$precio}}">{{$precio}}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="number" min="0" name="precio_{{($x+1)}}" class="form-control text-center precio_x_ramo"
                                            style="background-color: beige; width: 100%;text-align: center;padding-left: 1px;padding-right: 1px" min="0"
                                            onchange="calcular_precio_pedido(); set_precio_simple(this)" onkeyup="calcular_precio_pedido(); set_precio_simple(this)" value="0" required>
                                    @endif
                                </td>

                                <td id="td_precio_especificacion_{{($x+1)}}"
                                    style="border-color: #9d9d9d;padding: 0px 0px; vertical-align: middle;width:70px"
                                    class="text-center td_precio_total text-center"
                                    rowspan="{{getCantidadDetallesByEspecificacion($item->id_especificacion)}}"></td>
                                <td class="text-center agencia_carga" style="border-color: #9d9d9d; vertical-align: middle;width:75px"
                                    rowspan="{{getCantidadDetallesByEspecificacion($item->id_especificacion)}}">
                                    <select name="id_agencia_carga_{{$item->id_especificacion}}" id="id_agencia_carga_{{$x+1}}"
                                            class="text-center agencia_carga_simple" style="border: none; width:100%;height: 34px;" required>
                                        @foreach($agenciasCarga as $agencia)
                                            <option {{$agenciasCargaCliente->id_agencia_carga == $agencia->id_agencia_carga ? 'selected' : ''}} value="{{$agencia->id_agencia_carga}}">{{$agencia->nombre}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                @foreach($datos_exportacion as $de)
                                    <td class="{{( isset($id_pedido) || in_array('th_datos_exportacion_'.strtoupper($de->nombre) , $th_datos_exportacion) ) ? '' : 'hide'}}" rowspan="{{getCantidadDetallesByEspecificacion($item->id_especificacion)}}"
                                        style="border-color: #9d9d9d; vertical-align: middle">
                                        <input type="text" name="input_{{strtoupper($de->nombre)}}_{{$x+1}}"
                                            id="input_{{strtoupper($de->nombre)}}_{{$x+1}}" class="input_{{strtoupper($de->nombre)}}" style="border: none;width:100%;height:34px"
                                            onchange="set_marcacion_simple(this)"
                                            onkeyup="set_marcacion_simple(this)">
                                        <input type="hidden" name="id_dato_exportacion_{{strtoupper($de->nombre)}}_{{$x+1}}"
                                            class="id_dato_exportacion_{{strtoupper($de->nombre)}}" value="{{$de->id_dato_exportacion}}">
                                    </td>
                                @endforeach
                                <td class="text-center td_btn_option" style="border-color: #9d9d9d; vertical-align: middle"
                                    rowspan="{{getCantidadDetallesByEspecificacion($item->id_especificacion)}}" >
                                    <button type="button" class="btn btn-xs btn-success selecciona_especificacion" title="Agregar al pedido"
                                            onclick="agregar_fila_simple(this)">
                                        <i class="fa fa-fw fa-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-xs btn-primary duplica_especificacion" title="Duplicar fila"
                                            onclick="duplicar_especificacion_simple(this,'{{$item->id_cliente_pedido_especificacion}}')">
                                        <i class="fa fa-fw fa-clone"></i>
                                    </button>
                                    <small style="color: white;display:none">{{$item->id_especificacion}}</small>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                @endforeach
                {{-- <tr>
                    <td colspan="30" class="text-center">

                    </td>
                </tr> --}}
            @else
                <tr id="">
                    <td colspan="200">
                        <div class="alert alert-warning text-center">
                            <p style="font-size: 11pt;"> Este usuario no posee especificaciones asignadas </p>
                        </div>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
    <div class="col-md-12 text-center" style="margin: 15px 0px">
        <button type="button" class="btn btn-yura_primary" onclick="agregar_productos_simples()">
            <i class="fa fa-plus" aria-hidden="true"></i> Agregar productos al pedido
        </button>
    </div>
</div>

<div id="combos" class="collapse">
    @php
        $id1 = rand(10000, 200000);
        $id2 = rand(30000, 200000);
        $id3 = rand(20000, 200000);
    @endphp
    <table width="100%" class="table-responsive table-bordered" style="font-size: 0.8em; border-color: white" id="table_content_recepciones">
        <thead>
            <tr style="background-color: #dd4b39; color: white">
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width: 30px">
                    PIEZAS
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:230px">
                    VARIEDAD
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:129px">
                    COLOR
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:70px">
                    PESO
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:75px">
                    CAJA
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:65px">
                    PRESENTACIÓN
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:180px">
                    R. X CAJA
                </th>
                <th class="text-center hide th_tallo_x_malla table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width:45px">
                    TALLOS X MALLA
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d">
                    TOTAL RAMOS
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:35px">
                    T. X RAMO
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:35px">
                    TOTAL TALLOS
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:35px">
                    LONGITUD
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width:60px">
                    PRECIO
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width:70px">
                    TOTAL VARIEDAD
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width:75px">
                    CARGUERA
                </th>
                @foreach($datos_exportacion as $key => $de)
                    <th class="th_datos_exportacion th_datos_exportacion_{{strtoupper($de->nombre)}} text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}
                        {{( isset($id_pedido) || in_array('th_datos_exportacion_'.strtoupper($de->nombre) , $th_datos_exportacion) ) ? '' : 'hide'}}"
                        id="th_datos_exportacion_{{$key+1}}" style="border-color: #9d9d9d;width: 80px;">
                        {{strtoupper($de->nombre)}}
                    </th>
                @endforeach
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width:100px;width: 20px;">
                    OPCIONES
                </th>
            </tr>
        </thead>
        <tbody id="tbody_inputs_pedidos" class="tbody_inputs_pedidos_combos tbody_inputs_pedidos_combos_1">
            <tr id="tr_nueva_especificacion_1">
                <td style="border-color: #9d9d9d;width:120px" class="td_piezas_combo">
                    <input type="number" min="0" id="cantidad_piezas_1" style="width:100%;height: 34px;"
                    onkeyup="calcular_precio_pedido(this,1)" onchange="calcular_precio_pedido(this,1)"
                    name="cantidad_piezas" class="text-center cantidad_1 input_cantidad input_cantidad_{{$id1}}" value=""
                    data-id_tr_producto="{{$id1}}">
                </td>
                <td style="border-color: #9d9d9d;width:190px">
                    <select style="width:190px" name="id_planta" class="form-control planta_combo" onchange="seleccionar_variedad_combo(this)"></select>
                </td>
                <td style="border-color: #9d9d9d;width:130px" class="td_variedad td_variedad_{{$id1}}">
                    <select style="width:100px" name="id_variedad" class="form-control variedad_combo"
                    onchange="asigna_variedad(this); set_sigla_empaque(this,true);"></select>
                </td>
                <td style="border-color: #9d9d9d" class="td_calibre td_calibre_{{$id1}}">
                    <select style="width: 70px" name="id_clasificacion_ramo" class="form-control clasificacion_combo"></select>
                </td>
                <td style="border-color: #9d9d9d" class="td_caja_combo">
                    <select style="width: 180px" name="id_empaque" class="form-control caja_combo empaque empaque_{{$id1}}"
                        onchange="set_sigla_empaque(this);" data-siglas_empaque="{{$empaque[0]->siglas}}"></select>
                </td>
                <td style="border-color: #9d9d9d">
                    <select style="width: 200px" name="id_presentacion" class="form-control presentacion_combo"></select>
                </td>
                <td style="border-color: #9d9d9d;width:450px">
                    <input type="number" min="0" class="form-control input_ramos_x_caja input_ramos_x_caja_{{$id1}}"
                        style="width: 100%;padding-left: 1px;padding-right: 1px;text-align: center;"
                        value="1" name="ramo_x_caja" onchange="calcular_precio_pedido(this,1)"
                        onkeyup="calcular_precio_pedido(this,1)" data-id_tr_producto="{{$id1}}" required>
                </td>
                <td style="border-color: #9d9d9d;width:200px" class="td_total_ramos td_total_ramos_{{$id1}} text-center"></td>
                <td style="border-color: #9d9d9d; width: 600px" class="td_tallos_x_ramo_producto">
                    <span class="hide" id="span_tallos_x_ramo"></span>
                    <input type="number" min="0"  style="width: 100%;padding-left: 1px;padding-right: 1px;"
                        name="tallos_x_ramo" class="form-control text-center tallos_x_ramo_{{$id1}} input_tallos_x_ramo"
                        onchange="set_span_tallos_x_ramo(this); calcular_precio_pedido(this,1)"
                        onkeyup="calcular_precio_pedido(this,1); set_span_tallos_x_ramo(this)" data-id_tr_producto="{{$id1}}">
                </td>
                <td style="border-color: #9d9d9d;width:200px" class="text-center total_tallos_producto total_tallos_producto_{{$id1}}">
                </td>
                <td style="border-color: #9d9d9d; width: 100px">
                    <input type="text"  style="width: 100%;padding-left: 1px;padding-right: 1px;text-align: center" name="longitud" class="form-control">
                </td>
                <td style="border-color: #9d9d9d; width: 400px">
                    <input type="number" min="0" name="precio" class="form-control text-center precio_x_ramo precio_x_ramo_1 precio_x_ramo_1_1 precio_x_ramo_{{$id1}}"
                        style="background-color: beige; width: 100%;text-align: center;padding-left: 1px;padding-right: 1px;" min="0" value="0" required=""
                        onchange="calcular_precio_pedido(this,1); set_precio_combo(this)" onkeyup="calcular_precio_pedido(this,1); set_precio_combo(this)">
                </td>
                <td style="border-color: #9d9d9d; width: 80px" class="td_precio_total td_precio_total_{{$id1}} text-center"></td>
                <td style="border-color: #9d9d9d; width: 100px" class="agencia_carga">
                    <select name="id_agencia_carga" class="text-center agencia_carga_combo" style="border: none; width:100%;height: 34px;">
                        @foreach($agenciasCarga as $agencia)
                            <option {{$agenciasCargaCliente->id_agencia_carga == $agencia->id_agencia_carga ? 'selected' : ''}} value="{{$agencia->id_agencia_carga}}">{{$agencia->nombre}}</option>
                        @endforeach
                    </select>
                </td>
                @foreach($datos_exportacion as $key => $de)
                    <td style="border-color: #9d9d9d; width: 900px" class="marcacion_combo_{{$de->nombre}} td_marcacion_combo_1
                        {{( isset($id_pedido) || in_array('th_datos_exportacion_'.strtoupper($de->nombre) , $th_datos_exportacion) ) ? '' : 'hide'}}" data-marcacion="{{$de->nombre}}">
                        <input type="text" style="width: 100%; padding-left:1px;padding-right:1px" name="maracion_combo" class="form-control input_{{$de->nombre}}">
                        <input type="hidden" name="id_dato_exportacion_{{strtoupper($de->nombre)}}"
                            class="id_dato_exportacion_{{strtoupper($de->nombre)}}" value="{{$de->id_dato_exportacion}}">
                    </td>
                @endforeach
                <td style="border-color: #9d9d9d" class="text-center">
                    <button type="button" class="btn btn-yura_primary" id="btn_fila_combo_1" title="Crear fila"
                            onclick="agregar_fila_combo(this, 1)">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
    <div>
        <span></span>
        <div id="div_error_cajas_1" class="text-danger"></div>
    </div>
    <div class="col-md-12 text-center" style="margin: 15px 0px">
        <button type="button" class="btn btn-yura_primary" onclick="agregar_combo(1)">
            <i class="fa fa-plus" aria-hidden="true"></i> Agregar Combo al pedido
        </button>
    </div>

    <table width="100%" class="table-responsive table-bordered mt-3" style="font-size: 0.8em; border-color: white" id="table_content_recepciones">
        <thead>
            <tr style="background-color: #dd4b39; color: white">
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width: 30px">
                    PIEZAS
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:230px">
                    VARIEDAD
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:129px">
                    COLOR
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:70px">
                    PESO
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:75px">
                    CAJA
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:65px">
                    PRESENTACIÓN
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:180px">
                    R. X CAJA
                </th>
                <th class="text-center hide th_tallo_x_malla table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width:45px">
                    TALLOS X MALLA
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d">
                    TOTAL RAMOS
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:35px">
                    T. X RAMO
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:35px">
                    TOTAL TALLOS
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:35px">
                    LONGITUD
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width:60px">
                    PRECIO
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width:70px">
                    TOTAL VARIEDAD
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width:75px">
                    CARGUERA
                </th>
                @foreach($datos_exportacion as $key => $de)
                    <th class="th_datos_exportacion th_datos_exportacion_{{strtoupper($de->nombre)}} text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}
                        {{( isset($id_pedido) || in_array('th_datos_exportacion_'.strtoupper($de->nombre) , $th_datos_exportacion) ) ? '' : 'hide'}}"
                        id="th_datos_exportacion_{{$key+1}}" style="border-color: #9d9d9d;width: 80px;">
                        {{strtoupper($de->nombre)}}
                    </th>
                @endforeach
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width:100px;width: 20px;">
                    OPCIONES
                </th>
            </tr>
        </thead>
        <tbody id="tbody_inputs_pedidos" class="tbody_inputs_pedidos_combos tbody_inputs_pedidos_combos_2">
            <tr id="tr_nueva_especificacion_1">
                <td style="border-color: #9d9d9d;width:120px" class="td_piezas_combo">
                    <input type="number" min="0" id="cantidad_piezas_1" style="width:100%;height: 34px;"
                    onkeyup="calcular_precio_pedido(this,2)" onchange="calcular_precio_pedido(this,2)"
                    name="cantidad_piezas" class="text-center cantidad_1 input_cantidad input_cantidad_{{$id2}}" value=""
                    data-id_tr_producto="{{$id2}}">
                </td>
                <td style="border-color: #9d9d9d;width:190px">
                    <select style="width:190px" name="id_planta" class="form-control planta_combo" onchange="seleccionar_variedad_combo(this)"></select>
                </td>
                <td style="border-color: #9d9d9d;width:130px" class="td_variedad td_variedad_{{$id2}}">
                    <select style="width:100px" name="id_variedad" class="form-control variedad_combo"
                    onchange="asigna_variedad(this); set_sigla_empaque(this,true);"></select>
                </td>
                <td style="border-color: #9d9d9d" class="td_calibre td_calibre_{{$id2}}">
                    <select style="width: 70px" name="id_clasificacion_ramo" class="form-control clasificacion_combo"></select>
                </td>
                <td style="border-color: #9d9d9d" class="td_caja_combo">
                    <select style="width: 180px" name="id_empaque" class="form-control caja_combo empaque empaque_{{$id2}}"
                        onchange="set_sigla_empaque(this);" data-siglas_empaque="{{$empaque[0]->siglas}}"></select>
                </td>
                <td style="border-color: #9d9d9d">
                    <select style="width: 200px" name="id_presentacion" class="form-control presentacion_combo"></select>
                </td>
                <td style="border-color: #9d9d9d;width:450px">
                    <input type="number" min="0" class="form-control input_ramos_x_caja input_ramos_x_caja_{{$id2}}"
                        style="width: 100%;padding-left: 1px;padding-right: 1px;text-align: center"
                        value="1" name="ramo_x_caja" onchange="calcular_precio_pedido(this,2)"
                        data-id_tr_producto="{{$id2}}" onkeyup="calcular_precio_pedido(this,2)" required>
                </td>
                <td style="border-color: #9d9d9d;width:200px" class="td_total_ramos td_total_ramos_{{$id2}} text-center"></td>
                <td style="border-color: #9d9d9d; width: 600px" class="td_tallos_x_ramo_producto">
                    <span class="hide" id="span_tallos_x_ramo"></span>
                    <input type="number" min="0"  style="width: 100%;padding-left: 1px;padding-right: 1px;"
                        name="tallos_x_ramo" class="form-control text-center tallos_x_ramo_{{$id2}} input_tallos_x_ramo"
                        onchange="set_span_tallos_x_ramo(this); calcular_precio_pedido(this,2)"
                        onkeyup="calcular_precio_pedido(this,2); set_span_tallos_x_ramo(this)" data-id_tr_producto="{{$id2}}">
                </td>
                <td style="border-color: #9d9d9d;width:200px" class="text-center total_tallos_producto total_tallos_producto_{{$id2}}">
                </td>
                <td style="border-color: #9d9d9d; width: 100px">
                    <input type="text"  style="width: 100%;padding-left: 1px;padding-right: 1px;text-align: center" name="longitud" class="form-control">
                </td>
                <td style="border-color: #9d9d9d; width: 400px">
                    <input type="number" min="0" name="precio" class="form-control text-center precio_x_ramo precio_x_ramo_2 precio_x_ramo_2_1 precio_x_ramo_{{$id2}}"
                        style="background-color: beige; width: 100%;text-align: center;padding-left: 1px;padding-right: 1px;"
                        min="0" onchange="calcular_precio_pedido(this,2); set_precio_combo(this)" onkeyup="calcular_precio_pedido(this,2); set_precio_combo(this)" value="0" required="">
                </td>
                <td style="border-color: #9d9d9d; width: 80px" class="td_precio_total td_precio_total_{{$id2}} text-center"></td>
                <td style="border-color: #9d9d9d; width: 100px" class="agencia_carga">
                    <select name="id_agencia_carga" class="text-center agencia_carga_combo" style="border: none; width:100%;height: 34px;">
                        @foreach($agenciasCarga as $agencia)
                            <option {{$agenciasCargaCliente->id_agencia_carga == $agencia->id_agencia_carga ? 'selected' : ''}} value="{{$agencia->id_agencia_carga}}">{{$agencia->nombre}}</option>
                        @endforeach
                    </select>
                </td>
                @foreach($datos_exportacion as $key => $de)
                    <td style="border-color: #9d9d9d; width: 900px" class="marcacion_combo_{{$de->nombre}} td_marcacion_combo_2
                        {{( isset($id_pedido) || in_array('th_datos_exportacion_'.strtoupper($de->nombre) , $th_datos_exportacion) ) ? '' : 'hide'}}" data-marcacion="{{$de->nombre}}">
                        <input type="text" style="width: 100%; padding-left:1px;padding-right:1px" name="maracion_combo" class="form-control input_{{$de->nombre}}">
                        <input type="hidden" name="id_dato_exportacion_{{strtoupper($de->nombre)}}"
                            class="id_dato_exportacion_{{strtoupper($de->nombre)}}" value="{{$de->id_dato_exportacion}}">
                    </td>
                @endforeach
                <td style="border-color: #9d9d9d" class="text-center">
                    <button type="button" class="btn btn-yura_primary" id="btn_fila_combo_1" title="Crear fila"
                            onclick="agregar_fila_combo(this, 2)">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
    <div>
        <span></span>
        <div id="div_error_cajas_2" class="text-danger"></div>
    </div>
    <div class="col-md-12 text-center" style="margin: 15px 0px">
        <button type="button" class="btn btn-yura_primary" onclick="agregar_combo(2)">
            <i class="fa fa-plus" aria-hidden="true"></i> Agregar Combo al pedido
        </button>
    </div>

    <table width="100%" class="table-responsive table-bordered mt-3" style="font-size: 0.8em; border-color: white" id="table_content_recepciones">
        <thead>
            <tr style="background-color: #dd4b39; color: white">
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width: 30px">
                    PIEZAS
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:230px">
                    VARIEDAD
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:129px">
                    COLOR
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:70px">
                    PESO
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:75px">
                    CAJA
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:65px">
                    PRESENTACIÓN
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:180px">
                    R. X CAJA
                </th>
                <th class="text-center hide th_tallo_x_malla table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width:45px">
                    TALLOS X MALLA
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d">
                    TOTAL RAMOS
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:35px">
                    T. X RAMO
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:35px">
                    TOTAL TALLOS
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d;width:35px">
                    LONGITUD
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width:60px">
                    PRECIO
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width:70px">
                    TOTAL VARIEDAD
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width:75px">
                    CARGUERA
                </th>
                @foreach($datos_exportacion as $key => $de)
                    <th class="th_datos_exportacion th_datos_exportacion_{{strtoupper($de->nombre)}} text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}
                        {{( isset($id_pedido) || in_array('th_datos_exportacion_'.strtoupper($de->nombre) , $th_datos_exportacion) ) ? '' : 'hide'}}"
                        id="th_datos_exportacion_{{$key+1}}" style="border-color: #9d9d9d;width: 80px;">
                        {{strtoupper($de->nombre)}}
                    </th>
                @endforeach
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                    style="border-color: #9d9d9d;width:100px;width: 20px;">
                    OPCIONES
                </th>
            </tr>
        </thead>
        <tbody id="tbody_inputs_pedidos" class="tbody_inputs_pedidos_combos tbody_inputs_pedidos_combos_3">
            <tr id="tr_nueva_especificacion_1">
                <td style="border-color: #9d9d9d;width:120px" class="td_piezas_combo">
                    <input type="number" min="0" id="cantidad_piezas_1" style="width:100%;height: 34px;"
                    onkeyup="calcular_precio_pedido(this,3)" onchange="calcular_precio_pedido(this,3)"
                    name="cantidad_piezas" class="text-center cantidad_1 input_cantidad input_cantidad_{{$id3}}" value=""
                    data-id_tr_producto="{{$id3}}">
                </td>
                <td style="border-color: #9d9d9d;width:190px">
                    <select style="width:190px" name="id_planta" class="form-control planta_combo" onchange="seleccionar_variedad_combo(this)"></select>
                </td>
                <td style="border-color: #9d9d9d;width:130px" class="td_variedad td_variedad_{{$id3}}">
                    <select style="width:100px" name="id_variedad" class="form-control variedad_combo"
                    onchange="asigna_variedad(this); set_sigla_empaque(this,true);"></select>
                </td>
                <td style="border-color: #9d9d9d" class="td_calibre td_calibre_{{$id3}}">
                    <select style="width: 70px" name="id_clasificacion_ramo" class="form-control clasificacion_combo"></select>
                </td>
                <td style="border-color: #9d9d9d" class="td_caja_combo">
                    <select style="width: 180px" name="id_empaque" class="form-control caja_combo empaque empaque_{{$id3}}"
                        onchange="set_sigla_empaque(this);" data-siglas_empaque="{{$empaque[0]->siglas}}"></select>
                </td>
                <td style="border-color: #9d9d9d">
                    <select style="width: 200px" name="id_presentacion" class="form-control presentacion_combo"></select>
                </td>
                <td style="border-color: #9d9d9d;width:450px">
                    <input type="number" min="0" class="form-control input_ramos_x_caja input_ramos_x_caja_{{$id3}}"
                        style="width: 100%;padding-left: 1px;padding-right: 1px;text-align: center"
                        value="1" name="ramo_x_caja" onchange="calcular_precio_pedido(this,3)"
                        data-id_tr_producto="{{$id3}}" onkeyup="calcular_precio_pedido(this,3)" required>
                </td>
                <td style="border-color: #9d9d9d;width:200px" class="td_total_ramos td_total_ramos_{{$id3}} text-center"></td>
                <td style="border-color: #9d9d9d; width: 600px" class="td_tallos_x_ramo_producto">
                    <span class="hide" id="span_tallos_x_ramo"></span>
                    <input type="number" min="0"  style="width: 100%;padding-left: 1px;padding-right: 1px;"
                        name="tallos_x_ramo" class="form-control text-center tallos_x_ramo_{{$id3}} input_tallos_x_ramo"
                        onchange="set_span_tallos_x_ramo(this); calcular_precio_pedido(this,3)"
                        onkeyup="calcular_precio_pedido(this,3); set_span_tallos_x_ramo(this)" data-id_tr_producto="{{$id3}}">
                </td>
                <td style="border-color: #9d9d9d;width:200px" class="text-center total_tallos_producto total_tallos_producto_{{$id3}}">
                </td>
                <td style="border-color: #9d9d9d; width: 100px">
                    <input type="text"  style="width: 100%;padding-left: 1px;padding-right: 1px;text-align: center" name="longitud" class="form-control">
                </td>
                <td style="border-color: #9d9d9d; width: 400px">
                    <input type="number" min="0" name="precio" class="form-control text-center precio_x_ramo precio_x_ramo_3 precio_x_ramo_3_1 precio_x_ramo_{{$id3}}"
                        style="background-color: beige; width: 100%;text-align: center;padding-left: 1px;padding-right: 1px;"
                        min="0" onchange="calcular_precio_pedido(this,3); set_precio_combo(this)" onkeyup="calcular_precio_pedido(this,3); set_precio_combo(this)" value="0" required="">
                </td>
                <td style="border-color: #9d9d9d; width: 80px" class="td_precio_total td_precio_total_{{$id3}} text-center"></td>
                <td style="border-color: #9d9d9d; width: 100px" class="agencia_carga">
                    <select name="id_agencia_carga" class="text-center agencia_carga_combo" style="border: none; width:100%;height: 34px;">
                        @foreach($agenciasCarga as $agencia)
                            <option {{$agenciasCargaCliente->id_agencia_carga == $agencia->id_agencia_carga ? 'selected' : ''}} value="{{$agencia->id_agencia_carga}}">{{$agencia->nombre}}</option>
                        @endforeach
                    </select>
                </td>
                @foreach($datos_exportacion as $key => $de)
                    <td style="border-color: #9d9d9d; width: 900px" class="marcacion_combo_{{$de->nombre}} td_marcacion_combo_3
                        {{( isset($id_pedido) || in_array('th_datos_exportacion_'.strtoupper($de->nombre) , $th_datos_exportacion) ) ? '' : 'hide'}}" data-marcacion="{{$de->nombre}}">
                        <input type="text" style="width: 100%; padding-left:1px;padding-right:1px"
                                name="maracion_combo" class="form-control input_{{$de->nombre}}">
                        <input type="hidden" name="id_dato_exportacion_{{strtoupper($de->nombre)}}"
                            class="id_dato_exportacion_{{strtoupper($de->nombre)}}" value="{{$de->id_dato_exportacion}}">
                    </td>
                @endforeach
                <td style="border-color: #9d9d9d" class="text-center">
                    <button type="button" class="btn btn-yura_primary" id="btn_fila_combo_1" title="Crear fila"
                            onclick="agregar_fila_combo(this,3)">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
    <div>
        <span></span>
        <div id="div_error_cajas_3" class="text-danger"></div>
    </div>
    <div class="col-md-12 text-center" style="margin: 15px 0px">
        <button type="button" class="btn btn-yura_primary" onclick="agregar_combo(3)">
            <i class="fa fa-plus" aria-hidden="true"></i> Agregar Combo al pedido
        </button>
    </div>
</div>

<script>

    seletcs2 = "select.planta_combo, select.caja_combo, select.presentacion_combo, select.variedad_combo, select.clasificacion_combo, select.agencia_carga_combo, select.agencia_carga_simple, select.caja_simple, select.select_id_empaque";

    function agregar_fila_simple(input,calcular_precio =true){

        $(seletcs2).select2("destroy")

        let id_random = parseInt(Math.random()* 100000)

        let tr_producto =  $(input).parent().parent().clone()

        let tr_prodcuto_dom = $(input).parent().parent()

        if(tr_prodcuto_dom.find('select.select_id_empaque').length){

            let input_txr = tr_prodcuto_dom.find('input.input_tallos_x_ramos').val()
            let cant_tr = tr_prodcuto_dom.find('td').length

            let html_td_tallos_x_ramo = `

                <span>${input_txr}</span>
                <input id="tallos_x_ramo_${cant_tr+1}_1" name="tallos_x_ramo_${cant_tr+1}_1" type="hidden" value="${input_txr}" class="tallos_x_ramo_${cant_tr+1}_1 tallos_x_ramo_${cant_tr+1}">

            `

            tr_producto.find('td.td_presentacion').html(tr_prodcuto_dom.find('select.select_id_empaque option:selected').text()).end()
            .find('td.td_tallos_x_ramo_producto').html(html_td_tallos_x_ramo)


        }

        $("#alert_sin_productos").addClass('hide')

        let btn_remove_seleccion = `
            <button type="button" class="btn btn-yura_danger btn-sm btn-remove-seleccion"
                title="Eliminar del pedido" onclick="eliminar_producto_pedido(this)">
                <i class="fa fa-trash"></i>
            </button>
        `

        let caja = tr_prodcuto_dom.find('select.caja_simple option:selected').val()
        let agencia_carga = tr_prodcuto_dom.find('select.agencia_carga_simple option:selected').val()

        let html = tr_producto.find(`
            select.caja_simple option[value='${caja}'],
            select.agencia_carga_simple option[value='${agencia_carga}']
        `).attr('selected',true).end()
        .find('input.input_cantidad ').attr('data-id_tr_producto',id_random).attr('min',1).end()
        .find('input.input_ramos_x_caja').addClass('input_ramos_x_caja_'+id_random).attr('min',1).end()
        .find('input.precio_x_ramo').addClass('precio_x_ramo_'+id_random).attr('min','0.1').end()
        .find('td.td_precio_total').addClass('td_precio_total_'+id_random).end()
        .find('td.td_variedad').addClass('td_variedad_'+id_random).end()
        .find('td.td_calibre').addClass('td_calibre_'+id_random).end()
        .find('select.empaque').addClass('empaque_'+id_random).end()
        .find('td.td_total_ramos').addClass('td_total_ramos_'+id_random).end()
        .find('button.selecciona_especificacion').remove().end()
        .find('button.btn_eliminar_especificacion_simple_duplicada').remove().end()
        .find('button.duplica_especificacion').remove().end()
        .find('td.td_btn_option').append(btn_remove_seleccion).end()

        $("#tbody_productos_seleccionados").append(html)

        $(seletcs2).select2({ dropdownParent: $('#div_modal-modal_add_pedido') })

        if(calcular_precio){
            calcular_precio_pedido()
            mini_alerta('success', 'Producto agregado', 5000)
        }

    }

    function eliminar_producto_pedido(btn,id_det_ped=''){

        if(id_det_ped!=''){

            eliminar_detalle_pedido(id_det_ped)
            .then(res =>{
                console.log(res)
                $(btn).parent().parent().remove()

                if($("#tbody_productos_seleccionados tr").length ==1)
                    $("#alert_sin_productos").removeClass('hide')

                listar_resumen_pedidos($('#fecha_pedidos_search').val(), true)

                if(res.cant_det_ped== 1)
                    $(".modal").modal('hide')

                calcular_precio_pedido()

            })

        }else{

            $(btn).parent().parent().remove()

            if($("#tbody_productos_seleccionados tr").length ==1)
                $("#alert_sin_productos").removeClass('hide')

        }

    }

    function duplicar_especificacion_simple(input,id_cliente_pedido_especificacion){

        $(seletcs2).select2("destroy")

        let tr_especificacion = $(input).parent().parent().clone()
        let presentaciones = JSON.parse($("#presentaciones").val())
        let html_tallos_x_ramos =tr_especificacion.find('td.td_tallos_x_ramo_producto span').html()

        let datos = {
            _token: '{{@csrf_token()}}',
            id_cliente: $("#id_cliente_venta").val(),
            modo:0,
            arrData: [
                {
                    id_variedad: tr_especificacion.find('td.td_variedad').data('id_variedad'),
                    id_empaque: tr_especificacion.find('select.caja_simple').val(),
                    id_clasificacion_ramo_: tr_especificacion.find('input.id_clasificacion_ramo').val(),
                    ramos_x_caja: tr_especificacion.find('input.input_ramos_x_caja').val(),
                    id_presentacion: presentaciones[0].id_empaque,
                    tallos_x_ramo:  typeof html_tallos_x_ramos != 'undefined' ? html_tallos_x_ramos.trim() : tr_especificacion.find('td.td_tallos_x_ramo_producto input').val(),
                    longitud: tr_especificacion.find('input.longitud_ramo').val(),
                    id_unidad_medida: tr_especificacion.find('input.u_m_longitud_ramo').val(),

                }
            ]
        }

        let btn_remove_duplicado = `
            <button type="button" class="btn btn-danger btn-xs btn_eliminar_especificacion_simple_duplicada"
                    style="padding: 1px 8px;" title="Eliminar" onclick="eliminar_especificacion_simple_duplicada(this)">
                <i class="fa fa-trash"></i>
            </button>
        `

        let cant_tr = $("div#simple tbody#tbody_inputs_pedidos tr").length

        let option_presentaciones =``

        presentaciones.forEach((ele ,i ) => {
            option_presentaciones += `<option ${i == 0 ? 'selected' : ''} value="${ele.id_empaque}">${ele.nombre}</option>`
        })

        let input_tallos_x_ramo= `<input type='number' class='input_tallos_x_ramos form-control'
            style='background-color: beige;width: 100%;text-align: center;padding-left: 1px;padding-right: 1px;font-size: 11px'
            onchange="actualizar_especificacion_simple_duplicada(this)" onkeyup="actualizar_especificacion_simple_duplicada(this)"
            value='${datos.arrData[0].tallos_x_ramo}'>
        `

        tr_especificacion
        .find('td.td_presentacion').html(`<select class='select_id_empaque' onchange="actualizar_especificacion_simple_duplicada(this)">${option_presentaciones}</select>`).end()
        .find('td.td_tallos_x_ramo_producto').html(input_tallos_x_ramo).end()
        .find('input.input_cantidad').attr('id','cantidad_piezas_'+(cant_tr+1)).val('').end()
        .find('td.td_btn_option').append(btn_remove_duplicado).end()
        .find('button.duplica_especificacion').remove().end()
        .find('td.td_ramos_x_caja').find('input').addClass('new_input_especificacion_ramos_x_caja')

        post_jquery_m('especificacion/store_row_especificacion', datos, function (res) {

            if(res.success){

                tr_especificacion.find('td.td_piezas_simple input').attr('data-id_cliente_pedido_especificacion',res.resAsignacionEspecificacion.id_cliente_pedido_especificacion)
                tr_especificacion.find('td.td_calibre').attr('data-id_det_esp_emp',res.idDetEspEmp)
                tr_especificacion.find('td.td_ramos_x_caja').attr('data-id_det_esp_emp',res.idDetEspEmp)

                $("div#simple tbody#tbody_inputs_pedidos").append(tr_especificacion)
                $(seletcs2).select2({ dropdownParent: $('#div_modal-modal_add_pedido') })

                $("input.new_input_especificacion_ramos_x_caja").on('change keyup',function(){
                    actualizar_especificacion_simple_duplicada(this)
                })

            }

        },'simple')

    }

    function eliminar_especificacion_simple_duplicada(btn){

        let datos={
            _token: '{{@csrf_token()}}',
            id_cliente_pedido_especificacion: $(btn).parent().parent().find('td.td_piezas_simple').find('input').data('id_cliente_pedido_especificacion')
        }

        post_jquery_m('especificacion/delete_row_especificacion', datos, function (res) {

            $(btn).parent().parent().remove()

        },'simple')

    }

    function actualizar_especificacion_simple_duplicada(inpunt){

        let tr_especificacion = $(inpunt).parent().parent()

        let datos={
            _token: '{{@csrf_token()}}',
            id_presentacion: tr_especificacion.find('select.select_id_empaque').val(),
            ramos_x_caja: tr_especificacion.find('input.new_input_especificacion_ramos_x_caja').val(),
            tallos_x_ramo: tr_especificacion.find('input.input_tallos_x_ramos').val(),
            id_cliente_pedido_especificacion: tr_especificacion.find('td.td_piezas_simple').find('input').data('id_cliente_pedido_especificacion')
        }

        tr_especificacion.find('td.td_ramos_x_caja').attr('data-ramos_x_caja',datos.ramos_x_caja)

        if(datos.ramos_x_caja !='' && datos.ramos_x_caja > 0 && datos.tallos_x_ramo !='' && datos.tallos_x_ramo > 0){

            post_jquery_m('especificacion/actualizar_row_especificacion', datos, function (res) {

                console.log(res)

            },'simple')

        }

    }

    function collapse_pedido_simple(){
        $("button[data-target='#simple']").removeClass('btn-success').addClass('btn-info').css('heigth','0px')
        $("div#simple").removeClass('in')
        $("button[data-target='#combos']").removeClass('btn-info').addClass('btn-success')
    }

    function collapse_pedido_combo(){
        $("button[data-target='#combos']").css('heigth','0px').removeClass('btn-success').addClass('btn-info')
        $("div#combos").removeClass('in')
        $("button[data-target='#simple']").removeClass('btn-info').addClass('btn-success')
    }

    function agregar_fila_combo(element, caja){

        $(seletcs2).select2("destroy")

        let primer_tr = $("tbody.tbody_inputs_pedidos_combos_"+caja+" tr:first")

        let inputs_marcacion = []

        $.each($("td.td_marcacion_combo_"+caja),function(){
            inputs_marcacion.push('td.marcacion_combo_'+ $(this).data('marcacion'))
        })

        let tr_productos = primer_tr.clone()
        .find(`td.td_piezas_combo, td.td_total_ramos, td.td_precio_total, td.td_caja_combo, td.total_tallos_producto, td.agencia_carga ${ (inputs_marcacion.length ? (', '+inputs_marcacion.join(',')) : '' ) }`).remove().end()
        .find('input.precio_x_ramo').removeClass('precio_x_ramo_1_1').end()

        $("tbody.tbody_inputs_pedidos_combos_"+caja+"").append(tr_productos)

        let cant_rowspan = $("tbody.tbody_inputs_pedidos_combos_"+caja+" tr").length

        primer_tr.find(`td.td_piezas_combo, td.td_total_ramos, td.td_precio_total, td.total_tallos_producto, td.td_caja_combo, td.agencia_carga ${ (inputs_marcacion.length ? (', '+inputs_marcacion.join(',')) : '' ) }`)
        .attr('rowspan',cant_rowspan).end()

        let btn_delete_fila = `
            <button type="button" class="btn btn-yura_danger btn-sm btn-remove-fila"
                    style="margin-right: 5px;" title="Eliminar fila" onclick="eliminar_fila_combo(this,'${caja}')">
                <i class="fa fa-trash"></i>
            </button>
        `

        $("tbody.tbody_inputs_pedidos_combos_"+caja+" td:last").find('button').remove().end().append(btn_delete_fila)

        $(seletcs2).select2({ dropdownParent: $('#div_modal-modal_add_pedido') })

        calcular_precio_pedido($(element).parent().parent().find('input.input_ramos_x_caja')[0], caja)

    }

    function eliminar_fila_combo(element,n_caja){

        let el = $(element).parent().parent().parent()

        $(element).parent().parent().remove()

        if($("#tbody_productos_seleccionados tr").length ==1)
            $("#alert_sin_productos").removeClass('hide')

        let cant_rowspan = $("tbody.tbody_inputs_pedidos_combos_"+n_caja+" tr").length

        let primer_tr = $("tbody.tbody_inputs_pedidos_combos_"+n_caja+" tr:first")

        let inputs_marcacion = []

        $.each($("td.td_marcacion_combo_"+n_caja),function(){
            inputs_marcacion.push('td.marcacion_combo_'+ $(this).data('marcacion'))
        })

        primer_tr.find(`td.td_piezas_combo, td.td_total_ramos, td.total_tallos_producto, td.td_precio_total , td.td_caja_combo, td.agencia_carga ${ (inputs_marcacion.length ? (', '+inputs_marcacion.join(',')) : '' ) }`)
        .attr('rowspan',cant_rowspan).end()


        calcular_precio_pedido(el.find('input.input_ramos_x_caja')[0],n_caja)

    }

    function agregar_combo(n_caja){

        $(seletcs2).select2("destroy")

        let id_random = parseInt(Math.random()* 100000)

        let btn_delete_combo_pedido = `
            <button type="button" class="btn btn-yura_danger btn-sm btn-remove-fila"
                    style="margin-right: 5px;" title="Eliminar fila" onclick="eliminar_combo_pedido('${id_random}')">
                <i class="fa fa-trash"></i>
            </button>
        `

        let cant_rowspan = $("tbody.tbody_inputs_pedidos_combos_"+n_caja+" tr").length

        let tr_combo = $("tbody.tbody_inputs_pedidos_combos_"+n_caja+" tr").clone()

        $.each(tr_combo, function(i) {

            if(i > 0) $(this).find('td:last').remove().end()

            let tr_dom = $(`tbody.tbody_inputs_pedidos_combos_${n_caja} tr:nth-child(${i+1})`)

            let planta = tr_dom.find('select.planta_combo option:selected').val()
            let variedad = tr_dom.find('select.variedad_combo option:selected').val()
            let caja = tr_dom.find('select.caja_combo option:selected').val()
            let clasificacion = tr_dom.find('select.clasificacion_combo option:selected').val()
            let presentacion = tr_dom.find('select.presentacion_combo option:selected').val()
            let agencia_carga = tr_dom.find('select.agencia_carga_combo option:selected').val()

            $(this).find(`td.td_variedad`).attr('data-id_variedad',variedad)

            $(this).find(`
                select.planta_combo option[value='${planta}'],
                select.variedad_combo option[value='${variedad}'],
                select.caja_combo option[value='${caja}'],
                select.clasificacion_combo option[value='${clasificacion}'],
                select.agencia_carga_combo option[value='${agencia_carga}'],
                select.presentacion_combo option[value='${presentacion}']
            `).attr('selected',true)

        })

        tr_combo.find('input.input_cantidad, input.input_ramos_x_caja, select.caja_combo').attr('data-id_tr_producto',id_random).addClass('input_cantidad_'+id_random).end()
        .find('input.input_cantidad, input.input_ramos_x_caja').attr('min',1).end()
        .find('td.td_total_ramos').removeClass('td_total_ramos_'+{{$id1}}+' td_total_ramos_'+{{$id2}}+' td_total_ramos_'+{{$id3}}).addClass('td_total_ramos_'+id_random).end()
        .find('td.td_precio_total').removeClass('td_precio_total_'+{{$id1}}+' td_precio_total_'+{{$id2}}+' td_precio_total_'+{{$id3}}).addClass('td_precio_total_'+id_random).end()
        .find('td.td_variedad').removeClass('td_variedad_'+{{$id1}}+' td_variedad_'+{{$id2}}+' td_variedad_'+{{$id3}}).addClass('td_variedad_'+id_random).end()
        .find('td.td_calibre').removeClass('td_calibre_'+{{$id1}}+' td_calibre_'+{{$id2}}+' td_calibre_'+{{$id3}}).addClass('td_calibre_'+id_random).end()
        .find('td.total_tallos_producto').removeClass('total_tallos_producto_'+{{$id1}}+' total_tallos_producto_'+{{$id2}}+' total_tallos_producto_'+{{$id3}}).addClass('total_tallos_producto_'+id_random).end()
        .find('input.input_ramos_x_caja').removeClass('input_ramos_x_caja_'+{{$id1}}+' input_ramos_x_caja_'+{{$id2}}+' input_ramos_x_caja_'+{{$id3}}).addClass('input_ramos_x_caja_'+id_random).end()
        .find('input.precio_x_ramo').attr('min',0.1).removeClass('precio_x_ramo_'+{{$id1}}+' precio_x_ramo_'+{{$id2}}+' precio_x_ramo_'+{{$id3}}).addClass('precio_x_ramo_'+id_random).addClass('precio_x_ramo_producto_seleccionado').end()
        .find("input[name='tallos_x_ramo']").removeClass('tallos_x_ramo_'+{{$id1}}+' tallos_x_ramo_'+{{$id2}}+' tallos_x_ramo_'+{{$id3}}).addClass('tallos_x_ramo_'+id_random).end()
        .find('select.empaque').removeClass('empaque_'+{{$id1}}+' empaque_'+{{$id2}}+' empaque_'+{{$id3}}).addClass('empaque_'+id_random).end()
        .find('select.clasificacion_combo').removeClass('clasificacion_combo_'+{{$id1}}+' clasificacion_combo_'+{{$id2}}+' clasificacion_combo_'+{{$id3}}).addClass('clasificacion_combo_'+id_random).end()
        .removeClass('tr_pedido_combo_'+{{$id1}}+' tr_pedido_combo_'+{{$id2}}+' tr_pedido_combo_'+{{$id3}}).addClass('tr_pedido_combo_'+id_random).first().find('td:last button').remove().end()
        .find('td:last').attr('rowspan',cant_rowspan).append(btn_delete_combo_pedido).end()

        $("#alert_sin_productos").addClass('hide')

        $("tbody#tbody_productos_seleccionados").append(tr_combo)

        $(seletcs2).select2({ dropdownParent: $('#div_modal-modal_add_pedido') })
        console.log($("tbody.tbody_inputs_pedidos_combos_"+n_caja).find('input.input_ramos_x_caja')[0],n_caja)
        calcular_precio_pedido($("tbody.tbody_inputs_pedidos_combos_"+n_caja).find('input.input_ramos_x_caja')[0],n_caja)

        mini_alerta('success', 'Producto agregado', 5000)

    }

    function eliminar_combo_pedido(id,id_det_ped=''){

        if(id_det_ped!=''){

            let texto = `<div class="alert alert-warning text-center" style="margin-top:5px">
                    <p style="font-size: 16px;font-weight: bold;"><b>INDIQUE SI EL DETALLE SE REGISTRARÁ COMO PERIDIO</b></p>
                    <div style="display: flex;align-items: center;justify-content: space-evenly;">
                        <div>
                            <input type="radio" name="check_registra_perdido" value="NO" style="width: 18px;height: 18px;" id="no_registra">
                            <label style="font-size: 18px;position: relative;bottom: 4px;" for="no_registra">  <b>NO</b> </label>
                        </div>
                        <div>
                            <input type="radio" name="check_registra_perdido" value="SI" style="width: 18px;height: 18px;" id="regsitra" checked>
                            <label style="font-size: 18px;position: relative;bottom: 4px;" for="regsitra"> <b>SI </b></label>
                        </div>

                    </div>
                </div>`

            modal_quest('modal_edit_pedido', texto, '<i class="fa fa-question-o"></i> Acción a realizar', true, false, '40%', function () {

                eliminar_detalle_pedido(id_det_ped, $("input[name='check_registra_perdido']:checked").val())
                .then(res =>{

                    $("tr.tr_pedido_combo_"+id).remove()

                    listar_resumen_pedidos($('#fecha_pedidos_search').val(), true)

                    if(res.cant_det_ped== 1)
                        $(".modal").modal('hide')

                    calcular_precio_pedido()
                })

            })

        }else{

            $("tr.tr_pedido_combo_"+id).remove()
            calcular_precio_pedido()

        }

    }

    function seleccionar_variedad_combo(element,caja=null){

        let datos = {
            id_cliente: $("select#id_cliente_venta").val(),
            id_planta: $(element).val() ,
            id_emapque: $(element).parent().parent().parent().find("select.caja_combo").val()
        }
        get_jquery('/clientes/get_variedades_by_planta', datos, function (retorno) {
            //alerta(retorno)
            let variedades=''
            let pesos = ''
            let cajas = ''
            let presentaciones = ''

            retorno.orden_variedades.forEach( option => {
                variedades += `<option value="${option.id_variedad}">${option.nombre}</option>`
            })

            retorno.clasificacion_ramo.forEach(option => {
                pesos += `<option value="${option.id_clasificacion_ramo}">${option.nombre}</option>`
            })

            retorno.empaques.forEach(option => {
                cajas += `<option data-option_caja_siglas="${option.siglas}" value="${option.id_empaque}">${option.nombre.split('|')[0]}</option>`
            })

            retorno.presentaciones.forEach(option => {
                presentaciones += `<option value="${option.id_empaque}">${option.nombre}</option>`
            })

            console.log(retorno.presentaciones)
            $(element).parent().next().find('select').html(variedades)
            $(element).parent().next().next().find('select').html(pesos)
            $(element).parent().parent().parent().find("select.caja_combo").html(cajas)
            $(element).parent().parent().find('select.presentacion_combo').html(presentaciones)

            //asigna_variedad( $(element).parent().next().find('select') )

            datos={
                id_cliente: $("#id_cliente_venta").val(),
                id_variedad: typeof retorno.orden_variedades[0] != 'undefined' ? retorno.orden_variedades[0].id_variedad : null,
            }
            set_sigla_empaque(element,true)
            get_jquery('/clientes/get_logintud_especificacion_combo', datos, function (retorno) {
                $(element).parent().parent().find("input[name='longitud']").val(retorno.datos != null ? retorno.datos.longitud_ramo: 0)
                let txr = retorno.datos != null ? retorno.datos.tallos_x_ramos : 0
                $(element).parent().parent().find("input[name='tallos_x_ramo']").attr('data-tallos_predefinidos',txr).val(txr)

            },'aaaaa')

        },'aaaaa')

    }

    function asigna_variedad(element){
       $(element).parent().attr('data-id_variedad',$(element).val())
       datos={
                id_cliente: $("#id_cliente_venta").val(),
                id_variedad: element.value,
            }

        get_jquery('/clientes/get_logintud_especificacion_combo', datos, function (retorno) {
            $(element).parent().parent().find("input[name='longitud']").val(retorno.datos != null ? retorno.datos.longitud_ramo: 0)
            let txr= retorno.datos != null ? retorno.datos.tallos_x_ramos : 0
            $(element).parent().parent().find("input[name='tallos_x_ramo']").attr('data-tallos_predefinidos',txr).val(txr)


        },'aaaaa')
    }

    function eliminar_detalle_pedido(id_detalle_pedido,registra_perdido){

        return new  Promise((resolve, reject) => {

            let datos = {
                _token: '{{ csrf_token() }}',
                id_detalle_pedido,
                registra_perdido
            }

            post_jquery_m('/clientes/eliminar_detalle_pedido', datos, res =>{
                resolve(res)
            } ,'table_productos_pedidos')
        })

    }

    function set_sigla_empaque(select,caja =null){

        let siglas_empaque = $(select).find('option:selected').attr('data-option_caja_siglas')
        $(select).attr('data-siglas_empaque',siglas_empaque)
        calcular_precio_pedido(select)

        let planta_variedad =[]

        $.each($(select).parent().parent().parent().find('tr'),function(){

            planta_variedad.push({
                id_planta: $(this).find('select.planta_combo').val(),
                id_variedad: $(this).find('select.variedad_combo').val(),
            })

        })

        let datos = {
            id_cliente: $("select#id_cliente_venta").val(),
            id_empaque: caja == null ? $(select).val() : $(select).parent().parent().parent().find('select.caja_combo').val(),
            planta_variedad
        }
        get_jquery('/clientes/set_presentacion_combo', datos, function (retorno) {
            //alerta(retorno);
            console.log(retorno)
            $.each($(select).parent().parent().parent().find('tr'),function(i,j){

                let presentaciones =''

                retorno.presentaciones[i].forEach(option => {

                    presentaciones += `<option value="${option.id_empaque}">${option.nombre}</option>`
                })

                $(this).find('select.presentacion_combo').html(presentaciones)

            })

        },'aaaaa')

    }

    function set_span_tallos_x_ramo(input){

        $(input).parent().find('span#span_tallos_x_ramo').html(input.value == '' ? 0 : input.value)
        let n_caja = $(input).parent().parent().parent().attr('class').split(' ')[1].split('_')[4]

        if(input.value != $(input).data('tallos_predefinidos')){

            $("#div_error_cajas_"+n_caja).siblings().last().html(`<div id="div_error_tallos_${n_caja}" class="text-danger"> <b> <i class="fa fa-exclamation-triangle"></i> LA CANTIDAD DE TALLOS ${input.value} NO CORRESPONDE A LA PARAMETRIZADA ${$(input).data('tallos_predefinidos')}</b></div>`)

        }else{

            $("#div_error_tallos_"+n_caja).remove()

        }

    }

    function agregar_productos_simples(){

        let tabla_productos_simples = $("div#simple tbody#tbody_inputs_pedidos").find('input.input_cantidad').filter((e, j) =>  j.value > 0)

        $.each(tabla_productos_simples,function(i, obj){
            agregar_fila_simple(obj,false)
        })

        calcular_precio_pedido()
        mini_alerta('success', 'Productos agregado', 5000)
    }

    function set_precio_combo(el){

        if($(el).hasClass('precio_x_ramo_1_1') || $(el).hasClass('precio_x_ramo_2_1') || $(el).hasClass('precio_x_ramo_3_1')){

            if($(el).hasClass('precio_x_ramo_1_1')){

                if($(el).hasClass('precio_x_ramo_producto_seleccionado')){

                    $('tbody#tbody_productos_seleccionados input.precio_x_ramo_1:not(:first)').val(el.value)

                }else{

                    $('tbody.tbody_inputs_pedidos_combos_1 input.precio_x_ramo_1:not(:first)').val(el.value)

                }

            }else if($(el).hasClass('precio_x_ramo_2_1')){

                if($(el).hasClass('precio_x_ramo_producto_seleccionado')){

                    $('tbody#tbody_productos_seleccionados input.precio_x_ramo_2:not(:first)').val(el.value)

                }else{

                    $('tbody.tbody_inputs_pedidos_combos_2 input.precio_x_ramo_2:not(:first)').val(el.value)

                }

            }else if($(el).hasClass('precio_x_ramo_3_1')){

                if($(el).hasClass('precio_x_ramo_producto_seleccionado')){

                    $('tbody#tbody_productos_seleccionados input.precio_x_ramo_3:not(:first)').val(el.value)

                }else{

                    $('tbody.tbody_inputs_pedidos_combos_3 input.precio_x_ramo_3:not(:first)').val(el.value)

                }

            }

        }

    }

    function set_precio_simple(el){

        let id_input = $(el).attr('name').split('_')[1]
        let primera_caja=0
        let x = 0

        setTimeout(() => {
            $.each($("div#simple input.precio_x_ramo"),function(i){

                let pieza = $(this).parent().parent().find('input.input_cantidad').val()

                if(pieza!= '' && pieza>0){

                    if(x==0)
                        primera_caja = $(this).attr('name').split('_')[1]

                    if(id_input==primera_caja){

                        $(this).val(el.value)

                    }


                    x++

                }

            })
        }, 2000);

    }

    function set_marcacion_simple(el){

        let id_input = $(el).attr('id').split('_')[2]
        let marcacion = $(el).parent().parent().find('input.input_MARCACION').val()
        let po = $(el).parent().parent().find('input.input_PO').val()
        let upc = $(el).parent().parent().find('input.input_UPC').val()
        let primera_caja=0
        let x = 0

        $.each($("div#simple input.input_cantidad"),function(){

            if(this.value!= '' && this.value>0){

                if(x==0){
                    primera_caja = $(this).attr('id').split('_')[2]
                }

                if(id_input==primera_caja){

                    $(this).parent().parent().find('input.input_MARCACION').val(marcacion)
                    $(this).parent().parent().find('input.input_PO').val(po)
                    $(this).parent().parent().find('input.input_UPC').val(upc)

                }

                x++

            }

        })

    }

    $(function(){

        let thead = $("#thead_inputs_dinamicos tr").clone();
        @if (isset($id_pedido))
            thead = thead.find('th.th_datos_exportacion').removeClass("hide").end()
        @endif


        if(!$("#thead_productos_seleccionados tr").length){
            $("#thead_productos_seleccionados").html(thead)
        }

        let options_variedad =  $("select#filtro_planta").find('option[value=""]').text('Seleccione').end().html()
        $("select.planta_combo").append(options_variedad)
        $(seletcs2).select2({ dropdownParent: $('#div_modal-modal_add_pedido') })

    })

</script>

<style>
    .select2-container{
        width:110px!important;
    }

    .select2-selection__rendered{
        text-align: center!important
    }
</style>
