<script>
    vista_actual = 'despachos';
    listar_resumen_pedidos($("#fecha_pedidos_search").val(), '', $("#id_configuracion_empresa_despacho").val());

    $("#id_cliente, #id_agencia_carga").select2()

    function update_despacho_detalle(e, id_detalle_despacho) {
        e.preventDefault();
        const id_despacho_anterior = $('#select_detalle_despacho' + id_detalle_despacho).attr(
            'data-id_despacho_anterior');
        const datos = {
            _token: '{{ csrf_token() }}',
            id_despacho: e.currentTarget.value,
            id_detalle_despacho
        };

        post_jquery('/despachos/update_despacho_detalle', datos, function(result) {
            if (result.success) {
                // const classRowDetalleDespacho = '.detalle_despacho_tr' + datos.id_despacho;
                // const classDetalleDespacho_td_n_despacho = '.detalle_despacho_n_despacho' + datos.id_despacho;
                // const idRowDetalleDespacho = '#detalle_despacho_tr' + id_detalle_despacho;
                // const idDetalleDespacho_td_n_despacho = '#detalle_despacho_n_despacho' + id_detalle_despacho;
                // //Captura de estilo y n_despacho de un Despacho con el id_despacho nuevo
                // const rowStyle = $( classRowDetalleDespacho + ':first' ).attr('style');
                // const new_n_despacho = $( classDetalleDespacho_td_n_despacho + ':first' ).html();
                // //Se setean los valores nuevos en el detalle_despacho actualizado.
                // $(idRowDetalleDespacho).attr('style',rowStyle);
                // $(idDetalleDespacho_td_n_despacho).html(new_n_despacho);
                // //Se borran las clases del detalle_despacho cambiado
                // $(idRowDetalleDespacho).removeClass('detalle_despacho_tr' + id_despacho_anterior);
                // $(idDetalleDespacho_td_n_despacho).removeClass('detalle_despacho_n_despacho' + id_despacho_anterior);
                // //Se agregan las nuevas clases del detalle_despacho cambiado con los valores guardados anteriormente
                // $(idRowDetalleDespacho).addClass('detalle_despacho_tr' + datos.id_despacho);
                // $(idDetalleDespacho_td_n_despacho).addClass('detalle_despacho_n_despacho' + datos.id_despacho);

                $('#button_busqueda_detalles_despacho').click();
                //Se guarda el nuevo valor de id_despacho como el anterior para usos futuros
                $('#select_detalle_despacho' + id_detalle_despacho).attr('data-id_despacho_anterior', datos
                    .id_despacho);
                mini_alerta('success', 'Despacho actualizado con éxito.', 5000);
            } else {
                $('#select_detalle_despacho' + id_detalle_despacho).val(id_despacho_anterior);
                mini_alerta('error',
                    'Se ha intentado actualizar el despacho sin éxito, por favor vuelve a intentar nuevamente.',
                    4000);
            }
        }, false, true);
    }

    function empaquetar(fecha) {
        $.LoadingOverlay('show');
        datos = {
            fecha: fecha,
        };
        $.get('{{ url('despachos/empaquetar') }}', datos, function(retorno) {
            modal_view('modal_view_empaquetar', retorno, '<i class="fa fa-fw fa-gift"></i> Empaquetar', true,
                false, '{{ isPc() ? '35%' : '' }}');
        }).always(function() {
            $.LoadingOverlay('hide');
        });
    }

    function crear_despacho() {

        /* var pedidos =[];

         $.each($(".orden_despacho"),function (i,j) {
             if($(this).is(':checked'))
                 pedidos.push($(this).data('id_pedido'))
         }); */

        var arr_pedidos = [],
            arr_ordenado, pedidos = [];
        $.each($(".orden_despacho"), function(i, j) {
            if ($(j).is(':checked'))
                arr_pedidos.push(i);
        });

        arr_ordenado = arr_pedidos.sort(menor_mayor);
        for (x = 0; x < arr_ordenado.length; x++) {
            $.each($(".orden_despacho"), function(i, j) {
                if (($(j).is(':checked')) && (!pedidos.includes(j.id)))
                    pedidos.push(j.id)
            });
        }

        if (pedidos.length === 0) {
            modal_view('modal_view_msg_factura',
                '<div class="alert text-center  alert-warning"><p><i class="fa fa-fw fa-exclamation-triangle"></i> Debe ordenar al menos un pedido para crear el despacho</p></div>',
                '<i class="fa fa-truck" aria-hidden="true"></i> Despacho', true, false, '{{ isPC() ? '50%' : '' }}');
            return false;
        }

        $.LoadingOverlay('show')

        datos = {
            _token: '{{ csrf_token() }}',
            pedidos
        }

        $.post('{{ url('despachos/crear_despacho') }}', datos, function(retorno) {
            modal_form('modal_despacho', retorno, '<i class="fa fa-truck" ></i> Crear despacho', true, false,
                '{{ isPC() ? '80%' : '' }}',
                function() {
                    store_despacho();
                    $.LoadingOverlay('hide');
                })
        })

    }

    function store_despacho() {
        form_valid = true;
        cant_form = $("div#despachos form").length;
        for (i = 1; i <= cant_form; i++)
            if (!$("#form_despacho_" + i).valid()) form_valid = false;

        if (form_valid) {
            $.LoadingOverlay('show');
            arr_datos = [];
            arr_sellos = [];
            //arr_pedidos = [];
            for (i = 1; i <= cant_form; i++) {

                data = "";
                data_sellos = [];


                $.each($("tr.tr_sellos"), function() {

                    let sellos_tr = []
                    sellos_tr.push($(this).data('id_agencia_carga'))

                    $.each($(this).find('td'), function() {

                        let sello = $(this).find('input.sello').val()

                        if (sello != '' && typeof sello != 'undefined')
                            sellos_tr.push(sello)

                    })

                    data_sellos.push(sellos_tr)

                })

                /* $.each($("form#form_despacho_"+i+" .sello"),function (i,j) {
                    if(j.value != ""){
                        data_sellos.push(j.value);
                    }
                }); */
                console.log(data_sellos)


                if ($("#table_despacho_2").length == 1) {
                    tr_piezas = $("form#form_despacho_" + i + " tr#tr_pedido_piezas").length;
                    for (j = 1; j <= tr_piezas; j++) {
                        id_pedido = $("select#pedido_" + i + "_" + j).val();
                        cant_piezas_camion = 0;
                        cantidad = "";
                        $.each($("input.caja_" + i + "_" + j), function(l, m) {
                            if (m.value > 0) cant_piezas_camion += parseInt(m.value);
                        });
                        cantidad += cant_piezas_camion + ";";
                        data += id_pedido + "|" + cantidad;
                    }
                } else {
                    pedidos = $("table tr#tr_despachos").length;
                    for (j = 1; j <= pedidos; j++) {
                        id_pedido = $(".id_pedido_" + j).val();
                        full = $("td.full_" + j + " input.full").val();
                        half = $("td.half_" + j + " input.half").val();
                        cuarto = $("td.cuarto_" + j + " input.cuarto").val();
                        //  sexto = $("td.sexto_"+j+" input.sexto").val();
                        octavo = $("td.octavo_" + j + " input.octavo").val();
                        sb = $("td.sb_" + j + " input.sb").val();
                        cantidad = parseInt(full) + parseInt(half) + parseInt(cuarto) + /* parseInt(sexto) + */
                            parseInt(octavo) + parseInt(sb);
                        data += id_pedido + "|" + cantidad + ";";
                    }
                }
                arr_datos.push({
                    arr_sellos: data_sellos,
                    id_transportista: $("form#form_despacho_" + i + " #id_transportista").val(),
                    id_camion: $("form#form_despacho_" + i + " #id_camion").val(),
                    n_placa: $("form#form_despacho_" + i + " #n_placa").val(),
                    id_conductor: $("form#form_despacho_" + i + " #id_chofer").val(),
                    fecha_despacho: $("form#form_despacho_" + i + " #fecha_despacho").val(),
                    sello_salida: $("form#form_despacho_" + i + " #sello_salida").val(),
                    horario: $("form#form_despacho_" + i + " #horario").val(),
                    semana: $("form#form_despacho_" + i + " #semana").val(),
                    rango_temp: $("form#form_despacho_" + i + " #rango_temp").val(),
                    sello_adicional: $("form#form_despacho_" + i + " #sello_adicional").val(),
                    n_viaje: $("form#form_despacho_" + i + " #n_viaje").val(),
                    horas_salida: $("form#form_despacho_" + i + " #horas_salida").val(),
                    temperatura: $("form#form_despacho_" + i + " #temperatura").val(),
                    kilometraje: $("form#form_despacho_" + i + " #kilometraje").val(),
                    nombre_oficina_despacho: $("#nombre_oficina_despacho").val(),
                    id_oficina_despacho: $("#id_oficina_despacho").val(),
                    nombre_cuarto_frio: $("#nombre_cuarto_frio").val(),
                    id_cuarto_frio: $("#id_cuarto_frio").val(),
                    nombre_transportista: $("form#form_despacho_" + i + " #responsable").val(),
                    //firma_id_transportista : $("#firma_id_transportista").val(),
                    nombre_guardia_turno: $("#nombre_guardia_turno").val(),
                    id_guardia_turno: $("#id_guardia_turno").val(),
                    nombre_asist_comercial: $("#nombre_asist_comercial").val(),
                    id_asist_comercial: $("#id_asist_comercial").val(),
                    correo_oficina_despacho: $("#correo_oficina_despacho").val(),
                    distribucion: data
                });
            }

            //$.each($(".id_pedido"),function (i,j) { arr_pedidos.push(j.value); });
            datos = {
                _token: '{{ csrf_token() }}',
                data_despacho: arr_datos,
                //arr_pedidos : arr_pedidos,
            };
            post_jquery('despachos/store_despacho', datos, function() {
                cerrar_modals();
                listar_resumen_pedidos($('#fecha_pedidos_search').val());
                $.LoadingOverlay('hide');
            });

        }
    }

    function duplicar_nombre(input) {
        $("#nombre_transportista").val(input.value);
    }

    function ver_despachos() {
        $.LoadingOverlay('show');
        datos = {
            desde: $('#fecha_pedidos_search').val(),
            hasta: $('#fecha_pedidos_search_hasta').val(),
        }
        $.get('{{ url('despachos/ver_despachos') }}', datos, function(retorno) {
            modal_view('modal_view_despachos', retorno, '<i class="fa fa-truck"></i> Despachos realizados',
                true, false, '{{ isPc() ? '80%' : '' }}');
        }).always(function() {
            $.LoadingOverlay('hide');
        });
    }

    function update_estado_despacho(id_despacho, estado) {
        modal_quest('modal_despacho', "<div class='alert alert-danger text-center'>Desea cancelar este despacho?</div>",
            "<i class='fa fa-exclamation-triangle' ></i> Seleccione una opción", true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                $.LoadingOverlay('show');
                datos = {
                    _token: '{{ csrf_token() }}',
                    id_despacho: id_despacho,
                    estado: estado,
                };
                post_jquery('{{ url('despachos/update_estado_despachos') }}', datos, function() {
                    cerrar_modals();
                    listar_resumen_pedidos($('#fecha_pedidos_search').val(), '', $(
                        "#id_configuracion_empresa_despacho").val());
                    ver_despachos()
                });
                $.LoadingOverlay('hide');
            });
    }

    /*$(document).on("click", "#pagination_listado_despachos .pagination li a", function(e) {
        $.LoadingOverlay("show");
        //para que la pagina se cargen los elementos
        e.preventDefault();
        var url = $(this).attr("href");
        url = url.replace('?', '?busqueda=&');
        $('#div_listado_despachos').html($('#table_despachos').html());
        $.get(url, function(resul) {
            $('#div_listado_despachos').html(resul);
            estructura_tabla('table_content_despachos');
        }).always(function() {
            $.LoadingOverlay("hide");
        });
    })*/

    function desbloquea_pedido() {
        /* if($("#id_configuracion_empresa_despacho").val().length < 1){

             $.each($(".orden_despacho"),function (i,j) {
                 $(j).attr('disabled',true);
                 $(j).val("");
             });
         }else{
             $.each($("div#table_despachos input.id_configuracion_empresa_"+$('#id_configuracion_empresa_despacho').val()),function (i,j) {
                 $(j).removeAttr('disabled');
             });

         }
         $.each($("div#table_despachos input").not(".id_configuracion_empresa_"+$('#id_configuracion_empresa_despacho').val()),function (i,j) {
             $(j).attr('disabled',true);
             $(j).val("");
         });*/
        //listar_resumen_pedidos($("#fecha_pedidos_search").val(), '',$("#id_configuracion_empresa_despacho").val());
    }

    function filtrar_listado_despachos_js(select, clase) {

        let value = $(select).find("option:selected").text().replace('&', '').trim().toLowerCase()

        if (value == 'clientes' || value == 'agencias') {
            $("table#table_content_aperturas tbody tr").css('display', 'table-row')
        } else {
            $("table#table_content_aperturas tbody tr").filter(function() {

                if (typeof $(this).find('td span.' + clase).html() != 'undefined') {

                    $(this).toggle(
                        $(this).find('td span.' + clase).html().replace('&', '').replace('amp;', '').trim()
                        .toLowerCase() == value &&
                        $(this).text().replace('&', '').replace('amp;', '').toLowerCase().indexOf(value) > -
                        1
                    )

                }

            })
        }

        $("#check_select_orden").prop('checked', false)


    }

    function select_orden(check) {

        let input = $("input.orden_despacho")

        $.each($('input.orden_despacho'), function() {

            if ($(check).is(':checked')) {

                if ($(this).parent().parent().attr('style').split(' ')[7] == 'table-row;')
                    $(this).prop('checked', true)

            } else {

                if ($(this).parent().parent().attr('style').split(' ')[7] == 'table-row;')
                    $(this).prop('checked', false)

            }

        })

    }

    function select_all_to_dispatch(check) {
        $.each($('input.orden_despacho'), function() {
            if ($(check).is(':checked')) {
                $(this).prop('checked', true)
            } else {
                $(this).prop('checked', false)
            }
        });
    }
</script>
