<div class="bs-example bs-example-tabs" data-example-id="togglable-tabs">
    <ul id="tabs_ciclos" class="nav nav-tabs nav-justified" role="tablist">
        <li role="presentation" class="active">
            <a href="#proyecciones" id="home-tab" role="tab" data-toggle="tab" aria-controls="home" aria-expanded="true">Proyecciones</a>
        </li>
        <li role="presentation" class="">
            <a href="#campo" role="tab" id="profile-tab" data-toggle="tab" aria-controls="profile" aria-expanded="false">Campo</a>
        </li>
    </ul>
    <div id="tabs_ciclos_content" class="tab-content">
        <div role="tabpanel" class="tab-pane fade active in" id="proyecciones" aria-labelledby="home-tab">
            @include('adminlte.gestion.sectores_modulos.partials._proyecciones')
        </div>
        <div role="tabpanel" class="tab-pane fade" id="campo" aria-labelledby="profile-tab">
            @include('adminlte.gestion.sectores_modulos.partials._campo')
        </div>
    </div>
</div>