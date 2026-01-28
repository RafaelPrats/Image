<?php

namespace yura\Observers\Costos;

use yura\Jobs\Sincronizacion\Costos\StoreCostosHoras;
use yura\Modelos\CostoHoras;

class CostosHorasObserver
{
    public function saved(CostoHoras $costoHoras)
    {
        StoreCostosHoras::dispatch($costoHoras)->onQueue('store_costo_horas');
    }

}
