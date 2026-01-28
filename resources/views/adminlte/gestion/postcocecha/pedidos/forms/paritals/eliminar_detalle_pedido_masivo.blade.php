<form id="form_add_pedido" name="form_add_pedido">
    <div>
        <div id="table_recepciones">
            <div class="row" style="margin-bottom: 10px" id="filtros_pedido">
                <div class="col-md-2">
                    <label for="Fecha de entrega" style="font-size: 11pt">
                        <i class="fa fa-calendar"></i> FECHA
                    </label>
                    <input type="text" class="form-control" value="{{$detalles[0]->pedido->fecha_pedido}}" readonly>
                </div>
                <div class="col-md-2">
                    <label for="Cliente" style="font-size: 11pt">
                        <i class="fa fa-user-circle-o"></i> CLIENTE
                    </label>
                    <input type="text" class="form-control" value="{{$detalles[0]->pedido->cliente->detalle()->nombre}}" readonly>
                </div>
                <div class="col-md-2" >
                    <label for="filtro_planta" style="font-size: 11pt">
                        Variedad
                    </label>
                    <select class="form-control" id="filtro_planta" name="filtro_planta" onchange="getVariedadesByPlanta()">
                        <option value="">TODOS</option>
                        @foreach ($plantas as $p)
                            <option value="{{$p->id_planta}}">{{$p->nombre}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2" >
                    <label for="filtro_variedad" style="font-size: 11pt">
                        Color
                    </label>
                    <select class="form-control" id="filtro_variedad" name="filtro_variedad">
                        <option value="">TODOS</option>
                    </select>
                </div>
                <div class="col-md-2" >
                    <label for="filtro_marcaciones" style="font-size: 11pt">
                        Marcaciones
                    </label>
                    <select class="form-control" id="filtro_marcaciones" name="filtro_marcaciones">
                        <option value="">TODOS</option>
                    </select>
                </div>
                @if($detalles[0]->pedido->tipo_pedido == 'STANDING ORDER')
                    <div class="col-md-2" style="position: relative;top: 30px;"
                        title="Si selecciona esta opción se eliminará ese detalle en todos los pedidos Standing Order">
                        <input type="checkbox" name="elminar_masivo" id="elminar_masivo" style="width: 18px;height:18px" checked>
                        <label for="elminar_masivo" style="font-size: 11pt">
                            <span style="position: relative;bottom: 3px;" class="text-red">ELIMINAR MASIVO</span>
                        </label>
                    </div>
                @endif
            </div>
            <div class="row">
                <div class="col-md-12" id="table_campo_pedido" style="overflow-x: scroll"></div>
                <div class="col-md-12" >
                    <div id="productos_seleccionados" style="margin-top: 15px">
                        <table width="100%" class="table-responsive table-bordered" style="font-size: 0.8em; border-color: white" id="table_productos_pedidos">
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
                                        style="border-color: #9d9d9d;width:75px">
                                        CARGUERA
                                    </th>
                                    @foreach($marcaciones as $c => $m)
                                        @if(isset($datos_exportacion[$c]) && $m->id_datos_exportacion ==  $datos_exportacion[$c]->id_datos_exportacion )
                                        <td class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                                            style="border-color: #9d9d9d;width:75px">
                                            {{$m->nombre}}
                                        </td>
                                        @endif
                                    @endforeach
                                    <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}"
                                        style="border-color: #9d9d9d;width:100px;width: 20px;">
                                        ELIMINAR
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tbody_productos_seleccionados">
                                @if(isset($detalles) && count($detalles))
                                    @php $anterior = ''; @endphp
                                    @foreach($detalles as $x =>$det_ped)
                                        @php
                                            $b=1;
                                            $det_ped_cliente_especificacion = $det_ped->cliente_especificacion;
                                            $det_ped_especificacion = $det_ped_cliente_especificacion->especificacion;
                                            $getCantidadDetallesByEspecificacion = getCantidadDetallesByEspecificacion($det_ped_especificacion->id_especificacion);
                                            $det_ped_data_tallos = $det_ped->data_tallos;
                                            $id_random = mt_rand(200, 9999999);
                                            $especificaciones = getEspecificacion($det_ped_especificacion->id_especificacion)->especificacionesEmpaque
                                        @endphp
                                        @foreach($especificaciones as $y => $esp_emp)
                                            @foreach($esp_emp->detalles as $z => $det_esp_emp)
                                                @php
                                                    $ramos_modificado = getRamosXCajaModificado($det_ped->id_detalle_pedido,$det_esp_emp->id_detalle_especificacionempaque);
                                                    $det_esp_emp_variedad = $det_esp_emp->variedad;
                                                    $det_esp_emp_planta = $det_esp_emp_variedad->planta;
                                                    $det_esp_emp_clasificacion_ramo = $det_esp_emp->clasificacion_ramo;
                                                @endphp
                                                <tr style="border-top: {{$det_ped_especificacion->id_especificacion != $anterior ? '2px solid #9d9d9d' : ''}}"
                                                    class="tr_detalle_pedido_{{$id_random}}">
                                                    @if($det_ped_especificacion->id_especificacion != $anterior)
                                                        <td style="border-color: #9d9d9d;padding: 0px 0px; vertical-align: middle;width:60px"
                                                            class="text-center" rowspan="{{$getCantidadDetallesByEspecificacion}}">
                                                            {{$det_ped->cantidad}}
                                                        </td>
                                                    @endif
                                                    <td style="border-color: #9d9d9d; padding: 0px; vertical-align: middle; width: 60px;" class="text-center td_planta">
                                                        {{$det_esp_emp_variedad->planta->nombre}}
                                                    </td>
                                                    <td style="border-color: #9d9d9d; padding: 0px; vertical-align: middle; width: 60px;"
                                                        class="text-center td_variedad td_variedad_{{$id_random}}" data-id_variedad="{{$det_esp_emp_variedad->id_variedad}}">
                                                        {{$det_esp_emp_variedad->nombre}}
                                                    </td>
                                                    <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;width:40px"
                                                        class="text-center td_calibre_{{$x+1}}_{{$b}} td_calibre_{{$x+1}} td_calibre_{{$id_random}}"
                                                        data-id_det_esp_emp="{{$det_esp_emp->id_detalle_especificacionempaque}}">
                                                        <span>{{$det_esp_emp_clasificacion_ramo->nombre}}</span>
                                                        {{$det_esp_emp_clasificacion_ramo->unidad_medida->siglas}}
                                                    </td>
                                                    @if($z == 0)
                                                        <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;" class="text-center"
                                                            rowspan="{{count($esp_emp->detalles)}}">
                                                            {{explode('|', $esp_emp->empaque->nombre)[0]}}
                                                        </td>
                                                    @endif
                                                    <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle"
                                                        class="text-center td_presentacion_{{$x+1}}_{{$b}} td_presentacion_{{$x+1}}">
                                                        <span>{{$det_esp_emp->empaque_p->nombre}}</span>
                                                    </td>
                                                    <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;width:40px"
                                                        class="text-center ramos_x_caja_{{$x+1}} ramos_x_caja_{{$x+1}}_{{$b}}"
                                                        data-ramos_x_caja="{{$det_esp_emp->cantidad}}">
                                                        {{$det_esp_emp->cantidad}}
                                                    </td>
                                                    <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;width:40px"
                                                        class="td_tallos_x_malla td_tallos_x_malla_{{$x+1}} td_tallos_x_malla_{{$x+1}}_{{$b}}
                                                        {{(isset($det_ped_especificacion->tipo) && $det_ped_especificacion->tipo == "O") ? "" : "hide"}}">
                                                        <input type="number" min="0" id="tallos_x_malla_{{$x+1}}_{{$b}}" name="tallos_x_malla_{{$x+1}}_{{$b}}"
                                                            class="text-center tallos_x_malla_{{$x+1}} tallos_x_malla_{{$x+1}}_{{$b}}"
                                                            value="{{isset($det_ped_data_tallos->tallos_x_malla) ? $det_ped_data_tallos->tallos_x_malla : ""}}"
                                                            style="border: none;width: 100%;height: 34px;">
                                                        <input type="hidden" id="tallos_x_caja_{{$x+1}}_{{$b}}" name="tallos_x_caja_{{$x+1}}_{{$b}}"
                                                            class="text-center tallos_x_caja_{{$x+1}} tallos_x_caja_{{$x+1}}_{{$b}}">
                                                    </td>
                                                    @if($det_ped_especificacion->id_especificacion != $anterior)
                                                        <td id="td_total_ramos_{{$x+1}}" style="border-color: #9d9d9d; padding: 0px; vertical-align: middle; width: 45px;"
                                                            class="text-center td_total_ramos td_total_ramos_{{$id_random}}" rowspan="{{$getCantidadDetallesByEspecificacion}}">
                                                            {{$det_esp_emp->cantidad*$det_ped->cantidad}}
                                                        </td>
                                                    @endif
                                                    <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;width:35px"
                                                        class="text-center td_tallos_x_ramo_{{$x+1}}_{{$b}} td_tallos_x_ramo_{{$x+1}} td_tallos_x_ramo_producto">
                                                            @if(isset($det_ped_data_tallos->tallos_x_ramo))
                                                                <input type="text" id="input_tallos_{{$x+1}}" name="input_tallos_{{$x+1}}_{{$b}}"
                                                                    class="input_tallos_{{$x+1}} input_tallos_{{$x+1}}_{{$b}}"
                                                                    onkeyup="calcular_precio_pedido(null)" onchange="crear_orden_pedido(null)"
                                                                    value="{{$det_ped_data_tallos->tallos_x_ramo}}"
                                                                    style="width:100%;border:none;text-align:center;height: 34px;"
                                                                    title="Escribe la cantidad de tallos por malla">
                                                            @else
                                                                <span> {{$det_esp_emp->tallos_x_ramos}}</span>
                                                            @endif
                                                        <input id="tallos_x_ramo_{{$x+1}}_{{$b}}" name="tallos_x_ramo_{{$x+1}}_{{$b}}"
                                                        onchange="cargar_espeicificaciones_cliente(true)"      type="hidden" value="{{$det_esp_emp->tallos_x_ramos}}"
                                                            class="tallos_x_ramo_{{$x+1}}_{{$b}} tallos_x_ramo_{{$x+1}}">
                                                    </td>
                                                    <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;width:35px"
                                                        class="text-center td_tallos_total_{{$x+1}}_{{$b}} td_tallos_total_{{$x+1}} total_tallos_producto">
                                                        {{$det_esp_emp->cantidad*$det_ped->cantidad*$det_esp_emp->tallos_x_ramos}}
                                                    </td>
                                                    <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;width:35px" class="text-center">
                                                        @if($det_esp_emp->longitud_ramo != '' && $det_esp_emp->id_unidad_medida != '')
                                                            {{$det_esp_emp->longitud_ramo}}{{$det_esp_emp->unidad_medida->siglas}}
                                                            <input type="hidden" id="longitud_ramo_{{$x+1}}_{{$b}}" name="" class="longitud_ramo_{{$x+1}}"
                                                                value="{{$det_esp_emp->longitud_ramo}}">
                                                            <input type="hidden" id="u_m_longitud_ramo_{{$x+1}}_{{$b}}" name="" class="u_m_longitud_ramo_{{$x+1}}"
                                                                value="{{$det_esp_emp->unidad_medida->id_unidad_medida}}">
                                                        @endif
                                                    </td>
                                                    <td id="td_precio_variedad_{{$det_esp_emp->id_detalle_especificacionempaque}}_{{($x+1)}}"
                                                        style="border-color: #9d9d9d;padding: 0px 0px; vertical-align: middle;text-align:center">
                                                        {{explode(";",explode('|',$det_ped->precio)[$b-1])[0]}}
                                                    </td>
                                                    @if($det_ped_especificacion->id_especificacion != $anterior)
                                                        <td class="text-center agencia_carga" style="border-color: #9d9d9d; vertical-align: middle;width:75px"
                                                            rowspan="{{$getCantidadDetallesByEspecificacion}}">
                                                            {{$det_ped->agencia_carga->nombre}}
                                                        </td>
                                                        @foreach($datos_exportacion as $de)
                                                            <td rowspan="{{$getCantidadDetallesByEspecificacion}}" style="border-color: #9d9d9d; vertical-align: middle;text-align:center" class="td_dato_exportacion">
                                                                @php $getDe = getDatosExportacion($det_ped->id_detalle_pedido,$de->id_dato_exportacion); @endphp
                                                                {{isset($getDe->valor) ? $getDe->valor : ""}}
                                                            </td>
                                                        @endforeach
                                                        <td class="text-center" style="border-color: #9d9d9d; vertical-align: middle"
                                                            rowspan="{{$getCantidadDetallesByEspecificacion}}" id="{{$y==0 ? 'td-btn-delete-row' : ''}}">
                                                            <button type="button" class="btn btn-yura_danger btn-sm btn-remove-seleccion" style="margin-right: 5px;"
                                                                    title="Eliminar del pedido" onclick="eliminar_detalle_pedido({{$id_random}},'{{$det_ped->id_detalle_pedido}}')">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
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
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>

    setTimeout(() => {
        $("#filtro_marcaciones, #filtro_planta, #filtro_variedad").select2({
            dropdownParent: $('#div_modal-modal_eliminar_detalle_pedido_masivo')
        })
    }, 500)

    function getVariedadesByPlanta(){

        let datos = {
            id_planta: $("#filtro_planta").val(),
        }

        get_jquery('/clientes/get_variedades_by_planta', datos, function (retorno) {

            let html = `<option value="">TODOS</option>`

            retorno.orden_variedades.forEach((option)=>{
                html += `<option value="${option.id_variedad}">${option.nombre}</option>`
            })
            $("#filtro_variedad").html(html)

        },'filtro_variedad')

    }

    function get_marcaciones(){

        let marcaciones = []

        $.each($("td.td_dato_exportacion"),function(){

            if(this.innerText!='' && !marcaciones.includes(this.innerText.trim()))
                marcaciones.push(this.innerText.trim())

        })

        let html =''

        marcaciones.forEach(val=>{
            html += `<option value="${val}">${val}</option>`
        })
        $("#filtro_marcaciones").append(html)

    }

    $("#filtro_planta").on("change", function ()  {

        let value = $("#filtro_planta option:selected").text().toLowerCase()

        if(value == 'todos'){
            $("div#productos_seleccionados tbody tr").css('display','table-row')
        }else{
            $("div#productos_seleccionados tbody tr").filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        }

    })

    $("#filtro_variedad").on("change", function ()  {

        let planta = $("#filtro_planta option:selected").text().toLowerCase()
        let variedad = $("#filtro_variedad option:selected").text().toLowerCase()

        if(variedad == 'todos'){
            $("div#productos_seleccionados tbody tr").css('display','table-row')
        }else{
            $("div#productos_seleccionados tbody tr").filter(function () {

                $(this).toggle(
                    $(this).find('td.td_planta').html().trim().toLowerCase() == planta
                    &&
                    $(this).text().toLowerCase().indexOf(variedad) > -1
                )

            });
        }

    })

    $("#filtro_marcaciones").on("change", function ()  {

        let value = $("#filtro_marcaciones option:selected").text().toLowerCase()

        if(value == 'todos'){
            $("div#productos_seleccionados tbody tr").css('display','table-row')
        }else{
            $("div#productos_seleccionados tbody tr").filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        }

    })

    function eliminar_detalle_pedido(id, id_detalle_pedido){

        let datos = {
            _token: '{{ csrf_token() }}',
            id_detalle_pedido,
            elminar_masivo: $("#elminar_masivo").is(':checked')
        }

        if(!datos.elminar_masivo){

            post_jquery_m('/clientes/eliminar_detalle_pedido', datos, res =>{
                if(res.success){
                    $('tr.tr_detalle_pedido_'+id).remove()
                    //listar_resumen_pedidos(document.getElementById('fecha_pedidos_search').value,true,document.getElementById('id_configuracion_pedido').value,document.getElementById('id_cliente').value)
                }
            } ,'filtro_marcaciones')

        }else{
            modal_quest('modal_message_eliminar_detalle_pedido_masivo',
            '<div class="alert alert-warning text-center"><label><i class="fa fa-exclamation-triangle" ></i> Esta seguro de eliminar el detalle del pedido en todos los Stadings del cliente?</label></div>',
            '<i class="fa fa-cube"></i> Eliminar detalle del pedido', true, false, '50%', function () {
                post_jquery_m('/clientes/eliminar_detalle_pedido_masivo', datos, res =>{
                    if(res.success){
                        $('tr.tr_detalle_pedido_'+id).remove()
                        //listar_resumen_pedidos(document.getElementById('fecha_pedidos_search').value,true,document.getElementById('id_configuracion_pedido').value,document.getElementById('id_cliente').value)
                    }
                } ,'filtro_marcaciones')
            })

        }

    }

    $(document).ready(()=>{ get_marcaciones() })

</script>

<style>

    div#filtros_pedido .select2-container{
        display: block;
        width: 100%!important
    }

    .select2-selection--single{
        height: 33px!important;
        border-radius: 0px!important;
        border-color: #d2d6de!important;
    }

    div.select2_rxc span.select2-selection{
        width: 200px;
    }

    .select2-container{
        width:110px!important;
    }

    .select2-selection__rendered{
        text-align: center!important
    }

</style>
