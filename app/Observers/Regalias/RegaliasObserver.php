<?php

namespace yura\Observers\Regalias;

use yura\Jobs\Sincronizacion\Regalias\Store;

class RegaliasObserver
{
    function saved($regalias)
    {
        Store::dispatch($regalias)->onQueue('store_regalias');
    }
}
