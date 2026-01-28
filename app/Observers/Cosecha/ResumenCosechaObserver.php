<?php

namespace yura\Observers\Cosecha;

use yura\Jobs\Sincronizacion\Cosecha\StoreResumenCosecha;
use yura\Modelos\ResumenSemanaCosecha;

class ResumenCosechaObserver
{
    public function saved(ResumenSemanaCosecha $resumenCosecha)
    {
        StoreResumenCosecha::dispatch($resumenCosecha)->onQueue('store_resumen_semana_cosecha');
    }
}
