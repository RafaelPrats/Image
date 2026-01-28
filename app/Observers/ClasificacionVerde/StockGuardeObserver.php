<?php

namespace yura\Observers\ClasificacionVerde;

use yura\Jobs\Sincronizacion\ClasificacionVerde\StoreStockGuarde;
use yura\Modelos\StockGuarde;

class StockGuardeObserver
{
    public function saved(StockGuarde $stockGuarde)
    {
        StoreStockGuarde::dispatch($stockGuarde)->onQueue('store_stock_guarde');
    }
}
