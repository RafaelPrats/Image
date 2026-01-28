<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\StorePedidoModificacion;
use yura\Modelos\PedidoModificacion;

class PedidoModificacionObserver
{
    public function saved(PedidoModificacion $pedidoModificacion)
    {
        StorePedidoModificacion::dispatch($pedidoModificacion)->onQueue('store_pedido_modificacion');
    }
}
