<?php

namespace yura\Observers\Precio;

use yura\Jobs\Sincronizacion\Precio\Store;
use yura\Modelos\Precio;

class PrecioObserver
{
    public function saved(Precio $precio)
    {
        Store::dispatch($precio)->onQueue('store_precio');
    }
}
