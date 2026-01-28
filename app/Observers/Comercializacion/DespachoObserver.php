<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\StoreDespacho;
use yura\Modelos\Despacho;

class DespachoObserver
{
    public function saved(Despacho $despacho)
    {
        StoreDespacho::dispatch($despacho)->onQueue('store_despacho');
    }
}
