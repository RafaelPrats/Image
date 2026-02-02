@php
    $color_fondo = 'white';
    $color_texto = 'black';
    if ($color != '') {
        $color_fondo = $color->fondo;
        $color_texto = $color->texto;
    }
@endphp
<th class="padding_lateral_5" onclick="actualizar_row('{{ $pos }}')"
    style="border-color: {{ $color_texto }}; color: {{ $color_texto }}; background-color: {{ $color_fondo }}">
    {{ $variedad->planta->nombre }}
</th>
<th class="padding_lateral_5"
    style="border-color: {{ $color_texto }}; color: {{ $color_texto }}; background-color: {{ $color_fondo }}">
    {{ $variedad->nombre }}
</th>
<th class="padding_lateral_5"
    style="border-color: {{ $color_texto }}; color: {{ $color_texto }}; background-color: {{ $color_fondo }}">
    {{ $empaque->nombre }}
</th>
<th class="padding_lateral_5"
    style="border-color: {{ $color_texto }}; color: {{ $color_texto }}; background-color: {{ $color_fondo }}">
    {{ $tallos_x_ramo }}
</th>
<th class="padding_lateral_5"
    style="border-color: {{ $color_texto }}; color: {{ $color_texto }}; background-color: {{ $color_fondo }}">
    {{ $longitud_ramo }}cm
</th>
<th class="padding_lateral_5"
    style="border-color: {{ $color_texto }}; color: {{ $color_texto }}; background-color: {{ $color_fondo }}">
    {{ $peso_ramo }}gr
</th>
@php
    $acumulado_pedido = 0;
    $total_inventario = $ramos_inventario;
@endphp
@foreach ($fechas as $pos_f => $fecha)
    @php
        $ramos_actuales = 0;
        $ramos_distribuidos = 0;
        $ramos_cambios = 0;
        foreach ($query_ramos_solidos as $r) {
            if ($r->fecha == $fecha) {
                $ramos_actuales += $r->cantidad;
            }
        }
        foreach ($query_ramos_mixtos as $r) {
            if ($r->fecha == $fecha) {
                $ramos_actuales += $r->cantidad;
            }
        }
        foreach ($query_ramos_distribuidos as $r) {
            if ($r->fecha == $fecha) {
                $ramos_distribuidos += $r->cantidad;
            }
        }
        $cambios_en_fecha = false;
        foreach ($query_ramos_cambios as $r) {
            if ($r->fecha == $fecha) {
                $ramos_cambios += $r->ramos;
                $cambios_en_fecha = true;
            }
        }
        $ramos_anteriores = $ramos_actuales - $ramos_cambios;
        $acumulado_pedido += $ramos_actuales;
        $saldo = $total_inventario - $acumulado_pedido;
    @endphp
    <th class="text-center" style="border-color: #9d9d9d">
        <div class="btn-group">
            <button type="button" class="btn btn-xs btn-yura_dark" title="Pedidos"
                id="btn_pedidos_{{ $pos }}_{{ $pos_f }}" data-valor="{{ $ramos_anteriores }}">
                {{ $ramos_anteriores }}
            </button>
            <button type="button" class="btn btn-xs btn-yura_warning" title="Pedidos Actuales"
                onclick="distribuir_trabajo('{{ $pos }}', '{{ $pos_f }}')"
                id="btn_actuales_{{ $pos }}_{{ $pos_f }}" data-valor="{{ $ramos_actuales }}">
                {{ $ramos_actuales }}
            </button>
            <button type="button" class="btn btn-xs btn-yura_{{ $saldo < 0 ? 'danger' : 'primary' }}"
                title="{{ $saldo < 0 ? 'Por Armar' : 'Armados' }}"
                id="btn_saldo_{{ $pos }}_{{ $pos_f }}" data-valor="{{ $saldo }}">
                {{ abs($saldo) }}
            </button>
        </div>
    </th>
    <th class="text-center" style="border-color: #9d9d9d">
        @if ($cambios_en_fecha)
            @if ($ramos_cambios != 0)
                <button type="button" class="btn btn-xs btn-yura_{{ $ramos_cambios >= 0 ? 'primary' : 'danger' }}"
                    title="Ver cambios" onclick="ver_cambios('{{ $pos }}', '{{ $pos_f }}')">
                    {{ $ramos_cambios > 0 ? '+' : '' }}{{ $ramos_cambios }}
                </button>
            @else
                <button type="button" class="btn btn-xs btn-yura_default" title="Ver cambios"
                    onclick="ver_cambios('{{ $pos }}', '{{ $pos_f }}')">
                    <i class="fa fa-fw fa-exchange"></i>
                </button>
            @endif
        @endif
    </th>
    <th class="text-center" style="border-color: #9d9d9d">
        @if ($ramos_distribuidos > 0)
            <button type="button" class="btn btn-xs btn-yura_default"
                style="background-color: #00f3ff !important; color: black !important; border-color: #00b5be !important">
                {{ $ramos_distribuidos }}
            </button>
        @endif
    </th>
@endforeach
<th class="text-center" style="border-color: #9d9d9d">
    @if ($ramos_inventario > 0)
        <button type="button" class="btn btn-xs btn-yura_dark" onclick="modal_inventario('{{ $pos }}')">
            {{ $ramos_inventario }}
        </button>
    @endif
</th>
<th class="text-center" style="border-color: #9d9d9d">
    <input type="number" style="width: 100%" class="text-center" id="armar_{{ $pos }}">
</th>
<th class="text-center" style="border-color: #9d9d9d">
    <div class="btn-group">
        <button type="button" class="btn btn-xs btn-yura_primary" onclick="modal_armar_row('{{ $pos }}')"
            title="Grabar armados">
            <i class="fa fa-fw fa-save"></i>
        </button>
        <button type="button" class="btn btn-xs btn-yura_default" onclick="actualizar_row('{{ $pos }}')"
            title="Actualizar presentacion">
            <i class="fa fa-fw fa-refresh"></i>
        </button>
    </div>
</th>
