<?php

namespace yura\Observers\Monitoreo;

use yura\Jobs\Sincronizacion\Monitoreo\Store;
use yura\Modelos\Monitoreo;

class MonitoreoObserver
{
    public function saved(Monitoreo $monitoreo)
    {
        Store::dispatch($monitoreo)->onQueue('store_monitoreo');
    }
}
