<?php

namespace yura\Observers\ClasificacionVerde;

use yura\Jobs\Sincronizacion\ClasificacionVerde\StoreStockApertura;
use yura\Modelos\StockApertura;

class StockAperturaObserver
{
    public function saved(StockApertura $stockApertura)
    {
        StoreStockApertura::dispatch($stockApertura)->onQueue('store_stock_apertura');
    }
}
