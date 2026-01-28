<legend class="text-center" style="font-size: 1.3em; margin-bottom: 5px">
    <b>DESPACHO DE FINCA</b>
</legend>
<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="text-center bg-yura_dark" colspan="13">
            DESPACHO COMPLETO
        </th>
    </tr>
    <tr>
        <th class="text-center th_yura_green" style="width: 60px">
            No.
        </th>
        <th class="text-center th_yura_green">
            Packing
        </th>
        <th class="text-center th_yura_green">
            Agencia
        </th>
        <th class="text-center th_yura_green">
            Cliente
        </th>
        <th class="text-center th_yura_green">
            Consignatario
        </th>
        <th class="text-center th_yura_green">
            Marcaciones
        </th>
        <th class="text-center th_yura_green">
            Piezas
        </th>
        <th class="text-center th_yura_green">
            FB
        </th>
        <th class="text-center th_yura_green">
            HB
        </th>
        <th class="text-center th_yura_green">
            QB
        </th>
        <th class="text-center th_yura_green">
            EB
        </th>
        <th class="text-center th_yura_green">
            SB
        </th>
        <th class="text-center th_yura_green" style="width: 30px">
        </th>
    </tr>
    @php
        $total_cajas = 0;
        $cajas_fulls = 0;
        $total_fb = 0;
        $total_hb = 0;
        $total_qb = 0;
        $total_eb = 0;
        $total_sb = 0;
        $resumen_agencias = [];
    @endphp
    @foreach ($hoja_ruta->detalles->sortBy('orden') as $pos_i => $detalle)
        @php
            $item = $detalle->proyecto;
        @endphp
        @if ($item != '')
            @php
                $cliente = $item->cliente->detalle();
                $consignatario = $item->consignatario;
                $agencia_carga = $item->agencia_carga;
                $cajas = 0;
                $fb = 0;
                $hb = 0;
                $qb = 0;
                $eb = 0;
                $sb = 0;
                foreach ($item->cajas as $caja) {
                    $empaque = $caja->empaque;
                    $cajas += $caja->cantidad;
                    $total_cajas += $caja->cantidad;
                    if (explode('|', $empaque->nombre)[1] == 1) {
                        $fb += $caja->cantidad;
                        $total_fb += $caja->cantidad;
                        $cajas_fulls += $caja->cantidad * 1;
                    }
                    if (explode('|', $empaque->nombre)[1] == 0.5) {
                        $hb += $caja->cantidad;
                        $total_hb += $caja->cantidad;
                        $cajas_fulls += $caja->cantidad * 0.5;
                    }
                    if (explode('|', $empaque->nombre)[1] == 0.25) {
                        $qb += $caja->cantidad;
                        $total_qb += $caja->cantidad;
                        $cajas_fulls += $caja->cantidad * 0.25;
                    }
                    if (explode('|', $empaque->nombre)[1] == 0.125) {
                        $eb += $caja->cantidad;
                        $total_eb += $caja->cantidad;
                        $cajas_fulls += $caja->cantidad * 0.125;
                    }
                    if (explode('|', $empaque->nombre)[1] == 0.0625) {
                        $sb += $caja->cantidad;
                        $total_sb += $caja->cantidad;
                        $cajas_fulls += $caja->cantidad * 0.0625;
                    }
                }
                if (!in_array($agencia_carga, $resumen_agencias)) {
                    $resumen_agencias[] = $agencia_carga;
                }
            @endphp
            <tr onmouseover="$(this).css('background-color', 'cyan')" onmouseleave="$(this).css('background-color', '')"
                id="tr_detalle_{{ $detalle->id_detalle_hoja_ruta }}">
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" value="{{ $detalle->orden }}" style="width: 100%" class="text-center ordenes"
                        data-id_proyecto="{{ $item->id_proyecto }}" id="orden_{{ $item->id_proyecto }}" required>
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $item->packing }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $agencia_carga->nombre }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $cliente->nombre }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $consignatario != '' ? $consignatario->nombre : '' }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    @foreach ($item->getMarcaciones() as $val)
                        -{{ $val }} <br>
                    @endforeach
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                    {{ $cajas }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                    {{ $fb }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                    {{ $hb }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                    {{ $qb }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                    {{ $eb }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                    {{ $sb }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                    <button type="button" class="btn btn-xs btn-yura_danger"
                        onclick="quitar_detalle('{{ $detalle->id_detalle_hoja_ruta }}')">
                        <i class="fa fa-fw fa-times"></i>
                    </button>
                </th>
            </tr>
        @endif
    @endforeach
    <tr>
        <th class="padding_lateral_5 text-right th_yura_green" colspan="6">
            TOTALES
        </th>
        <th class="text-center bg-yura_dark">
            {{ $total_cajas }}
        </th>
        <th class="text-center bg-yura_dark">
            {{ $total_fb }}
        </th>
        <th class="text-center bg-yura_dark">
            {{ $total_hb }}
        </th>
        <th class="text-center bg-yura_dark">
            {{ $total_qb }}
        </th>
        <th class="text-center bg-yura_dark">
            {{ $total_eb }}
        </th>
        <th class="text-center bg-yura_dark">
            {{ $total_sb }}
        </th>
        <th class="text-center bg-yura_dark">
        </th>
    </tr>
</table>

<div class="text-center" style="margin-top: 5px">
    <b>DATOS GENERALES</b>
</div>
<table class="table-bordered" style="width: 100%; border: 2px solid #9d9d9d">
    <tr>
        <th class="text-center" style="border: 2px solid black; background-color: #eeeeee">
            Cajas Totales
        </th>
        <th class="text-center" style="border: 2px solid black; background-color: #eeeeee">
            FULL BOXES: [{{ $cajas_fulls }}]
        </th>
        <th class="text-center" style="border: 2px solid black; background-color: #eeeeee">
            HALF BOXES: [{{ $total_hb }}]
        </th>
        <th class="text-center" style="border: 2px solid black; background-color: #eeeeee">
            1/4 BOXES: [{{ $total_qb }}]
        </th>
        <th class="text-center" style="border: 2px solid black; background-color: #eeeeee">
            1/8 BOXES: [{{ $total_eb }}]
        </th>
        <th class="text-center" style="border: 2px solid black; background-color: #eeeeee">
            1/16 BOXES: [{{ $total_sb }}]
        </th>
    </tr>
</table>

<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="padding_lateral_5 bg-yura_dark">
            Transportista
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="transportista" style="width: 100%; height: 26px;" onchange="seleccionar_transportista()">
                @foreach ($transportistas as $t)
                    <option value="{{ $t->id_transportista }}"
                        {{ $hoja_ruta->id_transportista == $t->id_transportista ? 'selected' : '' }}>
                        {{ $t->nombre_empresa }}
                    </option>
                @endforeach
            </select>
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Camion
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="camion" style="width: 100%; height: 26px;" onchange="seleccionar_camion()">
                <option value="{{ $hoja_ruta->id_camion }}">
                    {{ $hoja_ruta->camion->modelo . ' (' . $hoja_ruta->camion->placa . ')' }}
                </option>
            </select>
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Placa
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="text" id="placa" style="width: 100%" value="{{ $hoja_ruta->placa }}">
        </th>
    </tr>
    <tr>
        <th class="padding_lateral_5 bg-yura_dark">
            Conductor
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="conductor" style="width: 100%; height: 26px;">
                <option value="{{ $hoja_ruta->id_conductor }}">
                    {{ $hoja_ruta->conductor->nombre }}
                </option>
            </select>
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Fecha
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="date" id="fecha" style="width: 100%" value="{{ $hoja_ruta->fecha }}"
                onchange="seleccionar_fecha()">
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Semana
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="text" id="semana" style="width: 100%"
                value="{{ getSemanaByDate($hoja_ruta->fecha)->codigo }}" readonly>
        </th>
    </tr>
    <tr>
        <th class="padding_lateral_5 bg-yura_dark">
            RESPONSABLE
        </th>
        <th class="text-center" style="border-color: #9d9d9d" colspan="5">
            <input type="text" id="responsable" style="width: 100%" value="{{ $hoja_ruta->responsable }}">
        </th>
    </tr>
</table>

<div class="text-center" style="margin-top: 5px">
    <b>SELLOS</b>
</div>
<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
    @foreach ($hoja_ruta->sellos as $sello)
        @php
            $agencia = $sello->agencia_carga;
        @endphp
        <tr id="tr_sello_{{ $sello->id_sello_hoja_ruta }}">
            <th class="padding_lateral_5 bg-yura_dark" style="width: 290px">
                {{ $sello->agencia_carga->nombre }}
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="text" id="sello_{{ $sello->id_agencia_carga }}" value="{{ $sello->sello }}"
                    class="padding_lateral_5 sello_agencia" data-id_agencia="{{ $sello->id_agencia_carga }}"
                    style="width: 100%; background-color: {{ !in_array($agencia, $resumen_agencias) ? '#ffa0a0' : '#eeeeee' }}">
            </th>
            <th class="text-center" style="border-color: #9d9d9d; width: 30px">
                <button type="button" class="btn btn-xs btn-yura_danger"
                    onclick="quitar_sello('{{ $sello->id_sello_hoja_ruta }}')">
                    <i class="fa fa-fw fa-times"></i>
                </button>
            </th>
        </tr>
    @endforeach
</table>

<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary" onclick="update_despacho()">
        <i class="fa fa-fw fa-save"></i> GRABAR DESPACHO
    </button>
</div>

<input type="hidden" id="hoja_ruta_selected" value="{{ $hoja_ruta->id_hoja_ruta }}">

<script>
    function seleccionar_transportista() {
        datos = {
            _token: '{{ csrf_token() }}',
            id: $('#transportista').val()
        }
        $.post('{{ url('hoja_ruta/seleccionar_transportista') }}', datos, function(retorno) {
            $('#camion').html(retorno.options_camiones);
            $('#conductor').html(retorno.options_conductores);
            seleccionar_camion();
        }, 'json');
    }

    function seleccionar_camion() {
        placa = $('#camion').find(':selected').data('placa');
        $('#placa').val(placa);
    }

    function quitar_detalle(id_detalle_hoja_ruta) {
        $('#tr_detalle_' + id_detalle_hoja_ruta).remove();
    }

    function quitar_sello(id_sello_hoja_ruta) {
        $('#tr_sello_' + id_sello_hoja_ruta).remove();
    }

    function update_despacho() {
        mensaje = {
            title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmacion',
            mensaje: '<div class="alert alert-info text-center" style="font-size: 16px">¿Está seguro de <b>MODIFICAR</b> el despacho?</div>',
        };
        modal_quest('modal_update_despacho', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                detalles = [];
                ordenes = $('.ordenes');
                for (i = 0; i < ordenes.length; i++) {
                    id = ordenes[i].id;
                    id_proyecto = $('#' + id).data('id_proyecto');
                    orden = $('#' + id).val();
                    if (orden != '')
                        detalles.push({
                            id_proyecto: id_proyecto,
                            orden: orden
                        });
                }
                sellos = [];
                sello_agencia = $('.sello_agencia');
                for (i = 0; i < sello_agencia.length; i++) {
                    id = sello_agencia[i].id;
                    id_agencia = $('#' + id).data('id_agencia');
                    sello = $('#' + id).val();
                    sellos.push({
                        id_agencia: id_agencia,
                        sello: sello
                    });
                }
                datos = {
                    _token: '{{ csrf_token() }}',
                    id_hoja_ruta: $('#hoja_ruta_selected').val(),
                    fecha: $('#fecha').val(),
                    transportista: $('#transportista').val(),
                    camion: $('#camion').val(),
                    placa: $('#placa').val(),
                    conductor: $('#conductor').val(),
                    responsable: $('#responsable').val(),
                    detalles: JSON.stringify(detalles),
                    sellos: JSON.stringify(sellos),
                };
                if (detalles.length > 0 && datos['transportista'] != null && datos['camion'] != null &&
                    datos['conductor'] != null && datos['responsable'] != '') {
                    $.LoadingOverlay('show');
                    $.post('{{ url('hoja_ruta/update_despacho') }}', datos, function(retorno) {
                        if (retorno.success) {
                            mini_alerta('success', retorno.mensaje, 5000);

                            cerrar_modals();
                            listar_reporte();
                            if (retorno.id_despacho != '')
                                exportar_despacho(retorno.id_despacho);
                        } else {
                            alerta(retorno.mensaje);
                        }
                    }, 'json').fail(function(retorno) {
                        console.log(retorno);
                        alerta_errores(retorno.responseText);
                    }).always(function() {
                        $.LoadingOverlay('hide');
                    });
                } else {
                    alerta(
                        '<div class="alert alert-danger text-center" style="font-size: 16px">Faltan datos por completar para crear el despacho</div>'
                    );
                }
            });
    }
</script>
