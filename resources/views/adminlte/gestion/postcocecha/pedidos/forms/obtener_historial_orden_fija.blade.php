<div style="overflow-y: scroll; max-height: 500px; width: 100%;">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green">
                <input type="checkbox" id="check_all_fechas_update_of" checked onchange="$('.check_all_fechas_update_of').prop('checked', $(this).prop('checked'))">
            </th>
            <th class="text-center th_yura_green">
                Fechas de la Orden Fija
            </th>
        </tr>
        @foreach ($fechas as $pos => $f)
            <tr>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="checkbox" id="check_fecha_update_of_{{ $pos }}"
                        class="check_all_fechas_update_of" checked value="{{ $f }}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <label for="check_fecha_update_of_{{ $pos }}" class="mouse-hand">
                        {{ convertDateToText($f) }}
                    </label>
                </th>
            </tr>
        @endforeach
    </table>
</div>
