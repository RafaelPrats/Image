<?php

namespace yura\Observers\Insumos;

use yura\Jobs\Sincronizacion\Insumos\StoreActividad;
use yura\Modelos\Actividad;

class ActividadObserver
{
    public function saved(Actividad $actividad)
    {
        StoreActividad::dispatch($actividad)->onQueue('store_actividad');
    }
}
