/* ================ OTRAS FUNCIONES ======================== */

const dominio = location.origin;

function add_pedido(id_cliente, pedido_fijo, vista, id_pedido, tipo) {

    return new Promise(function (resolve, reject) {

        datos = {
            id_cliente,
            pedido_fijo,
            vista,
            id_pedido,
            tipo
        };
        get_jquery('/clientes/add_pedido', datos, function (retorno) {
            modal_view('modal_add_pedido', retorno, '<i class="fa fa-fw fa-plus"></i> Agregar pedido', true, false, '100%');
            id_cliente !== '' ? add_campos(id_cliente) : '';
            pedido_fijo != '' ? div_opcion_pedido_fijo(1) : '';
            resolve()
            setTimeout(function () {
                vista == 'pedidos' ? $("#btn_add_campos").attr('disabled', true) : '';
            }, 500);
        });

    });
}

function add_campos(id_cliente, id_pedido, datos_exportacion = []) {

    datos = {
        id_cliente: id_cliente == "" ? $("#id_cliente_venta").val() : id_cliente,
        id_pedido,
        id_planta: $("#filtro_planta").val(),
        id_caja: $("#filtro_caja").val(),
        id_variedad: $("#filtro_variedad").val(),
        rxc: $("#search_rxc").val(),
        datos_exportacion
    };
    $.get('/clientes/inputs_pedidos', datos, function (retorno) {
        $('#table_campo_pedido').html(retorno);
        if ($("#id_cliente_venta").length > 0) {
            cargar_espeicificaciones_cliente(false);
        }
    }).always(function () {
        $('#table_campo_pedido').LoadingOverlay('hide');
    });
}

function delete_campos(value) {
    $.LoadingOverlay('show');
    var cant_tr = $('tbody#tbody_inputs_pedidos tr').length;

    if ($("#tr_inputs_pedido_" + cant_tr + " input#cantidad_" + cant_tr).val().length < 1) {
        var tr = $("tbody tr#tr_inputs_pedido_" + cant_tr);
        tr.remove();
        if (cant_tr == 2) {
            $('#btn_delete_inputs').addClass('hide');
        }
    } else {
        $('#btn_delete_inputs').addClass('hide')
    }
    $.LoadingOverlay('hide');
}

function div_opcion_pedido_fijo(opcion) {
    $.LoadingOverlay('show');
    datos = {
        opcion: opcion,
    };
    $.get('clientes/opcion_pedido_fijo', datos, function (retorno) {
        $('#div_opciones_pedido_fijo').html(retorno);
    }).always(function () {
        $.LoadingOverlay('hide');
    });
}

function pushSemanas(opcion, arrSemanas) {
    if (opcion == 2 || opcion == 1) {
        $("select#intervalo option#options_dinamics").remove();
        $.each(arrSemanas, function (i, j) {
            $("select#intervalo").append(`<option id="options_dinamics" ${i == 0 ? 'selected' : ''} value="${(i + 1)}">${j}</option>`)
        });

    }
}

function verificar_intervalo_fecha() {

    if ($("#fecha_desde_pedido_fijo").val() != '') {
        $("#intervalo").prop('disabled', false)
        $("#fecha_hasta_pedido_fijo").val(moment($("#fecha_desde_pedido_fijo").val()).add(3, 'months').format('YYYY-MM-DD'))
    }

    var fechaDesde = moment($("#fecha_desde_pedido_fijo").val());
    var fechaHasta = moment($("#fecha_hasta_pedido_fijo").val());

    var diferenciaDias = fechaHasta.diff(fechaDesde, 'days')

    var fechaFormateada = $('#fecha_desde_pedido_fijo').val().replace('/-/g', '/');
    let date = new Date(fechaFormateada);

    var p = 0;
    for (var x = 0; x < diferenciaDias + 2; x++) {
        var fechas = (date.getMonth() + 1) + "/" + date.getDate() + "/" + date.getFullYear();
        date.setDate(date.getDate() + 1);
        var d = new Date(fechas);
        if (d.getDay() === parseInt($("#dia_semana").val().trim())) {
            p++
        }
    }
    var arrSemanas = [];
    if (p > 0) {
        for (var i = 0; i < p; i++) {
            var plu = '';
            (i > 0) ? plu = 's' : plu;
            arrSemanas.push([(i + 1) + ' Semana' + plu]);
        }
    }
    pushSemanas(1, arrSemanas);

}

function add_fechas_pedido_fijo_personalizado() {

    $.LoadingOverlay('show');
    var cant_div = $('#td_fechas_pedido_fijo_personalizado div.col-md-4').length;
    if (cant_div > 0) {
        $('#btn_delete_fechas_pedido_fijo_personalizado').removeClass('hide');
    }
    datos = {
        cant_div: cant_div,
    };
    $.get('clientes/add_fechas_pedido_fijo_personalizado', datos, function (retorno) {
        $('#td_fechas_pedido_fijo_personalizado').append(retorno);
    }).always(function () {
        $.LoadingOverlay('hide');
    });
}

function delete_fechas_pedido_fijo_personalizado() {

    $.LoadingOverlay('show');
    var cant_div = $('#td_fechas_pedido_fijo_personalizado div.col-md-4').length;
    var div = $("#div_" + cant_div);
    div.remove();

    if (cant_div == 2) {
        $('#btn_delete_fechas_pedido_fijo_personalizado').addClass('hide');
    }
    $.LoadingOverlay('hide');
}

function habilitar_campos() {
    $("#fecha_desde_pedido_fijo").attr('disabled', false);
    $("#fecha_hasta_pedido_fijo").attr('disabled', false);
}

function store_pedido(id_cliente, pedido_fijo, csrf_token, vista, id_pedido) {

    if ($('#form_add_pedido').valid()) {

        let tipo = $("#tipo").val()

        empresa = $("select#id_configuracion_empresa option:selected").text()

        if (tipo == '') {

            texto = `<div class="alert alert-info text-center">
                    <p>Está seguro de guardar el pedido?</p>
                </div>`

        } else {

            texto = `<div class="alert alert-warning text-center hidden" style="margin-top:5px">
                    <p style="font-size: 16px;font-weight: bold;"><b>INDIQUE SI EL STANDING SE EDITARÁ HASTA LA ÚLTIMA FECHA CREADA</b></p>
                    <div style="display: flex;align-items: center;justify-content: space-evenly;">
                        <div>
                            <input type="radio" name="check_edita_futuro" value="NO" style="width: 18px;height: 18px;" id="no_edita_futuro" checked>
                            <label style="font-size: 18px;position: relative;bottom: 4px;" for="no_edita_futuro">  <b>NO</b> </label>
                        </div>
                        <div>
                            <input type="radio" name="check_edita_futuro" value="SI" style="width: 18px;height: 18px;" id="edita_futuro">
                            <label style="font-size: 18px;position: relative;bottom: 4px;" for="edita_futuro"> <b>SI </b></label>
                        </div>
                    </div>
                </div>`

        }

        let cant_datos_exportacion = $("thead#thead_productos_seleccionados th.th_datos_exportacion").length
        let arrDatosExportacion = []

        $.each($('tbody#tbody_productos_seleccionados input.input_cantidad'), function (i, j) {

            if (cant_datos_exportacion > 0) {
                let arrDatosExportacionEspecificacion = []
                for (a = 1; a <= cant_datos_exportacion; a++) { //1
                    nombre_columna_dato_exportacion = $("thead#thead_productos_seleccionados th#th_datos_exportacion_" + a).text().trim().toUpperCase();
                    arrDatosExportacionEspecificacion.push({
                        valor: $(this).parent().parent().find('input.input_' + nombre_columna_dato_exportacion).val(),
                        id_dato_exportacion: $(this).parent().parent().find("input.id_dato_exportacion_" + nombre_columna_dato_exportacion).val()
                    });
                }
                arrDatosExportacion.push(arrDatosExportacionEspecificacion)
            }

        })

        let alerta_marcaciones = true

        arrDatosExportacion.forEach(arr => {
            arr.forEach(m => {
                if (m.valor != '' && typeof m.valor != 'undefined') {
                    alerta_marcaciones = false
                    return false
                }
            })
        })

        if (alerta_marcaciones)
            texto += "<div class='alert alert-danger text-center'>No se ha colocado ninguna marcación en el pedido, está seguro de procesar el pedido?</div>"

        modal_quest('modal_edit_pedido', texto, '<i class="fa fa-floppy-o"></i> Guardar pedido', true, false, '40%', function () {

            if ($("#envio_automatico").is(":checked"));

            if ($("#fecha_envio").val() == "") {
                $("#error_fecha_envio").html('<p style="color: red;">Debe seleccionar una fecha para realizar el envío</p>');
                return false;
            } else {
                $("#error_fecha_envio").html("");
            }

            let variedades = '';
            let arrDataDetallesPedido = [];
            let arrDataPresentacionYuraVenture = [];
            let dataTallos = [];
            let valid_precio = false
            let valid_piezas = false

            $.each($('tbody#tbody_productos_seleccionados input.input_cantidad'), function (i, j) {

                let id_tr_producto = $(this).attr('data-id_tr_producto')
                let piezas = $(this).val()
                let precio = ''

                if (piezas != "" && piezas != 0) {

                    //COMBO
                    /* if($(this).parent().hasClass('td_piezas_combo')){

                        $.each($("tbody#tbody_productos_seleccionados td.td_variedad_"+id_tr_producto),function(){
                            variedades += $(this).attr('data-id_variedad')+ "|"
                        })

                    }else{ //SIMPLE

                        variedades += $( $("tbody#tbody_productos_seleccionados td.td_variedad_"+id_tr_producto)[0] ).attr('data-id_variedad') + "|";

                    } */

                    //COMBO
                    if ($(this).parent().hasClass('td_piezas_combo')) {

                        $.each($("td.td_calibre_" + id_tr_producto), function (i) {

                            let id_det_esp_emp = $(this).attr('data-id_det_esp_emp');
                            let p = $("input.precio_x_ramo_" + id_tr_producto)[i].value
                            if (p == 0 || p == '') {
                                valid_precio = true
                                return false
                            }
                            precio += `${p};${typeof id_det_esp_emp != 'undefined' ? id_det_esp_emp : 'null'}|`

                        })

                    } else { //SIMPLE

                        let id_det_esp_emp = $("td.td_calibre_" + id_tr_producto).attr('data-id_det_esp_emp')

                        //EN LOS COMBOS NO EXISTE EL id_det_esp_emp POR LO QUE SE VERIFICA EN EL BACKEND Y SE CREA LA ESPECIFICACION DE TIPO "O" Y SE ASIGNA AL CLIENTE Y AL PEDIDO
                        $.each($("input.precio_x_ramo_" + id_tr_producto), function () {
                            console.log($(this).val())
                            if ($(this).val() == 0 || $(this).val() == '') {
                                valid_precio = true
                                return false;
                            }
                            precio += `${$(this).val()};${typeof id_det_esp_emp != 'undefined' ? id_det_esp_emp : 'null'}|`
                        })

                    }

                    if (valid_precio)
                        return false

                    arr_custom_ramos_x_caja = []

                    //SOLO PARA CAJAS SIMPLES XQ LOS COMBOS SON EPSECIFICACIONES QUE SE CREAN EN TIEMPO DE EJECUCIÓN
                    $.each($("input.input_ramos_x_caja_" + id_tr_producto), function () {
                        let ramos_det_esp_emp = $(this).parent().attr('data-ramos_x_caja')
                        let ramos_modificados = $(this).val()
                        let id_det_esp_emp = $(this).parent().attr('data-id_det_esp_emp')
                        if ((ramos_det_esp_emp != ramos_modificados) && typeof id_det_esp_emp != 'undefined') {
                            arr_custom_ramos_x_caja.push({
                                ramos_x_caja: ramos_modificados,
                                id_det_esp_emp
                            });
                        }
                    });

                    let id_cliente_pedido_especificacion = $(this).attr('data-id_cliente_pedido_especificacion')

                    let especificacion_combo = []

                    $.each($('tr.tr_pedido_combo_' + id_tr_producto), function () {

                        especificacion_combo.push({
                            variedad: $(this).find('td.td_variedad_' + id_tr_producto + ' select').val(),
                            id_clasificacion_ramo: $(this).find('select.clasificacion_combo_' + id_tr_producto).val(),
                            caja: $(this).parent().parent().find('td.td_caja_combo select').val(),
                            ramos_x_caja: $(this).find('input.input_ramos_x_caja_' + id_tr_producto).val(),
                            presentacion: $(this).find('select.presentacion_combo').val(),
                            tallos_x_ramos: $(this).find("input[name='tallos_x_ramo']").val(),
                            longitud_ramo: $(this).find("input[name='longitud']").val(),
                            unidad_medida: 1
                        })

                    })

                    arrDataDetallesPedido.push({
                        cantidad: piezas,
                        id_cliente_pedido_especificacion,
                        id_agencia_carga: $(this).parent().parent().find('td.agencia_carga select').val(),
                        precio,
                        arr_custom_ramos_x_caja,
                        especificacion_combo
                    })

                } else {
                    valid_piezas = true
                    return false;
                }

            });

            if (valid_precio) {
                modal_view('modal_status_pedidos', '<div class="alert alert-danger text-center"><p> Verifique los precios del pedido </p> </div>', '<i class="fa fa-times" aria-hidden="true"></i> Estado pedido', true, false, '50%');
                return false;
            }

            if (valid_piezas) {
                modal_view('modal_status_pedidos', '<div class="alert alert-danger text-center"><p>Verfique los valores de la columna piezas del pedido </p> </div>', '<i class="fa fa-times" aria-hidden="true"></i> Estado pedido', true, false, '50%');
                return false
            }

            if (arrDataDetallesPedido.length < 1) {
                modal_view('modal_status_pedidos', '<div class="alert alert-danger text-center"><p> Debe colocar la cantidad de piezas en al menos una especificación</p> </div>', '<i class="fa fa-times" aria-hidden="true"></i> Estado pedido', true, false, '50%');
                return false;
            }

            var arrFechas = [];

            if (pedido_fijo && ($("#opcion_pedido_fijo").val() == 1) || $("#opcion_pedido_fijo").val() == 2) {
                var fechaDesde = moment($("#fecha_desde_pedido_fijo").val());
                var fechaHasta = moment($("#fecha_hasta_pedido_fijo").val());
                var diferenciaDias = fechaHasta.diff(fechaDesde, 'days');

                var fechaFormateada = $('#fecha_desde_pedido_fijo').val().replace('/-/g', '/');
                let date = new Date(fechaFormateada);
                var x = 1;

                for (var i = 0; i < diferenciaDias + 2; i++) {

                    var fechas = (date.getMonth() + 1) + "/" + date.getDate() + "/" + date.getFullYear();
                    date.setDate(date.getDate() + 1);
                    var d = new Date(fechas);

                    if ($("#opcion_pedido_fijo").val() == 1) {
                        if (d.getDay() === parseInt($("#dia_semana").val().trim())) {
                            if (x === parseInt($("#intervalo").val())) {
                                arrFechas.push(fechas);
                                x = 0;
                            }
                            x++;
                        }
                    } else if ($("#opcion_pedido_fijo").val() == 2) {
                        if (d.getDate() == parseInt($("#dia_mes").val())) {
                            arrFechas.push(fechas);
                        }
                    }
                }
            } else if (pedido_fijo && $("#opcion_pedido_fijo").val() == 3) {
                $cant_pedidos = $("#td_fechas_pedido_fijo_personalizado div.col-md-4").length;
                for (var i = 0; i < $cant_pedidos; i++) {
                    arrFechas.push(
                        $("input#fecha_desde_pedido_fijo_" + (i + 1)).val()
                    );
                }
            }

            $.LoadingOverlay('show');

            let datos = {
                _token: csrf_token,
                arrDataDetallesPedido: JSON.stringify(arrDataDetallesPedido),
                descripcion: $('#descripcion').val(),
                tipo_pedido: $("#tipo_pedido_fijo").val(),
                fecha_de_entrega: $('#fecha_de_entrega').length ? $('#fecha_de_entrega').val() : '',
                id_cliente: id_cliente == '' ? $("#id_cliente_venta").val() : id_cliente,
                id_consignatario: $("#filtro_consignatario").val(),
                id_pedido: $('#id_pedido_creado').val(),
                arrFechas: arrFechas.length < 1 ? '' : arrFechas,
                pedido_fijo: $("#opcion_pedido_fijo").length > 0 ? $("#opcion_pedido_fijo").val() : '',
                opcion: $("#opcion_pedido_fijo").val(),
                // variedades: variedades.split("|"),
                arrDatosExportacion: JSON.stringify(arrDatosExportacion),
                crear_envio: $("#envio_automatico").is(":checked"),
                fecha_envio: $("#fecha_de_entrega").val(),
                id_configuracion_empresa: $("select#id_configuracion_empresa").val(),
                factura_ficticia: $("#factura_ficticia").is(":checked"),
                numero_ficticio: $("#numero_ficticio").val(),
                arrDataPresentacionYuraVenture,
                dataTallos,
                edita_standing: $("input[name='check_edita_futuro']:checked").val()
            }

            post_jquery('clientes/store_pedidos', datos, function () {
                cerrar_modals()
                listar_resumen_pedidos($('#fecha_pedidos_search').val(), true, $("#id_configuracion_pedido").val(), $("#id_cliente").val(), false)

                if (typeof datos.id_pedido == 'undefined' || datos.id_pedido == '') {
                    add_pedido('', pedido_fijo, 'pedidos')
                }

                if (vista != 'pedidos') {
                    detalles_cliente(id_cliente == '' ? id_cliente = $("#id_cliente_venta").val() : id_cliente)
                }

            });
            $.LoadingOverlay('hide');

        });
    }
}

function cancelar_pedidos(id_pedido, id_cliente, estado, token, tipo_pedido) {
    let texto = ''

    if (tipo_pedido == 'OPEN MARKET') {

        texto = '<div class="alert alert-warning text-center">¿Esta seguro que desea eliminar el pedido?</div>'

    } else if (tipo_pedido == 'STANDING ORDER') {

        texto = '<div class="alert alert-warning text-center">¿Esta seguro que desea eliminar el pedido?</div>'
        texto += `<div class="alert alert-warning text-center hidden" style="margin-top:5px">
                <p style="font-size: 16px;font-weight: bold;"><b>INDIQUE SI EL STANDING SE CANCELARÁ HASTA LA ÚLTIMA FECHA CREADA</b></p>
                <div style="display: flex;align-items: center;justify-content: space-evenly;">
                    <div>
                        <input type="radio" name="check_elimina_futuro" value="NO" style="width: 18px;height: 18px;" id="no_edita_futuro"checked>
                        <label style="font-size: 18px;position: relative;bottom: 4px;" for="no_edita_futuro">  <b>NO</b> </label>
                    </div>
                    <div>
                        <input type="radio" name="check_elimina_futuro" value="SI" style="width: 18px;height: 18px;" id="edita_futuro">
                        <label style="font-size: 18px;position: relative;bottom: 4px;" for="edita_futuro"> <b>SI </b></label>
                    </div>

                </div>
            </div>`

    }

    texto += `<div class="alert alert-warning text-center" style="margin-top:5px">
        <p style="font-size: 16px;font-weight: bold;"><b>INDIQUE COMO SE GUARDARÁ EL PEDIDO ELIMINADO</b></p>
            <div style="display: flex;align-items: center;justify-content: space-evenly;">
                <div>
                    <input type="radio" name="check_guarda_perdida" value="P" style="width: 18px;height: 18px;" id="grabar_perdida">
                    <label style="font-size: 18px;position: relative;bottom: 4px;" for="grabar_perdida">
                    <b>PÉRDIDA</b>
                    </label>
                </div>
                <div>
                    <input type="radio" name="check_guarda_perdida" value="C" style="width: 18px;height: 18px;" id="grabar_cancelado" checked>
                    <label style="font-size: 18px;position: relative;bottom: 4px;" for="grabar_cancelado">
                    <b>CANCELADO</b>
                    </label>
                </div>
                <div>
                    <input type="radio" name="check_guarda_perdida" value="0" style="width: 18px;height: 18px;" id="no_grabar" checked>
                    <label style="font-size: 18px;position: relative;bottom: 4px;" for="no_grabar">
                    <b>ELIMINAR</b>
                    </label>
                </div>

            </div>
        </div>`

    modal_quest('modal_quest_cancelar_pedido', texto,
        '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de alerta', true, false, '50%', function () {
            $.LoadingOverlay('show');

            let datos = {
                _token: token,
                id_pedido,
                estado,
                eliminar_origen: 'SI',
                elimina_standing: $("input[name='check_elimina_futuro']:checked").val(),
                registra_perdido: $("input[name='check_guarda_perdida']:checked").val(),
            }

            post_jquery('clientes/cancelar_pedido', datos, function () {
                cerrar_modals();
                //console.log($("#listar_resumen_pedido").val());
                ($("#listar_resumen_pedido").val() == 'true' || $("#listar_resumen_pedido").val() == undefined)
                    ? listar_resumen_pedidos($('#fecha_pedidos_search').val(), true)
                    : "";

            })
            $.LoadingOverlay('hide')
        })
}

function cargar_espeicificaciones_cliente(remove) {

    let datos_exportacion_habilitados = []

    $.each($("div#simple thead#thead_inputs_dinamicos th.th_datos_exportacion"), function () {

        if (!$(this).hasClass('hide')) {
            datos_exportacion_habilitados.push('th_datos_exportacion_' + $(this).html().trim())
        }


    })

    $('#table_campo_pedido').LoadingOverlay('show');
    remove ? $("#table_campo_pedido table").remove() : '';
    var cant_tr = $('tbody#tbody_inputs_pedidos tr').length;



    if ($("#id_pedido_creado").val() == '' && $("#tbody_productos_seleccionados tr").length == 0) {
        $("tbody#tbody_productos_seleccionados").find("tr").not('#alert_sin_productos').remove()
        $("tr#alert_sin_productos").removeClass('hide')
    }

    let datos = {
        id_cliente: $("#id_cliente_venta").val()
    }

    get_jquery('pedidos/cargar_especificaciones', datos, function (response) {
        remove ? add_campos('', $('#id_pedido_creado').val(), datos_exportacion_habilitados) : '';
        $("#btn_add_campos").attr('disabled', false);
        $(".iva_pedido").html(response['iva_cliente'] + "%");
        $("#iva_cliente").val(response['iva_cliente']);
        calcular_precio_pedido();
        $(".btn_pedidos_simples").removeClass('collapsed')
        $("div#simple").addClass('in')



    }, 'table_campo_pedido');
    $('#table_campo_pedido').LoadingOverlay('hide');
}

function detalles_cliente(id_cliente) {
    $.LoadingOverlay('show');
    datos = {
        id_cliente: id_cliente
    };
    $.get('clientes/ver_detalles_cliente', datos, function (retorno) {
        modal_view('modal_view_detalle_cliente', retorno, '<i class="fa fa-fw fa-eye"></i> Detalles de cliente', true, false, '75%');
    });
    $.LoadingOverlay('hide');
}

function add_envio(id_pedido, token) {
    $.LoadingOverlay('show');
    datos = {
        id_pedido: id_pedido
    };
    $.get('clientes/add_envio', datos, function (retorno) {
        modal_form('modal_view_envio_pedido', retorno, '<i class="fa fa-plane" ></i> Crear envío', true, false, '75%', function () {
            store_envio(token, id_pedido);
        });
    });
    $.LoadingOverlay('hide');
}

function add_form_envio(id_form, total, form) {

    var cant_total_pedidos = $("#cantidad_detalle_form_" + id_form).val();

    var cant_rows = $("form#form_envio_" + id_form + " div#rows").length;
    cant_rows < 1 ? agregar_inputs(cant_rows, cant_total_pedidos, id_form, total, form) : '';

    if (cant_rows >= 1) {
        //var campo_at = $("#id_agencia_transporte_"+id_form+"_"+cant_rows).val();
        var campo_c = $("#cantidad_" + id_form + "_" + cant_rows).val();
        //var campo_e  =  $("#envio_"+id_form+"_"+cant_rows).val();
        cant_rows == 0 ? total = total - campo_c : '';

        var totales_cantidad = 0;

        for (var i = 1; i <= cant_rows; i++) {
            totales_cantidad = totales_cantidad + parseInt($("#cantidad_" + id_form + "_" + i).val());
        }
        total2 = total - totales_cantidad;

        if (campo_c == undefined || campo_c == null) {
            $('#msg_' + id_form).html('<b>Complete todos los campos del Envío N# ' + cant_rows + '</b>');
        } else {
            agregar_inputs(cant_rows, cant_total_pedidos, id_form, total2, form);
            $('#msg_' + id_form).html('');
        }
    }
}

function agregar_inputs(cant_rows, cant_total_pedidos, id_form, total, form) {

    //$.LoadingOverlay('show');
    if (total > 0) {
        datos = {
            rows: cant_rows + 1,
            cant_pedidos: cant_total_pedidos,
            id_form: id_form
        };
        $.get('clientes/add_form_envio', datos, function (retorno) {

            $("#div_inputs_envios_" + id_form).append(retorno);

            var_cant_inputs = $("#div_inputs_envios_" + id_form + " div#rows").length;
            $("#cantidad_" + id_form + "_" + (var_cant_inputs - 1)).attr('disabled', true);

            for (var i = 1; i <= total; i++) {
                $("#cantidad_" + id_form + "_" + (cant_rows + 1)).append('<option value="' + i + '">' + i + '</option>');
            }
            $('#msg_' + id_form).html('');
        });
    } else {
        setTimeout(function () {
            $('#msg_' + id_form).html('No se pueden realizar mas envíos en este detalle');
        }, 500);
    }
    setTimeout(function () {
        var cant_forms = $('div.well').length;
        var options = [];

        for (var j = 1; j <= cant_forms; j++) {
            var cant_rows_x_form = $("#div_inputs_envios_" + j + " div#rows").length;

            for (var z = 1; z <= cant_rows_x_form; z++) {
                options.push("<option  value=" + j + "_" + z + " id=dinamic_" + j + "> Detalle N# " + j + " Envio N# " + z + " </option>");
            }
            for (var l = 1; l <= cant_forms; l++) {
                var cant_rows_x_form = $("#div_inputs_envios_" + l + " div#rows").length;
                for (var p = 1; p <= cant_rows_x_form; p++) {
                    add_option(options, id_form, l, p, form);
                    $("select#envio_" + l + "_" + p + " option#dinamic_" + l).remove();
                }
            }
        }
    }, 1000);
    $.LoadingOverlay('hide');
}

function add_option(arr, id_form, form, input, selected) {
    $("#envio_" + form + "_" + input + " option:not(#seleccione)").remove();
    for (var p = 0; p < arr.length; p++) {
        $("#envio_" + form + "_" + input).append(arr[p]);
    }
    setTimeout(function () {
        if (selected != undefined) {
            var s = selected.split("|");
            $("#div_inputs_envios_" + s[0] + " select#envio_" + s[0] + "_" + s[1] + " option[value=" + s[2] + "_" + s[3] + "]").attr('selected', true);
        }
    });

}

function change_agencia_transporte(input) {

    var id_form = input.id.split("_")[1];
    var id_input = input.id.split("_")[2];
    var val_form = input.value.split("_")[0];
    var val_input = input.value.split("_")[1];

    $("select#id_agencia_transporte_" + id_form + "_" + id_input + " option").attr('selected', false);

    var val_form_selected = $("#id_agencia_transporte_" + val_form + "_" + val_input).val();

    $("select#id_agencia_transporte_" + id_form + "_" + id_input + " option[value=" + val_form_selected + "]").attr('selected', true);

    if ($("select#" + input.id + " option:selected").text().trim() != ("Mismo envío").trim()) {
        $("#id_agencia_transporte_" + id_form + "_" + id_input).attr("disabled", true)
    } else {
        $("#id_agencia_transporte_" + id_form + "_" + id_input).attr("disabled", false)
    }
}

function store_envio(token, id_pedido, vista) {

    var cant_forms = $('div.well').length;
    var data = [];
    var suma_cant_input = 0;
    var suma_cant_forms = 0;
    for (var j = 1; j <= cant_forms; j++) {

        if ($("#fecha_envio_" + j).val() == '') {
            var msg = '<div class="alert alert-warning text-center"><p> El campo fecha del Detalle N# ' + j + ' es obligatorio </p></div>';
            modal_view('modal_view_error_fechas', msg, '<i class="fa fa-fw fa-eye"></i> Error al realizar el envío', true, false, '40%');
            return false;
        }

        var cant_rows_x_form = $("#div_inputs_envios_" + j + " div#rows").length;
        for (var z = 1; z <= cant_rows_x_form; z++) {

            var envio = 1;
            var fecha = "";
            var form = '';

            if ($("select#envio_" + j + "_" + z).text().trim() === ("Mismo envío").trim()) {
                //envio = $("#envio_"+j+"_"+z).val();
                fecha = $("#fecha_envio_" + j).val();
                form = 0;
            } else {
                var arrEnvio = $("#envio_" + j + "_" + z).val().split("_");

                //envio = arrEnvio[0];
                envio = $("select[name=envio_" + j + "]")[0].name.split("_")[1];
                fecha = fecha = $("#fecha_envio_" + arrEnvio[0]).val();
                form = j + "|" + z + "|" + arrEnvio[0] + "|" + arrEnvio[1];
            }
            suma_cant_input += Number($("#cantidad_" + j + "_" + z).val());

            data.push([
                $("#id_especificacion_" + j).val(),
                $("#id_agencia_transporte_" + j + "_" + z).val(),
                $("#cantidad_" + j + "_" + z).val(),
                envio,
                fecha,
                form,
                //$("#id_detalle_envio_"+j+"_"+z).val()
            ]);
        }
        suma_cant_forms += Number($("#cantidad_detalle_form_" + j).val());
    }
    if (suma_cant_input < suma_cant_forms) {
        var msg = '<div class="alert alert-warning text-center"><p> Aún faltan especificaiones por ordenar en este pedido para su envío </p></div>';
        modal_view('modal_view_error_cantidad', msg, '<i class="fa fa-fw fa-eye"></i> Error al realizar el envío', true, false, '40%');
        return false;
    } else if (suma_cant_input > suma_cant_forms) {
        var msg = '<div class="alert alert-warning text-center"><p> La suma de las cantidades de los envios no puede ser mayor a la suma de las cantidades de las especificaciones</p></div>';
        modal_view('modal_view_error_cantidad', msg, '<i class="fa fa-fw fa-eye"></i> Error al realizar el envío', true, false, '40%');
        return false;
    }

    $.LoadingOverlay('show');
    datos = {
        _token: token,
        arrData: data,
        id_pedido: id_pedido
    };
    post_jquery('clientes/store_envio', datos, function () {
        cerrar_modals();
        vista === 'envios' ? buscar_listado_envios() : buscar_listado_pedidos();
    });
    $.LoadingOverlay('hide');
}

function editar_envio(id_envio, id_detalle_envio, id_pedido, token) {

    $.LoadingOverlay('show');
    datos = {
        //_token           : token,
        id_pedido: id_pedido,
        id_detalle_envio: id_detalle_envio,
        id_envio: id_envio
    };
    $.get('envio/editar_envio', datos, function (retorno) {
        modal_form('modal_view_edtiar_envio_pedido', retorno, '<i class="fa fa-plane" ></i> Editar envío', true, false, '75%', function () {
            store_envio(token, id_pedido, 'envios');
        });
    });
    $.LoadingOverlay('hide');

}

function ver_envio(id_pedido) {
    //clientes/ver_envio
}

function delete_input(id_form) {

    $.LoadingOverlay('show');
    var div = $('div#div_inputs_envios_' + id_form + ' div#rows:last-child');
    var rows = $("#div_inputs_envios_" + id_form + " #rows");
    var cant = $("#cantidad_" + id_form + "_" + rows.length).val();

    div.remove();

    var rows_new = $("#div_inputs_envios_" + id_form + " #rows");
    var cant_new = $("#cantidad_" + id_form + "_" + rows_new.length).val();

    /*cant = parseInt(cant) + parseInt(cant_new);

    if(rows_new.length != 1){
        if(cant !== null && !isNaN(cant)) {
            $("#cantidad_" + id_form + "_" + rows_new.length + " option").remove();
            for (var x = 1; x <= cant; x++) {
                cant == x ? selected = "selected='selected'" : selected = "";
                $("#cantidad_" + id_form + "_" + rows_new.length).append("<option " + selected + " value=" + x + ">" + x + "</option>");
            }
        }
    }*/
    var_cant_inputs = $("#div_inputs_envios_" + id_form + " div#rows").length;
    $("#cantidad_" + id_form + "_" + (var_cant_inputs)).attr('disabled', false);
    $.LoadingOverlay('hide');
}

function facturar_envio(id_envio, token) {
    $.LoadingOverlay('show');
    datos = {
        id_envio: id_envio
    };
    $.get('configuracion_facturacion/formulario_facturacion', datos, function (retorno) {
        modal_form('modal_view_envios_facturas', retorno, '<i class="fa fa-usd" aria-hidden="true"></i> Facturar Envío', true, false, '75%', function () {
            /*facturar_cliente(token);*/
        });
    }).always(function () {
        $.LoadingOverlay('hide');
    });
}

/* ========== Añadir/Quitar la clase "table" a una tabla =========*/
function estrechar_tabla(id, flag) {
    if (flag)
        $('#' + id).removeClass('table');
    else
        $('#' + id).addClass('table');

}

function create_agencia_carga(id_agencia_carga, token, id_cliente) {
    $.LoadingOverlay('show');
    datos = {
        id_agencia_carga: id_agencia_carga
    };
    $.get('agrencias_carga/create_agencia', datos, function (retorno) {
        modal_form('modal_add_agencia_carga', retorno, '<i class="fa fa-fw fa-plus"></i> Añadir Agencia de carga', true, false, '50%', function () {
            store_agencia_carga(token, id_cliente);
        });
    });
    $.LoadingOverlay('hide');
}

function store_agencia_carga(token, id_cliente) {

    arr_codigo_venture = [];
    $.each($("div.codigos_venture"), function (i, j) {
        if ($(j).find('select#id_configuracion_empresa').val() != "")
            arr_codigo_venture.push({
                id_configuracion_empresa: $(j).find('select#id_configuracion_empresa').val(),
                codigo_venture: $(j).find('input#codigo').val(),
            });
    });

    if ($('#form_add_agencia_carga').valid()) {
        $.LoadingOverlay('show');
        datos = {
            _token: token,
            id_agencia_carga: $("#id_agencia_carga").val(),
            nombre: $("#nombre_agencia").val(),
            //codigo: $("#codigo_agencia").val(),
            id_cliente: id_cliente,
            correo: $("#correo").val(),
            correo2: $("#correo2").val(),
            //correo3: $("#correo3").val(),
            identificacion: $("#identificacion").val(),
            codigo_venture: arr_codigo_venture
        };
        post_jquery('agrencias_carga/store_agencia', datos, function () {
            cerrar_modals();
            if (!id_cliente) {
                location.reload();
            } else {
                detalles_cliente(id_cliente);
                cargar_opcion('campos_agencia_carga', '', 'clientes/ver_agencias_carga');
            }
        });
        $.LoadingOverlay('hide');
    }
}

function cargar_opcion(div, id_cliente = '', url, add) {

    $.LoadingOverlay('show');

    if (div === 'campos_agencia_carga') {
        var cant_tr = $("tbody#campos_agencia_carga tr").length;
    } else if (div === 'campos_contactos') {
        var cant_tr = $("tbody#campos_contactos tr").length;
    }

    datos = {
        id_cliente: id_cliente,
        cant_tr: typeof cant_tr === "undefined" ? '' : cant_tr
    };

    get_jquery('/' + url, datos, function (retorno) {

        if (div === 'campos_agencia_carga') {
            $('#include_agencia_carga').removeClass('hide');
            $('#include_contactos_cliente,#div_content_opciones').addClass('hide');
            $("#div_content_opciones").html('');
            if (add === 'add') {
                $('#' + div).append(retorno);
            } else {
                $("#div_content_opciones").html(retorno);
            }
        }
        else if (div === 'campos_contactos') {

            $('#include_agencia_carga,#div_content_opciones').addClass('hide');
            $('#include_contactos_cliente').removeClass('hide');
            $("#div_content_opciones").html('');
            if (add === 'add') {
                $('#' + div).append(retorno);
            } else {
                $("#div_content_opciones").html(retorno);
            }

        } else if (div === 'div_content_opciones') {
            $("#div_content_opciones").removeClass('hide');
            $('#include_contactos_cliente,#include_agencia_carga').addClass('hide');

            $("#div_content_opciones").html(retorno);

        } else if (div === 'div_pedidos') {
            $('#include_contactos_cliente,#include_agencia_carga').addClass('hide');
            $("#div_content_opciones").removeClass('hide');
            $("#div_content_opciones").html(retorno);

        } else if (div === 'div_consignatario') {
            $('#include_contactos_cliente,#include_agencia_carga').addClass('hide');
            $("#div_content_opciones").removeClass('hide');
            $("#div_content_opciones").html(retorno);
        }

    });
    $.LoadingOverlay('hide');
}

function aumentar_consignatario() {
    /* $objDom = $("div#row_add_user_contactos .col-md-4").html();
    $("div#row_add_user_contactos").append("<div class='col-md-4'>" + $objDom + "</div>"); */

    $.get('/consignatario/add', {}, function (retorno) {

        $("div#row_add_user_contactos").prepend(retorno)

    })
}

function elimnar_consignatario(token, element, id_cliente_consignatario = '') {

    if (id_cliente_consignatario != '') {

        let datos = {
            _token: token,
            id_cliente_consignatario
        }

        post_jquery('clientes/eliminar_cliente_consignatario', datos, function () {
            $(element).parent().parent().parent().remove()
        })

    } else {
        $(element).parent().parent().parent().remove()
    }

}

function store_cliente_consignatario(token, id_cliente) {

    arr_consignatarios = []
    let continuar = true

    $.each($("div#row_add_user_contactos form.form-consignatario"), function () {
        if ($(this).valid()) {
            arr_consignatarios.push({
                id_consignatario: $(this).find('input#id_consignatario').val(),
                nombre: $(this).find('input#nombre').val(),
                identificacion: $(this).find('input#identificacion').val(),
                telefono: $(this).find('input#telefono').val(),
                pais: $(this).find('select#pais').val(),
                ciudad: $(this).find('input#ciudad').val(),
                correo: $(this).find('input#correo').val(),
                direccion: $(this).find('input#direccion').val(),
            });
        }
    })

    if (continuar) {

        let datos = {
            _token: token,
            id_cliente: id_cliente,
            arr_consignatarios: JSON.stringify(arr_consignatarios),
        }

        if (arr_consignatarios.length < 1) {
            modal_view('modal_cliente_consignatario', '<div class="alert alert-danger text-center"><p> Debe añadir al menos un consignatario al cliente para guardar</p> </div>', '<i class="fa fa-times" aria-hidden="true"></i> Estado pedido', true, false, '50%');
            return false;
        }

        post_jquery('clientes/store_cliente_consignatario', datos, function () {
            cerrar_modals()
        })

    }

    $.LoadingOverlay('hide')

}

function eliminar_cliente_consignatario(input, id_consignatario, id_cliente, token) {

    let datos = {
        _token: token,
        id_consignatario,
        id_cliente
    }

    post_jquery_m('/clientes/eliminar_cliente_consignatario', datos, function () {
        $(input).parent().parent().parent().remove()
    }, 'div_contactos_cliente')

}

function update_especificacion(id_especificacion, estado, token, cliente) {

    $.LoadingOverlay('show');
    datos = {
        _token: token,
        id_especificacion,
        estado,
    };
    post_jquery('clientes/update_especificaciones', datos, function () {
        cerrar_modals();
        if (cliente) {
            detalles_cliente($('#id_cliente').val());
            admin_especificaciones($('#id_cliente').val());
            setTimeout(function () {
                ver_especificaciones(($('#id_cliente').val()));
            }, 1000);
        } else {
            buscar_listado_especificaciones();
        }
    });
    $.LoadingOverlay('hide');
}

function buscar_listado_especificaciones() {
    $.LoadingOverlay('show');
    let datos = {
        id_cliente: $("#cliente_id").val(),
        id_variedad: $("#variedad_id").val(),
        id_planta: $("#planta_id").val(),
        tipo: $("#tipo").val(),
        estado: $("#estado").val(),
        longitud: $("#filtro_longitud").val(),
        tipo_caja: $("#filtro_tipo_caja").val(),
    };
    console.log(datos)
    $.get('especificacion/listado', datos, function (retorno) {
        $('#div_listado_especificaciones').html(retorno);

        /* if(datos.estado==1){
            $("button#accion_masiva").removeClass('btn-yura_success').addClass('btn-yura_danger')
            $("#icon_accion_masiva").removeClass('fa-undo').addClass('fa-trash')
        }else{
            $("button#accion_masiva").addClass('btn-yura_success').removeClass('btn-yura_danger')
            $("#icon_accion_masiva").removeClass('fa-trash').addClass('fa-undo')
        } */

        //estructura_tabla('table_content_especificaciones');
    }).always(function () {
        $.LoadingOverlay('hide');
    });
}

function add_especificacion(id_cliente, cliente) {
    datos = {
        id_cliente: id_cliente
    };
    get_jquery('clientes/add_especificacion', datos, function (retorno) {
        if (cliente) {
            $('#div_content').html(retorno);
        } else {
            modal_view('modal_admin_especificaciones', retorno, '<i class="fa fa-plus" aria-hidden="true"></i> Crear Especificaciones', true, false, '85%');
        }
    });
}

function tipo_unidad_medida(data, token) {
    datos = {
        _token: token,
        tipo_unidad_medida: 'P'//$('#'+data).val() COMENTADO SOLO PARA PYGANFLOR, SÓLO USA 'P' (PESO)
    };
    get_jquery('clientes/obtener_calsificacion_ramos', datos, function (retorno) {
        var select_clasif_x_ramo = $("#id_clasificacion_ramo_" + data.split('_')[2] + "_" + data.split('_')[3]);
        $('select#id_clasificacion_ramo_' + data.split('_')[2] + "_" + data.split('_')[3] + ' option#option_dinamic').remove();
        $.each(retorno, function (i, j) {
            select_clasif_x_ramo.append('<option id="option_dinamic" value="' + j.id_clasificacion_ramo + '"> ' + j.nombre + ' </option>');
        });
    });
}

function admin_colores() {
    $.LoadingOverlay('show');
    $.get('admin_colores', {}, function (retorno) {
        modal_view('modal_view_admin_colores', retorno, '<i class="fa fa-tint"></i> Administrar colores', true, false, '85%');

    }).always(function () {
        $.LoadingOverlay('hide');
    });
}

function form_codigo_barra() {
    $.LoadingOverlay('show');
    $.get(dominio + '/codigo_barra/form_codigo_barra', {}, function (retorno) {
        modal_form('modal_form_codigo_barra', retorno, '<i class="fa fa-barcode"></i> Crear código de barras', true, false, '85%', function () {
            genera_codigo_barra($("#prefijo").val(), $("#codigo").val());
        });

    }).always(function () {
        $.LoadingOverlay('hide');
    });
}

function genera_codigo_barra(prefijo, codigo) {

    $.LoadingOverlay('show');
    if (prefijo != null && prefijo != "") {
        $.get(dominio + '/codigo_barra/generar_codigo_barra/' + codigo + "/" + prefijo, {}, function (retorno) {
            $("#img_codigo_barra").html(retorno);
        });
    } else {
        $.get(dominio + '/codigo_barra/generar_codigo_barra/' + codigo, {}, function (retorno) {
            $("#img_codigo_barra").html(retorno);
        });
    }

    $.LoadingOverlay('hide');
}

function listar_resumen_pedidos(fecha, opciones, id_configuracion_empresa, id_cliente, loading = true) {

    $.LoadingOverlay('show');
    datos = {
        desde: $("#fecha_pedidos_search").val(),
        hasta: $("#fecha_pedidos_search_hasta").val(),
        opciones,
        id_configuracion_empresa,
        id_cliente: $("#id_cliente").val(),
        estado: $("#estado_pedido").val(),
        id_marcacion: $("select#id_marcacion").val(),
        id_planta: $("select#id_planta").val(),
        id_variedad: $("select#id_variedad").val(),
        tipo_pedido: $("select#tipo_pedido").val(),
        id_agencia_carga: $("select#id_agencia_carga").val()
    };
    $.get('despachos/listar_resumen_pedidos', datos, function (retorno) {

        $('#div_listado_blanco').html(retorno);
        let marcaciones = []
        $.each($("li.valor_marcacion"), function () {

            if (!marcaciones.includes($(this).html().trim()))
                marcaciones.push($(this).html().trim())
        })

        $('.option_dinamic_marcacion').remove()

        if (location.pathname == '/despachos')
            $("select#id_agencia_carga, select#id_cliente").val(null).trigger("change")

        marcaciones.forEach(item => {
            $("select#id_marcacion").append(`<option class='option_dinamic_marcacion' value='${item}'>${item}</option>`)
        })

    }).always(function () {
        $.LoadingOverlay('hide');
    });

}

function barra_string(input, event, barra = true) {
    value_input = $("#" + input.id).val();
    tecla = event.which || event.keyCode;
    if (tecla !== 46 && isNaN(String.fromCharCode(tecla)))
        return false;
    if (tecla === 46)
        if (value_input.indexOf(".") > -1 && value_input.indexOf("|") == -1)
            return false;
    if (barra)
        if (tecla === 32)
            value_input += "|";

    $("#" + input.id).val(value_input.replace(" ", ""));
}

function guion_bajo_string(input, event, guion = true) {
    value_input = $("#" + input.id).val();
    tecla = event.which || event.keyCode;
    if (guion)
        if (tecla === 32)
            value_input += "_";

    $("#" + input.id).val(value_input.replace(" ", ""));
}

function duplicar_especificacion(id_especificacion, especificacion_pedido, tipo) {
    if ($("#cantidad_piezas_" + especificacion_pedido).val() == "") {
        modal_view('modal_duplicar_especificacion', '<div class="alert alert-warning text-center"><p> Debe llenar todos los datos de la especififación antes de duplicarla</p> </div>', '<i class="fa fa-times" aria-hidden="true"></i> Estado pedido', true, false, '50%');
        return false;
    }
    $.LoadingOverlay('show');
    datos = {
        id_especificacion: id_especificacion,
        id_cliente: $("#id_cliente_venta").val(),
        cant_esp: $(".input_cantidad").length,
        empaque: $("#empaque_" + especificacion_pedido).val()
    };
    $.get('pedidos/duplicar_especificacion', datos, function (retorno) {

        if (tipo == 'simple') {
            $(".tbody_inputs_pedidos_simples").append(retorno);
        } else {
            $(".tbody_inputs_pedidos_combos").append(retorno);
        }

        //$("#cant_esp").val(parseInt($("#cant_esp").val())+1);
        calcular_precio_pedido();
        $("#btn_remove_especificacicon").removeClass('hide');
    }).always(function () {
        $.LoadingOverlay('hide');
    });
}

function formatear_numero(numero) {
    var clean = numero.split('.');
    var num = clean[0].replace(/\./g, '');
    if (!isNaN(num)) {
        num = num.toString().split('').reverse().join('').replace(/(?=\d*\.?)(\d{3})/g, '$1,');
        num = num.split('').reverse().join('').replace(/^[\.]/, '');
        numero = num;
    } else {
        alert('Solo se permiten numeros');
        numero = numero.replace(/[^\d\.]*/g, '');
    }
    if (clean.length > 1)
        return numero + '.' + clean[1];
    else
        return numero
}

function duplicar_pedido(id_pedido, id_cliente, tipo_pedido) {
    $.LoadingOverlay('show');
    let datos = {
        id_pedido,
        id_cliente,
        tipo_pedido
    };
    $.get('pedidos/form_duplicar_pedido', datos, function (retorno) {
        modal_view('modal_duplicar_pedido', retorno, '<i class="fa fa-files-o" aria-hidden="true"></i> Duplicar pedido', true, false, '70%');
    }).always(function () {
        $.LoadingOverlay('hide');
    });
}

function getRandomColor() {
    var letters = '0123456789ABCDEF';
    var color = '#';
    for (var i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}

function porcentaje_impuesto() {
    datos = {
        codigo_impuesto: $("#codigo_impuesto").val()
    };
    get_jquery('tipo_impuesto/get_tipo_impuesto', datos, function (retorno) {
        $("option#dinamic").remove();
        $.each(retorno, function (i, j) {
            $("#tipo_impuesto").append("<option id='dinamic' value=" + j.codigo + ">" + j.descripcion + "</option>");
        });
    });
}

function valida_identificacion() {
    if ($("#tipo_identificacion").val() == "07") {
        $("#identificacion").val("9999999999999").attr('disabled', true);
    } else {
        $("#identificacion").attr('disabled', false);
    }
    if ($("#tipo_identificacion").val() == "04") {
        $("#identificacion").attr('minlength', 13);
        $("#identificacion").attr('maxlength', 13);
    }
    if ($("#tipo_identificacion").val() == "05") {
        $("#identificacion").attr('minlength', 10);
        $("#identificacion").attr('maxlength', 10);
    }
    if ($("#tipo_identificacion").val() != "04" && $("#tipo_identificacion").val() != "05") {
        $("#identificacion").removeAttr("minlength");
        $("#identificacion").removeAttr("maxlength");
    }
}

function editar_pedido_tinturado(id_pedido, pos_det_ped, global = true, listar_resumen_pedido = true) {
    datos = {
        id_pedido: id_pedido,
        pos_det_ped: pos_det_ped,
        listar_resumen_pedido: listar_resumen_pedido
    };
    div_parametro = global ? 'td_opciones_' + id_pedido : false;
    get_jquery('pedidos/editar_pedido_tinturado', datos, function (retorno) {
        !global ? cerrar_modals() : '';
        modal_view('modal-view_editar_pedido_tinturado', retorno, '<i class="fa fa-fw fa-pencil"></i> Editar pedido', true, false, '98%');
    }, div_parametro);
}

function update_orden_tinturada(token, url) {
    if ($('#form-update_orden_semanal').valid()) {

        modal_quest('modal_quest_update_orden_tinturada', '<div class="alert alert-info text-center">' +
            '¿Está seguro de modificar los datos de este pedido?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de alerta', true, false, '35%', function () {
                z = 0;
                det_ped_arreglo_esp_emp = [];
                det_ped_arreglo_dat_exp = [];

                $.each($("div.well_detalle_pedido"), function (i, j) {
                    arreglo_esp_emp = [];
                    ids_esp_emp = $(j).find('.id_esp_emp');
                    arr_custom_ramos_x_caja = [];

                    for (ee = 0; ee < ids_esp_emp.length; ee++) {
                        ids_det_esp = $(j).find('input.id_det_esp_' + ids_esp_emp[ee].value);
                        /* ========= PRECIOS x DETALLE ESPECIFICACION ========== */

                        arreglo_precios = [];
                        for (det = 0; det < ids_det_esp.length; det++) {
                            arreglo_precios.push({
                                id_det_esp: ids_det_esp[det].value,
                                precio: $(j).find('#precio_det_esp_' + ids_det_esp[det].value).val(),
                                ramos_modificados: $('#ramos_x_caja_det_esp_' + ids_det_esp[det].value + '_' + ids_esp_emp[ee].value).val()
                            });
                        }

                        /* ========= MARCACIONES_COLORACIONES ========== */
                        fil = $(j).find('#marcaciones_' + ids_esp_emp[ee].value).val();
                        col = $(j).find('#coloraciones_' + ids_esp_emp[ee].value).val();
                        if ($(j).find('#cantidad_piezas').val() != $('#total_piezas_' + ids_esp_emp[ee].value).val()) {
                            alerta('<div class="alert alert-warning text-center">Las cantidades de piezas distribuidas no coinciden con las pedidas en el Detalle del pedido ' + (i + 1) + '</div>');
                            $(j).find('#cantidad_piezas').addClass('error');
                            z++;
                        }
                        arreglo_marcaciones = [];
                        arreglo_coloraciones = [];
                        console.log(fil);
                        for (f = 0; f < fil; f++) {
                            console.log("entro en el for de fill");
                            console.log($(j).find('#nombre_marcacion_' + f + '_' + ids_esp_emp[ee].value).val());
                            //if ($(j).find('#nombre_marcacion_' + f + '_' + ids_esp_emp[ee].value).val() != '') {
                            console.log("Entro en el siguiente if");
                            colores = [];
                            console.log(col);
                            for (c = 0; c < col; c++) {
                                console.log("entro en el for de col");
                                cant_x_det_esp = [];
                                if (f == 0) {
                                    console.log("f==0");
                                    /* =========== PRECIOS x COLORACION ========= */
                                    arreglo_precios_x_col = [];
                                    for (det = 0; det < ids_det_esp.length; det++) {
                                        arreglo_precios_x_col.push({
                                            id_det_esp: ids_det_esp[det].value,
                                            precio: $(j).find('#precio_color_' + c + '_' + ids_det_esp[det].value + '_' + ids_esp_emp[ee].value).val()
                                        });
                                    }
                                    arreglo_coloraciones.push({
                                        id_color: $(j).find('#color_' + c + '_' + ids_esp_emp[ee].value).val(),
                                        arreglo_precios_x_col: arreglo_precios_x_col
                                    });
                                }
                                for (det = 0; det < ids_det_esp.length; det++) {
                                    cant_x_det_esp.push({
                                        id_det_esp: ids_det_esp[det].value,
                                        cantidad: $(j).find('#ramos_marcacion_' + f + '_' + c + '_' + ids_det_esp[det].value + '_' + ids_esp_emp[ee].value).val(),
                                        precio: $(j).find('#p_marcacion_coloracion_' + f + '_' + c + '_' + ids_det_esp[det].value + '_' + ids_esp_emp[ee].value).val()
                                    });
                                }
                                colores.push({
                                    cant_x_det_esp: cant_x_det_esp
                                });
                            }
                            arreglo_marcaciones.push({
                                nombre: $(j).find('#nombre_marcacion_' + f + '_' + ids_esp_emp[ee].value).val(),
                                ramos: $(j).find('#total_ramos_marcacion_' + f + '_' + ids_esp_emp[ee].value).val(),
                                piezas: $(j).find('#total_piezas_marcacion_' + f + '_' + ids_esp_emp[ee].value).val(),
                                colores: colores
                            });
                            //} else {
                            //alerta('<div class="alert alert-warning text-center">Faltan datos (nombre de marcación) por ingresar en el Detalle del pedido ' + (i + 1) + '</div>');
                            //$(j).find('#nombre_marcacion_' + f + '_' + ids_esp_emp[ee].value).addClass('error');
                            //z++;
                            //}
                        }
                        arreglo_esp_emp.push({
                            id_esp_emp: ids_esp_emp[ee].value,
                            arreglo_precios: arreglo_precios,
                            arreglo_marcaciones: arreglo_marcaciones,
                            arreglo_coloraciones: arreglo_coloraciones,
                        });
                    }

                    ids_datos_exportacion = $(j).find('.id_dato_exportacion');
                    arreglo_dat_exp = [];
                    for (dat = 0; dat < ids_datos_exportacion.length; dat++) {
                        id_dat_exp = ids_datos_exportacion[dat].value;
                        arreglo_dat_exp.push({
                            id_dat_exp: id_dat_exp,
                            valor: $(j).find('#dato_exportacion_' + id_dat_exp).val().toUpperCase()
                        });
                    }
                    det_ped_arreglo_esp_emp.push({
                        id_det_ped: $(j).find('input.id_det_ped').val(),
                        agencia_carga: $(j).find('#id_agencia_carga').val(),
                        cant_piezas: $(j).find('#cantidad_piezas').val(),
                        arreglo_esp_emp: arreglo_esp_emp,
                        //arr_custom_ramos_x_caja : arr_custom_ramos_x_caja
                    });
                    det_ped_arreglo_dat_exp.push(arreglo_dat_exp);
                });


                datos = {
                    _token: token,
                    id_pedido: $('#id_pedido').val(),
                    arreglo_dat_exp: arreglo_dat_exp,
                    id_detalle_pedido: $('#id_detalle_pedido').val(),
                    fecha_pedido: $('#fecha_pedido').val(),
                    fecha_envio: $('#fecha_pedido').val(),
                    det_ped_arreglo_esp_emp: det_ped_arreglo_esp_emp,
                    det_ped_arreglo_dat_exp: det_ped_arreglo_dat_exp
                };

                alert(token + ' __________ ' + url);

                if (z == 0) {
                    post_jquery(url, datos, function () {
                        cerrar_modals();
                        editar_pedido_tinturado(datos['id_pedido'], $('#pos_det_ped').val(), false);
                        listar_resumen_pedidos($('#fecha_pedidos_search').val(), true);
                    });
                }
            });
    }
}

function terminar_edicion() {
    cerrar_modals();
    $("#listar_resumen_pedido").val() == 'true'
        ? listar_resumen_pedidos($('#fecha_pedidos_search').val(), true)
        : "";
}

function eliminar_detalle_pedido(det_ped, token) {
    datos = {
        _token: token,
        id_detalle_pedido: det_ped,
    };
    modal_quest('modal-quest_eliminar_detalle_pedido',
        '<div class="alert alert-warning text-center">¿Está seguro de eliminar este detalle del pedido?</div>',
        '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de alerta', true, false, '35%', function () {
            post_jquery('pedidos/eliminar_detalle_pedido_tinturado', datos, function () {
                cerrar_modals();
                if ($('#have_next').val() == 1 || $('#have_prev').val() == 1) {
                    editar_pedido_tinturado($('#id_pedido').val(), 0);
                }
                $("#listar_resumen_pedido").val() == 'true'
                    ? listar_resumen_pedidos($('#fecha_pedidos_search').val(), true)
                    : "";
            });
        });
}

function add_marcacion(esp_emp) {
    fil = parseInt($('#marcaciones_' + esp_emp).val());
    col = parseInt($('#coloraciones_' + esp_emp).val());
    console.log(fil, col);
    cant_marc = $('input.check_marcacion_' + esp_emp).length + 1;

    tabla = $('#tabla_marcacion_coloracion_' + esp_emp);

    tr = '<tr style="border: 2px solid #9d9d9d" class="tr_marcacion_' + esp_emp + '">' +
        '<td class="text-center" style="border-color: #9d9d9d">' +
        '<div class="input-group">' +
        '<span class="input-group-addon" style="border:none;">' +
        '<input type="checkbox" class="check_marcacion_' + esp_emp + ' marcacion_' + esp_emp + '_' + cant_marc + '">' +
        '</span>' +
        '<input type="text" id="nombre_marcacion_' + fil + '_' + esp_emp + '" name="nombre_marcacion_' + fil + '_' + esp_emp + '" ' +
        'placeholder="Marc" width="150px" style="border: none" class="text-center form-control form-control-sm input_marcacion_' + esp_emp + '">' +
        '<input type="hidden" id="id_marcacion_' + fil + '_' + esp_emp + '" name="id_marcacion_' + fil + '_' + esp_emp + '" value="">' +
        '</div>' +
        '</td>';
    for (c = 0; c < col; c++) {
        ids_det_esp = $('div#pedido_creado input.id_det_esp_' + esp_emp);
        inputs = '';
        for (det = 0; det < ids_det_esp.length; det++) {
            inputs += '<li>' +
                '<div class="input-group" style="width: 100px">' +
                '<span class="input-group-addon" style="background-color: #e9ecef">' +
                'P-' + (det + 1) +
                '</span>' +
                '<input type="number" value="0" id="ramos_marcacion_' + fil + '_' + c + '_' + ids_det_esp[det].value + '_' + esp_emp + '" ' +
                'name="ramos_marcacion_' + fil + '_' + c + '_' + ids_det_esp[det].value + '_' + esp_emp + '" ' +
                'onkeypress="return isNumber(event)" style="width: 100%;" ' +
                'class="text-center col_coloracion_' + esp_emp + '_' + c + ' elemento_color_' + c + '_' + esp_emp + ' ramos_marcacion_' + esp_emp + '" onchange="calcular_totales_tinturado(' + esp_emp + ')">' +
                '<input type="number" min="0" style="width: 100%;background-color: #e9ecef;text-align:center" ' +
                'class="col_precio_' + esp_emp + '_' + c + '"' +
                'id="p_marcacion_coloracion_' + fil + '_' + c + '_' + ids_det_esp[det].value + '_' + esp_emp + '" ' +
                'name="p_marcacion_coloracion_' + fil + '_' + c + '_' + ids_det_esp[det].value + '_' + esp_emp + '" ' +
                '</div>' +
                '</li>';
        }
        tr += '<td class="text-center col_coloracion td_col_coloracion_' + esp_emp + ' col_coloracion_' + esp_emp + '_' + c + ' col_precio_' + esp_emp + '_' + c + '"' +
            ' style="border-color: #9d9d9d">' +
            '<ul class="list-unstyled">' +
            inputs +
            '</ul>' +
            '</td>';
    }

    if (ids_det_esp.length > 1) {    // mixta
        ids_det_esp = $('div#pedido_creado input.id_det_esp_' + esp_emp);
        inputs = '';
        for (det = 0; det < ids_det_esp.length; det++) {
            inputs += '<li>' +
                '<div class="input-group" style="width: 100px">' +
                '<span class="input-group-addon" style="background-color: #e9ecef">' +
                'P-' + (det + 1) +
                '</span>' +
                '<input type="number" id="parcial_marcacion_' + fil + '_' + ids_det_esp[det].value + '_' + esp_emp + '" ' +
                'name="parcial_marcacion_' + fil + '_' + ids_det_esp[det].value + '_' + esp_emp + '" value="0" ' +
                'style="width: 100%; background-color: #357ca5; color: white" class="text-center">' +
                '</div>' +
                '</li>';
        }
        tr += '<td class="text-center" style="border-color: #9d9d9d" width="100px">' +
            '<ul class="list-unstyled">' +
            inputs +
            '</ul>' +
            '</td>';
    }

    tr += '<td class="text-center" style="border-color: #9d9d9d">' +
        '<input type="text" id="total_ramos_marcacion_' + fil + '_' + esp_emp + '" name="total_ramos_marcacion_' + fil + '_' + esp_emp + '" ' +
        'readonly class="text-center ramos_marcacion_' + esp_emp + ' total_ramos_marcacion_' + esp_emp + '" value="0"' +
        'style="background-color: #357ca5; color: white; width: 85px">' +
        '</td>' +
        '<td class="text-center" style="border-color: #9d9d9d">' +
        '<input type="text" id="total_piezas_marcacion_' + fil + '_' + esp_emp + '" name="total_piezas_marcacion_' + fil + '_' + esp_emp + '" ' +
        'readonly class="text-center piezas_marcacion_' + esp_emp + '" value="0" ' +
        'style="background-color: #357ca5; color: white; width: 85px">' +
        '</td>';

    $(tr + '</tr>').insertAfter($(tabla).find('tr')[fil]);

    for (c = 0; c < col; c++) {
        cambiar_color($('#color_' + c + '_' + esp_emp).val(), c, esp_emp);
    }

    fil++;
    $('#marcaciones_' + esp_emp).val(fil);
    $('.elemento_distribuir').hide();
}

function add_coloracion(esp_emp) {
    col = parseInt($('#coloraciones_' + esp_emp).val());
    cant_col = $('input.check_coloracion_' + esp_emp).length;

    tabla = $('div#pedido_creado #tabla_marcacion_coloracion_' + esp_emp);
    columna = col;
    num_colum_a_insertar = 0;
    $(tabla).find('tr').each(function (f, row) { // recorremos todas sus rows
        var primer_td = $(row).find('td,th')[columna]; // obtenemos  columna (por que insertaremos despues de esta)
        if (primer_td.tagName == 'TH') {
            for (var i = 0; i <= num_colum_a_insertar; i++) {
                if (f == 0) {
                    //insertamos una cabecera despues de la primera cabecera
                    $('<th id="celda_col_' + col + '_' + esp_emp + '" class="text-center col_coloracion col_coloracion_' + esp_emp + ' th_col_coloracion col_coloracion_' + esp_emp + '_' + cant_col + '" style="border-color: #9d9d9d" width="100px">' +
                        '<div class="input-group">' +
                        '<span class="input-group-addon" style="border:none;background: transparent;padding: 5px;">' +
                        '<input type="checkbox" class="check_coloracion_' + esp_emp + ' col_coloracion_' + esp_emp + '_' + cant_col + ' coloracion_' + esp_emp + '_' + cant_col + '" value="' + cant_col + '">' +
                        '</span>' +
                        '<select name="color_' + col + '_' + esp_emp + '" id="color_' + col + '_' + esp_emp + '" class="col_coloracion_' + esp_emp + '_' + cant_col + ' select_coloracion"' +
                        ' style="width: 100px;font-size:11px">' +
                        $('#select_colores').html() +
                        '</select>' +
                        '</div>' +
                        '<input type="hidden" id="id_color_' + col + '_' + esp_emp + '" name="id_color_' + col + '_' + esp_emp + '" ' +
                        'value="' + $('#select_colores').val() + '">' +
                        '</th>').insertAfter(primer_td);
                } else if (f == $(tabla).find('tr').length - 2) {
                    ids_det_esp = $('div#pedido_creado input.id_det_esp_' + esp_emp);
                    inputs = '';
                    for (det = 0; det < ids_det_esp.length; det++) {
                        inputs += '<li>' +
                            '<div class="input-group" style="width: 100px">' +
                            '<span class="input-group-addon" style="background-color: #e9ecef">' +
                            'P-' + (det + 1) +
                            '</span>' +
                            '<input type="number" id="parcial_color_' + col + '_' + ids_det_esp[det].value + '_' + esp_emp + '" ' +
                            'name="parcial_color_' + col + '_' + ids_det_esp[det].value + '_' + esp_emp + '" value="0" ' +
                            'style="width: 100%; background-color: #357ca5; color: white" class="text-center valor_parcial">' +
                            '</div>' +
                            '</li>';
                    }
                    $('<th class="text-center th_parcial col_coloracion col_coloracion_' + esp_emp + ' col_coloracion_' + esp_emp + '_' + cant_col + '" style="border-color: #9d9d9d" width="100px">' +
                        '<ul class="list-unstyled">' +
                        inputs +
                        '</ul>' +
                        '</th>').insertAfter(primer_td);
                } else {
                    ids_det_esp = $('div#pedido_creado input.id_det_esp_' + esp_emp);
                    inputs = '';
                    for (det = 0; det < ids_det_esp.length; det++) {
                        inputs += '<li>' +
                            '<div class="input-group" style="width: 100px">' +
                            '<span class="input-group-addon" style="background-color: #e9ecef">' +
                            'P-' + (det + 1) +
                            '</span>' +
                            '<input type="number" id="precio_color_' + col + '_' + ids_det_esp[det].value + '_' + esp_emp + '" ' +
                            'name="precio_color_' + col + '_' + ids_det_esp[det].value + '_' + esp_emp + '" ' +
                            'style="width: 100%; background-color: #e9ecef" class="text-center">' +
                            '</div>' +
                            '</li>';
                    }
                    $('<th class="text-center th_parcial col_coloracion col_coloracion_' + esp_emp + ' col_coloracion_' + esp_emp + '_' + cant_col + ' precio_col_coloracion" style="border-color: #9d9d9d" width="100px">' +
                        '<ul class="list-unstyled">' +
                        inputs +
                        '</ul>' +
                        '</th>').insertAfter(primer_td);
                }
            }
        } else {
            for (var i = 0; i <= num_colum_a_insertar; i++) {
                ids_det_esp = $('div#pedido_creado input.id_det_esp_' + esp_emp);
                inputs = '';
                for (det = 0; det < ids_det_esp.length; det++) {
                    inputs += '<li>' +
                        '<div class="input-group" style="width: 100px">' +
                        '<span class="input-group-addon" style="background-color: #e9ecef">' +
                        'P-' + (det + 1) +
                        '</span>' +
                        '<input type="number" value="0" id="ramos_marcacion_' + (f - 1) + '_' + col + '_' + ids_det_esp[det].value + '_' + esp_emp + '" ' +
                        'name="ramos_marcacion_' + (f - 1) + '_' + col + '_' + ids_det_esp[det].value + '_' + esp_emp + '" ' +
                        'onkeypress="return isNumber(event)" style="width: 100%;" ' +
                        'class="text-center col_coloracion_' + esp_emp + '_' + cant_col + ' elemento_color_' + col + '_' + esp_emp + '" onchange="calcular_totales_tinturado(' + esp_emp + ')">' +
                        '<input type="number" min="0" style="width: 100%;background-color: #e9ecef;text-align:center"' +
                        'class="col_precio_' + esp_emp + '_' + cant_col + '"' +
                        'id="p_marcacion_coloracion_' + (f - 1) + '_' + col + '_' + ids_det_esp[det].value + '_' + esp_emp + '" ' +
                        'name="p_marcacion_coloracion_' + (f - 1) + '_' + col + '_' + ids_det_esp[det].value + '_' + esp_emp + '">' +
                        '</div>' +
                        '</li>';
                }

                script = '<script>$(".select_coloracion").change(function($this){' +
                    '            arrId = $this.target.name.split("_");' +
                    '            fondo = $(\'#fondo_color_\' +  $("select#"+$this.target.name).val()).val();' +
                    '            texto = $(\'#texto_color_\' +  $("select#"+$this.target.name).val()).val();' +
                    '            $(\'.elemento_color_\' + arrId[1] + \'_\' + arrId[2]).css(\'background-color\', fondo);' +
                    '            $(\'.elemento_color_\' + arrId[1] + \'_\' + arrId[2]).css(\'color\', texto);' +
                    '        });</script>';
                //insertamos un valor despues del primer valor de la primera columna
                $('<td class="text-center col_coloracion td_col_coloracion_' + esp_emp + ' col_coloracion_' + esp_emp + '_' + cant_col + ' col_precio_' + esp_emp + '_' + cant_col + '" ' +
                    'style="border-color: #9d9d9d;" width="100px">' +
                    '<ul class="list-unstyled">' +
                    (inputs + script) +
                    '</ul>' +
                    '</td>').insertAfter(primer_td);
            }
        }
    });
    cambiar_color($('#select_colores').val(), col, esp_emp);
    col++;
    $('#coloraciones_' + esp_emp).val(col);
    $('.elemento_distribuir').hide();
}

function delete_marcacion(id_esp_emp) {
    $cant = 0
    $.each($(".check_marcacion_" + id_esp_emp), function (i, j) {
        if ($(j).is(":checked"))
            $cant++;
    });

    if ($cant > 0) {
        restar = false;
        $.each($("tr.tr_marcacion_" + id_esp_emp), function (i, j) {
            if ($(j).find('input[type=checkbox]').is(':checked')) {
                restar = true;
                if ($(j).remove())
                    $('#marcaciones_' + id_esp_emp).val($('#marcaciones_' + id_esp_emp).val() - 1);
            }


            $.each($("input.check_marcacion_" + id_esp_emp), function (k, l) {
                arr_clase = $(l).attr('class').split(" ");
                $(l).removeClass(arr_clase[1]).addClass('marcacion_' + id_esp_emp + '_' + (k + 1));
            });

            $.each($("input.input_marcacion_" + id_esp_emp), function (m, n) {
                $(n).attr({ id: 'nombre_marcacion_' + m + '_' + id_esp_emp, name: 'nombre_marcacion_' + m + '_' + id_esp_emp });
            });

            $.each($("input.total_ramos_marcacion_" + id_esp_emp), function (o, p) {
                $(p).attr({ id: 'total_ramos_marcacion_' + o + '_' + id_esp_emp, name: 'total_ramos_marcacion_' + o + '_' + id_esp_emp });
            });

            $.each($("input.piezas_marcacion_" + id_esp_emp), function (m, q) {
                $(q).attr({ id: 'total_piezas_marcacion_' + m + '_' + id_esp_emp, name: 'total_piezas_marcacion_' + m + '_' + id_esp_emp });
            });

            $.each($("input.distribucion_m_" + id_esp_emp), function (r, s) {
                $(s).attr({ id: 'distribucion_marcacion_' + o + '_' + id_esp_emp, name: 'distribucion_marcacion_' + s + '_' + id_esp_emp });
            });

            $.each($(j).find('td.col_coloracion'), function (k, l) {
                arrId = $(l).attr('class').split(' ');
                $(l).removeClass(arrId[3]).addClass('col_coloracion_' + id_esp_emp + '_' + k);

                $.each($(l).find('input.' + arrId[3]), function (m, n) {
                    arr_id_input_colocarcion = n.id.split("_");
                    (restar)
                        ? dinamico1 = arr_id_input_colocarcion[2] - 1
                        : dinamico1 = arr_id_input_colocarcion[2];

                    id_input_coloracion = arr_id_input_colocarcion[0] + "_" + arr_id_input_colocarcion[1] + "_" + dinamico1 + "_" + k + "_" + arr_id_input_colocarcion[4] + "_" + arr_id_input_colocarcion[5];
                    $(n).attr({
                        name: id_input_coloracion,
                        id: id_input_coloracion
                    }).removeClass(arrId[3]).addClass('col_coloracion_' + id_esp_emp + '_' + k).removeClass('elemento_color_' + arr_id_input_colocarcion[3] + '_' + id_esp_emp).addClass('elemento_color_' + k + '_' + id_esp_emp);
                });

                $(l).removeClass(arrId[4]).addClass('col_precio_' + id_esp_emp + '_' + k);
                $.each($(l).find('input.' + arrId[4]), function (y, w) {
                    arr_id_input_precio_mc = w.id.split("_");

                    (restar)
                        ? dinamico2 = arr_id_input_precio_mc[3] - 1
                        : dinamico2 = arr_id_input_precio_mc[3];

                    id_input_precio_mc = arr_id_input_precio_mc[0] + "_" + arr_id_input_precio_mc[1] + "_" + arr_id_input_precio_mc[2] + "_" + dinamico2 + "_" + k + "_" + arr_id_input_precio_mc[5] + "_" + arr_id_input_precio_mc[6];

                    $(w).attr({
                        name: id_input_precio_mc,
                        id: id_input_precio_mc
                    }).removeClass(arrId[4]).addClass('col_precio_' + id_esp_emp + '_' + k);
                });
            });

            $.each($("th.precio_col_coloracion"), function (q, r) {
                arrId = $(r).find("input[type='number']").attr('id').split("_");

                $(r).find("input[type='number']").attr({
                    id: 'precio_color_' + q + '_' + arrId[3] + '_' + arrId[4],
                    name: 'precio_color_' + q + '_' + arrId[3] + '_' + arrId[4]
                });
            });
        });

        $.each($("table#tabla_marcacion_coloracion_" + id_esp_emp), function (i, j) {

            $.each($(j).find('th.th_col_coloracion'), function (o, p) {
                arrIdTH = $(p).attr('class').split(' ');
                arr_th_coloracion = arrIdTH[4].split("_");
                class_col_coloracion = "col_coloracion_" + arr_th_coloracion[2] + "_" + o;
                id_th_coloracion = 'celda_col' + "_" + o + "_" + arr_th_coloracion[2];
                $('th.' + arrIdTH[4]).removeClass(arrIdTH[4]).addClass(class_col_coloracion).attr('id', id_th_coloracion);

                $(p).find("input[type='checkbox']." + arrIdTH[4]).removeClass(arrIdTH[4]).addClass('col_coloracion_' + id_esp_emp + "_" + o)
                    .removeClass('coloracion_' + id_esp_emp + "_" + arr_th_coloracion[3]).addClass('coloracion_' + id_esp_emp + "_" + o).val(o);

                $(p).find('select.' + arrIdTH[4]).removeClass(arrIdTH[4]).addClass('col_coloracion_' + id_esp_emp + "_" + o)
                    .attr({ id: 'color_' + o + "_" + id_esp_emp, name: 'color_' + o + "_" + id_esp_emp });

                $(p).find('input#id_color_' + arr_th_coloracion[3] + '_' + id_esp_emp).attr({
                    id: 'id_color_' + o + "_" + id_esp_emp,
                    name: 'id_color_' + o + "_" + id_esp_emp
                });

            });

        });

        calcular_totales_tinturado(id_esp_emp, false);
    }

}

function delete_coloracion(id_esp_emp) {

    col = [];
    $cant = 0;
    $.each($(".col_coloracion_" + id_esp_emp), function (i, j) {
        if ($(j).find('input[type=checkbox]').is(':checked')) {
            col.push((i));
            $cant++;
        }
    });

    if ($cant > 0) {
        if (col.length > 0)
            for (let i = 0; i < col.length; i++)
                if ($(".col_coloracion_" + id_esp_emp + "_" + col[i]).remove())
                    $('#coloraciones_' + id_esp_emp).val($('#coloraciones_' + id_esp_emp).val() - 1);

        $.each($("table#tabla_marcacion_coloracion_" + id_esp_emp), function (i, j) {
            $.each($(j).find('.tr_marcacion_' + id_esp_emp), function (x, z) {
                $.each($(z).find('td.col_coloracion'), function (k, l) {
                    arrId = $(l).attr('class').split(' ');
                    $(l).removeClass(arrId[3]).addClass('col_coloracion_' + id_esp_emp + '_' + k);
                    $.each($(l).find('input.' + arrId[3]), function (m, n) {
                        arr_id_input_colocarcion = n.id.split("_");

                        id_input_coloracion = arr_id_input_colocarcion[0] + "_" + arr_id_input_colocarcion[1] + "_" + arr_id_input_colocarcion[2] + "_" + k + "_" + arr_id_input_colocarcion[4] + "_" + arr_id_input_colocarcion[5];
                        $(n).attr({
                            name: id_input_coloracion,
                            id: id_input_coloracion
                        }).removeClass(arrId[3]).addClass('col_coloracion_' + id_esp_emp + '_' + k).removeClass('elemento_color_' + arr_id_input_colocarcion[3] + '_' + id_esp_emp).addClass('elemento_color_' + k + '_' + id_esp_emp);
                    });

                    $(l).removeClass(arrId[4]).addClass('col_precio_' + id_esp_emp + '_' + k);
                    $.each($(l).find('input.' + arrId[4]), function (y, w) {
                        arr_id_input_precio_mc = w.id.split("_");
                        id_input_precio_mc = arr_id_input_precio_mc[0] + "_" + arr_id_input_precio_mc[1] + "_" + arr_id_input_precio_mc[2] + "_" + arr_id_input_precio_mc[3] + "_" + k + "_" + arr_id_input_precio_mc[5] + "_" + arr_id_input_precio_mc[6];
                        $(w).attr({
                            name: id_input_precio_mc,
                            id: id_input_precio_mc
                        }).removeClass(arrId[4]).addClass('col_precio_' + id_esp_emp + '_' + k)//.removeClass('elemento_color_' + arr_id_input_precio_mc[3] + '_' + id_esp_emp).addClass('elemento_color_' + k + '_' + id_esp_emp);
                    });
                });
            });

            $.each($(j).find('th.th_col_coloracion'), function (o, p) {
                arrIdTH = $(p).attr('class').split(' ');
                arr_th_coloracion = arrIdTH[4].split("_");
                class_col_coloracion = "col_coloracion_" + arr_th_coloracion[2] + "_" + o;
                id_th_coloracion = 'celda_col' + "_" + o + "_" + arr_th_coloracion[2];
                $('th.' + arrIdTH[4]).removeClass(arrIdTH[4]).addClass(class_col_coloracion).attr('id', id_th_coloracion);

                $(p).find("input[type='checkbox']." + arrIdTH[4]).removeClass(arrIdTH[4]).addClass('col_coloracion_' + id_esp_emp + "_" + o)
                    .removeClass('coloracion_' + id_esp_emp + "_" + arr_th_coloracion[3]).addClass('coloracion_' + id_esp_emp + "_" + o).val(o);

                $(p).find('select.' + arrIdTH[4]).removeClass(arrIdTH[4]).addClass('col_coloracion_' + id_esp_emp + "_" + o)
                    .attr({ id: 'color_' + o + "_" + id_esp_emp, name: 'color_' + o + "_" + id_esp_emp });

                $(p).find('input#id_color_' + arr_th_coloracion[3] + '_' + id_esp_emp).attr({
                    id: 'id_color_' + o + "_" + id_esp_emp,
                    name: 'id_color_' + o + "_" + id_esp_emp
                });

            });

            $.each($("th.precio_col_coloracion"), function (q, r) {
                arrId = $(r).find("input[type='number']").attr('id').split("_");

                $(r).find("input[type='number']").attr({
                    id: 'precio_color_' + q + '_' + arrId[3] + '_' + arrId[4],
                    name: 'precio_color_' + q + '_' + arrId[3] + '_' + arrId[4]
                });
            });

        });

        $.each($("tr.tr_parcial_" + id_esp_emp + " th.th_parcial"), function (a, b) {
            arr_id_input_parcial = $(b).find('input.valor_parcial').attr('id').split("_");
            id_input_parcial = arr_id_input_parcial[0] + "_" + arr_id_input_parcial[1] + "_" + a + "_" + arr_id_input_parcial[3] + "_" + arr_id_input_parcial[4];
            $(b).find('input.valor_parcial').attr({ id: id_input_parcial, name: id_input_parcial });
        });

        calcular_totales_tinturado(id_esp_emp, false);
    }
}

function update_dato_exp_pedio_tinturado(id_detalle_pedido, token) {
    $.LoadingOverlay('show');
    datos_exportacion = [];
    $.each($("td.dato_exportacion_" + id_detalle_pedido), function (i, j) {
        valor = $(j).find('input.dato_exportacion_' + id_detalle_pedido).val();
        if (valor != '') {
            datos_exportacion.push({
                id_dato_exportacion: $(j).find('input.id_dato_exportacion_' + id_detalle_pedido).val(),
                valor: $(j).find('input.dato_exportacion_' + id_detalle_pedido).val(),
            });
        }
    });
    datos = {
        id_detalle_pedido: id_detalle_pedido,
        datos_exportacion: datos_exportacion,
        _token: token
    };

    post_jquery('pedidos/update_dato_exp_pedio_tinturado', datos, function () {
        listar_resumen_pedidos($('#fecha_pedidos_search').val(), true);
    });
    $.LoadingOverlay('hide');

}

function buscar_listado_envios() {
    $.LoadingOverlay('show');
    datos = {
        id_cliente: $('#id_cliente').val(),
        fecha: $('#fecha').val(),
        estado: $('#estado').val(),
    };
    $.get('envio/buscar', datos, function (retorno) {
        $('#div_listado_envios').html(retorno);
    }).always(function () {
        $.LoadingOverlay('hide');
    });
}

function calcular_precio_envio() {
    cant_forms = $("div#table_envios form").length;
    for (o = 1; o <= cant_forms; o++) {
        sub_total = 0.00;
        total_ramos = 0.00;
        total_piezas = 0.00;
        cant_rows = $(".input_cantidad_" + o).length;
        for (i = 1; i <= cant_rows; i++) {
            tipo_especificacion = $("#tipo_especificacion_" + i).val();
            precio_especificacion = 0.00;
            ramos_totales_especificacion = 0.00;
            $.each($(".cantidad_" + o + "_" + i), function (p, q) {
                $.each($(".input_ramos_x_caja_" + o + "_" + i), function (a, b) {
                    ramos_totales_especificacion += (q.value * b.value);
                });
                $.each($(".precio_" + o + "_" + i), function (y, z) {
                    precio_variedad = z.value == "" ? 0 : z.value;

                    if ($("#tipo_especificacion_" + o + "_" + i).val() === "O") {
                        // console.log($("#td_tallos_x_ramo_" + o + "_" + i).html().trim());
                        precio_especificacion += (parseFloat(precio_variedad) * parseFloat($("#td_tallos_x_ramo_" + o + "_" + i).html().trim()) * q.value);
                    } else {
                        ramos_x_caja = $(".input_ramos_x_caja_" + o + "_" + i + "_" + (y + 1)).val();
                        precio_especificacion += (parseFloat(precio_variedad) * parseFloat(ramos_x_caja) * q.value);
                    }
                });
            });
            sub_total += parseFloat(precio_especificacion);
            $("#td_total_ramos_" + o + "_" + i).html(Math.round(parseFloat(ramos_totales_especificacion)));
            total_ramos += ramos_totales_especificacion;
            $("#td_precio_especificacion_" + o + "_" + i).html("$" + parseFloat(precio_especificacion).toFixed(2));
            total_piezas += parseInt($(".cantidad_" + o + "_" + i).val());
        }
        !isNaN($("#porcentaje_impuesto_" + o).val())
            ? total = (parseFloat(sub_total) + parseFloat(((sub_total * parseFloat($("#porcentaje_impuesto_" + o).val())) / 100).toFixed(2))).toFixed(2)
            : total = sub_total;

        $("#total_piezas_" + o).html(total_piezas);
        $("#total_ramos_" + o).html(total_ramos);
        $("#sub_total_" + o).html(sub_total.toFixed(2));
        $("#total_" + o).html(parseFloat(total).toFixed(2));
    }
}

function delete_detalle_pedido(id_det_ped, id_pedido, token) {
    modal_quest('modal-quest_auto_distribuir',
        '<div class="alert alert-info text-center">¿Desea eliminar el detalle del pedido?</div>',
        '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '', function () {
            datos = {
                _token: token,
                id_det_ped: id_det_ped,
            };
            post_jquery('pedidos/delete_detalle_pedido_tinturado', datos, function () {
                cerrar_modals();
                listar_resumen_pedidos($('#fecha_pedidos_search').val(), true);
                editar_pedido_tinturado(id_pedido, 0, false);
            });
        });
}

function distribuir_pedido_tinturado(det_ped, auto = false, id_esp_emp = false, token) {
    ids_esp_emp = $('.id_esp_emp');
    arreglo_esp_emp = [];
    for (ee = 0; ee < ids_esp_emp.length; ee++) {
        esp_emp = ids_esp_emp[ee].value;
        fil = $('#marcaciones_' + esp_emp).val();
        marcaciones = [];

        for (f = 0; f < fil; f++) {
            marcaciones.push({
                id: $('#id_marcacion_' + f + '_' + esp_emp).val(),
                distribucion: $('#distribucion_marcacion_' + f + '_' + esp_emp).val(),
            });
        }

        arreglo_esp_emp.push({
            id_esp_emp: esp_emp,
            marcaciones: marcaciones,
        });
    }

    if (auto == true) {
        modal_quest('modal-quest_auto_distribuir',
            '<div class="alert alert-info text-center">¿Desea realizar la distribución automaticamente?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '', function () {
                datos = {
                    _token: token,
                    id_det_ped: det_ped,
                    arreglo_esp_emp: arreglo_esp_emp,
                    id_esp_emp: id_esp_emp
                };
                post_jquery('pedidos/auto_distribuir_pedido_tinturado', datos, function () {
                    $("#auto_distribuir_" + id_esp_emp).addClass('hide');
                    $("#distrubir_manual_" + id_esp_emp).addClass('hide');
                    $("#distribuido_" + id_esp_emp).removeClass('hide');
                    $("#div_distribucion_orden_semanal").empty();
                    ver_todas_distribuciones();
                });
            });
    } else {
        datos = {
            id_det_ped: det_ped,
            arreglo_esp_emp: arreglo_esp_emp,
        };
        get_jquery('pedidos/distribuir_pedido_tinturado', datos, function (retorno) {
            $('#div_tabla_distribucion').html(retorno);
            $('#btn_guardar_distribucion').show();
            $('#btn_update_orden_tinturada').hide();

        });
    }
}

function guardar_distribucion(token) {
    ids_esp_emp = $('.id_esp_emp');
    arreglo_esp_emp = [];
    for (ee = 0; ee < ids_esp_emp.length; ee++) {
        id_esp_emp = ids_esp_emp[ee].value;
        ids_det_esp = $('.id_det_esp_' + id_esp_emp);
        marcaciones = [];
        ids_marcaciones = $('.id_marcacion_' + id_esp_emp);
        for (m = 0; m < ids_marcaciones.length; m++) {
            id_marca = ids_marcaciones[m].value;
            distribuciones = [];
            cant_distr = parseInt($('#cantidad_distribuciones_' + id_marca + '_' + id_esp_emp).val());
            for (d = 1; d <= cant_distr; d++) {
                ids_coloraciones = $('.id_coloracion_' + id_esp_emp);
                coloraciones = [];
                r = 0;
                for (c = 0; c < ids_coloraciones.length; c++) {
                    id_col = ids_coloraciones[c].value;
                    detalles_esp = [];
                    for (det = 0; det < ids_det_esp.length; det++) {
                        id_det_esp = ids_det_esp[det].value;
                        detalles_esp.push({
                            id_det_esp: id_det_esp,
                            cant: $('#distribucion_' + id_marca + '_' + id_col + '_' + id_det_esp + '_' + d + '_' + id_esp_emp).val()
                        });

                        /* ======= VALIDAR CANTIDADES ======= */
                        r += parseInt($('#distribucion_' + id_marca + '_' + id_col + '_' + id_det_esp + '_' + d + '_' + id_esp_emp).val());
                    }
                    coloraciones.push({
                        id_coloracion: id_col,
                        detalles_esp: detalles_esp
                    });
                }
                if (r != parseInt($('#input_ramos_distribucion_' + id_marca + '_' + d + '_' + id_esp_emp).val())) {
                    alerta('La distribución de los ramos no coinciden con los totales');
                    $('#celda_ramos_' + id_marca + '_' + d + '_' + id_esp_emp).addClass('error');
                    return false;
                }
                distribuciones.push({
                    ramos: $('#input_ramos_distribucion_' + id_marca + '_' + d + '_' + id_esp_emp).val(),
                    piezas: $('#select_distribuir_' + id_marca + '_' + d + '_' + id_esp_emp).val(),
                    pos_pieza: $('#n_cajas_' + id_marca + '_' + d + '_' + id_esp_emp).val(),
                    coloraciones: coloraciones
                });
            }
            marcaciones.push({
                id_marcacion: id_marca,
                distribuciones: distribuciones
            });
        }
        arreglo_esp_emp.push({
            id_esp_emp: id_esp_emp,
            marcaciones: marcaciones
        });
    }
    datos = {
        _token: token,
        arreglo_esp_emp: arreglo_esp_emp
    };
    post_jquery('pedidos/guardar_distribucion', datos, function () {

    });
}

function ver_distribucion(det_ped) {
    datos = {
        id_det_ped: det_ped
    };
    get_jquery('pedidos/ver_distribucion', datos, function (retorno) {
        $('#div_tabla_distribucion').html(retorno);
        $('#btn_guardar_distribucion').hide();
        $('#btn_update_orden_tinturada').hide();
    });
}

function menor_mayor(elem1, elem2) {
    return elem1 - elem2;
}

function quitar_distribuciones(id_ped, token) {
    datos = {
        _token: token,
        id_ped: id_ped
    };
    modal_quest('modal-quest_quitar_distribuciones', '<div class="alert alert-info text-center">' +
        '¿Está seguro de eliminar todas las distribuciones del pedido?</div>',
        '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de alerta', true, false, '35%', function () {
            post_jquery('pedidos/quitar_distribuciones', datos, function (retorno) {
                editar_pedido_tinturado(id_ped, 0, false);
            });
        });
}

function crear_orden_pedido(input) {
    id_campo = input.id.split("_")[2];
    orden = 0;
    $.each($('.orden'), function (i, j) {
        if (j.value !== "")
            orden++;
    });
    if ($("#orden_" + id_campo).val() == "")
        $("#orden_" + id_campo).val(orden + 1);

    if ($("#cantidad_piezas_" + id_campo).val() == "") {
        orden--;
        $("#orden_" + id_campo).val("");
    }
}

function reiniciar_orden_pedido() {
    $.each($('.orden'), function (i, j) {
        j.value = ""
    });
    $.each($('.input_cantidad'), function (i, j) {
        j.value = ""
    })
}

function facturar_pedido(id_pedido) {
    $.LoadingOverlay('show');
    datos = {
        id_pedido: id_pedido
    };
    $.get('pedidos/facturar_pedido', datos, function (retorno) {
        modal_view('modal_view_Facturar_pedido', retorno, '<i class="fa fa-cubes"></i> Pedido', true, false, '80%');
    }).always(function () {
        $.LoadingOverlay('hide');
    });
}

function modificar_comprobante(id_pedido) {
    $.LoadingOverlay('show');
    datos = {
        id_pedido: id_pedido
    };
    $.get('pedidos/modificar_comprobante', datos, function (retorno) {
        modal_view('modal_view_modificar_comprobante', retorno, '<i class="fa fa-exclamation-triangle"></i> Comprobante', true, false, '80%');
    }).always(function () {
        $.LoadingOverlay('hide');
    });
}

function empaquetar_pedido(id_pedido, token) {

    modal_quest('modal_message_facturar_envios',
        '<div class="alert alert-warning text-center"><label><i class="fa fa-exclamation-triangle" ></i> Esta seguro que desea empaquetar el pedido?</label></div>',
        '<i class="fa fa-cube"></i> Empaquetar pedido', true, false, '50%', function () {
            $.LoadingOverlay('show');
            datos = {
                id_pedido: id_pedido,
                _token: token
            };
            post_jquery('pedidos/empaquetar_pedido', datos, function () {
                cerrar_modals();
                listar_resumen_pedidos($('#fecha_pedidos_search').val(), true);
            });
            $.LoadingOverlay('hide');
        });
}

function genera_comprobante_cliente(id_envio, form, action, token, id_comprobante) {
    if ($('#' + form).valid()) {
        id_form = form.split("_")[2];
        //COMENTADO PARA QUE LA FACTURACION FUNCIONE CON EL VENTURE
        // modal_quest('modal_message_facturar_envios',
        //'<div class="alert alert-info text-center">  <label>Se generará el comprobante electrónico para este envío</label></div>' +
        //'<div class="alert alert-info text-center"> <input type="checkbox" id="envio_correo" name="envio_correo"style="position: relative;top: 3px;" checked> <label for="envio_correo">¿Enviar Correo electrónico al cliente ?</label> </div>' +
        //'<div class="alert alert-info text-center"> <input type="checkbox" id="envio_correo_agencia_carga" name="envio_correo_agencia_carga"style="position: relative;top: 3px;" checked> <label for="envio_correo_agencia_carga">¿Enviar Correo electrónico a la Agencia de carga?</label> </div>',
        //'<i class="fa fa-file-code-o" aria-hidden="true"></i> Se realizaran las siguientes acciones', true, false, '40%', function () {

        arrCorreos = [];
        //COMENTADO PARA QUE LA FACTURACION FUNCIONE CON EL VENTURE
        //$.each($('input[name=correo_extra]'), function (i, j) {
        //arrCorreos.push({correo: j.value})
        //});

        datos = {
            _token: token,
            id_envio: id_envio,
            guia_madre: $("form#" + form + " #guia_madre").val(),
            guia_hija: $("form#" + form + " #guia_hija").val(),
            codigo_pais: $("form#" + form + " #codigo_pais").val(),
            dae: $("form#" + form + " #dae").val(),
            destino: $("form#" + form + " #direccion").val(),
            email: $("form#" + form + " #email").val(),
            telefono: $("form#" + form + " #telefono").val(),
            pais: $("form#" + form + " #codigo_pais option:selected").text(),
            fecha_envio: $("form#" + form + " #fecha_envio").val(),
            cant_variedades: $("form#" + form + " table tbody#tbody_inputs_pedidos tr").length,
            update: action == 'update' ? true : false,
            almacen: $("form#" + form + " #almacen").val(),
            envio_correo: $("#envio_correo").is(":checked"),
            envio_correo_agencia_carga: $("#envio_correo_agencia_carga").is(":checked"),
            arrCorreos: arrCorreos,
            fecha_pedidos_search: $("#fecha_pedidos_search").val(),
            id_comprobante: id_comprobante,
            id_configuracion_empresa: $("form#" + form + " #id_empresa").val(),
        };
        cerrar_modals();
        $.LoadingOverlay("show", {
            image: "",
            progress: true,
            text: "Registrando datos de la venta",//"Generando documento electrónico...", COMENTADO PARA QUE LA FACTURACION FUNCIONE CON EL VENTURE
            textColor: "#fff",
            progressColor: "#00a65a",
            progressResizeFactor: "0.20",
            background: "rgba(0, 0, 0, 0.75)"
        });
        var count = 0;
        var tiempo = 2000;
        var interval = setInterval(function () {
            if (count >= 15 && count < 99)
                //$.LoadingOverlay("text", "Firmado documento electrónico...");
                if (count >= 100) {
                    clearInterval(interval);
                    return;
                }
            count += 100;
            $.LoadingOverlay("progress", count);
        }, tiempo);
        $.get('comprobante/generar_comprobante_factura', datos, function (retorno) {
            modal_view('modal_view_msg_factura', retorno, '<i class="fa fa-check" aria-hidden="true"></i> Estatus facturas', true, false, '50%');
            //buscar_listado_envios();
            listar_resumen_pedidos($('#fecha_pedidos_search').val(), true);
        }).always(function () {
            $.LoadingOverlay("hide");
        });
        // });
    }
}

function activar(input_descuento, id_check) {
    var id = input_descuento.id.split("_")[1];
    $("#" + input_descuento.id,).removeAttr("readonly");
    $("#muestra_descuento_" + id).removeAttr("disabled");
}

function input_required(input) {
    if ($("input[type='checkbox']#" + input.id).is(':checked')) {
        $("#destino_" + input.id).attr('required', true);
    } else {
        $("#destino_" + input.id).attr('required', false);
    }
}

function buscar_codigo_dae(input, form, factura_cliente_tercero, id_envio) {
    $.LoadingOverlay('show');
    datos = {
        codigo_pais: input.value,
        fecha_envio: $("form#" + form + " #fecha_envio").val(),
        id_envio: id_envio
    };
    $.get('envio/buscar_codigo_dae', datos, function (retorno) {
        if (factura_cliente_tercero) {
            $("form#" + form + " #dae_cliente_tercero").val(retorno.codigo_dae);
            $("form#" + form + " #codigo_dae_cliente_tercero").val(retorno.dae);
        } else {
            $("form#" + form + " #dae").val(retorno.codigo_dae);
            $("form#" + form + " #codigo_dae").val(retorno.dae);
        }

        if (retorno.codigo_empresa === datos.codigo_pais) {
            $("form#" + form + " #dae").removeAttr('required').val('');
            $("form#" + form + " #codigo_dae").removeAttr('required').val('');
            $("form#" + form + " #dae_cliente_tercero").removeAttr('required').val('');
        } else {
            $("form#" + form + " #dae").attr('required', true).val(retorno.codigo_dae);
            $("form#" + form + " #codigo_dae").attr('required', true).val(retorno.dae);
            $("form#" + form + " #dae_cliente_tercero").attr('required', true).val(retorno.codigo_dae);

        }
    }).always(function () {
        $.LoadingOverlay('hide');
    });
}

function actualizar_envio(id_envio, form, tipo_pedido, token, id_pedido, vista) {
    if ($("#" + form).valid()) {
        $.LoadingOverlay('show');

        id_form = form.split("_")[2];
        cant_rows = $(".input_cantidad_" + id_form).length;
        arrDataPrecio = [];
        for (i = 1; i <= cant_rows; i++) {
            precio = '';
            $.each($('.precio_' + id_form + "_" + i), function (j, k) {
                precio += k.value + ";" + $(".id_detalle_esp_emp_" + id_form + "_" + i)[j].value + "|";

            });
            arrDataPrecio.push({
                precios: precio,
                piezas: $(".cantidad_" + id_form + "_" + i).val()
            });
        }

        datos = {
            _token: token,
            id_envio: id_envio,
            dae: $("form#" + form + " #dae").val(),
            guia_madre: $("form#" + form + " #guia_madre").val(),
            guia_hija: $("form#" + form + " #guia_hija").val(),
            codigo_pais: $("form#" + form + " #codigo_pais").val(),
            email: $("form#" + form + " #email").val(),
            telefono: $("form#" + form + " #telefono").val(),
            direccion: $("form#" + form + " #direccion").val(),
            fecha_envio: $("form#" + form + " #fecha_envio").val(),
            aerolinea: $("form#" + form + " #aerolinea option:selected").data('id_aerolinea'),
            precios: arrDataPrecio,
            almacen: $("form#" + form + " #almacen").val(),
            tipo_pedido: tipo_pedido,
            codigo_dae: $("#codigo_dae").val(),
            consignatario: $("#consignatario").val(),
            //id_configuracion_empresa: $("form#" + form + " #id_empresa").val(),
        };

        $.post('envio/actualizar_envio', datos, function (retorno) {
            if (retorno.success) {
                cerrar_modals();
                if (vista === "pedidos/facturar_pedido")
                    facturar_pedido(id_pedido);
                if (vista === "envio/buscar")
                    buscar_listado_envios();
                modal_view('modal_editar_envio', retorno.mensaje, '<i class="fa fa-user-plus" aria-hidden="true"></i> Editar pedido', true, false, '50%');
            } else {
                alerta(retorno.mensaje);
            }
        }, 'json').fail(function (retorno) {
            alerta(retorno.responseText);
            alerta('Ha ocurrido un problema al guardar los datos del envío');
        }).always(function () {
            $.LoadingOverlay('hide');
        });
    }
}

function factura_tercero(id_envio, token, id_pedido, vista) {
    datos = {
        id_envio: id_envio
    };
    $.get('envio/factura_cliente_tercero', datos, function (retorno) {
        modal_form('modal_factura_cliente_tercero', retorno, '<i class="fa fa-user-plus" aria-hidden="true">' +
            '</i> Datos del cliente a facturar', true, false, '75%', function () {
                store_datos_factura_cliente_tercero(id_envio, token, id_pedido, vista);
            });
    }).always(function () {
        $.LoadingOverlay("hide");
    });
}

function store_datos_factura_cliente_tercero(id_envio, token, id_pedido, vista) {
    if ($('#form_add_cliente_factura_tercero').valid()) {
        $.LoadingOverlay('show');
        datos = {
            _token: token,
            id_factura_cliente_tercero: $('#id_factura_cliente_tercero').val(),
            id_envio: id_envio,
            nombre: $('#nombre_cliente_tercero').val(),
            identificacion: $('#identificacion').val(),
            codigo_pais: $("#pais_cliente_tercero").val(),
            provincia: $("#provincia_cliente_tercero").val(),
            correo: $("#correo_cliente_tercero").val(),
            telefono: $("#telefono_cliente_tercero").val(),
            direccion: $("#direccion_cliente_tercero").val(),
            codigo_impuesto: $("#codigo_impuesto").val(),

            tipo_identificacion: $('#tipo_identificacion').val(),
            codigo_impuesto_porcentaje: $('#tipo_impuesto').val(),
            almacen: $('#almacen_cliente_tercero').val(),
            dae: $('#dae_cliente_tercero').val(),
            puerto_entrada: $('#puerto_entrada').val(),
            tipo_credito: $('#tipo_credito').val(),
            marca: $('#marca').val(),
            codigo_dae: $("#codigo_dae_cliente_tercero").val()
        };
        post_jquery('envio/store_datos_factura_cliente_tercero', datos, function () {
            cerrar_modals();
            if (vista === "pedidos/facturar_pedido")
                facturar_pedido(id_pedido);
            if (vista === "envio/buscar")
                buscar_listado_envios();
        });
        $.LoadingOverlay('hide');
    }
}

function delete_factura_tercero(id_envio, token, id_pedido, vista) {
    modal_quest('modal_message_delete_factura_tercero',
        '<div class="alert alert-warning text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Esta seguro que desea eliminar los datos del cliente a facturar?</div>',
        '<i class="fa fa-file-code-o" aria-hidden="true"></i>Datos de facturación', true, false, '40%', function () {
            $.LoadingOverlay('show');
            datos = {
                _token: token,
                id_envio: id_envio,
            };
            post_jquery('envio/delete_datos_factura_cliente_tercero', datos, function () {
                cerrar_modals();
                if (vista === "pedidos/facturar_pedido")
                    facturar_pedido(id_pedido);
                if (vista === "envio/buscar")
                    buscar_listado_envios();
            });
            $.LoadingOverlay('hide');
        });
}

function agregar_correo(form) {
    cant_input = $('form#' + form + " div#correos_extras div#div_correos").length;
    datos = {
        cant_input: cant_input
    };
    $.get('envio/agregar_correo', datos, function (retorno) {
        $('form#' + form + " div#correos_extras").append(retorno);
    });
}

function eliminar_correo(form) {
    $('form#' + form + " div#correos_extras div:last-child").remove();
}

function cambiar_input_precio(idDetEmp, id_precio, posicon_variedad) {
    $('#td_precio_variedad_' + idDetEmp + '_' + id_precio).html('<input type="number" id="precio_' + id_precio + '_' + posicon_variedad + '" ' +
        'name="precio_' + idDetEmp + '" min="0" value="0" onchange="calcular_precio_pedido()" class="form-control text-center precio_' + id_precio + '" style="background-color: beige; width: 100%;text-align: left" required>');

}

function borrar_duplicado() {
    $(".tr_remove_" + $(".input_cantidad").length).remove();
    calcular_precio_pedido();
}

function busqueda_camiones_conductores(id_form) {
    datos = {
        id_transportista: $("form#" + id_form + " #id_transportista").val()
    };
    $.get('despachos/list_camiones_conductores', datos, function (retorno) {
        $('#id_camion').html('');
        $('#id_chofer').html('');
        $.each(retorno.camiones, function (i, j) {
            $("form#" + id_form + " #id_camion").append("<option id='camion_dinamic' value='" + j.id_camion + "'>" + j.modelo + " (" + j.placa + ")</option>");
            if (i === 0)
                $("#n_placa").val(j.placa);
        });
        $.each(retorno.conductores, function (i, j) {
            $("form#" + id_form + " #id_chofer").append("<option id='chofer_dinamic' value='" + j.id_conductor + "'>" + j.nombre + "</option>")
        });

    }).always(function () {
        $.LoadingOverlay('hide');
    });
}

function busqueda_placa_camion(id_form) {
    datos = {
        id_camion: $("form#" + id_form + " #id_camion").val()
    };
    $.get('despachos/list_placa_camion', datos, function (retorno) {
        $("form#" + id_form + " #n_placa").val(retorno.placa);
    }).always(function () {
        $.LoadingOverlay('hide');
    });
}

function preparacion_flor(token, id_configuracion_empresa) {
    $.LoadingOverlay('show');
    $.ajax({
        type: "POST",
        dataType: "html",
        contentType: "application/x-www-form-urlencoded",
        url: 'despachos/exportar_preparacion_flor',
        data: {
            fecha_desde: $("#fecha_pedidos_search").val(),
            fecha_hasta: $("#fecha_pedidos_search_hasta").val(),
            id_configuracion_empresa,
            _token: token
        },
        success: function (data) {
            var opResult = JSON.parse(data);
            var $a = $("<a>");
            $a.attr("href", opResult.data);
            $("body").append($a);
            $a.attr("download", "PREPARACIÓN DE FLOR " + $("#fecha_pedidos_search").val() + " " + $("#fecha_pedidos_search_hasta").val() + " .xlsx");
            $a[0].click();
            $a.remove();
            cerrar_modals();
            $.LoadingOverlay('hide');
        }
    });
}

function exportar_excel_listado_despacho(token, id_configuracion_pedido) {
    $.LoadingOverlay('show');
    $.ajax({
        type: "POST",
        dataType: "html",
        contentType: "application/x-www-form-urlencoded",
        url: 'despachos/exportar_listado_pedidos_despacho',
        data: {
            fecha_pedido: $("#fecha_pedidos_search").val(),
            fecha_pedido_hasta: $("#fecha_pedidos_search_hasta").val(),
            id_configuracion_empresa: id_configuracion_pedido,
            fecha: $("#fecha_pedidos_search").val(),
            _token: token
        },
        success: function (data) {
            var opResult = JSON.parse(data);
            var $a = $("<a>");
            $a.attr("href", opResult.data);
            $("body").append($a);
            $a.attr("download", "Despacho " + $("#fecha_pedidos_search").val() + " .xlsx");
            $a[0].click();
            $a.remove();
            cerrar_modals();
            $.LoadingOverlay('hide');
        }
        /*fail: function (retorno){
            console.log;
            alerta_errores(retorno.responseText);
        }*/
    });
}

function exportar_listado_cuarto_frio(token) {
    $.LoadingOverlay('show');
    $.ajax({
        type: "POST",
        dataType: "html",
        contentType: "application/x-www-form-urlencoded",
        url: 'despachos/exportar_pedidos_despacho_cuarto_frio',
        data: {
            fecha_pedido: $("#fecha_pedidos_search").val(),
            _token: token
        },
        success: function (data) {
            var opResult = JSON.parse(data);
            var $a = $("<a>");
            $a.attr("href", opResult.data);
            $("body").append($a);
            $a.attr("download", "Despachos cuarto frio" + $("#fecha_pedidos_search").val() + " .xlsx");
            $a[0].click();
            $a.remove();
            cerrar_modals();
            $.LoadingOverlay('hide');
        }
    });
}

function listar_productos_vinculados() {
    $.LoadingOverlay('show');
    datos = {
        id_configuracion_empresa: $("#id_configuracion_empresa_productos").val()
    };
    $.get('/producto_venture/listar_productos_vinculados', datos, function (retorno) {
        $('#div_listado_codigo_prodcutos').html(retorno);
        estructura_tabla('table_productos_viculados');
    }).always(function () {
        $.LoadingOverlay('hide');
    });
}

function listar_productos() {
    $.LoadingOverlay('show');
    datos = {
        id_configuracion_empresa: $("#id_configuracion_empresa_productos").val()
    };
    $.get('/producto_venture/listar_productos', datos, function (retorno) {
        listar_productos_vinculados();
        $("option.option_dinamico").remove();

        //return false;
        $.each(retorno.articulo_venture, function (i, j) {
            $("select#presentacion_venture").append('<option class="option_dinamico" value="' + i + '">' + j + '</option>');
        });
    }).always(function () {
        $.LoadingOverlay('hide');
    });
}

function cuenta_ramos(input) {
    id = input.id.split("_")[1];
    $("#codigo_presentacion_" + id).val("");
    $("#codigo_venture_" + id).val("");
    if (input.value === "T") {
        $("td.td_presentacion_" + id + " span").html($(input).find("option")[1].innerHTML);
        $.each($("select.empaque_" + id + " option"), function (i, j) {
            if ($(this)[0].value === "T")
                $(this).attr('selected', true);
            $(".cantidad_" + (i + 1)).attr('title', "Ingrese el total de mallas");
        });
        calibre_estandar = parseInt($("#calibre_estandar").val());
        ramos_x_caja_conf_empresa = parseInt($("#ramos_x_caja_conf_empresa").val());

        //$.each($(".input_cantidad"),function (i) {
        //$.each($("td.td_calibre_"+(i+1)),function(k,l) {
        calibre = parseInt($("td.td_calibre_" + id).find("span")[0].innerText);
        html = '<input type="text" id="input_tallos_' + id + '" name="input_tallos_' + id + '" class="input_tallos_' + id + '" ' +
            'onkeyup="calcular_precio_pedido(this)" onchange="crear_orden_pedido(this)" ' +
            'value="0" style="width:100%;border:none;text-align:center;height: 34px;" ' +
            'title="Escribe la cantidad de tallos por malla">';
        $("td.td_tallos_x_ramo_" + id + " span").html(html);

        calibre_actual = parseInt($("td.td_calibre_" + id + " span").html());
        calibre_promedio = calibre_actual / calibre_estandar;
        ramos_x_caja = ramos_x_caja_conf_empresa / calibre_promedio;
        $("#input_tallos_" + id).val(25);
        $("td.ramos_x_caja_" + id + " span").html(ramos_x_caja.toFixed(0));
        //});
        // //  //});
        $(".th_tallo_x_malla, .td_tallos_x_malla").removeClass('hide');
        data = {
            id_variedad: $(".input_variedad_" + id).val(),
            id_clasificacion_ramo: $("#id_clasificacion_ramo_" + id).val(),
            id_u_m_clasificacion_ramo: $("#u_m_clasificacion_ramo_" + id).val(),
            tallos_x_ramos: $(".tallos_x_ramo_" + id).val(),
            longitud_ramo: $(".longitud_ramo_" + id).val(),
            id_u_m_logitud_ramo: $(".u_m_longitud_ramo_" + id).val(),
            id_configuracion_empresa: $("#id_configuracion_empresa").val(),
            tallos_x_malla: "tallos_x_malla"
        };
        $.get('clientes/buscar_codigo_venture', data, function (retorno) {
            if (retorno['presentacion_venture'] === "" || retorno['codigo_venture'] === "") {
                $("#error_codigo_venture").html('<b>La presentación base transformada a ' + $("select.empaque_" + id + " option:selected").text() + ' no esta vinculada aún con su código del venture de la empresa ' + $("select#id_configuracion_empresa option:selected").text() + "</b>");
                $("button.store_pedido_normal").attr('disabled', true);

            } else {
                $("#codigo_presentacion_" + id).val(retorno['presentacion_venture']);
                $("#codigo_venture_" + id).val(retorno['codigo_venture']);
                $("button.store_pedido_normal").removeAttr('disabled');
                $("#error_codigo_venture").html("");
            }
        }).always(function () {
            $.LoadingOverlay('hide');
        });

    }
    else {
        $.each($("select.empaque_" + id + " option"), function (i, j) {
            $(this).removeAttr('selected');
            if ($(this)[0].value !== "T") {
                $(".cantidad_" + (i + 1)).removeAttr('title');
                $(this).attr('selected', true);
            }
        });

        //$.each($(".input_cantidad_"+id),function (i) {
        //$.each($("td.td_calibre_"+(i+1)),function(k,l){
        $("td.td_presentacion_" + id + " span").html($("input.input_presentacion_" + id).val());
        $("td.ramos_x_caja_" + id + " span").html($("input.input_ramos_x_caja_" + id).val());
        $("td.td_tallos_x_ramo_" + id + " span").html($("input.tallos_x_ramo_" + id).val());
        // });
        //});
        z = 0;
        $.each($("select.empaque"), function (i, j) {
            if (j.value === "T")
                z++;
        });
        if (z === 0)
            $(".th_tallo_x_malla, .td_tallos_x_malla").addClass('hide');

        $("button.store_pedido_normal").removeAttr('disabled');
        $("#error_codigo_venture").html("");
    }
    calcular_precio_pedido(input);
}

function calcular_precio_pedido(combo = null, n_caja = null) {

    let tabla_pedido = $("table#table_productos_pedidos")
    let total_piezas = 0
    let total_ramos = 0
    let sub_total = 0

    let tabla_productos_simples = $("tbody.tbody_inputs_pedidos_simples")

    $.each(tabla_productos_simples.find('input.input_cantidad'), function () {

        let p = $(this).val()

        if (p > 0) {

            let rxc_producto = $(this).parent().parent().find('input.input_ramos_x_caja').val()
            let total_ramos_prodcuto = rxc_producto * p
            let html_tallos_x_ramo_producto = $(this).parent().parent().find('td.td_tallos_x_ramo_producto span').html()

            let tallos_x_ramo_producto = typeof html_tallos_x_ramo_producto != 'undefined' ? html_tallos_x_ramo_producto.trim() : $(this).parent().parent().find('td.td_tallos_x_ramo_producto input.input_tallos_x_ramos').val()

            $(this).parent().parent().find('td.td_total_ramos').html(total_ramos_prodcuto)
            $(this).parent().parent().find('td.total_tallos_producto').html(total_ramos_prodcuto * tallos_x_ramo_producto)

            if (!!combo) {
                $(this).parent().parent().parent().find('input.precio_x_ramo').css({ 'background-color': '#d9aa00', 'color': 'white' })
            } else {
                $(this).parent().parent().find('input.precio_x_ramo').css({ 'background-color': '#d9aa00', 'color': 'white' })
            }

        } else {

            $(this).parent().parent().find('td.td_total_ramos').html(0)
            $(this).parent().parent().find('td.total_tallos_producto').html(0)

            if (!!combo) {
                $(this).parent().parent().parent().find('input.precio_x_ramo').css({ 'background-color': 'beige', 'color': 'black' })
            } else {
                $(this).parent().parent().find('input.precio_x_ramo').css({ 'background-color': 'beige', 'color': 'black' })
            }

        }

    })

    $.each(tabla_pedido.find('input.input_cantidad'), function (i, cajas) {

        let id = $(this).data('id_tr_producto')
        let piezas = isNaN(parseFloat($(this).val())) ? 0 : parseFloat($(this).val())
        let rxc = 0
        let percio_total_producto = 0

        $.each($("input.input_ramos_x_caja_" + id), function () {
            rxc += isNaN(parseFloat($(this).val())) ? 0 : parseFloat($(this).val())
        })
        let html_tallos_x_ramos = $(this).parent().parent().find('td.td_tallos_x_ramo_producto span').html()
        let total_ramos_x_caja = piezas * rxc
        let tallos_x_ramo_pedido = html_tallos_x_ramos == '' ? $(this).parent().parent().find('td.td_tallos_x_ramo_producto input.input_tallos_x_ramo').val() : html_tallos_x_ramos.trim()
        $(this).parent().parent().find('td.total_tallos_producto').html(total_ramos_x_caja * tallos_x_ramo_pedido)

        $.each($("input.precio_x_ramo_" + id), function (i, obj) {

            if ($(cajas).val() > 0) {
                $(obj).css({ 'background-color': '#d9aa00', 'color': 'white' })
            } else {
                $(obj).css({ 'background-color': 'beige', 'color': 'black' })
            }

            let ramo_x_caja = $("input.input_ramos_x_caja_" + id)[i].value
            percio_total_producto += (isNaN(parseFloat($(this).val())) ? 0 : parseFloat($(this).val()) * ramo_x_caja * piezas)
        })

        total_piezas += piezas
        total_ramos += total_ramos_x_caja
        sub_total += percio_total_producto

        $("td.td_precio_total_" + id).html(percio_total_producto.toFixed(2))
        $("td.td_total_ramos_" + id).html(total_ramos_x_caja)

    })

    let impuesto = sub_total * ($("#iva_cliente").val() / 100)

    $("#total_piezas").html(total_piezas)
    $("#total_ramos").html(total_ramos)
    $("td.monto_total_pedido").html("$" + sub_total)
    $("td.iva_pedido").html("$" + impuesto.toFixed(2))
    $("td.total_pedido").html("$" + (sub_total + impuesto).toFixed(2))

    // ALERTA RAMOS POR CAJA
    if (combo != null) {

        let id = $(combo).data('id_tr_producto')
        let piezas = $('input.input_cantidad_' + id).val()

        let total_ramos_x_caja = 0
        let total_tallos = 0

        $.each($("input.input_ramos_x_caja_" + id), function (i) {
            rxc = (isNaN(parseFloat($(this).val())) ? 0 : parseFloat($(this).val()))
            total_ramos_x_caja += rxc
            txr = $("input.tallos_x_ramo_" + id)[i].value
            total_tallos += rxc * txr
        })

        switch ($("select.empaque_" + id).attr('data-siglas_empaque')) {
            case 'HB':
                rxc_permitidos = 20 * piezas
                break
            case 'QB':
                rxc_permitidos = 10 * piezas
                break
            case 'EB':
                rxc_permitidos = 5 * piezas
                break
            default:
                rxc_permitidos = 0
        }

        $("td.td_total_ramos_" + id).html(total_ramos_x_caja * piezas)
        $("td.total_tallos_producto_" + id).html(total_tallos * piezas)
        console.log(total_ramos_x_caja, piezas, total_ramos_x_caja * piezas, rxc_permitidos)
        if ((total_ramos_x_caja * piezas) != rxc_permitidos) {
            $("#div_error_cajas_" + n_caja).show().html(`<b> <i class="fa fa-exclamation-triangle"></i> LA CANTIDAD DE RAMOS POR CAJA NO ES CORRECTA </b>`)
        } else {
            $("#div_error_cajas_" + n_caja).hide()
        }

    }

}

function unificar_pedidos(token) {

    let id_pedidos = []
    let tipo_pedido = ''
    let id_cliente = $("#id_cliente").val()

    $.each($("input.check_unir_pedidos"), function () {

        if ($(this).is(":checked")) {

            id_pedidos.push($(this).val())
            tipo_pedido = $(this).data('tipo_pedido')

        }

    })

    let html = ''


    if (tipo_pedido == 'STANDING ORDER') {

        html = `<div class="alert alert-warning text-center" style="margin-top:5px">
                    <p style="font-size: 16px;font-weight: bold;"><b>INDIQUE QUE TIPO DE PEDIDO SE CREARÁ</b></p>
                    <div style="margin: 13px auto;width: 176px;">
                        <select class="form-control form-control-sm" id="tipo_nuevo_pedido" onchange="select_unificar_tipo_pedido(this)">
                            <option value="STANDING ORDER">STANDING ORDER</option>
                            <option value="OPEN MARKET">OPEN MARKET</option>
                        </select>
                    </div>
                    <p style="font-size: 16px;font-weight: bold;"><b>INDIQUE SI LOS PEDIDOS SE UNIRÁN HASTA LA ÚLTIMA FECHA CREADA</b></p>
                    <div style="display: flex;align-items: center;justify-content: space-evenly;">
                        <div>
                            <input type="radio" name="check_unir_futuro" value="NO" style="width: 18px;height: 18px;" id="no_une_futuro" onchange="check_unificar_tipo_pedido(this)">
                            <label style="font-size: 18px;position: relative;bottom: 4px;" for="no_une_futuro">  <b>NO</b> </label>
                        </div>
                        <div>
                            <input type="radio" name="check_unir_futuro" value="SI" style="width: 18px;height: 18px;" id="une_futuro" checked onchange="check_unificar_tipo_pedido(this)">
                            <label style="font-size: 18px;position: relative;bottom: 4px;" for="une_futuro"> <b>SI </b></label>
                        </div>
                    </div>
                </div>`

    } else {

        html = `<div class="alert alert-warning text-center">Esta seguro de unificar los pedidos seleccionados?</div>`

    }

    if (!id_pedidos.length) {
        modal_view('modal_unificar_pedido', '<div class="alert alert-danger text-center">Debe seleccionar al menos un pedido</div>', '<i class="fa fa-clone"></i> Unificar pedidos', true, false, '50%');
        return
    }

    if (id_cliente == '') {
        modal_view('modal_unificar_pedido', '<div class="alert alert-danger text-center">Debe seleccionar un cliente</div>', '<i class="fa fa-clone"></i> Unificar pedidos', true, false, '50%');
        return
    }

    if (tipo_pedido == 'STANDING ORDER' && $("#tipo_pedido").val() == '') {
        modal_view('modal_unificar_pedido', '<div class="alert alert-danger text-center">Debe seleccionar un tipo de pedido</div>', '<i class="fa fa-clone"></i> Unificar pedidos', true, false, '50%');
        return
    }

    modal_quest('modal_crear_pedido_unificado', html, "<i class='fa fa-clone'></i> Unificar pedidos", true, false, '40%', function () {

        if (tipo_pedido == 'STANDING ORDER')
            tipo_pedido = $("select#tipo_nuevo_pedido").val()

        let datos = {
            _token: token,
            id_pedidos,
            id_cliente,
            tipo_pedido,
            unir_futuro: $("input[name='check_unir_futuro']:checked").val()
        }

        post_jquery_m('despachos/unificar_pedidos', datos, function () {

            listar_resumen_pedidos(
                document.getElementById('fecha_pedidos_search').value,
                true,
                document.getElementById('id_configuracion_pedido').value,
                document.getElementById('id_cliente').value
            )

        })

    })

}

function check_unificar_tipo_pedido(input) {

    $("select#tipo_nuevo_pedido option").removeAttr('selected')

    if ($(input).val() == 'SI') {

        $("select#tipo_nuevo_pedido option[value='STANDING ORDER']").attr('selected', true)
        $("select#tipo_nuevo_pedido").removeAttr('disabled')

    } else {

        $("select#tipo_nuevo_pedido option[value='OPEN MARKET']").attr('selected', true)
        setTimeout(() => {
            $("select#tipo_nuevo_pedido").attr('disabled', true)
        }, 500);

    }



}

function select_unificar_tipo_pedido(select) {

    $.each($("input[name='check_unir_futuro']"), function () {

        if ($(select).val() == 'OPEN MARKET') {

            if ($(this).val() == 'SI') {

                $(this).prop('checked', false).attr('disabled', true)

            } else {

                $(this).prop('checked', true)

            }

        } else {

            if ($(this).val() == 'SI')
                $(this).removeAttr('disabled')

        }

    })

}

function habilita_unir_pedidos(check) {

    let cant_chequedos = $("input.check_unir_pedidos:checked").length

    let fecha_base = $(check).data('fecha_pedido')

    /* if(cant_chequedos == 1){

        $.each($("input.check_unir_pedidos"),function(){

            if($(this).data("fecha_pedido") != fecha_base){
                $(this).prop("disabled", true)
            }else{
                $(this).prop("disabled", false)
            }

        })

    }else if(cant_chequedos == 0){

        $.each($("input.check_unir_pedidos"),function(){
            $(this).prop("disabled", false)
        })

    } */

}

function dividir_marcaciones(token) {

    let detalles_pedido = []
    let id_cliente = $("#id_cliente").val()
    let tipo_pedido = ''

    $.each($("input.check_pedido_marcacion:checked"), function () {

        detalles_pedido.push({
            id_det_ped: $(this).data('id_det_pedido'),
            id_pedido: $(this).data('id_pedido'),
            tipo_pedido: $(this).data('tipo_pedido')
        })

        if (tipo_pedido == '' && $(this).data('tipo_pedido') == 'STANDING ORDER')
            tipo_pedido = $(this).data('tipo_pedido')

    })

    let html = ''

    if (tipo_pedido == 'STANDING ORDER') {

        html = `<div class="alert alert-warning text-center hidden" style="margin-top:5px">
                    <p style="font-size: 16px;font-weight: bold;"><b>INDIQUE SI EL STANDING SE EDITARÁ HASTA LA ÚLTIMA FECHA CREADA</b></p>
                    <div style="display: flex;align-items: center;justify-content: space-evenly;">
                        <div>
                            <input type="radio" name="check_edita_futuro" value="NO" style="width: 18px;height: 18px;" id="no_edita_futuro" checked>
                            <label style="font-size: 18px;position: relative;bottom: 4px;" for="no_edita_futuro">  <b>NO</b> </label>
                        </div>
                        <div>
                            <input type="radio" name="check_edita_futuro" value="SI" style="width: 18px;height: 18px;" id="edita_futuro">
                            <label style="font-size: 18px;position: relative;bottom: 4px;" for="edita_futuro"> <b>SI </b></label>
                        </div>

                    </div>
                </div>`

    } else {
        html = `<div class="alert alert-warning text-center">Esta seguro de dividir las marcaciones seleccionadas?</div>`
    }

    if (!detalles_pedido.length) {
        modal_view('modal_dividir_marcaciones', '<div class="alert alert-danger text-center">Debe seleccionar una marcación</div>', '<i class="fa fa-list-alt"></i> Dividir marcaciones', true, false, '50%');
        return
    }

    if (id_cliente == '') {
        modal_view('modal_dividir_marcaciones', '<div class="alert alert-danger text-center">Debe seleccionar un cliente</div>', '<i class="fa fa-list-alt"></i> Dividir marcaciones', true, false, '50%');
        return
    }

    modal_quest('modal_dividir_marcaciones', html, "<i class='fa fa-clone'></i> Unificar pedidos", true, false, '40%', function () {

        let datos = {
            _token: token,
            detalles_pedido,
            edicion_futura: $("input[name='check_edita_futuro']:checked").val()
        }

        post_jquery_m('despachos/dividir_marcaciones', datos, function () {
            listar_resumen_pedidos(
                document.getElementById('fecha_pedidos_search').value,
                true,
                document.getElementById('id_configuracion_pedido').value,
                document.getElementById('id_cliente').value
            )
        })

    })

}

function importar_jire(token) {

    $.LoadingOverlay('show');
    $.ajax({
        type: "POST",
        dataType: "html",
        contentType: "application/x-www-form-urlencoded",
        url: 'despachos/exportar_jire_cabecera',
        data: {
            fecha_desde: $("#fecha_pedidos_search").val(),
            fecha_hasta: $("#fecha_pedidos_search_hasta").val(),
            _token: token
        },
        success: function (data) {
            var opResult = JSON.parse(data);
            var $a = $("<a>");
            $a.attr("href", opResult.data);
            $("body").append($a);
            $a.attr("download", "PACKING JIRE " + $("#fecha_pedidos_search").val() + " - " + $("#fecha_pedidos_search_hasta").val() + ".xlsx");
            $a[0].click();
            $a.remove();
            cerrar_modals();
            $.LoadingOverlay('hide');

            /* $.ajax({
                type: "POST",
                dataType: "html",
                contentType: "application/x-www-form-urlencoded",
                url: 'despachos/exportar_jire_detalle_cabecera',
                data: {
                    fecha_desde: $("#fecha_pedidos_search").val(),
                    fecha_hasta: $("#fecha_pedidos_search_hasta").val(),
                    _token: token
                },
                success: function (data) {
                    var opResult = JSON.parse(data);
                    var $a = $("<a>");
                    $a.attr("href", opResult.data);
                    $("body").append($a);
                    $a.attr("download", "DETALLE PACKING "+$("#fecha_pedidos_search").val()+" - "+$("#fecha_pedidos_search_hasta").val()+".xlsx");
                    $a[0].click();
                    $a.remove();
                    cerrar_modals();
                    $.LoadingOverlay('hide');
                }
            }); */

        }
    })

}

function descargar_packings(token) {
    $.LoadingOverlay('show')
    ids_pedidos = [];
    check_unir_pedidos = $('.check_unir_pedidos');
    for (i = 0; i < check_unir_pedidos.length; i++) {
        id_check = check_unir_pedidos[i].id;
        if ($('#' + id_check).prop('checked') == true) {
            ids_pedidos.push($('#' + id_check).val());
        }
    }

    $.ajax({
        type: 'GET',
        url: 'despachos/descargar_packings_unificados',
        data: {
            _token: token,
            desde: $("#fecha_pedidos_search").val(),
            hasta: $("#fecha_pedidos_search_hasta").val(),
            id_cliente: $("#id_cliente").val(),
            tipo_pedido: $("#tipo_pedido").val(),
            ids_pedidos: ids_pedidos
        },
        xhrFields: {
            responseType: 'blob'
        },
        success: function (response) {
            var blob = new Blob([response]);
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = "Packings " + $("#fecha_pedidos_search").val() + " - " + $("#fecha_pedidos_search_hasta").val() + ".pdf";
            link.click();
        },
        error: function (blob) {
            console.log(blob);
        },

    }).always(() => {
        $.LoadingOverlay('hide');
    });

}

/*$(function(){
    $(document).bind("contextmenu",function(e){
        return false;
    });
});*/

/* ------------------------ Nuevo Diseño ------------------------------- */

