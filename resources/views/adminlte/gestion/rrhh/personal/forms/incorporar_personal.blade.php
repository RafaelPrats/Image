<form id="form_incorporar_personal">

    <div class="row">
        <div class="col-md-2">
            <div class="form-group text-center">
                <label for="nombre">Nombre</label>
                <input autocomplete="off" disabled type="text" id="nombre" name="nombre"
                       class="form-control input-yura_default" required maxlength="250"
                       value="{{$dataPersonal->nombre}}">
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group">
                <label for="apellido">Apellido</label>
                <input autocomplete="off" disabled type="text" id="apellido" name="apellido"
                       class="form-control input-yura_default" required maxlength="250"
                       value="{{$dataPersonal->apellido}}">
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group">
                <label for="cedula_identidad">Cédula de Identidad</label>
                <input disabled type="number" id="cedula_identidad" name="cedula_identidad"
                       class="form-control input-yura_default" required maxlength="250" autocomplete="off"
                       value="{{$dataPersonal->cedula_identidad}}">
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group">
                <label for="fecha_desvinculacion">F. de Reingreso</label>
                <input type="date" id="fecha_ingreso" name="fecha_ingreso" required
                       class="form-control input-yura_default">
            </div>
        </div>



    </div>
         

</form>
<div class="text-center">
    <button type="button" class="btn btn-yura_primary" onclick="reincorporar_personal()">
        <i class="fa fa-fw fa-save"></i> Guardar
    </button>
</div>
<div style="display: none">
<input type="hidden" id="fecha_nacimiento" name="fecha_nacimiento" required class="form-control input-yura_default btn-sm" 
                value="{{$dataPersonal->fecha_nacimiento}}">

                <select name="id_sexo" id="id_sexo" class="form-control input-yura_default"   style="visibility:hidden">
            <option value="{{$dataPersonal->id_sexo}}" {{$dataPersonal->id_sexo == $dataPersonal->id_sexo ? 'selected' : ''}}>{{$dataPersonal->nombre}}</option>
            </select>
       
                <select name="grado_instruccion" id="id_grado_instruccion" class="form-control input-yura_default"  style="visibility:hidden">
            <option value="{{$detalle->id_grado_instruccion}}"{{$detalle->id_grado_instruccion == $detalle->id_grado_instruccion ? 'selected' : ''}}>{{$detalle->nombre}}</option>
            </select>
        
              
                <select name="id_detalle_contrato" id="id_detalle_contrato" class="form-control input-yura_default"  style="visibility:hidden">
            <option value="{{$detalle->id_detalle_contrato}}">{{$detalle->nombre}}</option>
                      </select>
    
                <select name="estado_civil" id="id_estado_civil" class="form-control input-yura_default"  style="visibility:hidden">
            <option value="{{$detalle->id_estado_civil}}"{{$detalle->id_estado_civil == $detalle->id_estado_civil ? 'selected' : ''}}>{{$detalle->nombre}}</option>
            </select>
        
                <select name="nacionalidad" id="id_nacionalidad" class="form-control input-yura_default"  style="visibility:hidden">
            <option value="{{$dataPersonal->id_nacionalidad}}">{{$dataPersonal->nombre}}</option>
          
       
                <input type="hidden" id="telef" name="telef" class="form-control input-yura_default" required maxlength="250" autocomplete="off" 
                value="{{$detalle->telef}}">
       
                <input type="hidden"  id="cargas_familiares" name="cargas_familiares" class="form-control input-yura_default" required maxlength="250" autocomplete="off"
                value="{{$detalle->cargas_familiares}}">
    

                <select name="id_tipo_contrato" id="id_tipo_contrato" class="form-control input-yura_default"  style="visibility:hidden">
            <option value="{{$detalle->id_tipo_contrato}}">{{$detalle->nombre}}</option>

        
                <input type="hidden" id="lugar_residencia" name="lugar_residencia" class="form-control input-yura_default" required maxlength="250" autocomplete="off"
                value="{{$detalle->lugar_residencia}}">

                <input type="hidden" id="direccion" name="direccion" class="form-control input-yura_default" required maxlength="250" autocomplete="off"
                value="{{$detalle->direccion}}">
          
                <input type="hidden" id="correo" name="correo" class="form-control input-yura_default" required maxlength="250" autocomplete="off"
                value="{{$detalle->correo}}">
         
                <select id="discapacidad" class="form-control input-yura_default" 
                onchange="seleccionar_discapacidad()"  style="visibility:hidden">
                <option value='N' {{$detalle->discapacidad == 'N' ? 'selected' : ''}}>No</option>
                <option value='S' {{$detalle->discapacidad == 'S' ? 'selected' : ''}}>Si</option>
            </select>
      
            <input type="hidden" id="porcentaje_discapacidad" name="form-control" class="form-control input-yura_default" required 
            maxlength="250" autocomplete="off" disabled   value="{{$detalle->porcentaje_discapacidad}}">
       
                <select name="cargo" id="id_cargo" class="form-control input-yura_default"  style="visibility:hidden">
            <option value="{{$detalle->id_cargo}}">{{$detalle->nombre}}</option>
            </select>
      
                <select name="forma_pago" id="id_tipo_pago" class="form-control input-yura_default"  style="visibility:hidden">
            <option value="{{$detalle->id_tipo_pago}}">{{$detalle->nombre}}</option>
                  </select>
      
               
                <input type="hidden" id="sueldo" name="sueldo" class="form-control input-yura_default" required maxlength="250" autocomplete="off"
                value="{{$detalle->sueldo}}">   
         
                <select name="id_banco" id="id_banco" class="form-control input-yura_default"  style="visibility:hidden">
            <option value="{{$detalle->id_banco}}"{{$detalle->id_banco == $detalle->id_banco ? 'selected' : ''}}>{{$detalle->nombre}}</option>
            </select>
     
                <select name="id_tipo_cuenta" id="id_tipo_cuenta" class="form-control input-yura_default"  style="visibility:hidden">
            <option value="{{$detalle->id_tipo_cuenta}}"{{$detalle->id_tipo_cuenta == $detalle->id_tipo_cuenta ? 'selected' : ''}}>{{$detalle->nombre}}</option>
            </select>
     
                <input type="hidden" id="numero_cuenta" name="numero_cuenta" class="form-control input-yura_default" required maxlength="250" autocomplete="off"
                value="{{$detalle->numero_cuenta}}">
          
                <select name="tipo_rol" id="id_tipo_rol" class="form-control input-yura_default"  style="visibility:hidden">
            <option value="{{$detalle->id_tipo_rol}}"{{$detalle->id_tipo_rol == $detalle->id_tipo_rol ? 'selected' : ''}}>{{$detalle->nombre}}</option>
            </select>
     
                <select name="sucursal" id="id_sucursal" class="form-control input-yura_default"  style="visibility:hidden">
            <option value="{{$detalle->id_sucursal}}"{{$detalle->id_sucursal == $detalle->id_sucursal ? 'selected' : ''}}>{{$detalle->nombre}}</option>
            </select>
      
                <select name="id_departamento" id="id_departamento" class="form-control input-yura_default"  style="visibility:hidden">
            <option value="{{$detalle->id_departamento}}"{{$detalle->id_departamento == $detalle->id_departamento ? 'selected' : ''}}>{{$detalle->nombre}}</option>
            </select>
   
                <select name="id_area" id="id_area" class="form-control input-yura_default" 
                    onchange="seleccionar_area()"  style="visibility:hidden">
            <option value="{{$detalle->id_area}}"{{$detalle->id_area == $detalle->id_area ? 'selected' : ''}}>{{$detalle->nombre}}</option>
            </select>
      
                <select name="id_actividad" id="id_actividad" class="form-control input-yura_default"
                    onchange="seleccionar_actividad()"  style="visibility:hidden">
            <option value="{{$detalle->id_actividad}}"{{$detalle->id_actividad == $detalle->id_actividad ? 'selected' : ''}}>{{$detalle->nombre}}</option>
            </select>
       
                <select name="id_mano_obra" id="id_mano_obra" class="form-control input-yura_default"  style="visibility:hidden">
            <option value="{{$detalle->id_mano_obra}}"{{$detalle->id_mano_obra == $detalle->id_mano_obra ? 'selected' : ''}}>{{$detalle->nombre}}</option>
            </select>
     
                <select name="grupo" id="id_grupo" class="form-control input-yura_default"  style="visibility:hidden">
            <option value="{{$detalle->id_grupo}}"{{$detalle->id_grupo == $detalle->id_grupo ? 'selected' : ''}}>{{$detalle->nombre}}</option>
            </select>
      
                <select name="grupo_interno" id="id_grupo_interno" class="form-control input-yura_default"  style="visibility:hidden">
            <option value="{{$detalle->id_grupo_interno}}"{{$detalle->id_grupo_interno == $detalle->id_grupo_interno ? 'selected' : ''}}>{{$detalle->nombre}}</option>
            </select>
      
                <select name="id_plantilla" id="id_plantilla" class="form-control input-yura_default"  style="visibility:hidden">
            <option value="{{$detalle->id_plantilla}}" {{$detalle->id_plantilla == $detalle->id_plantilla ? 'selected' : ''}}>{{$detalle->nombre}}</option>
            </select>
      
                <select name="id_relacion_laboral" id="id_relacion_laboral" class="form-control input-yura_default"  style="visibility:hidden">
            <option value="{{$detalle->id_relacion_laboral}}">{{$detalle->nombre}}</option>
            </select>
        
                <select name="id_seguro" id="id_seguro" class="form-control input-yura_default"  style="visibility:hidden">
            <option value="{{$detalle->id_seguro}}">{{$detalle->nombre}}</option>
            </select>
      
                <input type="hidden" id="n_afiliacion" name="n_afiliacion" class="form-control input-yura_default" required maxlength="250" autocomplete="off" 
                value="{{$detalle->n_afiliacion}}">

                </div>
               
<input type="hidden" id="id_personal" value="{{$dataPersonal->id_personal}}">
<input type="hidden" id="id_personal_detalle" value="{{$detalle->id_personal_detalle}}">
<script>
    function reincorporar_personal(){
        datos = {
            _token: '{{csrf_token()}}',
            id_personal: $('#id_personal').val(),
            nombre: $('#nombre').val(),
            apellido: $('#apellido').val(),
            cedula_identidad: $('#cedula_identidad').val(),
            fecha_nacimiento: $('#fecha_nacimiento').val(),
            id_sexo: $('#id_sexo').val(),
            fecha_ingreso: $('#fecha_ingreso').val(),
            id_grado_instruccion: $('#id_grado_instruccion').val(),
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
            id_tipo_pago: $('#id_tipo_pago').val(),
            sueldo: $('#sueldo').val(),
            id_banco: $('#id_banco').val(),
            id_tipo_cuenta: $('#id_tipo_cuenta').val(),
            numero_cuenta: $('#numero_cuenta').val(),
            id_tipo_rol: $('#id_tipo_rol').val(),
            id_sucursal: $('#id_sucursal').val(),
            id_departamento: $('#id_departamento').val(),
            id_area: $('#id_area').val(),
            id_actividad: $('#id_actividad').val(),
            id_mano_obra: $('#id_mano_obra').val(),
            id_grupo: $('#id_grupo').val(),
            id_grupo_interno: $('#id_grupo_interno').val(),
            id_plantilla: $('#id_plantilla').val(),
            id_relacion_laboral: $('#id_relacion_laboral').val(),
            id_detalle_contrato: $('#id_detalle_contrato').val(),
            id_seguro: $('#id_seguro').val(),
            n_afiliacion: $('#n_afiliacion').val(),
            
        };
        post_jquery('{{url('personal/reincorporar_personal')}}', datos, function () {  
                    
        cerrar_modals();
        trabajador();
       
        });
    }
      
