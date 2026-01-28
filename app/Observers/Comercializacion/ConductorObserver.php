<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\StoreConductor;
use yura\Modelos\Conductor;

class ConductorObserver
{
    public function saved(Conductor $conductor)
    {
        StoreConductor::dispatch($conductor)->onQueue('store_conductor');
    }
}
