<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\StoreEnvio;
use yura\Modelos\Envio;

class EnvioObserver
{
    public function saved(Envio $envio)
    {
        StoreEnvio::dispatch($envio)->onQueue('store_envio');
    }
}
