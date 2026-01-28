<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\StoreDetalleDespacho;
use yura\Modelos\DetalleDespacho;

class DetalleDespachoObserver
{
    public function saved(DetalleDespacho $detalleDespacho)
    {
        StoreDetalleDespacho::dispatch($detalleDespacho)->onQueue('store_detalle_despacho');
    }
}
