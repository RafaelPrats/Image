<?php

namespace yura\Observers\Modulo;

use yura\Jobs\Sincronizacion\Modulo\Store;
use yura\Modelos\Modulo;

class ModuloObserver
{
    public function saved(Modulo $modulo)
    {
        Store::dispatch($modulo)->onQueue('store_modulo');
    }
}
