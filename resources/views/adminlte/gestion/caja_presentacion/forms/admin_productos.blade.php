<legend class="text-center" style="font-size: 1em; margin-bottom: 5px">
    Productos de: "<strong>{{ $empaque->nombre }}</strong>"
</legend>
<input type="hidden" id="id_empaque_seleccionado" value="{{ $empaque->id_empaque }}">
<table style="width: 100%;">
    <tr>
        <td id="listado_productos" style="vertical-align: top; width: 45%">
            <table style="width: 100%">
                <tr>
                    <td class="text-center padding_lateral_5" style="border-color: #9d9d9d" id="td_cargar_longitudes">
                        <div class="input-group">
                            <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                                Búsqueda
                            </span>
                            <input type="text" id="filtro_busqueda" style="width: 100%"
                                class="text-center form-control" onkeyup="buscar_productos()">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-yura_dark" onclick="buscar_productos()">
                                    <i class="fa fa-fw fa-search"></i>
                                </button>
                            </span>
                        </div>
                    </td>
                </tr>
            </table>

            <div id="div_listado_productos" style="margin-top: 5px">
            </div>
        </td>

        <td>
            <button type="button" class="btn btn-block btn-yura_dark" onclick="agregar_productos()">
                <i class="fa fa-fw fa-arrow-right"></i> Agregar
            </button>
            <button type="button" class="btn btn-block btn-yura_primary" onclick="store_agregar_productos()">
                <i class="fa fa-fw fa-save"></i> Grabar
            </button>
        </td>

        <td id="listado_seleccionados" style="vertical-align: top; width: 45%">
            <table style="width: 100%">
                <tr>
                    <td class="text-center padding_lateral_5" style="border-color: #9d9d9d" id="td_cargar_longitudes">
                        <div class="input-group">
                            <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                                Planta
                            </span>
                            <select id="filtro_planta" style="width: 100%" class="form-control"
                                onchange="buscar_detalles()">
                                @foreach ($plantas as $p)
                                    <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                                @endforeach
                            </select>
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-yura_dark" onclick="buscar_detalles()">
                                    <i class="fa fa-fw fa-search"></i>
                                </button>
                            </span>
                        </div>
                    </td>
                </tr>
            </table>
            <div id="div_detalles"></div>
        </td>
    </tr>
</table>

<script>
    buscar_productos();
    buscar_detalles();

    function buscar_productos() {
        datos = {
            busqueda: $('#filtro_busqueda').val(),
        };
        get_jquery('{{ url('caja_presentacion/buscar_productos') }}', datos, function(retorno) {
            $('#div_listado_productos').html(retorno);
        }, 'div_listado_productos');
    }

    function buscar_detalles() {
        datos = {
            planta: $('#filtro_planta').val(),
            empaque: $('#id_empaque_seleccionado').val(),
        };
        get_jquery('{{ url('caja_presentacion/buscar_detalles') }}', datos, function(retorno) {
            $('#div_detalles').html(retorno);
        }, 'div_detalles');
    }

    function agregar_productos() {
        productos_listados = $('.productos_listados');
        for (i = 0; i < productos_listados.length; i++) {
            id = productos_listados[i].value;
            if ($('#cantidad_' + id).val() > 0) {
                cant_producto_seleccionado++;
                nombre = $('#nombre_producto_' + id).val();
                cantidad = $('#cantidad_' + id).val();
                $('#table_productos_seleccionados').append('<tr id="tr_producto_seleccionado_' +
                    cant_producto_seleccionado + '">' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    nombre +
                    '<input type="hidden" class="cant_producto_seleccionado" value="' + cant_producto_seleccionado +
                    '">' +
                    '<input type="hidden" id="id_producto_seleccionado_' + cant_producto_seleccionado +
                    '" value="' + id + '">' +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    '<input type="number" class="text-center" style="width: 100%" id="cantidad_producto_seleccionado_' +
                    cant_producto_seleccionado + '" value="' + cantidad + '">' +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    '<button type="button" class="btn btn-xs btn-yura_danger" title="Quitar" onclick="quitar_producto_seleccionado(' +
                    cant_producto_seleccionado + ')">' +
                    '<i class="fa fa-fw fa-trash"></i>' +
                    '</button>' +
                    '</td>' +
                    '</tr>');
            }
        }
    }

    function quitar_producto_seleccionado(cant_producto_seleccionado) {
        $('#tr_producto_seleccionado_' + cant_producto_seleccionado).remove();
    }

    function store_agregar_productos() {
        cant_producto_seleccionado = $('.cant_producto_seleccionado');
        data = [];
        for (i = 0; i < cant_producto_seleccionado.length; i++) {
            pos = cant_producto_seleccionado[i].value;
            unidades = $('#cantidad_producto_seleccionado_' + pos).val();
            id_prod = $('#id_producto_seleccionado_' + pos).val();
            id_emp = $('#id_empaque_seleccionado').val();
            if (unidades > 0)
                data.push({
                    id_prod: id_prod,
                    unidades: unidades,
                })
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                id_emp: id_emp,
                planta: $('#filtro_planta').val(),
                data: JSON.stringify(data)
            };
            post_jquery_m('{{ url('caja_presentacion/store_agregar_productos') }}', datos, function(retorno) {
                /*cerrar_modals();
                admin_productos(id_emp);*/
            });
        }
    }
</script>
