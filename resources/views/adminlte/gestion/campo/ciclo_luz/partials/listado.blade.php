<div style="overflow-y: scroll; overflow-x: scroll; max-height: 450px">
    <table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d">
        <tr id="tr_fija_top_0">
            <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                Módulo
            </th>
            <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                Poda
            </th>
            <th class="text-center th_yura_green">
                <div class="text-center" style="width: 120px">
                    Fecha Poda
                </div>
            </th>
            <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                Días
            </th>
            <th class="text-center th_yura_green">
                <div class="text-center" style="width: 60px">
                    Tipo Luz
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div class="text-center" style="width: 60px">
                    # Lamp.
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div class="text-center" style="width: 60px">
                    Día Ini. Luz
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div class="text-center" style="width: 120px">
                    Ini. Luz
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div class="text-center" style="width: 60px">
                    Sem. Ini.
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div class="text-center" style="width: 60px">
                    Días Luz
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div class="text-center" style="width: 60px">
                    Días Proy.
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div class="text-center" style="width: 60px">
                    Días Adic. Luz
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div class="text-center" style="width: 120px">
                    Fin Luz
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div class="text-center" style="width: 60px">
                    Sem. Fin
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div class="text-center" style="width: 60px">
                    Hrs. Luz
                </div>
            </th>
            <th class="text-center th_yura_green" colspan="2">
                Horario
            </th>
            <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                Costo
            </th>
            <th class="text-center th_yura_green">

            </th>
        </tr>
        @foreach($ciclos as $c)
            @php
                $modulo = $c->modulo;
                $dias_ciclo = difFechas(hoy(), $c->fecha_inicio)->days;
                $luz = $c->getLuzByFecha($fecha);
                $inicio_luz = isset($luz) ? opDiasFecha('+', $luz->inicio_luz, $c->fecha_inicio) : '';
                $fin_luz = isset($luz) ? opDiasFecha('+', $luz->inicio_luz + $luz->dias_proy + $luz->dias_adicional - 1, $c->fecha_inicio) : '';
                $dias_luz = 0;
                if(isset($luz) && $luz->inicio_luz <= $dias_ciclo)
                    if(($luz->dias_proy + $luz->dias_adicional) >= $dias_ciclo - $luz->inicio_luz)
                        $dias_luz = $dias_ciclo - $luz->inicio_luz;
                    else
                        $dias_luz = ($luz->dias_proy + $luz->dias_adicional);
                $horas_dia = isset($luz) ? $luz->getHorasDia() : 0;
                $horas_luz = $dias_luz * $horas_dia;
                //calcular costo de luz
                $costo_luz = 0;
                if(isset($luz)){
                    $costo_x_tipo = $luz->tipo_luz / 1000;
                    $costo_x_lampara = $costo_x_tipo * $luz->lamparas;
                    $costo_x_lampara = $costo_x_lampara * $horas_luz;
                    $costo_luz = $costo_x_lampara * 0.10;
                }
            @endphp
            <tr id="tr_luz_{{$c->id_ciclo}}">
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{$modulo->nombre}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{$modulo->getPodaSiembraByCiclo($c->id_ciclo)}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{convertDateToText($c->fecha_inicio)}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{$dias_ciclo}}
                </th>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" class="text-center" id="tipo_luz_{{$c->id_ciclo}}" value="{{isset($luz) ? $luz->tipo_luz : ''}}"
                           style="width: 100%">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" class="text-center" id="lamparas_{{$c->id_ciclo}}" value="{{isset($luz) ? $luz->lamparas : ''}}"
                           style="width: 100%">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" class="text-center" id="inicio_luz_{{$c->id_ciclo}}" value="{{isset($luz) ? $luz->inicio_luz : ''}}"
                           style="width: 100%">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{isset($luz) ? convertDateToText($inicio_luz) : ''}}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{isset($luz) ? getSemanaByDate($inicio_luz)->codigo : ''}}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{$dias_luz}}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" class="text-center" id="dias_proy_{{$c->id_ciclo}}"
                           value="{{isset($luz) ? $luz->dias_proy : ''}}" style="width: 100%">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" class="text-center" id="dias_adicional_{{$c->id_ciclo}}"
                           value="{{isset($luz) ? $luz->dias_adicional : ''}}"
                           style="width: 100%">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{isset($luz) ? convertDateToText($fin_luz) : ''}}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{isset($luz) ? getSemanaByDate($fin_luz)->codigo : ''}}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{isset($luz) ? $horas_luz : ''}}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="time" class="text-center" id="hora_ini_{{$c->id_ciclo}}" title="Desde"
                           value="{{isset($luz) ? $luz->hora_ini : ''}}" style="width: 100%">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="time" class="text-center" id="hora_fin_{{$c->id_ciclo}}" title="Hasta"
                           value="{{isset($luz) ? $luz->hora_fin : ''}}" style="width: 100%">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    ${{$costo_luz}}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        @if(!isset($luz))
                            <button type="button" class="btn btn-xs btn-yura_primary" title="Crear" onclick="store_luz('{{$c->id_ciclo}}')">
                                <i class="fa fa-fw fa-plus"></i>
                            </button>
                        @else
                            <button type="button" class="btn btn-xs btn-yura_primary" title="Editar"
                                    onclick="update_luz('{{$luz->id_ciclo_luz}}', '{{$c->id_ciclo}}')">
                                <i class="fa fa-fw fa-pencil"></i>
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </table>
</div>

<script>
    function store_luz(ciclo) {
        datos = {
            _token: '{{csrf_token()}}',
            fecha: $('#filtro_fecha').val(),
            ciclo: ciclo,
            tipo_luz: $('#tipo_luz_' + ciclo).val(),
            lamparas: $('#lamparas_' + ciclo).val(),
            inicio_luz: $('#inicio_luz_' + ciclo).val(),
            dias_adicional: $('#dias_adicional_' + ciclo).val(),
            dias_proy: $('#dias_proy_' + ciclo).val(),
            hora_ini: $('#hora_ini_' + ciclo).val(),
            hora_fin: $('#hora_fin_' + ciclo).val(),
        };
        post_jquery_m('{{url('ciclo_luz/store_luz')}}', datos, function () {
            listar_ciclo_luz();
        }, 'tr_luz_' + ciclo);
    }

    function update_luz(id, ciclo) {
        datos = {
            _token: '{{csrf_token()}}',
            id: id,
            tipo_luz: $('#tipo_luz_' + ciclo).val(),
            lamparas: $('#lamparas_' + ciclo).val(),
            inicio_luz: $('#inicio_luz_' + ciclo).val(),
            dias_adicional: $('#dias_adicional_' + ciclo).val(),
            dias_proy: $('#dias_proy_' + ciclo).val(),
            hora_ini: $('#hora_ini_' + ciclo).val(),
            hora_fin: $('#hora_fin_' + ciclo).val(),
        };
        post_jquery_m('{{url('ciclo_luz/update_luz')}}', datos, function () {
            listar_ciclo_luz();
        }, 'tr_luz_' + ciclo);
    }
</script>

<style>
    #tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 8;
    }
</style>