 <div class="col-md-6">
     <div id="table_empaque_c" style="overflow-y: scroll; height: 500px;">
         @if (sizeof($empaques) > 0)
             <table width="100%" class="table-responsive table-bordered" style="border-color: #9d9d9d"
                 id="table_content_empaque_c">
                 <thead>
                     <tr style="font-size: 0.9em" class="tr_fija_top_0">
                         <th class="text-center th_yura_green">
                             NOMBRE CAJAS | FACTOR DE CONVERCIÓN | PESO CAJA (gr)
                         </th>
                         <th class="text-center th_yura_green">
                             OPCIONES
                         </th>
                     </tr>
                 </thead>
                 @foreach ($empaques as $item)
                     @if ($item->tipo == 'C')
                         <tr onmouseover="$(this).css('background-color','#add8e6')"
                             onmouseleave="$(this).css('background-color','')">
                             <td style="border-color: #9d9d9d" class="text-center">
                                 {{ $item->nombre }}
                             </td>
                             <td style="border-color: #9d9d9d" class="text-center">
                                 <div class="btn-group">
                                     <button type="button" {{ $item->estado == 1 ? '' : 'disabled' }}
                                         class="btn btn-yura_dark btn-xs" title="Detalle empaque"
                                         onclick="detalle_empaque('{{ $item->id_empaque }}')">
                                         <i class="fa fa-list-ol" aria-hidden="true"></i>
                                     </button>
                                     <button type="button" {{ $item->estado == 1 ? '' : 'disabled' }}
                                         class="btn btn-yura_primary btn-xs" title="Productos del empaque"
                                         onclick="admin_productos('{{ $item->id_empaque }}')">
                                         <i class="fa fa-fw fa-gift" aria-hidden="true"></i>
                                     </button>
                                     <button type="button" {{ $item->estado == 1 ? '' : 'disabled' }}
                                         class="btn btn-yura_default btn-xs" title="editar"
                                         onclick="add_empaque('{{ $item->id_empaque }}')">
                                         <i class="fa fa-pencil" aria-hidden="true"></i>
                                     </button>
                                     <button type="button"
                                         class="btn btn-yura_{{ $item->estado == 1 ? 'warning' : 'primary' }} btn-xs"
                                         title="{{ $item->estado == 1 ? 'Deshabilitar' : 'Habilitar' }}"
                                         onclick="update_detalle_empaque('{{ $item->id_empaque }}','{{ $item->estado }}')">
                                         <i class="fa fa-{{ $item->estado == 1 ? 'ban' : 'check' }}"
                                             aria-hidden="true"></i>
                                     </button>
                                 </div>
                             </td>
                         </tr>
                     @endif
                 @endforeach
             </table>
             <div id="pagination_listado_empaques_c">
                 {{-- {!! str_replace('/?','?',$listado->render()) !!} --}}
             </div>
         @else
             <div class="alert alert-info text-center">No se han encontrado coincidencias</div>
         @endif
     </div>
 </div>
 <div class="col-md-6">
     <div id="table_empaque_p" style="overflow-y: scroll; height: 500px;">
         @if (sizeof($empaques) > 0)
             <table width="100%" class="table-responsive table-bordered" style="border-color: #9d9d9d"
                 id="table_content_empaques_p">
                 <thead>
                     <tr style="font-size: 0.9em" class="tr_fija_top_0">
                         <th class="text-center th_yura_green">
                             NOMBRE PRESENTACIÓN
                         </th>
                         <th class="text-center th_yura_green">
                             OPCIONES
                         </th>
                     </tr>
                 </thead>
                 @foreach ($empaques as $item)
                     @if ($item->tipo == 'P')
                         <tr onmouseover="$(this).css('background-color','#add8e6')"
                             onmouseleave="$(this).css('background-color','')">
                             <td style="border-color: #9d9d9d" class="text-center">
                                 {{ $item->nombre }}
                             </td>
                             <td style="border-color: #9d9d9d" class="text-center">
                                 <div class="btn-group">
                                     {{-- <button type="button" class="btn btn-default btn-xs" title="Detalle empaque" onclick="detalle_empaque('{{$item->id_empaque}}')">
                                        <i class="fa fa-list-ol" aria-hidden="true"></i>
                                     </button> --}}
                                     <button type="button" {{ $item->estado == 1 ? '' : 'disabled' }}
                                         class="btn btn-yura_primary btn-xs" title="Productos del empaque"
                                         onclick="admin_productos('{{ $item->id_empaque }}')">
                                         <i class="fa fa-fw fa-gift" aria-hidden="true"></i>
                                     </button>
                                     <button type="button" {{ $item->estado == 1 ? '' : 'disabled' }}
                                         class="btn btn-yura_default btn-xs" title="editar"
                                         onclick="add_empaque('{{ $item->id_empaque }}')">
                                         <i class="fa fa-pencil" aria-hidden="true"></i>
                                     </button>
                                     <button type="button"
                                         class="btn btn-yura_{{ $item->estado == 1 ? 'warning' : 'primary' }} btn-xs"
                                         title="{{ $item->estado == 1 ? 'Deshabilitar' : 'Habilitar' }}"
                                         onclick="update_detalle_empaque('{{ $item->id_empaque }}','{{ $item->estado }}')">
                                         <i class="fa fa-{{ $item->estado == 1 ? 'ban' : 'check' }}"
                                             aria-hidden="true"></i>
                                     </button>
                                 </div>
                             </td>
                         </tr>
                     @endif
                 @endforeach
             </table>
             <div id="pagination_listado_empaques_p">
                 {{-- {!! str_replace('/?','?',$listado->render()) !!} --}}
             </div>
         @else
             <div class="alert alert-info text-center">No se han encontrado coincidencias</div>
         @endif
     </div>
 </div>

 <script>
     function admin_productos(id_empaque) {
         datos = {
             id_empaque: id_empaque
         };
         get_jquery('{{ url('caja_presentacion/admin_productos') }}', datos, function(retorno) {
             modal_view('modal_admin_productos', retorno,
                 '<i class="fa fa-edit" aria-hidden="true"></i> Editar productos del empaque', true, false,
                 '{{ isPC() ? '85%' : '' }}');
         });
     }
 </script>
