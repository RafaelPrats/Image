<?php

namespace yura\Observers\Resumen;

use yura\Jobs\Sincronizacion\Resumen\StoreResumenCostoSemanal;
use yura\Modelos\ResumenCostosSemanal;

class ResumenCostoSemanalObserver
{
    public function saved(ResumenCostosSemanal $resumenCostosSemanal)
    {
        StoreResumenCostoSemanal::dispatch($resumenCostosSemanal)->onQueue('store_resumen_costo_semanal');
    }
}
