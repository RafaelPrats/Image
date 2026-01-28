<?php

namespace yura\Observers\Cliente;

use yura\Jobs\Sincronizacion\Cliente\DeleteClientePedidoEspecificacion;
use yura\Jobs\Sincronizacion\Cliente\StoreClientePedidoEspecificacion;
use yura\Modelos\ClientePedidoEspecificacion;

class ClientePedidoEspecificacionObserver
{
    public function saved(ClientePedidoEspecificacion $clientePedidoEspecificacion)
    {
        StoreClientePedidoEspecificacion::dispatch($clientePedidoEspecificacion)->onQueue('store_cliente_pedido_especificacion');
    }

    public function deleted(ClientePedidoEspecificacion $clientePedidoEspecificacion)
    {
        DeleteClientePedidoEspecificacion::dispatch($clientePedidoEspecificacion)->onQueue('delete_cliente_pedido_especificacion');
    }
}
