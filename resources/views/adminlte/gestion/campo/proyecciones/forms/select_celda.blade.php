<legend class="text-center" style="font-size: 1em">Módulo: <strong>{{$modulo->nombre}}</strong> - Semana: <strong>{{$semana}}</strong></legend>
@if($proy != '')
    <table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0">
        <tr>
            <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0">
                Labor
            </th>
            <th class="text-center th_yura_green">
                Fecha
            </th>
            <th class="text-center th_yura_green">
                Repetición
            </th>
            <th class="text-center th_yura_green">
                Lt x Cama
            </th>
            <th class="text-center th_yura_green" style="border-radius: 0 18px 0 0">
                Opciones
            </th>
        </tr>
        @foreach($aplicaciones as $item)
            <tr>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{$item->app_nombre}}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{$item->fecha != '' ? $item->fecha : hoy()}}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{$item->app_repeticion}}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{$item->app_litro_x_cama}}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{$item->getEstado()}}
                </td>
            </tr>
        @endforeach
    </table>
@else
    <div class="alert alert-info text-center">No hay resultados que mostrar</div>
@endif