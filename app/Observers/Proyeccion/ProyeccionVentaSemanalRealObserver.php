<?php

namespace yura\Observers\Proyeccion;

use yura\Jobs\Sincronizacion\Proyeccion\StoreProyeccionVentaSemanalReal;
use yura\Modelos\ProyeccionVentaSemanalReal;

class ProyeccionVentaSemanalRealObserver
{
    public function saved(ProyeccionVentaSemanalReal $proyeccionVentaSemanalReal)
    {
        StoreProyeccionVentaSemanalReal::dispatch($proyeccionVentaSemanalReal)->onQueue('store_proyeccion_venta_semanal_real');
    }
}
