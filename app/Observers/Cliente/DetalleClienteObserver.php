<?php

namespace yura\Observers\Cliente;

use yura\Jobs\Sincronizacion\Cliente\StoreDetalleCliente;
use yura\Modelos\DetalleCliente;

class DetalleClienteObserver
{
    public function saved(DetalleCliente $detalleCliente)
    {
        StoreDetalleCliente::dispatch($detalleCliente)->onQueue('store_detalle_cliente');
    }
}
