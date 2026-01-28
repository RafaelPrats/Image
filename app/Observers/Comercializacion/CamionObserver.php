<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\StoreCamion;
use yura\Modelos\Camion;

class CamionObserver
{
    public function saved(Camion $camion)
    {
        StoreCamion::dispatch($camion)->onQueue('store_camion');
    }
}
