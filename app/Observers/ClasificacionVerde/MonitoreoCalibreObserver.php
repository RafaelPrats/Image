<?php

namespace yura\Observers\ClasificacionVerde;

use yura\Jobs\Sincronizacion\ClasificacionVerde\StoreMonitoreoCalibre;
use yura\Modelos\MonitoreoCalibre;

class MonitoreoCalibreObserver
{
    public function saved(MonitoreoCalibre $monitoreoCalibre)
    {
        StoreMonitoreoCalibre::dispatch($monitoreoCalibre)->onQueue('store_monitoreo_calibre');
    }
}
