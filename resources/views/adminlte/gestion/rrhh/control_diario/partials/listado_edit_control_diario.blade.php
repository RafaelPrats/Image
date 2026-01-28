@if(count($listado) > 0)
    <div id="table_control_diario">
        <table class="table-responsive table-bordered"
            style="width: 100%; border: 1px solid #9d9d9d;  border-radius: 18px 0px 0px 0" 
            id="table_content_personal">
            <tr>
                <th class="text-center th_yura_green" style="border-color: white; border-radius:  18px 0 0 0">
                <i class="fa fa-fw fa-check"></i></th>
                <th class="text-center th_yura_green" style="border-color: white;">Nombre</th>
                <th class="text-center th_yura_green" style="border-color: white;">Apellido</th>
                <th class="text-center th_yura_green" style="border-color: white;">Grupo</th>
                <th class="text-center th_yura_green" style="border-color: white;">Desde
                <th class="text-center th_yura_green" style="border-color: white;">Hasta
                <th class="text-center th_yura_green"
                    style="border-color: white; width: 120px">Ausentismos
                </th>
                <th class="text-center th_yura_green"style="border-color: white; border-radius: 0  18px 0 0">
                    Opciones
                </th>
            </tr>
            @foreach($listado as $pos => $t)
                <input type="hidden" id="id_control_personal_{{$pos}}" value="{{$t->id_control_personal}}">
                <input type="hidden" id="id_personal_detalle_{{$pos}}" value="{{$t->id_personal_detalle}}">
            <tr id="tr_control_personal_{{$pos}}">
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="checkbox" checked id="check_trabajador_{{$pos}}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <span id="span_nombre_{{$pos}}">{{$t->personal_detalle->personal->nombre}}</span>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <span id="span_apellido_{{$pos}}">{{$t->personal_detalle->personal->apellido}}</span>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                <select id="id_actividades_{{$pos}}" style="width: 100%;">
                    @foreach($actividades as  $b)
                    <option value="{{$b->id_actividad}}" {{$b->area->id_area == $actividad ? 'selected' :''}}
                    {{$b->id_actividad == $actividad ? 'selected' :''}}>{{$b->area->nombre}} - {{$b->nombre}}</option>
                    @endforeach
                </select>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input class="input_hora_inicio text-center" type="time" style="width:100%" required
                        id="desde_{{$pos}}" value="{{$t->desde}}">
                </td>
                <td style="border-color: #9d9d9d">
                    <input class="input_hora_fin text-center" type="time" style="width:100%" required
                        id="hasta_{{$pos}}" value="{{$t->hasta}}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                <select  id="id_ausentismo_{{$pos}}" style="width: 100%;">
                <option value="">Seleccione</option>
            @foreach($ausentismos as $b)
            <option value="{{$b->id_ausentismo}}" {{$b->id_ausentismo == $t->id_ausentismo ? 'selected' :''}}>{{$b->nombre}}</option>
            @endforeach
            </select>
                </td>
                <td class="text-center" style="border-color: #9d9d9d;">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_dark" title="Copiar registro"
                            onclick="duplicar_registro('{{$pos}}')">
                            <i class="fa fa-fw fa-copy"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_primary" title="Actualizar registro"
                            onclick="update_control_personal('{{$pos}}')">
                            <i class="fa fa-fw fa-pencil"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </table>
        <input type="hidden" id="cant_registros" value="{{$pos}}">
    </div>

    <script>

function otra(){
    hora_inicio_fija = $('#desde_fijo').val();
    cant_registros = $('#cant_registros').val();
    for(i=0; i<=cant_registros; i++){
        if($('#check_trabajador_'+i).prop('checked') === true){
            $('#desde_'+i).val(hora_inicio_fija);
        }
    }
}


function hasta(){
    hora_final_fija = $('#hasta_fijo').val();
    cant_registros = $('#cant_registros').val();
    for(i=0; i<=cant_registros; i++){
        if($('#check_trabajador_'+i).prop('checked') === true){
            $('#hasta_'+i).val(hora_final_fija);
        }
    }
}

function duplicar_registro(pos){
    cant_registros = $('#cant_registros').val();
    cant_registros++;
    id_personal_detalle = $('#id_personal_detalle_'+pos).val();
    nombre = $('#span_nombre_'+pos).html();
    apellido = $('#span_apellido_'+pos).html();
    id_actividad = $('#id_actividades_'+pos).html();
    id_ausentismo = $('#id_ausentismo_'+pos).html();

    $('#table_content_personal').append('<tr id="tr_control_personal_'+cant_registros+'">'+
    '<td class="text-center" style="border-color: #9d9d9d">'+
    '<input type="hidden" id="id_control_personal_'+cant_registros+'">'+
    '<input type="hidden" id="id_personal_detalle_'+cant_registros+'" value="'+id_personal_detalle+'">'+
    '<input type="checkbox" checked id="check_trabajador_'+cant_registros+'"></td>'+
    '<td class="text-center" style="border-color: #9d9d9d">'+
    '<span id="span_nombre_'+cant_registros+'">'+nombre+'</span>'+
    '</td>'+    
    '<td class="text-center" style="border-color: #9d9d9d">'+
    '<span id="span_apellido_'+cant_registros+'">'+apellido+'</span>'+
    '</td>'+   
    '<td class="text-center" style="border-color: #9d9d9d">'+
                '<select id="id_actividades_'+cant_registros+'" style="width: 100%;">'+
                    @foreach($actividades as  $b)
                    ' <option value="{{$b->id_actividad}}" {{$b->area->id_area == $actividad ? 'selected' :''}}{{$b->id_actividad == $actividad ? 'selected' :''}}>{{$b->area->nombre}} - {{$b->nombre}}</option>'+
                    @endforeach
                '</select>'+
                '</td>'+ 
    '<td class="text-center" style="border-color: #9d9d9d">'+
    '<input class="input_hora_inicio text-center" type="time" style="width:100%" required id="desde_'+cant_registros+'">'
    +'</td>'+    
    '<td class="text-center" style="border-color: #9d9d9d">'+
    '<input class="input_hora_fin text-center" type="time" style="width:100%" required id="hasta_'+cant_registros+'">'
    +'</td>'+    
    '<td class="text-center" style="border-color: #9d9d9d">'+
                '<select id="id_ausentismo_'+cant_registros+'" style="width: 100%;">'+
                '<option value="">Seleccione</option>'+
                    @foreach($ausentismos as  $b)
                    '<option value="{{$b->id_ausentismo}}">{{$b->nombre}}</option>'+
                    @endforeach
                '</select>'+
                '</td>'+  
    '<td class="text-center" style="border-color: #9d9d9d">'+
        '<div class="btn-group">'+
            '<button type="button" class="btn btn-xs btn-yura_dark" title="Copiar registro"'+
                    'onclick="duplicar_registro('+cant_registros+')">'+
                    '<i class="fa fa-fw fa-copy"></i>'+
            '</button>'+
            '<button type="button" class="btn btn-xs btn-yura_primary" title="Guardar registro"'+
                    'onclick="store_control_personal('+cant_registros+')" id="btn_store_'+cant_registros+'">'+
                    '<i class="fa fa-fw fa-save"></i>'+
            '</button>'+
            '<button type="button" class="btn btn-xs btn-yura_primary hidden" title="Actualizar registro"'+
                    'onclick="update_control_personal('+cant_registros+')" id="btn_update_'+cant_registros+'">'+
                    '<i class="fa fa-fw fa-pencil"></i>'+
            '</button>'+
        '</div>'+
    '</td>'+    
    +'</tr>');

    $('#cant_registros').val(cant_registros);
}

function update_control_personal(pos) {
    datos = {
        _token: '{{csrf_token()}}',
        id: $('#id_control_personal_'+pos).val(),
        desde: $('#desde_'+pos).val(),
        hasta: $('#hasta_'+pos).val(),
        id_actividad: $('#id_actividades_'+pos).val(),
        id_ausentismo: $('#id_ausentismo_'+pos).val(),
    };
    $('#tr_control_personal_'+pos).LoadingOverlay('show');
    $.post('{{url('control_diario/update_control_personal')}}', datos, function(retorno){
        if(!retorno.success)
            alerta(retorno.mensaje);
    }, 'json').fail(function(retorno){
        console.log(retorno);
        alerta_errores(retorno.responseText);
    }).always(function(){
        $('#tr_control_personal_'+pos).LoadingOverlay('hide');
    })
}

function store_control_personal(pos) {
    datos = {
        _token: '{{csrf_token()}}',
        id_personal_detalle: $('#id_personal_detalle_'+pos).val(),
        id_control_diario: $('#id_control_diario').val(),
        id_actividades: $('#id_actividades_'+pos).val(),  
        desde: $('#desde_'+pos).val(),
        hasta: $('#hasta_'+pos).val(),
        id_ausentismo: $('#id_ausentismo'+pos).val(),
    };
    $('#tr_control_personal_'+pos).LoadingOverlay('show');
    $.post('{{url('control_diario/store_control_personal')}}', datos, function(retorno){
        if(!retorno.success)
            alerta(retorno.mensaje);
        else{
            $('#id_control_personal_'+pos).val(retorno.id);

            $('#btn_store_'+pos).addClass('hidden');
            $('#btn_update_'+pos).removeClass('hidden');
        }
    }, 'json').fail(function(retorno){
        console.log(retorno);
        alerta_errores(retorno.responseText);
    }).always(function(){
        $('#tr_control_personal_'+pos).LoadingOverlay('hide');
    })
}
</script>
@else
    <div class="alert alert-info text-center">No se han encontrado resultados</div>
@endif