<?php

namespace yura\Observers\ProyNintanga;

use yura\Jobs\Sincronizacion\ProyNintanga\DeleteDistribucionMixtosSemana;
use yura\Jobs\Sincronizacion\ProyNintanga\StoreDistribucionMixtosSemana;
use yura\Modelos\DistribucionMixtosSemana;

class DistribucionMixtosSemanaObserver
{
    public function saved(DistribucionMixtosSemana $distribucionMixtosSemana)
    {
        StoreDistribucionMixtosSemana::dispatch($distribucionMixtosSemana)->onQueue('store_distribucion_mixto_semana');
    }

    public function deleted(DistribucionMixtosSemana $distribucionMixtosSemana)
    {
        DeleteDistribucionMixtosSemana::dispatch($distribucionMixtosSemana)->onQueue('delete_distribucion_mixto_semana');
    }
}
