@if (!isset($empaque->id_empaque))
    <form id="form_add_empaque">
        <input type="hidden" id="id_empaque" value="">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="nombre_empaque">Nombre</label>
                    <input type="text" id="nombre_empaque" name="nombre_empaque" class="form-control" required
                        maxlength="250" autocomplete="off" placeholder="NOMBRE">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="conversion_empaque">Conversion</label>
                    <input type="number" id="conversion_empaque" name="conversion_empaque" class="form-control"
                        required maxlength="250" autocomplete="off" placeholder="0.25" value="1">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="peso_empaque">Peso</label>
                    <input type="number" id="peso_empaque" name="peso_empaque" class="form-control" required
                        maxlength="250" autocomplete="off" placeholder="1000" value="1">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="tipo_empaque">Tipo empaque</label>
                    <select id="tipo" name="tipo" class="form-control">
                        <option value="C">Caja</option>
                        <option value="P">Presentación</option>
                    </select>
                </div>
            </div>
        </div>
    </form>
@else
    <form id="form_add_empaque">
        <input type="hidden" value="{{ $empaque->id_empaque }}">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="nombre_empaque">Nombre</label>
                    <input type="text" id="nombre_empaque" name="nombre_empaque" class="form-control" required
                        maxlength="250" autocomplete="off" placeholder="NOMBRE" value="{{ $empaque->nombre }}">
                    <input type="hidden" id="peso_empaque" name="peso_empaque" value="0">
                    <input type="hidden" id="conversion_empaque" name="conversion_empaque" value="0">
                </div>
            </div>
        </div>
    </form>
@endif
