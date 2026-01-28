<?php

namespace yura\Observers\ClasificacionVerde;

use yura\Jobs\Sincronizacion\ClasificacionVerde\DeleteDetalleClasificacionVerde;
use yura\Jobs\Sincronizacion\ClasificacionVerde\StoreDetalleClasificacionVerde;
use yura\Modelos\DetalleClasificacionVerde;

class DetalleClasificacionVerdeObserver
{
    public function saved(DetalleClasificacionVerde $detalleClasificacionVerde)
    {
        StoreDetalleClasificacionVerde::dispatch($detalleClasificacionVerde)->onQueue('store_detalle_clasificacion_verde');
    }

    public function deleted(DetalleClasificacionVerde $detalleClasificacionVerde)
    {
        DeleteDetalleClasificacionVerde::dispatch($detalleClasificacionVerde)->onQueue('delete_detalle_clasificacion_verde');
    }
}
