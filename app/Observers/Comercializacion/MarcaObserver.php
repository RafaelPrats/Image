<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\StoreMarca;
use yura\Modelos\Marca;

class MarcaObserver
{
    public function saved(Marca $marca)
    {
        StoreMarca::dispatch($marca)->onQueue('store_marca');
    }
}
