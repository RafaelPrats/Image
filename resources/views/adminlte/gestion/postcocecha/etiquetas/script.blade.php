<script>
    vista_actual = 'etiqueta';
    listado_etiquetas();

    $("select#id_cliente, select#id_agencia_carga").select2()

    function listado_etiquetas() {
        $.LoadingOverlay('show');
        datos = {
            desde: $('#desde').val(),
            cliente: $('#id_cliente').val(),
            agencia_carga: $('#id_agencia_carga').val(),
            id_configuracion_empresa: $("#id_configuracion_empresa").val()
        };
        $.get('{{ url('etiqueta/listado') }}', datos, function(retorno) {
            $('#div_listado_etiquetas').html(retorno);
            estructura_tabla('table_content_etiqueta')
        }).always(function() {
            $.LoadingOverlay('hide');
        });
    }

    function update_etiqueta_descargada(pedidos) {
        return new Promise((resolve, reject) => {
            let errores = []; // Array para almacenar los errores
            pedidos.forEach((pedido) => {
                const datos = {
                    _token: '{{ csrf_token() }}',
                    id_pedido: pedido.id_pedido
                };

                post_jquery('/etiqueta/update_etiqueta_descargada', datos, function(result) {
                    if (!result.success) {
                        errores.push(result); // Agregar el error al array de errores
                    }
                }, false, true);
            });

            // Resolver o rechazar la promesa dependiendo de si hay errores
            if (errores.length === 0) {
                resolve();
            } else {
                reject(errores);
            }
        });
    }

    function errorIsRetrying(e, pedidos, intentos) {
        if (intentos < 3) {
            mini_alerta('warning', `Reintentando en 3 segundos, nro de reintentos: ${intentos + 1}/3.`, 1000);
            setTimeout(function() {
                descarga_pdf(e, pedidos, intentos + 1);
            }, 3000);
        } else {
            $(e.target).attr('disabled', false);
            pedidos.forEach(function(pedido) {
                $('#tr_etiqueta_' + pedido.id_pedido).LoadingOverlay('hide');
            });
            mini_alerta('error',
                'Se ha intentado descargar el archivo varias veces sin éxito, por favor vuelve a intentar nuevamente.',
                4000);
        }
    }

    function load_pdf(e, pedidos, intentos) {
        const url = '{{ url('etiqueta/ver_pdf') }}';
        const currentDate = new Date();
        let fileName = 'etiqueta_pedido_' + currentDate.toLocaleString().replace(/[/\\?%*:|"<>]/g, '_') + '.pdf';
        $(e.target).attr('disabled', true);
        pedidos.forEach(function(pedido) {
            $('#tr_etiqueta_' + pedido.id_pedido).LoadingOverlay('show');
        });
        $.ajax({
            type: 'POST', // Utilizar el método POST
            url: url,
            contentType: "application/x-www-form-urlencoded",
            data: {
                _token: '{{ csrf_token() }}',
                pedidos
            },
            xhrFields: {
                responseType: 'blob'
            },
            success: function(blob) {
                var a = document.createElement('a');
                var url = window.URL.createObjectURL(blob);
                a.href = url;
                a.download = fileName;
                a.click();
                window.URL.revokeObjectURL(url);
                $(e.target).attr('disabled', false);
                pedidos.forEach(function(pedido) {
                    $('#tr_etiqueta_' + pedido.id_pedido).LoadingOverlay('hide');
                });
                mini_alerta('success', 'Etiqueta descargada con éxito.', 5000);
                if ($('#spinner-download-pdf'))
                    $('#spinner-download-pdf').addClass('hidden');

                update_etiqueta_descargada(pedidos).then(function(result) {
                    pedidos.forEach(function(pedido) {
                        $('#progress-bar_' + pedido.id_pedido).css(
                            'width', '100%');
                        $('#progress-bar_' + pedido.id_pedido)
                            .addClass(
                                'progress-bar-bench-80');
                        $('#progress-bar_' + pedido.id_pedido).find('.sr-only').html(
                            `Descargado <i class="fa fa-check"></i>`);
                    });
                }).catch(function(error) {
                    errorIsRetrying(e, pedidos, intentos);
                });
            },
            error: function(jqXHR, textStatus, errorThrown) {
                errorIsRetrying(e, pedidos, intentos);
            },
            timeout: 100000, // timeout de 10 segundos
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total * 100;
                        pedidos.forEach(function(pedido) {
                            $('#progress-bar_' + pedido.id_pedido).LoadingOverlay('show');
                            $('#progress-bar_' + pedido.id_pedido).find('span.sr-only')
                                .html(percentComplete
                                    .toFixed(0) + '% completado');
                            $('#progress-bar_' + pedido.id_pedido)
                                .css('width',
                                    percentComplete.toFixed(0) + '%');
                            $('#progress-bar_' + pedido.id_pedido)
                                .attr('aria-valuenow',
                                    percentComplete.toFixed(0));
                            $('#progress-bar_' + pedido.id_pedido)
                                .removeClass(
                                    'progress-bar-bench');
                            $('#progress-bar_' + pedido.id_pedido)
                                .removeClass(
                                    'progress-bar-50');
                            $('#progress-bar_' + pedido.id_pedido)
                                .removeClass(
                                    'progress-bar-80');
                            $('#progress-bar_' + pedido.id_pedido)
                                .addClass(
                                    percentComplete.toFixed(0) < 50 ? 'progress-bar-bench' :
                                    (
                                        percentComplete.toFixed(0) >= 50 && percentComplete
                                        .toFixed(0) <
                                        80 ? 'progress-bar-bench-50' :
                                        'progress-bar-bench-80'));
                        });
                    }
                }, false);
                return xhr;
            }
        });
    }

    function descarga_pdf(e, pedidos = [], intentos = 0) {
        e.preventDefault();
        load_pdf(e, pedidos, intentos);
    }

    function select_doble(input) {
        $.each($(".doble"), function(i, j) {
            $(input).is(":checked") ?
                $(j).prop('checked', true) :
                $(j).prop('checked', false);
        });
    }

    function select_exportar(input) {
        $.each($(".exportar"), function(i, j) {
            $(input).is(":checked") ?
                $(j).prop('checked', true) :
                $(j).prop('checked', false);
        });
    }

    function generar_excel() {
        arr_facturas = [];
        cant_facturas = $("#tbody_etiquetas_facturas tr").length;

        for (let x = 1; x <= cant_facturas; x++) {
            $.each($("#tr_exportables_" + x + " .exportar"), function(i, j) {
                if ($(j).is(":checked")) {
                    arr_facturas.push({
                        caja: j.value,
                        id_pedido: $("#tr_exportables_" + x + " .id_pedido").val(),
                        doble: $("#doble_" + j.name.split("_")[1]).is(":checked")
                    });
                }
            });
        }

        if (arr_facturas.length === 0) {
            modal_view('modal_view_msg_factura',
                '<div class="alert text-center  alert-warning"><p><i class="fa fa-fw fa-exclamation-triangle"></i> Debe seleccionar al menos una factura para generar la(s) etiqueta(s)</p></div>',
                '<i class="fa fa-clone"></i> Etiquetas', true, false,
                '{{ isPC() ? '50%' : '' }}');
            return false;
        }

        modal_quest('modal_exportar_etiquetas', "Desea exportar el excel con las facturas seleccionadas?",
            "<i class='fa fa-cubes'></i> Seleccione una opción", true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                $.LoadingOverlay('show');
                $.ajax({
                    type: "POST",
                    dataType: "html",
                    contentType: "application/x-www-form-urlencoded",
                    url: '{{ url('etiqueta/exportar_excel') }}',
                    data: {
                        arr_facturas: arr_facturas,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        var opResult = JSON.parse(data);
                        var $a = $("<a>");
                        $a.attr("href", opResult.data);
                        $("body").append($a);
                        $a.attr("download", "Etiquestas Cajas.xlsx");
                        $a[0].click();
                        $a.remove();
                        cerrar_modals();
                        $.LoadingOverlay('hide');
                    }
                });
            });

    }

    function descargar_etiquetas_all(e) {
        var pedidos = []; // Array para almacenar los objetos de pedido
        $('.double_print_checkbox').each(function() {
            const download_selection = $(this).parent().parent().find('input.pedido_seleccionado').is(
                ':checked');
            if (download_selection) {
                const pedido = {
                    id_pedido: $(this).attr('data-id_pedido'),
                    isDoublePage: $(this).is(':checked')
                };

                pedidos.push(pedido); // Agregar el objeto de pedido al array
            }
        });
        if (getSelectedLabels() <= 200) {
            if (pedidos.length > 0) {
                $('#spinner-download-pdf').removeClass('hidden');
                descarga_pdf(e, pedidos);
            } else {
                mini_alerta('warning', `Debe seleccionar al menos una etiqueta para poder descargar el PDF.`, 1000);
            }
        } else {
            mini_alerta('warning', `Debes seleccionar máximo 200 etiquetas por descarga.`, 1000);
        }
    }

    function getSelectedLabels() {
        let etiquetas = 0;
        let cantidadEtiquetas = 0;
        let isDouble = false;
        $('.pedido_seleccionado').each(function() {
            cantidadEtiquetas = parseInt($(this).attr("data-cantidad_piezas"), 10);
            isDouble = $(this).parent().parent().find(".double_print_checkbox").is(":checked");
            if ($(this).is(":checked")) {
                etiquetas += isDouble ? (cantidadEtiquetas * 2) : cantidadEtiquetas;
            }
        });
        return etiquetas;
    }

    function onChangeSelection() {
        $('#box-selection').html(getSelectedLabels());
    }

    function imprimir_etiquetas() {

        data_pedidos = [];
        cant_facturas = $("#tbody_etiquetas_facturas tr").length;

        $.each($("input.pedido_seleccionado"), function(i, j) {

            if ($(j).is(':checked')) {
                data_pedidos.push({
                    id_pedido: j.value,
                    doble: $(this).parent().parent().find('input.doble').is(':checked'),
                    inicio: $(this).parent().parent().find('input#etiqueta_desde').val(),
                    fin: $(this).parent().parent().find('input#etiqueta_hasta').val(),
                })
            }

        })

        if (data_pedidos.length === 0) {
            modal_view('modal_view_msg_factura',
                '<div class="alert text-center  alert-warning"><p><i class="fa fa-fw fa-exclamation-triangle"></i> Debe seleccionar al menos una factura para generar la(s) etiqueta(s)</p></div>',
                '<i class="fa fa-clone"></i> Etiquetas', true, false,
                '{{ isPC() ? '50%' : '' }}');
            return false;
        }

        datos = {
            _token: '{{ csrf_token() }}',
            data_pedidos
        };

        modal_quest('modal_exportar_etiquetas', "Desea imprimir las etiquetas seleccionadas?",
            "<i class='fa fa-cubes'></i> Seleccione una opción", true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                post_jquery('/etiqueta/imprimir_etiqueta', datos, function() {
                    listado_etiquetas();
                    cerrar_modals();
                }, 'div_content_etiquetas');
            });

    }

    function filtrar_listado_etiquetas_js(select, clase) {

        let value = $(select).find("option:selected").text().replace('&', '').trim().toLowerCase()

        if (value == 'todos') {
            $("tbody#tbody_etiquetas_facturas tr").css('display', 'table-row')
        } else {
            $("tbody#tbody_etiquetas_facturas tr").filter(function() {

                if (typeof $(this).find('td.' + clase).html() != 'undefined') {

                    $(this).toggle(
                        $(this).find('td.' + clase).html().replace('&', '').replace('amp;', '').trim()
                        .toLowerCase() == value &&
                        $(this).text().replace('&', '').replace('amp;', '').toLowerCase().indexOf(value) > -
                        1
                    )

                }

            })
        }

    }
</script>
