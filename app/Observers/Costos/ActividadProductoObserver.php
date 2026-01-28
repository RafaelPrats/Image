<?php

namespace yura\Observers\Costos;

use yura\Jobs\Sincronizacion\Costos\StoreActividadProducto;
use yura\Modelos\ActividadProducto;

class ActividadProductoObserver
{
    public function saved(ActividadProducto $actividadProducto)
    {
        StoreActividadProducto::dispatch($actividadProducto)->onQueue('store_actividad_producto');
    }
}
