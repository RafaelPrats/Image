<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\DeleteComprobante;
use yura\Jobs\Sincronizacion\Comercializacion\StoreComprobante;
use yura\Modelos\Comprobante;

class ComprobanteObserver
{
    public function saved(Comprobante $comprobante)
    {
        StoreComprobante::dispatch($comprobante)->onQueue('store_comprobante');
    }

    public function deleted(Comprobante $comprobante)
    {
        DeleteComprobante::dispatch($comprobante)->onQueue('delete_comprobante');
    }

}
