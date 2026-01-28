<?php

namespace yura\Observers\ClasificacionVerde;

use yura\Jobs\Sincronizacion\ClasificacionVerde\StoreRecepcionClasificacionVerde;
use yura\Modelos\RecepcionClasificacionVerde;

class RecepcionClasificacionVerdeObserver
{
    public function saved(RecepcionClasificacionVerde $recepcionClasificacionVerde)
    {
        StoreRecepcionClasificacionVerde::dispatch($recepcionClasificacionVerde)->onQueue('store_recepcion_clasificacion_verde');
    }
}
