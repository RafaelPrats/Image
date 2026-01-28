@if (count($pesos) > 0 && count($presentaciones) > 0 && count($tallos) > 0 && count($longitudes) > 0)
    <table style="width: 100%; margin-top: 5px">
        <tr>
            <td>
                <div class="input-group">
                    <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                        <i class="fa fa-fw fa-check"></i>
                    </span>
                    <select id="filtro_marcacion" class="form-control w-100 input-yura_default">
                        @foreach ($marcaciones as $m)
                            <option value="{{ $m->id_dato_exportacion }}">
                                {{ $m->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </td>
            <td>
                <div class="input-group">
                    <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                        Peso
                    </span>
                    <select id="filtro_peso" class="form-control w-100 input-yura_default">
                        <option value="">Todos</option>
                        @foreach ($pesos as $p)
                            <option value="{{ $p->id_clasificacion_ramo }}">{{ $p->nombre . $p->siglas }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </td>
            <td>
                <div class="input-group">
                    <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                        Pres.
                    </span>
                    <select id="filtro_presentacion" class="form-control w-100 input-yura_default">
                        <option value="">Todos</option>
                        @foreach ($presentaciones as $p)
                            <option value="{{ $p->id_empaque_p }}">{{ $p->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </td>
            <td>
                <div class="input-group">
                    <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                        Tallos
                    </span>
                    <select id="filtro_tallos" class="form-control w-100 input-yura_default">
                        <option value="">Todos</option>
                        @foreach ($tallos as $t)
                            <option value="{{ $t->tallos_x_ramos }}">{{ $t->tallos_x_ramos }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </td>
            <td>
                <div class="input-group">
                    <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                        Long.
                    </span>
                    <select id="filtro_longitud" class="form-control w-100 input-yura_default">
                        <option value="">Todos</option>
                        @foreach ($longitudes as $l)
                            <option value="{{ $l->longitud_ramo . '|' . $l->id_unidad_medida }}">
                                {{ $l->longitud_ramo . $l->siglas }}
                            </option>
                        @endforeach
                    </select>
                    <div class="input-group-btn">
                        <button class="btn btn-yura_dark" type="button" title="Listar"
                            onclick="listar_combinaciones()">
                            <i class="fa fa-fw fa-search"></i>
                        </button>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div id="div_listado_combinaciones" style="overflow-y: scroll; height: 450px; margin-top: 5px">
    </div>

    <script>
        function listar_combinaciones() {
            datos = {
                planta: $('#filtro_planta').val(),
                marcacion: $('#filtro_marcacion').val(),
                peso: $('#filtro_peso').val(),
                presentacion: $('#filtro_presentacion').val(),
                tallos: $('#filtro_tallos').val(),
                longitud: $('#filtro_longitud').val(),
            };
            get_jquery('{{ url('reporte_por_marcaciones/listar_combinaciones') }}', datos, function(retorno) {
                $('#div_listado_combinaciones').html(retorno);
                estructura_tabla('table_reporte_por_marcaciones');
                $('#table_reporte_por_marcaciones_filter>label>input').addClass('input-yura_default');
            });
        }

        function exportar_reporte(fecha) {
            $.LoadingOverlay('show');
            window.open('{{ url('clasificacion_blanco/exportar_reporte') }}?fecha=' + fecha +
                '&peso=' + $('#filtro_peso').val() +
                '&presentacion=' + $('#filtro_presentacion').val() +
                '&tallos=' + $('#filtro_tallos').val() +
                '&longitud=' + $('#filtro_longitud').val() +
                '&planta=' + $('#filtro_planta').val() +
                '&variedad=' + '', '_blank');
            $.LoadingOverlay('hide');
        }
    </script>
@else
    <div class="alert alert-info text-center">
        No se han encontrado datos que mostrar
    </div>
@endif
