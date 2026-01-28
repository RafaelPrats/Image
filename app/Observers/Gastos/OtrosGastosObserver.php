<?php

namespace yura\Observers\Gastos;

use yura\Jobs\Sincronizacion\Gastos\StoreOtrosGastos;
use yura\Modelos\OtrosGastos;

class OtrosGastosObserver
{
    public function saved(OtrosGastos $otrosGastos)
    {
        StoreOtrosGastos::dispatch($otrosGastos)->onQueue('store_otros_gastos');
    }
}
