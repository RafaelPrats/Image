<?php

namespace yura\Observers\Resumen;

use yura\Jobs\Sincronizacion\Resumen\StoreResumenAreaSemanal;
use yura\Modelos\ResumenAreaSemanal;

class ResumenAreaSemanalObserver
{
    public function saved(ResumenAreaSemanal $resumenAreaSemanal)
    {
        //StoreResumenAreaSemanal::dispatch($resumenAreaSemanal)->onQueue('store_resumen_area_semanal');
    }
}
