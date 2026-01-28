<div id="table_usuarios" style="overflow-y: scroll; max-height: 450px">
    @if(sizeof($listado)>0)
        <table width="100%" class="table-responsive table-bordered" style="border-color: #9d9d9d; border-radius: 18px 18px 0 0"
               id="table_content_usuarios">
            <thead>
            <tr style="background-color: #dd4b39; color: white" id="tr_fija_top_0">
                <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0">
                    NOMBRE COMPLETO
                </th>
                <th class="text-center th_yura_green">
                    CORREO
                </th>
                <th class="text-center th_yura_green">
                    USUARIO
                </th>
                <th class="text-center th_yura_green">
                    ROL
                </th>
                <th class="text-center th_yura_green" style="border-radius: 0 18px 0 0">
                    OPCIONES
                </th>
            </tr>
            </thead>
            @foreach($listado as $item)
                <tr onmouseover="$(this).css('background-color','#add8e6')" onmouseleave="$(this).css('background-color','')"
                    class="{{$item->estado == 'A'?'':'error'}}" id="row_usuarios_{{$item->id_usuario}}">
                    <td style="border-color: #9d9d9d" class="text-center">{{$item->nombre_completo}}</td>
                    <td style="border-color: #9d9d9d" class="text-center">{{$item->correo}}</td>
                    <td style="border-color: #9d9d9d" class="text-center">{{$item->username}}</td>
                    <td style="border-color: #9d9d9d" class="text-center">{{$item->rol}}</td>
                    <td style="border-color: #9d9d9d" class="text-center">
                        <div class="btn-group">
                            <button type="button" class="btn btn-yura_default btn-xs" title="Detalles"
                                    onclick="ver_usuario('{{$item->id_usuario}}')" id="btn_view_usuario_{{$item->id_usuario}}">
                                <i class="fa fa-fw fa-eye" style="color: black"></i>
                            </button>
                            @if (es_server())
                                @if(getUsuario($item->id_usuario)->rol()->tipo == 'S')
                                    <button type="button" class="btn {{$item->estado == 'A' ? 'btn-yura_primary' : 'btn-yura_danger'}} btn-xs"
                                            title="{{$item->estado == 'A' ? 'Desactivar' : 'Activar'}}"
                                            onclick="eliminar_usuario('{{$item->id_usuario}}', '{{$item->estado}}')"
                                            id="btn_usuarios_{{$item->id_usuario}}">
                                        <i class="fa fa-fw {{$item->estado == 'A' ? 'fa-trash' : 'fa-unlock'}}" style="color: black"
                                            id="icon_usuarios_{{$item->id_usuario}}"></i>
                                    </button>
                                @endif
                                <button class="btn btn-xs btn-yura_default" type="button" title="Asignar sectores"
                                        onclick="asignar_sectores('{{$item->id_usuario}}')">
                                    <i class="fa fa-fw fa-leaf"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </table>
    @else
        <div class="alert alert-info text-center">No se han encontrado coincidencias</div>
    @endif
</div>

<script>
    function asignar_sectores(usuario) {
        datos = {
            usuario: usuario,
        };
        get_jquery('{{url('usuarios/asignar_sectores')}}', datos, function (retorno) {
            modal_view('modal-view_asignar_sectores', retorno, '<i class="fa fa-fw fa-users"></i> Asignar sectores', true, false,
                '{{isPC() ? '50%' : ''}}');
        });
    }
</script>

<style>
    #tr_fija_top_0 th{
        position: sticky;
        top: 0;
        z-index: 9;
    }
</style>
