<table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%">
    <tr>
        <th class="text-center th_yura_green">
            Variedad
        </th>
        <th class="text-center th_yura_green">
            Color
        </th>
        <th class="text-center th_yura_green">
            Longitud
        </th>
        <th class="text-center th_yura_green">
            Tallos
        </th>
        @if (session('id_usuario') == 1)
            <th class="text-center th_yura_green" style="width: 60px">
            </th>
        @endif
    </tr>
    @foreach ($listado as $item)
        @php
            $variedad = $item->variedad;
        @endphp
        <tr onmouseover="$(this).addClass('bg-yura_dark')" onmouseleave="$(this).removeClass('bg-yura_dark')">
            <th class="text-center" style="border-color: #9d9d9d">
                {{ $variedad->planta->nombre }}
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                {{ $variedad->nombre }}
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                {{ $item->longitud }}cm
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                {{ $item->cantidad }}
            </th>
            @if (session('id_usuario') == 1)
                <th class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_danger"
                            onclick="delete_sobrante('{{ $item->id_sobrante_recepcion }}')">
                            <i class="fa fa-fw fa-trash"></i>
                        </button>
                    </div>
                </th>
            @endif
        </tr>
    @endforeach
</table>

<script>
    function delete_sobrante(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
        }
        modal_quest('modal_quest_delete_sobrante', '<div class="alert alert-info text-center">' +
            '¿Está seguro de <strong>ELIMINAR</strong> el registro de sobrante?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de alerta', true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                post_jquery_m('{{ url('recepcion/delete_sobrante') }}', datos, function() {
                    cerrar_modals();
                    ver_sobrantes();
                });
            });
    }
</script>
