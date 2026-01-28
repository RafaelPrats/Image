<div style="overflow-y: scroll; overflow-x: scroll; max-height: 450px">
    <table class="table-striped table-bordered" style="width: 100%; border: 2px solid #9d9d9d; font-size: 1em; border-radius: 18px 0 0 0">
        <tr id="tr_fija_top_0">
            <th class="text-center th_yura_green th_fijo_left_0" style="border-radius: 18px 0 0 0; z-index: 9 !important;">
                <div style="width: 220px">
                    Labores/Semanas
                </div>
            </th>
            @foreach($semanas as $pos_s => $sem)
                <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
                    <div style="width: 80px">
                        {{$sem->codigo}}
                    </div>
                </th>
            @endforeach
        </tr>
        @foreach($matriz as $pos_l => $item)
            <tr>
                <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
                    {{$item['app_nombre']}}
                </th>
                @foreach($item['modulos'] as $pos_m => $mod)
                    @php
                        $id_celda = 'celda_'.$pos_l.'_'.$semanas[$pos_m]->codigo;
                        $count = count($mod);
                        $tooltip = count($mod) > 0 ? 'Módulos: <br>' : '';
                        $back_color = '';
                        foreach ($mod as $m){
                            $tooltip .= '<em>'.$m->nombre.'</em><br>';
                        }
                    @endphp
                    <td class="text-center mouse-hand celda_hovered"
                        style="border-color: #9d9d9d; background-color: {{$tooltip != '' ? '#03de00' : $back_color}}" id="{{$id_celda}}"
                        onclick="cargar_labor_semanal('{{$item['app_nombre']}}', '{{$semanas[$pos_m]->codigo}}')"
                        onmouseover="mouse_over_celda('{{$id_celda}}', 1)" onmouseleave="mouse_over_celda('{{$id_celda}}', 0)">
                        <span data-toggle="tooltip" data-placement="top" title="{{$tooltip != '' ? $tooltip : ''}}" data-html="true">
                            {{$count > 0 ? $count.'º' : ''}}
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