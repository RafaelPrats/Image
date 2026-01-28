<table width="100%" class="table-responsive table-bordered"
       style="border-color: #9d9d9d; margin-bottom: 10px; border-radius: 18px 18px 0 0"
       id="table_content_especificaciones">
    <thead>
    <tr style="background-color: #dd4b39; color: white">
        <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0">
            VARIEDAD
        </th>
        <th class="text-center th_yura_green">
            CALIBRE
        </th>
        <th class="text-center th_yura_green">
            CAJA
        </th>
        <th class="text-center th_yura_green">
            RAMO X CAJA
        </th>
        <th class="text-center th_yura_green">
            PRESENTACIÓN
        </th>
        <th class="text-center th_yura_green">
            TALLOS X RAMO
        </th>
        <th class="text-center th_yura_green" style="border-radius: 0 18px 0 0">
            LONGITUD
        </th>
    </tr>
    </thead>
    <tr onmouseover="$(this).css('background-color','#add8e6')" onmouseleave="$(this).css('background-color','')">
    @php  $anterior = "";  @endphp
    @foreach($data_especificacion as $x => $item)
        @foreach($item->especificacionesEmpaque as $y => $esp_emp)
            @foreach($esp_emp->detalles as $z => $det_esp_emp)
                <tr style="border-top: {{$item->id_especificacion != $anterior ? '2px solid #9d9d9d' : ''}}">
                    <td style="border-color: #9d9d9d; padding: 0px; vertical-align: middle; width: 100px; "
                        class="text-center">
                        {{$det_esp_emp->variedad->nombre}}
                    </td>
                    <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;" class="text-center">
                        {{$det_esp_emp->clasificacion_ramo->nombre}}
                    </td>
                    @if($z == 0)
                        <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;" class="text-center"
                            rowspan="{{count($esp_emp->detalles)}}">
                            {{explode('|',$esp_emp->empaque->nombre)[0]}}
                        </td>
                    @endif
                    <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;" class="text-center">
                        {{$det_esp_emp->cantidad}}
                    </td>
                    <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;" class="text-center">
                        {{$det_esp_emp->empaque_p->nombre}}
                    </td>
                    <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;" class="text-center">
                        {{isset($det_esp_emp->tallos_x_ramos) ? $det_esp_emp->tallos_x_ramos : "-"}}
                    </td>
                    <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;" class="text-center">
                        {{isset($det_esp_emp->longitud_ramo) ? $det_esp_emp->longitud_ramo." ".$det_esp_emp->unidad_medida->siglas : "-"}}
                    </td>
                </tr>
                @php  $anterior = $item->id_especificacion;  @endphp
                @endforeach
                @endforeach
                @endforeach
                {{--@php $esp = getDetalleEspecificacion($id_especificacion);@endphp
                <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;" class="text-center">
                    <ul style="padding: 0;margin:0">
                        @foreach($esp as $key => $e)
                            <li style="list-style: none;{{count($esp) != 1 ? "border-bottom: 1px solid silver" : ""}}">
                                {{$e["variedad"]}}
                            </li>
                        @endforeach
                    </ul>
                </td>
                <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;" class="text-center">
                    <ul style="padding: 0;margin:0">
                        @foreach($esp as  $e)
                            <li style="list-style: none;{{count($esp) != 1 ? "border-bottom: 1px solid silver" : ""}}">
                                {{$e["calibre"]}}
                            </li>
                        @endforeach
                    </ul>
                </td>
                <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;" class="text-center">
                    <ul style="padding: 0;margin:0">
                        @foreach($esp as $e)
                            <li style="list-style: none;{{count($esp) != 1 ? "border-bottom: 1px solid silver" : ""}}">
                                {{$e["caja"]}}
                            </li>
                        @endforeach
                    </ul>
                </td>
                <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;" class="text-center">
                    <ul style="padding: 0;margin:0">
                        @foreach($esp as $e)
                            <li style="list-style: none;{{count($esp) != 1 ? "border-bottom: 1px solid silver" : ""}}">
                                {{$e["rxc"]}}
                            </li>
                        @endforeach
                    </ul>
                </td>
                <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;" class="text-center">
                    <ul style="padding: 0;margin:0">
                        @foreach($esp as $e)
                            <li style="list-style: none;{{count($esp) != 1 ? "border-bottom: 1px solid silver" : ""}}">
                                {{$e["presentacion"]}}
                            </li>
                        @endforeach
                    </ul>
                </td>
                <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;" class="text-center">
                    <ul style="padding: 0;margin:0">
                        @foreach($esp as $e)
                            <li style="list-style: none;{{count($esp) != 1 ? "border-bottom: 1px solid silver" : ""}}">
                                {{$e["txr"] == null ? "-" : $e["txr"] }}
                            </li>
                        @endforeach
                    </ul>
                </td>
                <td style="border-color: #9d9d9d;padding: 0px;vertical-align: middle;" class="text-center">
                    <ul style="padding: 0;margin:0">
                        @foreach($esp as $e)
                            <li style="list-style: none;{{count($esp) != 1 ? "border-bottom: 1px solid silver" : ""}}">
                                {{$e["longitud"] == null ? "-" : $e["longitud"] }} {{($e["unidad_medida_longitud"] == null || $e["longitud"] == null) ? "" : $e["unidad_medida_longitud"]}}
                            </li>
                        @endforeach
                    </ul>
                </td>--}}
                </tr>
</table>

<div class="form-group input-group" style="padding: 0px">
    <input type="text" class="form-control" placeholder="Buscar cliente" id="busqueda_cliente" name="busqueda_cliente">
    <span class="input-group-btn">
        <button class="btn btn-default" onclick="buscar_listado()" >
            <i class="fa fa-fw fa-search" style="color: #0c0c0c"></i>
        </button>
    </span>
    </span>
</div>

<div id="tabla_cliente_especificacion">
    <table width="100%" class="table-responsive table-bordered" style="border-color: #9d9d9d; border-radius: 18px 18px 0 0"
        id="table_content_especificaciones">
        <thead>
        <tr style="background-color: #dd4b39; color: white">
            <th class="text-left th_yura_green" style="border-radius: 18px 0 0 0; padding-left: 10px">
                CLIENTE
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
                ASIGNAR
            </th>
            @if (es_server())
                <th class="text-center th_yura_green" style="width: 60px; border-radius: 0 18px 0 0">
                    ESTADO
                </th>
            @endif
        </tr>
        </thead>
        @foreach($listado as $item)
            <tr onmouseover="$(this).css('background-color','#add8e6')" onmouseleave="$(this).css('background-color','')"
                id="tr_cliente_{{$item->id_cliente}}">
                <td style="border-color: #9d9d9d; padding-left: 10px" class="text-left">{{$item->nombre}}</td>
                <td style="border-color: #9d9d9d" class="text-center error_{{$item->id_cliente}}">
                    @php
                        $check = '';
                        $activo = '';
                        foreach ($asginacion as $a)
                            if($a->id_cliente == $item->id_cliente){
                                $check = 'checked';
                                $activo = $a->estado;
                            }
                    @endphp
                    @if (es_server())
                        <input type="checkbox" {{$check}} id="cliente_{{$item->id_cliente}}" name="cliente"
                            onclick="verificar_pedido_especificacion('{{$item->id_cliente}}','{{$id_especificacion}}',this.id)"
                            value="{{$id_especificacion}}">
                    @else
                        {!! $check == 'checked' ? '<i class="fa fa-fw fa-check"></i>' : ''!!}
                    @endif
                </td>
                @if (es_server())
                    <td style="border-color: #9d9d9d" class="text-center">
                        <button type="button" title="{{$activo == 1 || $activo == ''  ? 'Activo' : 'Inactivo'}}"
                                id="btn_estado_esp_{{$item->id_cliente}}"
                                onclick="cambiar_estado('{{$item->id_cliente}}','{{$id_especificacion}}')"
                                class="btn btn-yura_{{$activo == 1 || $activo == '' ? 'primary' : 'warning'}} btn-xs {{$activo == '' ? 'hidden' : ''}}">
                            <i class="fa fa-fw fa-{{$activo == 1 || $activo == '' ? 'check' : 'trash'}}"></i>
                        </button>
                    </td>
                @endif
            </tr>
        @endforeach
    </table>
</div>
<script>
    function cambiar_estado(cliente, esp) {
        datos = {
            _token: '{{csrf_token()}}',
            cliente: cliente,
            esp: esp,
        };
        $('#tr_cliente_' + cliente).LoadingOverlay('show');
        $.post('{{url('especificacion/cambiar_estado')}}', datos, function (retorno) {
            if (retorno.estado == 1) {
                $('#btn_estado_esp_' + cliente).removeClass('btn-yura_warning');
                $('#btn_estado_esp_' + cliente).addClass('btn-yura_primary');
                $('#btn_estado_esp_' + cliente).html('<i class="fa fa-fw fa-check"></i>');
            } else {
                $('#btn_estado_esp_' + cliente).removeClass('btn-yura_primary');
                $('#btn_estado_esp_' + cliente).addClass('btn-yura_warning');
                $('#btn_estado_esp_' + cliente).html('<i class="fa fa-fw fa-trash"></i>');
            }
        }, 'json').fail(function (retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function () {
            $('#tr_cliente_' + cliente).LoadingOverlay('hide');
        });
    }

    $("#busqueda_cliente").on("keyup", function ()  {
        var value = $("#busqueda_cliente").val().toLowerCase();
        $("div#tabla_cliente_especificacion tbody tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    })


</script>
