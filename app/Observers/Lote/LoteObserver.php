<?php

namespace yura\Observers\Lote;

use yura\Jobs\Sincronizacion\Lote\Store;
use yura\Modelos\Lote;

class LoteObserver
{
    public function saved(Lote $lote)
    {
        Store::dispatch($lote)->onQueue('store_lote');
    }
}
