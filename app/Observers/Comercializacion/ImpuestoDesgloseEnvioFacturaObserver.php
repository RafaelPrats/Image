<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\DeleteImpuestoDesgloseEnvioFactura;
use yura\Jobs\Sincronizacion\Comercializacion\StoreImpuestoDesgloseEnvioFactura;
use yura\Modelos\ImpuestoDesgloseEnvioFactura;

class ImpuestoDesgloseEnvioFacturaObserver
{
    public function saved(ImpuestoDesgloseEnvioFactura $impuestoDesgloseEnvioFactura)
    {
        StoreImpuestoDesgloseEnvioFactura::dispatch($impuestoDesgloseEnvioFactura)->onQueue('store_impuesto_desglose_envio_factura');
    }

    public function deleted(ImpuestoDesgloseEnvioFactura $impuestoDesgloseEnvioFactura)
    {
        DeleteImpuestoDesgloseEnvioFactura::dispatch($impuestoDesgloseEnvioFactura)->onQueue('delete_impuesto_desglose_envio_factura');
    }
}
