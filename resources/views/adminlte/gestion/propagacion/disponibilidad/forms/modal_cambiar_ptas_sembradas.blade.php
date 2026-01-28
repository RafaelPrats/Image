<legend style="font-size: 1em" class="text-center">Mover <strong>{{$valor}}</strong> semana(s) de <strong>{{$variedad->nombre}}</strong></legend>

<table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="text-center th_yura_green">
            Semana desde
        </th>
        <th class="text-center th_yura_green">
            Plantas
        </th>
        <th class="text-center th_yura_green">

        </th>
    </tr>
    @foreach(explode('|', $propag_disp->destino_plantas_sembradas) as $item)
        @php
            $semana_ini = explode('+', $item)[0];
        @endphp
        <tr>
            <td class="text-center" style="border-color: #9d9d9d">
                {{$semana_ini}}
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                {{number_format(explode('+', $item)[1])}}
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <button type="button" class="btn btn-xs btn-yura_primary"
                        onclick="store_cambiar_ptas_sembradas('{{$propag_disp->id_propag_disponibilidad}}', '{{$semana_ini}}')">
                    <i class="fa fa-fw fa-check"></i>
                </button>
            </td>
        </tr>
    @endforeach
</table>

<script>
    function store_cambiar_ptas_sembradas(id_disp, semana_ini) {
        datos = {
            _token: '{{csrf_token()}}',
            id_disp: id_disp,
            semana_ini: semana_ini,
            valor: $('#cambiar_ptas_sembradas_' + id_disp).val(),
            variedad: $('#filtro_predeterminado_variedad').val(),
        };
        post_jquery('{{url('propag_disponibilidad/cambiar_ptas_sembradas')}}', datos, function () {
            cerrar_modals();
            listar_disponibilidades();

            datos = {
                id_disp: id_disp,
                valor: $('#cambiar_ptas_sembradas_' + id_disp).val(),
                variedad: $('#filtro_predeterminado_variedad').val(),
            };
            get_jquery('{{url('propag_disponibilidad/modal_cambiar_ptas_sembradas')}}', datos, function (retorno) {
                modal_view('moda-view_modal_cambiar_ptas_sembradas', retorno,
                    '<i class="fa fa-fw fa-sitemap"></i> Mover semanas de plantas sembradas', true, false, '50%');
            });
        });
    }
</script>