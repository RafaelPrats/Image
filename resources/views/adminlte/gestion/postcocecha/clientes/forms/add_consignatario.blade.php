<div id="div_contactos_cliente">
    <div class="row">
        <div class="col-md-12">
            <table width="100%" class="table table-responsive table-bordered"
                style="font-size: 0.8em; border-color: #9d9d9d">
                <thead>
                    <tr class="th_yura_green">
                        <th class="text-center" style="border-color: #9d9d9d">
                            <div class="box-title" style="font-size:15px">Agregar consignatario</div>
                        </th>
                        @if (es_server())
                            <th class="text-center" style="border-color: #9d9d9d;width: 10%;">
                                <button type="button" class="btn btn-xs btn-default" title="Añadir campo"
                                    onclick="aumentar_consignatario()">
                                    <i class="fa fa-fw fa-plus"></i>
                                </button>
                            </th>
                        @endif
                    </tr>
                </thead>
            </table>

            <div class="col-md-12">
                <div class="row" id="row_add_user_contactos" style="overflow-y: scroll; max-height: 700px">
                    @if (count($clienteConsignatario))
                        @foreach ($clienteConsignatario as $cc)
                            <div>
                                <div
                                    style="display: flex;align-items: center;justify-content: space-between;background: #337ab7;color:white;height: 30px;">
                                    <div style="margin-left: 10px"><b>CONSIGNATARIO</b></div>
                                    <div style="margin-right: 10px">
                                        <button type="button" class="btn btn-xs btn-danger" title="Elminar campo"
                                            onclick="elimnar_consignatario('{{ csrf_token() }}',this,'{{ $cc->id_cliente_consignatario }}')">
                                            <i class="fa fa-fw fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <form id="form_add_consignatario" class="form-consignatario">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="nombre_completo">Nombre</label>
                                                <input type="text" id="nombre" name="nombre" class="form-control"
                                                    required maxlength="250" autocomplete="off"
                                                    value="{{ $cc->consignatario->nombre }}">
                                                <input type="hidden" id="id_consignatario" name="id_consignatario"
                                                    class="form-control" value="{{ $cc->id_consignatario }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="identificacion">Código JIRE</label>
                                                <input type="text" id="identificacion" name="identificacion"
                                                    class="form-control" required autocomplete="off"
                                                    value="{{ $cc->consignatario->identificacion }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="telefono">Teléfono</label>
                                                <input type="number" onkeypress="return isNumber()" id="telefono"
                                                    name="telefono" class="form-control" maxlength="25"
                                                    autocomplete="off" value="{{ $cc->consignatario->telefono }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="pais">País</label>
                                                <select id="pais" name="pais" class="form-control">
                                                    <option selected disabled>Seleccione</option>
                                                    @foreach ($dataPais as $pais)
                                                        <option
                                                            {{ $cc->consignatario->codigo_pais == $pais->codigo ? 'selected' : '' }}
                                                            value="{{ $pais->codigo }}">{{ $pais->nombre }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="provincia">Ciudad</label>
                                                <input type="text" id="ciudad" name="ciudad" class="form-control"
                                                    required maxlength="255" autocomplete="off"
                                                    value="{{ $cc->consignatario->ciudad }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="correo">Correo</label>
                                                <input type="email" id="correo" name="correo" class="form-control"
                                                    maxlength="255" autocomplete="off"
                                                    value="{{ $cc->consignatario->correo }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="direccion">Dirección</label>
                                                <input type="text" id="direccion" name="direccion"
                                                    class="form-control" value="{{ $cc->consignatario->direccion }}">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            @if (es_server())
                <div class="text-center">
                    <button class="btn btn-success" type="button" id="btn_guardar_modal_add_cliente"
                        onclick="store_cliente_consignatario('{{ csrf_token() }}','{{ $id_cliente }}')">
                        <span class="bootstrap-dialog-button-icon fa fa-fw fa-save"></span>Guardar</button>
                </div>
            @endif
        </div>
    </div>
</div>
