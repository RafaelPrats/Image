<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\DeleteDetallePedido;
use yura\Jobs\Sincronizacion\Comercializacion\StoreDetallePedido;
use yura\Modelos\DetallePedido;

class DetallePedidoObserver
{
    public function saved(DetallePedido $detallePedido)
    { 
        StoreDetallePedido::dispatch($detallePedido)->onQueue('store_detalle_pedido');
    }

    public function deleted(DetallePedido $detallePedido)
    {
        DeleteDetallePedido::dispatch($detallePedido)->onQueue('delete_detalle_pedido');
    }
}
