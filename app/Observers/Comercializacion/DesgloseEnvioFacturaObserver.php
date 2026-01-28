<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\DeleteDesgloseEnvioFactura;
use yura\Jobs\Sincronizacion\Comercializacion\StoreDesgloseEnvioFactura;
use yura\Modelos\DesgloseEnvioFactura;

class DesgloseEnvioFacturaObserver
{
    public function saved(DesgloseEnvioFactura $desgloseEnvioFactura)
    {
        StoreDesgloseEnvioFactura::dispatch($desgloseEnvioFactura)->onQueue('store_desglose_envio_factura');
    }

    public function deleted(DesgloseEnvioFactura $desgloseEnvioFactura)
    {
        DeleteDesgloseEnvioFactura::dispatch($desgloseEnvioFactura)->onQueue('delete_desglose_envio_factura');
    }
}
