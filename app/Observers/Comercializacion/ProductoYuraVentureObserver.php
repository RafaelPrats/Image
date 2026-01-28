<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\DeleteProductoYuraVenture;
use yura\Jobs\Sincronizacion\Comercializacion\StoreProductoYuraVenture;
use yura\Modelos\ProductoYuraVenture;

class ProductoYuraVentureObserver
{
    public function saved(ProductoYuraVenture $productoYuraVenture)
    {
        StoreProductoYuraVenture::dispatch($productoYuraVenture)->onQueue('store_producto_yura_venture');
    }

    public function deleted(ProductoYuraVenture $productoYuraVenture)
    {
        DeleteProductoYuraVenture::dispatch($productoYuraVenture)->onQueue('delete_producto_yura_venture');
    }
}
