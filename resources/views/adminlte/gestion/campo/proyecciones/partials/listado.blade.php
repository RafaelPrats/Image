<div style="overflow-y: scroll; overflow-x: scroll; max-height: 450px">
    <table class="table-striped table-bordered" style="width: 100%; border: 2px solid #9d9d9d; font-size: 1em; border-radius: 18px 0 0 0">
        <tr id="tr_fija_top_0">
            <th class="text-center th_yura_green th_fijo_left_0" style="border-radius: 18px 0 0 0; z-index: 9 !important;">
                <div style="width: 100px">
                    Mód/Sem
                </div>
            </th>
            @foreach($semanas as $pos_s => $sem)
                <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
                    <div style="width: 80px">
                        <button type="button" class="btn btn-yura_dark btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            {{$sem->codigo}}
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            @foreach($resumen_semana[$pos_s] as $r)
                                <li>
                                    <a href="javascript:void(0)" onclick="cargar_labor_semanal('{{$r->app_nombre}}', '{{$sem->codigo}}')">
                                        {{$r->app_nombre}}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </th>
            @endforeach
        </tr>
        @foreach($matriz as $item)
            <tr>
                <th class="text-center th_fijo_left_0" style="border-color: #9d9d9d; background-color: #e9ecef">
                    <button type="button" class="btn btn-yura_default btn-xs">
                        {{$item['m']->nombre}}
                    </button>
                </th>
                @foreach($semanas as $sem)
                    @php
                        $html = '';
                        $back_color = '';
                        $tooltip = '';
                        $id_celda = 'celda_'.$item['m']->id_modulo.'_'.$sem->codigo;
                    @endphp
                    @foreach($item['proys'] as $proy)
                        @php
                            if ($sem->codigo == $proy->semana){
                                $html = $proy->num_sem.'º';
                                if ($proy->num_sem == 1)
                                    if ($proy->poda_siembra == 'P')
                                        $back_color = '#efff00';    // Poda
                                    else
                                        $back_color = '#08ffe8';        // Siembra
                                $getResumenAplicaciones = $proy->getResumenAplicaciones();
                                if (count($getResumenAplicaciones) > 0){
                                    foreach($getResumenAplicaciones as $title){
                                        if ($title->min != $title->max)
                                            $tooltip .= '<em>'.$title->app_nombre.' #'.$title->min.'-'.$title->max.'</em><br>';
                                        else
                                            $tooltip .= '<em>'.$title->app_nombre.' #'.$title->min.'</em><br>';
                                    }
                                }
                            }
                        @endphp
                    @endforeach
                    <td class="text-center mouse-hand celda_hovered" id="{{$id_celda}}"
                        style="border-color: #9d9d9d; background-color: {{$tooltip != '' ? '#03de00' : $back_color}}"
                        onclick="select_celda('{{$item['m']->id_modulo}}', '{{$sem->codigo}}')"
                        onmouseover="mouse_over_celda('{{$id_celda}}', 1)" onmouseleave="mouse_over_celda('{{$id_celda}}', 0)">
                        <span data-toggle="tooltip" data-placement="top" title="{{$tooltip != '' ? $tooltip : ''}}" data-html="true">
                            @if($tooltip != '')
                                <strong style="color: black">{{$html}}</strong>
                            @else
                                {{$html}}
                            @endif
                        </span>
                    </td>
                @endforeach
            </tr>
        @endforeach
    </table>
</div>

<style>
    #tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 8;
    }

    .th_fijo_left_0 {
        position: sticky;
        left: 0;
        z-index: 8;
    }
</style>

<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });

    function mouse_over_celda(id, action) {
        $('.celda_hovered').css('border', '1px solid #9d9d9d');
        if (action == 1) {  // over
            $('#' + id).css('border', '3px solid black');
        }
    }

    function select_celda(mod, sem) {
        datos = {
            mod: mod,
            sem: sem,
            variedad: $('#filtro_variedad').val(),
        };
        get_jquery('{{url('proyeccion_aplicaciones/select_celda')}}', datos, function (retorno) {
            modal_view('modal-view_select_celda', retorno, '<i class="fa fa-fw fa-sitemap"></i> Programación semanal', true, false, '70%');
        });
    }

    function cargar_labor_semanal(nombre, semana) {
        datos = {
            nombre: nombre,
            semana: semana,
            variedad: $('#filtro_variedad').val(),
            uso: $('#filtro_uso').val(),
        };
        get_jquery('{{url('proyeccion_aplicaciones/cargar_labor_semanal')}}', datos, function (retorno) {
            modal_view('modal-view_cargar_labor_semanal', retorno, '<i class="fa fa-fw fa-sitemap"></i> Programación semanal', true, false, '80%');
        });
    }
</script>