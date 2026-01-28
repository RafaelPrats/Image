<?php

namespace yura\Observers\Insumos;

use yura\Jobs\Sincronizacion\Insumos\StoreProducto;
use yura\Modelos\Producto;

class ProductoObserver
{
    public function saved(Producto $producto)
    {
        StoreProducto::dispatch($producto)->onQueue('store_producto');
    }
}
