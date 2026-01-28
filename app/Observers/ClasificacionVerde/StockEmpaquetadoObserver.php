<?php

namespace yura\Observers\ClasificacionVerde;

use yura\Jobs\Sincronizacion\ClasificacionVerde\StoreStockEmpaquetado;
use yura\Modelos\StockEmpaquetado;

class StockEmpaquetadoObserver
{
    public function saved(StockEmpaquetado $stockEmpaquetado)
    {
        StoreStockEmpaquetado::dispatch($stockEmpaquetado)->onQueue('store_stock_empaquetado');
    }
}
