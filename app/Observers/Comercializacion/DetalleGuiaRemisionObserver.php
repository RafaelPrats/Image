<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\StoreDetalleGuiaRemision;
use yura\Modelos\DetalleGuiaRemision;

class DetalleGuiaRemisionObserver
{
    public function saved(DetalleGuiaRemision $detalleGuiaRemision)
    {
        StoreDetalleGuiaRemision::dispatch($detalleGuiaRemision)->onQueue('store_detalle_guia_remision');
    }
}
