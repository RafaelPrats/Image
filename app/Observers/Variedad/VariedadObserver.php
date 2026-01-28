<?php

namespace yura\Observers\Variedad;

use yura\Jobs\Sincronizacion\Variedad\StoreVariedad;
use yura\Modelos\Variedad;

class VariedadObserver
{
    public function saved(Variedad $variedad)
    {
        StoreVariedad::dispatch($variedad)->onQueue('store_variedad');
    }
}
