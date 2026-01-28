@if(count($modulos) > 0 && $tipo == 1)
    <div style="overflow-y: scroll; overflow-x: scroll; max-height: 450px" class="text-center">
        <table style="width: 100%; border: 1px solid #9d9d9d" class="table-bordered table-striped" id="table_ciclos_campo">
            <thead>
            <tr>
                <th class="text-center th_yura_green" rowspan="2">
                    <input type="checkbox" id="check_all_ciclos" class="mouse-hand"
                           onchange="$('.check_ciclos').prop('checked', $(this).prop('checked'))">
                </th>
                <th class="text-center th_yura_green" rowspan="2">
                    <div style="width: 120px" class="text-center">
                        Sector
                    </div>
                </th>
                <th class="text-center th_yura_green" rowspan="2">
                    Módulo
                </th>
                <th class="text-center th_yura_green" rowspan="2">
                    <div style="width: 100px" class="text-center">
                        Inicio
                    </div>
                </th>
                <th class="text-center th_yura_green" rowspan="2">
                    P/S
                </th>
                <th class="text-center th_yura_green" rowspan="2">
                    Días
                </th>
                <th class="text-center th_yura_green" rowspan="2">
                    <div style="width: 60px" class="text-center">
                        Área m<sup>2</sup>
                    </div>
                </th>
                <th class="text-center bg-yura_dark">
                    <div style="width: 100px" class="text-center">
                        Ancho Cama
                    </div>
                </th>
                <th class="text-center bg-yura_dark">
                    <div style="width: 100px" class="text-center">
                        Ancho Camino
                    </div>
                </th>
                <th class="text-center bg-yura_dark" rowspan="2">
                    Metros Lineales
                </th>
                <th class="text-center bg-yura_dark" rowspan="2">
                    Camas
                </th>
                @if(es_local())
                    <th class="text-center bg-yura_dark" rowspan="2">
                    </th>
                @endif
            </tr>
            <tr>
                <th class="text-center bg-yura_dark">
                    <input type="number" class="text-center bg-yura_dark" style="width: 100%" id="input_all_ancho_cama"
                           onchange="seleccionar_all_ancho_cama()" onkeyup="seleccionar_all_ancho_cama()" placeholder="cm">
                </th>
                <th class="text-center bg-yura_dark">
                    <input type="number" class="text-center bg-yura_dark" style="width: 100%" id="input_all_ancho_camino"
                           onchange="seleccionar_all_ancho_camino()" onkeyup="seleccionar_all_ancho_camino()" placeholder="cm">
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach($modulos as $modulo)
                @php
                    $ciclo = $modulo->cicloActual();
                @endphp
                <tr id="tr_ciclo_{{$ciclo->id_ciclo}}">
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="checkbox" class="check_ciclos mouse-hand" id="check_ciclo_{{$ciclo->id_ciclo}}">
                        <input type="hidden" class="ids_ciclos" value="{{$ciclo->id_ciclo}}">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$modulo->sector->nombre}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$modulo->nombre}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{convertDateToText($ciclo->fecha_inicio)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$modulo->getPodaSiembraActual()}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{difFechas(hoy(), $ciclo->fecha_inicio)->days}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" readonly class="text-center" id="area_ciclo_campo_{{$ciclo->id_ciclo}}" value="{{$ciclo->area}}"
                               style="width: 100%">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="text-center" id="ancho_cama_{{$ciclo->id_ciclo}}"
                               value="{{$ciclo->ancho_cama}}" placeholder="cm">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="text-center" id="ancho_camino_{{$ciclo->id_ciclo}}"
                               value="{{$ciclo->ancho_camino}}" placeholder="cm">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" id="td_ciclo_campo_m_lineales_{{$ciclo->id_ciclo}}">
                        {{$ciclo->getMetrosLineales()}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" id="td_ciclo_campo_camas_{{$ciclo->id_ciclo}}">
                        {{$ciclo->getCamas()}}
                    </td>
                    @if(es_local())
                        <td class="text-center" style="border-color: #9d9d9d">
                            <button type="button" class="btn btn-xs btn-yura_primary" onclick="update_ciclo_campo('{{$ciclo->id_ciclo}}')">
                                <i class="fa fa-fw fa-pencil"></i>
                            </button>
                        </td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>
        @if(es_local())
            <button type="button" class="btn btn-yura_primary" onclick="update_all_ciclo_campo()">
                <i class="fa fa-fw fa-save"></i> Grabar
            </button>
        @endif
    </div>

    <script>
        estructura_tabla('table_ciclos_campo', false, false);
        $('#table_ciclos_campo_filter label').addClass('text-color_yura');
        $('#table_ciclos_campo_filter label input').addClass('input-yura_white');
    </script>
@else
    <div class="alert alert-info text-center">
        No hay resultados que mostrar
    </div>
@endif

<script>
    function seleccionar_all_ancho_cama() {
        valor = $('#input_all_ancho_cama').val();
        ids_ciclo = $('.ids_ciclos');
        for (i = 0; i < ids_ciclo.length; i++) {
            ciclo = ids_ciclo[i].value;
            if ($('#check_ciclo_' + ciclo).prop('checked') == true) {
                $('#ancho_cama_' + ciclo).val(valor);
            }
        }
    }

    function seleccionar_all_ancho_camino() {
        valor = $('#input_all_ancho_camino').val();
        ids_ciclo = $('.ids_ciclos');
        for (i = 0; i < ids_ciclo.length; i++) {
            ciclo = ids_ciclo[i].value;
            if ($('#check_ciclo_' + ciclo).prop('checked') == true) {
                $('#ancho_camino_' + ciclo).val(valor);
            }
        }
    }

    function update_ciclo_campo(id) {
        datos = {
            _token: '{{csrf_token()}}',
            id: id,
            ancho_cama: $('#ancho_cama_' + id).val(),
            ancho_camino: $('#ancho_camino_' + id).val(),
        };
        post_jquery_m('{{url('sectores_modulos/update_ciclo_campo')}}', datos, function () {
            suma = parseFloat(datos['ancho_cama']) + parseFloat(datos['ancho_camino']);
            $('#td_ciclo_campo_m_lineales_' + id).html(suma > 0 ? Math.round(($('#area_ciclo_campo_' + id).val() / suma) * 100) / 100 : 0);
            $('#td_ciclo_campo_camas_' + id).html(Math.round((suma * 30) * 100) / 100);
        }, 'tr_ciclo_' + id);
    }

    function update_all_ciclo_campo() {
        ids_ciclo = $('.ids_ciclos');
        data = [];
        for (i = 0; i < ids_ciclo.length; i++) {
            id = ids_ciclo[i].value;
            data.push({
                id: id,
                ancho_cama: $('#ancho_cama_' + id).val(),
                ancho_camino: $('#ancho_camino_' + id).val(),
            });
            suma = parseFloat($('#ancho_cama_' + id).val()) + parseFloat($('#ancho_camino_' + id).val());
            $('#td_ciclo_campo_m_lineales_' + id).html(suma > 0 ? Math.round(($('#area_ciclo_campo_' + id).val() / suma) * 100) / 100 : 0);
            $('#td_ciclo_campo_camas_' + id).html(Math.round((suma * 30) * 100) / 100);
        }

        datos = {
            _token: '{{csrf_token()}}',
            data: data,
        };
        post_jquery_m('{{url('sectores_modulos/update_all_ciclo_campo')}}', datos, function () {
        });
    }
</script>
