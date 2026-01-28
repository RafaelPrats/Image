<legend class="text-center" style="font-size: 1.3em; margin-bottom: 5px">
    <b>DESPACHO DE FINCA</b>
</legend>
<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="text-center bg-yura_dark" colspan="12">
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
    @foreach ($listado as $pos_i => $item)
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
                if ($empaque->siglas == 'FB') {
                    $fb += $caja->cantidad;
                    $total_fb += $caja->cantidad;
                    $cajas_fulls += 1;
                }
                if ($empaque->siglas == 'HB') {
                    $hb += $caja->cantidad;
                    $total_hb += $caja->cantidad;
                    $cajas_fulls += 0.5;
                }
                if ($empaque->siglas == 'QB') {
                    $qb += $caja->cantidad;
                    $total_qb += $caja->cantidad;
                    $cajas_fulls += 0.25;
                }
                if ($empaque->siglas == 'EB') {
                    $eb += $caja->cantidad;
                    $total_eb += $caja->cantidad;
                    $cajas_fulls += 0.125;
                }
                if ($empaque->siglas == 'SB') {
                    $sb += $caja->cantidad;
                    $total_sb += $caja->cantidad;
                    $cajas_fulls += 0.0625;
                }
            }
            if (!in_array($agencia_carga, $resumen_agencias)) {
                $resumen_agencias[] = $agencia_carga;
            }
        @endphp
        <tr onmouseover="$(this).css('background-color', 'cyan')" onmouseleave="$(this).css('background-color', '')">
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="number" value="{{ $pos_i + 1 }}" style="width: 100%" class="text-center ordenes"
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
        </tr>
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
                    <option value="{{ $t->id_transportista }}">
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
                <option value=""></option>
            </select>
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Placa
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="text" id="placa" style="width: 100%">
        </th>
    </tr>
    <tr>
        <th class="padding_lateral_5 bg-yura_dark">
            Conductor
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="conductor" style="width: 100%; height: 26px;">
                <option value=""></option>
            </select>
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Fecha
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="date" id="fecha" style="width: 100%" value="{{ $fecha }}"
                onchange="seleccionar_fecha()">
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Semana
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="text" id="semana" style="width: 100%" value="{{ getSemanaByDate($fecha)->codigo }}"
                readonly>
        </th>
    </tr>
    <tr>
        <th class="padding_lateral_5 bg-yura_dark">
            RESPONSABLE
        </th>
        <th class="text-center" style="border-color: #9d9d9d" colspan="5">
            <input type="text" id="responsable" style="width: 100%" value="{{ $usuario->nombre_completo }}">
        </th>
    </tr>
</table>

<div class="text-center" style="margin-top: 5px">
    <b>SELLOS</b>
</div>
<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
    @foreach ($resumen_agencias as $a)
        <tr>
            <th class="padding_lateral_5 bg-yura_dark" style="width: 290px">
                {{ $a->nombre }}
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="text" id="sello_{{ $a->id_agencia_carga }}" class="padding_lateral_5 sello_agencia"
                    data-id_agencia="{{ $a->id_agencia_carga }}" style="width: 100%; background-color: #eeeeee">
            </th>
        </tr>
    @endforeach
</table>

<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary" onclick="store_despacho()">
        <i class="fa fa-fw fa-save"></i> GRABAR DESPACHO
    </button>
</div>

<script>
    seleccionar_transportista();

    function seleccionar_transportista() {
        datos = {
            _token: '{{ csrf_token() }}',
            id: $('#transportista').val()
        }
        $.post('{{ url('hoja_ruta/seleccionar_transportista') }}', datos, function(retorno) {
            $('#camion').html(retorno.options_camiones);
            $('#conductor').html(retorno.options_conductores);
            seleccionar_camion()
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        });
    }

    function seleccionar_fecha() {
        datos = {
            _token: '{{ csrf_token() }}',
            fecha: $('#fecha').val()
        }
        $.post('{{ url('hoja_ruta/seleccionar_fecha') }}', datos, function(retorno) {
            $('#semana').val(retorno.codigo);
        }, 'json');
    }

    function seleccionar_camion() {
        placa = $('#camion').find(':selected').data('placa');
        $('#placa').val(placa);
    }

    function store_despacho() {
        mensaje = {
            title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmacion',
            mensaje: '<div class="alert alert-info text-center" style="font-size: 16px">¿Está seguro de <b>CREAR</b> el despacho?</div>',
        };
        modal_quest('modal_store_despacho', mensaje['mensaje'], mensaje['title'], true, false,
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
                    $.post('{{ url('hoja_ruta/store_despacho') }}', datos, function(retorno) {
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
