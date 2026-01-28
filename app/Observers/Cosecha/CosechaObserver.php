<?php

namespace yura\Observers\Cosecha;

use yura\Jobs\Sincronizacion\Cosecha\StoreCosecha;
use yura\Modelos\Cosecha;

class CosechaObserver
{
    public function saved(Cosecha $cosecha)
    {
        StoreCosecha::dispatch($cosecha)->onQueue('store_cosecha');
    }
}
