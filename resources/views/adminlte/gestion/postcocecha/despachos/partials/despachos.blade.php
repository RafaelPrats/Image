<div id="table_despachos">
    @if (sizeof($listado) > 0)
        <table width="100%" class="table-responsive table-bordered" style="border-color: #9d9d9d"
            id="table_content_despachos">
            <thead>
                <tr>
                    <th class="text-center th_yura_green">
                        N# Despacho
                    </th>
                    <th class="text-center th_yura_green">
                        Fecha despacho
                    </th>
                    <th class="text-center th_yura_green">
                        Responsable
                    </th>
                    <th class="text-center th_yura_green">
                        OPCIONES
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($listado as $item)
                    <tr onmouseover="$(this).css('background-color','#add8e6')"
                        onmouseleave="$(this).css('background-color','')">
                        <td style="border-color: #9d9d9d" class="text-center">{{ $item->n_despacho }}</td>
                        <td style="border-color: #9d9d9d" class="text-center">{{ $item->fecha_despacho }}</td>
                        <td style="border-color: #9d9d9d" class="text-center">{{ $item->resp_transporte }}</td>
                        <td style="border-color: #9d9d9d" class="text-center">
                            <a target="_blank" class="btn btn-default btn-xs"
                                href="{{ url('despachos/descargar_despacho/' . $item->id_despacho . '') }}">
                                <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                            </a>
                            @if (es_server())
                                <button type="button" class="btn btn-danger btn-xs" title="Cancelar despacho"
                                    onclick="update_estado_despacho('{{ $item->id_despacho }}','{{ $item->estado }}')">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <script>
            estructura_tabla('table_content_despachos');
            $('#table_content_despachos_filter>label>input').addClass('input-yura_default')
        </script>
    @else
        <div class="alert alert-info text-center">No se han encontrado coincidencias</div>
    @endif
</div>
