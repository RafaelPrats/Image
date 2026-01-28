@if(count($listado) > 0)
    <div id="table_control_diario_general">
        <table class="table-responsive table-bordered"
            style="width: 100%; border: 1px solid #9d9d9d;  border-radius: 18px 0px 0px 0" 
            id="table_control_diario_general">
            <tr>
                <th class="text-center th_yura_green" style="border-color: white; border-radius:  18px 0 0 0">
                <i class="fa fa-fw fa-check"></i></th>
                <th class="text-center th_yura_green" style="border-color: white;">Nombre</th>
                <th class="text-center th_yura_green" style="border-color: white;">Apellido</th>
                <th class="text-center th_yura_green" style="border-color: white;">Grupo</th>
                <th class="text-center th_yura_green" style="border-color: white;">Desde</th>
                <th class="text-center th_yura_green" style="border-color: white;">Hasta</th>
                <th class="text-center th_yura_green"
                    style="border-color: white; width: 120px">Ausentismos
                </th>
                <th class="text-center th_yura_green"style="border-color: white; border-radius: 0  18px 0 0">
                    Opciones
                </th>
            </tr>
            </tr>
            @foreach($listado as $pos => $t)
                <input type="hidden" id="id_personal_detalle_{{$pos}}" value="{{$t->id_personal_detalle}}">
            <tr>
            <tr>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="checkbox" checked id="check_trabajador_{{$pos}}">
                </td>
                <td class="text-center-sm" style="border-color: #9d9d9d">
                    <span id="span_nombre_{{$pos}}">{{$t->personal->nombre}}</span>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <span id="span_apellido_{{$pos}}">{{$t->personal->apellido}}</span>
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
                        id="desde_{{$pos}}">
                </td>
                <td style="border-color: #9d9d9d">
                    <input class="input_hora_fin text-center" type="time" style="width:100%" required
                        id="hasta_{{$pos}}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                <select id="id_ausentismo_{{$pos}}" style="width: 100%;">
                <option value="">Seleccione</option>    
            @foreach($ausentismos as $b)
            <option value="{{$b->id_ausentismo}}">{{$b->nombre}}</option>
            @endforeach
            </select>
                </td>
                <td class="text-center" style="border-color: #9d9d9d;">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_dark" title="Copiar registro"
                            onclick="duplicar_registro('{{$pos}}')">
                            <i class="fa fa-fw fa-copy"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </table>
        <input type="hidden" id="cant_registros" value="{{$pos}}">
        
        <div class="text-center">
            <button type="button" class="btn btn-yura_primary"  style="margin-top:5px" 
                onclick="guardar_control_personal()">
                <i class="fa fa-fw fa-save"></i> Guardar
            </button>
        </div>
    </div>

    <script>

function otra(){
    //alert(7777)
    hora_inicio_fija = $('#desde_fijo').val();
  //  alert(hora_final_fija)
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

        $('#table_content_personal').append('<tr>'+
    '<td class="text-center" style="border-color: #9d9d9d">'+
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
                    '@foreach($actividades as  $b)'+
                    ' <option value="{{$b->id_actividad}}" {{$b->area->id_area == $actividad ? 'selected' :''}}{{$b->id_actividad == $actividad ? 'selected' :''}}>{{$b->area->nombre}} - {{$b->nombre}}</option>'+
                    '@endforeach'+
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
        '</div>'+
    '</td>'+    
    +'</tr>');

    $('#cant_registros').val(cant_registros);
}
function guardar_control_personal() {
    cant_registros = $('#cant_registros').val();
    data = [];
    for(i=0; i<=cant_registros; i++){
        if($('#check_trabajador_'+i).prop('checked') === true && $('#desde_'+i).val() != '' && $('#hasta_'+i).val() != ''){
            data.push({
                id_personal_detalle: $('#id_personal_detalle_'+i).val(),
                desde: $('#desde_'+i).val(),
                hasta: $('#hasta_'+i).val(), 
               id_actividades: $('#id_actividades_'+i).val(),  
                id_ausentismo: $('#id_ausentismo_'+i).val(),     
            });
        }
    }   
    if(data.length > 0){
        datos = {
            _token: '{{csrf_token()}}',
            data: data,
            id_control_diario: $('#id_control_diario').val(),
        }
        if($('#id_control_diario').val() != '')
            post_jquery('{{url('control_diario/guardar_control_personal')}}', datos, function(){
                listar_grupo();
            });
        else
            alerta('<div class="alert alert-warning text-center">Debe crear el control diario para la actividad</div>')
    } else {
        alerta('<div class="alert alert-warning text-center">Debe completar los campos obligatorios</div>')
    }
}

</script>
@else
    <div class="alert alert-info text-center">No se han encontrado resultados</div>
@endif