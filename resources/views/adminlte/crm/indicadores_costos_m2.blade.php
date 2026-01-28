<legend style="margin-bottom: 5px"></legend>
<div style="overflow-x: scroll">
    <table class="table-responsive" width="100%">
        <tr>
            <th class="text-center">$/m<sup>2</sup>/año (1 mes)</th>
            <th class="text-center">$/m<sup>2</sup>/año (4 meses)</th>
            <th class="text-center">$/m<sup>2</sup>/año (1 año)</th>
        </tr>
        <tr>
            <th class="text-center">
                <canvas id="canvas_costos_m2_1_mes2" style="width: 210px"></canvas>
            </th>
            <th class="text-center">
                <canvas id="canvas_costos_m2_mensual2" style="width: 210px"></canvas>
            </th>
            <th class="text-center">
                <canvas id="canvas_costos_m2_anual2" style="width: 210px"></canvas>
            </th>
        </tr>
        <tr>
            <th class="text-center">{{number_format($costos_m2_1_mes, 2)}}</th>
            <th class="text-center">{{number_format($costos_m2_mensual, 2)}}</th>
            <th class="text-center">{{number_format($costos_m2_anual, 2)}}</th>
        </tr>
    </table>
</div>

<script>
    var rangos_costos_m2_1_mes = [];
    @foreach(getIntervalosIndicador('C11') as $r)
    rangos_costos_m2_1_mes.push({
        desde: parseFloat('{{$r->desde}}'),
        hasta: parseFloat('{{$r->hasta}}'),
        color: '{{$r->color}}',
    });
            @endforeach

    var rangos_costos_m2_mensual = [];
    @foreach(getIntervalosIndicador('C9') as $r)
    rangos_costos_m2_mensual.push({
        desde: parseFloat('{{$r->desde}}'),
        hasta: parseFloat('{{$r->hasta}}'),
        color: '{{$r->color}}',
    });
            @endforeach

    var rangos_costos_m2_anual = [];
    @foreach(getIntervalosIndicador('C10') as $r)
    rangos_costos_m2_anual.push({
        desde: parseFloat('{{$r->desde}}'),
        hasta: parseFloat('{{$r->hasta}}'),
        color: '{{$r->color}}',
    });
    @endforeach

    render_gauge('canvas_costos_m2_1_mes2', '{{number_format($costos_m2_1_mes, 2)}}', rangos_costos_m2_1_mes, true, 100);
    render_gauge('canvas_costos_m2_mensual2', '{{number_format($costos_m2_mensual, 2)}}', rangos_costos_m2_mensual, true, 100);
    render_gauge('canvas_costos_m2_anual2', '{{number_format($costos_m2_anual, 2)}}', rangos_costos_m2_anual, true, 100);

    function select_variedad() {
        datos = {
            variedad: $('#variedad').val()
        };
        get_jquery('{{url('')}}')
    }
</script>