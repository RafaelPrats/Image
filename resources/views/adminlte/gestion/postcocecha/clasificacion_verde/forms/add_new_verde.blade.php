<legend class="text-center" style="font-size: 1em">Clasificación en verde del día: <strong>{{convertDateToText($fecha)}}</strong></legend>

<input type="hidden" id="id_verde" value="{{isset($verde) ? $verde->id_clasificacion_verde : ''}}">
<div class="row">
    <div class="col-md-7">
        <table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d">
            <tr>
                <th class="text-center th_yura_green" rowspan="2">
                    Calibre
                </th>
                @foreach($variedades as $var)
                    <input type="hidden" class="ids_variedad" value="{{$var['id_variedad']}}">
                    <th class="text-center th_yura_green" colspan="2">
                        {{$var['siglas']}}
                    </th>
                @endforeach
                <th class="text-center th_yura_green" style="padding-left: 5px; padding-right: 5px" colspan="2">
                    Totales
                </th>
            </tr>
            <tr>
                @foreach($variedades as $v)
                    <th class="text-center bg-yura_dark" style="padding-left: 10px; padding-right: 10px">
                        Tallos
                    </th>
                    <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px">
                        Descartes
                    </th>
                @endforeach
                <th class="text-center bg-yura_dark" style="padding-left: 10px; padding-right: 10px">
                    Tallos
                </th>
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px">
                    Descartes
                </th>
            </tr>
            @foreach($unitarias as $item)
                <input type="hidden" class="ids_unitaria" value="{{$item->id_clasificacion_unitaria}}">
                <tr>
                    <th class="text-center"
                        style="border-color: #9d9d9d; background-color: {{explode('|', $item->color)[0]}}; {{--color: {{explode('|', $item->color)[1]}};--}}">
                        {{explode('|',$item->nombre)[0]}}{{$item->unidad_medida->siglas}}
                    </th>
                    @foreach($variedades as $var)
                        <td class="text-center" style="border-color: #9d9d9d">
                            <input type="number" style="width: 100%" class="text-center" onchange="calcular_tallos()" onkeyup="calcular_tallos()"
                                   id="verde_tallos_x_ramos_{{$item->id_clasificacion_unitaria}}_{{$var['id_variedad']}}" placeholder="0"
                                   min="0">
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            <input type="number" style="width: 100%" class="text-center" onchange="calcular_tallos()" onkeyup="calcular_tallos()"
                                   id="verde_descartes_{{$item->id_clasificacion_unitaria}}_{{$var['id_variedad']}}" placeholder="0"
                                   min="0">
                        </td>
                    @endforeach
                    <th class="text-center" style="border-color: #9d9d9d" id="th_total_unitaria_{{$item->id_clasificacion_unitaria}}">
                        0
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d" id="th_total_descarte_unitaria_{{$item->id_clasificacion_unitaria}}">
                        0
                    </th>
                </tr>
            @endforeach
            <tr>
                <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                    Totales
                </th>
                @foreach($variedades as $var)
                    <th class="text-center th_yura_green" id="th_total_tallos_{{$var['id_variedad']}}">
                        0
                    </th>
                    <th class="text-center th_yura_green" id="th_total_descartes_{{$var['id_variedad']}}">
                        0
                    </th>
                @endforeach
                <th class="text-center th_yura_green" id="th_total_tallos">
                    0
                </th>
                <th class="text-center th_yura_green" id="th_total_descartes">
                    0
                </th>
            </tr>
        </table>
        @if(count($variedades) > 0)
            <div class="text-center" style="margin-top: 10px">
                <button type="button" class="btn btn-yura_primary" onclick="store_verde()">
                    <i class="fa fa-fw fa-save"></i> Guardar
                </button>
            </div>
        @endif
    </div>
    <div class="col-md-5">
        <table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d">
            <tr>
                <th class="text-center th_yura_green">
                    Variedad
                </th>
                <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                    Cosechados
                </th>
                <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                    Clasificados
                </th>
                <th class="text-center th_yura_green">
                    Desecho %
                </th>
            </tr>
            @php
                $total_cosechados = 0;
                $total_clasificados = 0;
            @endphp
            @foreach($variedades as $var)
                @php
                    $desecho = 100 - porcentaje($var['clasificado'], $var['cosechado'], 1);
                    $total_cosechados += $var['cosechado'];
                    $total_clasificados += $var['clasificado'];
                @endphp
                <tr>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$var['nombre']}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($var['cosechado'])}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($var['clasificado'])}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{round($desecho, 2)}}%
                    </td>
                </tr>
            @endforeach
            @php
                $total_desecho = 100 - porcentaje($total_clasificados, $total_cosechados, 1);
            @endphp
            <tr>
                <th class="text-center th_yura_green">
                    Totales
                </th>
                <th class="text-center th_yura_green">
                    {{number_format($total_cosechados)}}
                </th>
                <th class="text-center th_yura_green">
                    {{number_format($total_clasificados)}}
                </th>
                <th class="text-center th_yura_green">
                    {{round($total_desecho, 2)}}%
                </th>
            </tr>
        </table>
    </div>
</div>

<legend style="font-size: 1em; margin-top: 10px" class="text-center mouse-hand" onclick="$('#new_div_detalles_verde').toggleClass('hidden')">
    <i class="fa fa-fw fa-edit"></i> Editar
</legend>
<div class="hidden" id="new_div_detalles_verde">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr>
            <th class="text-center th_yura_green">
                Calibre
            </th>
            <th class="text-center th_yura_green">
                Variedad
            </th>
            <th class="text-center th_yura_green">
                Tallos
            </th>
            <th class="text-center th_yura_green">
                Descartes
            </th>
            <th class="text-center th_yura_green">
                Opciones
            </th>
        </tr>
        @foreach($detalles as $d)
            @php
                $unitaria = $d->clasificacion_unitaria;
            @endphp
            <tr id="tr_edit_detalle_{{$d->id_detalle_clasificacion_verde}}">
                <th class="text-center"
                    style="border-color: #9d9d9d; background-color: {{explode('|', $unitaria->color)[0]}}; color: {{explode('|', $unitaria->color)[1]}};">
                    {{explode('|', $unitaria->nombre)[0]}}{{$unitaria->unidad_medida->siglas}}
                </th>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{$d->variedad->nombre}}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="edit_tallos_{{$d->id_detalle_clasificacion_verde}}" value="{{$d->tallos_x_ramos}}" min="0"
                           class="text-center" style="width: 100%">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="edit_descartes_{{$d->id_detalle_clasificacion_verde}}" value="{{$d->descartes}}" min="0"
                           class="text-center" style="width: 100%">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_primary" title="Editar"
                                onclick="update_detalle_verde('{{$d->id_detalle_clasificacion_verde}}')">
                            <i class="fa fa-fw fa-save"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_danger" title="Eliminar"
                                onclick="delete_detalle_verde('{{$d->id_detalle_clasificacion_verde}}')">
                            <i class="fa fa-fw fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
    </table>
</div>

<script>
    function calcular_tallos() {
        ids_variedad = $('.ids_variedad');
        total_variedades = [];
        for (z = 0; z < ids_variedad.length; z++)
            total_variedades.push({
                tallos: 0,
                descartes: 0,
            });

        ids_unitaria = $('.ids_unitaria');
        total_tallos = 0;
        total_descartes = 0;
        for (i = 0; i < ids_unitaria.length; i++) {
            id_u = ids_unitaria[i].value;
            total_unitaria = 0;
            total_descartes_unitaria = 0;
            for (z = 0; z < ids_variedad.length; z++) {
                id_v = ids_variedad[z].value;
                tallos = parseInt($('#verde_tallos_x_ramos_' + id_u + '_' + id_v).val());
                descartes = parseInt($('#verde_descartes_' + id_u + '_' + id_v).val());
                if (tallos > 0) {
                    total_unitaria += tallos;
                    total_variedades[z]['tallos'] += tallos;
                }
                if (descartes > 0) {
                    total_descartes_unitaria += descartes;
                    total_variedades[z]['descartes'] += descartes;
                }
            }
            $('#th_total_unitaria_' + id_u).html(total_unitaria);
            $('#th_total_descarte_unitaria_' + id_u).html(total_descartes_unitaria);
            total_tallos += total_unitaria;
            total_descartes += total_descartes_unitaria;
        }
        $('#th_total_tallos').html(total_tallos);
        $('#th_total_descartes').html(total_descartes);

        for (i = 0; i < ids_unitaria.length; i++) {
            id_v = ids_variedad[i].value;
            $('#th_total_tallos_' + id_v).html(total_variedades[i]['tallos']);
            $('#th_total_descartes_' + id_v).html(total_variedades[i]['descartes']);
        }

    }

    function store_verde() {
        data = [];
        ids_unitaria = $('.ids_unitaria');
        ids_variedad = $('.ids_variedad');
        total_tallos = 0;
        total_descartes = 0;
        for (i = 0; i < ids_unitaria.length; i++) {
            id_u = ids_unitaria[i].value;
            total_unitaria = 0;
            total_descartes_unitaria = 0;
            for (z = 0; z < ids_variedad.length; z++) {
                id_v = ids_variedad[z].value;
                tallos = parseInt($('#verde_tallos_x_ramos_' + id_u + '_' + id_v).val());
                descartes = parseInt($('#verde_descartes_' + id_u + '_' + id_v).val());
                if (tallos > 0 || descartes > 0) {
                    total_unitaria += tallos > 0 ? tallos : 0;
                    total_descartes_unitaria += descartes > 0 ? descartes : 0;
                    data.push({
                        unitaria: id_u,
                        variedad: id_v,
                        tallos: tallos > 0 ? tallos : 0,
                        descartes: descartes > 0 ? descartes : 0,
                    });
                }
            }
            total_tallos += total_unitaria;
            total_descartes += total_descartes_unitaria;
        }
        if (total_tallos > 0 || total_descartes > 0) {
            datos = {
                _token: '{{csrf_token()}}',
                verde: $('#id_verde').val(),
                data: data,
                fecha: '{{$fecha}}',
            };
            post_jquery_m('{{url('clasificacion_verde/store_verde')}}', datos, function () {
                cerrar_modals();
                add_new_verde();
                buscar_listado();
            });
        }
    }

    function update_detalle_verde(det) {
        datos = {
            _token: '{{csrf_token()}}',
            det: det,
            tallos: $('#edit_tallos_' + det).val(),
            descartes: $('#edit_descartes_' + det).val(),
        };
        $('#tr_edit_detalle_' + det).LoadingOverlay('show');
        $.post('{{url('clasificacion_verde/update_detalle_verde')}}', datos, function (retorno) {
            mini_alerta('success', retorno.mensaje, 5000);
        }, 'json').fail(function (retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function () {
            $('#tr_edit_detalle_' + det).LoadingOverlay('hide');
        });
    }

    function delete_detalle_verde(det) {
        modal_quest('modal_quest-delete_detalle_verde',
            '<div class="alert alert-warning text-center">¿Desea <strong>ELIMINAR</strong> el registro?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '50%', function () {
                datos = {
                    _token: '{{csrf_token()}}',
                    det: det,
                };
                $('#tr_edit_detalle_' + det).LoadingOverlay('show');
                $.post('{{url('clasificacion_verde/delete_detalle_verde')}}', datos, function (retorno) {
                    if (retorno.success) {
                        $('#tr_edit_detalle_' + det).remove();
                        mini_alerta('success', retorno.mensaje, 5000);
                    }
                }, 'json').fail(function (retorno) {
                    console.log(retorno);
                    alerta_errores(retorno.responseText);
                }).always(function () {
                    $('#tr_edit_detalle_' + det).LoadingOverlay('hide');
                });
            });
    }
</script>
