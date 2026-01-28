<div style="overflow-x: scroll; overflow-y: scroll; width: 100%; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d;" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="text-center th_yura_green" style="width: 30px">
                    <input type="checkbox" id="check_all"
                        onchange="$('.check_proy').prop('checked', $(this).prop('checked'))">
                </th>
                <th class="padding_lateral_5 th_yura_green" style="width: 60px">
                    Packing
                </th>
                <th class="padding_lateral_5 th_yura_green">
                    Observacion
                </th>
                <th class="padding_lateral_5 th_yura_green">
                    Cliente
                </th>
                <th class="padding_lateral_5 th_yura_green">
                    País
                </th>
                <th class="padding_lateral_5 th_yura_green">
                    Agencia de Carga
                </th>
                <th class="padding_lateral_5 th_yura_green">
                    Marcaciones
                </th>
                <th class="padding_lateral_5 th_yura_green" style="width: 60px">
                    Piezas
                </th>
                <th class="padding_lateral_5 th_yura_green" style="width: 60px">
                    Doble
                </th>
                <th class="text-center th_yura_green" style="width: 60px">
                    <i class="fa fa-fw fa-download"></i> Etiqueta
                </th>
                <th class="text-center th_yura_green" style="width: 60px">
                    <i class="fa fa-fw fa-download"></i> Packing
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $item)
                @php
                    $cliente = $item->cliente->detalle();
                    $consignatario = $item->consignatario;
                    if ($consignatario != '') {
                        $codigo_pais = $item->codigo_pais != '' ? $item->codigo_pais : $consignatario->codigo_pais;
                    } else {
                        $codigo_pais = '';
                    }
                    $pais = getPais($codigo_pais);
                    $getTotalesMixtos = $item->getTotalesMixtos();
                    $getMixtosDistribuidos = $item->getMixtosDistribuidos();
                    $texto_observacion = '';
                    if ($item->guia_madre == '') {
                        $texto_observacion .= '<span class="error">*Guia Madre</span>';
                    }
                    if ($item->guia_hija == '') {
                        $texto_observacion .=
                            $texto_observacion != ''
                                ? '<br><span class="error">*Guia Hija</span>'
                                : '<span class="error">*Guia Hija</span>';
                    }
                    if ($item->dae == '') {
                        $texto_observacion .=
                            $texto_observacion != ''
                                ? '<br><span class="error">*DAE</span>'
                                : '<span class="error">*DAE</span>';
                    }
                    if ($getTotalesMixtos->tallos != $getMixtosDistribuidos->tallos) {
                        $texto_observacion .=
                            $texto_observacion != ''
                                ? '<br><span class="error">*Mixtos</span>'
                                : '<span class="error">*Mixtos</span>';
                    }
                @endphp
                <tr onmouseover="$(this).css('background-color', 'cyan')"
                    onmouseleave="$(this).css('background-color', '')">
                    <th class="text-center" style="border-color: #9d9d9d">
                        <input type="checkbox" class="check_proy mouse-hand" id="check_proy_{{ $item->id_proyecto }}"
                            data-id_proy="{{ $item->id_proyecto }}">
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->packing }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d; font-size: 0.9em">
                        {!! $texto_observacion !!}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $cliente->nombre }}
                    </th>
                    <th class="padding_lateral_5 {{ $item->codigo_pais == '' ? 'error' : '' }}"
                        style="border-color: #9d9d9d">
                        {{ $pais != '' ? $pais->nombre : '' }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->agencia_carga->nombre }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        @foreach ($item->getMarcaciones() as $val)
                            -{{ $val }} <br>
                        @endforeach
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->getTotalPiezas() }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <input type="checkbox" id="doble_{{ $item->id_proyecto }}" class="mouse-hand" disabled>
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_dark"
                                onclick="descargar_etiqueta('{{ $item->id_proyecto }}')">
                                <i class="fa fa-fw fa-download"></i>
                            </button>
                        </div>
                        @if ($item->impreso == 1)
                            <div class="progress" style="height: 24px; margin-top: 10px;">
                                <div id="progress-bar_{{ $item->id_proyecto }}"
                                    class="progress-bar progress-bar-striped active progress-bar-full"
                                    role="progressbar" aria-valuemin="0" aria-valuemax="100">
                                    <span class="sr-only" style="position: unset;">Descargado</span>
                                </div>
                            </div>
                        @endif
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_dark"
                                onclick="descargar_packing('{{ $item->id_proyecto }}')">
                                <i class="fa fa-fw fa-file-pdf-o"></i>
                            </button>
                        </div>
                    </th>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="text-center" style="margin-top: 5px">
    <div class="btn-group">
        <button type="button" class="btn btn-yura_primary" onclick="descargar_etiquetas_all()">
            <i class="fa fa-fw fa-download"></i> Etiquetas SELECCIONADOS
        </button>
        <button type="button" class="btn btn-yura_warning" onclick="descargar_packings_all()">
            <i class="fa fa-fw fa-file-pdf-o"></i> Packings SELECCIONADOS
        </button>
    </div>
</div>

<style>
    .progress-bar-full {
        width: 100% !important;
        background-color: #00B388 !important;
    }

    .progress-bar-bench {
        background-color: #b32a00 !important;
    }

    .progress-bar-bench-50 {
        background-color: #b37a00 !important;
    }

    .progress-bar-bench-80 {
        background-color: #00B388 !important;
    }

    .progress-bar {
        padding: auto 8px !important;
    }

    .progress {
        background-color: #5A7177 !important;
        border-radius: 10px !important;
    }
</style>

<script>
    estructura_tabla('table_listado');

    function descargar_packing(id) {
        $.LoadingOverlay('show');
        window.open('{{ url('proyectos/descargar_packing') }}?id=' + id, '_blank');
        $.LoadingOverlay('hide');
    }

    function descargar_packings_all(id) {
        data = [];
        check_proy = $('.check_proy');
        for (i = 0; i < check_proy.length; i++) {
            id = check_proy[i].id;
            if ($('#' + id).prop('checked') == true) {
                data.push($('#' + id).data('id_proy'));
            }
        }
        if (data.length > 0) {
            $.LoadingOverlay('show');
            window.open('{{ url('proyectos/descargar_packings_all') }}?data=' + JSON.stringify(data), '_blank');
            $.LoadingOverlay('hide');
        }
    }

    function descargar_etiqueta(id) {
        $.LoadingOverlay('show');
        doble = $('#doble_' + id).prop('checked') ? 1 : 0;
        window.open('{{ url('etiquetas/descargar_etiqueta') }}?id=' + id + '&doble=' + doble, '_blank');
        $.LoadingOverlay('hide');
        listar_reporte();
    }

    function descargar_etiquetas_all(id) {
        data = [];
        dobles = [];
        check_proy = $('.check_proy');
        for (i = 0; i < check_proy.length; i++) {
            id = check_proy[i].id;
            if ($('#' + id).prop('checked') == true) {
                data.push($('#' + id).data('id_proy'));
                dobles.push($('#doble_' + id).prop('checked') ? 1 : 0);
            }
        }
        if (data.length > 0) {
            $.LoadingOverlay('show');
            window.open('{{ url('etiquetas/descargar_etiquetas_all') }}?data=' + JSON.stringify(data) +
                '&dobles=' + JSON.stringify(dobles), '_blank');
            $.LoadingOverlay('hide');
            listar_reporte();
        }
    }
</script>
