<?php

namespace yura\Observers\ProyNintanga;

use yura\Jobs\Sincronizacion\ProyNintanga\DeleteDistribucionMixtos;
use yura\Jobs\Sincronizacion\ProyNintanga\StoreDistribucionMixtos;
use yura\Modelos\DistribucionMixtos;

class DistribucionMixtosObserver
{
    public function saved(DistribucionMixtos $distribucionMixtos)
    {
        StoreDistribucionMixtos::dispatch($distribucionMixtos)->onQueue('store_distribucion_mixto');
    }

    public function deleted(DistribucionMixtos $distribucionMixtos)
    {
        DeleteDistribucionMixtos::dispatch($distribucionMixtos)->onQueue('delete_distribucion_mixto');
    }
}
