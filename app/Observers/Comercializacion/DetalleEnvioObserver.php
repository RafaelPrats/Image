<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\StoreDetalleEnvio;
use yura\Modelos\DetalleEnvio;

class DetalleEnvioObserver
{
    public function saved(DetalleEnvio $detalleEnvio)
    {
        StoreDetalleEnvio::dispatch($detalleEnvio)->onQueue('store_detalle_envio');
    }
}
