<?php

namespace yura\Observers\Costos;

use yura\Jobs\Sincronizacion\Costos\StoreActividadManoObra;
use yura\Modelos\ActividadManoObra;

class ActividadManoObraObserver
{
    public function saved(ActividadManoObra $actividadManoObra)
    {
        StoreActividadManoObra::dispatch($actividadManoObra)->onQueue('store_actividad_mano_obra');
    }
}
