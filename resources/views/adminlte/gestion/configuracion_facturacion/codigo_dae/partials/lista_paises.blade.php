<div class="row" id="busqueda">
    <div class="col-md-12">
        <div class="input-group">
            <input type="text" id="nombre_pais" name="nombre_pais" value="" placeholder="Buscar país"
                class="form-control" onkeyup="buscar_pais(this.value)">
            <span class="input-group-btn">
                <a class="btn btn-default" type="button"><span class="glyphicon glyphicon-search"></span></a>
            </span>
        </div>
    </div>
    <div class="row" style="margin-top:55px">
        <div class="container-fluid">
            <div id="paises" class="col-md-9">
                @foreach ($dataPaises as $pais)
                    <div class="col-md-4">
                        <input type="checkbox" id="codigo_pais_{{ $pais->codigo }}"
                            {{ in_array($pais->codigo, $paises) ? 'checked' : '' }}
                            name="codigo_pais_{{ $pais->codigo }}" onclick="selected('codigo_pais_{{ $pais->codigo }}')"
                            value="{{ $pais->codigo }}">
                        <label for="codigo_pais_{{ $pais->codigo }}" class="mouse-hand">{{ $pais->nombre }}</label>
                    </div>
                    @if (in_array($pais->codigo, $paises))
                        <script>
                            selected('codigo_pais_{{ $pais->codigo }}');
                        </script>
                    @endif
                @endforeach
            </div>
            <div class="col-md-3">
                <div class="list-group" id="paises_selected">
                    <a href="javascript:void(0)" class="list-group-item active list-group-item-action">
                        <i class="fa fa-flag" aria-hidden="true"></i>
                        Paises Seleccionados
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
