<table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%" id="table_pedido_proceso">
    <thead>
        <tr class="tr_fija_top_0">
            <th class="text-center bg-yura_dark">
                ESTADO
            </th>
            <th class="text-center bg-yura_dark">
                DESCRIPCION
            </th>
            <th class="text-center padding_lateral_5 bg-yura_dark">
                USUARIO
            </th>
            <th class="text-center padding_lateral_5 bg-yura_dark">
                REGISTRO
            </th>
            <th class="text-center padding_lateral_5 bg-yura_dark">
                PROCESOS
            </th>
            <th class="text-center padding_lateral_5 bg-yura_dark">
                PROGRESO %
            </th>
            <th class="text-center padding_lateral_5 bg-yura_dark">
                ULTIMA ACTUALIZ.
            </th>
            @if (session('id_usuario') == 1)
                <th class="text-center bg-yura_dark padding_lateral_5" style="width: 60px">
                    ADMIN
                </th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach ($listado as $item)
            <tr style="font-size: 0.8em" onmouseover="$(this).css('background-color', '#dddddd')"
                onmouseleave="$(this).css('background-color', '')"
                id="tr_pedido_proceso_{{ $item->id_pedido_proceso }}">
                <td class="text-center padding_lateral_5" style="border-color: #9d9d9d;">
                    @if ($item->estado == 'P')
                        <span class="badge" style="margin-bottom: 2px; width: 100%">
                            PROCESANDO
                        </span>
                    @else
                        <span class="badge btn-yura_primary" style="margin-bottom: 2px; width: 100%">
                            COMPLETADO
                        </span>
                    @endif
                </td>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $item->descripcion }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $item->usuario->username }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ substr($item->fecha_registro, 11, 5) }}
                </th>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{ $item->cant_procesado }} <b>/ {{ $item->total_procesar }}</b>
                </td>
                <th class="text-center progress" style="border-color: #9d9d9d"
                    id="celda_progreso_{{ $item->id_pedido_proceso }}">
                    @php
                        $bg_progreso_end = $item->estado == 'P' ? '#ef6d11' : '#00b388';
                    @endphp
                    <div class="progress-bar text-center btn-yura_primary" role="progressbar"
                        style="width: {{ $item->progreso }}%; padding-top: 6px; position: relative; background: linear-gradient(to right, #5a7177, {{ $bg_progreso_end }}) !important;"
                        aria-valuemin="0" aria-valuemax="100">
                        <span style="position: ; right: 5%; color: {{ $item->progreso > 50 ? 'white' : 'black' }}">
                            {{ round($item->progreso) }}%
                        </span>
                    </div>
                </th>
                <th class="text-center" style="border-color: #9d9d9d; color: black">
                    {{ substr($item->last_update, 11, 5) }}
                </th>
                @if (session('id_usuario') == 1)
                    <th class="text-center" style="border-color: #9d9d9d">
                        <div class="btn-group">
                            @if ($item->estado == 'P')
                                <button type="button" class="btn btn-xs btn-yura_warning" title="Completar Proceso"
                                    onclick="completar_pedido_proceso('{{ $item->id_pedido_proceso }}')">
                                    <i class="fa fa-fw fa-check"></i>
                                </button>
                            @endif
                            <button type="button" class="btn btn-xs btn-yura_danger" title="Eliminar Registro"
                                onclick="delete_pedido_proceso('{{ $item->id_pedido_proceso }}')">
                                <i class="fa fa-fw fa-trash"></i>
                            </button>
                        </div>
                    </th>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>
