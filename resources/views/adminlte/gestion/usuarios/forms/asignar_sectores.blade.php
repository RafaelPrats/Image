<input type="hidden" id="id_usuario_seleccionado" value="{{$usuario}}">
<table class="table-bordered table-striped" style="width: 100%; border-radius: 18px 0 0 0; border: 1px solid #9d9d9d">
    <tr>
        <th class="text-left th_yura_green" style="border-radius: 18px 0 0 0; padding-left: 10px">
            Sector
        </th>
        <th class="text-center th_yura_green" style="border-radius: 0 18px 0 0; width: 80px">
            Asignar
        </th>
    </tr>
    @foreach($sectores as $s)
        <tr id="tr_sector_{{$s->id_sector}}">
            <td class="text-left" style="border-color: #9d9d9d; padding-left: 10px">
                {{$s->nombre}}
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                @php
                    $check = 0;
                    foreach($asignados as $item)
                        if ($item->id_sector == $s->id_sector)
                            $check = 1;
                @endphp
                <input type="checkbox" id="check_sector_{{$s->id_sector}}" onchange="store_usuario_sector('{{$s->id_sector}}')"
                       {{$check == 1 ? 'checked' : ''}} class="mouse-hand">
            </td>
        </tr>
    @endforeach
</table>

<script>
    function store_usuario_sector(sector) {
        datos = {
            _token: '{{csrf_token()}}',
            sector: sector,
            usuario: $('#id_usuario_seleccionado').val(),
            estado: $('#check_sector_' + sector).prop('checked'),
        };
        $('#tr_sector_' + sector).LoadingOverlay('show');
        $.post('{{url('usuarios/store_usuario_sector')}}', datos, function (retorno) {
            mini_alerta('success', 'Se ha <strong>actualizado</strong> la información', 5000)
        }, 'json').fail(function (retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function () {
            $('#tr_sector_' + sector).LoadingOverlay('hide');
        })
    }
</script>