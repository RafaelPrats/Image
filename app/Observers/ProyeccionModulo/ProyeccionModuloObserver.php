<?php

namespace yura\Observers\ProyeccionModulo;

use yura\Jobs\Sincronizacion\ProyeccionModulo\{ StoreProyeccionModulo, DeleteProyeccionModulo };
use yura\Modelos\ProyeccionModulo;

class ProyeccionModuloObserver
{
    public function saved(ProyeccionModulo $proyeccionModulo)
    {
        StoreProyeccionModulo::dispatch($proyeccionModulo)->onQueue('store_proyeccion_modulo');
    }

    public function deleted(ProyeccionModulo $proyeccionModulo)
    {
        DeleteProyeccionModulo::dispatch($proyeccionModulo)->onQueue('delete_proyeccion_modulo');
    }
}
