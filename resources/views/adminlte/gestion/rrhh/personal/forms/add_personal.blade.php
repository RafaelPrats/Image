<form id="form_add_personal">
    <input type="hidden" id="id_personal" value=" {!! !empty($dataPersonal->nombre) != '' ? $dataPersonal->id_personal : '' !!}">
    
    <div class="row">
    <div class="col-md-2">
            <div class="form-group text-center">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" class="form-control input-yura_default" required maxlength="250" autocomplete="off" value='{!! !empty($dataPersonal->nombre) != '' ? $dataPersonal->nombre : '' !!}'>
            </div>
        </div>
       
    <div class="col-md-2">
        <div class="form-group">
                <label for="apellido">Apellido</label>
                <input type="text" id="apellido" name="apellido" class="form-control input-yura_default" required maxlength="250" autocomplete="off" value='{!! !empty($dataPersonal->apellido) != '' ? $dataPersonal->apellido : '' !!}'>
        </div>
        </div>
            
    <div class="col-md-2">
        <div class="form-group">
                <label for="cedula_identidad">Cédula de Identidad</label>
                <input type="cedula_identidad" id="cedula_identidad" name="cedula_identidad" class="form-control input-yura_default" required maxlength="250" autocomplete="off" value='{!! !empty($dataPersonal->cedula_identidad) != '' ? $dataPersonal->cedula_identidad : '' !!}'>
        </div>
    </div>
    <div class="col-md-2">
            <div class="form-group">
            <label for="fecha_nacimiento">Fecha de Nacimiento</label>             
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required class="form-control input-yura_default btn-sm" value='{!! !empty($fecha_nacimiento->fecha_nacimiento) != '' ? $dataPersonal->fecha_nacimiento : '' !!}'>
                </span>
            </div>
        </div>
    <div class="col-md-2">
        <div class="form-group">
                <label for="id_sexo">Sexo</label>
                <select name="id_sexo" id="id_sexo" class="form-control input-yura_default ">
                <option value="">seleccione</option>
            @foreach($sexo as $a)
            <option value="{{$a->id_sexo}}">{{$a->nombre}}</option>
            @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-2">
    <div class="form-group">
                <label for="grado_instruccion">Grado de Instruccion</label>
                <select name="grado_instruccion" id="id_grado_instruccion" class="form-control input-yura_default">
                <option>seleccione</option>
            @foreach($grado_instruccion as $p)
            <option value="{{$p->id_grado_instruccion}}">{{$p->nombre}}</option>
            @endforeach
            </select>
        </div>
    </div>
       
</div>

<div class="row">
<div class="col-md-2">
            <div class="form-group">
            <label for="fecha_ingreso">Fecha de Ingreso</label>             
                <input type="date" id="fecha_ingreso" name="fecha_ingreso" required class="form-control input-yura_default" value='{!! !empty($fecha_ingreso->fecha_ingreso) != '' ? $dataPersonal->fecha_ingreso : '' !!}'> 
                </span>
            </div>
        </div>
        <div class="col-md-2">
        <div class="form-group">
                <label for="id_detalle_contrato">Detalle de Contrato</label>
                <select name="id_detalle_contrato" id="id_detalle_contrato" class="form-control input-yura_default">
                <option>seleccione</option>
            @foreach($detalle_contrato as $b)
            <option value="{{$b->id_detalle_contrato}}">{{$b->nombre}}</option>
            @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
                <label for="estado_civil">Estado Civil</label>
                <select name="estado_civil" id="id_estado_civil" class="form-control input-yura_default ">
                <option>seleccione</option>
            @foreach($estado_civil as $a)
            <option value="{{$a->id_estado_civil}}">{{$a->nombre}}</option>
            @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
                <label for="nacionalidad">Nacionalidad</label>
                <select name="nacionalidad" id="id_nacionalidad" class="form-control input-yura_default">
                <option>seleccione</option>
            @foreach($nacionalidad as $b)
            <option value="{{$b->id_nacionalidad}}">{{$b->nombre}}</option>
            @endforeach
            </select>
        </div>
    </div>

    <div class="col-md-2">
            <div class="form-group">
                <label for="telef">Teléfono</label>
                <input type="text" id="telef" name="telef" class="form-control input-yura_default" required maxlength="250" autocomplete="off" value='{!! !empty($dataPersonal->telef) != '' ? $dataPersonal->telef : '' !!}'>
            </div>
        </div>


<div class="col-md-2">
            <div class="form-group">
                <label for="cargas_familiares">N° Cargas Flia</label>
                <input type="text" id="cargas_familiares" name="cargas_familiares" class="form-control input-yura_default" required maxlength="250" autocomplete="off" value='{!! !empty($dataPersonal->cargas_familiares) != '' ? $dataPersonal->cargas_familiares : '' !!}'>
            </div>
        </div>
</div>

<div class="row">
<div class="col-md-2">
        <div class="form-group">
                <label for="id_tipo_contrato">Tipo de Contrato</label>
                <select name="id_tipo_contrato" id="id_tipo_contrato" class="form-control input-yura_default">
                <option>seleccione</option>
            @foreach($tipo_contrato as $b)
            <option value="{{$b->id_tipo_contrato}}">{{$b->nombre}}</option>
            @endforeach
            </select>
        </div>
    </div>

<div class="col-md-2">
            <div class="form-group">
                <label for="lugar_residencia">Lugar de residencia</label>
                <input type="text" id="lugar_residencia" name="lugar_residencia" class="form-control input-yura_default" required maxlength="250" autocomplete="off" value='{!! !empty($dataPersonal->lugar_residencia) != '' ? $dataPersonal->lugar_residencia : '' !!}'>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="direccion">Dirección</label>
                <input type="text" id="direccion" name="direccion" class="form-control input-yura_default" required maxlength="250" autocomplete="off" value='{!! !empty($dataPersonal->direccion) != '' ? $dataPersonal->direccion : '' !!}'>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="correo">Correo</label>
                <input type="text" id="correo" name="correo" class="form-control input-yura_default" required maxlength="250" autocomplete="off" value='{!! !empty($dataPersonal->correo) != '' ? $dataPersonal->correo : '' !!}'>
            </div>
        </div>
        <div class="col-md-2">
        <div class="form-group">
                <label for="discapacidad">Discp.</label>
                <select id="discapacidad" class="form-control input-yura_default" 
                onchange="seleccionar_discapacidad()">
                <option value='N'>No</option>
                <option value='S'>Sí</option>
            </select>
        </div>
    </div>

        <div class="col-md-2">
        <div class="form-group">
            <label for="porcentaje_discapacidad">% de discp.</label>
            <input type="number" id="porcentaje_discapacidad" name="form-control" class="form-control input-yura_default" required 
            maxlength="250" autocomplete="off" disabled value=0>
        </div>
        </div>
        </div>

        <div class="row">
  <div class="col-md-2">
   <div class="form-group">
           <label for="cargo">Cargo</label>
           <select name="cargo" id="id_cargo" class="form-control input-yura_default">
           <option>seleccione</option>
       @foreach($cargo as $p)
       <option value="{{$p->id_cargo}}">{{$p->nombre}}</option>
       @endforeach
       </select>
   </div>
</div>

<div class="col-md-2">
   <div class="form-group">
           <label for="forma_pago">Forma de Pago</label>
           <select name="forma_pago" id="id_tipo_pago" class="form-control input-yura_default">
           <option>seleccione</option>
       @foreach($tipo_pago as $p)
       <option value="{{$p->id_tipo_pago}}">{{$p->nombre}}</option>
       @endforeach
       </select>
   </div>
</div>

<div class="col-md-2">
       <div class="form-group">
           <label for="sueldo">Sueldo</label>
           <input type="text" id="sueldo" name="sueldo" class="form-control input-yura_default" required maxlength="250" autocomplete="off" value='{!! !empty($dataPersonal->sueldo) != '' ? $dataPersonal->sueldo : '' !!}'>
       </div>
   </div>

<div class="col-md-2">
   <div class="form-group">
           <label for="id_banco">Banco</label>
           <select name="id_banco" id="id_banco" class="form-control input-yura_default">
           <option>seleccione</option>
       @foreach($banco as $p)
       <option value="{{$p->id_banco}}">{{$p->nombre}}</option>
       @endforeach
       </select>
   </div>
</div>

<div class="col-md-2">
   <div class="form-group">
           <label for="id_tipo_cuenta">Tipo_Cuenta</label>
           <select name="id_tipo_cuenta" id="id_tipo_cuenta" class="form-control input-yura_default">
           <option>seleccione</option>
       @foreach($tipo_cuenta as $p)
       <option value="{{$p->id_tipo_cuenta}}">{{$p->nombre}}</option>
       @endforeach
       </select>
   </div>
</div>
<div class="col-md-2">
       <div class="form-group">
           <label for="numero_cuenta">N° de Cuenta</label>
           <input type="text" id="numero_cuenta" name="numero_cuenta" class="form-control input-yura_default" required maxlength="250" autocomplete="off" value='{!! !empty($dataPersonal->numero_cuenta) != '' ? $dataPersonal->numero_cuenta : '' !!}'>
       </div>
   </div>

</div>
  

<div class="row">
    <div class="col-md-2">
        <div class="form-group">
                <label for="id_departamento">departamento</label>
                <select name="id_departamento" id="id_departamento" class="form-control input-yura_default">
                <option>seleccione</option>
            @foreach($departamento as $p)
            <option value="{{$p->id_departamento}}">{{$p->nombre}}</option>
            @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
                <label for="sucursal">Sucursal</label>
                <select name="sucursal" id="id_sucursal" class="form-control input-yura_default">
                <option>seleccione</option>
            @foreach($sucursal as $p)
            <option value="{{$p->id_sucursal}}">{{$p->nombre}}</option>
            @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-2">
    <div class="form-group">
                <label for="tipo_rol">Rol</label>
                <select name="tipo_rol" id="id_tipo_rol" class="form-control input-yura_default">
                <option>seleccione</option>
            @foreach($tipo_rol as $p)
            <option value="{{$p->id_tipo_rol}}">{{$p->nombre}}</option>
            @endforeach
            </select>

    </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
                <label for="id_area">Área</label>
                <select name="id_area" id="id_area" class="form-control input-yura_default" 
                    onchange="seleccionar_area()">
                <option>seleccione</option>
            @foreach($area as $p)
            <option value="{{$p->id_area}}">{{$p->nombre}}</option>
            @endforeach
            </select>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="form-group">
                <label for="id_actividad">Actividad</label>
                <select name="id_actividad" id="id_actividad" class="form-control input-yura_default"
                    onchange="seleccionar_actividad()">
                <option>seleccione</option>
            </select>
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
                <label for="id_mano_obra">Mano de Obra</label>
                <select name="id_mano_obra" id="id_mano_obra" class="form-control input-yura_default">
                <option>seleccione</option>
            </select>
        </div>
    </div>
</div>

    <div class="row text-center aling-center">
    <div class="col-md-2">
        <div class="form-group">
                <label for="grupo">Agrupación</label>
                <select name="grupo" id="id_grupo" class="form-control input-yura_default">
                <option>seleccione</option>
            @foreach($grupo as $p)
            <option value="{{$p->id_grupo}}">{{$p->nombre}}</option>
            @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
                <label for="grupo_interno">Grupo Interno</label>
                <select name="grupo_interno" id="id_grupo_interno" class="form-control input-yura_default">
                <option>seleccione</option>
            @foreach($grupo_interno as $p)
            <option value="{{$p->id_grupo_interno}}">{{$p->nombre}}</option>
            @endforeach
            </select>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="form-group">
                <label for="id_plantilla">Plantilla</label>
                <select name="id_plantilla" id="id_plantilla" class="form-control input-yura_default">
                <option>seleccione</option>
            @foreach($plantilla as $p)
            <option value="{{$p->id_plantilla}}">{{$p->nombre}}</option>
            @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
                <label for="id_relacion_laboral">Relación Laboral</label>
                <select name="id_relacion_laboral" id="id_relacion_laboral" class="form-control input-yura_default">
                <option>seleccione</option>
            @foreach($relacion_laboral as $p)
            <option value="{{$p->id_relacion_laboral}}">{{$p->nombre}}</option>
            @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
                <label for="id_seguro">Seguro</label>
                <select name="id_seguro" id="id_seguro" class="form-control input-yura_default">
                <option>seleccione</option>
            @foreach($seguro as $p)
            <option value="{{$p->id_seguro}}">{{$p->nombre}}</option>
            @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-2">
            <div class="form-group text-center">
                <label for="n_afiliacion">N° de Afiliación</label>
                <input type="text" id="n_afiliacion" name="n_afiliacion" class="form-control input-yura_default" required maxlength="250" autocomplete="off" value='{!! !empty($dataPersonal->nombre) != '' ? $dataPersonal->nombre : '' !!}'>
            </div>
        </div>
</div>
</form>
<div class="text-center">
    <button type="button" class="btn btn-yura_primary" onclick="crear_personal()">
        <i class="fa fa-fw fa-save"></i> Guardar
    </button>
</div>

<script>
    function crear_personal(){
        datos = {
            _token: '{{csrf_token()}}',
            nombre: $('#nombre').val(),
            apellido: $('#apellido').val(),
            cedula_identidad: $('#cedula_identidad').val(),
            id_departamento: $('#id_departamento').val(),
            id_sexo: $('#id_sexo').val(),
            fecha_nacimiento: $('#fecha_nacimiento').val(),
            fecha_ingreso: $('#fecha_ingreso').val(),
            id_estado_civil: $('#id_estado_civil').val(),
            id_nacionalidad: $('#id_nacionalidad').val(),
            telef: $('#telef').val(),
            cargas_familiares: $('#cargas_familiares').val(),
            id_tipo_contrato: $('#id_tipo_contrato').val(),
            lugar_residencia: $('#lugar_residencia').val(),
            direccion: $('#direccion').val(),
            correo: $('#correo').val(),
            discapacidad: $('#discapacidad').val(),
            porcentaje_discapacidad: $('#porcentaje_discapacidad').val(),
            id_cargo: $('#id_cargo').val(),
            sueldo: $('#sueldo').val(),
            id_banco: $('#id_banco').val(),
            id_tipo_cuenta: $('#id_tipo_cuenta').val(),
            id_tipo_rol: $('#id_tipo_rol').val(),
            id_tipo_pago: $('#id_tipo_pago').val(),
            numero_cuenta: $('#numero_cuenta').val(),
            id_grado_instruccion: $('#id_grado_instruccion').val(),
            id_sucursal: $('#id_sucursal').val(),
            id_grupo: $('#id_grupo').val(),
            id_grupo_interno: $('#id_grupo_interno').val(),
            id_area: $('#id_area').val(),
            id_actividad: $('#id_actividad').val(),
            id_mano_obra: $('#id_mano_obra').val(),
            id_plantilla: $('#id_plantilla').val(),
            id_relacion_laboral: $('#id_relacion_laboral').val(),
            id_detalle_contrato: $('#id_detalle_contrato').val(),
            id_seguro: $('#id_seguro').val(),
            n_afiliacion: $('#n_afiliacion').val(),
        };
        post_jquery('{{url('personal/store_personal')}}', datos, function () {
            $('#tipo').val('cedula_identidad');
            $('#estado').val('1');
            $('#busqueda_personal').val(datos['cedula_identidad']);

            trabajador();        
        cerrar_modals();
        });
    }
      
    function seleccionar_discapacidad(){
        if($('#discapacidad').val() == 'S')
            $('#porcentaje_discapacidad').prop('disabled', false)
        else
            $('#porcentaje_discapacidad').prop('disabled', true)
    }

    function seleccionar_area(){
        datos={
            id_area:$('#id_area').val(),
        }
        get_jquery('{{url('personal/seleccionar_area')}}', datos, function(retorno){
            $('#id_actividad').html(retorno);
        })
    }

    function seleccionar_actividad(){
        datos={
            id_actividad:$('#id_actividad').val(),
        }
        get_jquery('{{url('personal/seleccionar_actividad')}}', datos, function(retorno){
            $('#id_mano_obra').html(retorno);
        })
    }
</script>