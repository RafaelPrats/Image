<?php

namespace yura\Observers\Planta;

use yura\Jobs\Sincronizacion\Planta\Store;
use yura\Modelos\Planta;

class PlantaObserver
{
    public function saved(Planta $planta)
    {
        Store::dispatch($planta)->onQueue('store_planta');
    }
}
