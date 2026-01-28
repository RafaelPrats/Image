<div>
    <div
        style="display: flex;align-items: center;justify-content: space-between;background: #337ab7;color:white;height: 30px;">
        <div style="margin-left: 10px"><b>CONSIGNATARIO</b></div>
        <div style="margin-right: 10px">
            <button type="button" class="btn btn-xs btn-danger" title="Elminar campo"
                onclick="elimnar_consignatario(null,this)">
                <i class="fa fa-fw fa-trash"></i>
            </button>
        </div>
    </div>
    <form id="form_add_consignatario" class="form-consignatario">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="nombre_completo">Nombre</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required maxlength="250"
                        autocomplete="off" value="">
                    <input type="hidden" id="id_consignatario" name="id_consignatario" class="form-control"
                        value="">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="identificacion">Códio Jire</label>
                    <input type="text" id="identificacion" name="identificacion" class="form-control" required
                        autocomplete="off" value="">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="number" onkeypress="return isNumber()" id="telefono" name="telefono"
                        class="form-control" maxlength="25" autocomplete="off" value="">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="pais">País</label>
                    <select id="pais" name="pais" class="form-control">
                        <option selected disabled>Seleccione</option>
                        @foreach ($dataPais as $pais)
                            <option value="{{ $pais->codigo }}">{{ $pais->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="provincia">Ciudad</label>
                    <input type="text" id="ciudad" name="ciudad" class="form-control" required maxlength="255"
                        autocomplete="off" value="" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="correo">Correo</label>
                    <input type="email" id="correo" name="correo" class="form-control" maxlength="255"
                        autocomplete="off" value="">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="direccion">Dirección</label>
                    <input type="text" id="direccion" name="direccion" class="form-control" value="">
                </div>
            </div>
        </div>
    </form>
</div>
