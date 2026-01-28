<?php

namespace yura\Observers\ClasificacionVerde;

use yura\Jobs\Sincronizacion\ClasificacionVerde\StoreClasificacionVerde;
use yura\Modelos\ClasificacionVerde;

class ClasificacionVerdeObserver
{
    public function saved(ClasificacionVerde $clasificacionVerde)
    {
        StoreClasificacionVerde::dispatch($clasificacionVerde)->onQueue('store_clasificacion_verde');
    }


}
