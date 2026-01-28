<div class="box box-success">
    {{--ENTRADAS--}}
    <div class="box-header with-border text-center">
        Ciclos <strong>ENTRANTES</strong>
    </div>
    <div class="box-body" style="overflow-x: scroll">
        <table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d" id="table_entrantes">
            <thead>
            <tr id="tr_fija_top_0">
                <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                    Variedad
                </th>
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
                <th class="text-center th_yura_green" style="padding-left: 5px; padding-right: 5px">
                    Costo
                </th>
                <th class="text-center th_yura_green" style="padding-left: 5px; padding-right: 5px">
                    Costo/m2
                </th>
                <th class="text-center th_yura_green">

                </th>
            </tr>
            </thead>
            <tbody>
            @foreach($entradas as $luz)
                @php
                    $ciclo = $luz->ciclo;
                    $modulo = $ciclo->modulo;
                    $dias_ciclo = difFechas(hoy(), $ciclo->fecha_inicio)->days;
                    $inicio_luz = opDiasFecha('+', $luz->inicio_luz, $ciclo->fecha_inicio);
                    $fin_luz = opDiasFecha('+', $luz->inicio_luz + $luz->dias_proy + $luz->dias_adicional - 1, $ciclo->fecha_inicio);
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
                <tr id="tr_luz_{{$luz->id_ciclo_luz}}">
                    <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{$ciclo->variedad->siglas}}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{$modulo->nombre}}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{$modulo->getPodaSiembraByCiclo($ciclo->id_ciclo)}}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{convertDateToText($ciclo->fecha_inicio)}}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{$dias_ciclo}}
                    </th>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="text-center" id="tipo_luz_{{$luz->id_ciclo_luz}}"
                               value="{{$luz->tipo_luz}}" min="0">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="text-center" id="lamparas_{{$luz->id_ciclo_luz}}"
                               value="{{$luz->lamparas}}" min="0">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="text-center" id="inicio_luz_{{$luz->id_ciclo_luz}}"
                               value="{{$luz->inicio_luz}}" min="0">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" id="td_inicio_luz_{{$luz->id_ciclo_luz}}">
                        {{convertDateToText($inicio_luz)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="text-center" id="dias_proy_{{$luz->id_ciclo_luz}}"
                               value="{{$luz->dias_proy}}" min="0">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="text-center" id="dias_adicional_{{$luz->id_ciclo_luz}}"
                               value="{{$luz->dias_adicional}}" min="0">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" id="td_fin_luz_{{$luz->id_ciclo_luz}}">
                        {{convertDateToText($fin_luz)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" id="td_sem_fin_luz_{{$luz->id_ciclo_luz}}">
                        {{getSemanaByDate($fin_luz)->codigo}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" id="td_horas_luz_{{$luz->id_ciclo_luz}}">
                        {{$horas_luz}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="time" style="width: 100%" class="text-center" id="hora_ini_{{$luz->id_ciclo_luz}}"
                               value="{{$luz->hora_ini}}" min="0">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="time" style="width: 100%" class="text-center" id="hora_fin_{{$luz->id_ciclo_luz}}"
                               value="{{$luz->hora_fin}}" min="0">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" id="td_costo_luz_{{$luz->id_ciclo_luz}}">
                        ${{$costo_luz}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" id="td_costo_m2_{{$luz->id_ciclo_luz}}">
                        ₵{{round($costo_luz / $ciclo->area, 4) * 100}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <button type="button" class="btn btn-xs btn-yura_primary" onclick="update_luz('{{$luz->id_ciclo_luz}}')">
                            <i class="fa fa-fw fa-check"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{--SALIDAS--}}
    <div class="box-header with-border text-center">
        Ciclos <strong>SALIENTES</strong>
    </div>
    <div class="box-body" style="overflow-x: scroll">
        <table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d" id="table_salientes">
            <thead>
            <tr id="tr_fija_top_0">
                <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                    Variedad
                </th>
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
                        Hrs. Luz
                    </div>
                </th>
                <th class="text-center th_yura_green" colspan="2">
                    Horario
                </th>
                <th class="text-center th_yura_green" style="padding-left: 5px; padding-right: 5px">
                    Costo
                </th>
                <th class="text-center th_yura_green" style="padding-left: 5px; padding-right: 5px">
                    Costo/m2
                </th>
                <th class="text-center th_yura_green">

                </th>
            </tr>
            </thead>
            <tbody>
            @foreach($salidas as $luz)
                @php
                    $ciclo = $luz->ciclo;
                    $modulo = $ciclo->modulo;
                    $dias_ciclo = difFechas(hoy(), $ciclo->fecha_inicio)->days;
                    $inicio_luz = opDiasFecha('+', $luz->inicio_luz, $ciclo->fecha_inicio);
                    $fin_luz = opDiasFecha('+', $luz->inicio_luz + $luz->dias_proy + $luz->dias_adicional - 1, $ciclo->fecha_inicio);
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
                <tr id="tr_luz_{{$luz->id_ciclo_luz}}">
                    <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{$ciclo->variedad->siglas}}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{$modulo->nombre}}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{$modulo->getPodaSiembraByCiclo($ciclo->id_ciclo)}}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{convertDateToText($ciclo->fecha_inicio)}}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{$dias_ciclo}}
                    </th>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="text-center" id="tipo_luz_{{$luz->id_ciclo_luz}}"
                               value="{{$luz->tipo_luz}}" min="0">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="text-center" id="lamparas_{{$luz->id_ciclo_luz}}"
                               value="{{$luz->lamparas}}" min="0">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="text-center" id="inicio_luz_{{$luz->id_ciclo_luz}}"
                               value="{{$luz->inicio_luz}}" min="0">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" id="td_inicio_luz_{{$luz->id_ciclo_luz}}">
                        {{convertDateToText($inicio_luz)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" id="td_sem_inicio_luz_{{$luz->id_ciclo_luz}}">
                        {{getSemanaByDate($inicio_luz)->codigo}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" id="td_dias_luz_{{$luz->id_ciclo_luz}}">
                        {{$dias_luz}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="text-center" id="dias_proy_{{$luz->id_ciclo_luz}}"
                               value="{{$luz->dias_proy}}" min="0">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="text-center" id="dias_adicional_{{$luz->id_ciclo_luz}}"
                               value="{{$luz->dias_adicional}}" min="0">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" id="td_fin_luz_{{$luz->id_ciclo_luz}}">
                        {{convertDateToText($fin_luz)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" id="td_horas_luz_{{$luz->id_ciclo_luz}}">
                        {{$horas_luz}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="time" style="width: 100%" class="text-center" id="hora_ini_{{$luz->id_ciclo_luz}}"
                               value="{{$luz->hora_ini}}" min="0">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="time" style="width: 100%" class="text-center" id="hora_fin_{{$luz->id_ciclo_luz}}"
                               value="{{$luz->hora_fin}}" min="0">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" id="td_costo_luz_{{$luz->id_ciclo_luz}}">
                        ${{$costo_luz}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" id="td_costo_m2_{{$luz->id_ciclo_luz}}">
                        ₵{{round($costo_luz / $ciclo->area, 4) * 100}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <button type="button" class="btn btn-xs btn-yura_primary" onclick="update_luz('{{$luz->id_ciclo_luz}}')">
                            <i class="fa fa-fw fa-check"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{--ACTIVOS--}}
    <div class="box-header with-border text-center">
        Ciclos <strong>ACTIVOS</strong>
    </div>
    <div class="box-body" style="overflow-x: scroll">
        <table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d" id="table_activos">
            <thead>
            <tr id="tr_fija_top_0">
                <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                    Variedad
                </th>
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
                <th class="text-center th_yura_green" style="padding-left: 5px; padding-right: 5px">
                    Costo
                </th>
                <th class="text-center th_yura_green" style="padding-left: 5px; padding-right: 5px">
                    Costo/m2
                </th>
                <th class="text-center th_yura_green">

                </th>
            </tr>
            </thead>
            <tbody>
            @foreach($activos as $luz)
                @php
                    $ciclo = $luz->ciclo;
                    $modulo = $ciclo->modulo;
                    $dias_ciclo = difFechas(hoy(), $ciclo->fecha_inicio)->days;
                    $inicio_luz = opDiasFecha('+', $luz->inicio_luz, $ciclo->fecha_inicio);
                    $fin_luz = opDiasFecha('+', $luz->inicio_luz + $luz->dias_proy + $luz->dias_adicional - 1, $ciclo->fecha_inicio);
                    $dias_luz = 0;
                    if(isset($luz) && $luz->inicio_luz <= $dias_ciclo)
                        if(($luz->dias_proy + $luz->dias_adicional) >= $dias_ciclo - $luz->inicio_luz)
                            $dias_luz = $dias_ciclo - $luz->inicio_luz;
                        else
                            $dias_luz = ($luz->dias_proy + $luz->dias_adicional);
                    $horas_dia = isset($luz) ? $luz->getHorasDia() : 0;
                    // calcular horas luz
                    $horas_luz = $dias_luz * $horas_dia;
                    $costo_luz = 0;
                    if(isset($luz)){
                        $costo_x_tipo = $luz->tipo_luz / 1000;
                        $costo_x_lampara = $costo_x_tipo * $luz->lamparas;
                        $costo_x_lampara = $costo_x_lampara * $horas_luz;
                        $costo_luz = $costo_x_lampara * 0.10;
                    }
                @endphp
                <tr id="tr_luz_{{$luz->id_ciclo_luz}}">
                    <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{$ciclo->variedad->siglas}}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{$modulo->nombre}}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{$modulo->getPodaSiembraByCiclo($ciclo->id_ciclo)}}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{convertDateToText($ciclo->fecha_inicio)}}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{$dias_ciclo}}
                    </th>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="text-center" id="tipo_luz_{{$luz->id_ciclo_luz}}"
                               value="{{$luz->tipo_luz}}" min="0">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="text-center" id="lamparas_{{$luz->id_ciclo_luz}}"
                               value="{{$luz->lamparas}}" min="0">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="text-center" id="inicio_luz_{{$luz->id_ciclo_luz}}"
                               value="{{$luz->inicio_luz}}" min="0">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" id="td_inicio_luz_{{$luz->id_ciclo_luz}}">
                        {{convertDateToText($inicio_luz)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" id="td_sem_inicio_luz_{{$luz->id_ciclo_luz}}">
                        {{getSemanaByDate($inicio_luz)->codigo}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" id="td_dias_luz_{{$luz->id_ciclo_luz}}">
                        {{$dias_luz}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="text-center" id="dias_proy_{{$luz->id_ciclo_luz}}"
                               value="{{$luz->dias_proy}}" min="0">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="text-center" id="dias_adicional_{{$luz->id_ciclo_luz}}"
                               value="{{$luz->dias_adicional}}" min="0">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" id="td_fin_luz_{{$luz->id_ciclo_luz}}">
                        {{convertDateToText($fin_luz)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" id="td_sem_fin_luz_{{$luz->id_ciclo_luz}}">
                        {{getSemanaByDate($fin_luz)->codigo}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$horas_luz}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="time" style="width: 100%" class="text-center" id="hora_ini_{{$luz->id_ciclo_luz}}"
                               value="{{$luz->hora_ini}}" min="0">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="time" style="width: 100%" class="text-center" id="hora_fin_{{$luz->id_ciclo_luz}}"
                               value="{{$luz->hora_fin}}" min="0">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" id="td_costo_luz_{{$luz->id_ciclo_luz}}">
                        ${{$costo_luz}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" id="td_costo_m2_{{$luz->id_ciclo_luz}}">
                        ₵{{round($costo_luz / $ciclo->area, 4) * 100}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <button type="button" class="btn btn-xs btn-yura_primary" onclick="update_luz('{{$luz->id_ciclo_luz}}')">
                            <i class="fa fa-fw fa-check"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    function update_luz(id) {
        datos = {
            _token: '{{csrf_token()}}',
            id: id,
            tipo_luz: $('#tipo_luz_' + id).val(),
            lamparas: $('#lamparas_' + id).val(),
            inicio_luz: $('#inicio_luz_' + id).val(),
            dias_adicional: $('#dias_adicional_' + id).val(),
            dias_proy: $('#dias_proy_' + id).val(),
            hora_ini: $('#hora_ini_' + id).val(),
            hora_fin: $('#hora_fin_' + id).val(),
        };
        post_jquery_m('{{url('ciclo_luz/update_luz')}}', datos, function () {
            listar_row_luz(id);
        }, 'tr_luz_' + id);
    }
</script>

<style>
    #tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 8;
    }
</style>