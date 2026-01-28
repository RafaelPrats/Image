<table width="100%" class="table-striped table-bordered" style="font-size: 0.9em; border-color: #9d9d9d"
    id="table_content_variedades">
    <thead>
        <tr>
            <th class="text-center th_yura_green">Tipo</th>
            <th class="text-center th_yura_green">Código</th>
            {{-- <th class="text-center">Minimo apertura (Dias)</th>
        <th class="text-center">Estandar apertura (Dias)</th>
        <th class="text-center">Maximo apertura (Dias)</th> --}}
            <th class="text-center th_yura_green">Tallos por malla</th>
            <th class="text-center th_yura_green">Medida</th>
            {{-- <th class="text-center">Tallos por Ramo</th>
        <th class="text-center">Saldo Inicial</th> --}}
            <th class="text-center th_yura_green" width="115px">
                <button type="button" class="btn btn-xs btn-yura_default" title="Añadir tipo" onclick="add_variedad()">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </th>
        </tr>
    </thead>
    @if (sizeof($variedades) > 0)
        @foreach ($variedades as $v)
            <tr onmouseover="$(this).css('background-color','#add8e6')"
                onmouseleave="$(this).css('background-color','')" class="{{ $v->estado == 1 ? '' : 'error' }}"
                id="row_variedad_{{ $v->id_variedad }}">
                <td style="border-color: #9d9d9d" class="text-center">
                    {{ $v->nombre }}
                </td>
                <td style="border-color: #9d9d9d" class="text-center">
                    {{ $v->siglas }}
                </td>
                {{-- <td style="border-color: #9d9d9d" class="text-center">
                    {{$v->minimo_apertura}}
                </td>
                <td style="border-color: #9d9d9d" class="text-center">
                    {{$v->estandar_apertura}}
                </td>
                <td style="border-color: #9d9d9d" class="text-center">
                    {{$v->maximo_apertura}}
                </td> --}}
                <td style="border-color: #9d9d9d" class="text-center">
                    {{ $v->tallos_x_malla }}
                </td>
                <td style="border-color: #9d9d9d" class="text-center">
                    {{ $v->tipo == 'P' ? 'Peso' : 'Longitud' }}
                </td>
                {{-- <td style="border-color: #9d9d9d" class="text-center">
                    {{$v->tallos_x_ramo_estandar}}
                </td>
                <td style="border-color: #9d9d9d" class="text-center">
                    {{$v->saldo_inicial}}
                </td> --}}
                <td style="border-color: #9d9d9d" class="text-center" style="width: 80px">
                    <div class="btn-group">
                        {{-- <button class="btn btn-xs btn-default" type="button" title="Precio"
                                    onclick="add_precio('{{$v->id_variedad}}')">
                                <i class="fa fa-usd"></i>
                            </button> --}}
                        <button class="btn btn-xs btn-yura_default" type="button" title="Editar"
                            onclick="edit_variedad('{{ $v->id_variedad }}')">
                            <i class="fa fa-fw fa-pencil"></i>
                        </button>
                        {{-- <button class="btn btn-xs btn-default" type="button" title="Clasificaciones Unitarias"
                                onclick="vincular_variedad_unitaria('{{$v->id_variedad}}')">
                            <i class="fa fa-fw fa-filter"></i>
                        </button> --}}
                        @if ($v->receta == 1)
                            <button class="btn btn-xs btn-yura_dark" type="button" title="Administrar Receta"
                                onclick="admin_receta('{{ $v->id_variedad }}')">
                                <i class="fa fa-fw fa-gift"></i>
                            </button>
                        @endif
                        {{-- <button class="btn btn-xs btn-yura_dark" type="button" title="Regalías"
                            onclick="add_regalias('{{ $v->id_variedad }}')">
                            <i class="fa fa-fw fa-usd"></i>
                        </button> --}}
                        <button class="btn btn-xs btn-yura_danger" type="button"
                            title="{{ $v->estado == 1 ? 'Desactivar' : 'Activar' }}"
                            onclick="cambiar_estado_variedad('{{ $v->id_variedad }}','{{ $v->estado }}')">
                            <i class="fa fa-fw fa-{{ $v->estado == 1 ? 'trash' : 'unlock' }}"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
    @else
        <tr onmouseover="$(this).css('background-color','#add8e6')" onmouseleave="$(this).css('background-color','')">
            <td style="border-color: #9d9d9d" class="text-center mouse-hand" colspan="3">
                No hay variedades registradas para esta planta
            </td>
        </tr>
    @endif
</table>

<script>
    function admin_receta(id_var) {
        datos = {
            id_var: id_var,
        };
        get_jquery('{{ url('plantas_variedades/admin_receta') }}', datos, function(retorno) {
            modal_view('modal_admin_receta', retorno, '<i class="fa fa-fw fa-plus"></i> Administrar receta',
                true, false, '{{ isPC() ? '75%' : '' }}');
        });
    }
</script>
