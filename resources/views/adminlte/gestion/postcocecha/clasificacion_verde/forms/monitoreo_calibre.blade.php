<legend class="text-center" style="font-size: 1.2em">Ingreso de calibres por monitoreo de módulos</legend>
<div class="input-group" style="width: 100%; margin-bottom: 10px">
    <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
        Fecha
    </div>
    <input type="date" id="fecha_monitoreo_calibre" class="form-control text-center" value="{{$fecha}}">
    <div class="input-group-btn">
        <button type="button" class="btn btn-yura_dark" title="Reiniciar formulario"
                onclick="cerrar_modals();monitoreo_calibres($('#fecha_monitoreo_calibre').val());">
            <i class="fa fa-fw fa-search"></i>
        </button>
    </div>
</div>
@if(count($ciclos) > 0)
    <div style="overflow-y: scroll; max-height: 430px">
        <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0"
               id="table_ingreso_monitoreo_calibre">
            <tr id="tr_calibre_fija_top_0">
                <th class="text-center th_yura_green columna_fija_left_0">
                    <div style="width: 80px; z-index: 9;">
                        Módulo
                    </div>
                </th>
                <th class="text-center th_yura_green columna_fija_left_1">
                    Calibre
                </th>
                @foreach($clasificacion_unitaria as $u)
                    <th class="text-center" style="background-color: {{explode('|', $u->color)[0]}}; {{--color: {{explode('|', $u->color)[1]--}}}};
                            border: 2px solid black">
                        {{explode('|',$u->nombre)[0]}}{{$u->um_siglas}}
                    </th>
                @endforeach
            </tr>
            @foreach($ciclos as $pos_c => $c)
                <input type="hidden" class="ids_ciclo" value="{{$c->id_ciclo}}">
                <tr>
                    <th class="text-center columna_fija_left_0" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{$c->modulo_nombre}}
                    </th>
                    <th class="text-center columna_fija_left_1" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{$c->getCalibreMonitoreoByFecha($fecha)}}
                    </th>
                    @foreach($clasificacion_unitaria as $u)
                        @php
                            $monitoreo = $c->getCalibreByFechaUnitaria($fecha, $u->id_clasificacion_unitaria);
                        @endphp
                        <input type="hidden" class="ids_unitarias_{{$c->id_ciclo}}" value="{{$u->id_clasificacion_unitaria}}">
                        <input type="hidden" id="factor_unitaria_{{$u->id_clasificacion_unitaria}}_{{$c->id_ciclo}}"
                               value="{{explode('|',$u->nombre)[1]}}">
                        <input type="hidden" id="unitaria_tipo_{{$u->id_clasificacion_unitaria}}_{{$c->id_ciclo}}"
                               value="{{$u->tipo}}">
                        <td class="text-center" style="border: 3px solid black">
                            <div class="input-group" data-toggle="tooltip" data-placement="top"
                                 title="{{($monitoreo != '' && $monitoreo->calibre > 0) ? 'Factor: '.$monitoreo->calibre : ''}}">
                                <div class="input-group-addon bg-yura_dark hidden">
                                    R
                                </div>
                                <input type="hidden" id="ramos_{{$u->id_clasificacion_unitaria}}_{{$c->id_ciclo}}"
                                       class="text-center" placeholder="0"
                                       style="width: 70px; font-weight: {{($monitoreo != '' && $monitoreo->ramos > 0) ? 'bold' : ''}};
                                               background-color: {{($monitoreo != '' && $monitoreo->ramos > 0) ? '#00ffdd40' : ''}}"
                                       value="{{$monitoreo != '' ? $monitoreo->ramos : 1}}" readonly>
                                <div class="input-group-addon bg-yura_dark">
                                    T
                                </div>
                                <input type="number" id="tallos_x_ramo_{{$u->id_clasificacion_unitaria}}_{{$c->id_ciclo}}"
                                       class="text-center" placeholder="0"
                                       style="width: 100%; font-weight: {{($monitoreo != '' && $monitoreo->tallos_x_ramo > 0) ? 'bold' : ''}};
                                               background-color: {{($monitoreo != '' && $monitoreo->tallos_x_ramo > 0) ? '#00ffdd40' : ''}}"
                                       value="{{$monitoreo != '' ? $monitoreo->tallos_x_ramo : ''}}">
                            </div>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </table>
    </div>
    <div class="text-center" style="margin-top: 5px">
        <button type="button" class="btn btn-yura_primary" onclick="store_monitoreo_calibre()">
            <i class="fa fa-fw fa-save"></i> Guardar
        </button>
    </div>
@else
    <div class="alert alert-info text-center">No se han encontrado resultados que mostrar</div>
@endif

<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });

    function store_monitoreo_calibre() {
        data = [];
        ids_ciclo = $('.ids_ciclo');
        for (i = 0; i < ids_ciclo.length; i++) {
            ciclo = ids_ciclo[i].value;
            ids_unitarias = $('.ids_unitarias_' + ciclo);
            valores = [];
            for (z = 0; z < ids_unitarias.length; z++) {
                unitaria = ids_unitarias[z].value;
                factor_unitaria = $('#factor_unitaria_' + unitaria + '_' + ciclo).val();
                unitaria_tipo = $('#unitaria_tipo_' + unitaria + '_' + ciclo).val();
                ramos = $('#ramos_' + unitaria + '_' + ciclo).val();
                tallos_x_ramo = $('#tallos_x_ramo_' + unitaria + '_' + ciclo).val();
                valores.push({
                    unitaria: unitaria,
                    factor_unitaria: factor_unitaria,
                    unitaria_tipo: unitaria_tipo,
                    ramos: tallos_x_ramo != '' ? 1 : 0,
                    tallos_x_ramo: tallos_x_ramo != '' ? tallos_x_ramo : 0,
                });
            }
            data.push({
                ciclo: ciclo,
                valores: valores,
            });
        }
        datos = {
            _token: '{{csrf_token()}}',
            fecha: $('#fecha_monitoreo_calibre').val(),
            data: data,
        };
        post_jquery_m('{{url('clasificacion_verde/store_monitoreo_calibre')}}', datos, function () {
            cerrar_modals();
            monitoreo_calibres($('#fecha_monitoreo_calibre').val());
        });
    }
</script>

<style>
    #table_ingreso_monitoreo_calibre tr#tr_calibre_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 9 !important;
    }

    .columna_fija_left_0 {
        position: sticky;
        left: 0;
        z-index: 8 !important;
    }
</style>
