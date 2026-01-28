<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\StoreEmpaque;
use yura\Modelos\Empaque;

class EmpaqueObserver
{
    public function saved(Empaque $empaque)
    {
        StoreEmpaque::dispatch($empaque)->onQueue('store_empaque');
    }
}
