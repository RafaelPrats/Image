<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\DeleteImpuestoDetalleFactura;
use yura\Jobs\Sincronizacion\Comercializacion\StoreImpuestoDetalleFactura;
use yura\Modelos\ImpuestoDetalleFactura;

class ImpuestoDetalleFacturaObserver
{
    public function saved(ImpuestoDetalleFactura $impuestoDetalleFactura)
    {
        StoreImpuestoDetalleFactura::dispatch($impuestoDetalleFactura)->onQueue('store_impuesto_detalle_factura');
    }

    public function deleted(ImpuestoDetalleFactura $impuestoDetalleFactura)
    {
        DeleteImpuestoDetalleFactura::dispatch($impuestoDetalleFactura)->onQueue('delete_impuesto_detalle_factura');
    }
}
