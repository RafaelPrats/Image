<div id="table_codigo_dae" style="overflow-y: scroll; max-height: 450px">
    @if(sizeof($listado)>0)
        <table width="100%" class="table-responsive table-bordered" style="border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0"
               id="table_content_codigo_dae">
            <thead>
            <tr id="tr_fija_top_0">
                <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0">
                    AÑO
                </th>
                <th class="text-center th_yura_green">
                    MES
                </th>
                <th class="text-center th_yura_green">
                    PAÍS
                </th>
                <th class="text-center th_yura_green" style="width: 80px">
                    DAE
                </th>
                <th class="text-center th_yura_green" style="width: 150px">
                    CÓDIGO DAE
                </th>
                <th class="text-center th_yura_green">
                    EMPRESA
                </th>
                <th class="text-center th_yura_green" style="border-radius: 0 18px 0 0; width: 60px">
                </th>
            </tr>
            </thead>
            @foreach($listado as $item)
                <tr onmouseover="$(this).css('background-color','#add8e6')" onmouseleave="$(this).css('background-color','')"
                    class="{{$item->estado == 1 ? '':'error'}}" id="row_codigo_dae_{{$item->id_codigo_dae}}">
                    <td style="border-color: #9d9d9d" class="text-center">{{$item->anno}}</td>
                    <td style="border-color: #9d9d9d" class="text-center">{{$item->mes}}</td>
                    <td style="border-color: #9d9d9d" class="text-center">{{$item->pais() !=null ? $item->pais()->nombre : ''}}</td>
                    <td style="border-color: #9d9d9d" class="text-center">
                        <input type="text" style="width: 100%" value="{{$item->dae}}" class="text-center" id="dae_{{$item->id_codigo_dae}}">
                    </td>
                    <td style="border-color: #9d9d9d" class="text-center">
                        <input type="text" style="width: 100%" value="{{$item->codigo_dae}}" class="text-center"
                               id="codigo_dae_{{$item->id_codigo_dae}}">
                    </td>
                    <td style="border-color: #9d9d9d" class="text-center">{{$item->empresa->razon_social}}</td>
                    <td style="border-color: #9d9d9d" class="text-center">
                        <div class="btn-group">
                            <button class="btn btn-yura_primary btn-xs" title="Actualizar código"
                                    onclick="update_codigo_dae('{{$item->id_codigo_dae}}')">
                                <i class="fa fa-fw fa-edit"></i>
                            </button>
                            <button class="btn btn-yura_warning btn-xs" title="Desactivar código"
                                    onclick="desactivar_codigo('{{$item->id_codigo_dae}}')">
                                <i class="fa fa-fw fa-ban"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </table>
    @else
        <div class="alert alert-info text-center">No se han encontrado coincidencias</div>
    @endif
</div>

<style>
    #table_content_codigo_dae tr#tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 9 !important;
    }
</style>

<script>
    function update_codigo_dae(id) {
        datos = {
            _token: '{{csrf_token()}}',
            id_codigo_dae: id,
            dae: $('#dae_' + id).val(),
            codigo_dae: $('#codigo_dae_' + id).val(),
        };
        post_jquery('{{url('codigo_dae/update_codigo_dae')}}', datos, function () {

        });
    }
</script>
