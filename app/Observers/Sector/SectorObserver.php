<?php

namespace yura\Observers\Sector;

use yura\Jobs\Sincronizacion\Sector\StoreSector;
use yura\Modelos\Sector;

class SectorObserver
{
    function saved(Sector $sector)
    {
        StoreSector::dispatch($sector)->onQueue('store_sector');
    }
}
