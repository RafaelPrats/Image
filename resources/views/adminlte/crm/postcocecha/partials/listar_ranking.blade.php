@php
    $total = 0;
    $colores_array = ['#00b388', '#30bbbb', '#ef6e11', '#d01c62'];
    foreach ($query as $q) {
        $total += $q->cantidad;
    }
@endphp

@foreach ($query as $pos => $item)
    @php
        $valor = $item->cantidad;
    @endphp
    <div class="progress-group">
        <table style="width: 100%">
            <tr>
                <th>
                    {{ $item->nombre }} <sup>{{ porcentaje($valor, $total, 1) }}%</sup>
                </th>
                <td class="text-right">
                    {{ number_format($valor) }}
                </td>
            </tr>
        </table>

        <div class="progress progress-sm">
            <div class="progress-bar"
                style="width: {{ porcentaje($valor, $total, 1) }}%; background-color: {{ $colores_array[$pos] }}"></div>
        </div>
    </div>
@endforeach
