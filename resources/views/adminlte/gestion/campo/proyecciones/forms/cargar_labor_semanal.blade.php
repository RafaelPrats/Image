<legend style="font-size: 1em" class="text-center">
    <strong>{{$app_nombre}}</strong> de la semana: <strong>{{$semana->codigo}}</strong>
</legend>

<div style="overflow-y: scroll; overflow-x: scroll; height: 400px">
    <table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0">
        <tr id="tr_proy_app_fija_top_0">
            <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0; padding-left: 5px">
                #
            </th>
            <th class="text-center th_yura_green">
                Módulo
            </th>
            <th class="text-center th_yura_green" style="width: 80px">
                Fecha
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
                No. Sem.
            </th>
            <th class="text-center th_yura_green" style="width: 60px; padding: 5px">
                Repetición
            </th>
            <th class="text-center th_yura_green {{$app_uso == 'C' ? 'hidden' : ''}}" style="width: 60px; padding: 5px">
                Ltr x Cama
            </th>
            <th class="text-center th_yura_green" style="width: 60px; padding: 5px">
                #Camas
            </th>
            <th class="text-center th_yura_green" style="width: 60px; padding: 5px">
                Horas
            </th>
            <th class="text-center th_yura_green" style="width: 120px; padding: 5px">
                Estado
            </th>
            @foreach($detalles as $det)
                <th class="text-center th_detalles_app hidden"
                    style="background-color: #e9ecef; border-right: 2px solid black; border-left: 2px solid black"
                    colspan="2">
                    <div style="width: 150px">
                        @if($det->producto != '')
                            {{$det->producto}}
                        @else
                            {{$det->mano_obra}}
                        @endif
                    </div>
                </th>
            @endforeach
            <th class="text-center th_yura_green" style="padding: 5px; border-radius: 0 18px 0 0; width: 100px">
                <div style="width: 100px">
                    <div class="btn-group">
                        <button type="button" class="btn btn-yura_default btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-fw fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu">
                            <li>
                                <a href="javascript:void(0)" onclick="add_adicional('{{$semana->codigo}}', '{{$app_nombre}}')">
                                    <i class="fa fa-fw fa-plus"></i> Agregar
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" onclick="$('.th_detalles_app').toggleClass('hidden')">
                                    <i class="fa fa-fw fa-eye-slash"></i> Mostrar/Ocultar detalles
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)">
                                    <i class="fa fa-fw fa-file-excel-o"></i> Exportar
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </th>
        </tr>
        @php
            $estados = [
                'P' => 'Programado',
                'M' => 'Modificado',
                'E' => 'Ejecutado',
                'C' => 'Cancelado',
                'A' => 'Adicional',
                'X' => 'Programado (+)',
                'clase-P' => '',
                'clase-M' => '',
                'clase-X' => '',
                'clase-E' => 'text-color_yura',
                'clase-C' => 'text-color_yura_danger',
                'clase-A' => '',
            ];
            $anterior = '';
        @endphp
        @foreach($listado as $pos => $item)
            @if($item->app_continua == 1 && $item->estado != 'C')
                @php
                    if ($pos == 0){
                        $anterior = $item->nombre;
                        $fecha_desde = $item->fecha != '' ? $item->fecha : $semana->fecha_inicial;
                        $fecha_hasta = $item->fecha != '' ? $item->fecha : $semana->fecha_inicial;
                        $rep_ini = $item->app_repeticion;
                        $rep_fin = $item->app_repeticion;
                    }
                @endphp
                @if($anterior == $item->nombre)
                    @php
                        $fecha = $item->fecha != '' ? $item->fecha : $semana->fecha_inicial;
                        if($fecha_desde > $fecha)
                            $fecha_desde = $fecha;
                        if($fecha_hasta < $fecha)
                            $fecha_hasta = $fecha;
                        if($rep_ini > $item->app_repeticion)
                            $rep_ini = $item->app_repeticion;
                        if($rep_fin < $item->app_repeticion)
                            $rep_fin = $item->app_repeticion;
                    @endphp
                @else
                    <tr class="bg-yura_dark">
                        <td class="text-center" colspan="2">
                            {{$anterior}}
                        </td>
                        <td class="text-center" colspan="2">
                            <strong>{{$fecha_desde}}</strong> - <strong>{{$fecha_hasta}}</strong>
                        </td>
                        <td class="text-center" colspan="2">
                            <strong>{{$rep_ini}}</strong> - <strong>{{$rep_fin}}</strong>
                        </td>
                        <td colspan="3" class="text-right" style="padding-right: 5px">
                            <div class="btn-group">
                                <button type="button" class="btn btn-xs btn-yura_default"
                                        onclick="$('.tr_app_continua_{{$anterior}}').toggleClass('hidden')">
                                    <i class="fa fa-fw fa-caret-up"></i>
                                </button>
                                <button type="button"
                                        class="btn btn-yura_primary btn-xs"
                                        title="Ejecutar" id="btn_ejecutar_continua_{{$anterior}}"
                                        onclick="cambiar_estado_labor_continua('{{$anterior}}', 'E')">
                                    <i class="fa fa-fw fa-check"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @php
                        $anterior = $item->nombre;
                        $fecha_desde = $item->fecha != '' ? $item->fecha : $semana->fecha_inicial;
                        $fecha_hasta = $item->fecha != '' ? $item->fecha : $semana->fecha_inicial;
                        $rep_ini = $item->app_repeticion;
                        $rep_fin = $item->app_repeticion;
                    @endphp
                @endif
            @endif
            <tr id="tr_proy_app_{{$item->id_proyeccion_campo_semanal_aplicacion}}"
                class="{{$item->app_continua == 1 ? 'hidden' : ''}} tr_app_continua_{{$anterior}}">
                <td class="text-center" style="border-color: #9d9d9d">
                    #{{$pos+1}}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{$item->nombre}}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="date" id="fecha_ejec_{{$item->id_proyeccion_campo_semanal_aplicacion}}"
                           value="{{($item->fecha != '' || $item->estado == 'C') ? $item->fecha : $semana->fecha_inicial}}" required
                           style="width: 100%;" class="text-center">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{$item->num_sem}}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="repeticion_ejec_{{$item->id_proyeccion_campo_semanal_aplicacion}}"
                           value="{{$item->app_repeticion}}" min="1"
                           required style="width: 100%;" class="text-center">
                </td>
                <td class="text-center {{$app_uso == 'C' ? 'hidden' : ''}}" style="border-color: #9d9d9d">
                    <input type="number" id="litro_x_cama_ejec_{{$item->id_proyeccion_campo_semanal_aplicacion}}" min="1"
                           value="{{$item->app_litro_x_cama}}" required style="width: 100%;" class="text-center">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="camas_ejec_{{$item->id_proyeccion_campo_semanal_aplicacion}}" min="1"
                           value="{{$item->camas}}" required style="width: 100%;" class="text-center">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="horas_trabajo_ejec_{{$item->id_proyeccion_campo_semanal_aplicacion}}" min="0"
                           value="{{$item->horas_trabajo}}" required style="width: 100%;" class="text-center">
                </td>
                <td class="text-center" style="border-color: #9d9d9d" id="td_estado_{{$item->id_proyeccion_campo_semanal_aplicacion}}">
                    <strong class="{{$estados['clase-'.$item->estado]}}">{{$estados[$item->estado]}}</strong>
                </td>
                @foreach($detalles as $det)
                    @php
                        $valor = '';
                        $unidad_medida = '';
                        $id_mano_obra = $det->id_mano_obra;
                        $id_producto = $det->id_producto;
                    @endphp
                    @foreach($item->detalles as $det_app)
                        @php
                            if ($det->id_producto != ''){ // insumo
                                if ($det_app->id_producto == $det->id_producto){
                                    $valor = $det_app->dosis;
                                    $unidad_medida = $det_app->id_unidad_medida;
                                    $id_mano_obra = $det_app->id_mano_obra;
                                    $id_producto = $det_app->id_producto;
                                }
                            } elseif ($det->id_mano_obra != ''){ // mano_obra
                                if ($det_app->id_mano_obra == $det->id_mano_obra){
                                    $valor = $det_app->rendimiento;
                                    $unidad_medida = $det_app->id_unidad_medida;
                                    $id_mano_obra = $det_app->id_mano_obra;
                                    $id_producto = $det_app->id_producto;
                                }
                            }
                        @endphp
                    @endforeach
                    <th class="text-center th_detalles_app hidden" style="border-left: 2px solid black;">
                        <input type="number" style="width: 100%" class="text-center" value="{{$valor}}" min="0"
                               ondblclick="update_detalle_app('{{$item->id_proyeccion_campo_semanal_aplicacion}}', '{{$id_mano_obra}}', '{{$id_producto}}', 'valor')"
                               id="valor_detalle_app_{{$id_mano_obra}}_{{$id_producto}}_{{$item->id_proyeccion_campo_semanal_aplicacion}}">
                    </th>
                    <th class="text-center th_detalles_app hidden" style="border-right: 2px solid black;">
                        <select style="width: 100%; height: 26px"
                                id="unidad_medida_detalle_app_{{$id_mano_obra}}_{{$id_producto}}_{{$item->id_proyeccion_campo_semanal_aplicacion}}"
                                ondblclick="update_detalle_app('{{$item->id_proyeccion_campo_semanal_aplicacion}}', '{{$id_mano_obra}}', '{{$id_producto}}', 'unidad_medida')">
                            <option value=""></option>
                            @foreach($unidades_medida as $um)
                                <option value="{{$um->id_unidad_medida}}" {{$um->id_unidad_medida == $unidad_medida ? 'selected' : ''}}>
                                    {{$um->siglas}}
                                </option>
                            @endforeach
                        </select>
                    </th>
                @endforeach
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button"
                                class="btn btn-yura_primary btn-xs {{in_array($item->estado, ['A', 'P', 'M']) ? '' : 'hidden'}}"
                                title="Ejecutar" id="btn_ejecutar_{{$item->id_proyeccion_campo_semanal_aplicacion}}"
                                onclick="cambiar_estado_labor('{{$item->id_proyeccion_campo_semanal_aplicacion}}', 'E', '{{$app_nombre}}', '{{$semana->codigo}}')">
                            <i class="fa fa-fw fa-check"></i>
                        </button>
                        <button type="button"
                                class="btn btn-yura_default btn-xs {{in_array($item->estado, ['A', 'P', 'M', 'E']) ? '' : 'hidden'}}"
                                title="Modificar" id="btn_editar_{{$item->id_proyeccion_campo_semanal_aplicacion}}"
                                onclick="modificar_labor('{{$item->id_proyeccion_campo_semanal_aplicacion}}')">
                            <i class="fa fa-fw fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-yura_warning btn-xs {{$item->estado != 'C' ? '' : 'hidden'}}" title="Cancelar"
                                id="btn_cancelar_{{$item->id_proyeccion_campo_semanal_aplicacion}}"
                                onclick="cambiar_estado_labor('{{$item->id_proyeccion_campo_semanal_aplicacion}}', 'C', '{{$app_nombre}}', '{{$semana->codigo}}')">
                            <i class="fa fa-fw fa-ban"></i>
                        </button>
                    </div>
                </td>
                <input type="hidden" class="ids_proyeccion_campo_semanal_aplicacion_{{$anterior}}"
                       value="{{$item->id_proyeccion_campo_semanal_aplicacion}}">
            </tr>
            @if($pos + 1 == count($listado) && $item->app_continua == 1 && $item->estado != 'C')
                <tr class="bg-yura_dark">
                    <td class="text-center" colspan="2">
                        {{$anterior}}
                    </td>
                    <td class="text-center" colspan="2">
                        <strong>{{$fecha_desde}}</strong> - <strong>{{$fecha_hasta}}</strong>
                    </td>
                    <td class="text-center" colspan="2">
                        <strong>{{$rep_ini}}</strong> - <strong>{{$rep_fin}}</strong>
                    </td>
                    <td colspan="3" class="text-right" style="padding-right: 5px">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_default"
                                    onclick="$('.tr_app_continua_{{$anterior}}').toggleClass('hidden')">
                                <i class="fa fa-fw fa-caret-up"></i>
                            </button>
                            <button type="button"
                                    class="btn btn-yura_primary btn-xs"
                                    title="Ejecutar" id="btn_ejecutar_continua_{{$anterior}}"
                                    onclick="cambiar_estado_labor_continua('{{$anterior}}', 'E')">
                                <i class="fa fa-fw fa-check"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @endif
        @endforeach
    </table>
</div>

<style>
    #tr_proy_app_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 9;
    }
</style>

<script>
    function cambiar_estado_labor(proy_app, estado, app_nombre, semana) {
        estados = {
            E: 'EJECUTAR',
            C: 'CANCELAR',
        };
        modal_quest('modal-quest_cambiar_estado_labor',
            '<div class="alert alert-warning text-center">¿Desea <strong>' + estados[estado] + '</strong> la labor?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '', function () {
                datos = {
                    _token: '{{csrf_token()}}',
                    proy_app: proy_app,
                    estado: estado,
                    fecha: $('#fecha_ejec_' + proy_app).val(),
                    repeticion: $('#repeticion_ejec_' + proy_app).val(),
                    litro_x_cama: $('#litro_x_cama_ejec_' + proy_app).val(),
                    camas: $('#camas_ejec_' + proy_app).val(),
                    horas_trabajo: $('#horas_trabajo_ejec_' + proy_app).val(),
                };
                $('#tr_proy_app_' + proy_app).LoadingOverlay('show');
                $.post('{{url('proyeccion_aplicaciones/cambiar_estado_labor')}}', datos, function (retorno) {
                    if (!retorno.success) {
                        alerta(retorno.mensaje);
                    } else {
                        if (estado == 'E') {
                            $('#btn_ejecutar_' + proy_app).addClass('hidden');
                            $('#btn_editar_' + proy_app).removeClass('hidden');
                            $('#td_estado_' + proy_app).html('<strong class="text-color_yura">Ejecutado</strong>');
                        }
                        if (estado == 'C') {
                            $('#btn_ejecutar_' + proy_app).addClass('hidden');
                            $('#btn_editar_' + proy_app).addClass('hidden');
                            $('#btn_cancelar_' + proy_app).addClass('hidden');
                            $('#td_estado_' + proy_app).html('<strong class="text-color_yura_danger">Cancelado</strong>');
                        }
                    }
                }, 'json').fail(function (retorno) {
                    console.log(retorno);
                    alerta_errores(retorno.responseText);
                }).always(function () {
                    $('#tr_proy_app_' + proy_app).LoadingOverlay('hide');
                });
            });
    }

    function cambiar_estado_labor_continua(modulo, estado) {
        estados = {
            E: 'EJECUTAR',
            C: 'CANCELAR',
        };
        modal_quest('modal-quest_cambiar_estado_labor',
            '<div class="alert alert-warning text-center">¿Desea <strong>' + estados[estado] + '</strong> la labor?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '', function () {
                ids_proy = $('.ids_proyeccion_campo_semanal_aplicacion_' + modulo);
                data = [];
                for (i = 0; i < ids_proy.length; i++) {
                    proy_app = ids_proy[i].value;
                    data.push({
                        proy_app: proy_app,
                        estado: estado,
                        fecha: $('#fecha_ejec_' + proy_app).val(),
                        repeticion: $('#repeticion_ejec_' + proy_app).val(),
                        litro_x_cama: $('#litro_x_cama_ejec_' + proy_app).val(),
                        camas: $('#camas_ejec_' + proy_app).val(),
                        horas_trabajo: $('#horas_trabajo_ejec_' + proy_app).val(),
                    });
                }
                $.LoadingOverlay('show');
                datos = {
                    _token: '{{csrf_token()}}',
                    data: data
                };
                $.post('{{url('proyeccion_aplicaciones/cambiar_estado_labor_continua')}}', datos, function (retorno) {
                    if (!retorno.success) {
                        alerta(retorno.mensaje);
                    } else {
                        for (i = 0; i < data.length; i++) {
                            proy_app = data[i]['proy_app'];
                            if (estado == 'E') {
                                $('#btn_ejecutar_' + proy_app).addClass('hidden');
                                $('#btn_editar_' + proy_app).removeClass('hidden');
                                $('#td_estado_' + proy_app).html('<strong class="text-color_yura">Ejecutado</strong>');
                            }
                            if (estado == 'C') {
                                $('#btn_ejecutar_' + proy_app).addClass('hidden');
                                $('#btn_editar_' + proy_app).addClass('hidden');
                                $('#btn_cancelar_' + proy_app).addClass('hidden');
                                $('#td_estado_' + proy_app).html('<strong class="text-color_yura_danger">Cancelado</strong>');
                            }
                        }
                    }
                }, 'json').fail(function (retorno) {
                    console.log(retorno);
                    alerta_errores(retorno.responseText);
                }).always(function () {
                    $.LoadingOverlay('hide');
                });
            });
    }

    function modificar_labor(proy_app) {
        datos = {
            _token: '{{csrf_token()}}',
            proy_app: proy_app,
            fecha: $('#fecha_ejec_' + proy_app).val(),
            repeticion: $('#repeticion_ejec_' + proy_app).val(),
            litro_x_cama: $('#litro_x_cama_ejec_' + proy_app).val(),
            camas: $('#camas_ejec_' + proy_app).val(),
            horas_trabajo: $('#horas_trabajo_ejec_' + proy_app).val(),
        };
        $('#tr_proy_app_' + proy_app).LoadingOverlay('show');
        $.post('{{url('proyeccion_aplicaciones/modificar_labor')}}', datos, function (retorno) {
            if (!retorno.success) {
                alerta(retorno.mensaje);
            }
        }, 'json').fail(function (retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function () {
            $('#tr_proy_app_' + proy_app).LoadingOverlay('hide');
        });
    }

    function add_adicional(semana, app_nombre) {
        datos = {
            semana: semana,
            app_nombre: app_nombre,
            variedad: $('#filtro_variedad').val(),
            uso: $('#filtro_uso').val(),
        };
        get_jquery('{{url('proyeccion_aplicaciones/add_adicional')}}', datos, function (retorno) {
            modal_view('modal-view_add_adicional', retorno, '<i class="fa fa-fw fa-plus"></i> Agregar labor', true, false, '85%');
        });
    }

    function update_detalle_app(id_proy, mo, prod, campo) {
        datos = {
            _token: '{{csrf_token()}}',
            id_proy: id_proy,
            campo: campo,
            mo: mo,
            prod: prod,
            valor: $('#' + campo + '_detalle_app_' + mo + '_' + prod + '_' + id_proy).val(),
        };
        $('#' + campo + '_detalle_app_' + mo + '_' + prod + '_' + id_proy).LoadingOverlay('show');
        $.post('{{url('proyeccion_aplicaciones/update_detalle_app')}}', datos, function (retorno) {
            if (!retorno.success) {
                alerta(retorno.mensaje)
            }
        }, 'json').fail(function (retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function () {
            $('#' + campo + '_detalle_app_' + mo + '_' + prod + '_' + id_proy).LoadingOverlay('hide');
        })
    }
</script>