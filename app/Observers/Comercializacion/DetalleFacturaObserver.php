<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\DeleteDetalleFactura;
use yura\Jobs\Sincronizacion\Comercializacion\StoreDetalleFactura;
use yura\Modelos\DetalleFactura;

class DetalleFacturaObserver
{
    public function saved(DetalleFactura $detalleFactura)
    {
        StoreDetalleFactura::dispatch($detalleFactura)->onQueue('store_detalle_factura');
    }

    public function deleted(DetalleFactura $detalleFactura)
    {
        DeleteDetalleFactura::dispatch($detalleFactura)->onQueue('delete_detalle_factura');
    }
}
