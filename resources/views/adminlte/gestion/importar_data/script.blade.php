<script>
    /*function importar_postcosecha() {
        if ($('#form-importar_postcosecha').valid()) {
            $.LoadingOverlay('show');
            formulario = $('#form-importar_postcosecha');
            var formData = new FormData(formulario[0]);
            //hacemos la petición ajax
            $.ajax({
                url: formulario.attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                //necesario para subir archivos via ajax
                cache: false,
                contentType: false,
                processData: false,

                success: function (retorno2) {
                    notificar('Se ha importado un archivo', '{{url('importar_data')}}');
                    if (retorno2.success) {
                        $.LoadingOverlay('hide');
                        alerta_accion(retorno2.mensaje, function () {

                            location.reload();
                        });
                    } else {
                        alerta(retorno2.mensaje);
                        $.LoadingOverlay('hide');
                    }
                },
                //si ha ocurrido un error
                error: function (retorno2) {
                    console.log(retorno2);
                    alerta(retorno2.responseText);
                    alert('Hubo un problema en el envío de la información');
                    $.LoadingOverlay('hide');
                }
            });
        }
    }

    function importar_venta() {
        if ($('#form-importar_venta').valid()) {
            $.LoadingOverlay('show');
            formulario = $('#form-importar_venta');
            var formData = new FormData(formulario[0]);
            //hacemos la petición ajax
            $.ajax({
                url: formulario.attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                //necesario para subir archivos via ajax
                cache: false,
                contentType: false,
                processData: false,

                success: function (retorno2) {
                    notificar('Se ha importado un archivo', '{{url('importar_data')}}');
                    if (retorno2.success) {
                        $.LoadingOverlay('hide');
                        alerta_accion(retorno2.mensaje, function () {

                            location.reload();
                        });
                    } else {
                        alerta(retorno2.mensaje);
                        $.LoadingOverlay('hide');
                    }
                },
                //si ha ocurrido un error
                error: function (retorno2) {
                    console.log(retorno2);
                    alerta(retorno2.responseText);
                    alert('Hubo un problema en el envío de la información');
                    $.LoadingOverlay('hide');
                }
            });
        }
    }

    function importar_area() {
        if ($('#form-importar_area').valid()) {
            $.LoadingOverlay('show');
            formulario = $('#form-importar_area');
            var formData = new FormData(formulario[0]);
            //hacemos la petición ajax
            $.ajax({
                url: formulario.attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                //necesario para subir archivos via ajax
                cache: false,
                contentType: false,
                processData: false,

                success: function (retorno2) {
                    notificar('Se ha importado un archivo', '{{url('importar_data')}}');
                    if (retorno2.success) {
                        $.LoadingOverlay('hide');
                        alerta_accion(retorno2.mensaje, function () {
                            location.reload();
                        });
                    } else {
                        alerta(retorno2.mensaje);
                        $.LoadingOverlay('hide');
                    }
                },
                //si ha ocurrido un error
                error: function (retorno2) {
                    console.log(retorno2);
                    alerta(retorno2.responseText);
                    alert('Hubo un problema en el envío de la información');
                    $.LoadingOverlay('hide');
                }
            });
        }
    }

    function importar_personal_activo() {
        if ($('#form-importar_personal_activo').valid()) {
            $.LoadingOverlay('show');
            formulario = $('#form-importar_personal_activo');
            var formData = new FormData(formulario[0]);
            //hacemos la petición ajax
            $.ajax({
                url: formulario.attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                //necesario para subir archivos via ajax
                cache: false,
                contentType: false,
                processData: false,

                success: function (retorno2) {
                    notificar('Se ha importado un archivo', '{{url('importar_data')}}');
                    if (retorno2.success) {
                        $.LoadingOverlay('hide');
                        alerta_accion(retorno2.mensaje, function () {
                            location.reload();
                        });
                    } else {
                        alerta(retorno2.mensaje);
                        $.LoadingOverlay('hide');
                    }
                },
                //si ha ocurrido un error
                error: function (retorno2) {
                    console.log(retorno2);
                    alerta(retorno2.responseText);
                    alert('Hubo un problema en el envío de la información');
                    $.LoadingOverlay('hide');
                }
            });
        }
    }*/

    function importar_datos_comercializacion(){

        if ($('#form-datos-comercializacion').valid()) {

            $.LoadingOverlay('show');
            formulario = $('#form-datos-comercializacion');
            let formData = new FormData(formulario[0]);

            $.ajax({
                url: formulario.attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {

                    //notificar('Se ha importado un archivo', '{{url('importar_data')}}');
                    if (response.success) {
                        $.LoadingOverlay('hide');
                        alerta_accion(response.mensaje, function () {
                            location.reload();
                        });
                    } else {
                        alerta(response.mensaje);
                        $.LoadingOverlay('hide');
                    }
                },
                error: function (err) {
                    console.log(err)
                    alerta(err.responseText)
                    alert('Hubo un problema en el envío de la información')
                    $.LoadingOverlay('hide')
                }
            })
        }

    }

</script>
