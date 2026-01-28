<select class="select-yura_default" id="select_submenu_crm" onchange="cargar_url($(this).val())">
    <option value="">Dashboards</option>
    @foreach(getSubmenusOfUser(Session::get('id_usuario')) as $item)
        @if($item->tipo == 'C')
            <option value="{{$item->url}}">{{$item->nombre}}</option>
        @endif
    @endforeach
</select>