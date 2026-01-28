<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\DeletePedido;
use yura\Jobs\Sincronizacion\Comercializacion\StorePedido;
use yura\Modelos\Pedido;

class PedidoObserver
{
    public function saved(Pedido $predido)
    {
        StorePedido::dispatch($predido)->onQueue('store_pedido');
    }

    public function deleted(Pedido $predido)
    {
        DeletePedido::dispatch($predido)->onQueue('delete_pedido');
    }
}
