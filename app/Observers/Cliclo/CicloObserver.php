<?php

namespace yura\Observers\Cliclo;

use yura\Jobs\Sincronizacion\Ciclo\Store;
use yura\Modelos\Ciclo;

class CicloObserver
{
    public function saved(Ciclo $ciclo)
    {
        Store::dispatch($ciclo)->onQueue('store_ciclo');
    }
}
