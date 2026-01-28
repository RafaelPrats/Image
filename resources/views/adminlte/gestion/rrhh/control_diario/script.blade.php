<script>
buscar_control_diario();

function buscar_control_diario() {
    datos = {
        fecha: $('#fecha').val(),
       id_actividad: $('#id_actividad').val(),
      
    };
    $.LoadingOverlay('show');
    $.get('{{url('control_diario/buscar_control_diario')}}', datos,
        function(retorno) {
            $('#numero_semana').val(retorno.semana.codigo);

            if (retorno.model != null) {
                $('#inicio_hora_ordinario').val(retorno.model.inicio_hora_ordinario);
                $('#fin_hora_ordinario').val(retorno.model.fin_hora_ordinario);
                $('#inicio_hora_50').val(retorno.model.inicio_hora_50);
                $('#fin_hora_50').val(retorno.model.fin_hora_50);
                $('#inicio_hora_100').val(retorno.model.inicio_hora_100);
                $('#fin_hora_100').val(retorno.model.fin_hora_100);
                $('#inicio_hora_nocturno').val(retorno.model.inicio_hora_nocturno);
                $('#fin_hora_nocturno').val(retorno.model.fin_hora_nocturno);
                $('#id_control_diario').val(retorno.model.id_control_diario);
            } else {
                $('#inicio_hora_ordinario').val('');
                $('#fin_hora_ordinario').val('');
                $('#inicio_hora_50').val('');
                $('#fin_hora_50').val('');
                $('#inicio_hora_100').val('');
                $('#fin_hora_100').val('');
                $('#inicio_hora_nocturno').val('');
                $('#fin_hora_nocturno').val('');
                $('#id_control_diario').val('');
            }
            listar_grupo();
        $.LoadingOverlay('hide'); 
        }, 'json').fail(function(retorno) {
        console.log(retorno);
        alerta_errores(retorno.responseText);
    }).always(function() {
        $.LoadingOverlay('hide');
    });
}

function buscar_control_diario_general() {
    datos = {
        fecha: $('#fecha').val(),
    };
    $.LoadingOverlay('show');
    $.get('{{url('control_diario/buscar_control_diario_general')}}', datos,
        function(retorno) {
          // listar_grupo_general();
        $.LoadingOverlay('hide'); 
        }, 'json').fail(function(retorno) {
        console.log(retorno);
        alerta_errores(retorno.responseText);
    }).always(function() {
        $.LoadingOverlay('hide');
    });
}


function toggle(source) {
    var fecha = document.getElementById('desde_fijo').value;
  checkboxes = document.getElementsByName('myCheck');

  for(var i=0, n=checkboxes.length;i<n;i++) {
    checkboxes[i].checked = source.checked ;
    console.log(fecha)
    if('checked', true){
        
     document.getElementById("hora_inicio").value = document.getElementById("desde_fijo").value;
     document.getElementById("hora_fin").value = document.getElementById("hasta_fijo").value;
    }
  }
  
 
  
  function comprobar(target) {
  var textInput = target.closest('tr').getElementById('name');
  if (target.checked) {
    textInput.value = "1";
  } else {
    textInput.value = "0";
  }
}
}

function listar_grupo() {
    datos = {
        id_actividad: $('#id_actividad').val(),
        id_control_diario: $('#id_control_diario').val(),
    };
    get_jquery('{{url('control_diario/listar_grupo')}}', datos,
        function(retorno) {
            $('#div_control_diario').html(retorno);
            desde();
        });
}
function listar_grupo_general() {
    datos = {
        fecha: $('#fecha').val(),
    };
  
    get_jquery('{{url('control_diario/listar_grupo_general')}}', datos,
        function(retorno) {
            $('#div_control_diario').html(retorno);
          //  desde();
        });
}


function pasarValor() {
    $("#myCheck").prop('checked', true);
    if('checked', true){
        document.getElementById("hora_fin").value = document.getElementById("hasta_fijo").value;
    }
}


function guardar_horario() {
    datos = {
        _token: '{{csrf_token()}}',
        fecha: $('#fecha').val(),
        id_actividad: $('#id_actividad').val(),
        inicio_hora_ordinario: $('#inicio_hora_ordinario').val(),
        fin_hora_ordinario: $('#fin_hora_ordinario').val(),
        inicio_hora_50: $('#inicio_hora_50').val(),
        fin_hora_50: $('#fin_hora_50').val(),
        inicio_hora_100: $('#inicio_hora_100').val(),
        fin_hora_100: $('#fin_hora_100').val(),
        inicio_hora_nocturno: $('#inicio_hora_nocturno').val(),
        fin_hora_nocturno: $('#fin_hora_nocturno').val(),
    };
    post_jquery('{{url('control_diario/guardar_horario')}}', datos, function() {
        buscar_control_diario();
    });
}


</script>