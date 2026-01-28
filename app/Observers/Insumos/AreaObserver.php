<?php

namespace yura\Observers\Insumos;

use yura\Jobs\Sincronizacion\Insumos\StoreArea;
use yura\Modelos\Area;

class AreaObserver
{
    public function saved(Area $area)
    {
        StoreArea::dispatch($area)->onQueue('store_area');
    }
}
