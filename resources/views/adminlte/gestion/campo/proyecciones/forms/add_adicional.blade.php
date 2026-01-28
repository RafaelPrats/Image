<legend style="font-size: 1em" class="text-center">
    Agregar <strong>{{$aplicacion->nombre}}</strong> a la semana <strong>{{$semana->codigo}}</strong>
</legend>

<table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="text-center th_yura_green">
            Módulo
        </th>
        <th class="text-center th_yura_green">
            Fecha
        </th>
        <th class="text-center th_yura_green">
            Repetición
        </th>
        <th class="text-center th_yura_green {{$app_uso == 'C' ? 'hidden' : ''}}">
            Litros x cama
        </th>
        <th class="text-center th_yura_green">
            #Camas
        </th>
        <th class="text-center th_yura_green">
            Horas
        </th>
        <th class="text-center th_yura_green">
            Estado
        </th>
    </tr>
    <tr>
        <td class="text-center" style="border-color: #9d9d9d">
            <select id="id_proyeccion_campo_semanal_adicional" style="width: 100%" onchange="seleccionar_camas()">
                @foreach($modulos as $mod)
                    <option value="{{$mod->id_proyeccion_campo_semanal}}">
                        {{$mod->nombre}} - {{$mod->num_sem}}º
                    </option>
                @endforeach
            </select>
        </td>
        @foreach($modulos as $mod)
            <input type="hidden" value="{{$mod->area}}" id="area_modulo_{{$mod->id_proyeccion_campo_semanal}}">
        @endforeach
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="date" id="fecha_adicional" class="text-center" style="width: 100%" required value="{{$semana->fecha_inicial}}"
                   min="{{$semana->fecha_inicial}}"
                   max="{{$semana->fecha_final}}">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" id="repeticion_adicional" class="text-center" style="width: 100%" required placeholder="#" min="1">
        </td>
        <td class="text-center {{$app_uso == 'C' ? 'hidden' : ''}}" style="border-color: #9d9d9d">
            <input type="number" id="litro_x_cama_adicional" class="text-center" style="width: 100%" required placeholder="#" min="1">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" id="camas_adicional" class="text-center" style="width: 100%" required placeholder="#" min="1">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" id="horas_trabajo_adicional" class="text-center" style="width: 100%" required placeholder="#" min="0">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <select id="estado_adicional" style="width: 100%">
                <option value="A">Programado</option>
                <option value="E">Ejecutado</option>
            </select>
        </td>
    </tr>
</table>

<legend style="font-size: 1em; margin-top: 10px" class="text-center">
    Mezcla
</legend>

<div style="overflow-x: scroll">
    <table style="width: 100%; border: 1px solid #9d9d9d;" class="table-striped table-bordered">
        @foreach($detalles as $pos_det => $det)
            <tr>
                <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
                    {{$det['nombre_det']}}
                </th>
                <td class="text-center" style="border-color: #9d9d9d">
                    @php
                        $dosis = '';
                        $rendimiento = '';
                        if ($det['parametro'] != '')
                            if ($det['det']->id_producto != '')
                                $dosis = $det['parametro']->dosis;
                            else
                                $rendimiento = $det['parametro']->cantidad_mo;
                    @endphp
                    <input type="number" placeholder="{{$det['det']->id_producto != '' ? 'Dosis' : 'Rendimiento'}}" min="0"
                           title="{{$det['det']->id_producto != '' ? 'Dosis' : 'Rendimiento'}}" style="width: 100%"
                           class="text-center" value="{{$det['det']->id_producto != '' ? $dosis : $rendimiento}}" id="new_valor_{{$pos_det}}"
                           onchange="seleccionar_dosis_rendimiento('{{$det["det"]->id_producto != "" ? "dosis" : "rendimiento"}}', '{{$pos_det}}')">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <select style="width: 100%" id="new_id_unidad_medida_{{$pos_det}}" title="Unidad de Medida">
                        <option value="">Unidad de Medida</option>
                        @foreach($unidades_medida as $um)
                            <option value="{{$um->id_unidad_medida}}"
                                    {{$det['parametro'] != '' && $um->id_unidad_medida == $det['parametro']->id_unidad_medida ? 'selected' : ''}}>
                                {{$um->siglas}}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" placeholder="Factor conversión" title="Factor conversión" style="width: 100%" min="0"
                           class="text-center" value="{{$det['parametro'] != '' ? $det['parametro']->factor_conversion : ''}}"
                           id="new_factor_conversion_{{$pos_det}}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <select style="width: 100%" id="new_id_unidad_conversion_{{$pos_det}}" title="Unidad conversión">
                        <option value="">Unidad de Conversión</option>
                        @foreach($unidades_medida as $um)
                            <option value="{{$um->id_unidad_medida}}"
                                    {{$det['parametro'] != '' && $um->id_unidad_medida == $det['parametro']->id_unidad_conversion ? 'selected' : ''}}>
                                {{$um->siglas}}
                            </option>
                        @endforeach
                    </select>
                </td>
            </tr>
            <input type="hidden" id="new_id_producto_{{$pos_det}}" value="{{$det['det']->id_producto}}">
            <input type="hidden" id="new_id_mano_obra_{{$pos_det}}" value="{{$det['det']->id_mano_obra}}">
            <input type="hidden" id="new_dosis_{{$pos_det}}" value="{{$dosis}}">
            <input type="hidden" id="new_rendimiento_{{$pos_det}}" value="{{$rendimiento}}">
        @endforeach
    </table>
</div>

<div class="text-center" style="margin-top: 10px">
    <button type="button" class="btn btn-yura_primary" onclick="store_adicional('{{$aplicacion->nombre}}', {{$semana->codigo}})">
        <i class="fa fa-fw fa-save"></i> Guardar
    </button>
</div>

<script>
    seleccionar_camas();

    function seleccionar_dosis_rendimiento(campo, pos_det) {
        valor = $('#new_valor_' + pos_det).val();
        $('#new_' + campo + '_' + pos_det).val(valor);
    }

    function seleccionar_camas() {
        id_proy = $('#id_proyeccion_campo_semanal_adicional').val();
        m2 = $('#area_modulo_' + id_proy).val();
        camas = calcularCamas(m2);
        $('#camas_adicional').val(camas);
    }

    function store_adicional(app_nombre, semana) {
        detalles = [];
        count_detalles = '{{count($detalles)}}';
        for (i = 0; i < count_detalles; i++) {
            detalles.push({
                id_mo: $('#new_id_mano_obra_' + i).val(),
                id_producto: $('#new_id_producto_' + i).val(),
                id_um: $('#new_id_unidad_medida_' + i).val(),
                dosis: $('#new_dosis_' + i).val(),
                rendimiento: $('#new_rendimiento_' + i).val(),
                factor_conversion: $('#new_factor_conversion_' + i).val(),
                id_um_conversion: $('#new_id_unidad_conversion_' + i).val(),
            });
        }

        datos = {
            _token: '{{csrf_token()}}',
            app_uso: $('#filtro_uso').val(),
            app_nombre: app_nombre,
            id_proyeccion_campo_semanal: $('#id_proyeccion_campo_semanal_adicional').val(),
            fecha: $('#fecha_adicional').val(),
            repeticion: $('#repeticion_adicional').val(),
            litro_x_cama: $('#litro_x_cama_adicional').val(),
            estado: $('#estado_adicional').val(),
            camas: $('#camas_adicional').val(),
            horas_trabajo: $('#horas_trabajo_adicional').val(),
            detalles: detalles,
        };
        $.LoadingOverlay('show');
        post_jquery('{{url('proyeccion_aplicaciones/store_adicional')}}', datos, function () {
            cerrar_modals();
            cargar_labor_semanal(app_nombre, semana);
        });
        $.LoadingOverlay('hide');
    }
</script>