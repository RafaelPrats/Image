<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\StoreAerolinea;
use yura\Modelos\Aerolinea;

class AerolineaObserver
{
    public function saved(Aerolinea $aerolinea)
    {
        StoreAerolinea::dispatch($aerolinea)->onQueue('store_aerolinea');
    }
}
