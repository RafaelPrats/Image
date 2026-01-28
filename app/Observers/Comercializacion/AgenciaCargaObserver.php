<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\StoreAgenciaCarga;
use yura\Modelos\AgenciaCarga;

class AgenciaCargaObserver
{
    public function saved(AgenciaCarga $agenciaCarga)
    {
        StoreAgenciaCarga::dispatch($agenciaCarga)->onQueue('store_agencia_carga');
    }
}
